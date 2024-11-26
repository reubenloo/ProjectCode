<?php
// Simple db_connect.php for XAMPP
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');  // default XAMPP username
define('DB_PASSWORD', '');      // default XAMPP password is blank
define('DB_NAME', 'Project');

try {
    $conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
} catch (Exception $e) {
    error_log($e->getMessage());
    die("Sorry, there was a problem connecting to the database.");
}
?>