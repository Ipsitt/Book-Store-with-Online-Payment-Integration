<?php
include 'db.php';
session_start();

$secret_key = getenv('KHALTI_SECRET_KEY');
if (!$secret_key) {
    http_response_code(500);
    exit('Khalti secret key is not configured.');
}

$pidx = $_GET['pidx'] ?? '';
$buyer_id = $_SESSION['buyer_id'] ?? 1;

if (!$pidx) {
    header("Location: cart.php?error=missing_pidx");
    exit;
}

// STEP 1: Lookup Khalti payment
$lookupUrl = "https://dev.khalti.com/api/v2/epayment/lookup/";
$payload = json_encode(["pidx" => $pidx]);

$curl = curl_init($lookupUrl);
curl_setopt($curl, CURLOPT_HTTPHEADER, [
    "Authorization: Key $secret_key",
    "Content-Type: application/json"
]);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $payload);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($curl);
curl_close($curl);

$data = json_decode($response, true);

// STEP 2: If payment completed, proceed
if (isset($data['status']) && $data['status'] === 'Completed') {
    $transaction_id = $data['transaction_id'];
    $order_id = time(); // unique ID for this purchase batch
    $payment_method = 'wallet';
    $payment_status = 'successful';

    // Get cart items from session
    $cart_items = $_SESSION['cart_items'] ?? [];

    if (!is_array($cart_items) || count($cart_items) === 0) {
        header("Location: cart.php?error=empty_cart");
        exit;
    }

    // Calculate total price
    $total_price = 0;
    foreach ($cart_items as $item) {
        $total_price += floatval($item['price']) * intval($item['quantity']);
    }

    // STEP 3: Insert into transactions (no orders table)
    $stmt = $conn->prepare("INSERT INTO transactions (transaction_id, order_id, buyer_id, payment_method, payment_status) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("siiss", $transaction_id, $order_id, $buyer_id, $payment_method, $payment_status);
    $stmt->execute();

    // STEP 4: Insert order items
    foreach ($cart_items as $item) {
        $book_id = $item['book_id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        $seller_id = $item['seller_id'];
        $status = 'Being Delivered';

        $stmt2 = $conn->prepare("
            INSERT INTO order_items (order_id, book_id, quantity, price, buyer_id, seller_id, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt2->bind_param("iiidiss", $order_id, $book_id, $quantity, $price, $buyer_id, $seller_id, $status);
        $stmt2->execute();
    }

    // STEP 5: Clear cart
    $clear = $conn->prepare("DELETE FROM cart_items WHERE buyer_id = ?");
    $clear->bind_param("i", $buyer_id);
    $clear->execute();

    // Redirect to cart with success
    header("Location: cart.php?success=1&order_id=$order_id");
    exit;

} else {
    // Redirect to cart with failure
    header("Location: cart.php?error=payment_failed");
    exit;
}
?>
