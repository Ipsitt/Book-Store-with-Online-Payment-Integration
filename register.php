<?php
session_start();
include 'db.php';

function emailExists($conn, $email) {
    $tables = ['admin', 'buyers', 'sellers']; 
    foreach ($tables as $table) {
        $stmt = $conn->prepare("SELECT email FROM $table WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) return true;
    }
    return false;
}

function validPassword($password) {
    return preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{6,}$/', $password);
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if (!validPassword($password)) {
        $message = "Password must be at least 6 characters, with one uppercase, one number, and one special character.";
    } elseif (emailExists($conn, $email)) {
        $message = "Email already exists.";
    } else {
        if (isset($_POST['register_buyer'])) {
            $stmt = $conn->prepare("INSERT INTO buyers (full_name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $full_name, $email, $password, $phone, $address);
            if ($stmt->execute()) {
                // Set session and redirect to account page
                $_SESSION['buyer_id'] = $conn->insert_id;
                $_SESSION['buyer_email'] = $email;
                header("Location: index.php");
                exit();
            } else {
                $message = "Error: " . $stmt->error;
            }
        }

        if (isset($_POST['register_seller'])) {
            $stmt = $conn->prepare("INSERT INTO sellers (full_name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $full_name, $email, $password, $phone, $address);
            if ($stmt->execute()) {
                $_SESSION['seller_id'] = $conn->insert_id;
                $_SESSION['seller_email'] = $email;
                header("Location: seller_page.php");
                exit();
            } else {
                $message = "Error: " . $stmt->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Register</title>
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

    .register-container {
      background-color: #1c1c1c;
      padding: 30px 40px;
      border-radius: 10px;
      box-shadow: 0 0 15px rgba(255,255,255,0.1);
      width: 350px;
    }

    .register-container h2 {
      text-align: center;
      margin-bottom: 20px;
    }

    .register-container input,
    .register-container textarea {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: none;
      border-radius: 5px;
      background-color: #333;
      color: white;
    }

    .register-container .buttons {
      display: flex;
      justify-content: space-between;
      gap: 10px;
    }

    .register-container button {
      flex: 1;
      padding: 10px;
      background-color: #00ff88;
      color: black;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
    }

    .register-container button:hover {
      background-color: #00cc6a;
    }

    .message {
      text-align: center;
      margin-top: 10px;
      color: #ff4444;
    }

    .register-container a {
      display: block;
      text-align: center;
      color: #ccc;
      margin-top: 15px;
      text-decoration: none;
    }

    .register-container a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>

  <div class="register-container">
    <h2>Register</h2>

    <?php if (!empty($message)): ?>
      <div class="message"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST">
      <input type="text" name="full_name" placeholder="Full Name" required />
      <input type="email" name="email" placeholder="Email" required />
      <input type="password" name="password" placeholder="Password" required />
      <input type="text" name="phone" placeholder="Phone" />
      <textarea name="address" placeholder="Address"></textarea>

      <div class="buttons">
        <button type="submit" name="register_buyer">Register as Buyer</button>
        <button type="submit" name="register_seller">Register as Seller</button>
      </div>
    </form>

    <a href="login.php">Back to Login</a>
  </div>

</body>
</html>
