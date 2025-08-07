<?php
session_start();
include '../db_connect.php'; 

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $coordinator_name = $_POST['coordinator_name'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM coordinators WHERE coordinator_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $coordinator_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if ($row['password'] === $password) {
            $_SESSION['coordinator_id'] = $row['id'];
            $_SESSION['coordinator_name'] = $row['coordinator_name'];
            header("Location: coordinator_dashboard.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "Coordinator not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Coordinator Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(-45deg, #ff512f, #dd2476, #00c6ff, #0072ff);
            background-size: 400% 400%;
            animation: gradientBG 10s ease infinite;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes gradientBG {
            0% {background-position: 0% 50%;}
            50% {background-position: 100% 50%;}
            100% {background-position: 0% 50%;}
        }

        .container {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255,255,255,0.2);
            box-shadow: 0 8px 32px 0 rgba(31,38,135,0.37);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 40px;
            max-width: 400px;
            width: 90%;
            color: #fff;
            animation: fadeIn 1s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
h2 {
    font-size: 2rem;
    text-align: center;
    margin-bottom: 30px;
    background: linear-gradient(90deg, #ff9a9e, #fad0c4, #fad0c4, #ffdde1);
    background-size: 300% 300%;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: gradientMove 6s ease infinite;
    text-shadow: 2px 2px 10px rgba(255, 255, 255, 0.2);
    font-weight: 700;
    letter-spacing: 1px;
}

@keyframes gradientMove {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}


        .input-group {
            position: relative;
            margin-bottom: 25px;
        }

        .input-group input {
            width: 100%;
            padding: 14px 12px;
            border: none;
            border-radius: 8px;
            background: rgba(255,255,255,0.15);
            color: white;
            font-size: 1rem;
            outline: none;
        }

        .input-group label {
            position: absolute;
            top: 14px;
            left: 12px;
            pointer-events: none;
            font-size: 0.95rem;
            color: #ccc;
            transition: 0.2s ease all;
        }

        .input-group input:focus + label,
        .input-group input:not(:placeholder-shown) + label {
            top: -10px;
            left: 10px;
            font-size: 0.8rem;
            background: rgba(30, 60, 114, 0.8);
            padding: 0 5px;
            color: #fff;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 1rem;
            color: #ddd;
        }

        button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 25px;
            background: rgba(0, 0, 0, 0.6);
            color: #fff;
            font-weight: bold;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }

        button:hover {
            background: #ffffff;
            color: #2a5298;
        }

        .error-message {
            text-align: center;
            color: #ff4b5c;
            font-weight: bold;
            margin-top: 10px;
        }

        @media (max-width: 480px) {
            .container {
                padding: 25px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Coordinator Login</h2>
        <form method="POST" autocomplete="off">
            <div class="input-group">
                <input type="text" id="coordinator_name" name="coordinator_name" required placeholder=" " />
                <label for="coordinator_name">Coordinator Name</label>
            </div>

            <div class="input-group">
                <input type="password" id="password" name="password" required placeholder=" " />
                <label for="password">Password</label>
                <span class="toggle-password" onclick="togglePassword()">👁️</span>
            </div>

            <button type="submit">Login</button>
        </form>

        <form action="../index.php" method="get">
            <button type="submit">Back to Home</button>
        </form>

        <?php if($error): ?>
            <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById("password");
            input.type = input.type === "password" ? "text" : "password";
        }
    </script>
</body>
</html>
