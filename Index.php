<?php
// Index.php
session_start(); // Start session to get student ID from login

// Enable error reporting for debugging purposes (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
                if (!$stmt) {
                    echo "Error preparing statement: " . $conn->error;
                    exit;
                }
                $stmt->bind_param("i", $student_id);
                if (!$stmt->execute()) {
                    echo "Error executing query: " . $stmt->error;
                    exit;
                }
                $result = $stmt->get_result();
                if (!$result) {
                    echo "Error getting result: " . $stmt->error;
                    exit;
                }

                $selected_module = $_POST['module'] ?? '';

                while ($row = $result->fetch_assoc()) {
                    $selected = ($selected_module == $row['student_module_id']) ? 'selected' : '';
                    echo "<option value='{$row['student_module_id']}' $selected>{$row['module_name']}</option>";
                }

                $stmt->close();
                ?>
            </select>

            <label for="goal">Select Goal:</label>
            <select id="goal" name="goal" class="form-control mb-3" onchange="this.form.submit()">
                <option value="" disabled selected>Select a goal</option>
                <?php
                $grades = array('A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D+', 'D', 'D-', 'F');

                $selected_goal = $_POST['goal'] ?? '';

                foreach ($grades as $grade) {
                    $selected = ($selected_goal == $grade) ? 'selected' : '';
                    echo "<option value='$grade' $selected>$grade</option>";
                }
                ?>
            </select>
        </form>

        <?php
        if ($selected_module != '') {
            // Fetch components and grades
            $sql = "SELECT c.component_name, c.component_id, sg.grade
                    FROM components c
                    JOIN student_modules sm ON c.module_id = sm.module_id
                    LEFT JOIN student_grades sg ON c.component_id = sg.component_id AND sg.student_module_id = sm.student_module_id
                    WHERE sm.student_module_id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                echo "Error preparing statement: " . $conn->error;
                exit;
            }
            $stmt->bind_param("i", $selected_module);
            if (!$stmt->execute()) {
                echo "Error executing query: " . $stmt->error;
                exit;
            }
            $result = $stmt->get_result();
            if (!$result) {
                echo "Error getting result: " . $stmt->error;
                exit;
            }

            // Initialize grade mapping
            $grade_to_percentage = array(
                'A+' => 85,
                'A'  => 80,
                'A-' => 75,
                'B+' => 70,
                'B'  => 65,
                'B-' => 60,
                'C+' => 55,
                'C'  => 50,
                'C-' => 45,
                'D+' => 40,
                'D'  => 35,
                'D-' => 30,
                'F'  => 0,
            );

            $percentage_to_grade = array_flip($grade_to_percentage);
            krsort($percentage_to_grade);

            // Initialize variables
            $total_components = 0;
            $grade_sum = 0;

            $components = array();

            while ($row = $result->fetch_assoc()) {
                $component_name = $row['component_name'];
                $grade_letter = $row['grade']; // may be NULL

                $total_components++;

                // Treat NULL grades or grades not in mapping as 'F' (0%)
                if ($grade_letter === null || !isset($grade_to_percentage[$grade_letter])) {
                    $grade_percentage = 0; // 'F' or 'N/A' equivalent to 0%
                } else {
                    $grade_percentage = $grade_to_percentage[$grade_letter];
                }

                $grade_sum += $grade_percentage;

                $components[] = array(
                    'component_name' => $component_name,
                    'grade' => $grade_letter,
                );
            }

            $stmt->close();

            // Display components and grades
            if (count($components) > 0) {
                echo '<table class="table table-bordered mt-3">';
                echo '<thead><tr><th>Component</th><th>Grade</th></tr></thead>';
                echo '<tbody>';

                foreach ($components as $comp) {
                    $grade_display = $comp['grade'] ?? 'N/A';

                    // Determine CSS class
                    $grade_class = 'default-grade'; // Default for N/A or invalid grades
                    switch ($grade_display) {
                        case 'A+':
                        case 'A':
                        case 'A-':
                            $grade_class = 'grade-a';
                            break;
                        case 'B+':
                        case 'B':
                        case 'B-':
                            $grade_class = 'grade-b';
                            break;
                        case 'C+':
                        case 'C':
                        case 'C-':
                            $grade_class = 'grade-c';
                            break;
                        case 'D+':
                        case 'D':
                        case 'D-':
                            $grade_class = 'grade-d';
                            break;
                        case 'F':
                            $grade_class = 'grade-f';
                            break;
                    }

                    echo "<tr><td>{$comp['component_name']}</td><td class='{$grade_class}'>{$grade_display}</td></tr>";
                }

                echo '</tbody></table>';
            } else {
                echo "<p>No components found for the selected module.</p>";
            }

            // Calculate current grade percentage
            if ($total_components > 0) {
                $current_grade_percentage = $grade_sum / $total_components;
            } else {
                $current_grade_percentage = 0;
            }

            // Get current grade letter
            $current_grade_letter = get_grade_letter($current_grade_percentage, $percentage_to_grade);

            // Display current grade

            // Get the appropriate grade class
            $grade_class = 'default-grade'; // Default class
            $first_letter = substr($current_grade_letter, 0, 1);
            switch ($first_letter) {
                case 'A':
                    $grade_class = 'grade-a';
                    break;
                case 'B':
                    $grade_class = 'grade-b';
                    break;
                case 'C':
                    $grade_class = 'grade-c';
                    break;
                case 'D':
                    $grade_class = 'grade-d';
                    break;
                case 'F':
                    $grade_class = 'grade-f';
                    break;
            }

            echo "<h3>Current Average Grade: <span class='$grade_class'>$current_grade_letter (" . round($current_grade_percentage, 2) . "%)</span></h3>";

            // If goal is selected, calculate required difference
            if ($selected_goal != '' && isset($grade_to_percentage[$selected_goal])) {
                $goal_percentage = $grade_to_percentage[$selected_goal];

                // Calculate the required difference
                $required_difference = $goal_percentage - $current_grade_percentage;

                if ($required_difference <= 0) {
                    echo "<h3>Required: Goal achieved, Good Job!</h3>";
                } else {
                    echo "<h3>Required: You need to increase your average by " . round($required_difference, 2) . "% to achieve $selected_goal.</h3>";
                }
            }
        }

        function get_grade_letter($percentage, $percentage_to_grade)
        {
            foreach ($percentage_to_grade as $perc => $grade) {
                if ($percentage >= $perc) {
                    return $grade;
                }
            }
            return 'F';
        }
        ?>

    </div>

    <?php
    include "inc/footer.inc.php";
    ?>
</body>

</html>