<?php

include 'db.php';

if(isset($_POST['login'])){

    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query(
        $conn,
        "SELECT * FROM students WHERE email='$email'"
    );

    if(mysqli_num_rows($query) > 0){

        $student = mysqli_fetch_assoc($query);

        // Verify hashed password
        if(password_verify($password, $student['password'])){

            header("Location: dashboard.php?id=".$student['id']);
            exit();

        }else{

            echo "<script>alert('Wrong Password');</script>";

        }

    }else{

        echo "<script>alert('Student Not Found');</script>";

    }

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<title>Student Login</title>

<link rel="stylesheet" href="style.css">

</head>

<body>

<div class="main-container">

    <div class="container">

        <h2>Student Login</h2>

        <form method="POST">

            <input
            type="email"
            name="email"
            placeholder="Enter Email"
            required>

            <input
            type="password"
            name="password"
            placeholder="Enter Password"
            required>

            <button
            type="submit"
            name="login">
            Login
            </button>

        </form>

        <br>

        <a href="register.php">
            New User? Register Here
        </a>

    </div>

</div>

</body>
</html>
