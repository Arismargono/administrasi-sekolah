<?php
// Simple landing page to guide users to the correct sections
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Schedule Application - SMA Negeri 6 Surakarta</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        header {
            background-color: #003366;
            color: white;
            padding: 2rem;
            text-align: center;
        }
        header h1 {
            margin-bottom: 0.5rem;
        }
        main {
            flex: 1;
            padding: 2rem;
        }
        .welcome {
            text-align: center;
            margin-bottom: 2rem;
        }
        .welcome h2 {
            color: #003366;
            margin-bottom: 1rem;
        }
        .navigation {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
        }
        .nav-card {
            flex: 1;
            min-width: 250px;
            max-width: 350px;
            margin: 1rem;
            padding: 1.5rem;
            background-color: #eef5ff;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .nav-card h3 {
            color: #003366;
            margin-bottom: 1rem;
        }
        .btn {
            display: inline-block;
            background-color: #004080;
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            transition: background-color 0.3s;
            margin: 5px;
        }
        .btn:hover {
            background-color: #0055aa;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        footer {
            background-color: #003366;
            color: white;
            text-align: center;
            padding: 1rem;
            margin-top: auto;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Jadwal Pelajaran SMA Negeri 6 Surakarta</h1>
            <p>Sistem Informasi Manajemen Jadwal Pelajaran</p>
        </header>
        
        <main>
            <section class="welcome">
                <h2>Selamat Datang</h2>
                <p>Aplikasi ini digunakan untuk mengelola jadwal pelajaran di SMA Negeri 6 Surakarta.</p>
            </section>
            
            <div class="navigation">
                <div class="nav-card">
                    <h3>🏠 Beranda</h3>
                    <p>Halaman utama aplikasi dengan navigasi ke semua fitur.</p>
                    <a href="index.php" class="btn">Akses Beranda</a>
                </div>
                
                <div class="nav-card">
                    <h3>📊 Laporan</h3>
                    <p>Cetak jadwal pelajaran dalam format PDF atau Excel.</p>
                    <a href="laporan.php" class="btn">Akses Laporan</a>
                </div>
                
                <div class="nav-card">
                    <h3>⚙️ Inisialisasi</h3>
                    <p>Inisialisasi database dan tabel aplikasi.</p>
                    <a href="initialize.php" class="btn btn-secondary">Inisialisasi Database</a>
                </div>
            </div>
            
            <div class="info-box" style="background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 1rem; margin-top: 2rem;">
                <h3>Petunjuk Penggunaan</h3>
                <p>1. Pertama, inisialisasi database dengan mengklik tombol "Inisialisasi Database"</p>
                <p>2. Setelah itu, Anda dapat mengakses fitur-fitur aplikasi melalui halaman Beranda</p>
                <p>3. Untuk mencetak laporan jadwal, akses halaman Laporan</p>
            </div>
        </main>
        
        <footer>
            <p>&copy; 2025 SMA Negeri 6 Surakarta - Sistem Jadwal Pelajaran</p>
        </footer>
    </div>
</body>
</html>