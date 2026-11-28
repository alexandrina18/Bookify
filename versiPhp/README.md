# Perpustakaan SMA GLITZ (PHP & SQLite)

Sistem peminjaman perpustakaan berbasis instansi untuk **SMA GLITZ**. Buka `http://localhost/Bookify/versiPhp/index.php` (sesuaikan nama folder proyek bila berbeda). Database `storage/bookify.sqlite` dan data master siswa SMA GLITZ dibuat otomatis.

### Akun Login Demo:
- **Petugas / Admin**: `ADMIN001` / `admin123`
- **Siswa SMA GLITZ**: Bisa menggunakan **NIS** (contoh: `20241001`) atau ID (`MBR001`) dengan password `member123`.

### Fitur Utama SMA GLITZ:
1. **Deteksi Data & Alamat Otomatis**: Saat siswa meminjam buku, sistem secara otomatis mendeteksi identitas lengkap (NIS, Nama Siswa, Kelas, Alamat Rumah, dan No. Telepon) langsung dari database. Siswa tidak perlu menginput alamat manual lagi.
2. **CRUD User Dihapus**: Menu user digantikan menjadi direktori **Data Siswa SMA GLITZ** (Read-Only) untuk menjaga integritas data master siswa sekolah tanpa fitur tambah/ubah/hapus user sembarangan.
3. **Peminjaman Sekolah Terintegrasi**: Peminjaman buku langsung tercatat dengan masa pinjam standar 7 hari.

Pastikan ekstensi `pdo_sqlite` aktif di PHP Laragon. Tidak perlu membuka port 3000 atau menjalankan npm.
