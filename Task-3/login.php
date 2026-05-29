
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

        // FOR HASHED PASSWORDS
        if(password_verify($password, $student['password'])){

            header("Location: dashboard.php?id=".$student['id']);
            exit();

        }

        // FOR NORMAL PASSWORDS
        else if($password == $student['password']){

            header("Location: dashboard.php?id=".$student['id']);
            exit();

        }

        else{

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

    <title>Login</title>

    <link rel="stylesheet" href="style.css">

</head>

<body>

<div class="main-container">

    <div class="title">
        Campus Placement Portal
    </div>

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

            <button type="submit" name="login">
                Login
            </button>

        </form>

    </div>

</div>

</body>
</html>
=======
<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "user_auth"
);

if(!$conn){

    die("Connection Failed");

}

$email = $_POST['email'];
$password = $_POST['password'];


$check = mysqli_query(

$conn,

"SELECT * FROM users
 WHERE email='$email'
 AND password='$password'"

);


if(mysqli_num_rows($check) > 0){

    $row = mysqli_fetch_assoc($check);

    $name = $row['name'];

    header("Location: home.php?name=$name");

}

else{

    echo "

    <script>

    alert('Invalid Email or Password ❌');

    window.location.href='index.html';

    </script>

    ";

}

?>

