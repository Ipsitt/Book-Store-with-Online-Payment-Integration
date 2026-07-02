<?php
include 'db.php';
session_start();

$buyer_id = $_SESSION['buyer_id'] ?? 1;

// Delete cart item
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ? AND buyer_id = ?");
    $stmt->bind_param("ii", $delete_id, $buyer_id);
    $stmt->execute();
    header("Location: cart.php");
    exit;
}

// Fetch cart items
$stmt = $conn->prepare("
    SELECT ci.id, ci.book_id, ci.quantity, b.title, b.price, b.image, b.seller_id
    FROM cart_items ci 
    JOIN books b ON ci.book_id = b.book_id 
    WHERE ci.buyer_id = ?
");
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();

$cart_items = [];
$total = 0;

while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
    $total += $row['price'] * $row['quantity'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Cart</title>
    <style>
        body {
            background-color: #111;
            color: white;
            font-family: Arial;
            padding: 20px;
        }
        .cart-item {
            display: flex;
            align-items: center;
            gap: 15px;
            background-color: #1e1e1e;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .cart-item img {
            width: 100px;
            border-radius: 6px;
        }
        .item-details {
            flex-grow: 1;
        }
        .delete-btn {
            background: red;
            color: white;
            border: none;
            padding: 6px 10px;
            cursor: pointer;
            border-radius: 4px;
        }
        .total {
            font-size: 20px;
            margin-top: 20px;
            font-weight: bold;
        }
        .proceed-btn {
            background-color: #00cc66;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            margin-top: 10px;
            cursor: pointer;
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

<h1>Your Cart</h1>

<?php if (empty($cart_items)): ?>
    <p>No items in your cart.</p>
<?php else: ?>
    <?php foreach ($cart_items as $item): ?>
        <div class="cart-item">
            <img src="book_images/<?php echo htmlspecialchars($item['image']); ?>" alt="Book">
            <div class="item-details">
                <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                <p>Quantity: <?php echo $item['quantity']; ?></p>
                <p>Price: Rs. <?php echo number_format($item['price'], 2); ?></p>
            </div>
            <a href="cart.php?delete=<?php echo $item['id']; ?>">
                <button class="delete-btn">Delete</button>
            </a>
        </div>
    <?php endforeach; ?>

    <div class="total">Total: Rs. <?php echo number_format($total, 2); ?></div>

    <form action="initiate.php" method="POST">
        <input type="hidden" name="buyer_id" value="<?php echo $buyer_id; ?>">
        <input type="hidden" name="amount" value="<?php echo $total * 100; ?>">
        <input type="hidden" name="cart_items" value='<?php echo json_encode($cart_items); ?>'>
        <button class="proceed-btn" type="submit">Proceed to Pay with Khalti</button>
    </form>
<?php endif; ?>

<a class="back-link" href="index.php">← Back to Book List</a>

</body>
</html>
