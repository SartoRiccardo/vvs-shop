<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <style>
        @page { size: A4; margin: 0.8cm 1.2cm; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, Helvetica, sans-serif; font-size: 7pt; color: #1a1a1a; background: #fff; }

        .sheet { width: 100%; border-collapse: collapse; page-break-after: always; }
        .sheet:last-child { page-break-after: avoid; }
        .copy-cell { vertical-align: top; overflow: hidden; padding: 0; }
        .cut-row td { border-top: 1px dashed #ccc; text-align: center; font-size: 5.5pt; color: #bbb; vertical-align: middle; }

        .invoice { }

        .l-hdr { width: 100%; border-collapse: collapse; border-bottom: 2px solid #1a1a1a; margin-bottom: 0.55em; }
        .l-hdr td { padding-bottom: 0.4em; }
        .hdr-title { font-size: 17pt; font-weight: 700; letter-spacing: 0.05em; line-height: 1; vertical-align: bottom; }
        .hdr-meta { text-align: right; vertical-align: top; }
        .hdr-num { font-size: 10pt; font-weight: 700; margin-bottom: 0.1em; }
        .hdr-date { font-size: 7pt; color: #555; margin-bottom: 0.25em; }
        .hdr-total { font-size: 12pt; font-weight: 700; }

        .l-parties { width: 100%; border-collapse: separate; border-spacing: 6pt 0; margin-bottom: 0.55em; }
        .pcell { width: 50%; background: #f5f5f5; padding: 0.4em 0.55em; vertical-align: top; }
        .party-lbl { font-size: 5pt; font-weight: 700; letter-spacing: 0.12em; color: #888; text-transform: uppercase; margin-bottom: 0.3em; }
        .party-name { font-weight: 700; font-size: 7.5pt; margin-bottom: 0.2em; }
        .party-line { font-size: 6.5pt; color: #444; line-height: 1.4; }

        .items { width: 100%; border-collapse: collapse; margin-bottom: 0.55em; font-size: 7pt; }
        .items th { background: #1a1a1a; color: #fff; padding: 0.22em 0.35em; font-size: 6pt; letter-spacing: 0.04em; text-align: left; }
        .items td { padding: 0.22em 0.35em; border-bottom: 1px solid #e8e8e8; vertical-align: top; }
        .items tr:last-child td { border-bottom: none; }
        .items tr.even td { background: #fafafa; }

        .l-bot { width: 100%; border-collapse: separate; border-spacing: 6pt 0; margin-bottom: 0.55em; }
        .vcell { width: 58%; vertical-align: top; }
        .paycell { vertical-align: top; border: 1px solid #ddd; padding: 0.4em 0.55em; font-size: 7pt; }

        .stbl { width: 100%; border-collapse: collapse; font-size: 6.5pt; margin-bottom: 0.3em; }
        .stbl th { background: #efefef; padding: 0.2em 0.35em; font-size: 6pt; font-weight: 600; text-align: left; border-bottom: 1px solid #ddd; }
        .stbl td { padding: 0.2em 0.35em; border-bottom: 1px solid #eee; }

        .total-tbl { width: 100%; border-collapse: collapse; background: #1a1a1a; color: #fff; }
        .total-tbl td { padding: 0.35em 0.5em; vertical-align: middle; }
        .total-lbl { font-size: 6.5pt; font-weight: 600; letter-spacing: 0.05em; }
        .total-val { text-align: right; font-size: 10pt; font-weight: 700; }

        .sec-lbl { font-size: 5pt; font-weight: 700; letter-spacing: 0.12em; color: #888; text-transform: uppercase; margin-bottom: 0.35em; }
        .pay-row { margin-bottom: 0.25em; line-height: 1.4; }
        .pay-k { color: #666; }
        .iban-val { font-family: 'Courier New', Courier, monospace; font-size: 5pt; }

        .causale { border-top: 1px solid #ddd; padding-top: 0.5em; font-size: 5.5pt; color: #666; line-height: 1.5; }
        .causale p { margin-bottom: 0.3em; }

        .r { text-align: right; }
        .c { text-align: center; }
    </style>
</head>
<body>
@php
    $perPage  = 3;
    $copies   = 4;
    $cutH     = 5;      // mm
    $contentH = 281;    // mm  (A4 297mm minus 2×0.8cm margins)
    $copyH    = ($contentH - ($perPage - 1) * $cutH) / $perPage;

    $queue = [];
    foreach ($invoices as $invoice) {
        for ($i = 0; $i < $copies; $i++) {
            $queue[] = $invoice;
        }
    }
    $sheets = array_chunk($queue, $perPage);

    $sellerName    = core()->getConfigData('sales.shipping.origin.store_name') ?? '';
    $sellerAddress = core()->getConfigData('sales.shipping.origin.address') ?? '';
    $sellerZip     = core()->getConfigData('sales.shipping.origin.zipcode') ?? '';
    $sellerCity    = core()->getConfigData('sales.shipping.origin.city') ?? '';
    $sellerState   = core()->getConfigData('sales.shipping.origin.state') ?? '';
    $sellerCountry = core()->getConfigData('sales.shipping.origin.country') ?? '';
    $sellerBank    = core()->getConfigData('sales.shipping.origin.bank_details') ?? '';
@endphp

@foreach ($sheets as $sheet)
<table class="sheet">
    @foreach ($sheet as $j => $invoice)
        @if ($j > 0)
        <tr class="cut-row" style="height: {{ $cutH }}mm"><td>✂</td></tr>
        @endif
        <tr style="height: {{ number_format($copyH, 1) }}mm">
            <td class="copy-cell">
                @php
                    $currency  = $invoice->order->order_currency_code;
                    $invoiceNo = $invoice->increment_id ?? $invoice->id;
                    $date      = $invoice->created_at->format('j F Y');

                    $billingAddr  = $invoice->order->billing_address;
                    $buyerName    = $billingAddr->company_name ?: $billingAddr->name;
                    $buyerAddress = $billingAddr->address;
                    $buyerCity    = trim(($billingAddr->city ?? '') . ' ' . ($billingAddr->postcode ?? ''));
                    $buyerCountry = $billingAddr->country ?? '';

                    $paymentTitle = core()->getConfigData('sales.payment_methods.' . $invoice->order->payment->method . '.title')
                        ?: $invoice->order->payment->method;

                    // Build VAT summary grouped by tax rate
                    $vatRows = [];
                    foreach ($invoice->items as $item) {
                        $rate = (float) ($item->getTypeInstance()->getOrderedItem($item)->tax_percent ?? 0);
                        $key  = number_format($rate, 2);
                        if (!isset($vatRows[$key])) {
                            $vatRows[$key] = ['rate' => $rate, 'taxable' => 0, 'tax' => 0];
                        }
                        $vatRows[$key]['taxable'] += (float) $item->total;
                        $vatRows[$key]['tax']     += (float) $item->tax_amount;
                    }
                @endphp

                <div class="invoice">

                    {{-- Header --}}
                    <table class="l-hdr"><tr>
                        <td class="hdr-title">INVOICE</td>
                        <td class="hdr-meta">
                            <div class="hdr-num">{{ $invoiceNo }}</div>
                            <div class="hdr-date">{{ $date }}</div>
                            <div class="hdr-total">{!! core()->formatPrice($invoice->grand_total, $currency) !!}</div>
                        </td>
                    </tr></table>

                    {{-- Parties --}}
                    <table class="l-parties"><tr>
                        <td class="pcell">
                            <div class="party-lbl">Seller</div>
                            <div class="party-name">{{ $sellerName }}</div>
                            @if ($sellerAddress)
                                <div class="party-line">{{ $sellerAddress }}</div>
                            @endif
                            @php $cityLine = trim($sellerZip . ' ' . $sellerCity); if ($sellerState) $cityLine .= ' (' . $sellerState . ')'; @endphp
                            @if ($cityLine)
                                <div class="party-line">{{ $cityLine }}</div>
                            @endif
                            @if ($sellerCountry)
                                <div class="party-line">{{ $sellerCountry }}</div>
                            @endif
                        </td>
                        <td class="pcell">
                            <div class="party-lbl">Buyer</div>
                            <div class="party-name">{{ $buyerName }}</div>
                            @if ($buyerAddress)
                                <div class="party-line">{{ $buyerAddress }}</div>
                            @endif
                            @if ($buyerCity)
                                <div class="party-line">{{ $buyerCity }}</div>
                            @endif
                            @if ($buyerCountry)
                                <div class="party-line">{{ $buyerCountry }}</div>
                            @endif
                        </td>
                    </tr></table>

                    {{-- Line items --}}
                    <table class="items">
                        <thead><tr>
                            <th class="c" style="width:4%">#</th>
                            <th>Description</th>
                            <th class="r" style="width:8%">Qty</th>
                            <th class="r" style="width:13%">Unit price</th>
                            <th class="r" style="width:13%">Total</th>
                        </tr></thead>
                        <tbody>
                            @foreach ($invoice->items as $idx => $item)
                            <tr @if($idx % 2 !== 0) class="even" @endif>
                                <td class="c">{{ $idx + 1 }}</td>
                                <td>
                                    {{ $item->name }}
                                    @if (isset($item->additional['attributes']))
                                        <div style="font-size:5.5pt;color:#666;">
                                            @foreach ($item->additional['attributes'] as $attr)
                                                @if (!isset($attr['attribute_type']) || $attr['attribute_type'] !== 'file')
                                                    {{ $attr['attribute_name'] }}: {{ $attr['option_label'] }}@if (!$loop->last), @endif
                                                @endif
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="r">{{ $item->qty }}</td>
                                <td class="r">{!! core()->formatPrice($item->price, $currency) !!}</td>
                                <td class="r">{!! core()->formatPrice($item->total, $currency) !!}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    {{-- Bottom: VAT summary + payment --}}
                    <table class="l-bot"><tr>
                        <td class="vcell">
                            <table class="stbl">
                                <thead><tr>
                                    <th colspan="2">VAT Summary</th>
                                    <th class="r">Taxable</th>
                                    <th class="r">Tax</th>
                                </tr></thead>
                                <tbody>
                                    @foreach ($vatRows as $row)
                                    <tr>
                                        <td>IVA {{ number_format($row['rate'], 0) }}%</td>
                                        <td></td>
                                        <td class="r">{!! core()->formatPrice($row['taxable'], $currency) !!}</td>
                                        <td class="r">{!! core()->formatPrice($row['tax'], $currency) !!}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            <table class="total-tbl"><tr>
                                <td class="total-lbl">TOTAL {{ $currency }}</td>
                                <td class="total-val">{!! core()->formatPrice($invoice->grand_total, $currency) !!}</td>
                            </tr></table>
                        </td>
                        <td class="paycell">
                            <div class="sec-lbl">Payment</div>
                            <div class="pay-row"><span class="pay-k">Method:</span> {{ $paymentTitle }}</div>
                            @if ($sellerBank)
                                <div class="pay-row" style="margin-top:0.3em;">
                                    <span class="pay-k">Bank details:</span><br>
                                    <span class="iban-val">{{ $sellerBank }}</span>
                                </div>
                            @endif
                        </td>
                    </tr></table>

                </div>{{-- .invoice --}}
            </td>
        </tr>
    @endforeach
</table>
@endforeach
</body>
</html>
