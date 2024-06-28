import os
import requests
import base64
import json

def send_images_in_directory(directory_path):
    # URL Antares
    antares_url = "https://platform.antares.id:8443/~/antares-cse/cnt-nUYb2U2ePZi1te9x"
    
    # Header
    headers = {
        "X-M2M-Origin": "52611abd7352ee93:5b27d91bb8c356e7",
        "Content-Type": "application/json;ty=4",
        "Accept": "application/json"
    }
    
    # Iterasi setiap file dalam direktori
    for filename in os.listdir(directory_path):
        filepath = os.path.join(directory_path, filename)
        
        # Baca gambar dan encode ke base64
        with open(filepath, "rb") as image_file:
            image_data = image_file.read()
            base64_image = base64.b64encode(image_data).decode('utf-8')

        # Potong base64 menjadi bagian-bagian (chunk) agar tidak terlalu panjang
        chunk_size = 2000  # Ukuran potongan base64
        chunks = [base64_image[i:i+chunk_size] for i in range(0, len(base64_image), chunk_size)]

        # Kirim potongan-potongan base64
        total_chunks = len(chunks)

        for i, chunk in enumerate(chunks):
            status = "next" if i < total_chunks - 1 else "end"
            data = {
                "id":"plant001",
                "posisi":"atas",
                "image_data": chunk,
                "status": status
            }
            body = {
                "m2m:cin": {
                    "xmlns:m2m": "http://www.onem2m.org/xml/protocols",
                    "cnf": "application/json",
                    "con": json.dumps(data)  # Menyimpan data dalam format JSON
                }
            }
            response = requests.post(antares_url, headers=headers, json=body)
            print(f"Sent chunk {i+1}/{len(chunks)} of file {filename} with status: {status}")
    
    print("All images in the directory sent successfully to Antares!")

# Path direktori yang ingin Anda kirim
directory_path = "/var/www/nurtura-grow/testing"

# Kirim gambar-gambar dalam direktori ke Antares
send_images_in_directory(directory_path)