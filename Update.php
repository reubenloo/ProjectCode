<?php
// Update.php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Mock update process
    header('Location: Index.php');
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
        <form method="post" action="">
            <div class="form-group">
                <label for="module">Module to Update:</label>
                <select id="module" name="module" class="form-control">
                    <option value="INF1001">INF1001</option>
                    <option value="ICT1005">ICT1005</option>
                </select>
            </div>
            <div class="form-group">
                <label for="component">Component to Update:</label>
                <select id="component" name="component" class="form-control">
                    <option value="quiz1">Quiz 1</option>
                    <option value="quiz2">Quiz 2</option>
                    <!-- Add other components -->
                </select>
            </div>
            <div class="form-group">
                <label for="grade">Grade:</label>
                <select id="grade" name="grade" class="form-control">
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