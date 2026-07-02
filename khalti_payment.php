<?php

$secret_key = getenv('KHALTI_SECRET_KEY');
if (!$secret_key) {
    http_response_code(500);
    exit('Khalti secret key is not configured.');
}

$data = [
    "return_url" => "http://localhost/MIS/verify_payment.php",
    "website_url" => "http://localhost",
    "amount" => 1500, // in paisa 
    "purchase_order_id" => uniqid("ORDER_"),
    "purchase_order_name" => "Used Book",
    "customer_info" => [
        "name" => "Test User",
        "email" => "ipsit@example.com",
        "phone" => "9800000001"  // Test phone number
    ]
];

$ch = curl_init("https://dev.khalti.com/api/v2/epayment/initiate/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Key $secret_key",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if (isset($result["payment_url"])) {
    header("Location: " . $result["payment_url"]);
    exit();
} else {
    echo "<pre>";
    print_r($result);
    echo "</pre>";
}
?>
