<?php
require '../config.php';

$username = 'testuser';
$password = 'password';
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

$conn = new mysqli("localhost", "root", "" , "phpmyadmin");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
$stmt->bind_param("ss", $username, $hashed_password);

if ($stmt->execute()) {
    echo "New user created successfully.";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
