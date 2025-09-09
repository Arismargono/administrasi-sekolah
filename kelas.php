<?php
require_once 'config/database.php';

// Initialize database
initializeDatabase();

$conn = getConnection();

// Handle Excel export
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    // Fetch all classes with wali kelas information
    $result = $conn->query("SELECT k.*, g.nama as nama_wali_kelas FROM kelas k LEFT JOIN guru g ON k.wali_kelas_id = g.id ORDER BY k.tingkat, k.nama_kelas");
    $kelas_list = [];
    while ($row = $result->fetch_assoc()) {
        $kelas_list[] = $row;
    }
    
    // Set headers for Excel export
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="Data_Kelas.xls"');
    header('Cache-Control: max-age=0');
    
    // Output Excel table
    echo "<table border='1'>";
    echo "<tr><th colspan='6' style='text-align:center; font-size:16px; font-weight:bold;'>DATA KELAS SMA NEGERI 6 SURAKARTA</th></tr>";
    echo "<tr><th colspan='6' style='text-align:center;'>&nbsp;</th></tr>";
    echo "<tr>";
    echo "<th style='background-color:#f0f0f0;'>No</th>";
    echo "<th style='background-color:#f0f0f0;'>Nama Kelas</th>";
    echo "<th style='background-color:#f0f0f0;'>Tingkat</th>";
    echo "<th style='background-color:#f0f0f0;'>Jurusan</th>";
    echo "<th style='background-color:#f0f0f0;'>Wali Kelas</th>";
    echo "</tr>";
    
    foreach ($kelas_list as $index => $kelas) {
        echo "<tr>";
        echo "<td>" . ($index + 1) . "</td>";
        echo "<td>" . htmlspecialchars($kelas['nama_kelas']) . "</td>";
        echo "<td>" . htmlspecialchars($kelas['tingkat']) . "</td>";
        echo "<td>" . htmlspecialchars($kelas['jurusan']) . "</td>";
        echo "<td>" . htmlspecialchars($kelas['nama_wali_kelas'] ?? '-') . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    exit();
}

$conn = getConnection();

// Get all teachers for dropdown
$guru_result = $conn->query("SELECT id, nama FROM guru ORDER BY nama");
$guru_list = [];
while ($row = $guru_result->fetch_assoc()) {
    $guru_list[] = $row;
}

// Handle form submission for adding new class
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_kelas'])) {
    $nama_kelas = $_POST['nama_kelas'];
    $tingkat = $_POST['tingkat'];
    $jurusan = $_POST['jurusan'];
    $wali_kelas_id = !empty($_POST['wali_kelas_id']) ? $_POST['wali_kelas_id'] : null;
    
    if ($wali_kelas_id) {
        $stmt = $conn->prepare("INSERT INTO kelas (nama_kelas, tingkat, jurusan, wali_kelas_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $nama_kelas, $tingkat, $jurusan, $wali_kelas_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO kelas (nama_kelas, tingkat, jurusan) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nama_kelas, $tingkat, $jurusan);
    }
    
    if ($stmt->execute()) {
        $message = "Kelas berhasil ditambahkan!";
        $message_type = "success";
    } else {
        $message = "Error: " . $stmt->error;
        $message_type = "error";
    }
    
    $stmt->close();
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM kelas WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "Kelas berhasil dihapus!";
        $message_type = "success";
    } else {
        $message = "Error: " . $stmt->error;
        $message_type = "error";
    }
    
    $stmt->close();
}

// Fetch all classes with wali kelas information
$result = $conn->query("SELECT k.*, g.nama as nama_wali_kelas FROM kelas k LEFT JOIN guru g ON k.wali_kelas_id = g.id ORDER BY k.tingkat, k.nama_kelas");
$kelas_list = [];
while ($row = $result->fetch_assoc()) {
    $kelas_list[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kelas - SMA Negeri 6 Surakarta</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Jadwal Pelajaran SMA Negeri 6 Surakarta</h1>
            <p>Data Kelas</p>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php">Beranda</a></li>
                <li><a href="jadwal.php">Lihat Jadwal</a></li>
                <li><a href="tambah.php">Tambah Jadwal</a></li>
                <li><a href="kelas.php" class="active">Data Kelas</a></li>
                <li><a href="guru.php">Data Guru</a></li>
            </ul>
        </nav>
        
        <main>
            <h2>Daftar Kelas</h2>
            
            <?php if (isset($message)): ?>
                <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <div class="actions">
                <button class="btn" onclick="showAddForm()">Tambah Kelas</button>
                <a href="?export=excel" class="btn">Export ke Excel</a>
            </div>
            
            <div id="addForm" style="display: none;">
                <h3>Tambah Kelas Baru</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="nama_kelas">Nama Kelas:</label>
                        <input type="text" id="nama_kelas" name="nama_kelas" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="tingkat">Tingkat:</label>
                        <select id="tingkat" name="tingkat" required>
                            <option value="">Pilih Tingkat</option>
                            <option value="X">X (Sepuluh)</option>
                            <option value="XI">XI (Sebelas)</option>
                            <option value="XII">XII (Dua Belas)</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="jurusan">Jurusan:</label>
                        <input type="text" id="jurusan" name="jurusan" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="wali_kelas_id">Wali Kelas:</label>
                        <select id="wali_kelas_id" name="wali_kelas_id">
                            <option value="">Pilih Wali Kelas (Opsional)</option>
                            <?php foreach ($guru_list as $guru): ?>
                                <option value="<?php echo $guru['id']; ?>"><?php echo htmlspecialchars($guru['nama']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" name="add_kelas" class="btn btn-block">Simpan Kelas</button>
                </form>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Kelas</th>
                        <th>Tingkat</th>
                        <th>Jurusan</th>
                        <th>Wali Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($kelas_list)): ?>
                        <tr>
                            <td colspan="6">Tidak ada data kelas</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($kelas_list as $index => $kelas): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($kelas['nama_kelas']); ?></td>
                                <td><?php echo htmlspecialchars($kelas['tingkat']); ?></td>
                                <td><?php echo htmlspecialchars($kelas['jurusan']); ?></td>
                                <td><?php echo htmlspecialchars($kelas['nama_wali_kelas'] ?? '-'); ?></td>
                                <td>
                                    <a href="edit_kelas.php?id=<?php echo $kelas['id']; ?>" class="btn">Edit</a>
                                    <a href="?delete=<?php echo $kelas['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus kelas ini?')" class="btn btn-delete">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
        
        <footer>
            <p>&copy; 2025 SMA Negeri 6 Surakarta - Sistem Jadwal Pelajaran</p>
        </footer>
    </div>
    
    <script>
        function showAddForm() {
            var form = document.getElementById('addForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>