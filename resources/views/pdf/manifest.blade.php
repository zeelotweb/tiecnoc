 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manifest {{ $order->order_number }}</title>
    <style>
        /* TNC / PDF LOGISTICS AESTHETIC */
        body { 
            font-family: 'Helvetica', sans-serif; 
            text-transform: uppercase; 
            font-size: 10px; 
            color: #000;
            margin: 0;
            padding: 40px;
        }
        .header { 
            border-bottom: 2px solid #000; 
            padding-bottom: 20px; 
            margin-bottom: 30px; 
        }
        .header h1 { 
            font-size: 24px; 
            margin: 0; 
            font-style: italic;
            letter-spacing: -1px;
        }
        .meta-grid { 
            margin-bottom: 40px; 
        }
        .meta-item { 
            display: inline-block; 
            width: 30%; 
        }
        .label { 
            color: #888; 
            font-size: 7px; 
            display: block; 
            margin-bottom: 4px;
        }
        .item-table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        .item-row { 
            border-bottom: 1px solid #eee; 
        }
        .item-cell { 
            padding: 15px 0; 
            vertical-align: top;
        }
        .total-section { 
            margin-top: 50px; 
            text-align: right; 
            border-top: 2px solid #000; 
            padding-top: 20px; 
        }
        .total-amount { 
            font-size: 20px; 
            font-weight: bold; 
            font-style: italic;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>Digital Manifest</h1>
        <p style="opacity: 0.5;">Verified Platform Authority // Native Record</p>
    </div>

    <div class="meta-grid">
        <div class="meta-item">
            <span class="label">Identity ID</span>
            <strong>{{ $order->order_number }}</strong>
        </div>
        <div class="meta-item">
            <span class="label">Timestamp</span>
            <strong>{{ $order->created_at->format('Y.m.d / H:i:s') }}</strong>
        </div>
        <div class="meta-item">
            <span class="label">Status</span>
            <strong>{{ strtoupper($order->status) }}</strong>
        </div>
    </div>

    <table class="item-table">
        <thead>
            <tr style="text-align: left; border-bottom: 1px solid #000;">
                <th class="label" style="padding-bottom: 10px;">Item Description</th>
                <th class="label" style="padding-bottom: 10px; text-align: right;">Valuation</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
                <tr class="item-row">
                    <td class="item-cell">
                        <div style="font-weight: bold; margin-bottom: 4px;">{{ $item->name }}</div>
                        <div style="opacity: 0.6; font-size: 8px;">SKU: {{ $item->sku }} // ATTR: {{ $item->attr }} // QTY: {{ $item->qty }}</div>
                    </td>
                    <td class="item-cell" style="text-align: right; font-weight: bold;">
                        ${{ number_format($item->price, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-section">
        <span class="label">Aggregate Total</span>
        <div class="total-amount">${{ number_format($order->total_amount, 2) }}</div>
    </div>

    <div style="margin-top: 100px; opacity: 0.3; font-size: 7px; letter-spacing: 2px;">
        Official Digital Manifest // End of Record
    </div>

</body>
</html>
