<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Your Invoice</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            padding: 20px;
            background-color: #f8f8f8;
        }

        .container {
            background-color: #fff;
            padding: 25px;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            max-width: 600px;
            margin: auto;
        }

        h2 {
            color: #0b5ed7;
        }

        .info {
            margin-bottom: 20px;
        }

        .info strong {
            display: inline-block;
            width: 120px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 10px;
        }

        th {
            background-color: #f1f1f1;
            text-align: left;
        }

        .total {
            text-align: right;
            font-size: 16px;
        }

        .footer {
            font-size: 13px;
            color: #777;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>Invoice</h2>

        <div class="info">
            <p><strong>Order ID:</strong> #{{ $order->id }}</p>
            <p><strong>Customer:</strong> {{ $order->user->name }} ({{ $order->user->email }})</p>
            <p><strong>Date:</strong> {{ $order->created_at->format('F j, Y') }}</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item</th>
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
                    <td>$20.00</td>
                </tr>
            </tbody>
        </table>

        <p class="total"><strong>Total:</strong> ${{ number_format($total + 20, 2) }}</p>

        <p class="footer">Thank you for your order! If you have any questions, contact us anytime.</p>
    </div>
</body>

</html>
