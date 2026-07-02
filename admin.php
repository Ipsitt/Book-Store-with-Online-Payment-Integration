<?php
session_start();
include 'db.php';

// Mark payment as sent
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_paid'])) {
    $order_item_id = $_POST['order_item_id'];
    $stmt = $conn->prepare("UPDATE order_items SET status = 'Payment Received', buyer_pay = 'Paid' WHERE order_item_id = ?");
    $stmt->bind_param("i", $order_item_id);
    $stmt->execute();
    exit("success");
}

// Fetch payment info
$stmt = $conn->prepare("
    SELECT oi.order_item_id, oi.status, oi.price, oi.order_id, b.title, s.full_name AS seller_name, s.phone, s.seller_id
    FROM order_items oi
    JOIN books b ON oi.book_id = b.book_id
    JOIN sellers s ON oi.seller_id = s.seller_id
    ORDER BY oi.status DESC, oi.order_item_id DESC
");

$stmt->execute();
$payments = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <style>
        body {
            background-color: #111;
            color: white;
            font-family: Arial;
            padding: 30px;
        }
        h1 {
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #444;
            text-align: left;
        }
        th {
            background-color: #222;
        }
        .paid {
            color: #00cc66;
        }
        .pending {
            color: red;
        }
        .delivered {
            color: #04d6fd;
        }
        .btn {
            padding: 6px 10px;
            font-size: 14px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 5px;
        }
        .btn-pending {
            background-color: red;
            color: white;
        }
        .btn-paid {
            background-color: green;
            color: white;
        }
        form.inline {
            display: inline;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .login-user-btn {
            background-color: green;
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="top-bar">
    <h1>Admin Dashboard</h1>
    <a href="admin_userlist.php" class="login-user-btn">Login as User</a>
</div>

<h2>Seller Payments</h2>
<table>
    <thead>
        <tr>
            <th>Order ID</th>
            <th>Book</th>
            <th>Seller</th>
            <th>Phone</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $payments->fetch_assoc()): ?>
        <tr>
            <td>#<?= $row['order_id'] ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= htmlspecialchars($row['seller_name']) ?> (ID: <?= $row['seller_id'] ?>)</td>
            <td><?= htmlspecialchars($row['phone']) ?></td>
            <td>Rs. <?= $row['price'] ?></td>
            <td class="<?= 
                $row['status'] == 'Payment Received' ? 'paid' : 
                ($row['status'] == 'Delivered' ? 'delivered' : 'pending') ?>">
                <?= $row['status'] ?>
            </td>
            <td>
                <?php if ($row['status'] != 'Payment Received'): ?>
                    <form class="inline" method="POST" onsubmit="return markPaid(this);">
                        <input type="hidden" name="order_item_id" value="<?= $row['order_item_id'] ?>">
                        <input type="hidden" name="mark_paid" value="1">
                        <button type="submit" class="btn btn-pending">Mark as Paid</button>
                    </form>
                <?php else: ?>
                    <span class="btn btn-paid">✔ Paid</span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

<script>
function markPaid(form) {
    const data = new FormData(form);
    fetch('admin.php', {
        method: 'POST',
        body: data
    }).then(res => res.text())
    .then(response => {
        if (response.trim() === "success") {
            location.reload();
        } else {
            alert("Failed to mark as paid");
        }
    });
    return false;
}
</script>

</body>
</html>
