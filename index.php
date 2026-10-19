
<?php
$conn = new mysqli("localhost", "root", "", "sportify_db");

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

$result = $conn->query(
    "SELECT id, equipment_name, quantity, available_quantity, status
     FROM equipment
     ORDER BY equipment_name"
);

$serverIp = "10.139.251.28";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Sportify | Sports Room Management</title>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: Arial, sans-serif;
    background: #f4f7fb;
    color: #1e293b;
}

header {
    background: #102a43;
    color: white;
    padding: 22px 8%;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

header h2 {
    color: #38bdf8;
}

.hero {
    text-align: center;
    padding: 75px 20px;
    background: linear-gradient(135deg, #102a43, #2563eb);
    color: white;
}

.hero h1 {
    font-size: 42px;
    margin-bottom: 18px;
}

.hero p {
    font-size: 18px;
    line-height: 1.7;
}

.button {
    display: inline-block;
    margin: 24px 7px 0;
    padding: 14px 22px;
    border: none;
    border-radius: 8px;
    background: #38bdf8;
    color: #102a43;
    text-decoration: none;
    font-weight: bold;
    font-size: 15px;
    cursor: pointer;
}

.button.white {
    background: white;
    color: #102a43;
}

.features {
    padding: 50px 7%;
    text-align: center;
}

.features h2 {
    margin-bottom: 30px;
}

.cards {
    display: flex;
    justify-content: center;
    gap: 24px;
    flex-wrap: wrap;
}

.card {
    width: 270px;
    padding: 25px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 4px 15px #00000012;
}

.card h3 {
    margin: 14px 0;
    color: #1d4ed8;
}

.card p {
    margin: 9px 0;
    color: #64748b;
    line-height: 1.5;
}

.qr-image {
    display: block;
    width: 150px;
    height: 150px;
    object-fit: contain;
    margin: 18px auto;
}

.status {
    display: inline-block;
    margin-top: 8px;
    padding: 7px 12px;
    border-radius: 20px;
    background: #dcfce7;
    color: #166534;
    font-size: 13px;
}

.status.unavailable {
    background: #fee2e2;
    color: #991b1b;
}

.scan-button {
    display: block;
    margin-top: 16px;
    padding: 12px;
    border-radius: 7px;
    background: #2563eb;
    color: white;
    text-decoration: none;
    font-weight: bold;
}

.scan-button:hover,
.button:hover {
    opacity: 0.88;
}

footer {
    padding: 22px;
    background: #102a43;
    color: white;
    text-align: center;
}

@media (max-width: 600px) {
    .hero h1 {
        font-size: 30px;
    }

    header {
        justify-content: center;
        text-align: center;
    }

    .features {
        padding: 35px 5%;
    }
}
</style>
</head>

<body>

<header>
    <h2>⚽ SPORTIFY</h2>
    <span>Smart Sports Room Management</span>
</header>

<section class="hero">
    <h1>Manage Sports Smarter!</h1>

    <p>
        Manage sports equipment, track availability,<br>
        and organize sports room activities in one place.
    </p>

    <a class="button" href="#equipment">View Equipment</a>
    <a class="button" href="issue.php">Issue Equipment</a>
    <a class="button white" href="return.php">Return Equipment</a>
    <a class="button white" href="history.php">📋 Transaction History</a>
</section>

<section class="features" id="equipment">
    <h2>Sports Equipment & QR Tracking</h2>

    <div class="cards">

        <?php if ($result && $result->num_rows > 0) { ?>

            <?php while ($row = $result->fetch_assoc()) {
                $id = (int)$row["id"];
                $available = (int)$row["available_quantity"];
                $qrFile = "qr_codes/equipment_" . $id . ".png";
                $scanUrl = "http://" . $serverIp .
                           "/sportify/scan.php?id=" . $id;
            ?>

            <div class="card">
                <h1>🏅</h1>

                <h3>
                    <?php echo htmlspecialchars($row["equipment_name"]); ?>
                </h3>

                <p>Equipment ID: <?php echo $id; ?></p>

                <p>
                    Total Quantity:
                    <?php echo (int)$row["quantity"]; ?>
                </p>

                <p>Available: <?php echo $available; ?></p>

                <?php if ($available > 0) { ?>
                    <span class="status">Available</span>
                <?php } else { ?>
                    <span class="status unavailable">Out of Stock</span>
                <?php } ?>

                <img
                    class="qr-image"
                    src="<?php echo htmlspecialchars($qrFile); ?>"
                    alt="QR code for <?php echo htmlspecialchars($row["equipment_name"]); ?>"
                    loading="lazy"
                >

                <a
                    class="scan-button"
                    href="<?php echo htmlspecialchars($scanUrl); ?>"
                    target="_blank"
                    rel="noopener"
                >
                    View Equipment Details
                </a>
            </div>

            <?php } ?>

        <?php } else { ?>

            <p>No equipment found in the database.</p>

        <?php } ?>

    </div>
</section>

<section class="features">
    <h2>Our Features</h2>

    <div class="cards">

        <div class="card">
            <h1>🏸</h1>
            <h3>Equipment Management</h3>
            <p>Organize equipment and track stock availability.</p>
        </div>

        <div class="card">
            <h1>📋</h1>
            <h3>Issue & Return</h3>
            <p>Maintain issue and return records for students.</p>
        </div>

        <div class="card">
            <h1>📱</h1>
            <h3>QR Code Tracking</h3>
            <p>Scan QR codes to view equipment details quickly.</p>
        </div>

        <div class="card">
            <h1>📊</h1>
            <h3>Transaction History</h3>
            <p>View student equipment issue and return records.</p>
            <a class="scan-button" href="history.php">
                View Transaction History
            </a>
        </div>

    </div>
</section>

<footer>
    <p>© 2026 Sportify | Smart Sports Room Management System</p>
</footer>

<?php $conn->close(); ?>

</body>
</html>
