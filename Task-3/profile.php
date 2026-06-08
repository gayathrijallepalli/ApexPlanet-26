<?php

session_start();

include 'db.php';

if(!isset($_SESSION['student'])){
    header("Location: login.php");
}

$student = $_SESSION['student'];

?>


<!DOCTYPE html>
<html>
<head>

    <title>Student Profile</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="container">

<h2>Student Profile</h2>

<center>

<img
src="uploads/<?php echo $student['profile_pic']; ?>"
width="120"
height="120"
>

</center>

<br>

<h3>Name:</h3>
<p><?php echo $student['name']; ?></p>

<h3>Email:</h3>
<p><?php echo $student['email']; ?></p>

<h3>Department:</h3>
<p><?php echo $student['department']; ?></p>

<br>

<a href="dashboard.php">
<button>Back to Dashboard</button>
</a>

<br><br>

<a href="logout.php">
<button>Logout</button>
</a>

</div>

</body>
</html>