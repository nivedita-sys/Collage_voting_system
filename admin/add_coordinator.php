<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION["admin_id"])) {
    header("Location: admin_login.php");
    exit();
}

$message = "";

// Add Coordinator
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_coordinator'])) {
    $coordinator_name = trim($_POST["coordinator_name"]);
    $password = trim($_POST["password"]);
    $hashed_password = $password; // use password_hash() in production

    $check_sql = "SELECT * FROM coordinators WHERE coordinator_name = ?";
    $stmt = $conn->prepare($check_sql);
    $stmt->bind_param("s", $coordinator_name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $message = "Coordinator already exists.";
    } else {
        $sql = "INSERT INTO coordinators (coordinator_name, password) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $coordinator_name, $hashed_password);

        if ($stmt->execute()) {
            $message = "Coordinator added successfully!";
        } else {
            $message = "Error adding coordinator.";
        }
    }
}

// Delete Coordinator
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $delete_sql = "DELETE FROM coordinators WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $message = "Coordinator deleted successfully!";
    } else {
        $message = "Error deleting coordinator.";
    }
}

// Fetch coordinators
$coordinators = [];
$result = $conn->query("SELECT id, coordinator_name FROM coordinators ORDER BY id DESC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $coordinators[] = $row;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Coordinator Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 50px 15px;
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(-45deg, #6a11cb, #2575fc, #ff416c, #ff4b2b);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            color: #fff;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes floatCard {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        .container {
            background-color: #fff;
            padding: 40px 50px;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 650px;
            text-align: center;
            animation: floatCard 6s ease-in-out infinite;
            color: #333;
        }

        h2 {
            margin-bottom: 25px;
            font-weight: 700;
            font-size: 2.3rem;
        }

        label {
            display: block;
            text-align: left;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 1rem;
        }

        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 2px solid #ddd;
            font-size: 1rem;
            transition: 0.3s;
        }

        input[type="text"]:focus, input[type="password"]:focus {
            border-color: #2575fc;
            outline: none;
            box-shadow: 0 0 8px rgba(37, 117, 252, 0.3);
        }

        input[type="submit"], button.toggle-btn {
            width: 100%;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: white;
            border: none;
            padding: 14px;
            font-size: 1.1rem;
            border-radius: 10px;
            cursor: pointer;
            font-weight: bold;
            letter-spacing: 0.05em;
            box-shadow: 0 4px 15px rgba(101, 60, 255, 0.4);
            transition: all 0.3s ease-in-out;
        }

        input[type="submit"]:hover, button.toggle-btn:hover {
            background: linear-gradient(135deg, #ff4b2b, #ff416c);
            box-shadow: 0 6px 20px rgba(255, 75, 43, 0.5);
            transform: scale(1.03);
        }

        p.message {
            font-size: 1.1rem;
            margin-top: 15px;
            font-weight: 600;
        }

        p.success { color: #28a745; }
        p.error { color: #dc3545; }

        table {
            margin-top: 30px;
            width: 100%;
            border-collapse: collapse;
            font-size: 1rem;
        }

        th, td {
            padding: 14px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background-color: #f7f9fc;
            color: #555;
            font-weight: 600;
        }

        tbody tr:hover {
            background-color: #f0f4ff;
        }

        .delete-button {
            background-color: #dc3545;
            color: white;
            padding: 8px 14px;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: 0.3s;
            display: inline-block;
        }

        .delete-button:hover {
            background-color: #b52a37;
        }

        .back-button {
            margin-top: 30px;
            display: inline-block;
            padding: 12px 26px;
            background-color: #6c757d;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .back-button:hover {
            background-color: #565e64;
            transform: translateY(-2px);
        }

        #addCoordinatorSection {
            margin-bottom: 15px;
        }

        @media (max-width: 480px) {
            .container {
                padding: 30px 25px;
            }
            h2 {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Coordinator Management</h2>

    <?php if (!empty($message)): ?>
        <p class="message <?php echo ($message === 'Coordinator added successfully!' || $message === 'Coordinator deleted successfully!') ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <!-- Add Coordinator Form -->
    <div id="addCoordinatorSection">
        <form method="post" action="">
            <input type="hidden" name="add_coordinator" value="1">
            <label for="coordinator_name">Coordinator Name:</label>
            <input type="text" name="coordinator_name" required>

            <label for="password">Password:</label>
            <input type="password" name="password" required>

            <input type="submit" value="Add Coordinator">
        </form>
    </div>

    <button class="toggle-btn" id="toggleViewBtn">Show Coordinators List</button>

    <!-- Coordinator List -->
    <div id="coordinatorListSection" style="display:none;">
        <?php if (count($coordinators) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Coordinator Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($coordinators as $coord): ?>
                        <tr>
                            <td><?php echo $coord['id']; ?></td>
                            <td><?php echo htmlspecialchars($coord['coordinator_name']); ?></td>
                            <td>
                                <a href="?delete=<?php echo $coord['id']; ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this coordinator?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No coordinators found.</p>
        <?php endif; ?>
    </div>

    <a href="admin_dashboard.php" class="back-button">← Back to Dashboard</a>
</div>

<script>
    const toggleBtn = document.getElementById('toggleViewBtn');
    const addSection = document.getElementById('addCoordinatorSection');
    const listSection = document.getElementById('coordinatorListSection');

    toggleBtn.addEventListener('click', () => {
        if (addSection.style.display === 'none') {
            addSection.style.display = 'block';
            listSection.style.display = 'none';
            toggleBtn.textContent = 'Show Coordinators List';
        } else {
            addSection.style.display = 'none';
            listSection.style.display = 'block';
            toggleBtn.textContent = 'Add Coordinator';
        }
    });
</script>

</body>
</html>
