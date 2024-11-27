<?php
// Homepage.php
?>
<!DOCTYPE html>
<html lang="en">
<?php
include "inc/head.inc.php";
?>

<body>
    <nav class="navbar navbar-expand-sm bg-secondary navbar-dark">
        <div class="container">
            <div class="navbar-brand">
                <img src="images/logo.jpg" alt="GradeTracker Logo" class="navbar-logo" href="homepage.php">
                <a class="navbar-brand-text">GradeTracker</a>
            </div>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-links">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbar-links">
                <div class="navbar-nav ms-auto">
                    <a href="Register.php" class="nav-link">Register</a>
                    <a href="Login.php" class="btn btn-primary">Login</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content Description -->
    <div class="container mt-5 text-center"> <!-- Center content horizontally -->
        <h1>Welcome to Grade Tracker</h1>

        <!-- First Description of website -->
        <h2 class="subheading mt-4">Calculate and Collect Results</h2>
        <figure>
        <img class="img-thumbnail img-fluid" src="images/Calculate.jpg" alt="Calculate" style="width: 35%; height: auto;" />
            <figcaption>Calculate and Collect Results</figcaption>
        </figure>
        <p class="description">
            We collect, calculate, and store our users' results in our highly secure and confidential database,
            keeping it safe and allowing them to see how they are doing anytime! Users can see both their
            individual grades and current overall score, enabling them to observe their academic performance in real time!
        </p>

        <!-- Second Description of website -->
        <h2 class="subheading mt-4">Improve and Set Goals</h2>
        <figure>
            <img class="img-thumbnail img-fluid" src="images/Goals.jpg" alt="Goals" style="width: 45%; height: auto;"/>
            <figcaption>Improve and Set Goals</figcaption>
        </figure>
        <p class="description">
            Using their current results, users can set academic goals, and Grade Tracker will help
            to inform them on what grades they would need in order to achieve it!
        </p>
    </div>

    <?php include 'footer.inc.php'; ?>
</body>

</html>
