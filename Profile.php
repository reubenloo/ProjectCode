<?php
// Start session to ensure we have access to the student ID
session_start();

// Enable error reporting for debugging purposes (optional, remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include the database connection file
include 'db_connect.php';

// Retrieve the student ID from the session
$student_id = $_SESSION['student_id'] ?? null;

// Check if the student ID is available
if (!$student_id) {
    die("Student ID is not set.");
}

// Prepare the SQL query to fetch student profile data
$sql = "SELECT student_id FROM credentials WHERE student_id = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("i", $student_id);  // Bind the student ID to the query
$stmt->execute();

// Get the result of the query
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Fetch the student data
    $row = $result->fetch_assoc();
    $student_id_display = $row['student_id'];
} else {
    die("Student not found.");
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include "inc/head.inc.php"; ?> <!-- Include the header -->
</head>

<body>

    <?php include "inc/nav.inc.php"; ?> <!-- Include the navbar -->

    <div class="container mt-5">
        <!-- Applying the 'profile-title' class to the h1 tag -->
        <h1 class="profile-title">Student Profile</h1>

        <p><strong>Student ID:</strong> <?php echo htmlspecialchars($student_id_display); ?></p>

        <!-- Completed Modules Table -->
        <h2>Completed Modules</h2>
        <?php
        // Fetch completed modules for the student (with average grade of C and above)
        // Fetch completed modules for the student (modules with average grade >= 50)
        $sql = "SELECT DISTINCT m.module_name 
FROM modules m
JOIN student_modules sm ON sm.module_id = m.module_id
JOIN student_grades g ON g.student_module_id = sm.student_module_id
WHERE sm.student_id = ?
GROUP BY m.module_name
HAVING AVG(CASE 
    WHEN g.grade = 'A+' THEN 95
    WHEN g.grade = 'A' THEN 85
    WHEN g.grade = 'A-' THEN 80
    WHEN g.grade = 'B+' THEN 75
    WHEN g.grade = 'B' THEN 65
    WHEN g.grade = 'B-' THEN 60
    WHEN g.grade = 'C+' THEN 55
    WHEN g.grade = 'C' THEN 50
    ELSE 0 
END) >= 50";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<table class='table'>
    <thead>
        <tr>
            <th>Module Name</th>
        </tr>
    </thead>
    <tbody>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
        <td>" . htmlspecialchars($row['module_name']) . "</td>
      </tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p>No completed modules found.</p>";
        }
        // Fetch modules that the student is currently enrolled in (not completed)
        $sql = "SELECT m.module_name 
                FROM modules m
                JOIN student_modules sm ON sm.module_id = m.module_id
                WHERE sm.student_id = ? AND sm.student_module_id NOT IN (SELECT student_module_id FROM student_grades WHERE grade IS NOT NULL)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<h2>Current Modules</h2><table class='table'><thead><tr><th>Module Name</th></tr></thead><tbody>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr><td>" . htmlspecialchars($row['module_name']) . "</td></tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p>No current modules found.</p>";
        }

        $stmt->close();
        ?>
    </div>

    <?php include "inc/footer.inc.php"; ?> <!-- Include the footer -->

</body>

</html>