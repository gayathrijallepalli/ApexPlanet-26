<?php

// DATABASE CONNECTION
$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "portfolio_db"
);

// CHECK CONNECTION
if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

// FORM SUBMISSION
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // GET FORM VALUES
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // INSERT QUERY
    $sql = "INSERT INTO contacts (name, email, message)
            VALUES ('$name', '$email', '$message')";

    // RUN QUERY
    $result = mysqli_query($conn, $sql);

    if ($result) {

        echo "
        <script>
            alert('Message Sent Successfully');
            window.location.href='contact.php';
        </script>
        ";

    } else {

        echo "
        <script>
            alert('Database Error');
        </script>
        ";

        echo mysqli_error($conn);
    }
}

// CLOSE CONNECTION
mysqli_close($conn);

?>

<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <meta name="viewport"
    content="width=device-width, initial-scale=1.0">

    <title>Contact Form</title>

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
        }

        body{
            font-family:Arial, sans-serif;
            background:#111827;
            display:flex;
            justify-content:center;
            align-items:center;
            min-height:100vh;
        }

        .container{
            width:420px;
            background:#1f2937;
            padding:40px;
            border-radius:12px;
            box-shadow:0 0 20px rgba(0,0,0,0.5);
        }

        h1{
            color:white;
            text-align:center;
            margin-bottom:30px;
        }

        form{
            display:flex;
            flex-direction:column;
            gap:20px;
        }

        input,
        textarea{
            padding:15px;
            border:none;
            border-radius:8px;
            font-size:16px;
            outline:none;
        }

        textarea{
            resize:none;
        }

        button{
            padding:15px;
            border:none;
            border-radius:8px;
            background:#8b5cf6;
            color:white;
            font-size:16px;
            cursor:pointer;
            transition:0.3s;
        }

        button:hover{
            background:#7c3aed;
        }

    </style>

</head>
<body>

<div class="container">

    <h1>Contact Form</h1>

    <form method="POST" action="">

        <input
        type="text"
        name="name"
        placeholder="Enter Your Name"
        required>

        <input
        type="email"
        name="email"
        placeholder="Enter Your Email"
        required>

        <textarea
        name="message"
        rows="5"
        placeholder="Enter Your Message"
        required></textarea>

        <button type="submit">
            Send Message
        </button>

    </form>

</div>

</body>
</html>