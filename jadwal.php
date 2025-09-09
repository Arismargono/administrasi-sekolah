<?php
require_once 'config/database.php';

// Initialize database
initializeDatabase();

$conn = getConnection();

// Get all classes
$kelas_result = $conn->query("SELECT * FROM kelas ORDER BY tingkat, nama_kelas");
$kelas_list = [];
while ($row = $kelas_result->fetch_assoc()) {
    $kelas_list[] = $row;
}

// Get selected class schedule if class ID is provided
$selected_kelas = null;
$jadwal_by_day = [];
$message = '';

if (isset($_GET['id_kelas']) && !empty($_GET['id_kelas'])) {
    $id_kelas = $_GET['id_kelas'];
    
    // Get class information
    $stmt = $conn->prepare("SELECT * FROM kelas WHERE id = ?");
    $stmt->bind_param("i", $id_kelas);
    $stmt->execute();
    $result = $stmt->get_result();
    $selected_kelas = $result->fetch_assoc();
    $stmt->close();
    
    if ($selected_kelas) {
        // Get schedule for the class
        $stmt = $conn->prepare("SELECT j.*, g.nama as nama_guru, g.mata_pelajaran 
                               FROM jadwal j 
                               JOIN guru g ON j.id_guru = g.id 
                               WHERE j.id_kelas = ? 
                               ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), j.jam_mulai");
        $stmt->bind_param("i", $id_kelas);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $jadwal_list = [];
        while ($row = $result->fetch_assoc()) {
            $jadwal_list[] = $row;
        }
        $stmt->close();
        
        // Group schedule by day
        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        foreach ($days as $day) {
            $jadwal_by_day[$day] = [];
        }
        
        foreach ($jadwal_list as $jadwal) {
            $jadwal_by_day[$jadwal['hari']][] = $jadwal;
        }
        
        // Function to insert break times into the schedule
        function insertBreakTimes($schedule) {
            // Break 1: 09:15 - 09:30 (only for weekdays)
            $break1 = [
                'jam_mulai' => '09:15:00',
                'jam_selesai' => '09:30:00',
                'is_break' => true,
                'break_name' => 'Istirahat Ke-1'
            ];
            
            // Break 2: 11:45 - 12:00 (only for weekdays)
            $break2 = [
                'jam_mulai' => '11:45:00',
                'jam_selesai' => '12:00:00',
                'is_break' => true,
                'break_name' => 'Istirahat Ke-2'
            ];
            
            // Combine all items (schedule items and breaks)
            $all_items = array_merge($schedule, [$break1, $break2]);
            
            // Sort by time
            usort($all_items, function($a, $b) {
                return strtotime($a['jam_mulai']) - strtotime($b['jam_mulai']);
            });
            
            return $all_items;
        }
        
        // Apply break times to each day's schedule (only for weekdays)
        foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $day) {
            if (!empty($jadwal_by_day[$day])) {
                $jadwal_by_day[$day] = insertBreakTimes($jadwal_by_day[$day]);
            } else {
                // If no schedule for the day, still show break times
                $jadwal_by_day[$day] = [
                    [
                        'jam_mulai' => '09:15:00',
                        'jam_selesai' => '09:30:00',
                        'is_break' => true,
                        'break_name' => 'Istirahat Ke-1'
                    ],
                    [
                        'jam_mulai' => '11:45:00',
                        'jam_selesai' => '12:00:00',
                        'is_break' => true,
                        'break_name' => 'Istirahat Ke-2'
                    ]
                ];
            }
        }
    } else {
        $message = "Kelas tidak ditemukan.";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lihat Jadwal - SMA Negeri 6 Surakarta</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Jadwal Pelajaran SMA Negeri 6 Surakarta</h1>
            <p>Lihat Jadwal</p>
        </header>
        
        <nav>
            <ul>
                <li><a href="index.php">Beranda</a></li>
                <li><a href="jadwal.php" class="active">Lihat Jadwal</a></li>
                <li><a href="tambah.php">Tambah Jadwal</a></li>
                <li><a href="kelas.php">Data Kelas</a></li>
                <li><a href="guru.php">Data Guru</a></li>
                <li><a href="laporan.php">Laporan</a></li>
            </ul>
        </nav>
        
        <main>
            <h2>Lihat Jadwal Pelajaran</h2>
            
            <?php if ($message): ?>
                <div class="message error"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <form method="GET" class="form-inline">
                <div class="form-group">
                    <label for="id_kelas">Pilih Kelas:</label>
                    <select name="id_kelas" id="id_kelas" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($kelas_list as $kelas): ?>
                            <option value="<?php echo $kelas['id']; ?>" <?php echo (isset($_GET['id_kelas']) && $_GET['id_kelas'] == $kelas['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($kelas['tingkat'] . ' ' . $kelas['nama_kelas'] . ' ' . $kelas['jurusan']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn">Lihat Jadwal</button>
                <a href="jadwal.php" class="btn btn-secondary">Reset</a>
            </form>
            
            <?php if ($selected_kelas): ?>
                <div class="info-box">
                    <h3>Jadwal Kelas: <?php echo htmlspecialchars($selected_kelas['tingkat'] . ' ' . $selected_kelas['nama_kelas'] . ' ' . $selected_kelas['jurusan']); ?></h3>
                </div>
                
                <?php foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $day): ?>
                    <h3>Jadwal Hari <?php echo $day; ?></h3>
                    <?php if (!empty($jadwal_by_day[$day])): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th width="15%">Jam</th>
                                    <th width="25%">Mata Pelajaran</th>
                                    <th width="30%">Guru</th>
                                    <th width="15%">Ruangan</th>
                                    <th width="10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($jadwal_by_day[$day] as $index => $jadwal): ?>
                                    <tr>
                                        <td><?php echo $index + 1; ?></td>
                                        <td><?php echo date('H:i', strtotime($jadwal['jam_mulai'])) . " - " . date('H:i', strtotime($jadwal['jam_selesai'])); ?></td>
                                        <?php if (isset($jadwal['is_break']) && $jadwal['is_break']): ?>
                                            <td colspan="3" class="break-row"><?php echo htmlspecialchars($jadwal['break_name']); ?></td>
                                        <?php else: ?>
                                            <td><?php echo htmlspecialchars($jadwal['mata_pelajaran']); ?></td>
                                            <td><?php echo htmlspecialchars($jadwal['nama_guru']); ?></td>
                                            <td><?php echo htmlspecialchars($jadwal['ruangan']); ?></td>
                                        <?php endif; ?>
                                        <td>
                                            <?php if (!isset($jadwal['is_break']) || !$jadwal['is_break']): ?>
                                                <a href="edit_jadwal.php?id=<?php echo $jadwal['id']; ?>" class="btn btn-small">Edit</a>
                                                <a href="hapus_jadwal.php?id=<?php echo $jadwal['id']; ?>" class="btn btn-small btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?')">Hapus</a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>Tidak ada jadwal untuk hari <?php echo $day; ?>.</p>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
        
        <footer>
            <p>&copy; 2025 SMA Negeri 6 Surakarta - Sistem Jadwal Pelajaran</p>
        </footer>
    </div>
</body>
</html>