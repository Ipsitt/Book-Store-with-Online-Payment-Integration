<?php
session_start();
include 'db.php';

if (!isset($_SESSION['seller_email'])) {
    header("Location: login.php");
    exit();
}

$seller_email = $_SESSION['seller_email'];
$stmt = $conn->prepare("SELECT seller_id FROM sellers WHERE email = ?");
$stmt->bind_param("s", $seller_email);
$stmt->execute();
$seller_result = $stmt->get_result();
$seller = $seller_result->fetch_assoc();
$seller_id = $seller['seller_id'];

$query = "
    SELECT oi.book_id, oi.price, oi.status, b.title, b.image
    FROM order_items oi
    JOIN books b ON oi.book_id = b.book_id
    WHERE oi.seller_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();

$sales = [];
$total_received = 0;
$total_pending = 0;

while ($row = $result->fetch_assoc()) {
    $sales[] = $row;
    if ($row['status'] === "Payment Received") {
        $total_received += $row['price'];
    } else {
        $total_pending += $row['price'];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Earnings</title>
    <style>
        body {
            background-color: #111;
            color: white;
            font-family: Arial;
            padding: 20px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        .sale-card {
            display: flex;
            align-items: center;
            background-color: #1e1e1e;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(255,255,255,0.05);
        }

        .sale-card img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 6px;
            margin-right: 20px;
        }

        .details {
            flex-grow: 1;
        }

        .details h3 {
            margin: 0 0 8px 0;
        }

        .details p {
            margin: 3px 0;
        }

        .status {
            font-weight: bold;
            color: #ffc107;
        }

        .status.received {
            color: #00cc66;
        }

        .summary {
            margin-top: 30px;
            font-size: 18px;
            font-weight: bold;
        }

        .summary p {
            margin: 10px 0;
        }

        .back-link {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 15px;
            background-color: #444;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>

<h1>📊 My Transactions</h1>

<?php if (empty($sales)): ?>
    <p style="text-align:center;">No sales yet.</p>
<?php else: ?>
    <?php foreach ($sales as $sale): ?>
        <div class="sale-card">
            <img src="book_images/<?php echo htmlspecialchars($sale['image']); ?>" alt="Book">
            <div class="details">
                <h3><?php echo htmlspecialchars($sale['title']); ?></h3>
                <p>Price: Rs. <?php echo number_format($sale['price'], 2); ?></p>
            </div>
            <div class="status <?php echo $sale['status'] === 'Payment Received' ? 'received' : ''; ?>">
                <?php echo $sale['status'] === 'Payment Received' ? '✅ Payment Received' : '⌛ Payment Pending'; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="summary">
        <p>Total Received: Rs. <?php echo number_format($total_received, 2); ?></p>
        <p>Total Left to Receive: Rs. <?php echo number_format($total_pending, 2); ?></p>
    </div>
<?php endif; ?>

<a class="back-link" href="seller_page.php">← Back to Seller Dashboard</a>

</body>
</html>
