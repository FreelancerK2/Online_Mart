<?php
include('config.php');
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);

if (!$payload || empty($payload['cart']) || !is_array($payload['cart'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Cart is empty or invalid.'
    ]);
    exit;
}

$billing = $payload['billing'] ?? [];
$shipping = $payload['shipping'] ?? null;
$payment = $payload['payment']['method'] ?? 'cash-on-delivery';
$totalsInput = $payload['totals'] ?? [];

$shippingCost = isset($totalsInput['shipping']) ? (float)$totalsInput['shipping'] : 0.0;
$taxAmount = isset($totalsInput['tax']) ? (float)$totalsInput['tax'] : 0.0;

$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

$cartItems = $payload['cart'];
$itemsData = [];
$subtotal = 0.0;

$productStmt = mysqli_prepare($conn, "SELECT name, price, discount_price, image_path FROM products WHERE id = ?");
if (!$productStmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Unable to prepare product query.']);
    exit;
}

foreach ($cartItems as $item) {
    $productId = isset($item['id']) ? (int)$item['id'] : 0;
    $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 1;

    if ($productId <= 0 || $quantity <= 0) {
        mysqli_stmt_close($productStmt);
        echo json_encode(['success' => false, 'message' => 'Invalid product in cart.']);
        exit;
    }

    mysqli_stmt_bind_param($productStmt, 'i', $productId);
    mysqli_stmt_execute($productStmt);
    $result = mysqli_stmt_get_result($productStmt);
    $productRow = mysqli_fetch_assoc($result);

    if (!$productRow) {
        mysqli_stmt_close($productStmt);
        echo json_encode(['success' => false, 'message' => 'One of the products in the cart no longer exists.']);
        exit;
    }

    $unitPrice = ($productRow['discount_price'] && $productRow['discount_price'] > 0)
        ? (float)$productRow['discount_price']
        : (float)$productRow['price'];

    $lineTotal = $unitPrice * $quantity;
    $subtotal += $lineTotal;

$itemsData[] = [
    'product_id' => $productId,
    'name' => $productRow['name'],
    'quantity' => $quantity,
    'unit_price' => $unitPrice,
    'line_total' => $lineTotal,
    'image' => $productRow['image_path'] ?? null
];
}

mysqli_stmt_close($productStmt);

if (empty($itemsData)) {
    echo json_encode(['success' => false, 'message' => 'No valid items to place order.']);
    exit;
}

$grandTotal = $subtotal + $shippingCost + $taxAmount;
$orderNumber = 'ORD-' . strtoupper(dechex(time())) . '-' . mt_rand(100, 999);

$status = 'pending';
$paymentStatus = in_array($payment, ['credit-card', 'paypal'], true) ? 'paid' : 'unpaid';
$shippingAddress = $shipping ? json_encode($shipping, JSON_UNESCAPED_UNICODE) : json_encode($billing, JSON_UNESCAPED_UNICODE);
$billingAddress = json_encode($billing, JSON_UNESCAPED_UNICODE);

mysqli_begin_transaction($conn);

try {
    if ($userId) {
        $orderStmt = mysqli_prepare($conn, "INSERT INTO orders (order_number, user_id, total_amount, status, payment_status, shipping_address, billing_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if (!$orderStmt) {
            throw new Exception('Unable to prepare order insert statement.');
        }
        mysqli_stmt_bind_param($orderStmt, 'sidssss', $orderNumber, $userId, $grandTotal, $status, $paymentStatus, $shippingAddress, $billingAddress);
    } else {
        $orderStmt = mysqli_prepare($conn, "INSERT INTO orders (order_number, total_amount, status, payment_status, shipping_address, billing_address) VALUES (?, ?, ?, ?, ?, ?)");
        if (!$orderStmt) {
            throw new Exception('Unable to prepare order insert statement.');
        }
        mysqli_stmt_bind_param($orderStmt, 'sdssss', $orderNumber, $grandTotal, $status, $paymentStatus, $shippingAddress, $billingAddress);
    }

    if (!mysqli_stmt_execute($orderStmt)) {
        throw new Exception('Failed to save order.');
    }

    $orderId = mysqli_insert_id($conn);
    mysqli_stmt_close($orderStmt);

    $itemStmt = mysqli_prepare($conn, "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    if (!$itemStmt) {
        throw new Exception('Unable to prepare order item insert statement.');
    }

    foreach ($itemsData as $item) {
        mysqli_stmt_bind_param($itemStmt, 'iiid', $orderId, $item['product_id'], $item['quantity'], $item['unit_price']);
        if (!mysqli_stmt_execute($itemStmt)) {
            throw new Exception('Failed to save order items.');
        }
    }

    mysqli_stmt_close($itemStmt);

    mysqli_commit($conn);

    echo json_encode([
        'success' => true,
        'order_id' => $orderId,
        'order_number' => $orderNumber,
        'order_date' => date('c'),
        'status' => $status,
        'payment_status' => $paymentStatus,
        'totals' => [
            'subtotal' => round($subtotal, 2),
            'shipping' => round($shippingCost, 2),
            'tax' => round($taxAmount, 2),
            'total' => round($grandTotal, 2)
        ],
        'items' => array_map(function ($item) {
            return [
                'product_id' => $item['product_id'],
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'line_total' => $item['line_total'],
                'image' => $item['image']
            ];
        }, $itemsData)
    ]);
} catch (Exception $e) {
    mysqli_rollback($conn);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}

