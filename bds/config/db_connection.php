<?php
$servername = "localhost";
$username = "root";
$password = "";
$db_name = '125-cn-pham-xuan-lap';
// Kết nối đến database
$conn = new mysqli($servername, $username, $password, $db_name);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
