<?php
// Register.php

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Include the database connection file
include 'db_connect.php'; // Connection to the database

// Handle form submission
$register_error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user inputs from the form
    $student_id = trim($_POST['studentId']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirmPassword']);
    $email = trim($_POST['email']);

    // Check if passwords match
    if ($password !== $confirm_password) {
        $register_error = "Passwords do not match!";
    } else {
        // Hash the password before storing it in the database
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert the data into the database
        $sql = "INSERT INTO credentials (student_id, password, email) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            die("Error preparing the statement: " . $conn->error);
        }

        $stmt->bind_param("sss", $student_id, $hashed_password, $email);

        if ($stmt->execute()) {
            // Registration successful
            header("Location: Login.php"); // Redirect to the login page
            exit();
        } else {
            // Error handling if there was an issue executing the statement
            if ($conn->errno == 1062) {
                // Duplicate entry error
                $register_error = "A user with this student ID or email already exists.";
            } else {
                $register_error = "Error: " . $stmt->error;
            }
        }

        // Close the statement
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<?php
include "inc/head.inc.php";
?>

<body>
    <?php
    include "inc/nav.inc.php";
    ?>
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
