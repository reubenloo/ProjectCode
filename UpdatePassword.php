<?php
// UpdatePassword.php

// Includes the database connection file
include 'db_connect.php';
?>

<?php
// Handles the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Gets the user inputs from the form
        $student_id = $_POST['studentId'];
        $new_password = $_POST['newPassword'];
        $confirm_password = $_POST['confirmPassword'];

        // Validates the Student ID
        if (!preg_match('/^\d{7}$/', $student_id)) {
            $errors['studentId'] = "Student ID must be exactly 7 digits";
        }

        // Validates the Password so its secure
        if (strlen($new_password) < 8) {
            $errors['newPassword'] = "Password must be at least 8 characters long";
        } elseif (
            !preg_match('/[A-Z]/', $new_password) ||
            !preg_match('/[a-z]/', $new_password) ||
            !preg_match('/[0-9]/', $new_password) ||
            !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $new_password)
        ) {
            $errors['newPassword'] = "Password must include uppercase, lowercase, number, and special character";
        }

        // Checks if the 2 passwords match
        if ($new_password !== $confirm_password) {
            $errors['confirmPassword'] = "Passwords do not match";
        }

        if (empty($errors)) {
            // Check if the student ID exists in the database so that there are no duplicates
            $sql = "SELECT * FROM credentials WHERE student_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Database error during student check");
            }

            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            if ($result->num_rows === 0) {
                $errors['studentId'] = "No account found with that Student ID";
            } else {
                // Hash the new password before updating it in the database for security reasons
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

                // Updates the password in the database as a hash
                $update_sql = "UPDATE credentials SET password = ? WHERE student_id = ?";
                $update_stmt = $conn->prepare($update_sql);

                if (!$update_stmt) {
                    throw new Exception("Database error during password update");
                }

                $update_stmt->bind_param("si", $hashed_password, $student_id);

                if ($update_stmt->execute()) {
                    $success = "Password updated successfully! Redirecting to login page...";
                    header("refresh:2;url=Login.php");
                    exit();
                } else {
                    $update_stmt->close();
                    throw new Exception("Failed to update password");
                }
            }
        }
    } catch (Exception $e) {
        $errors['general'] = "Error: " . $e->getMessage(); 
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
        <?php if (isset($success)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($errors['general']); ?>
            </div>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group mb-3">
                <label for="studentId">Student ID:</label>
                <input type="text"
                    class="form-control <?php echo isset($errors['studentId']) ? 'is-invalid' : ''; ?>"
                    id="studentId"
                    name="studentId"
                    required>
                <?php if (isset($errors['studentId'])): ?>
                    <div class="invalid-feedback">
                        <?php echo htmlspecialchars($errors['studentId']); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group mb-3">
                <label for="newPassword">New Password:</label>
                <input type="password"
                    class="form-control <?php echo isset($errors['newPassword']) ? 'is-invalid' : ''; ?>"
                    id="newPassword"
                    name="newPassword"
                    required>
                <?php if (isset($errors['newPassword'])): ?>
                    <div class="invalid-feedback">
                        <?php echo htmlspecialchars($errors['newPassword']); ?>
                    </div>
                <?php endif; ?>
                <div class="form-text">
                    Password must be at least 8 characters long and contain uppercase, lowercase, number, and special character.
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="confirmPassword">Confirm Password:</label>
                <input type="password"
                    class="form-control <?php echo isset($errors['confirmPassword']) ? 'is-invalid' : ''; ?>"
                    id="confirmPassword"
                    name="confirmPassword"
                    required>
                <?php if (isset($errors['confirmPassword'])): ?>
                    <div class="invalid-feedback">
                        <?php echo htmlspecialchars($errors['confirmPassword']); ?>
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Update Password</button>
        </form>
    </div>

    <?php
    include "inc/footer.inc.php";
    ?>

</body>

</html>