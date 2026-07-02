<?php
// error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// JSON header
header('Content-Type: application/json');

include 'db.php';

try {
    $course = isset($_GET['course']) ? $_GET['course'] : '';
    $subject = isset($_GET['subject']) ? $_GET['subject'] : '';

    $sql = "SELECT * FROM books WHERE 1";
    $params = [];
    $types = "";

    if (!empty($course)) {
        $sql .= " AND course = ?";
        $params[] = $course;
        $types .= "s";
    }

    if (!empty($subject)) {
        $sql .= " AND subject = ?";
        $params[] = $subject;
        $types .= "s";
    }

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $books = [];
    while ($row = $result->fetch_assoc()) {
        $books[] = $row;
    }

    echo json_encode($books);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
