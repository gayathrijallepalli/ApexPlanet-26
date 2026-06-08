<?php

include 'db.php';

if(isset($_POST['submit'])){

    $name = $_POST['name'];
    $email = $_POST['email'];
    $department = $_POST['department'];
    $percentage = $_POST['percentage'];
    $skills = $_POST['skills'];

    $password = password_hash(
        $_POST['password'],
        PASSWORD_DEFAULT
    );

    // ELIGIBILITY
    if($percentage >= 75){

        $eligibility = "Eligible";
        $placement_status = "Ready for Placements";

    }else{

        $eligibility = "Not Eligible";
        $placement_status = "Improve Percentage";

    }

    // IMAGE UPLOAD
    $image = $_FILES['profile_pic']['name'];
    $temp = $_FILES['profile_pic']['tmp_name'];

    move_uploaded_file(
        $temp,
        "uploads/".$image
    );

    // CHECK EMAIL EXISTS
    $check = mysqli_query(
        $conn,
        "SELECT * FROM students WHERE email='$email'"
    );

    if(mysqli_num_rows($check) > 0){

        echo "<script>alert('Email Already Exists');</script>";

    }else{

        $stmt = mysqli_prepare(
            $conn,
            "INSERT INTO students
            (name,email,department,percentage,skills,password,eligibility,placement_status,profile_pic)
            VALUES(?,?,?,?,?,?,?,?,?)"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "sssssssss",
            $name,
            $email,
            $department,
            $percentage,
            $skills,
            $password,
            $eligibility,
            $placement_status,
            $image
        );

        if(mysqli_stmt_execute($stmt)){

            echo "<script>alert('Registration Successful');</script>";

        }else{

            echo "<script>alert('Registration Failed');</script>";

        }

    }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>

<meta charset="UTF-8">

<title>Student Registration</title>

<link rel="stylesheet" href="style.css">

</head>

<body>

<div class="main-container">

    <div class="container">

        <h2>Campus Placement Portal</h2>

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
            Already have an account? Login
        </a>

    </div>

</div>

</body>
</html>
