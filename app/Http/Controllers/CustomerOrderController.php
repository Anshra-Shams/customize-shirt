<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\Customer;
use App\Models\CustomerLedger;
use App\Models\CustomerOrder;
use App\Models\CustomerOrderItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Vendor;
use App\Models\VendorLedger;
use App\Models\WarehouseStock;
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
            ->where(function ($q) {
                $q->where('is_raw_material', 0)
                  ->where('is_part', 0);
            })
            ->where('is_active', true)
            ->orderBy('item_name')
            ->get();

        $rawMaterials = Product::rawMaterials()
            ->with(['unit'])
            ->withSum('warehouseStocks', 'total_pieces')
            ->where('is_active', true)
            ->orderBy('item_name')
            ->get();

        $rawMaterialCategories = Category::where('is_raw_material', 1)
            ->orWhereIn('id', Product::rawMaterials()->whereNotNull('category_id')->pluck('category_id'))
            ->orderBy('name')
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

        $stitchingVendors = Vendor::where('type', 'stitching')->orderBy('name')->get();
        $allVendors = Vendor::orderBy('name')->get();

        return compact(
            'customers',
            'products',
            'rawMaterials',
            'rawMaterialCategories',
            'accounts',
            'statuses',
            'stitchingVendors',
            'allVendors'
        );
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
                if ($request->boolean('confirm_preparing')) {
                    $order->status = 'ready';
                }
                $order->save();

                $this->syncOrderItems($order, $request);
                $this->syncOrderMaterialsStock($order);
                $this->syncAdvanceJournalEntries($order);
                $this->syncCustomerLedgerAdvance($order);
                $this->syncStitchingVendorJournalEntries($order);

                \App\Models\SystemNotification::createDeliveryNotification($order, 'booked');

                return $order;
            });

            return redirect()
                ->route('customer_orders.print', $order->id)
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
     * Redirect to the Sales module for delivering the customer order.
     */
    public function deliver($id)
    {
        return redirect()->route('sales.from.order', $id);
    }

    /**
     * Complete and record the delivery of a customer order, including handover details and final payment.
     */
    public function processDelivery(Request $request, $id)
    {
        $order = CustomerOrder::findOrFail($id);

        $validated = $request->validate([
            'delivery_date' => ['required', 'date'],
            'received_by' => ['nullable', 'string', 'max:255'],
            'delivery_notes' => ['nullable', 'string', 'max:2000'],
            'collect_payment' => ['nullable'],
            'payment_account_id' => ['nullable', 'array'],
            'payment_account_id.*' => ['nullable', 'integer', 'exists:accounts,id'],
            'payment_amount' => ['nullable', 'array'],
            'payment_amount.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            DB::transaction(function () use ($request, $validated, $order) {
                $order->status = 'delivered';
                $order->delivery_date = $validated['delivery_date'];
                $order->delivered_at = now();
                $order->delivered_by = auth()->id();
                $order->received_by = !empty($validated['received_by'])
                    ? trim($validated['received_by'])
                    : $order->customer_display_name;
                $order->delivery_notes = $validated['delivery_notes'] ?? null;

                $collectPayment = $request->boolean('collect_payment') || !empty($request->input('payment_amount'));
                if ($collectPayment) {
                    $paymentData = $this->parseOrderPaymentMethods($request);
                    $order->delivery_payment_amount = $paymentData['total'];
                    $order->delivery_payment_methods = $paymentData['methods'];
                } else {
                    $order->delivery_payment_amount = 0;
                    $order->delivery_payment_methods = [];
                }

                $order->save();

                $this->syncAdvanceJournalEntries($order);
            });

            return redirect()
                ->route('customer_orders.show', $order->id)
                ->with('success', "Order #{$order->order_no} has been marked as Delivered successfully.");
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to deliver order: ' . $e->getMessage());
        }
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
                if ($request->boolean('confirm_preparing')) {
                    $order->status = 'ready';
                }
                $order->save();

                $this->syncOrderItems($order, $request);
                $this->syncOrderMaterialsStock($order);
                $this->syncAdvanceJournalEntries($order);
                $this->syncCustomerLedgerAdvance($order);
                $this->syncStitchingVendorJournalEntries($order);

                if ($order->status === 'ready') {
                    \App\Models\SystemNotification::createDeliveryNotification($order, 'ready');
                } elseif ($order->status === 'delivered' || $order->status === 'cancelled') {
                    \App\Models\SystemNotification::resolveOrderNotifications($order->id);
                }
            });

            $message = $request->boolean('confirm_preparing')
                ? "Order #{$order->order_no} confirmed and marked as Ready."
                : "Order #{$order->order_no} updated successfully.";

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'order' => $order
                ]);
            }

            return redirect()
                ->route('customer_orders.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update order: ' . $e->getMessage()
                ], 422);
            }

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
        $oldStatus = $order->status;
        $order->status = $request->status;

        if ($request->status === 'delivered') {
            if (!$order->delivered_at) {
                $order->delivered_at = now();
            }
            if (!$order->delivered_by) {
                $order->delivered_by = auth()->id();
            }
        }

        $order->save();

        if ($request->status === 'cancelled') {
            $this->reverseOrderMaterialsStock($order);
            \App\Models\SystemNotification::resolveOrderNotifications($order->id);
        } elseif ($request->status === 'delivered') {
            \App\Models\SystemNotification::resolveOrderNotifications($order->id);
        } elseif ($request->status === 'ready') {
            \App\Models\SystemNotification::createDeliveryNotification($order, 'ready');
        } elseif ($oldStatus === 'cancelled' && in_array($request->status, ['in_progress', 'ready', 'delivered'])) {
            $this->syncOrderMaterialsStock($order);
        }

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
            CustomerLedger::where('description', 'like', "%Order #{$order->order_no}%")->delete();
            $this->reverseOrderMaterialsStock($order);
            \App\Models\SystemNotification::where('source_type', 'App\Models\CustomerOrder')->where('source_id', $order->id)->delete();
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
     * Synchronize advance and delivery receipts for the order to the selected Account Ledgers.
     * Reverses any previous entries for this order before recording current allocations.
     */
    private function syncAdvanceJournalEntries(CustomerOrder $order): void
    {
        $this->journalService->reverseEntriesForSource($order);

        $customerLabel = $order->customer_display_name;
        $totalPosted = 0.0;
        $customerParty = $order->customer_id ? Customer::find($order->customer_id) : null;

        // 1. Advance Receipts
        $methods = is_array($order->payment_methods) ? $order->payment_methods : [];
        $entryDate = $order->order_date ? $order->order_date->format('Y-m-d') : now()->format('Y-m-d');
        $advanceDescription = "Advance received for Customer Order #{$order->order_no} ({$customerLabel})";
        $totalAdvance = 0.0;

        foreach ($methods as $method) {
            $accountId = !empty($method['account_id']) ? (int) $method['account_id'] : 0;
            $amount = round((float) ($method['amount'] ?? 0), 2);
            if ($accountId <= 0 || $amount <= 0) {
                continue;
            }

            // Debit selected Cash/Bank account so /accounts/{id}/ledger and current_balance reflect the advance
            $this->journalService->recordEntry($order, $accountId, $amount, 0, $advanceDescription, $entryDate);
            $totalAdvance += $amount;
        }

        // If no payment method accounts were provided but advance_amount > 0, fallback to default Cash account
        if ($totalAdvance <= 0 && (float) $order->advance_amount > 0) {
            $defaultCashAcc = Account::whereHas('head', function ($q) {
                $q->where('name', 'Cash');
            })->where('status', 1)->first() ?? Account::where('status', 1)->first();

            if ($defaultCashAcc) {
                $amount = round((float) $order->advance_amount, 2);
                $this->journalService->recordEntry($order, $defaultCashAcc->id, $amount, 0, $advanceDescription, $entryDate);
                $totalAdvance = $amount;
            }
        }

        $totalPosted += $totalAdvance;

        // 2. Delivery Receipts (if any)
        $deliveryMethods = is_array($order->delivery_payment_methods) ? $order->delivery_payment_methods : [];
        $deliveryDate = $order->delivered_at ? $order->delivered_at->format('Y-m-d') : ($order->delivery_date ? $order->delivery_date->format('Y-m-d') : now()->format('Y-m-d'));
        $deliveryDescription = "Delivery payment for Customer Order #{$order->order_no} ({$customerLabel})";
        $totalDelivery = 0.0;

        foreach ($deliveryMethods as $method) {
            $accountId = !empty($method['account_id']) ? (int) $method['account_id'] : 0;
            $amount = round((float) ($method['amount'] ?? 0), 2);
            if ($accountId <= 0 || $amount <= 0) {
                continue;
            }

            // Debit selected Cash/Bank account so /accounts/{id}/ledger and current_balance reflect delivery payment
            $this->journalService->recordEntry($order, $accountId, $amount, 0, $deliveryDescription, $deliveryDate);
            $totalDelivery += $amount;
        }
        $totalPosted += $totalDelivery;

        if ($totalPosted > 0) {
            // Keep double-entry journal balanced without touching CustomerLedger or Sale/POS tables
            $counterAccountId = $order->customer_id
                ? $this->balanceService->getAccountsReceivableId()
                : $this->balanceService->getSalesRevenueId();

            if ($totalAdvance > 0) {
                $this->journalService->recordEntry(
                    $order,
                    $counterAccountId,
                    0,
                    round($totalAdvance, 2),
                    $advanceDescription,
                    $entryDate,
                    $customerParty
                );
            }
            if ($totalDelivery > 0) {
                $this->journalService->recordEntry(
                    $order,
                    $counterAccountId,
                    0,
                    round($totalDelivery, 2),
                    $deliveryDescription,
                    $deliveryDate,
                    $customerParty
                );
            }
        }
    }

    /**
     * Synchronize advance receipt to Customer Ledger for registered customers.
     */
    private function syncCustomerLedgerAdvance(CustomerOrder $order): void
    {
        // 1. Remove previous advance entries for this order
        CustomerLedger::where('description', 'like', "%Order #{$order->order_no}%")->delete();

        // 2. If registered customer and advance > 0, post advance receipt to Customer Ledger
        if ($order->customer_id && $order->advance_amount > 0) {
            $customer = Customer::find($order->customer_id);
            if ($customer) {
                $lastEntry = CustomerLedger::where('customer_id', $customer->id)->latest('id')->first();
                $prevBal = $lastEntry ? $lastEntry->closing_balance : ($customer->opening_balance ?? 0);
                $newBal = $prevBal - (float) $order->advance_amount; // Advance received reduces customer balance (credit)

                CustomerLedger::create([
                    'customer_id' => $customer->id,
                    'admin_or_user_id' => auth()->id() ?? 1,
                    'previous_balance' => $prevBal,
                    'opening_balance' => 0,
                    'closing_balance' => $newBal,
                    'description' => "Advance Received for Customer Order #{$order->order_no}",
                ]);

                $customer->previous_balance = $newBal;
                $customer->save();
            }
        }
    }

    /**
     * Synchronize stitching vendor charges to Journal Entries and Vendor Ledgers.
     * Records Accounts Payable (Credit) for each assigned stitching vendor so their
     * ledger reflects that we owe them for stitching this order.
     */
    private function syncStitchingVendorJournalEntries(CustomerOrder $order): void
    {
        $order->load('items');
        $apId = $this->balanceService->getAccountsPayableId();
        $entryDate = $order->order_date ? $order->order_date->format('Y-m-d') : now()->format('Y-m-d');

        // Stitching Expense account for balanced double-entry
        $stitchingExpenseAcc = Account::where('account_code', 'STITCH_EXP')
            ->orWhere('title', 'like', '%Stitching%')
            ->first();

        if (!$stitchingExpenseAcc) {
            $purchaseExp = Account::where('account_code', 'PURCHASE')->first();
            $stitchingExpenseAcc = Account::create([
                'title' => 'Stitching Expense',
                'account_code' => 'STITCH_EXP',
                'head_id' => $purchaseExp ? $purchaseExp->head_id : 3, // Expense
                'status' => 1,
            ]);
        }

        $vendorTotals = []; // [vendor_id => ['vendor' => $v, 'amount' => 0.0, 'items' => []]]

        foreach ($order->items as $item) {
            $materials = is_array($item->materials) ? $item->materials : [];
            foreach ($materials as $mat) {
                if (($mat['type'] ?? '') !== 'stitching') {
                    continue;
                }
                $vendorId = !empty($mat['vendor_id']) ? (int) $mat['vendor_id'] : null;
                $amount = is_numeric($mat['amount'] ?? null) ? round((float) $mat['amount'], 2) : 0.0;

                if ($vendorId && $amount > 0) {
                    if (!isset($vendorTotals[$vendorId])) {
                        $vendorTotals[$vendorId] = [
                            'vendor' => Vendor::find($vendorId),
                            'amount' => 0.0,
                            'items' => [],
                        ];
                    }
                    $vendorTotals[$vendorId]['amount'] += $amount;
                    $vendorTotals[$vendorId]['items'][] = $item->item_name;
                }
            }
        }

        foreach ($vendorTotals as $vendorId => $data) {
            $vendor = $data['vendor'];
            if (!$vendor || $data['amount'] <= 0) {
                continue;
            }

            $itemNames = implode(', ', array_unique($data['items']));
            $desc = "Stitching charges for Customer Order #{$order->order_no}" . ($itemNames ? " ({$itemNames})" : '');

            // 1. Debit Stitching Expense Account
            if ($stitchingExpenseAcc) {
                $this->journalService->recordEntry(
                    $order,
                    $stitchingExpenseAcc->id,
                    $data['amount'], // Debit: Expense increases
                    0,               // Credit
                    $desc,
                    $entryDate
                );
            }

            // 2. Credit Accounts Payable Account (with party = Vendor)
            // This is what getVendorLedger and getVendorBalance reads!
            $this->journalService->recordEntry(
                $order,
                $apId,
                0,               // Debit
                $data['amount'], // Credit: We owe vendor (Payable increases)
                $desc,
                $entryDate,
                $vendor          // Party = Vendor
            );

            // 3. Update legacy VendorLedger closing balance
            $ledger = VendorLedger::firstOrCreate(
                ['vendor_id' => $vendor->id],
                [
                    'admin_or_user_id' => auth()->id() ?? 1,
                    'opening_balance' => 0,
                    'previous_balance' => 0,
                    'closing_balance' => 0,
                ]
            );
            $ledger->closing_balance = $this->balanceService->getVendorBalance($vendor->id);
            $ledger->save();
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
            $type = $item['type'] ?? (!empty($item['vendor_id']) || !empty($item['vendor_name']) ? 'stitching' : 'material');
            $name = trim((string) ($item['name'] ?? ''));
            if ($type === 'stitching' && empty($name)) {
                $name = !empty($item['vendor_name']) ? ('Stitching - ' . $item['vendor_name']) : 'Stitching';
            }
            $amount = is_numeric($item['amount'] ?? null) ? round((float) $item['amount'], 2) : 0.0;
            $qty = is_numeric($item['qty'] ?? null) ? round((float) $item['qty'], 2) : 1.0;
            if ($qty <= 0) {
                $qty = 1.0;
            }
            $unit = trim((string) ($item['unit'] ?? 'Pcs'));
            $unitPrice = is_numeric($item['unit_price'] ?? null) 
                ? round((float) $item['unit_price'], 2) 
                : (is_numeric($item['rate'] ?? null) ? round((float) $item['rate'], 2) : 0.0);
            $rate = $unitPrice;

            if ($name === '' && $amount == 0.0 && $qty == 0.0) {
                continue;
            }
            $clean[] = [
                'type' => $type,
                'name' => $name,
                'amount' => $amount,
                'qty' => $qty,
                'unit' => $unit ?: 'Pcs',
                'rate' => $rate,
                'unit_price' => $unitPrice,
                'category_id' => !empty($item['category_id']) ? (int) $item['category_id'] : null,
                'product_id' => !empty($item['product_id']) ? (int) $item['product_id'] : null,
                'vendor_id' => !empty($item['vendor_id']) ? (int) $item['vendor_id'] : null,
                'vendor_name' => !empty($item['vendor_name']) ? trim((string) $item['vendor_name']) : null,
                'is_custom' => !empty($item['is_custom']),
            ];
        }

        return $clean;
    }

    /**
     * Synchronize raw material stock deduction for Customer Orders.
     * When materials are assigned/prepared, deduct raw material stock from WarehouseStock
     * and log StockMovement ('out'). If materials change or order is updated, reverse previous
     * deductions first to maintain exact stock balance.
     */
    private function syncOrderMaterialsStock(CustomerOrder $order): void
    {
        // 1. First reverse any existing stock deductions for this order
        $this->reverseOrderMaterialsStock($order);

        // If order is cancelled, do not deduct stock
        if ($order->status === 'cancelled') {
            return;
        }

        // 2. Fetch fresh items with materials
        $order->load('items');

        $now = now();
        $warehouseId = 1; // Default Main Store

        foreach ($order->items as $orderItem) {
            $materials = is_array($orderItem->materials) ? $orderItem->materials : [];
            foreach ($materials as $mat) {
                if (($mat['type'] ?? '') === 'stitching') {
                    continue; // Skip stitching services from inventory stock deduction
                }
                $productId = !empty($mat['product_id']) ? (int) $mat['product_id'] : null;
                $qty = is_numeric($mat['qty'] ?? null) ? (float) $mat['qty'] : 0.0;

                if (!$productId || $qty <= 0) {
                    continue;
                }

                $product = Product::find($productId);
                if (!$product) {
                    continue;
                }

                $matName = $mat['name'] ?? $product->item_name;
                $ppb = (float) ($product->pieces_per_box > 0 ? $product->pieces_per_box : 1);

                // Find or create WarehouseStock in warehouse
                $whStock = WarehouseStock::firstOrNew([
                    'warehouse_id' => $warehouseId,
                    'product_id' => $productId,
                ]);

                $whStock->total_pieces = (float) ($whStock->total_pieces ?? 0) - $qty;
                $whStock->quantity = $whStock->total_pieces / $ppb;
                $whStock->save();

                // Record StockMovement ('out')
                StockMovement::create([
                    'product_id' => $productId,
                    'type' => 'out',
                    'qty' => -1 * abs($qty),
                    'ref_type' => 'CUSTOMER_ORDER',
                    'ref_id' => $order->id,
                    'note' => "Material consumed for Order #{$order->order_no} ({$orderItem->item_name} -> {$matName})",
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Reverse any raw material stock deductions previously made for this customer order.
     */
    private function reverseOrderMaterialsStock(CustomerOrder $order): void
    {
        $movements = StockMovement::where('ref_type', 'CUSTOMER_ORDER')
            ->where('ref_id', $order->id)
            ->get();

        $warehouseId = 1;

        foreach ($movements as $mov) {
            $product = Product::find($mov->product_id);
            $ppb = (float) ($product && $product->pieces_per_box > 0 ? $product->pieces_per_box : 1);
            $qtyDeducted = abs((float) $mov->qty);

            $whStock = WarehouseStock::where('warehouse_id', $warehouseId)
                ->where('product_id', $mov->product_id)
                ->first();

            if ($whStock) {
                $whStock->total_pieces = (float) ($whStock->total_pieces ?? 0) + $qtyDeducted;
                $whStock->quantity = $whStock->total_pieces / $ppb;
                $whStock->save();
            }

            $mov->delete();
        }
    }
}
