<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

$conn = new mysqli("localhost", "root", "", "car_inventory");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$car_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($car_id > 0) {
   
    $sql = "UPDATE cars SET hide = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $stmt->close();
}

$conn->close();

header("Location: search.php");
exit;
?>
