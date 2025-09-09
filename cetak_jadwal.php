<?php
require_once 'config/database.php';

// Initialize database
initializeDatabase();

$conn = getConnection();

// Get class ID and format from URL
$id_kelas = isset($_GET['id']) ? $_GET['id'] : null;
$format = isset($_GET['format']) ? $_GET['format'] : 'pdf';

// Redirect if no ID provided
if (!$id_kelas) {
    header("Location: laporan.php");
    exit();
}

// Get class information
$stmt = $conn->prepare("SELECT * FROM kelas WHERE id = ?");
$stmt->bind_param("i", $id_kelas);
$stmt->execute();
$result = $stmt->get_result();
$kelas = $result->fetch_assoc();
$stmt->close();

// If class not found
if (!$kelas) {
    header("Location: laporan.php");
    exit();
}

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

$conn->close();

// Group schedule by day
$jadwal_by_day = [];
$days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];
foreach ($days as $day) {
    $jadwal_by_day[$day] = [];
}

foreach ($jadwal_list as $jadwal) {
    if (in_array($jadwal['hari'], $days)) {
        $jadwal_by_day[$jadwal['hari']][] = $jadwal;
    }
}

// Function to insert break times into the schedule
function insertBreakTimes($schedule) {
    // Break 1: 09:15 - 09:30
    $break1 = [
        'jam_mulai' => '09:15:00',
        'jam_selesai' => '09:30:00',
        'is_break' => true,
        'break_name' => 'Istirahat Ke-1'
    ];
    
    // Break 2: 11:45 - 12:00
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
foreach ($days as $day) {
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

// Generate output based on format
if ($format == 'excel') {
    // Generate Excel file
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment;filename="Jadwal_' . $kelas['nama_kelas'] . '.xls"');
    header('Cache-Control: max-age=0');
    
    echo "<table border='1'>";
    echo "<tr><th colspan='6' style='text-align:center; font-size:16px; font-weight:bold;'>JADWAL PELAJARAN KELAS " . htmlspecialchars($kelas['nama_kelas']) . "</th></tr>";
    echo "<tr><th colspan='6' style='text-align:center;'>SMA Negeri 6 Surakarta</th></tr>";
    echo "<tr><th colspan='6' style='text-align:center;'>&nbsp;</th></tr>";
    
    foreach ($days as $day) {
        echo "<tr><th colspan='6' style='text-align:left; background-color:#e0e0e0;'>HARI " . strtoupper($day) . "</th></tr>";
        echo "<tr>";
        echo "<th style='background-color:#f0f0f0;'>No</th>";
        echo "<th style='background-color:#f0f0f0;'>Jam</th>";
        echo "<th style='background-color:#f0f0f0;'>Mata Pelajaran</th>";
        echo "<th style='background-color:#f0f0f0;'>Guru</th>";
        echo "<th style='background-color:#f0f0f0;'>Ruangan</th>";
        echo "</tr>";
        
        if (!empty($jadwal_by_day[$day])) {
            foreach ($jadwal_by_day[$day] as $index => $jadwal) {
                echo "<tr>";
                echo "<td>" . ($index + 1) . "</td>";
                echo "<td>" . date('H:i', strtotime($jadwal['jam_mulai'])) . " - " . date('H:i', strtotime($jadwal['jam_selesai'])) . "</td>";
                
                if (isset($jadwal['is_break']) && $jadwal['is_break']) {
                    echo "<td colspan='3' style='text-align:center; font-weight:bold;'>" . htmlspecialchars($jadwal['break_name']) . "</td>";
                } else {
                    echo "<td>" . htmlspecialchars($jadwal['mata_pelajaran']) . "</td>";
                    echo "<td>" . htmlspecialchars($jadwal['nama_guru']) . "</td>";
                    echo "<td>" . htmlspecialchars($jadwal['ruangan']) . "</td>";
                }
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6' style='text-align:center;'>Tidak ada jadwal</td></tr>";
        }
        echo "<tr><td colspan='6' style='text-align:center;'>&nbsp;</td></tr>";
    }
    
    echo "</table>";
    exit();
} else {
    // Generate HTML for PDF printing
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Jadwal <?php echo htmlspecialchars($kelas['nama_kelas']); ?> - SMA Negeri 6 Surakarta</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                font-size: 12px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            th, td {
                border: 1px solid #000;
                padding: 5px;
                text-align: left;
            }
            th {
                background-color: #f0f0f0;
                font-weight: bold;
            }
            .header {
                text-align: center;
                margin-bottom: 20px;
            }
            .header h1 {
                margin: 0;
                font-size: 18px;
            }
            .header h2 {
                margin: 5px 0;
                font-size: 16px;
            }
            .day-header {
                background-color: #e0e0e0;
                font-weight: bold;
                text-align: left;
            }
            .break-row {
                font-weight: bold;
                text-align: center;
                background-color: #ffffcc;
            }
            @media print {
                body {
                    font-size: 10px;
                }
                th, td {
                    padding: 3px;
                }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>JADWAL PELAJARAN KELAS <?php echo htmlspecialchars($kelas['nama_kelas']); ?></h1>
            <h2>SMA NEGERI 6 SURAKARTA</h2>
            <h3>TAHUN AJARAN 2025/2026</h3>
        </div>
        
        <?php foreach ($days as $day): ?>
            <table>
                <tr>
                    <th colspan="6" class="day-header">HARI <?php echo strtoupper($day); ?></th>
                </tr>
                <tr>
                    <th width="5%">No</th>
                    <th width="15%">Jam</th>
                    <th width="25%">Mata Pelajaran</th>
                    <th width="30%">Guru</th>
                    <th width="15%">Ruangan</th>
                </tr>
                <?php if (!empty($jadwal_by_day[$day])): ?>
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
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Tidak ada jadwal</td>
                    </tr>
                <?php endif; ?>
            </table>
        <?php endforeach; ?>
        
        <div style="margin-top: 30px; text-align: right;">
            <p>Surakarta, <?php echo date('d F Y'); ?></p>
            <p>Wali Kelas,</p>
            <br><br><br>
            <p>______________________</p>
        </div>
        
        <script>
            window.onload = function() {
                window.print();
            }
        </script>
    </body>
    </html>
    <?php
    exit();
}
?>