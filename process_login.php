<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="css/main.css">

<?php
include "inc/head.inc.php";
?>

<body>
    <?php include "inc/nav.inc.php"; ?>

    <?php
    require_once 'validation_functions.php';
    $student_id = $password = $errorMsg = "";
    $success = true;

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (empty($_POST["student_id"])) {
            $errorMsg .= "Student ID is required.<br>";
            $success = false;
        } else {
            $student_id = $_POST["student_id"];
        }

        if (empty($_POST["password"])) {
            $errorMsg .= "Password is required.<br>";
            $success = false;
        } else {
            $password = $_POST["password"];
        }

        if ($success) {
            if (verify_credentials()) {
                echo "<h2>Login successful!</h2>";
                echo "<h3>Welcome back, " . $_SESSION['user_fname'] . " " . $_SESSION['user_lname'] . "</h3>";
                echo "<a href='index.php' class='home-button'>Return to home</a>";
            } else {
                echo "<h2>Oops!</h2>";
                echo "<h3>The following errors were detected:</h3>";
                echo "<a>Student ID not found or password doesn't match...</a>";
                echo "<a href='login.php' class='login  -button'>Return to login</a>";
            }
        } else {
            echo "<h4>Please fill in all fields</h4>";
            echo "<p>" . $errorMsg . "</p>";
        }
    } else {
        echo "No form data submitted";
        return;
    }

    function verify_credentials()
    {
        global $student_id, $password, $errorMsg, $success;

        // Create database connection.
        $config = parse_ini_file('/var/www/private/db-config.ini');
        if (!$config) {
            $errorMsg = "Failed to read database config file.";
            $success = false;
        } else {
            $conn = new mysqli(
                $config['servername'],
                $config['username'],
                $config['password'],
                $config['dbname']
            );
            // Check connection
            if ($conn->connect_error) {
                $errorMsg = "Connection failed: " . $conn->connect_error;
                $success = false;
            } else {
                // Prepare the statement:
                $stmt = $conn->prepare("SELECT * FROM #????? WHERE student_id = ?");

                if (!$stmt) {
                    echo "Prepare failed: " . $conn->error . "<br>";
                    $success = false;
                    return;
                }

                $stmt->bind_param("s", $email);

                if (!$stmt->execute()) {
                    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error . "<br>";
                    $errorMsg = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
                    $success = false;
                }

                $result = $stmt->get_result();

                if ($row = $result->fetch_assoc()) {
                    // 4. If email exists, verify password using password_verify():
                    if (password_verify($password, $row['password'])) {
                        $_SESSION['user_fname'] = $row['fname'];
                        $_SESSION['user_lname'] = $row['lname'];
                        return true;
                    }
                }
                return false;
                $stmt->close();
            }
            $conn->close();
        }
    }
    ?>

    <?php
    include "inc/footer.inc.php";
    ?>

</body>