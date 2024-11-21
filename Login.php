<?php
// login.php
session_start();
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
        <form method="post" action="homepage.php">
            <div class="form-group">
                <label for="studentId">Student ID:</label>
                <input type="text" class="form-control" id="studentId" name="studentId" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Login</button>
            <div class="mt-3">
                <a href="UpdatePassword.php">Forgot Password?</a><br>
                <a href="Register.php">No account? Click here to register</a>
            </div>
        </form>
    </div>
    <?php
    include "inc/footer.inc.php";
    ?>
</body>

</html>