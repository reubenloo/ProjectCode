<?php
// Login.php
session_start(); // Start session to manage login status

// Session timeout for 30 mins
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    session_unset();
    session_destroy();
    header("Location: login.php?msg=timeout");
    exit();
}
$_SESSION['last_activity'] = time();

// CSRF protection
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF token validation failed');
    }
}
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

require_once 'validation_functions.php';

// Handle form submission
$login_error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user inputs
    $student_id = trim($_POST['student_id']); // Trim input to remove leading/trailing spaces
    $password = trim($_POST['password']);

    // Prepare SQL to get user credentials using a placeholder
    $sql = "SELECT student_id, password FROM credentials WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        error_log("Login prepare failed: " . $conn->error);
        $login_error = "System error, please try again later.";
    } else {
        // Bind the student_id parameter (assuming student_id is VARCHAR)
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
    }



    // Check if the student ID exists
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $row['password'])) {
            // Login successful
            $_SESSION['student_id'] = $student_id; // Store student ID in session
            $_SESSION['last_login'] = time();
            header("Location: Homepage.php"); // Redirect to homepage
            exit();
        } else {
            // Invalid password
            $login_error = "Invalid credentials. Please try again.";
        }
    } else {
        // Student ID not found
        $login_error = "Invalid credentials. Please try again.";
    }

    // Close the statement
    $stmt->close();
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
                <img src="images/logo.jpg" alt="GradeTracker Logo" class="navbar-logo" href="homepage.php">
                <a class="navbar-brand-text">GradeTracker</a>
            </div>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-links">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbar-links">
                <div class="navbar-nav ms-auto">
                    <a href="FirstPage.php" class="nav-link active">Home</a>
                    <a href="Register.php" class="nav-link">Register</a>
                </div>
            </div>
        </div>
    </nav>
    <?php
    include "inc/nav.inc.php";
    ?>
    <h1>Student Login</h1>
    <p>
        Existing students log in here. For new students, plesae go to the
        <a href="register.php">Student Registration page.</a>.
    </p>
    <div class="container mt-5">
        <?php if ($login_error != ""): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $login_error; ?>
            </div>
        <?php endif; ?>
        <form method="post" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="form-group mb-3">
                <label for="student_id">Student ID:</label>
                <input type="text" class="form-control" id="student_id" name="student_id" placeholder="Enter student ID" required pattern="[A-Za-z0-9]{8}">
            </div>
            <div class="form-group mb-3">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
            <div class="mt-3">
                <a href="update_password.php">Forgot Password?</a><br>
                <a href="register.php">No account? Click here to register</a>
            </div>
        </form>
    </div>

    <?php include "inc/footer.inc.php"; ?>
</body>

</html>