<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt LNPAY-{{ str_pad($payment->id, 3, '0', STR_PAD_LEFT) }} - CFMC</title>
    <style>
        :root {
            --brand-primary: #1f6f5c;
            --brand-border: #e6e9ef;
            --brand-surface-muted: #f8fafc;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: var(--brand-surface-muted);
            color: #1e293b;
            margin: 0;
            padding: 32px 16px;
        }
        .receipt {
            max-width: 640px;
            margin: 0 auto;
            background: #fff;
            border: 1px solid var(--brand-border);
            border-radius: 12px;
            padding: 40px;
        }
        .receipt-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid var(--brand-primary);
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .receipt-header h1 {
            font-size: 22px;
            margin: 0 0 4px;
            color: var(--brand-primary);
        }
        .receipt-header p {
            margin: 0;
            font-size: 13px;
            color: #64748b;
        }
        .receipt-badge {
            font-size: 13px;
            font-weight: 600;
            color: var(--brand-primary);
            border: 1px solid var(--brand-primary);
            border-radius: 999px;
            padding: 6px 14px;
        }
        .receipt-title {
            text-align: center;
            margin-bottom: 24px;
        }
        .receipt-title h2 {
            margin: 0;
            font-size: 18px;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #1e293b;
        }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px 24px;
            margin-bottom: 24px;
        }
        .grid .full { grid-column: 1 / -1; }
        .field label {
            display: block;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #94a3b8;
            margin-bottom: 2px;
        }
        .field p {
            margin: 0;
            font-size: 15px;
            font-weight: 600;
            color: #1e293b;
        }
        .amount-box {
            background: var(--brand-surface-muted);
            border: 1px dashed var(--brand-border);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-bottom: 24px;
        }
        .amount-box label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #64748b;
        }
        .amount-box .amount {
            font-size: 32px;
            font-weight: 700;
            color: var(--brand-primary);
            margin-top: 4px;
        }
        .receipt-footer {
            border-top: 1px solid var(--brand-border);
            padding-top: 16px;
            font-size: 12px;
            color: #94a3b8;
            text-align: center;
        }
        .print-bar {
            max-width: 640px;
            margin: 0 auto 16px;
            display: flex;
            justify-content: flex-end;
        }
        .print-bar button {
            background: var(--brand-primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .print-bar { display: none; }
            .receipt { border: none; border-radius: 0; padding: 0; max-width: 100%; }
        }
    </style>
</head>
<body>
    <div class="print-bar">
        <button type="button" onclick="window.print()">Print / Save as PDF</button>
    </div>

    <div class="receipt">
        <div class="receipt-header">
            <div>
                <h1>CFMC Cooperative</h1>
                <p>Official Payment Receipt</p>
            </div>
            <span class="receipt-badge">LNPAY-{{ str_pad($payment->id, 3, '0', STR_PAD_LEFT) }}</span>
        </div>

        @php
            $paymentTypeLabel = match ($payment->type) {
                'payment' => 'Loan Payment',
                'prepayment' => 'Prepayment',
                default => 'Interest Charge',
            };
        @endphp

        <div class="receipt-title">
            <h2>{{ $paymentTypeLabel }} Receipt</h2>
        </div>

        <div class="grid">
            <div class="field"><label>Farmer</label><p>{{ $payment->loan->farmer->full_name }}</p></div>
            <div class="field"><label>Date</label><p>{{ $payment->transaction_date->format('M d, Y') }}</p></div>
            <div class="field"><label>Loan ID</label><p>LN-{{ str_pad($payment->loan_id, 3, '0', STR_PAD_LEFT) }}</p></div>
            <div class="field"><label>Balance After Payment</label><p>{{ peso($payment->balance_after) }}</p></div>
            <div class="field"><label>Recorded By</label><p>{{ $payment->recordedBy->name ?? '—' }}</p></div>
            <div class="field"><label>Transaction Type</label><p>{{ $paymentTypeLabel }}</p></div>
            @if($payment->notes)
            <div class="field full"><label>Notes</label><p>{{ $payment->notes }}</p></div>
            @endif
        </div>

        <div class="amount-box">
            <label>{{ $payment->type === 'interest' ? 'Interest Charged' : 'Amount Paid' }}</label>
            <div class="amount">{{ peso($payment->amount) }}</div>
        </div>

        <div class="receipt-footer">
            This receipt was generated by the CFMC Cooperative Management System on {{ now()->format('M d, Y g:i A') }}.
        </div>
    </div>
</body>
</html>
