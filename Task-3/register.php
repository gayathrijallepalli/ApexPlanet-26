<?php

include 'db.php';

if(isset($_POST['submit']))
{
    $name = $_POST['name'];
    $email = $_POST['email'];
    $department = $_POST['department'];
    $percentage = $_POST['percentage'];
    $skills = $_POST['skills'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $profile_pic = $_FILES['profile_pic']['name'];

    move_uploaded_file(
        $_FILES['profile_pic']['tmp_name'],
        "uploads/".$profile_pic
    );

    $eligibility = ($percentage >= 60) ? "Eligible" : "Not Eligible";

    $placement_status = "Not Placed";

    mysqli_query(
        $conn,
        "INSERT INTO students
        (name,email,department,percentage,skills,password,profile_pic,eligibility,placement_status)

        VALUES
        ('$name','$email','$department','$percentage','$skills','$password','$profile_pic','$eligibility','$placement_status')"
    );

    echo "<script>alert('Registration Successful');</script>";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <title>Register</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="main-container">

    <div class="title">
        Campus Placement Portal
    </div>

    <div class="container">

        <h2>Student Registration</h2>

        <form method="POST" enctype="multipart/form-data">

            <input
            type="text"
            name="name"
            placeholder="Enter Name"
            required>

            <input
            type="email"
            name="email"
            placeholder="Enter Email"
            required>

            <input
            type="text"
            name="department"
            placeholder="Enter Department"
            required>

            <input
            type="number"
            name="percentage"
            placeholder="Enter Percentage"
            required>

            <input
            type="text"
            name="skills"
            placeholder="Enter Skills"
            required>

            <input
            type="password"
            name="password"
            placeholder="Enter Password"
            required>

            <input
            type="file"
            name="profile_pic"
            required>

            <button type="submit" name="submit">
                Register
            </button>

        </form>

        <br>

        <a href="login.php">
            Already have account? Login
        </a>

    </div>

</div>

</body>
</html>
=======
$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "user_auth"
);

if(!$conn){

    die("Connection Failed");

}

$name = $_POST['name'];
$email = $_POST['email'];
$password = $_POST['password'];


$sql = "INSERT INTO users(name,email,password)

VALUES('$name','$email','$password')";


if(mysqli_query($conn,$sql)){

    echo "DATA INSERTED SUCCESSFULLY ✅";

}

else{

    echo mysqli_error($conn);

}

?>
