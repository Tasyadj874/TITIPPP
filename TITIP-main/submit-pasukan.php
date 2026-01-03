<?php
// ==========================================================
// KONFIGURASI DAN KONEKSI
// ==========================================================

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "titip";

// KUNCI ENKRIPSI (GANTI DENGAN KUNCI 32 KARAKTER ACAK YANG KUAT!)
define('ENCRYPTION_KEY', 'KunciRahasiaAndaDenganPanjang32Karakter'); 
define('CIPHER', 'aes-256-cbc');

// Koneksi ke database
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// ==========================================================
// FUNGSI UTILITY
// ==========================================================

// Fungsi untuk mengenkripsi data string
function encryptData($data) {
    $ivlen = openssl_cipher_iv_length(CIPHER);
    $iv = openssl_random_pseudo_bytes($ivlen);
    $encrypted = openssl_encrypt($data, CIPHER, ENCRYPTION_KEY, 0, $iv);
    // Simpan IV bersama dengan data terenkripsi untuk dekripsi
    return base64_encode($encrypted . '::' . $iv); 
}

// Fungsi untuk menghasilkan nama file unik
function generateUniqueFileName($originalFileName) {
    $extension = pathinfo($originalFileName, PATHINFO_EXTENSION);
    return uniqid('file_', true) . '.' . $extension;
}

// ==========================================================
// PROSES FORM SUBMISSION
// ==========================================================

// Lokasi penyimpanan file (Pastikan folder 'uploads/' sudah ada!)
$target_dir = "uploads/";

// --- TANGKAP INPUT (Data Pribadi) ---
$nama = htmlspecialchars($_POST['nama']);
$tempat_lahir = htmlspecialchars($_POST['tempat']); // Asumsi name="tempat"
$tanggal_lahir = htmlspecialchars($_POST['tanggal_lahir']);
$jenis_kelamin = htmlspecialchars($_POST['jenis_kelamin']);
$alamat = htmlspecialchars($_POST['alamat']);
$telepon = htmlspecialchars($_POST['telepon']);
$email = htmlspecialchars($_POST['email']);

// --- TANGKAP INPUT (Data Kendaraan) ---
$merk_kendaraan = htmlspecialchars($_POST['merk_kendaraan']);
$plat_kendaraan = htmlspecialchars($_POST['plat_kendaraan']);

// --- UPLOAD FILE DOKUMEN ---

$uploaded_files = [];
$required_files = ['sim_c', 'foto_diri', 'foto_kendaraan', 'foto_stnk'];

foreach ($required_files as $file_input_name) {
    if (isset($_FILES[$file_input_name]) && $_FILES[$file_input_name]['error'] == 0) {
        
        $unique_name = generateUniqueFileName($_FILES[$file_input_name]['name']);
        $target_path = $target_dir . $unique_name;

        if (move_uploaded_file($_FILES[$file_input_name]['tmp_name'], $target_path)) {
            $uploaded_files[$file_input_name] = $unique_name;
        } else {
            header("Location: gabung-pasukan.php?status=error_upload&file=" . $file_input_name);
            $conn->close();
            exit();
        }
    } else {
        header("Location: gabung-pasukan.php?status=error_missing_file&file=" . $file_input_name);
        $conn->close();
        exit();
    }
}

// --- PEMBACAAN & ENKRIPSI ISI DOKUMEN (SIM C & STNK) ---

// SIM C
$sim_c_path = $target_dir . $uploaded_files['sim_c'];
$sim_c_content = file_get_contents($sim_c_path);
$encrypted_sim_c = encryptData($sim_c_content);
unlink($sim_c_path); // Opsional: Hapus file asli di folder 'uploads/' setelah dienkripsi

// STNK
$stnk_path = $target_dir . $uploaded_files['foto_stnk'];
$stnk_content = file_get_contents($stnk_path);
$encrypted_stnk = encryptData($stnk_content);
unlink($stnk_path); // Opsional: Hapus file asli di folder 'uploads/' setelah dienkripsi


// ==========================================================
// SQL INSERT: DATA PRIBADI (Tabel: daftar_pasukan)
// ==========================================================

$sql_pasukan = "INSERT INTO daftar_pasukan (
                    nama, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, no_telepon_wa, email, 
                    file_sim_c, foto_driver, status_pendaftaran
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')"; // status_pendaftaran default 'pending'

$stmt_pasukan = $conn->prepare($sql_pasukan);

if ($stmt_pasukan === FALSE) {
    die("Error preparing statement (Pasukan): " . $conn->error);
}

// Binding parameter: 8x string (s), 1x string (untuk status, meskipun default)
$stmt_pasukan->bind_param(
    "sssssssss", 
    $nama, 
    $tempat_lahir, 
    $tanggal_lahir, 
    $jenis_kelamin, 
    $alamat, 
    $telepon, 
    $email, 
    $encrypted_sim_c, // Konten SIM C terenkripsi
    $uploaded_files['foto_diri']
);

if ($stmt_pasukan->execute()) {
    // Ambil ID dari pendaftar baru
    $rd_id = $conn->insert_id;
    $stmt_pasukan->close(); 
    
    // ==========================================================
    // SQL INSERT: DATA KENDARAAN (Tabel: kendaraan_pasukan)
    // ==========================================================
    
    $sql_kendaraan = "INSERT INTO kendaraan_pasukan (
                        id_pendaftar, merk, plat, foto_kendaraan_path, stnk_encrypted
                    ) VALUES (?, ?, ?, ?, ?)";

    $stmt_kendaraan = $conn->prepare($sql_kendaraan);
    
    if ($stmt_kendaraan === FALSE) {
        die("Error preparing statement (Kendaraan): " . $conn->error);
    }
    
    // Binding parameter: 1x integer (i), 4x string (s)
    $stmt_kendaraan->bind_param(
        "issss", 
        $rd_id, 
        $merk_kendaraan, 
        $plat_kendaraan, 
        $uploaded_files['foto_kendaraan'], // Nama unik file foto kendaraan
        $encrypted_stnk // Konten STNK terenkripsi
    );
    
    if ($stmt_kendaraan->execute()) {
        // Sukses Total
        header("Location: gabung-pasukan.php?status=success");
        $stmt_kendaraan->close();
        $conn->close();
        exit();
    } else {
        // Error menyimpan data kendaraan
        // Opsional: Anda mungkin ingin menghapus baris dari daftar_pasukan jika ini gagal
        header("Location: gabung-pasukan.php?status=error_kendaraan_save");
        $stmt_kendaraan->close();
        $conn->close();
        exit();
    }

} else {
    // Error menyimpan data pribadi
    header("Location: gabung-pasukan.php?status=error_registrasi_save");
    $conn->close();
    exit();
}
?>