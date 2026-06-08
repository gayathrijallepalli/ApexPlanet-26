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