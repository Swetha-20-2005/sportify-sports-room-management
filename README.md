# ⚽ Sportify – Smart Sports Room Management System

## 📌 About the Project
Sportify is a web-based sports room management system designed to manage sports equipment, track availability, and maintain student issue and return records. QR codes help users quickly view equipment details.

## ✨ Features
- **Equipment Management:** View equipment names, IDs, and quantities.
- **Issue Equipment:** Record equipment issued to students.
- **Return Equipment:** Update return records and available stock.
- **QR Code Tracking:** Scan QR codes to view equipment details.
- **Transaction History:** View issue and return dates, student names, quantities, and status.
- **Stock Tracking:** Monitor available equipment quantities.

## 🛠️ Technologies Used
- PHP
- MySQL
- HTML
- CSS
- Python
- QR Code Generation
- XAMPP

## 🚀 How to Run the Project
1. Install XAMPP and start Apache and MySQL.
2. Copy the project folder into `C:\xampp\htdocs\sportify`.
3. Create a MySQL database named `sportify_db` using phpMyAdmin.
4. Create the required `equipment` and `transactions` tables.
5. Configure the database connection in the PHP files if necessary.
6. Generate QR codes using `generate_qr.py` after installing its required Python packages.
7. Open `http://localhost/sportify/index.php` in your browser.

## 📂 Main Files
- `index.php` – Home page and equipment listing
- `issue.php` – Equipment issue management
- `return.php` – Equipment return management
- `history.php` – Transaction history
- `scan.php` – QR-based equipment details
- `generate_qr.py` – QR code generation script
- `qr_codes/` – Generated equipment QR images

## 👩‍💻 Project Purpose
This project demonstrates a simple approach to sports equipment inventory management using a web application, database operations, and QR code tracking.
