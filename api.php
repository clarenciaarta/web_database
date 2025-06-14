<?php
header('Content-Type: application/json'); // Tambahkan tanda kurung pada header
// Mengizinkan akses dari domain manapun. Penting untuk pengembangan lokal dengan Live Server VS Code.
// Dalam produksi, ganti '*' dengan domain frontend Anda (misalnya 'http://localhost:5500' atau 'http://yourdomain.com')
header('Access-Control-Allow-Origin: *');
// Mengizinkan metode HTTP yang akan digunakan (GET untuk mengambil data)
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
// Mengizinkan header tertentu jika diperlukan oleh permintaan klien
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// --- Konfigurasi Database ---
$servername = "localhost"; // Alamat server database, biasanya 'localhost'
$username = "root";        // Username database MySQL Anda (default XAMPP adalah 'root')
$password = "";            // Password database MySQL Anda (default XAMPP adalah kosong)
$dbname = "web_database";  // Nama database Anda yang sudah dibuat

// Buat koneksi ke database MySQL
$conn = new mysqli($servername, $username, $password, $dbname);

// Periksa apakah koneksi database berhasil
if ($conn->connect_error) {
    // Jika koneksi gagal, kirimkan respons JSON dengan status error
    echo json_encode(["status" => "error", "message" => "Koneksi database gagal: " . $conn->connect_error]);
    exit(); // Hentikan eksekusi skrip
}

// Tangani permintaan berdasarkan parameter 'action' yang diterima dari URL
if (isset($_GET['action'])) {
    $action = $_GET['action']; // Ambil nilai parameter 'action'

    switch ($action) {
        case 'get_articles':
            // Query SQL untuk mengambil semua artikel beserta nama kategorinya
            // Menggunakan JOIN untuk menggabungkan tabel articles dan categories
            $sql = "SELECT a.id, a.title, a.content, c.name AS category_name, a.category_id, a.created_at
                    FROM articles a
                    LEFT JOIN categories c ON a.category_id = c.id
                    ORDER BY a.created_at DESC"; // Urutkan berdasarkan tanggal terbaru
            $result = $conn->query($sql); // Jalankan query

            $articles = array(); // Inisialisasi array untuk menampung artikel
            if ($result->num_rows > 0) {
                // Jika ada hasil, ambil setiap baris dan masukkan ke array articles
                while($row = $result->fetch_assoc()) {
                    $articles[] = $row;
                }
            }
            // Kirim respons JSON dengan status sukses dan data artikel
            echo json_encode(["status" => "success", "data" => $articles]);
            break;

        case 'get_categories':
            // --- PERBAIKAN DI SINI ---
            // Ganti nama-nama file gambar dengan nama kolom 'image_url'
            $sql = "SELECT id, name, image_url FROM categories ORDER BY name ASC";
            $result = $conn->query($sql);

            $categories = array();
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $categories[] = $row;
                }
            }
            // Kirim respons JSON dengan status sukses dan data kategori
            echo json_encode(["status" => "success", "data" => $categories]);
            break;

        case 'search_articles':
            // Pastikan parameter 'query' ada dalam permintaan
            if (isset($_GET['query'])) {
                // Lindungi input dari SQL Injection menggunakan real_escape_string
                $search_query = $conn->real_escape_string($_GET['query']);
                // Query SQL untuk mencari artikel berdasarkan judul atau konten
                $sql = "SELECT a.id, a.title, a.content, c.name AS category_name, a.category_id, a.created_at
                        FROM articles a
                        LEFT JOIN categories c ON a.category_id = c.id
                        WHERE a.title LIKE '%$search_query%' OR a.content LIKE '%$search_query%'
                        ORDER BY a.created_at DESC";
                $result = $conn->query($sql);

                $searched_articles = array(); // Inisialisasi array untuk menampung hasil pencarian
                if ($result->num_rows > 0) {
                    // Jika ada hasil, ambil setiap baris
                    while($row = $result->fetch_assoc()) {
                        $searched_articles[] = $row;
                    }
                }
                // Kirim respons JSON dengan hasil pencarian
                echo json_encode(["status" => "success", "data" => $searched_articles]);
            } else {
                // Jika parameter 'query' tidak ditemukan, kirim error
                echo json_encode(["status" => "error", "message" => "Parameter 'query' tidak ditemukan."]);
            }
            break;

        case 'get_articles_by_category':
            // Pastikan parameter 'category_id' ada dalam permintaan
            if (isset($_GET['category_id'])) {
                // Ubah category_id menjadi integer untuk keamanan
                $category_id = (int)$_GET['category_id'];
                // Query SQL untuk mengambil artikel berdasarkan category_id
                $sql = "SELECT a.id, a.title, a.content, c.name AS category_name, a.category_id, a.created_at
                        FROM articles a
                        LEFT JOIN categories c ON a.category_id = c.id
                        WHERE a.category_id = $category_id
                        ORDER BY a.created_at DESC";
                $result = $conn->query($sql);

                $category_articles = array(); // Inisialisasi array
                if ($result->num_rows > 0) {
                    // Jika ada hasil, ambil setiap baris
                    while($row = $result->fetch_assoc()) {
                        $category_articles[] = $row;
                    }
                }
                // Kirim respons JSON dengan artikel kategori yang diminta
                echo json_encode(["status" => "success", "data" => $category_articles]);
            } else {
                // Jika parameter 'category_id' tidak ditemukan, kirim error
                echo json_encode(["status" => "error", "message" => "Parameter 'category_id' tidak ditemukan."]);
            }
            break;

        default:
            // Jika 'action' yang diminta tidak valid, kirim error
            echo json_encode(["status" => "error", "message" => "Aksi tidak valid."]);
            break;
    }
} else {
    // Jika parameter 'action' tidak ditemukan sama sekali dalam URL, kirim error
    echo json_encode(["status" => "error", "message" => "Parameter 'action' tidak ditemukan."]);
}

// Tutup koneksi database setelah semua operasi selesai
$conn->close();
?>