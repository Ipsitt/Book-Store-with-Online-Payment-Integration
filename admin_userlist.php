<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_as'])) {
    session_unset(); // Clear current session (admin logs out)
    $user_type = $_POST['user_type'];
    $user_id = $_POST['user_id'];

    if ($user_type === 'buyer') {
        $_SESSION['buyer_id'] = $user_id;
        header("Location: index.php");
        exit;
    } elseif ($user_type === 'seller') {
        $stmt = $conn->prepare("SELECT email FROM sellers WHERE seller_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $email = $result->fetch_assoc()['email'];
        $_SESSION['seller_email'] = $email;
        header("Location: seller_page.php");
        exit;
    }
}

$buyers = $conn->query("SELECT buyer_id, full_name, phone FROM buyers");
$sellers = $conn->query("SELECT seller_id, full_name, phone FROM sellers");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin: Login as User</title>
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
        .tables-container {
            display: flex;
            gap: 40px;
        }
        table {
            border-collapse: collapse;
            background-color: #1c1c1c;
            width: 100%;
            min-width: 350px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #444;
            text-align: left;
        }
        th {
            background-color: #222;
        }
        h2 {
            margin-bottom: 10px;
        }
        .btn {
            padding: 8px 14px;
            font-size: 14px;
            border: none;
            border-radius: 4px;
            background-color: green;
            color: white;
            cursor: pointer;
        }
        .table-wrapper {
            flex: 1;
        }
    </style>
</head>
<body>

<h1>Login as User</h1>

<div class="tables-container">
    <div class="table-wrapper">
        <h2>Buyers</h2>
        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Phone</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $buyers->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="login_as" value="1">
                            <input type="hidden" name="user_type" value="buyer">
                            <input type="hidden" name="user_id" value="<?= $row['buyer_id'] ?>">
                            <button class="btn" type="submit">Login</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="table-wrapper">
        <h2>Sellers</h2>
        <table>
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Phone</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $sellers->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['full_name']) ?></td>
                    <td><?= htmlspecialchars($row['phone']) ?></td>
                    <td>
                        <form method="POST">
                            <input type="hidden" name="login_as" value="1">
                            <input type="hidden" name="user_type" value="seller">
                            <input type="hidden" name="user_id" value="<?= $row['seller_id'] ?>">
                            <button class="btn" type="submit">Login</button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
