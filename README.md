# Aplikasi Jadwal Pelajaran SMA Negeri 6 Surakarta

Aplikasi ini digunakan untuk mengelola jadwal pelajaran di SMA Negeri 6 Surakarta.

## Fitur Utama

1. **Lihat Jadwal** - Melihat jadwal pelajaran per kelas
2. **Tambah Jadwal** - Menambahkan jadwal pelajaran baru
3. **Data Kelas** - Mengelola data kelas
4. **Data Guru** - Mengelola data guru dengan fitur import CSV
5. **Laporan** - Mencetak jadwal pelajaran dalam format PDF dan Excel

## Kebutuhan Sistem

- PHP 7.0 atau lebih tinggi
- MySQL atau MariaDB
- Web Server (Apache/Nginx)

## Instalasi

1. Clone atau download repository ini
2. Letakkan file-file di dalam folder web server (htdocs untuk XAMPP)
3. Jalankan file `init.php` untuk menginisialisasi database
4. Akses aplikasi melalui browser

## Struktur Database

Aplikasi ini menggunakan 3 tabel utama:

1. **kelas** - Menyimpan data kelas
2. **guru** - Menyimpan data guru
3. **jadwal** - Menyimpan jadwal pelajaran

## Penggunaan

1. **Inisialisasi Database**: Jalankan `init.php` untuk membuat database dan mengisi data sample
2. **Lihat Jadwal**: Pilih kelas di halaman "Lihat Jadwal" untuk melihat jadwal
3. **Tambah Jadwal**: Gunakan halaman "Tambah Jadwal" untuk menambahkan jadwal baru
4. **Kelola Data**: Gunakan halaman "Data Kelas" dan "Data Guru" untuk mengelola data
5. **Cetak Laporan**: Gunakan halaman "Laporan" untuk mencetak jadwal kelas dalam format PDF atau Excel

## Fitur Import CSV pada Data Guru

Halaman "Data Guru" kini memiliki fitur import data guru melalui file CSV. Cara penggunaannya:

1. Klik tombol "Import CSV" di halaman Data Guru
2. Pilih file CSV dengan format: NIP,Nama,Mata Pelajaran,No Telepon
3. Klik tombol "Import CSV" untuk mengimport data

### Template CSV

Anda dapat mengunduh template CSV yang tersedia di halaman "Data Guru" dengan mengklik tombol "Download Template CSV". Template ini berisi format yang benar dan beberapa contoh data untuk memudahkan penggunaan.

Contoh format file CSV:
```
NIP,Nama,Mata Pelajaran,No Telepon
197501012000031001,Budi Santoso,Matematika,081234567890
198005152005021002,Siti Rahayu,Bahasa Indonesia,081234567891
```

## Fitur Laporan

Halaman "Laporan" memungkinkan Anda untuk mencetak jadwal pelajaran untuk setiap kelas:

1. Pilih kelas dari daftar yang tersedia
2. Klik "Cetak PDF" untuk menghasilkan versi PDF yang dapat dicetak
3. Klik "Export Excel" untuk mengunduh jadwal dalam format Excel

Fitur ini mencakup:
- Jadwal dari Senin sampai Jumat
- Waktu istirahat yang ditampilkan (09:15-09:30 dan 11:45-12:00)
- Informasi lengkap tentang mata pelajaran, guru, dan ruangan
- Tanda tangan wali kelas pada versi PDF

## Konfigurasi Database

Konfigurasi database dapat diubah di file `config/database.php`:
- Host: localhost
- Username: root
- Password: (kosong)
- Database: jadwal_sma6ska

## Dikembangkan oleh

Tim IT SMA Negeri 6 Surakarta