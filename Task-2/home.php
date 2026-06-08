<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Home Page</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
          rel="stylesheet">

    <style>

        *{
            margin:0;
            padding:0;
            box-sizing:border-box;
            font-family:'Poppins',sans-serif;
        }

        body{

            height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            background:linear-gradient(135deg,#00b894,#0984e3);

        }

        .home-box{

            background:white;
            padding:40px;
            border-radius:20px;
            text-align:center;
            box-shadow:0 10px 30px rgba(0,0,0,0.2);
            animation:fadeIn 1s ease;

        }

        h1{

            margin-bottom:15px;

        }

        p{

            margin-bottom:25px;
            font-size:18px;

        }

        .home-btn{

            padding:12px 25px;
            border:none;
            border-radius:10px;
            background:#00b894;
            color:white;
            font-size:16px;
            cursor:pointer;
            transition:0.3s;

        }

        .home-btn:hover{

            background:#019875;
            transform:translateY(-2px);

        }

        @keyframes fadeIn{

            from{

                opacity:0;
                transform:translateY(20px);

            }

            to{

                opacity:1;
                transform:translateY(0);

            }

        }

    </style>

</head>

<body>

    <div class="home-box">

        <?php

        $name = $_GET['name'];

        echo "<h1>Welcome $name 🎉</h1>";

        ?>

        <p>Welcome to ApexPlanet Task-2 Project 🚀</p>

        <button class="home-btn"
                onclick="goBack()">

            Return to Login Page

        </button>

    </div>

    <script>

        function goBack(){

            window.location.href = "index.html";

        }

    </script>

</body>
</html>