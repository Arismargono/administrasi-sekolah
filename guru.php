<?php
require_once 'config/database.php';

// Initialize database
initializeDatabase();

$conn = getConnection();

// Handle Excel export
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    // Fetch all teachers
    $result = $conn->query("SELECT * FROM guru ORDER BY nama");
    $guru_list = [];
    while ($row = $result->fetch_assoc()) {
        $guru_list[] = $row;
    }
    
    // Set headers for Excel export
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="Data_Guru.xls"');
    header('Cache-Control: max-age=0');
    
    // Output Excel table
    echo "<table border='1'>";
    echo "<tr><th colspan='5' style='text-align:center; font-size:16px; font-weight:bold;'>DATA GURU SMA NEGERI 6 SURAKARTA</th></tr>";
    echo "<tr><th colspan='5' style='text-align:center;'>&nbsp;</th></tr>";
    echo "<tr>";
    echo "<th style='background-color:#f0f0f0;'>No</th>";
    echo "<th style='background-color:#f0f0f0;'>NIP</th>";
    echo "<th style='background-color:#f0f0f0;'>Nama</th>";
    echo "<th style='background-color:#f0f0f0;'>Mata Pelajaran</th>";
    echo "<th style='background-color:#f0f0f0;'>No. Telepon</th>";
    echo "</tr>";
    
    foreach ($guru_list as $index => $guru) {
        echo "<tr>";
        echo "<td>" . ($index + 1) . "</td>";
        echo "<td>" . htmlspecialchars($guru['nip']) . "</td>";
        echo "<td>" . htmlspecialchars($guru['nama']) . "</td>";
        echo "<td>" . htmlspecialchars($guru['mata_pelajaran']) . "</td>";
        echo "<td>" . htmlspecialchars($guru['no_telepon']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    exit();
}

// Handle form submission for adding new teacher
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_guru'])) {
    $nip = $_POST['nip'];
    $nama = $_POST['nama'];
    $mata_pelajaran = $_POST['mata_pelajaran'];
    $no_telepon = $_POST['no_telepon'];
    
    $stmt = $conn->prepare("INSERT INTO guru (nip, nama, mata_pelajaran, no_telepon) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nip, $nama, $mata_pelajaran, $no_telepon);
    
    if ($stmt->execute()) {
        $message = "Guru berhasil ditambahkan!";
        $message_type = "success";
    } else {
        $message = "Error: " . $stmt->error;
        $message_type = "error";
    }
    
    $stmt->close();
}

// Handle CSV import
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['import_csv'])) {
    if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] == 0) {
        $file = $_FILES['csv_file']['tmp_name'];
        $handle = fopen($file, "r");
        $import_count = 0;
        $error_count = 0;
        
        // Skip header row
        fgetcsv($handle, 1000, ",");
        
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (count($data) >= 4) {
                $nip = trim($data[0]);
                $nama = trim($data[1]);
                $mata_pelajaran = trim($data[2]);
                $no_telepon = trim($data[3]);
                
                // Check if teacher with same NIP already exists
                $check_stmt = $conn->prepare("SELECT id FROM guru WHERE nip = ?");
                $check_stmt->bind_param("s", $nip);
                $check_stmt->execute();
                $check_result = $check_stmt->get_result();
                
                if ($check_result->num_rows == 0) {
                    // Insert new teacher
                    $stmt = $conn->prepare("INSERT INTO guru (nip, nama, mata_pelajaran, no_telepon) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssss", $nip, $nama, $mata_pelajaran, $no_telepon);
                    
                    if ($stmt->execute()) {
                        $import_count++;
                    } else {
                        $error_count++;
                    }
                    $stmt->close();
                } else {
                    $error_count++;
                }
                $check_stmt->close();
            }
        }
        
        fclose($handle);
        $message = "Import selesai: $import_count data berhasil diimport, $error_count data gagal.";
        $message_type = "success";
    } else {
        $message = "Error uploading file.";
        $message_type = "error";
    }
}

// Handle deletion
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM guru WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $message = "Guru berhasil dihapus!";
        $message_type = "success";
    } else {
        $message = "Error: " . $stmt->error;
        $message_type = "error";
    }
    
    $stmt->close();
}

// Fetch all teachers
$result = $conn->query("SELECT * FROM guru ORDER BY nama");
$guru_list = [];
while ($row = $result->fetch_assoc()) {
    $guru_list[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Guru - SMA Negeri 6 Surakarta</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Jadwal Pelajaran SMA Negeri 6 Surakarta</h1>
            <p>Data Guru</p>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php">Beranda</a></li>
                <li><a href="jadwal.php">Lihat Jadwal</a></li>
                <li><a href="tambah.php">Tambah Jadwal</a></li>
                <li><a href="kelas.php">Data Kelas</a></li>
                <li><a href="guru.php" class="active">Data Guru</a></li>
            </ul>
        </nav>
        
        <main>
            <h2>Daftar Guru</h2>
            
            <?php if (isset($message)): ?>
                <div class="message <?php echo $message_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <div class="actions">
                <button class="btn" onclick="showAddForm()">Tambah Guru</button>
                <button class="btn" onclick="showImportForm()">Import CSV</button>
                <a href="sample_guru.csv" class="btn" download>Download Template CSV</a>
                <a href="?export=excel" class="btn">Export ke Excel</a>
            </div>
            
            <div id="addForm" style="display: none;">
                <h3>Tambah Guru Baru</h3>
                <form method="POST">
                    <div class="form-group">
                        <label for="nip">NIP:</label>
                        <input type="text" id="nip" name="nip" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="nama">Nama:</label>
                        <input type="text" id="nama" name="nama" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="mata_pelajaran">Mata Pelajaran:</label>
                        <input type="text" id="mata_pelajaran" name="mata_pelajaran" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="no_telepon">No. Telepon:</label>
                        <input type="text" id="no_telepon" name="no_telepon">
                    </div>
                    
                    <button type="submit" name="add_guru" class="btn btn-block">Simpan Guru</button>
                </form>
            </div>
            
            <div id="importForm" style="display: none;">
                <h3>Import Data Guru dari CSV</h3>
                <p>Format CSV: NIP,Nama,Mata Pelajaran,No Telepon</p>
                <p><a href="sample_guru.csv" download>Download template CSV</a> untuk melihat format yang benar.</p>
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="csv_file">Pilih File CSV:</label>
                        <input type="file" id="csv_file" name="csv_file" accept=".csv" required>
                    </div>
                    
                    <button type="submit" name="import_csv" class="btn btn-block">Import CSV</button>
                </form>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Mata Pelajaran</th>
                        <th>No. Telepon</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($guru_list)): ?>
                        <tr>
                            <td colspan="6">Tidak ada data guru</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($guru_list as $index => $guru): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($guru['nip']); ?></td>
                                <td><?php echo htmlspecialchars($guru['nama']); ?></td>
                                <td><?php echo htmlspecialchars($guru['mata_pelajaran']); ?></td>
                                <td><?php echo htmlspecialchars($guru['no_telepon']); ?></td>
                                <td>
                                    <a href="edit_guru.php?id=<?php echo $guru['id']; ?>" class="btn">Edit</a>
                                    <a href="?delete=<?php echo $guru['id']; ?>" onclick="return confirm('Apakah Anda yakin ingin menghapus guru ini?')" class="btn btn-delete">Hapus</a>
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
        
        function showImportForm() {
            var form = document.getElementById('importForm');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</body>
</html>