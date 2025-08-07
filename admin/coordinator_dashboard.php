<?php 
session_start();
include '../db_connect.php';

if (!isset($_SESSION['coordinator_id'])) {
    header("Location: coordinator_login.php");
    exit();
}

$coordinator_name = $_SESSION['coordinator_name'] ?? 'Coordinator';

if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM students WHERE college_id = ?");
    $stmt->bind_param("s", $delete_id);
    $stmt->execute();
    header("Location: coordinator_dashboard.php?show_students=1");
    exit();
}

if (isset($_GET['delete_all']) && $_GET['delete_all'] == '1') {
    $conn->query("DELETE FROM students");
    header("Location: coordinator_dashboard.php");
    exit();
}

$show_students = isset($_GET['show_students']) && $_GET['show_students'] == '1';

$search_name = $_GET['search_name'] ?? '';
$search_id = $_GET['search_id'] ?? '';

$students = [];
$sql_total = "SELECT COUNT(*) AS total FROM students";
$result_total = $conn->query($sql_total);
$total_students = 0;
if ($result_total) {
    $row_total = $result_total->fetch_assoc();
    $total_students = $row_total['total'];
}

if ($show_students) {
    $sql = "SELECT * FROM students WHERE 1=1";
    $params = [];
    $types = '';

    if ($search_name !== '') {
        $sql .= " AND name LIKE ?";
        $params[] = "%" . $search_name . "%";
        $types .= 's';
    }
    if ($search_id !== '') {
        $sql .= " AND college_id LIKE ?";
        $params[] = "%" . $search_id . "%";
        $types .= 's';
    }

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $students = $result->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Coordinator Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 40px;
            background: linear-gradient(to right, #c2e9fb, #a1c4fd);
            color: #333;
        }

        h2 {
            text-align: center;
            font-size: 2.8em;
            background: linear-gradient(90deg, #667eea, #764ba2);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: glow 2s infinite alternate;
            margin-bottom: 40px;
        }

        @keyframes glow {
            from {
                text-shadow: 0 0 5px #fff;
            }
            to {
                text-shadow: 0 0 20px #9d50bb, 0 0 30px #6e48aa;
            }
        }

        .container {
            max-width: 1100px;
            margin: auto;
            background: #ffffffdd;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .card-boxes {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            flex: 1 1 200px;
            background: linear-gradient(135deg, #ffecd2, #fcb69f);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #444;
        }

        .top-links {
            text-align: center;
            margin-bottom: 20px;
        }

        .top-links a {
            margin: 0 15px;
            font-size: 18px;
            color: #007BFF;
            font-weight: bold;
            text-decoration: none;
        }

        .top-links a:hover {
            text-decoration: underline;
        }

        .btn, .danger-btn, button {
            padding: 12px 20px;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease-in-out;
        }

        .btn {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
            letter-spacing: 1px;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #388e3c;
        }

        .danger-btn {
            background-color: #ff4d4d;
            color: white;
        }

        .danger-btn:hover {
            background-color: #cc0000;
        }

        .search-form {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        input[type="text"] {
            padding: 10px;
            font-size: 16px;
            border-radius: 6px;
            border: 1px solid #ccc;
            width: 220px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #007BFF;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .delete-button {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            cursor: pointer;
        }

        .delete-button:hover {
            background-color: #c0392b;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>Welcome, <?php echo htmlspecialchars($coordinator_name); ?>!</h2>

    <div class="card-boxes">
        <div class="card">Total Students: <?php echo $total_students; ?></div>
        <div class="card"><a href="add_student_by_coordinator.php" style="color: inherit; text-decoration: none;">Add Student</a></div>
        <div class="card"><a href="coordinator_logout.php" style="color: inherit; text-decoration: none;">Logout</a></div>
    </div>

    <?php if (!$show_students): ?>
        <div style="text-align:center;">
            <a href="coordinator_dashboard.php?show_students=1" class="btn" style="text-decoration: none;">View Registered Students</a>
        </div>
    <?php else: ?>
        <form method="GET" class="search-form">
            <input type="hidden" name="show_students" value="1">
            <input type="text" name="search_name" placeholder="Search by Name" value="<?php echo htmlspecialchars($search_name); ?>">
            <input type="text" name="search_id" placeholder="Search by College ID" value="<?php echo htmlspecialchars($search_id); ?>">
            <button type="submit" class="btn">Search</button>
            <a href="coordinator_dashboard.php?show_students=1&delete_all=1" class="danger-btn" onclick="return confirm('Are you sure you want to delete ALL students? This action cannot be undone.')">Delete All Students</a>
            <a href="coordinator_dashboard.php" class="btn">Back</a>
        </form>

        <table>
            <thead>
            <tr>
                <th>College ID</th>
                <th>Name</th>
                <th>House</th>
                <th>Has Voted</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php if (empty($students)): ?>
                <tr>
                    <td colspan="5" style="text-align:center;">No students found.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($students as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['college_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['house']); ?></td>
                        <td><?php echo $row['has_voted'] ? 'Yes' : 'No'; ?></td>
                        <td>
                            <form method="GET" onsubmit="return confirm('Are you sure you want to delete this student?');" style="display:inline;">
                                <input type="hidden" name="delete_id" value="<?php echo htmlspecialchars($row['college_id']); ?>">
                                <button type="submit" class="delete-button">Delete</button>
                                <input type="hidden" name="show_students" value="1">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
