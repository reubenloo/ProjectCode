<?php
// Update.php
session_start();

// Includes the database connection file
include 'db_connect.php';

// Assumes the student ID is stored in the session after login
$student_id = $_SESSION['student_id'] ?? null;

if (!$student_id) {
    echo "Please log in first.";
    exit;
}

// Handles the form submission for enrolling in an unenrolled module
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enroll_module'])) {
    $module_id = $_POST['enroll_module'];

    // Check if the student is already enrolled in the selected module
    $sql_check = "SELECT student_module_id FROM student_modules WHERE student_id = ? AND module_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $student_id, $module_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows === 0) {
        // If not enrolled, insert into student_modules to enroll the student
        $sql_insert = "INSERT INTO student_modules (student_id, module_id) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ii", $student_id, $module_id);
        if ($stmt_insert->execute()) {
            echo "<div class='alert alert-success'>Successfully enrolled in module.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error enrolling in module: " . $stmt_insert->error . "</div>";
        }
        $stmt_insert->close();
    } else {
        echo "<div class='alert alert-warning'>You are already enrolled in this module.</div>";
    }

    $stmt_check->close();
}

// Handles the form submission for deleting or unenrolling the module
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_module'])) {
    $student_module_id = $_POST['delete_module'];

    // Check if the student has enrolled for the module
    $sql_check = "SELECT student_module_id FROM student_modules WHERE student_id = ? AND student_module_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $student_id, $student_module_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // If enrolled, deletes the previous grades and module information from the user
        $sql_delete = "DELETE FROM student_modules WHERE student_id = ? AND student_module_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("ii", $student_id, $student_module_id);
        if ($stmt_delete->execute()) {
            echo "<div class='alert alert-success'>Successfully unenrolled from module.</div>";
        } else {
            echo "<div class='alert alert-danger'>Error unenrolling from module: " . $stmt_delete->error . "</div>";
        }
        $stmt_delete->close();
    } else {
        echo "<div class='alert alert-warning'>You are not enrolled in this module.</div>";
    }

    $stmt_check->close();
}

// Handles the form submission for updating the grade
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['grade'], $_POST['module'], $_POST['component'])) {
    // Gets the user input from the form
    $student_module_id = $_POST['module'];
    $component_id = $_POST['component'];
    $grade = $_POST['grade'];

    // Checks if the grade already exists for the given student_module_id and component_id
    $sql_check = "SELECT * FROM student_grades WHERE student_module_id = ? AND component_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("ii", $student_module_id, $component_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // If the record exists, perform the update
        $sql_update = "UPDATE student_grades SET grade = ? WHERE student_module_id = ? AND component_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("sii", $grade, $student_module_id, $component_id);
    } else {
        // If the record does not exist, perform an insert
        $sql_insert = "INSERT INTO student_grades (student_module_id, component_id, grade) VALUES (?, ?, ?)";
        $stmt_update = $conn->prepare($sql_insert);
        $stmt_update->bind_param("iis", $student_module_id, $component_id, $grade);
    }

    if ($stmt_update->execute()) {
        // Redirects the user back to Index.php after update
        header('Location: Index.php');
        exit();
    } else {
        echo "Error updating grade: " . $stmt_update->error;
    }

    $stmt_check->close();
    $stmt_update->close();
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

        <!-- For Enrolling in the Module -->
        <form method="post" action="">
            <div class="form-group">
                <label for="enroll_module">Enroll in a Module:</label>
                <select id="enroll_module" name="enroll_module" class="form-control mb-3" required>
                    <option value="" disabled selected>Select a module</option>
                    <?php
                    // Fetches all the modules from the database
                    $sql = "SELECT module_id, module_name FROM modules";
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    while ($row = $result->fetch_assoc()) {
                        echo "<option value='{$row['module_id']}'>{$row['module_name']}</option>";
                    }

                    $stmt->close();
                    ?>
                </select>
                <button type="submit" class="btn btn-primary">Enroll</button>
            </div>
        </form>

        <!-- For deleting the Module -->
        <form method="post" action="">
            <div class="form-group">
                <label for="delete_module">Unenroll from a Module:</label>
                <select id="delete_module" name="delete_module" class="form-control mb-3" required>
                    <option value="" disabled selected>Select a module</option>
                    <?php
                    // Fetches modules that the student is enrolled in
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
                <button type="submit" class="btn btn-danger">Unenroll</button>
            </div>
        </form>

        <!-- For updating the Grade -->
        <form method="post" action="">
            <div class="form-group">
                <label for="module">Module to Update:</label>
                <select id="module" name="module" class="form-control" required>
                    <option value="" disabled selected>Select a module</option>
                    <?php
                    // Fetches the modules that the student is enrolled in
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
                    <option value="" disabled selected>Select a component</option>
                </select>
            </div>
            <div class="form-group">
                <label for="grade">Grade:</label>
                <select id="grade" name="grade" class="form-control" required>
                    <option value="" disabled selected>Select a grade</option>
                    <option value="A+">A+</option>
                    <option value="A">A</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B">B</option>
                    <option value="B-">B-</option>
                    <option value="C+">C+</option>
                    <option value="C">C</option>
                    <option value="C-">C-</option>
                    <option value="D+">D+</option>
                    <option value="D">D</option>
                    <option value="D-">D-</option>
                    <option value="F">F</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Update Grade</button>
        </form>
    </div>
    <?php
    include "inc/footer.inc.php";
    ?>
    <script>
    document.getElementById('module').addEventListener('change', function() {
        var student_module_id = this.value;

        // Clears the component selected
        var componentSelect = document.getElementById('component');
        componentSelect.innerHTML = '<option value="" disabled selected>Select a component</option>';

        if (student_module_id) {
            // Makes an AJAX request to fetch the components
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_components.php?student_module_id=' + student_module_id, true);
            xhr.onload = function() {
                if (this.status == 200) {
                    try {
                        var components = JSON.parse(this.responseText);
                        if (components.error) {
                            alert(components.error);
                        } else {
                            components.forEach(function(component) {
                                var option = document.createElement('option');
                                option.value = component.component_id;
                                option.text = component.component_name;
                                componentSelect.add(option);
                            });
                        }
                    } catch (e) {
                        console.error('Error parsing response:', e);
                    }
                } else {
                    console.error('Error fetching components:', this.statusText);
                }
            };
            xhr.send();
        }
    });
    </script>
</body>

</html>
