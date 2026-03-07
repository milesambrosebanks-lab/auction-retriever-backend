<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $order->id }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #333;
            font-size: 14px;
            margin: 0;
            padding: 0;
        }
        .invoice-box {
            padding: 30px;
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #0b5ed7;
        }
        .info {
            margin-bottom: 20px;
        }
        .info p {
            margin: 0;
            line-height: 1.5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .total-row td {
            border-top: 2px solid #000;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 40px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
<div class="invoice-box">
    <h2>Invoice</h2>

    <div class="info">
        <p><strong>Invoice ID:</strong> #{{ $order->id }}</p>
        <p><strong>Customer:</strong> {{ $order->user->name }} ({{ $order->user->email }})</p>
        <p><strong>Date:</strong> {{ $order->created_at->format('F j, Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Description</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
        @php $total = 0; @endphp
        @foreach($order->items as $index => $item)
            @php
                $subtotal = $item->price * $item->quantity;
                $total += $subtotal;
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>{{ number_format($item->price, 2) }}</td>
                <td>{{ number_format($subtotal, 2) }}</td>
            </tr>
        @endforeach
        <tr>
            <td colspan="4" align="right">Shipping</td>
            <td>{{ number_format(20, 2) }}</td>
        </tr>
        <tr class="total-row">
            <td colspan="4" align="right">Total</td>
            <td>{{ number_format($total + 20, 2) }}</td>
        </tr>
        </tbody>
    </table>

    <p class="footer">This is a computer-generated invoice. No signature is required.</p>
</div>
</body>
</html>

