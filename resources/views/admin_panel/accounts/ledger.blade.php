@extends('admin_panel.layout.app')

@section('content')
    <style>
        .ledger-header {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px 8px 0 0;
        }

        .balance-positive {
            color: #198754;
            font-weight: bold;
        }

        .balance-negative {
            color: #dc3545;
            font-weight: bold;
        }

        .table-ledger th {
            background-color: #212529 !important;
            color: #fff;
            text-align: center;
        }

        .table-ledger td {
            vertical-align: middle;
        }
    </style>

    <div class="main-content">
        <div class="main-content-inner">
            <div class="container-fluid mt-4">

                <div class="card shadow-sm">
                    <!-- Ledger Header -->
                    <div class="ledger-header d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1 text-primary"><i class="bi bi-book"></i> General Ledger</h4>
                            <h5 class="text-dark">{{ $account->title }} <span
                                    class="text-muted fs-6">({{ $account->account_code }})</span></h5>
                            <p class="mb-0 text-muted">Head: {{ $account->head->name ?? 'N/A' }} | Type: {{ $account->type }}
                            </p>
                        </div>
                        <div class="text-end">
                            <h3 class="{{ $account->current_balance >= 0 ? 'balance-positive' : 'balance-negative' }}">
                                {{ number_format(abs($account->current_balance), 2) }}
                                <small class="fs-6 text-muted">{{ $account->current_balance >= 0 ? 'Dr' : 'Cr' }}</small>
                            </h3>
                            <span class="badge bg-secondary">Current Balance</span>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Filters -->
                        <form method="GET" class="row g-3 mb-4">
                            <div class="col-md-3">
                                <label class="form-label">From Date</label>
                                <input type="text" name="from_date" value="{{ request('from_date') }}"
                                    class="form-control datepicker-custom">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">To Date</label>
                                <input type="text" name="to_date" value="{{ request('to_date') }}" class="form-control datepicker-custom">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100"><i class="bi bi-filter"></i>
                                    Filter</button>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <a href="{{ route('accounts.ledger', $account->id) }}"
                                    class="btn btn-outline-secondary w-100">Reset</a>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <a href="{{ route('view_all') }}" class="btn btn-secondary w-100"><i
                                        class="bi bi-arrow-left"></i> Back</a>
                            </div>
                        </form>

                        <!-- Ledger Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-hover table-ledger">
                                <thead>
                                    <tr>
                                        <th width="10%">Date</th>
                                        <th width="12%">Voucher No</th>
                                        <th width="25%" class="text-start">Description</th>
                                        <th width="18%">Party</th>
                                        <th width="10%">Debit</th>
                                        <th width="10%">Credit</th>
                                        <th width="15%">Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $runningBalance = $account->opening_balance ?? 0;
                                    @endphp

                                    {{-- Opening Balance Row --}}
                                    <tr class="bg-light">
                                        <td colspan="6" class="text-end fw-bold">Opening Balance</td>
                                        <td class="text-end fw-bold">{{ number_format($runningBalance, 2) }}</td>
                                    </tr>

                                    @foreach ($entries as $entry)
                                        @php
                                            $debit = $entry->debit ?? 0;
                                            $credit = $entry->credit ?? 0;
                                            $runningBalance = $runningBalance + $debit - $credit;

                                            $voucherNo = '-';
                                            if ($entry->source && $entry->source->voucher_no) {
                                                $voucherNo = $entry->source->voucher_no;
                                            } elseif ($entry->source && $entry->source->invoice_no) {
                                                $voucherNo = $entry->source->invoice_no;
                                            } elseif ($entry->source && $entry->source->order_no) {
                                                $voucherNo = $entry->source->order_no;
                                            }

                                            // Resolve party name and type
                                            $partyName = '';
                                            $partyType = '';
                                            $partyBadgeClass = 'bg-secondary';
                                            if ($entry->party) {
                                                if ($entry->party_type === 'App\\Models\\Customer') {
                                                    $partyName = $entry->party->customer_name ?? 'Unknown';
                                                    $partyType = 'Customer';
                                                    $partyBadgeClass = 'bg-primary';
                                                } elseif ($entry->party_type === 'App\\Models\\Vendor') {
                                                    $partyName = $entry->party->name ?? 'Unknown';
                                                    $partyType = 'Vendor';
                                                    $partyBadgeClass = 'bg-warning text-dark';
                                                } else {
                                                    $partyName = $entry->party->title ?? $entry->party->name ?? 'Account';
                                                    $partyType = 'Account';
                                                    $partyBadgeClass = 'bg-info text-dark';
                                                }
                                            } elseif ($entry->source instanceof \App\Models\CustomerOrder) {
                                                $partyType = 'Customer';
                                                $partyBadgeClass = 'bg-primary';
                                                $partyName = $entry->source->customer ? ($entry->source->customer->customer_name ?? $entry->source->customer->name) : ($entry->source->walkin_name ?? 'Walk-in');
                                            }

                                            // Detect Customer Order & Delivery details from description if party or order is present
                                            $desc = $entry->description ?? '';
                                            $orderNo = null;
                                            if (preg_match('/(?:Customer Order|Order)\s*#([A-Za-z0-9\-_]+)/i', $desc, $mOrd)) {
                                                $orderNo = $mOrd[1];
                                            }

                                            // If no order found yet, check if description mentions a Delivery invoice (e.g. #DEL-0003)
                                            if (preg_match('/(?:Invoice|Delivery)\s*#(DEL-[A-Za-z0-9\-_]+)/i', $desc, $mDel)) {
                                                $delInvoiceNo = $mDel[1];
                                                $delSale = \App\Models\Sale::with('customer')->where('invoice_no', $delInvoiceNo)->first();
                                                if ($delSale) {
                                                    $linkedOrder = \App\Models\CustomerOrder::where('sale_id', $delSale->id)->first();
                                                    if (!$linkedOrder && !empty($delSale->reference) && preg_match('/ORD-[A-Za-z0-9\-]+/i', $delSale->reference, $mRef)) {
                                                        $linkedOrder = \App\Models\CustomerOrder::where('order_no', $mRef[0])->first();
                                                    }

                                                    if ($linkedOrder && !$orderNo) {
                                                        $orderNo = $linkedOrder->order_no;
                                                    }

                                                    $detectedCustomer = $delSale->customer ? ($delSale->customer->customer_name ?? $delSale->customer->name) : ($delSale->walkin_name ?? ($linkedOrder ? ($linkedOrder->customer ? ($linkedOrder->customer->customer_name ?? $linkedOrder->customer->name) : $linkedOrder->walkin_name) : null));

                                                    if (empty($partyName) && $detectedCustomer) {
                                                        $partyName = $detectedCustomer;
                                                        $partyType = 'Customer';
                                                        $partyBadgeClass = 'bg-primary';
                                                    }

                                                    if (!\Illuminate\Support\Str::contains($desc, ['Customer Order', 'ORD-']) && $orderNo) {
                                                        $desc .= " (Customer Order #{$orderNo}" . ($detectedCustomer ? " - {$detectedCustomer}" : '') . ")";
                                                     }
                                                }
                                            }

                                            // Fallback to extract customer name from description if partyName is still empty
                                            if (empty($partyName)) {
                                                if (preg_match('/\((?:Customer Order #[A-Za-z0-9\-_]+\s*-\s*)([^)]+)\)/i', $desc, $mPart)) {
                                                    $partyName = trim($mPart[1]);
                                                    $partyType = 'Customer';
                                                    $partyBadgeClass = 'bg-primary';
                                                } elseif (preg_match('/(?:Advance received for Customer Order|Delivery payment for Customer Order)\s*#[A-Za-z0-9\-_]+\s*\(([^)]+)\)/i', $desc, $mPart)) {
                                                    $partyName = trim($mPart[1]);
                                                    $partyType = 'Customer';
                                                    $partyBadgeClass = 'bg-primary';
                                                }
                                            }
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $entry->entry_date->format('d/m/Y') }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border">{{ $voucherNo }}</span>
                                            </td>
                                            <td>{{ $desc }}</td>
                                            <td>
                                                @if ($partyName)
                                                    <span class="badge {{ $partyBadgeClass }}" style="font-size: 0.7rem;">{{ $partyType }}</span>
                                                    <span class="fw-bold d-block" style="font-size: 0.85rem;">{{ $partyName }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-end text-success">
                                                {{ $debit > 0 ? number_format($debit, 2) : '-' }}</td>
                                            <td class="text-end text-danger">
                                                {{ $credit > 0 ? number_format($credit, 2) : '-' }}</td>
                                            <td class="text-end fw-bold">
                                                {{ number_format(abs($runningBalance), 2) }}
                                                <small class="text-muted">{{ $runningBalance >= 0 ? 'Dr' : 'Cr' }}</small>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-dark">
                                        <td colspan="4" class="text-end">Total Period</td>
                                        <td class="text-end">{{ number_format($entries->sum('debit'), 2) }}</td>
                                        <td class="text-end">{{ number_format($entries->sum('credit'), 2) }}</td>
                                        <td class="text-end">{{ number_format(abs($runningBalance), 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-muted text-center">
                        End of Report
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
