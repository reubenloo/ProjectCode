<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet" href="css/main.css">

<?php
include "inc/head.inc.php";
?>

<body>
    <?php include "inc/nav.inc.php"; ?>

    <?php
    $student_id = $email = $confirm_password = $errorMsg = "";
    $success = true;

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (empty($_POST["student_id"])) {
            $errorMsg .= "Student ID is required.<br>";
            $success = false;
        } else {
            $student_id = sanitize_input($_POST["student_id"]);
        }

        if (empty($_POST["email"])) {
            $errorMsg .= "Email is required.<br>";
            $success = false;
        } else {
            $email = sanitize_input($_POST["email"]);
            // Additional check to make sure e-mail address is well-formed.
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errorMsg .= "Invalid email format.<br>";
                $success = false;
            }
        }

        if (empty($_POST["password"]) || empty($_POST["confirm_password"])) {
            $errorMsg .= "Password and confirm password is required.<br>";
            $success = false;
        } else {
            if ($_POST["password"] != $_POST["confirm_password"]) {
                $errorMsg .= "Password do not match.<br>";
                $success = false;
            } else {
                $password_hashed = password_hash($_POST["password"], PASSWORD_DEFAULT);
            }
        }

        if ($success) {
            saveMemberToDB();
            echo "<h2>Your registration is successful!</h2>";
            echo "<h3>Thank you for signing up, " . $fname . " " . $student_id . "</h3>";
            echo "<a href='login.php' class='login-button'>Log-in</a>";
        } else {
            echo "<h4>Oops!</h4>";
            echo "<h4>The following input errors were detected:</h4>";
            echo "<p>" . $errorMsg . "</p>";
            echo "<a href='register.php' class='register-button'>Return to Sign Up</a>";
        }
    } else {
        echo "No form data submitted";
        return;  // or show your form
    }
    /*
 * Helper function that checks input for malicious or unwanted content.
 */
    function sanitize_input($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    function saveMemberToDB()
    {
        global $fname, $student_id, $email, $password_hashed, $errorMsg, $success;

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
                $stmt = $conn->prepare("INSERT INTO world_of_pets_members(fname, student_id, email, password) VALUES (?, ?, ?, ?)");

                if (!$stmt) {
                    echo "Prepare failed: " . $conn->error . "<br>";
                    $success = false;
                    return;
                }

                $stmt->bind_param("ssss", $fname, $student_id, $email, $password_hashed);

                if (!$stmt->execute()) {
                    echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error . "<br>";
                    $errorMsg = "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
                    $success = false;
                }
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