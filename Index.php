<?php
// Index.php
session_start(); // Start session to get student ID from login

// Include database connection file
include 'db_connect.php';

// Assume the student ID is stored in session after login
$student_id = $_SESSION['student_id'] ?? null;

if (!$student_id) {
    echo "Please log in first.";
    exit;
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
        <form method="post">
            <label for="module">Modules:</label>
            <select id="module" name="module" class="form-control mb-3" onchange="this.form.submit()">
                <option value="" disabled selected>Select a module</option>

                <?php
                // Fetch modules for the logged-in student
                $sql = "SELECT sm.student_module_id, m.module_name 
                        FROM student_modules sm
                        JOIN modules m ON sm.module_id = m.module_id
                        WHERE sm.student_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $student_id);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    $selected = (isset($_POST['module']) && $_POST['module'] == $row['student_module_id']) ? 'selected' : '';
                    echo "<option value='{$row['student_module_id']}' $selected>{$row['module_name']}</option>";
                }

                $stmt->close();
                ?>
            </select>
        </form>

        <?php
        // Display selected module components and grades
        if (isset($_POST['module']) && $_POST['module'] != '') {
            $student_module_id = $_POST['module'];

            // Fetch components and grades for the selected module
            $sql = "SELECT c.component_name, c.component_id, sg.grade
                    FROM components c
                    JOIN student_grades sg ON c.component_id = sg.component_id
                    WHERE sg.student_module_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $student_module_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo '<table class="table table-bordered mt-3">';
                echo '<thead><tr><th>Component</th><th>Grade</th></tr></thead>';
                echo '<tbody>';

                while ($row = $result->fetch_assoc()) {
                    echo "<tr><td>{$row['component_name']}</td><td>{$row['grade']}</td></tr>";
                }

                echo '</tbody></table>';
            } else {
                echo "<p>No components found for the selected module.</p>";
            }

            $stmt->close();
        }
        ?>

        <h3>Current Grade: B- (Calculate dynamically based on components)</h3>
        <label for="goal">Select Goal:</label>
        <select id="goal" name="goal" class="form-control mb-3">
            <option value="A+">A+</option>
            <option value="A">A</option>
            <option value="A-">A-</option>
            <!-- Add other grades -->
        </select>
        <h3>Required: 20% (Calculate based on current grades and goal)</h3>
    </div>

    <?php
    function get_data()
    {
        global $email, $pwd, $errorMsg, $success;

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
                $stmt = $conn->prepare("SELECT * FROM XXX WHERE XXX INNER JOIN XXX = ?");

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
                    if (password_verify($pwd, $row['password'])) {
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

</html>
