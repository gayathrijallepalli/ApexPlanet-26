<?php

include 'db.php';

if(!isset($_GET['id'])){

    header("Location: login.php");
    exit();

}

$id = mysqli_real_escape_string($conn, $_GET['id']);

$query = mysqli_query(
    $conn,
    "SELECT * FROM students WHERE id='$id'"
);

$student = mysqli_fetch_assoc($query);

?>

<!DOCTYPE html>
<html>

<head>

    <title>Dashboard</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="dashboard">

    <!-- SIDEBAR -->

    <div class="sidebar">

        <h2>Placement Portal</h2>

        <a href="dashboard.php?id=<?php echo $student['id']; ?>">
    Dashboard
</a>

<a href="profile.php?id=<?php echo $student['id']; ?>">
    Profile
</a>

<a href="companies.php?id=<?php echo $student['id']; ?>">
    Companies
</a>

<a href="logout.php">
    Logout
</a>

    </div>

    <!-- MAIN CONTENT -->

    <div class="main-content">

        <div class="container">

            <h2>Placement Dashboard</h2>

            <!-- PROFILE SECTION -->

            <div class="profile-card">

                <img
                src="uploads/<?php echo $student['profile_pic']; ?>"
                class="profile-pic">

                <h2>
                    <?php echo $student['name']; ?>
                </h2>

            </div>

            <!-- STUDENT DETAILS -->

            <div class="info-card">

                <h4>Email</h4>

                <p>
                    <?php echo $student['email']; ?>
                </p>

            </div>

            <div class="info-card">

                <h4>Department</h4>

                <p>
                    <?php echo $student['department']; ?>
                </p>

            </div>

            <div class="info-card">

                <h4>Percentage</h4>

                <p>
                    <?php echo $student['percentage']; ?>%
                </p>

            </div>

            <div class="info-card">

                <h4>Skills</h4>

                <p>
                    <?php echo $student['skills']; ?>
                </p>

            </div>

            <div class="info-card">

                <h4>Eligibility</h4>

                <p>
                    <?php echo $student['eligibility']; ?>
                </p>

            </div>

            <div class="info-card">

                <h4>Placement Status</h4>

                <p
                style="
                background:
                <?php echo ($student['placement_status']=='Placed') ? 'green' : 'red'; ?>;
                color:white;
                padding:8px;
                border-radius:8px;
                display:inline-block;
                ">

                <?php echo $student['placement_status']; ?>

                </p>

            </div>

            <hr>

            <!-- TOP RECRUITERS -->

            <h2>Top Recruiters</h2>

            <div class="company-grid">

                <div class="company-card">

                    <img src="images/tcs.jpg">

                    <p>TCS</p>

                </div>

                <div class="company-card">

                    <img src="images/infosys.jpg">

                    <p>Infosys</p>

                </div>

                <div class="company-card">

                    <img src="images/google.jpg">

                    <p>Google</p>

                </div>

                <div class="company-card">

                    <img src="images/microsoft.jpg">

                    <p>Microsoft</p>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>