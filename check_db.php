<?php
require_once 'config/database.php';

// Initialize database
initializeDatabase();

$conn = getConnection();

echo "Database connection successful!\n";

// Check tables
$tables = ['kelas', 'guru', 'jadwal'];
foreach ($tables as $table) {
    $result = $conn->query("SELECT COUNT(*) as count FROM $table");
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Table $table: " . $row['count'] . " records\n";
    } else {
        echo "Error checking table $table: " . $conn->error . "\n";
    }
}

// Check if wali_kelas_id column exists
$check_column = "SHOW COLUMNS FROM kelas LIKE 'wali_kelas_id'";
$result = $conn->query($check_column);
if ($result && $result->num_rows > 0) {
    echo "Column wali_kelas_id exists in kelas table\n";
} else {
    echo "Column wali_kelas_id does not exist in kelas table\n";
}

$conn->close();
?>