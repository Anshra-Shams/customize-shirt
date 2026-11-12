<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderItem;
use App\Models\Product;
use App\Services\BalanceService;
use App\Services\JournalEntryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerOrderController extends Controller
{
    public function __construct(
        protected JournalEntryService $journalService,
        protected BalanceService $balanceService
    ) {
    }
    /**
     * Display a listing of customer orders with filters and summary stats.
     */
    public function index(Request $request)
    {
        $query = CustomerOrder::with(['customer', 'items', 'creator']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_no', 'like', "%{$search}%")
                    ->orWhere('walkin_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('customer_name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%")
                            ->orWhere('customer_id', 'like', "%{$search}%");
                    })
                    ->orWhereHas('items', function ($iq) use ($search) {
                        $iq->where('item_name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('order_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('order_date', '<=', $request->to_date);
        }

        if ($request->filled('delivery_date')) {
            $query->whereDate('delivery_date', $request->delivery_date);
        }

        $orders = $query->orderByDesc('id')->paginate(20)->withQueryString();

        $today = now()->toDateString();
        $stats = [
            'total' => CustomerOrder::count(),
            'pending' => CustomerOrder::where('status', 'pending')->count(),
            'in_progress' => CustomerOrder::where('status', 'in_progress')->count(),
            'ready' => CustomerOrder::where('status', 'ready')->count(),
            'delivered' => CustomerOrder::where('status', 'delivered')->count(),
            'due_today' => CustomerOrder::whereDate('delivery_date', $today)
                ->whereNotIn('status', ['delivered', 'cancelled'])
                ->count(),
        ];

        $customers = Customer::orderBy('customer_name')->get();
        $statuses = CustomerOrder::STATUSES;

        return view('admin_panel.customer_orders.index', compact('orders', 'stats', 'customers', 'statuses'));
    }

    /**
     * Load shared lookup data for order create, edit, and prepare forms.
     */
    private function getFormLookups(): array
    {
        $customers = Customer::orderBy('customer_name')->get();
        $products = Product::with('unit')
            ->where('is_active', true)
            ->orderBy('item_name')
            ->get();
        $accounts = Account::whereHas('head', function ($q) {
            $q->whereIn('name', ['Cash', 'Bank']);
        })->where('status', 1)
            ->orderBy('title')
            ->get();

        if ($accounts->isEmpty()) {
            $accounts = Account::where('status', 1)
                ->whereNotIn('account_code', ['AR', 'AP', 'SALES', 'PURCHASE'])
                ->orderBy('title')
                ->get();
        }

        $statuses = CustomerOrder::STATUSES;

        return compact('customers', 'products', 'accounts', 'statuses');
    }

    /**
     * Show the form for creating a new customer order.
     */
    public function create()
    {
        $lookups = $this->getFormLookups();
        $lookups['nextOrderNo'] = CustomerOrder::generateOrderNo();

        return view('admin_panel.customer_orders.create', $lookups);
    }

    /**
     * Store a newly created customer order in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validateOrderRequest($request);

        try {
            $order = DB::transaction(function () use ($request, $validated) {
                $orderNo = !empty($validated['order_no'])
                    ? trim($validated['order_no'])
                    : CustomerOrder::generateOrderNo();

                $order = new CustomerOrder();
                $order->order_no = $orderNo;
                $order->created_by = auth()->id();

                $this->fillOrderHeader($order, $validated, $request);
                $order->save();

                $this->syncOrderItems($order, $request);
                $this->syncAdvanceJournalEntries($order);

                return $order;
            });

            return redirect()
                ->route('customer_orders.index')
                ->with('success', "Order #{$order->order_no} saved successfully.");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to save order: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified customer order.
     */
    public function show($id)
    {
        $order = CustomerOrder::with(['customer', 'items.product', 'creator'])->findOrFail($id);
        $statuses = CustomerOrder::STATUSES;

        return view('admin_panel.customer_orders.show', compact('order', 'statuses'));
    }

    /**
     * Show the form for editing the specified customer order.
     */
    public function edit($id)
    {
        $lookups = $this->getFormLookups();
        $lookups['order'] = CustomerOrder::with(['customer', 'items'])->findOrFail($id);

        return view('admin_panel.customer_orders.edit', $lookups);
    }

    /**
     * Show the confirmation page before confirming an order as In Preparing.
     */
    public function prepare($id)
    {
        $lookups = $this->getFormLookups();
        $lookups['order'] = CustomerOrder::with(['customer', 'items'])->findOrFail($id);

        return view('admin_panel.customer_orders.prepare', $lookups);
    }

    /**
     * Update the specified customer order in storage.
     */
    public function update(Request $request, $id)
    {
        $order = CustomerOrder::findOrFail($id);
        $validated = $this->validateOrderRequest($request, $order->id);

        try {
            DB::transaction(function () use ($request, $validated, $order) {
                if (!empty($validated['order_no'])) {
                    $order->order_no = trim($validated['order_no']);
                }

                $this->fillOrderHeader($order, $validated, $request);
                $order->save();

                $this->syncOrderItems($order, $request);
                $this->syncAdvanceJournalEntries($order);
            });

            $message = $request->boolean('confirm_preparing')
                ? "Order #{$order->order_no} confirmed as In Preparing."
                : "Order #{$order->order_no} updated successfully.";

            return redirect()
                ->route('customer_orders.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to update order: ' . $e->getMessage());
        }
    }

    /**
     * Update only the status of a customer order.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => ['required', Rule::in(array_keys(CustomerOrder::STATUSES))],
        ]);

        $order = CustomerOrder::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'status' => $order->status,
                'label' => CustomerOrder::STATUSES[$order->status] ?? ucfirst($order->status),
                'message' => 'Order status updated.',
            ]);
        }

        return redirect()->back()->with('success', 'Order status updated to ' . (CustomerOrder::STATUSES[$order->status] ?? $order->status) . '.');
    }

    /**
     * Print-friendly Customer Order Slip.
     */
    public function print($id)
    {
        $order = CustomerOrder::with(['customer', 'items.product', 'creator'])->findOrFail($id);

        return view('admin_panel.customer_orders.print', compact('order'));
    }

    /**
     * Remove the specified customer order from storage.
     */
    public function destroy($id)
    {
        $order = CustomerOrder::findOrFail($id);
        $orderNo = $order->order_no;

        DB::transaction(function () use ($order) {
            $this->journalService->reverseEntriesForSource($order);
            $order->delete();
        });

        return redirect()
            ->route('customer_orders.index')
            ->with('success', "Order #{$orderNo} deleted successfully.");
    }

    /**
     * Validate order creation and update payloads uniformly.
     */
    private function validateOrderRequest(Request $request, ?int $ignoreOrderId = null): array
    {
        $isWalkin = $request->input('customer_mode', 'existing') === 'walkin';

        $rules = [
            'order_no' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('customer_orders', 'order_no')->ignore($ignoreOrderId),
            ],
            'customer_mode' => ['nullable', 'in:existing,walkin'],
            'customer_id' => [$isWalkin ? 'nullable' : 'required', 'nullable', 'exists:customers,id'],
            'walkin_name' => [$isWalkin ? 'required' : 'nullable', 'nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:50'],
            'customer_address' => ['nullable', 'string', 'max:500'],
            'order_date' => ['required', 'date'],
            'delivery_date' => ['nullable', 'date'],
            'status' => ['nullable', Rule::in(array_keys(CustomerOrder::STATUSES))],
            'description' => ['nullable', 'string', 'max:2000'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'advance_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_account_id' => ['nullable', 'array'],
            'payment_account_id.*' => ['nullable', 'integer', 'exists:accounts,id'],
            'payment_amount' => ['nullable', 'array'],
            'payment_amount.*' => ['nullable', 'numeric', 'min:0'],
            'item_name' => ['required', 'array', 'min:1'],
            'item_name.*' => ['nullable', 'string', 'max:255'],
            'product_id' => ['nullable', 'array'],
            'product_id.*' => ['nullable', 'exists:products,id'],
            'item_description' => ['nullable', 'array'],
            'item_description.*' => ['nullable', 'string', 'max:1000'],
            'item_materials' => ['nullable', 'array'],
            'item_materials.*' => ['nullable', 'string'],
            'qty' => ['required', 'array', 'min:1'],
            'qty.*' => ['nullable', 'numeric', 'min:0.01'],
            'unit' => ['nullable', 'array'],
            'unit.*' => ['nullable', 'string', 'max:50'],
            'price' => ['nullable', 'array'],
            'price.*' => ['nullable', 'numeric', 'min:0'],
        ];

        $messages = [
            'customer_id.required' => 'Please select a customer or switch to Walk-in Customer.',
            'walkin_name.required' => 'Please enter the customer name.',
            'order_date.required' => 'Please select an order date.',
            'item_name.required' => 'Please add at least one order item.',
        ];

        $validated = $request->validate($rules, $messages);

        // Ensure at least one valid item row exists
        $hasValidRow = false;
        foreach ($request->input('item_name', []) as $idx => $name) {
            $qty = (float) ($request->input("qty.$idx", 0));
            $pid = $request->input("product_id.$idx");
            if ((trim((string) $name) !== '' || !empty($pid)) && $qty > 0) {
                $hasValidRow = true;
                break;
            }
        }

        if (!$hasValidRow) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'item_name' => 'Please enter at least one item with a name/product and quantity greater than 0.',
            ]);
        }

        return $validated;
    }

    /**
     * Populate header fields on CustomerOrder from validated data.
     */
    private function fillOrderHeader(CustomerOrder $order, array $validated, Request $request): void
    {
        $isWalkin = ($validated['customer_mode'] ?? 'existing') === 'walkin';

        if ($isWalkin) {
            $order->customer_id = null;
            $order->walkin_name = trim((string) ($validated['walkin_name'] ?? ''));
            $order->customer_phone = $validated['customer_phone'] ?? null;
            $order->customer_address = $validated['customer_address'] ?? null;
        } else {
            $customer = Customer::find($validated['customer_id']);
            $order->customer_id = $validated['customer_id'];
            $order->walkin_name = null;
            $order->customer_phone = !empty($validated['customer_phone'])
                ? $validated['customer_phone']
                : ($customer->mobile ?? null);
            $order->customer_address = !empty($validated['customer_address'])
                ? $validated['customer_address']
                : ($customer->address ?? null);
        }

        $order->order_date = $validated['order_date'];
        $order->delivery_date = $validated['delivery_date'] ?? null;
        $order->status = $validated['status'] ?? ($order->status ?: 'pending');
        $order->description = $validated['description'] ?? null;
        $order->discount = (float) ($validated['discount'] ?? 0);

        $paymentData = $this->parseOrderPaymentMethods($request);
        $order->payment_methods = $paymentData['methods'];
        $order->advance_amount = $paymentData['total'] > 0
            ? $paymentData['total']
            : round((float) ($validated['advance_amount'] ?? 0), 2);
    }

    /**
     * Parse submitted payment method account rows for order storage and advance posting.
     */
    private function parseOrderPaymentMethods(Request $request): array
    {
        $accountIds = $request->input('payment_account_id', []);
        $amounts = $request->input('payment_amount', []);
        $methods = [];
        $total = 0.0;

        if (!is_array($amounts)) {
            return ['methods' => [], 'total' => 0.0];
        }

        $accountMap = Account::whereIn('id', array_filter((array) $accountIds))
            ->pluck('title', 'id');

        foreach ($amounts as $idx => $rawAmt) {
            $amt = is_numeric($rawAmt) ? round((float) $rawAmt, 2) : 0.0;
            if ($amt <= 0) {
                continue;
            }
            $accId = !empty($accountIds[$idx]) ? (int) $accountIds[$idx] : null;
            $methods[] = [
                'account_id' => $accId,
                'account_title' => $accId ? ($accountMap[$accId] ?? ('Account #' . $accId)) : 'Cash / Counter',
                'amount' => $amt,
            ];
            $total += $amt;
        }

        return [
            'methods' => $methods,
            'total' => round($total, 2),
        ];
    }

    /**
     * Synchronize advance receipts for the order to the selected Account Ledgers.
     * Reverses any previous entries for this order before recording current allocations.
     */
    private function syncAdvanceJournalEntries(CustomerOrder $order): void
    {
        $this->journalService->reverseEntriesForSource($order);

        $methods = is_array($order->payment_methods) ? $order->payment_methods : [];
        if (empty($methods)) {
            return;
        }

        $customerLabel = $order->display_customer_name;
        $entryDate = $order->order_date ? $order->order_date->format('Y-m-d') : now()->format('Y-m-d');
        $description = "Advance received for Customer Order #{$order->order_no} ({$customerLabel})";
        $totalPosted = 0.0;

        foreach ($methods as $method) {
            $accountId = !empty($method['account_id']) ? (int) $method['account_id'] : 0;
            $amount = round((float) ($method['amount'] ?? 0), 2);
            if ($accountId <= 0 || $amount <= 0) {
                continue;
            }

            // Debit selected Cash/Bank account so /accounts/{id}/ledger and current_balance reflect the advance
            $this->journalService->recordEntry($order, $accountId, $amount, 0, $description, $entryDate);
            $totalPosted += $amount;
        }

        if ($totalPosted > 0) {
            // Keep double-entry journal balanced without touching CustomerLedger or Sale/POS tables
            $counterAccountId = $order->customer_id
                ? $this->balanceService->getAccountsReceivableId()
                : $this->balanceService->getSalesRevenueId();

            $this->journalService->recordEntry($order, $counterAccountId, 0, round($totalPosted, 2), $description, $entryDate);
        }
    }

    /**
     * Replace order items and recalculate order totals without touching inventory or accounting.
     */
    private function syncOrderItems(CustomerOrder $order, Request $request): void
    {
        CustomerOrderItem::where('customer_order_id', $order->id)->delete();

        $itemNames = $request->input('item_name', []);
        $productIds = $request->input('product_id', []);
        $itemDescs = $request->input('item_description', []);
        $itemMaterialsRaw = $request->input('item_materials', []);
        $quantities = $request->input('qty', []);
        $units = $request->input('unit', []);
        $prices = $request->input('price', []);

        $totalQty = 0;
        $subtotal = 0;

        foreach ($itemNames as $index => $rawName) {
            $productId = !empty($productIds[$index]) ? (int) $productIds[$index] : null;
            $qty = (float) ($quantities[$index] ?? 0);

            if ($qty <= 0) {
                continue;
            }

            $itemName = trim((string) $rawName);
            $price = (float) ($prices[$index] ?? 0);
            $unit = trim((string) ($units[$index] ?? ''));
            $materials = $this->parseOrderItemMaterials($itemMaterialsRaw[$index] ?? null);

            if ($productId) {
                $product = Product::with('unit')->find($productId);
                if ($product) {
                    if ($itemName === '') {
                        $itemName = $product->item_name;
                    }
                    if ($unit === '' && $product->unit) {
                        $unit = $product->unit->name ?? 'Pcs';
                    }
                }
            }

            if ($itemName === '') {
                continue;
            }

            if ($unit === '') {
                $unit = 'Pcs';
            }

            $lineTotal = round($qty * $price, 2);

            CustomerOrderItem::create([
                'customer_order_id' => $order->id,
                'product_id' => $productId,
                'item_name' => $itemName,
                'description' => $itemDescs[$index] ?? null,
                'materials' => $materials,
                'qty' => $qty,
                'unit' => $unit,
                'price' => $price,
                'total' => $lineTotal,
            ]);

            $totalQty += $qty;
            $subtotal += $lineTotal;
        }

        $discount = (float) $order->discount;
        $order->total_qty = round($totalQty, 2);
        $order->subtotal = round($subtotal, 2);
        $order->total_amount = round(max(0, $subtotal - $discount), 2);
        $order->save();
    }

    /**
     * Parse and sanitize per-item materials/services JSON for customer orders only.
     */
    private function parseOrderItemMaterials($rawJson): array
    {
        $decoded = is_string($rawJson) ? json_decode($rawJson, true) : (is_array($rawJson) ? $rawJson : []);
        $clean = [];

        if (!is_array($decoded)) {
            return $clean;
        }

        foreach ($decoded as $item) {
            if (!is_array($item)) {
                continue;
            }
            $name = trim((string) ($item['name'] ?? ''));
            $amount = is_numeric($item['amount'] ?? null) ? round((float) $item['amount'], 2) : 0.0;
            if ($name === '' && $amount == 0.0) {
                continue;
            }
            $clean[] = [
                'name' => $name,
                'amount' => $amount,
                'is_custom' => !empty($item['is_custom']),
            ];
        }

        return $clean;
    }
}
