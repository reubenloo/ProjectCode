<!DOCTYPE html>
<html lang="en">
<?php
include "inc/head.inc.php";
?>

<body>
    <?php
    include "inc/nav.inc.php";  // Include navigation (e.g., menu bar).
    ?>
    <div class="container mt-5">
        <h1>Welcome to GradeTracker</h1>

        <!-- Table to show modules and their current grades -->
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Module Name</th>
                    <th>Current Grade</th>
                </tr>
            </thead>
            <tbody>

            <?php
            // Fetch all modules the student is enrolled in, along with their grade
            $sql = "SELECT m.module_name, 
                           COALESCE(sg.grade, 'N/A') AS grade
                    FROM modules m
                    LEFT JOIN student_modules sm ON sm.module_id = m.module_id AND sm.student_id = ?
                    LEFT JOIN student_grades sg ON sg.student_module_id = sm.student_module_id
                    WHERE sm.student_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $student_id, $student_id); // Bind student ID twice: for both joins
            $stmt->execute();
            $result = $stmt->get_result();

            $module_found = false;

            while ($row = $result->fetch_assoc()) {
                $module_name = $row['module_name'];
                $grade = $row['grade']; // This will show 'N/A' if no grade is assigned

                // Check if the student is enrolled in any module
                $module_found = true;

                echo "<tr><td>{$module_name}</td><td>{$grade}</td></tr>";
            }

            $stmt->close();

            // If no modules were found, display N/A for both module name and grade
            if (!$module_found) {
                echo "<tr><td colspan='2' class='text-center'>N/A</td></tr>";  // Improved 'N/A' display for no modules
            }

            ?>

            </tbody>
        </table>

    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php
    include "inc/footer.inc.php";  // Include footer
    ?>
</body>
</html>
