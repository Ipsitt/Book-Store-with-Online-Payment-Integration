<?php
include 'db.php';
session_start();

if (!isset($_SESSION['buyer_id'])) {
    // Redirect to login page if not logged in
    header("Location: buyer_login.php");
    exit;
}

$buyer_id = $_SESSION['buyer_id'];

$buyer = [];

// Get current buyer info
$stmt = $conn->prepare("SELECT full_name, email, phone, address, password FROM buyers WHERE buyer_id = ?");
$stmt->bind_param("i", $buyer_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $buyer = $result->fetch_assoc();
} else {
    echo "Buyer not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = $_POST['password'] ? $_POST['password'] : $buyer['password']; 

    if ($full_name && $phone && $address) {
        
        $update = $conn->prepare("UPDATE buyers SET full_name = ?, phone = ?, address = ?, password = ? WHERE buyer_id = ?");
        $update->bind_param("ssssi", $full_name, $phone, $address, $password, $buyer_id);
        $update->execute();
        echo "<script>alert('Saved successfully!'); window.location.href='account.php';</script>";
        exit;
    } else {
        echo "<script>alert('Please fill in all fields.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Account</title>
    <style>
        body {
            background-color: #111;
            color: white;
            font-family: Arial;
            padding: 40px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: none;
            font-size: 16px;
        }
        input:disabled {
            background-color: #333;
            color: #ccc;
        }
        .save-btn {
            padding: 12px 25px;
            font-size: 16px;
            border: none;
            border-radius: 6px;
            cursor: not-allowed;
            background-color: red;
            color: white;
            transition: background 0.3s ease;
        }
        .save-btn.active {
            background-color: green;
            cursor: pointer;
        }
    </style>
</head>
<body>

<h1>Your Account</h1>

<form method="POST" id="accountForm">
    <div class="form-group">
        <label>Email</label>
        <input type="email" value="<?= htmlspecialchars($buyer['email']) ?>" disabled />
    </div>
    <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="full_name" id="full_name" value="<?= htmlspecialchars($buyer['full_name']) ?>" required />
    </div>
    <div class="form-group">
        <label>Phone</label>
        <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($buyer['phone']) ?>" required />
    </div>
    <div class="form-group">
        <label>Address</label>
        <textarea name="address" id="address" rows="3" required><?= htmlspecialchars($buyer['address']) ?></textarea>
    </div>
    <div class="form-group">
        <label>Password</label>
        <input type="text" name="password" id="password" value="<?= htmlspecialchars($buyer['password']) ?>" />
    </div>

    <button type="submit" id="saveBtn" class="save-btn">Save</button>
</form>

<script>
    window.onload = function() {
        const passwordField = document.getElementById('password');
        const currentPassword = "<?= htmlspecialchars($buyer['password']) ?>"; // Get current password from PHP
        
        console.log("Current password from JS:", currentPassword);

        // Only set value if the current password is not empty
        if (currentPassword) {
            passwordField.value = currentPassword;
        }
    };

    const form = document.getElementById('accountForm');
    const saveBtn = document.getElementById('saveBtn');

    const originalValues = {
        full_name: "<?= htmlspecialchars($buyer['full_name']) ?>",
        phone: "<?= htmlspecialchars($buyer['phone']) ?>",
        address: `<?= htmlspecialchars($buyer['address']) ?>`,
        password: "<?= htmlspecialchars($buyer['password']) ?>" 
    };

    const inputs = {
        full_name: document.getElementById('full_name'),
        phone: document.getElementById('phone'),
        address: document.getElementById('address'),
        password: document.getElementById('password')
    };

    const checkChangesAndValidation = () => {
        // Check if any value has changed (including password)
        const changed = (
            inputs.full_name.value !== originalValues.full_name ||
            inputs.phone.value !== originalValues.phone ||
            inputs.address.value !== originalValues.address ||
            inputs.password.value !== originalValues.password
        );

        const allFilled = (
            inputs.full_name.value.trim() !== '' &&
            inputs.phone.value.trim() !== '' &&
            inputs.address.value.trim() !== ''
        );

        // Update button state 
        if (changed && allFilled) {
            saveBtn.classList.add('active');
            saveBtn.disabled = false;
            saveBtn.style.cursor = 'pointer';
        } else {
            saveBtn.classList.remove('active');
            saveBtn.disabled = true;
            saveBtn.style.cursor = 'not-allowed';
        }
    };

    Object.values(inputs).forEach(input => {
        input.addEventListener('input', checkChangesAndValidation);
    });

    checkChangesAndValidation();
</script>

</body>
</html>
