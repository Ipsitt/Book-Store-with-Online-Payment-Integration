<?php
session_start();

$secret_key = getenv('KHALTI_SECRET_KEY');
if (!$secret_key) {
    http_response_code(500);
    exit('Khalti secret key is not configured.');
}

$amount = $_POST['amount'] ?? 0;
$buyer_id = $_POST['buyer_id'] ?? 1;
$order_id = time();
$_SESSION['order_id'] = $order_id;
$_SESSION['buyer_id'] = $buyer_id;

// Decode and store cart_items
$cart_items_raw = $_POST['cart_items'] ?? '[]';
$cart_items_array = json_decode($cart_items_raw, true);
$_SESSION['cart_items'] = $cart_items_array;

$data = [
    "return_url" => "http://localhost/MIS/khalti_return.php",
    "website_url" => "http://localhost/MIS/cart.php",
    "amount" => intval($amount),
    "purchase_order_id" => $order_id,
    "purchase_order_name" => "Book Cart Payment",
    "customer_info" => [
        "name" => "User $buyer_id",
        "email" => "example@email.com",
        "phone" => "9800000001"
    ]
];

$curl = curl_init('https://dev.khalti.com/api/v2/epayment/initiate/');
curl_setopt_array($curl, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        "Authorization: Key $secret_key",
        "Content-Type: application/json"
    ]
]);

$response = curl_exec($curl);
curl_close($curl);

$res = json_decode($response, true);

if (isset($res['payment_url'])) {
    header("Location: " . $res['payment_url']);
    exit;
} else {
    echo "<h3>❌ Failed to initiate payment.</h3>";
    echo "<pre>"; print_r($res); echo "</pre>";
}
?>
