<?php
include 'db.php';
session_start();

$secret_key = getenv('KHALTI_SECRET_KEY');
if (!$secret_key) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Khalti secret key is not configured']);
    exit;
}
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit;
}

parse_str(file_get_contents("php://input"), $post_vars);
$token = $post_vars['token'] ?? '';
$amount = intval($post_vars['amount'] ?? 0);
$buyer_id = $_SESSION['buyer_id'] ?? 1;

if (empty($token) || $amount <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Missing token or amount']);
    exit;
}

// Lookup payment
$url = 'https://dev.khalti.com/api/v2/epayment/lookup/';
$payload = json_encode(['pidx' => $token]);

$curl = curl_init($url);
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    'Authorization: Key ' . $secret_key,
    'Content-Type: application/json'
]);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response, true);

if (!$data || !isset($data['status'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Khalti response']);
    exit;
}

if ($data['status'] === 'Completed' && $data['total_amount'] == $amount) {
    $transaction_id = $data['transaction_id'];
    $payment_method = 'wallet';
    $payment_status = 'successful';
    $order_id = $_SESSION['order_id'] ?? time();

    $cart_items = json_decode($_SESSION['cart_items'] ?? '[]', true);

    if (!is_array($cart_items) || count($cart_items) === 0) {
        echo json_encode(['status' => 'error', 'message' => 'Cart items empty or malformed']);
        exit;
    }

    // Insert transaction
    $stmt = $conn->prepare("INSERT INTO transactions (transaction_id, order_id, buyer_id, payment_method, payment_status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("siiss", $transaction_id, $order_id, $buyer_id, $payment_method, $payment_status);
    $stmt->execute();

    // Insert order_items
    foreach ($cart_items as $item) {
        $book_id = $item['book_id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        $seller_id = $item['seller_id'];
        $status = 'Being Delivered';

        $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, book_id, quantity, price, buyer_id, seller_id, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("iiidiss", $order_id, $book_id, $quantity, $price, $buyer_id, $seller_id, $status);
        if (!$stmt2->execute()) {
            error_log("Order item insert failed: " . $stmt2->error);
        }
    }

    // Clear cart
    $clear = $conn->prepare("DELETE FROM cart_items WHERE buyer_id = ?");
    $clear->bind_param("i", $buyer_id);
    $clear->execute();

    echo json_encode(['status' => 'success', 'message' => 'Payment verified and order recorded']);
} else {
    echo json_encode(['status' => 'failed', 'message' => 'Payment verification failed', 'data' => $data]);
}
?>
