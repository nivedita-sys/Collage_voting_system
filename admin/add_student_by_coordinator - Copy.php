<?php
session_start();
include '../db_connect.php'; 

if (!isset($_SESSION['coordinator_id'])) {
    header("Location: coordinator_login.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $college_id = $_POST["college_id"];
    $name = $_POST["name"];
    $password = $_POST["password"];
    $house = $_POST["house"];

    $check = $conn->prepare("SELECT * FROM students WHERE college_id = ?");
    $check->bind_param("s", $college_id);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        $message = "Student with this College ID already exists.";
    } else {
        $sql = "INSERT INTO students (college_id, name, password, house, has_voted) VALUES (?, ?, ?, ?, 0)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $college_id, $name, $password, $house);

        if ($stmt->execute()) {
            $message = "Student added successfully!";
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Add Student</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(120deg, #a1c4fd, #c2e9fb, #fbc2eb, #fcb69f);
            background-size: 400% 400%;
            animation: gradientBG 12s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            width: 100%;
            max-width: 480px;
            padding: 40px 30px;
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(8px);
        }

        h2 {
            text-align: center;
            background: linear-gradient(90deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 30px;
            font-weight: bold;
            margin-bottom: 30px;
            text-shadow: 1px 1px 1px rgba(0,0,0,0.1);
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
            color: #555;
            width: 100%;
            max-width: 320px;
            text-align: left;
        }

        input[type="text"],
        select {
            width: 100%;
            max-width: 320px;
            padding: 12px 15px;
            margin-top: 8px;
            border: 1px solid #ccc;
            border-radius: 12px;
            font-size: 1rem;
            outline: none;
            text-align: center;
            transition: 0.3s;
        }

        input[type="text"]:focus,
        select:focus {
            border-color: #007bff;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.2);
        }

        button[type="submit"] {
            margin-top: 28px;
            width: 100%;
            max-width: 320px;
            padding: 14px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            font-size: 1.1rem;
            font-weight: bold;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button[type="submit"]:hover {
            background: linear-gradient(135deg, #764ba2, #667eea);
        }

        .message {
            margin-top: 20px;
            font-weight: bold;
            text-align: center;
        }

        .message.error { color: red; }
        .message.success { color: green; }

        .back-button {
            display: block;
            margin: 30px auto 0;
            width: 100%;
            max-width: 320px;
            padding: 12px;
            background-color: transparent;
            color: #667eea;
            border: 2px solid #667eea;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .back-button:hover {
            background-color: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Add New Student</h2>

        <form method="POST" <?php if ($message === "Student added successfully!") echo 'style="display:none;"'; ?>>
            <label>College ID:</label>
            <input type="text" name="college_id" required />

            <label>Name:</label>
            <input type="text" name="name" required />

            <label>Password:</label>
            <input type="text" name="password" required />

            <label>House:</label>
            <select name="house" required>
                <option value="">--Select House--</option>
                <option value="Ignis">Ignis</option>
                <option value="Aer">Aer</option>
                <option value="Terra">Terra</option>
                <option value="Aqua">Aqua</option>
            </select>

            <button type="submit">Add Student</button>
        </form>

        <?php if ($message): ?>
            <p class="message <?php echo ($message === 'Student added successfully!') ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
        <?php endif; ?>

        <button class="back-button" onclick="window.location.href='coordinator_dashboard.php'">
            ⬅ Back to Dashboard
        </button>
    </div>
</body>
</html>
