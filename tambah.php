<?php
require_once 'config/database.php';

// Initialize database
initializeDatabase();

$conn = getConnection();

// Get classes for dropdown
$kelas_result = $conn->query("SELECT * FROM kelas ORDER BY tingkat, nama_kelas");
$kelas_list = [];
while ($row = $kelas_result->fetch_assoc()) {
    $kelas_list[] = $row;
}

// Get teachers for dropdown
$guru_result = $conn->query("SELECT * FROM guru ORDER BY nama");
$guru_list = [];
while ($row = $guru_result->fetch_assoc()) {
    $guru_list[] = $row;
}

// Handle form submission for adding new schedule
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_jadwal'])) {
    $id_kelas = $_POST['id_kelas'];
    $id_guru = $_POST['id_guru'];
    $hari = $_POST['hari'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $ruangan = $_POST['ruangan'];
    
    // Check if teacher is already scheduled at the same time in another class
    $check_stmt = $conn->prepare("SELECT j.*, k.nama_kelas FROM jadwal j JOIN kelas k ON j.id_kelas = k.id WHERE j.id_guru = ? AND j.hari = ? AND ((j.jam_mulai <= ? AND j.jam_selesai > ?) OR (j.jam_mulai < ? AND j.jam_selesai >= ?))");
    $check_stmt->bind_param("isssss", $id_guru, $hari, $jam_mulai, $jam_mulai, $jam_selesai, $jam_selesai);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        // Teacher is already scheduled at this time
        $conflict = $check_result->fetch_assoc();
        $message = "Guru sudah mengajar di kelas " . $conflict['nama_kelas'] . " pada jam yang sama (" . $hari . " " . date('H:i', strtotime($conflict['jam_mulai'])) . " - " . date('H:i', strtotime($conflict['jam_selesai'])) . ")";
        $message_type = "error";
    } else {
        // No conflict, proceed with insertion
        $stmt = $conn->prepare("INSERT INTO jadwal (id_kelas, id_guru, hari, jam_mulai, jam_selesai, ruangan) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissss", $id_kelas, $id_guru, $hari, $jam_mulai, $jam_selesai, $ruangan);
        
        if ($stmt->execute()) {
            $message = "Jadwal berhasil ditambahkan!";
            $message_type = "success";
        } else {
            $message = "Error: " . $stmt->error;
            $message_type = "error";
        }
        
        $stmt->close();
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
    <title>Tambah Jadwal - SMA Negeri 6 Surakarta</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Jadwal Pelajaran SMA Negeri 6 Surakarta</h1>
            <p>Tambah Jadwal</p>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php">Beranda</a></li>
                <li><a href="jadwal.php">Lihat Jadwal</a></li>
                <li><a href="tambah.php" class="active">Tambah Jadwal</a></li>
                <li><a href="kelas.php">Data Kelas</a></li>
                <li><a href="guru.php">Data Guru</a></li>
            </ul>
        </nav>
        
        <main>
            <h2>Tambah Jadwal Pelajaran</h2>
            
            <?php if (isset($message)): ?>
                <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <div class="info-box">
                <h3>Jam Istirahat</h3>
                <p><strong>Istirahat Ke-1:</strong> 09:15 - 09:30 (Senin-Jumat)</p>
                <p><strong>Istirahat Ke-2:</strong> 11:45 - 12:00 (Senin-Jumat)</p>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label for="id_kelas">Kelas:</label>
                    <select id="id_kelas" name="id_kelas" required>
                        <option value="">Pilih Kelas</option>
                        <?php foreach ($kelas_list as $kelas): ?>
                            <option value="<?php echo $kelas['id']; ?>">
                                <?php echo htmlspecialchars($kelas['nama_kelas'] . ' (' . $kelas['tingkat'] . ' ' . $kelas['jurusan'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="id_guru">Guru:</label>
                    <select id="id_guru" name="id_guru" required>
                        <option value="">Pilih Guru</option>
                        <?php foreach ($guru_list as $guru): ?>
                            <option value="<?php echo $guru['id']; ?>">
                                <?php echo htmlspecialchars($guru['nama'] . ' (' . $guru['mata_pelajaran'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="hari">Hari:</label>
                    <select id="hari" name="hari" required>
                        <option value="">Pilih Hari</option>
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="jam_mulai">Jam Mulai:</label>
                    <input type="time" id="jam_mulai" name="jam_mulai" required>
                </div>
                
                <div class="form-group">
                    <label for="jam_selesai">Jam Selesai:</label>
                    <input type="time" id="jam_selesai" name="jam_selesai" required>
                </div>
                
                <div class="form-group">
                    <label for="ruangan">Ruangan:</label>
                    <input type="text" id="ruangan" name="ruangan" required>
                </div>
                
                <button type="submit" name="add_jadwal" class="btn btn-block">Simpan Jadwal</button>
            </form>
        </main>
        
        <footer>
            <p>&copy; 2025 SMA Negeri 6 Surakarta - Sistem Jadwal Pelajaran</p>
        </footer>
    </div>
</body>
</html>