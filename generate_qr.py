
import qrcode
import os

# Folder to save QR code images
QR_FOLDER = "qr_codes"
os.makedirs(QR_FOLDER, exist_ok=True)

# Sports equipment details
equipment_list = [
    {"id": 1, "name": "Cricket Bat"},
    {"id": 2, "name": "Football"},
    {"id": 3, "name": "Basketball"},
    {"id": 4, "name": "Badminton Racket"},
    {"id": 5, "name": "Volleyball"}
]

for item in equipment_list:
    equipment_id = item["id"]
    equipment_name = item["name"]

    # QR code contains the equipment ID
    qr_data = f"http://10.139.251.28/sportify/scan.php?id={equipment_id}"

    qr = qrcode.QRCode(
        version=1,
        box_size=10,
        border=4
    )

    qr.add_data(qr_data)
    qr.make(fit=True)

    image = qr.make_image(
        fill_color="black",
        back_color="white"
    )

    filename = f"{QR_FOLDER}/equipment_{equipment_id}.png"
    image.save(filename)

    print(f"QR generated: {equipment_name} -> {filename}")

print("\nAll equipment QR codes generated successfully!")
