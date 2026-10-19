
<?php
session_start();

$conn = new mysqli("localhost", "root", "", "sportify_db");

if ($conn->connect_error) {
    die("Database connection failed.");
}

$conn->set_charset("utf8mb4");

$message = "";
$messageType = "";

if (!isset($_SESSION["return_token"])) {
    $_SESSION["return_token"] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $transaction_id = (int)($_POST["transaction_id"] ?? 0);
    $token = $_POST["return_token"] ?? "";

    if (
        !hash_equals($_SESSION["return_token"], $token)
    ) {
        $message = "This form was already submitted. Refresh and check the list.";
        $messageType = "error";
    } elseif ($transaction_id <= 0) {
        $message = "Please select a valid transaction.";
        $messageType = "error";
    } else {
        $_SESSION["return_token"] = bin2hex(random_bytes(32));

        try {
            $conn->begin_transaction();

            // Lock the issue record to prevent duplicate returns.
            $stmt = $conn->prepare(
                "SELECT equipment_id, quantity
                 FROM transactions
                 WHERE id = ? AND status = 'Issued'
                 FOR UPDATE"
            );
            $stmt->bind_param("i", $transaction_id);
            $stmt->execute();
            $record = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$record) {
                throw new Exception(
                    "This transaction is already returned or does not exist."
                );
            }

            $equipment_id = (int)$record["equipment_id"];
            $quantity = (int)$record["quantity"];

            // Mark as returned only if it is still Issued.
            $stmt = $conn->prepare(
                "UPDATE transactions
                 SET status = 'Returned', return_date = NOW()
                 WHERE id = ? AND status = 'Issued'"
            );
            $stmt->bind_param("i", $transaction_id);
            $stmt->execute();

            if ($stmt->affected_rows !== 1) {
                $stmt->close();
                throw new Exception("This transaction has already been returned.");
            }
            $stmt->close();

            // Restore the available stock.
            $stmt = $conn->prepare(
                "UPDATE equipment
                 SET available_quantity = available_quantity + ?
                 WHERE id = ?"
            );
            $stmt->bind_param("ii", $quantity, $equipment_id);
            $stmt->execute();

            if ($stmt->affected_rows !== 1) {
                $stmt->close();
                throw new Exception("Could not update equipment stock.");
            }
            $stmt->close();

            $conn->commit();

            $message = "Equipment returned successfully!";
            $messageType = "success";
        } catch (Throwable $e) {
            $conn->rollback();

            if (strpos($e->getMessage(), "This transaction") !== false) {
                $message = $e->getMessage();
            } else {
                $message = "Return failed. Please try again.";
            }

            $messageType = "error";
        }
    }
}

$result = $conn->query(
    "SELECT t.id, t.student_name, t.quantity, t.issue_date,
            e.equipment_name
     FROM transactions t
     JOIN equipment e ON t.equipment_id = e.id
     WHERE t.status = 'Issued'
     ORDER BY t.issue_date DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Return Equipment | Sportify</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #102a43;
        }

        header {
            background: #102a43;
            color: white;
            padding: 22px 8%;
        }

        header h2 {
            margin-top: 0;
        }

        header a {
            color: #38bdf8;
            text-decoration: none;
        }

        .container {
            max-width: 850px;
            margin: 40px auto;
            padding: 28px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px #00000012;
        }

        h2 {
            text-align: center;
        }

        .message {
            padding: 12px;
            border-radius: 6px;
            line-height: 1.5;
        }

        .success {
            background: #dcfce7;
            color: #166534;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
        }

        .transaction {
            border: 1px solid #dbe4ef;
            border-radius: 8px;
            padding: 18px;
            margin: 16px 0;
        }

        .transaction p {
            line-height: 1.6;
        }

        button {
            padding: 11px 18px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        @media (max-width: 600px) {
            .container {
                margin: 20px 12px;
                padding: 18px;
            }
        }
    </style>
</head>

<body>

<header>
    <h2>⚽ SPORTIFY</h2>
    <a href="index.php">← Back to Home</a>
</header>

<div class="container">
    <h2>Return Sports Equipment</h2>

    <?php if ($message !== "") { ?>
        <p class="message <?php echo htmlspecialchars($messageType); ?>">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php } ?>

    <?php if ($result && $result->num_rows > 0) { ?>

        <?php while ($row = $result->fetch_assoc()) { ?>
            <div class="transaction">
                <p>
                    <strong>Student:</strong>
                    <?php echo htmlspecialchars($row["student_name"]); ?>
                </p>

                <p>
                    <strong>Equipment:</strong>
                    <?php echo htmlspecialchars($row["equipment_name"]); ?>
                </p>

                <p>
                    <strong>Quantity:</strong>
                    <?php echo (int)$row["quantity"]; ?>
                </p>

                <p>
                    <strong>Issue Date:</strong>
                    <?php echo htmlspecialchars($row["issue_date"]); ?>
                </p>

                <form method="POST" class="returnForm">
                    <input
                        type="hidden"
                        name="transaction_id"
                        value="<?php echo (int)$row["id"]; ?>"
                    >

                    <input
                        type="hidden"
                        name="return_token"
                        value="<?php echo htmlspecialchars($_SESSION["return_token"]); ?>"
                    >

                    <button type="submit">Return Equipment</button>
                </form>
            </div>
        <?php } ?>

    <?php } else { ?>
        <p>No issued equipment is waiting to be returned.</p>
    <?php } ?>
</div>

<script>
    document.querySelectorAll(".returnForm").forEach(function (form) {
        form.addEventListener("submit", function (event) {
            const button = form.querySelector("button");

            if (button.disabled) {
                event.preventDefault();
                return;
            }

            button.disabled = true;
            button.textContent = "Processing...";
        });
    });
</script>

</body>
</html>

<?php
$conn->close();
?>
