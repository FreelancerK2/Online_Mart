<?php
include('config.php');
session_start();

if (!isset($_SESSION['admin'])) {
    header('Location: login.php');
    exit;
}

$orderId = isset($_GET['order_id']) ? (int) $_GET['order_id'] : 0;
if ($orderId <= 0) {
    exit('Invalid order.');
}

$orderQuery = mysqli_prepare($conn, "SELECT o.*, u.username, u.email FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE o.id = ?");
if (!$orderQuery) {
    exit('Unable to prepare order query.');
}

mysqli_stmt_bind_param($orderQuery, 'i', $orderId);
mysqli_stmt_execute($orderQuery);
$orderResult = mysqli_stmt_get_result($orderQuery);
$order = mysqli_fetch_assoc($orderResult);
mysqli_stmt_close($orderQuery);

if (!$order) {
    exit('Order not found.');
}

$items = [];
$itemsQuery = mysqli_prepare($conn, "SELECT oi.*, p.name, p.image_path FROM order_items oi LEFT JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
if ($itemsQuery) {
    mysqli_stmt_bind_param($itemsQuery, 'i', $orderId);
    mysqli_stmt_execute($itemsQuery);
    $itemsResult = mysqli_stmt_get_result($itemsQuery);
    if ($itemsResult) {
        while ($row = mysqli_fetch_assoc($itemsResult)) {
            $items[] = $row;
        }
        mysqli_free_result($itemsResult);
    }
    mysqli_stmt_close($itemsQuery);
}

$shippingAddress = json_decode($order['shipping_address'] ?? '', true);
$billingAddress = json_decode($order['billing_address'] ?? '', true);

function formatAddress($address) {
    if (empty($address) || !is_array($address)) {
        return 'N/A';
    }

    $parts = [];
    if (!empty($address['fullName'])) {
        $parts[] = $address['fullName'];
    }
    if (!empty($address['street'])) {
        $parts[] = $address['street'];
    }
    if (!empty($address['city'])) {
        $line = $address['city'];
        if (!empty($address['state'])) {
            $line .= ', ' . $address['state'];
        }
        if (!empty($address['postalCode'])) {
            $line .= ' ' . $address['postalCode'];
        }
        $parts[] = $line;
    }
    if (!empty($address['country'])) {
        $parts[] = $address['country'];
    }
    if (!empty($address['phone'])) {
        $parts[] = 'Phone: ' . $address['phone'];
    }

    return !empty($parts) ? implode('<br>', array_map('htmlspecialchars', $parts)) : 'N/A';
}

?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - <?php echo htmlspecialchars($order['order_number']); ?></title>
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            background-color: #0f172a;
            color: #e2e8f0;
            margin: 0;
            padding: 40px;
        }
        .invoice {
            max-width: 900px;
            margin: 0 auto;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            padding: 40px 48px;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.4);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }
        .header h1 {
            font-size: 28px;
            margin: 0;
            color: #ffffff;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 9999px;
            background: rgba(16, 185, 129, 0.1);
            color: #34d399;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 24px;
            margin-bottom: 36px;
        }
        .meta-card {
            background: rgba(51, 65, 85, 0.35);
            border: 1px solid rgba(148, 163, 184, 0.15);
            border-radius: 14px;
            padding: 18px;
        }
        .meta-card h3 {
            margin: 0 0 12px;
            font-size: 12px;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #94a3b8;
        }
        .meta-card p {
            margin: 0;
            font-size: 15px;
            line-height: 1.6;
            color: #e2e8f0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 32px;
            overflow: hidden;
            border-radius: 14px;
        }
        thead {
            background: rgba(51, 65, 85, 0.4);
        }
        th {
            text-align: left;
            padding: 14px 16px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
        }
        td {
            padding: 18px 16px;
            border-bottom: 1px solid rgba(148, 163, 184, 0.1);
            vertical-align: middle;
        }
        tbody tr:hover {
            background: rgba(51, 65, 85, 0.25);
        }
        .product {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .product img {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            object-fit: cover;
            border: 1px solid rgba(148, 163, 184, 0.2);
        }
        .totals {
            display: flex;
            justify-content: flex-end;
        }
        .totals table {
            width: 320px;
            border-radius: 12px;
            border: 1px solid rgba(148, 163, 184, 0.1);
        }
        .totals td {
            border-bottom: none;
        }
        .totals tr:last-child td {
            font-size: 18px;
            font-weight: 600;
            color: #ffffff;
        }
        .footer {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #94a3b8;
            font-size: 13px;
        }
        .actions {
            text-align: right;
            margin-top: 24px;
        }
        .actions button {
            background: #22c55e;
            border: none;
            color: white;
            padding: 10px 18px;
            border-radius: 999px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(34, 197, 94, 0.25);
        }
        .actions button:hover {
            background: #16a34a;
        }
        @media print {
            body {
                background: white;
                color: #0f172a;
                padding: 0;
            }
            .invoice {
                box-shadow: none;
                border: none;
                background: white;
                color: #0f172a;
            }
            .badge {
                background: rgba(16, 185, 129, 0.15);
                color: #047857;
            }
            .meta-card {
                background: rgba(148, 163, 184, 0.1);
                border-color: rgba(148, 163, 184, 0.3);
            }
            .actions {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice">
        <div class="header">
            <div>
                <h1>Invoice</h1>
                <p style="margin: 6px 0 0; color: #94a3b8;">Order #<?php echo htmlspecialchars($order['order_number']); ?></p>
            </div>
            <span class="badge">Status: <?php echo strtoupper(htmlspecialchars($order['status'])); ?></span>
        </div>

        <div class="meta">
            <div class="meta-card">
                <h3>Billed To</h3>
                <p><?php echo formatAddress($billingAddress); ?></p>
            </div>
            <div class="meta-card">
                <h3>Shipped To</h3>
                <p><?php echo formatAddress($shippingAddress); ?></p>
            </div>
            <div class="meta-card">
                <h3>Order Date</h3>
                <p><?php echo date('M d, Y \a\t h:i A', strtotime($order['created_at'])); ?></p>
            </div>
            <div class="meta-card">
                <h3>Payment Method</h3>
                <p><?php echo htmlspecialchars($order['payment_method'] ?? 'N/A'); ?><br>Payment Status: <?php echo ucfirst(htmlspecialchars($order['payment_status'])); ?></p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td>
                            <div class="product">
                                <?php if (!empty($item['image_path'])): ?>
                                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['name'] ?? 'Product'); ?>" onerror="this.style.display='none'">
                                <?php endif; ?>
                                <div>
                                    <strong><?php echo htmlspecialchars($item['name'] ?? 'Product'); ?></strong><br>
                                    <span style="color:#94a3b8; font-size: 13px;">SKU: #<?php echo $item['product_id']; ?></span>
                                </div>
                            </div>
                        </td>
                        <td><?php echo (int) $item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td>$<?php echo number_format($item['quantity'] * $item['price'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tbody>
                    <tr>
                        <td>Subtotal</td>
                        <td style="text-align:right;">$<?php echo number_format($order['total_amount'] - ($order['tax_amount'] ?? 0) - ($order['shipping_amount'] ?? 0), 2); ?></td>
                    </tr>
                    <tr>
                        <td>Shipping</td>
                        <td style="text-align:right;">$<?php echo number_format($order['shipping_amount'] ?? 0, 2); ?></td>
                    </tr>
                    <tr>
                        <td>Tax</td>
                        <td style="text-align:right;">$<?php echo number_format($order['tax_amount'] ?? 0, 2); ?></td>
                    </tr>
                    <tr>
                        <td>Total Due</td>
                        <td style="text-align:right;">$<?php echo number_format($order['total_amount'], 2); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="actions">
            <button onclick="window.print()">Print / Save as PDF</button>
        </div>

        <div class="footer">
            <span>Thank you for shopping with Mini Mart.</span>
            <span>Generated on <?php echo date('M d, Y h:i A'); ?></span>
        </div>
    </div>
</body>
</html>

