
<?php
session_start();

$conn = new mysqli("localhost", "root", "", "sportify_db");

if ($conn->connect_error) {
    die("Database connection failed.");
}

$conn->set_charset("utf8mb4");

if (!isset($_SESSION["issue_token"])) {
    $_SESSION["issue_token"] = bin2hex(random_bytes(32));
}

$message = "";
$messageType = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student = trim($_POST["student_name"] ?? "");
    $equipment_id = (int)($_POST["equipment_id"] ?? 0);
    $quantity = filter_var(
        $_POST["quantity"] ?? null,
        FILTER_VALIDATE_INT
    );
    $token = $_POST["issue_token"] ?? "";

    if (
        !hash_equals($_SESSION["issue_token"], $token)
    ) {
        $message = "This form was already submitted. Please check the equipment list before trying again.";
        $messageType = "error";
    } elseif (
        $student === "" ||
        $equipment_id <= 0 ||
        $quantity === false ||
        $quantity === null ||
        $quantity <= 0
    ) {
        $message = "Please enter valid details.";
        $messageType = "error";
    } else {
        // Consume the token so the same form cannot be submitted twice.
        $_SESSION["issue_token"] = bin2hex(random_bytes(32));

        try {
            $conn->begin_transaction();

            $stmt = $conn->prepare(
                "UPDATE equipment
                 SET available_quantity = available_quantity - ?
                 WHERE id = ? AND available_quantity >= ?"
            );
            $stmt->bind_param(
                "iii",
                $quantity,
                $equipment_id,
                $quantity
            );
            $stmt->execute();

            if ($stmt->affected_rows !== 1) {
                $stmt->close();
                throw new Exception(
                    "Not enough equipment is available. Please check the quantity."
                );
            }

            $stmt->close();

            $stmt = $conn->prepare(
                "INSERT INTO transactions
                 (student_name, equipment_id, quantity, status)
                 VALUES (?, ?, ?, 'Issued')"
            );
            $stmt->bind_param(
                "sii",
                $student,
                $equipment_id,
                $quantity
            );
            $stmt->execute();
            $stmt->close();

            $conn->commit();

            $message = "Equipment issued successfully!";
            $messageType = "success";
        } catch (Throwable $e) {
            $conn->rollback();

            if (strpos($e->getMessage(), "Not enough equipment") !== false) {
                $message = $e->getMessage();
            } else {
                $message = "Unable to issue equipment. Please try again.";
            }

            $messageType = "error";
        }
    }
}

$equipment = $conn->query(
    "SELECT id, equipment_name, available_quantity
     FROM equipment
     WHERE available_quantity > 0
     ORDER BY equipment_name"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Equipment | Sportify</title>

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
            max-width: 600px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px #00000012;
        }

        h2 {
            text-align: center;
        }

        label {
            display: block;
            margin: 18px 0 8px;
        }

        input,
        select,
        button {
            width: 100%;
            padding: 12px;
            border-radius: 6px;
            font-size: 15px;
        }

        input,
        select {
            border: 1px solid #cbd5e1;
        }

        button {
            margin-top: 24px;
            background: #2563eb;
            color: white;
            border: none;
            cursor: pointer;
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
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

        @media (max-width: 640px) {
            .container {
                margin: 25px 15px;
                padding: 22px;
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
    <h2>Issue Sports Equipment</h2>

    <?php if ($message !== "") { ?>
        <p class="message <?php echo htmlspecialchars($messageType); ?>">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php } ?>

    <?php if ($equipment && $equipment->num_rows > 0) { ?>
        <form method="POST" id="issueForm">
            <input
                type="hidden"
                name="issue_token"
                value="<?php echo htmlspecialchars($_SESSION["issue_token"]); ?>"
            >

            <label for="student_name">Student Name</label>
            <input
                type="text"
                id="student_name"
                name="student_name"
                maxlength="100"
                required
            >

            <label for="equipment_id">Select Equipment</label>
            <select id="equipment_id" name="equipment_id" required>
                <option value="">Choose equipment</option>

                <?php while ($item = $equipment->fetch_assoc()) { ?>
                    <option
                        value="<?php echo (int)$item["id"]; ?>"
                        data-available="<?php echo (int)$item["available_quantity"]; ?>"
                    >
                        <?php
                        echo htmlspecialchars($item["equipment_name"])
                            . " (" . (int)$item["available_quantity"]
                            . " available)";
                        ?>
                    </option>
                <?php } ?>
            </select>

            <label for="quantity">Quantity</label>
            <input
                type="number"
                id="quantity"
                name="quantity"
                min="1"
                value="1"
                required
            >

            <button type="submit" id="issueButton">
                Issue Equipment
            </button>
        </form>
    <?php } else { ?>
        <p>No equipment is currently available.</p>
    <?php } ?>
</div>

<script>
    const form = document.getElementById("issueForm");
    const button = document.getElementById("issueButton");
    const equipmentSelect = document.getElementById("equipment_id");
    const quantityInput = document.getElementById("quantity");

    if (form && button) {
        form.addEventListener("submit", function (event) {
            const selectedOption =
                equipmentSelect.options[equipmentSelect.selectedIndex];

            const available = Number(
                selectedOption.dataset.available || 0
            );
            const quantity = Number(quantityInput.value);

            if (
                !selectedOption.value ||
                quantity < 1 ||
                quantity > available
            ) {
                event.preventDefault();
                alert("Please select equipment and enter a valid available quantity.");
                return;
            }

            if (button.disabled) {
                event.preventDefault();
                return;
            }

            button.disabled = true;
            button.textContent = "Processing...";
        });
    }
</script>

</body>
</html>

<?php
$conn->close();
?>
