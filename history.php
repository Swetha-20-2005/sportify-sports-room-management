
<?php
$conn = new mysqli("localhost", "root", "", "sportify_db");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$sql = "SELECT t.id, t.student_name, e.equipment_name,
               t.quantity, t.issue_date, t.return_date, t.status
        FROM transactions t
        LEFT JOIN equipment e ON t.equipment_id = e.id
        ORDER BY t.id DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sportify - Transaction History</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #1e293b;
            margin: 0;
            padding: 30px;
        }

        h1 {
            text-align: center;
            color: #1d4ed8;
        }

        .table-container {
            overflow-x: auto;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 15px #00000012;
            margin-top: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 750px;
        }

        th, td {
            padding: 14px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #1d4ed8;
            color: white;
        }

        .issued {
            color: #b45309;
            font-weight: bold;
        }

        .returned {
            color: #15803d;
            font-weight: bold;
        }

        .button {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 20px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 7px;
        }
    </style>
</head>

<body>

<h1>📋 Transaction History</h1>

<div class="table-container">
    <table>
        <tr>
            <th>ID</th>
            <th>Student Name</th>
            <th>Equipment</th>
            <th>Quantity</th>
            <th>Issue Date</th>
            <th>Return Date</th>
            <th>Status</th>
        </tr>

        <?php if ($result && $result->num_rows > 0) { ?>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo (int)$row["id"]; ?></td>
                    <td><?php echo htmlspecialchars($row["student_name"] ?? ""); ?></td>
                    <td><?php echo htmlspecialchars($row["equipment_name"] ?? "Unknown"); ?></td>
                    <td><?php echo (int)$row["quantity"]; ?></td>
                    <td><?php echo htmlspecialchars($row["issue_date"] ?? "-"); ?></td>
                    <td><?php echo htmlspecialchars($row["return_date"] ?? "-"); ?></td>
                    <td class="<?php echo strtolower($row["status"] ?? ""); ?>">
                        <?php echo htmlspecialchars($row["status"] ?? "Unknown"); ?>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="7">No transaction records found.</td>
            </tr>
        <?php } ?>
    </table>
</div>

<center>
    <a class="button" href="index.php">← Back to Home</a>
</center>

</body>
</html>

<?php $conn->close(); ?>
