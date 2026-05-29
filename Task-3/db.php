<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "placement_portal"
);

if(!$conn){
    die("Database Connection Failed");
}

?>