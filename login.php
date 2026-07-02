<?php
session_start();
include 'db.php'; 

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check Admin
    $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $adminResult = $stmt->get_result();
    if ($adminResult->num_rows > 0) {
        $admin = $adminResult->fetch_assoc();
        if ($password === $admin['password']) {
            $_SESSION['admin_id'] = $admin['admin_id'];
            header("Location: admin.php");
            exit;
        } else {
            $error = "Invalid credentials.";
        }
    }

    // Check Buyer
    $stmt = $conn->prepare("SELECT * FROM buyers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $buyerResult = $stmt->get_result();
    if ($buyerResult->num_rows > 0) {
        $buyer = $buyerResult->fetch_assoc();
        if ($password === $buyer['password']) {
            $_SESSION['buyer_id'] = $buyer['buyer_id'];
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid credentials.";
        }
    }

    // Check Seller
    $stmt = $conn->prepare("SELECT * FROM sellers WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $sellerResult = $stmt->get_result();
    if ($sellerResult->num_rows > 0) {
        $seller = $sellerResult->fetch_assoc();
        if ($password === $seller['password']) {
            $_SESSION['seller_email'] = $seller['email'];
            header("Location: seller_page.php");
            exit;
        } else {
            $error = "Invalid credentials.";
        }
    }

    // If not found
    if (empty($error)) {
        $error = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login</title>
  <style>
    body {
      background-color: #111;
      color: white;
      font-family: Arial, sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
    }

    .login-container {
      background-color: #1c1c1c;
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(255,255,255,0.1);
      width: 300px;
    }

    .login-container h2 {
      text-align: center;
      margin-bottom: 20px;
    }

    .login-container input {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: none;
      border-radius: 5px;
      background-color: #333;
      color: white;
    }

    .login-container button {
      width: 100%;
      padding: 10px;
      background-color: #00ff88;
      color: black;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
    }

    .login-container button:hover {
      background-color: #00cc6a;
    }

    .login-container .register-link {
      display: block;
      margin-top: 15px;
      text-align: center;
      color: #ccc;
      text-decoration: none;
    }

    .login-container .register-link:hover {
      text-decoration: underline;
    }

    .error {
      color: red;
      text-align: center;
    }
  </style>
</head>
<body>

  <div class="login-container">
    <h2>Login</h2>

    <?php if (!empty($error)): ?>
      <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <button type="submit">Login</button>
    </form>

    <a class="register-link" href="register.php">Register</a>
  </div>

</body>
</html>
