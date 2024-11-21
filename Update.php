<?php
// Update.php
session_start();

// Include the database connection file
include 'db_connect.php';

// Assume the student ID is stored in session after login
$student_id = $_SESSION['student_id'] ?? null;

if (!$student_id) {
    echo "Please log in first.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user input from form
    $student_module_id = $_POST['module'];
    $component_id = $_POST['component'];
    $grade = $_POST['grade'];

    // Update the grade in the student_grades table
    $sql = "UPDATE student_grades SET grade = ? WHERE student_module_id = ? AND component_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $grade, $student_module_id, $component_id);
    
    if ($stmt->execute()) {
        // Redirect back to Index.php after update
        header('Location: Index.php');
        exit();
    } else {
        echo "Error updating grade: " . $stmt->error;
    }

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
    <div class="container mt-5">
        <form method="post" action="">
            <div class="form-group">
                <label for="module">Module to Update:</label>
                <select id="module" name="module" class="form-control" required>
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
                        echo "<option value='{$row['student_module_id']}'>{$row['module_name']}</option>";
                    }

                    $stmt->close();
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="component">Component to Update:</label>
                <select id="component" name="component" class="form-control" required>
                    <?php
                    // Fetch all components to allow updates
                    $sql = "SELECT component_id, component_name FROM components";
                    $result = $conn->query($sql);

                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['component_id']}'>{$row['component_name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="grade">Grade:</label>
                <select id="grade" name="grade" class="form-control" required>
                    <option value="A+">A+</option>
                    <option value="A">A</option>
                    <option value="A-">A-</option>
                    <!-- Add other grades -->
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
    <?php
    include "inc/footer.inc.php";
    ?>
</body>

</html>
