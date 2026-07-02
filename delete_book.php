<?php
include 'db.php';
session_start();

if (!isset($_SESSION['seller_email'])) {
    echo "unauthorized";
    exit();
}

$seller_email = $_SESSION['seller_email'];
$seller_stmt = $conn->prepare("SELECT seller_id FROM sellers WHERE email = ?");
$seller_stmt->bind_param("s", $seller_email);
$seller_stmt->execute();
$seller_result = $seller_stmt->get_result();
$seller = $seller_result->fetch_assoc();
$seller_id = $seller['seller_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_id'])) {
    $book_id = $_POST['book_id'];
    $delete_stmt = $conn->prepare("DELETE FROM books WHERE book_id = ? AND seller_id = ?");
    $delete_stmt->bind_param("ii", $book_id, $seller_id);
    if ($delete_stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
}
?>
