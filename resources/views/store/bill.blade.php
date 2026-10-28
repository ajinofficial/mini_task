<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bill #{{ str_pad((string)$order->id, 5, '0', STR_PAD_LEFT) }} — MiniStore</title>
    <style>
        *{box-sizing:border-box}body{margin:0;background:#f6f7f3;color:#202821;font:14px Arial,sans-serif;line-height:1.5}.toolbar{max-width:900px;margin:24px auto;display:flex;gap:12px;justify-content:space-between;padding:0 20px}.button{background:#315b43;color:#fff;border:0;border-radius:8px;padding:11px 18px;font:inherit;cursor:pointer;text-decoration:none}.back{background:white;color:#315b43;border:1px solid #e8ebe5}.bill{max-width:860px;margin:24px auto 48px;padding:40px;background:white;border:1px solid #e8ebe5;border-radius:14px}.heading,.details{display:flex;justify-content:space-between;gap:24px;margin-bottom:30px}h1,h2,p{margin:0}h1{font-size:25px;color:#315b43}h2{font-size:20px}.muted{color:#737b71}.details strong{display:block;margin-bottom:6px}.customer{overflow-wrap:anywhere}.table-wrap{overflow:auto}table{width:100%;border-collapse:collapse;margin-bottom:24px}th,td{text-align:left;padding:12px 8px;border-bottom:1px solid #e8ebe5}th{font-size:11px;color:#737b71;background:#f7f8f5}.number{text-align:right;white-space:nowrap}.totals{margin-left:auto;max-width:340px}.row{display:flex;justify-content:space-between;gap:20px;padding:7px 0}.total{border-top:1px solid #e8ebe5;margin-top:8px;font-size:18px;font-weight:bold}.status{display:inline-block;background:#edf4ee;color:#315b43;padding:5px 10px;border-radius:20px;font-size:12px;margin-top:10px}.footer{border-top:1px solid #e8ebe5;margin-top:28px;padding-top:20px;text-align:center;font-size:12px}@media(max-width:600px){.bill{padding:20px;margin:16px}.heading,.details{flex-direction:column;gap:16px}.totals{max-width:none}}@page{size:A4;margin:15mm}@media print{body{background:white;font-size:11px}.toolbar{display:none}.bill{max-width:none;margin:0;padding:0;border:0;border-radius:0}.table-wrap{overflow:visible}thead{display:table-header-group}tr{break-inside:avoid}.totals,.footer,.details{break-inside:avoid}th,td{padding:8px 5px}.heading,.details{flex-direction:row}.totals{max-width:340px}a{color:inherit;text-decoration:none}}
    </style>
</head>
<body>
    <div class="toolbar"><a class="button back" href="{{ route('orders.index') }}">Back to orders</a><button class="button" type="button" onclick="window.print()">Print bill / Save PDF</button></div>
    <main class="bill">
        <header class="heading"><div><h1>MiniStore</h1><p class="muted">Retail workspace · Order bill</p></div><div><h2>Bill #{{ str_pad((string)$order->id, 5, '0', STR_PAD_LEFT) }}</h2><p class="muted">{{ $order->created_at->format('d M Y, h:i A') }}</p></div></header>
        <div class="details"><div class="customer"><strong>Billed to</strong><p>{{ $order->customer->name }}</p><p class="muted">{{ $order->customer->email }}</p></div><div><strong>Payment status</strong><span class="status">{{ $order->balance_due > 0 ? ((float)$order->paid_amount > 0 ? 'Partially paid' : 'Unpaid') : 'Completed' }}</span><p class="muted">Currency: INR (₹)</p></div></div>
        <div class="table-wrap"><table><thead><tr><th>Product</th><th class="number">Qty</th><th class="number">Unit price</th><th class="number">Subtotal</th><th class="number">Tax</th><th class="number">Total</th></tr></thead><tbody>
            @foreach($order->items as $item)
                <tr><td>{{ $item->product->name }}<br><small class="muted">{{ $item->product->code }}</small></td><td class="number">{{ $item->quantity }}</td><td class="number">₹{{ number_format((float)$item->unit_price, 2) }}</td><td class="number">₹{{ number_format((float)$item->subtotal, 2) }}</td><td class="number">₹{{ number_format((float)$item->tax, 2) }}<br><small class="muted">{{ number_format((float)$item->tax_percentage, 2) }}%</small></td><td class="number">₹{{ number_format((float)$item->total, 2) }}</td></tr>
            @endforeach
        </tbody></table></div>
        <div class="totals">
            <div class="row"><span>Subtotal</span><strong>₹{{ number_format((float)$order->subtotal, 2) }}</strong></div>
            <div class="row"><span>Tax</span><strong>₹{{ number_format((float)$order->tax, 2) }}</strong></div>
            <div class="row total"><span>Grand total</span><span>₹{{ number_format((float)$order->total, 2) }}</span></div>
            <div class="row"><span>Customer paid amount</span><strong>₹{{ number_format((float)$order->paid_amount, 2) }}</strong></div>
            <div class="row"><span>Balance due</span><strong>₹{{ number_format($order->balance_due, 2) }}</strong></div>
            <div class="row"><span>Balance to return</span><strong>₹{{ number_format($order->change_due, 2) }}</strong></div>
        </div>
        <footer class="footer muted">Thank you for shopping with MiniStore.</footer>
    </main>
</body>
</html>
