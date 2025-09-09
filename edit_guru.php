<?php
require_once 'config/database.php';

// Initialize database
initializeDatabase();

$conn = getConnection();

// Get teacher ID from URL
$id = isset($_GET['id']) ? $_GET['id'] : null;

// Redirect if no ID provided
if (!$id) {
    header("Location: guru.php");
    exit();
}

// Get current teacher data
$stmt = $conn->prepare("SELECT * FROM guru WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$guru = $result->fetch_assoc();
$stmt->close();

// If teacher not found
if (!$guru) {
    header("Location: guru.php");
    exit();
}

// Handle form submission for updating teacher
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_guru'])) {
    $nip = $_POST['nip'];
    $nama = $_POST['nama'];
    $mata_pelajaran = $_POST['mata_pelajaran'];
    $no_telepon = $_POST['no_telepon'];
    
    // Check if another teacher already has this NIP (excluding current teacher)
    $check_stmt = $conn->prepare("SELECT id FROM guru WHERE nip = ? AND id != ?");
    $check_stmt->bind_param("si", $nip, $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // NIP already exists for another teacher
        $message = "NIP sudah digunakan oleh guru lain!";
        $message_type = "error";
    } else {
        // No conflict, proceed with update
        $update_stmt = $conn->prepare("UPDATE guru SET nip = ?, nama = ?, mata_pelajaran = ?, no_telepon = ? WHERE id = ?");
        $update_stmt->bind_param("ssssi", $nip, $nama, $mata_pelajaran, $no_telepon, $id);
        
        if ($update_stmt->execute()) {
            $message = "Data guru berhasil diperbarui!";
            $message_type = "success";
            
            // Refresh the teacher data
            $stmt = $conn->prepare("SELECT * FROM guru WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $guru = $result->fetch_assoc();
            $stmt->close();
        } else {
            $message = "Error: " . $update_stmt->error;
            $message_type = "error";
        }
        
        $update_stmt->close();
    }
    
    $check_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Guru - SMA Negeri 6 Surakarta</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Jadwal Pelajaran SMA Negeri 6 Surakarta</h1>
            <p>Edit Data Guru</p>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php">Beranda</a></li>
                <li><a href="jadwal.php">Lihat Jadwal</a></li>
                <li><a href="tambah.php">Tambah Jadwal</a></li>
                <li><a href="kelas.php">Data Kelas</a></li>
                <li><a href="guru.php">Data Guru</a></li>
            </ul>
        </nav>
        
        <main>
            <h2>Edit Data Guru</h2>
            
            <?php if (isset($message)): ?>
                <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="nip">NIP:</label>
                    <input type="text" id="nip" name="nip" value="<?php echo htmlspecialchars($guru['nip']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="nama">Nama:</label>
                    <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($guru['nama']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="mata_pelajaran">Mata Pelajaran:</label>
                    <input type="text" id="mata_pelajaran" name="mata_pelajaran" value="<?php echo htmlspecialchars($guru['mata_pelajaran']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="no_telepon">No. Telepon:</label>
                    <input type="text" id="no_telepon" name="no_telepon" value="<?php echo htmlspecialchars($guru['no_telepon']); ?>">
                </div>
                
                <button type="submit" name="update_guru" class="btn btn-block">Perbarui Data Guru</button>
                <a href="guru.php" class="btn btn-block" style="text-align: center; margin-top: 10px; background-color: #6c757d;">Batal</a>
            </form>
        </main>
        
        <footer>
            <p>&copy; 2025 SMA Negeri 6 Surakarta - Sistem Jadwal Pelajaran</p>
        </footer>
    </div>
</body>
</html>