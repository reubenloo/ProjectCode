<?php
// Register.php

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Require the database connection file once
require_once 'db_connect.php'; // Connection to the database

// CSRF Protection
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }
}
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));


// Handle form submission
$register_error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user inputs from the form
    $student_id = trim($_POST['studentId']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $email = trim($_POST['email']);

    if (!preg_match("/^[A-Za-z0-9]{8}$/", $student_id)) {
        $error = "Student ID must be 8 characters of letters and numbers.";
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    }
    elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    }
    // Check if passwords match
    elseif ($password !== $confirm_password) {
        $register_error = "Passwords do not match!";
    } 
    else {
        try {
            // Check if student ID already exists
            $check_sql = "SELECT student_id FROM credentials WHERE student_id = ? OR email = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ss", $student_id, $email);
            $check_stmt->execute();

            if ($check_stmt->get_result()->num_rows > 0) {
                $error = "Student ID or email already registered.";
            } else {
                // Insert new student
                // Hash the password before storing it in the database
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $insert_sql = "INSERT INTO credentials (student_id, email, password) VALUES (?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("sss", $student_id, $email, $hashed_password);
                
                if ($insert_stmt->execute()) {
                    $success = "Registration successful! You can now login.";
                } else {
                    $error = "Registration failed. Please try again.";
                }
                $insert_stmt->close();
            }
            $check_stmt->close();
        } catch (Exception $e) {
            $error = "System error. Please try again later.";
            error_log($e->getMessage());
        }
    }
}
?>›
<!DOCTYPE html>
<html lang="en">

<?php
include "inc/head.inc.php";
?>

<body>
    <nav class="navbar navbar-expand-sm bg-secondary navbar-dark">
        <div class="container">
            <div class="navbar-brand">
                <img src="images/logo.jpg" alt="GradeTracker Logo" class="navbar-logo" href="homepage.php">
                <a class="navbar-brand-text">GradeTracker</a>
            </div>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-links">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbar-links">
                <div class="navbar-nav ms-auto">
                    <a href="FirstPage.php" class="nav-link active">Home</a>
                    <a href="Login.php" class="btn btn-primary">Login</a>
                </div>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <form method="post" action="process_register.php">
            #question, shouldn't the student ID be assigned by system than chosen by student?
            <div class="form-group">
                <label for="studentId">Student ID:</label>
                <input type="text" class="form-control" id="student_id" name="student_id" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirm Password:</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>
    <?php
    include "inc/footer.inc.php";
    ?>
</body>

</html>