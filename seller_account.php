<?php
include 'db.php';
session_start();

if (!isset($_SESSION['seller_email'])) {
    header("Location: seller_login.php");
    exit;
}

$seller_email = $_SESSION['seller_email'];

$seller = [];

// Get current seller info
$stmt = $conn->prepare("SELECT seller_id, full_name, email, phone, address, password FROM sellers WHERE email = ?");
$stmt->bind_param("s", $seller_email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 1) {
    $seller = $result->fetch_assoc();
    $seller_id = $seller['seller_id'];
} else {
    echo "Seller not found.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = $_POST['password'] ? $_POST['password'] : $seller['password']; 

    if ($full_name && $phone && $address) {
        $update = $conn->prepare("UPDATE sellers SET full_name = ?, phone = ?, address = ?, password = ? WHERE seller_id = ?");
        $update->bind_param("ssssi", $full_name, $phone, $address, $password, $seller_id);
        $update->execute();
        echo "<script>alert('Saved successfully!'); window.location.href='seller_account.php';</script>";
        exit;
    } else {
        echo "<script>alert('Please fill in all fields.');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Seller Account</title>
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

<h1>Seller Account</h1>

<form method="POST" id="accountForm">
    <div class="form-group">
        <label>Email</label>
        <input type="email" value="<?= htmlspecialchars($seller['email']) ?>" disabled />
    </div>
    <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="full_name" id="full_name" value="<?= htmlspecialchars($seller['full_name']) ?>" required />
    </div>
    <div class="form-group">
        <label>Phone</label>
        <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($seller['phone']) ?>" required />
    </div>
    <div class="form-group">
        <label>Address</label>
        <textarea name="address" id="address" rows="3" required><?= htmlspecialchars($seller['address']) ?></textarea>
    </div>
    <div class="form-group">
        <label>Password</label>
        <input type="text" name="password" id="password" value="<?= htmlspecialchars($seller['password']) ?>" />
    </div>

    <button type="submit" id="saveBtn" class="save-btn">Save</button>
</form>

<script>
    window.onload = function() {
        const passwordField = document.getElementById('password');
        const currentPassword = "<?= htmlspecialchars($seller['password']) ?>";
        if (currentPassword) {
            passwordField.value = currentPassword;
        }
    };

    const form = document.getElementById('accountForm');
    const saveBtn = document.getElementById('saveBtn');

    const originalValues = {
        full_name: "<?= htmlspecialchars($seller['full_name']) ?>",
        phone: "<?= htmlspecialchars($seller['phone']) ?>",
        address: `<?= htmlspecialchars($seller['address']) ?>`,
        password: "<?= htmlspecialchars($seller['password']) ?>" 
    };

    const inputs = {
        full_name: document.getElementById('full_name'),
        phone: document.getElementById('phone'),
        address: document.getElementById('address'),
        password: document.getElementById('password')
    };

    const checkChangesAndValidation = () => {
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
