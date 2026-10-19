
<?php
$conn = new mysqli("localhost", "root", "", "sportify_db");

if ($conn->connect_error) {
    die("Database connection failed.");
}

$conn->set_charset("utf8mb4");

$equipment = null;
$error = "";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    $error = "Invalid equipment QR code.";
} else {
    $stmt = $conn->prepare(
        "SELECT id, equipment_name, quantity, available_quantity, status
         FROM equipment
         WHERE id = ?"
    );
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $equipment = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$equipment) {
        $error = "Equipment not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Equipment | Sportify</title>
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            padding: 24px;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #102a43;
        }

        .card {
            max-width: 520px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 14px;
            box-shadow: 0 4px 18px #00000012;
        }

        h1, h2 { text-align: center; }

        .detail {
            padding: 12px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .error {
            padding: 14px;
            background: #fee2e2;
            color: #991b1b;
            border-radius: 8px;
        }

        .available { color: #15803d; }
        .unavailable { color: #b91c1c; }

        a {
            display: block;
            margin-top: 24px;
            padding: 13px;
            text-align: center;
            text-decoration: none;
            background: #2563eb;
            color: white;
            border-radius: 8px;
        }
    </style>
</head>
<body>
<div class="card">
    <h1>⚽ SPORTIFY</h1>
    <h2>Equipment Details</h2>

    <?php if ($error !== "") { ?>
        <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php } else { ?>
        <div class="detail">
            <strong>Equipment ID:</strong>
            <?php echo (int)$equipment["id"]; ?>
        </div>

        <div class="detail">
            <strong>Name:</strong>
            <?php echo htmlspecialchars($equipment["equipment_name"]); ?>
        </div>

        <div class="detail">
            <strong>Total Quantity:</strong>
            <?php echo (int)$equipment["quantity"]; ?>
        </div>

        <div class="detail">
            <strong>Available Quantity:</strong>
            <?php echo (int)$equipment["available_quantity"]; ?>
        </div>

        <div class="detail">
            <strong>Status:</strong>
            <span class="<?php echo (int)$equipment["available_quantity"] > 0 ? "available" : "unavailable"; ?>">
                <?php echo (int)$equipment["available_quantity"] > 0 ? "Available" : "Out of Stock"; ?>
            </span>
        </div>
    <?php } ?>

    <a href="index.php">Back to Sportify</a>
</div>
</body>
</html>
<?php $conn->close(); ?>
