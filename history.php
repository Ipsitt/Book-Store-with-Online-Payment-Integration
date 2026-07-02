<?php
// history.php
include 'db.php';
session_start();

$buyer_id = $_SESSION['buyer_id'] ?? 1;

// Fetch order history
$stmt = $conn->prepare("
    SELECT oi.quantity, oi.price, oi.status, b.title, b.image 
    FROM order_items oi
    JOIN books b ON oi.book_id = b.book_id
    WHERE oi.buyer_id = ?
    ORDER BY oi.order_item_id DESC
");
$stmt->bind_param("s", $buyer_id); // buyer_id is varchar
$stmt->execute();
$result = $stmt->get_result();

$history_items = [];
while ($row = $result->fetch_assoc()) {
    $history_items[] = $row;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Purchase History</title>
    <style>
        body {
            background-color: #111;
            color: white;
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        .history-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            background-color: #1e1e1e;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .history-item img {
            width: 100px;
            border-radius: 6px;
        }
        .item-details {
            flex-grow: 1;
            margin-left: 15px;
        }
        .status {
            font-weight: bold;
            padding: 8px 12px;
            border-radius: 6px;
            background-color: #333;
            min-width: 140px;
            text-align: center;
        }
        .status.Delivered {
    background-color: #00cc66; /* green for delivered */
}

.status.Being-Delivered {
    background-color: #9b8322ff; /* yellow */
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

<h1>Purchase History</h1>

<?php if (empty($history_items)): ?>
    <p>No purchases yet.</p>
<?php else: ?>
    <?php foreach ($history_items as $item): ?>
        <div class="history-item">
            <img src="book_images/<?php echo htmlspecialchars($item['image']); ?>" alt="Book">
            <div class="item-details">
                <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                <p>Quantity: <?php echo $item['quantity']; ?></p>
                <p>Price: Rs. <?php echo number_format($item['price'], 2); ?></p>
            </div>
            <div class="status <?php echo $item['status'] === 'Payment Received' ? 'Delivered' : 'Being-Delivered'; ?>">
    <?php echo $item['status'] === 'Payment Received' ? 'Delivered' : 'Being Delivered'; ?>
</div>

        </div>
    <?php endforeach; ?>
<?php endif; ?>

<a class="back-link" href="index.php">← Back to Book List</a>

</body>
</html>
