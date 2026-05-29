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