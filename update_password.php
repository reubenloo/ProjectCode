<?php

session_start();

require_once 'db_connect.php';

$error = $success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = trim($_POST['student_id']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validate student ID
    if (!preg_match('/^[A-Za-z0-9]{8}$/', $student_id)) {
        $error = "Invalid student ID format.";
    }
    // Validate password
    else if (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } else if ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Check if student exists
        $sql = "SELECT student_id FROM credentials WHERE student_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $student_id);
        $stmt->execute();

        if ($stmt->get_result()->num_rows === 1) {
            // Update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE credentials SET password = ? WHERE student_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ss", $hashed_password, $student_id);

            if ($update_stmt->execute()) {
                $success = "Password has been reset successfully.";
            } else {
                $error = "Error resetting password.";
            }
            $update_stmt->close();
        } else {
            $error = "Student ID not found.";
        }
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
        <h2>Reset Password</h2>
        <form method="post" action="">
            <div class="form-group mb-3">
                <label for="studentId">Student ID:</label>
                <input type="text" class="form-control" id="student_id" name="student_id" required>
            </div>
            <div class="form-group mb-3">
                <label for="new_password">New Password:</label>
                <input type="password" class="form-control" id="new_password" name="new_password" required>
            </div>
            <div class="form-group mb-3">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
    </div>
    <?php
    include "inc/footer.inc.php";
    ?>
</body>

</html>