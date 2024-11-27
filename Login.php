<?php
// Login.php
session_start(); // Start session to manage login status

// Include the database connection file
include 'db_connect.php'; // Connection to the database

// Catch and display errors
$errors = [];

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Get user inputs
        $student_id = trim($_POST['studentId']); // Trim input to remove leading/trailing spaces
        $password = trim($_POST['password']);

        // Validate Student ID is 7 digits
        if (!preg_match('/^\d{7}$/', $student_id)) {
            $errors['studentId'] = "Student ID must be exactly 7 digits";
        }

        if (empty($errors)) {
            // Prepare SQL to get user credentials using a placeholder
            $sql = "SELECT * FROM credentials WHERE student_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Database error during login");
            }

            // Bind the student_id parameter (assuming student_id is VARCHAR)
            $stmt->bind_param("s", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();

            // Check if the student ID exists
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();

                // Verify the password
                if (password_verify($password, $row['password'])) {
                    // Login successful
                    $_SESSION['student_id'] = $student_id; // Store student ID in session
                    header("Location: Homepage.php"); // Redirect to homepage
                    exit();
                } else {
                    // Invalid password
                    $errors['password'] = "Invalid password. Please try again.";
                }
            } else {
                // Student ID not found
                $errors['studentId'] = "No account found with that Student ID. Please register first.";
            }

            // Close the statement
            $stmt->close();
        }
    } catch (Exception $e) {
        $errors['general'] = "An error occurred. Please try again later.";
        error_log("Login error: " . $e->getMessage());
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
                    <a href="Register.php" class="btn btn-primary">Register</a>
                </div>
            </div>
        </div>
    </nav>
    <div class="container mt-5">
        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $field => $error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <form method="post" action="">
            <div class="form-group">
                <label for="studentId">Student ID:</label>
                <input type="text" class="form-control <?php echo isset($errors['studentId']) ? 'is-invalid' : ''; ?>"
                    id="studentId" name="studentId" required
                    value="<?php echo isset($_POST['studentId']) ? htmlspecialchars($_POST['studentId']) : ''; ?>">
                <?php if (isset($errors['studentId'])): ?>
                    <div class="invalid-feedback">
                        <?php echo htmlspecialchars($errors['studentId']); ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>"
                    id="password" name="password" required>
                <?php if (isset($errors['password'])): ?>
                    <div class="invalid-feedback">
                        <?php echo htmlspecialchars($errors['password']); ?>
                    </div>
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
            <div class="mt-3">
                <a href="UpdatePassword.php">Forgot Password?</a><br>
                <a href="Register.php">No account? Click here to register</a>
            </div>
        </form>
    </div>
</body>

</html>