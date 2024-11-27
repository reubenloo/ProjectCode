<?php
// db_connect.php

// Loads the configuration file
$config = parse_ini_file('/var/www/private/db-config.ini', true);

$servername = $config['database']['servername'];
$username = $config['database']['username'];
$password = $config['database']['password'];
$dbname = $config['database']['dbname'];

// Creates the connection with the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Checks the connection with the database
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
