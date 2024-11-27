<?php
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

/**
 * Converts a percentage to a grade letter (A+, A, A-, etc.).
 * @param float|null $percentage The average percentage grade.
 * @return string The corresponding grade letter.
 */
function percentage_to_grade($percentage) {
    if ($percentage === null) return 'N/A'; // No grade available
    if ($percentage >= 85) return 'A+';
    if ($percentage >= 80) return 'A';
    if ($percentage >= 75) return 'A-';
    if ($percentage >= 70) return 'B+';
    if ($percentage >= 65) return 'B';
    if ($percentage >= 60) return 'B-';
    if ($percentage >= 55) return 'C+';
    if ($percentage >= 50) return 'C';
    if ($percentage >= 45) return 'C-';
    if ($percentage >= 40) return 'D+';
    if ($percentage >= 35) return 'D';
    if ($percentage >= 30) return 'D-';
    return 'F';
}
?>
<!DOCTYPE html>
<html lang="en">

<?php include "inc/head.inc.php"; ?>

<body>
    <?php include "inc/nav.inc.php"; ?>

    <div class="container mt-5">
        <h1>Welcome to GradeTracker</h1>

        <!-- Table to show their enrolled modules and their current average grades -->
        <?php
        // SQL Query to get all components and calculate the average grade
        $sql = "
            SELECT 
                m.module_name, 
                COUNT(c.component_id) AS total_components, 
                SUM(
                    CASE 
                        WHEN sg.grade = 'A+' THEN 85
                        WHEN sg.grade = 'A' THEN 80
                        WHEN sg.grade = 'A-' THEN 75
                        WHEN sg.grade = 'B+' THEN 70
                        WHEN sg.grade = 'B' THEN 65
                        WHEN sg.grade = 'B-' THEN 60
                        WHEN sg.grade = 'C+' THEN 55
                        WHEN sg.grade = 'C' THEN 50
                        WHEN sg.grade = 'C-' THEN 45
                        WHEN sg.grade = 'D+' THEN 40
                        WHEN sg.grade = 'D' THEN 35
                        WHEN sg.grade = 'D-' THEN 30
                        WHEN sg.grade = 'F' THEN 0
                        ELSE NULL -- Keep grades as NULL if missing
                    END
                ) AS total_grade_percentage,
                SUM(
                    CASE 
                        WHEN sg.grade IS NOT NULL THEN 1
                        ELSE 0
                    END
                ) AS total_graded_components
            FROM modules m
            LEFT JOIN student_modules sm ON sm.module_id = m.module_id AND sm.student_id = ?
            LEFT JOIN components c ON c.module_id = m.module_id
            LEFT JOIN student_grades sg ON sg.component_id = c.component_id AND sg.student_module_id = sm.student_module_id
            WHERE sm.student_id = ?
            GROUP BY m.module_name;
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $student_id, $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Start table
        echo '<table class="table table-bordered mt-3">';
        echo '<thead><tr><th>Module Name</th><th>Average Grade</th></tr></thead>';
        echo '<tbody>';

        if ($result->num_rows == 0) {
            // If no modules are enrolled, display a single row with "N/A"
            echo '<tr><td class="text-center">N/A</td><td class="text-center">N/A</td></tr>';
        } else {
            // Display enrolled modules with grades if found
            while ($row = $result->fetch_assoc()) {
                $module_name = $row['module_name'];
                $total_components = $row['total_components'];
                $total_graded_components = $row['total_graded_components'];
                $total_grade_percentage = $row['total_grade_percentage'];

                // Determines if the grades exist/ grades have been updated
                if ($total_graded_components > 0) {
                    // Calculates the average grade percentage
                    $average_grade_percentage = ($total_grade_percentage / $total_components);
                    $average_grade_letter = percentage_to_grade($average_grade_percentage);
                } else {
                    // If no grades exist, display N/A
                    $average_grade_letter = 'N/A';
                }

                echo "<tr><td>{$module_name}</td><td>{$average_grade_letter}</td></tr>";
            }
        }

        echo '</tbody></table>';
        $stmt->close();
        ?>
    </div>

    <?php include "inc/footer.inc.php"; ?>
</body>

</html>
