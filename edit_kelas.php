<?php
require_once 'config/database.php';

// Initialize database
initializeDatabase();

$conn = getConnection();

// Get all teachers for dropdown
$guru_result = $conn->query("SELECT id, nama FROM guru ORDER BY nama");
$guru_list = [];
while ($row = $guru_result->fetch_assoc()) {
    $guru_list[] = $row;
}

// Get class ID from URL
$id = isset($_GET['id']) ? $_GET['id'] : null;

// Redirect if no ID provided
if (!$id) {
    header("Location: kelas.php");
    exit();
}

// Get current class data
$stmt = $conn->prepare("SELECT * FROM kelas WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$kelas = $result->fetch_assoc();
$stmt->close();

// If class not found
if (!$kelas) {
    header("Location: kelas.php");
    exit();
}

// Handle form submission for updating class
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_kelas'])) {
    $nama_kelas = $_POST['nama_kelas'];
    $tingkat = $_POST['tingkat'];
    $jurusan = $_POST['jurusan'];
    $wali_kelas_id = !empty($_POST['wali_kelas_id']) ? $_POST['wali_kelas_id'] : null;
    
    // Check if another class already has this name (excluding current class)
    $check_stmt = $conn->prepare("SELECT id FROM kelas WHERE nama_kelas = ? AND id != ?");
    $check_stmt->bind_param("si", $nama_kelas, $id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Class name already exists for another class
        $message = "Nama kelas sudah digunakan oleh kelas lain!";
        $message_type = "error";
    } else {
        // No conflict, proceed with update
        if ($wali_kelas_id) {
            $update_stmt = $conn->prepare("UPDATE kelas SET nama_kelas = ?, tingkat = ?, jurusan = ?, wali_kelas_id = ? WHERE id = ?");
            $update_stmt->bind_param("ssssi", $nama_kelas, $tingkat, $jurusan, $wali_kelas_id, $id);
        } else {
            $update_stmt = $conn->prepare("UPDATE kelas SET nama_kelas = ?, tingkat = ?, jurusan = ?, wali_kelas_id = NULL WHERE id = ?");
            $update_stmt->bind_param("sssi", $nama_kelas, $tingkat, $jurusan, $id);
        }
        
        if ($update_stmt->execute()) {
            $message = "Data kelas berhasil diperbarui!";
            $message_type = "success";
            
            // Refresh the class data
            $stmt = $conn->prepare("SELECT * FROM kelas WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $kelas = $result->fetch_assoc();
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
    <title>Edit Data Kelas - SMA Negeri 6 Surakarta</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Jadwal Pelajaran SMA Negeri 6 Surakarta</h1>
            <p>Edit Data Kelas</p>
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
            <h2>Edit Data Kelas</h2>
            
            <?php if (isset($message)): ?>
                <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="nama_kelas">Nama Kelas:</label>
                    <input type="text" id="nama_kelas" name="nama_kelas" value="<?php echo htmlspecialchars($kelas['nama_kelas']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="tingkat">Tingkat:</label>
                    <select id="tingkat" name="tingkat" required>
                        <option value="">Pilih Tingkat</option>
                        <option value="X" <?php echo ($kelas['tingkat'] == 'X') ? 'selected' : ''; ?>>X (Sepuluh)</option>
                        <option value="XI" <?php echo ($kelas['tingkat'] == 'XI') ? 'selected' : ''; ?>>XI (Sebelas)</option>
                        <option value="XII" <?php echo ($kelas['tingkat'] == 'XII') ? 'selected' : ''; ?>>XII (Dua Belas)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="jurusan">Jurusan:</label>
                    <input type="text" id="jurusan" name="jurusan" value="<?php echo htmlspecialchars($kelas['jurusan']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="wali_kelas_id">Wali Kelas:</label>
                    <select id="wali_kelas_id" name="wali_kelas_id">
                        <option value="">Pilih Wali Kelas (Opsional)</option>
                        <?php foreach ($guru_list as $guru): ?>
                            <option value="<?php echo $guru['id']; ?>" <?php echo ($kelas['wali_kelas_id'] == $guru['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($guru['nama']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" name="update_kelas" class="btn btn-block">Perbarui Data Kelas</button>
                <a href="kelas.php" class="btn btn-block" style="text-align: center; margin-top: 10px; background-color: #6c757d;">Batal</a>
            </form>
        </main>
        
        <footer>
            <p>&copy; 2025 SMA Negeri 6 Surakarta - Sistem Jadwal Pelajaran</p>
        </footer>
    </div>
</body>
</html>