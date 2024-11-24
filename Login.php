<?php
// Login.php
session_start(); // Start session to manage login status

// Include the database connection file
include 'db_connect.php'; // Connection to the database

// Handle form submission
$login_error = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user inputs
    $student_id = trim($_POST['studentId']); // Trim input to remove leading/trailing spaces
    $password = trim($_POST['password']);

    // Prepare SQL to get user credentials using a placeholder
    $sql = "SELECT * FROM credentials WHERE student_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die("Error preparing the statement: " . $conn->error);
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
            $login_error = "Invalid password. Please try again.";
        }
    } else {
        // Student ID not found
        $login_error = "No account found with that Student ID. Please register first.";
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
            <div class="form-group">
                <label for="studentId">Student ID:</label>
                <input type="text" class="form-control" id="student_id" name="student_id" placeholder="Enter student ID" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
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
