<?php
require_once 'config/database.php';

// Initialize database
initializeDatabase();

$conn = getConnection();

// Get all classes with wali kelas information
$kelas_result = $conn->query("SELECT k.*, g.nama as nama_wali_kelas FROM kelas k LEFT JOIN guru g ON k.wali_kelas_id = g.id ORDER BY k.tingkat, k.nama_kelas");
$kelas_list = [];
while ($row = $kelas_result->fetch_assoc()) {
    $kelas_list[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Jadwal - SMA Negeri 6 Surakarta</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Jadwal Pelajaran SMA Negeri 6 Surakarta</h1>
            <p>Laporan Jadwal</p>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php">Beranda</a></li>
                <li><a href="jadwal.php">Lihat Jadwal</a></li>
                <li><a href="tambah.php">Tambah Jadwal</a></li>
                <li><a href="kelas.php">Data Kelas</a></li>
                <li><a href="guru.php">Data Guru</a></li>
                <li><a href="laporan.php" class="active">Laporan</a></li>
            </ul>
        </nav>
        
        <main>
            <h2>Laporan Jadwal Pelajaran</h2>
            
            <div class="info-box">
                <h3>Petunjuk Penggunaan</h3>
                <p>Pilih kelas untuk menghasilkan laporan jadwal pelajaran dari Senin sampai Jumat.</p>
                <p>Anda dapat mencetak laporan dalam format PDF atau mengekspor ke Excel.</p>
            </div>
            
            <div class="report-options">
                <h3>Daftar Kelas</h3>
                <?php if (empty($kelas_list)): ?>
                    <p>Tidak ada data kelas. Silakan tambahkan kelas terlebih dahulu.</p>
                    <a href="kelas.php" class="btn">Tambah Kelas</a>
                <?php else: ?>
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
                            <?php foreach ($kelas_list as $index => $kelas): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo htmlspecialchars($kelas['nama_kelas']); ?></td>
                                    <td><?php echo htmlspecialchars($kelas['tingkat']); ?></td>
                                    <td><?php echo htmlspecialchars($kelas['jurusan']); ?></td>
                                    <td><?php echo htmlspecialchars($kelas['nama_wali_kelas'] ?? '-'); ?></td>
                                    <td>
                                        <a href="cetak_jadwal.php?id=<?php echo $kelas['id']; ?>&format=pdf" class="btn" target="_blank">Cetak PDF</a>
                                        <a href="cetak_jadwal.php?id=<?php echo $kelas['id']; ?>&format=excel" class="btn" target="_blank">Export Excel</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
        
        <footer>
            <p>&copy; 2025 SMA Negeri 6 Surakarta - Sistem Jadwal Pelajaran</p>
        </footer>
    </div>
</body>
</html>