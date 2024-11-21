<?php
// Index.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Index</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<?php
    include "inc/nav.inc.php"; 
    ?>
    <div class="container mt-5">
        <form method="post">
            <label for="module">Tristan Modules:</label>
            <select id="module" name="module" class="form-control mb-3" onchange="this.form.submit()">
                <option value="" disabled selected>Select a module</option>
                <option value="INF1001" <?php if (isset($_POST['module']) && $_POST['module'] == 'INF1001') echo 'selected'; ?>>INF1001</option>
                <option value="ICT1005" <?php if (isset($_POST['module']) && $_POST['module'] == 'ICT1005') echo 'selected'; ?>>ICT1005</option>
            </select>
        </form>
        <?php
        // Mock data for modules and components
        $modules = [
            'INF1001' => [
                ['name' => 'Quiz 1', 'percentage' => '15%', 'grade' => 'A-'],
                ['name' => 'Quiz 2', 'percentage' => '15%', 'grade' => 'B+'],
                ['name' => 'Project 1', 'percentage' => '20%', 'grade' => 'B-'],
                ['name' => 'Quiz 3', 'percentage' => '20%', 'grade' => 'A'],
                ['name' => 'Project 2', 'percentage' => '30%', 'grade' => 'B']
            ],
            'ICT1005' => [
                ['name' => 'Assignment 1', 'percentage' => '25%', 'grade' => 'B+'],
                ['name' => 'Midterm', 'percentage' => '25%', 'grade' => 'A'],
                ['name' => 'Final Project', 'percentage' => '50%', 'grade' => 'A-']
            ]
        ];

        // Display selected module components if selected
        if (isset($_POST['module']) && $_POST['module'] != '') {
            $selectedModule = $_POST['module'];
            if (isset($modules[$selectedModule])) {
                echo '<table class="table table-bordered mt-3">';
                echo '<thead><tr>';
                foreach ($modules[$selectedModule] as $component) {
                    echo '<th>' . $component['name'] . ' (' . $component['percentage'] . ')</th>';
                }
                echo '</tr></thead>';
                echo '<tbody><tr>';
                foreach ($modules[$selectedModule] as $component) {
                    echo '<td>' . $component['grade'] . '</td>';
                }
                echo '</tr></tbody>';
                echo '</table>';
            }
        }
        ?>
        <h3>Current Grade: B-</h3>
        <label for="goal">Select Goal:</label>
        <select id="goal" name="goal" class="form-control mb-3">
            <option value="A+">A+</option>
            <option value="A">A</option>
            <option value="A-">A-</option>
            <!-- Add other grades -->
        </select>
        <h3>Required: 20%</h3>
    </div>
</body>
</html>