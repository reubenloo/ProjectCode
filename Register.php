<?php
// Register.php

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Includes the database connection file
include 'db_connect.php'; // Connection to the database

$errors = [];
$form_data = [];

// Handles the form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get user inputs from the form
        $student_id = trim($_POST['studentId']);
        $password = trim($_POST['password']);
        $confirm_password = trim($_POST['confirmPassword']);
        $email = trim($_POST['email']);

        // Stores the form data for re-populating the form (so user doesn't have to type the data again)
        $form_data = [
            'studentId' => $student_id,
            'email' => $email
        ];

        // Validates the student ID such that it is 7 digits
        if (!preg_match('/^\d{7}$/', $student_id)) {
            $errors['studentId'] = "Student ID must be exactly 7 digits";
        }

        // Validating such that the password is complex (8 chars long and is alphanumeric with special chars)
        if (strlen($password) < 8) {
            $errors['password'] = "Password must be at least 8 characters long";
        } elseif (
            !preg_match('/[A-Z]/', $password) ||
            !preg_match('/[a-z]/', $password) ||
            !preg_match('/[0-9]/', $password) ||
            !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)
        ) {
            $errors['password'] = "Password must include uppercase, lowercase, number, and special character";
        }

        // Validate if the 2 passwords match
        if ($password !== $confirm_password) {
            $errors['confirmPassword'] = "Passwords do not match!";
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = "Please enter a valid email address";
        }

        if (empty($errors)) {
            // Checks if the student ID already exists to prevent duplicates
            $check_sql = "SELECT student_id FROM credentials WHERE student_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            if (!$check_stmt) {
                throw new Exception("Database error during student ID check");
            }
            $check_stmt->bind_param("s", $student_id);
            $check_stmt->execute();
            $result = $check_stmt->get_result();
            $check_stmt->close();

            if ($result->num_rows > 0) {
                $errors['studentId'] = "This Student ID is already registered";
            } else {
                // Checks if the email already exists to prevent duplicates
                $check_email_sql = "SELECT email FROM credentials WHERE email = ?";
                $check_email_stmt = $conn->prepare($check_email_sql);
                if (!$check_email_stmt) {
                    throw new Exception("Database error during email check");
                }
                $check_email_stmt->bind_param("s", $email);
                $check_email_stmt->execute();
                $email_result = $check_email_stmt->get_result();
                $check_email_stmt->close();

                if ($email_result->num_rows > 0) {
                    $errors['email'] = "This email is already registered";
                } else {
                    // Inserts the data into the database
                    // Hash the password before storing it in the database for security reasons
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    $sql = "INSERT INTO credentials (student_id, password, email) VALUES (?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    if ($stmt === false) {
                        $errors['general'] = "Error preparing the statement: " . $conn->error;
                    } else {
                        $stmt->bind_param("sss", $student_id, $hashed_password, $email);

                        if ($stmt->execute()) {
                            // If the registration is successful
                            header("Location: Login.php"); // Redirect the user to the login page
                            exit();
                        } else {
                            // For error handling if there was an issue executing the statement
                            if ($conn->errno == 1062) {
                                // Indicate the duplicate entry error
                                $errors['general'] = "A user with this student ID or email already exists.";
                            } else {
                                $errors['general'] = "Error: " . $stmt->error;
                            }
                            $stmt->close();
                        }
                    }
                }
            }
        }
    } catch (Exception $e) {
        $errors['general'] = "An error occurred. Please try again later.";
        error_log("Registration error: " . $e->getMessage()); // Logs the actual error for debugging
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<?php
include "inc/head.inc.php";
?>

<body>
    <nav class="navbar navbar-expand-sm bg-secondary navbar-dark">
        <div class="container">
            <div class="navbar-brand">
                <img src="images/logo.jpg" alt="GradeTracker Logo" class="navbar-logo">
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
        <?php if (isset($errors['general'])): ?>
            <div class="alert alert-danger" role="alert">
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
                <label for="password">Password:</label>
                <input type="password"
                    class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
                    id="password"
                    name="password"
                    required>
                <?php if (isset($errors['password'])): ?>
                    <div class="invalid-feedback">
                        <?php echo htmlspecialchars($errors['password']); ?>
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

            <div class="form-group mb-3">
                <label for="email">Email:</label>
                <input type="email"
                    class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>"
                    id="email"
                    name="email"
                    required>
                <?php if (isset($errors['email'])): ?>
                    <div class="invalid-feedback">
                        <?php echo htmlspecialchars($errors['email']); ?>
                    </div>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary">Register</button>
        </form>
    </div>
    <?php
    include "inc/footer.inc.php";
    ?>
</body>

</html>