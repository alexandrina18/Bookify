<?php
declare(strict_types=1);

/* Bookify PHP edition: shared bootstrapping, SQLite schema, and helpers. */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
date_default_timezone_set('Asia/Jakarta');

const APP_NAME = 'SMA GLITZ';
const DB_FILE = __DIR__ . '/../storage/bookify.sqlite';

function base_url(string $path = ''): string
{
    $script = rawurldecode($_SERVER['SCRIPT_NAME'] ?? '');
    $marker = '/versiphp/';
    $position = stripos($script, $marker);
    // Keep the real path and casing from SCRIPT_NAME so URLs work on case-sensitive servers too.
    $base = $position === false ? '' : substr($script, 0, $position + strlen($marker) - 1);
    return $base . ($path === '' ? '/' : '/' . ltrim($path, '/'));
}

function url(string $path = ''): string
{
    return base_url($path);
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function flash(string $message, string $type = 'success'): void
{
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function pull_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

function pdo(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    if (!extension_loaded('pdo_sqlite')) {
        http_response_code(500);
        exit('Ekstensi pdo_sqlite belum aktif. Aktifkan pdo_sqlite pada PHP Laragon.');
    }
    $pdo = new PDO('sqlite:' . DB_FILE);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
    initialise_database($pdo);
    return $pdo;
}

function initialise_database(PDO $db): void
{
    $db->exec(<<<'SQL'
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT, member_id TEXT NOT NULL UNIQUE, name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE, phone TEXT, gender TEXT, address TEXT, password TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'member', status TEXT NOT NULL DEFAULT 'active', photo_path TEXT, nik TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS publishers (
    id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, location TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS books (
    id INTEGER PRIMARY KEY AUTOINCREMENT, book_id TEXT NOT NULL UNIQUE, title TEXT NOT NULL, author TEXT NOT NULL,
    year INTEGER, pages INTEGER, genre TEXT, synopsis TEXT, stock INTEGER NOT NULL DEFAULT 1, available INTEGER NOT NULL DEFAULT 1,
    cover_url TEXT, publisher_id INTEGER, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (publisher_id) REFERENCES publishers(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS borrowings (
    id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, book_id INTEGER NOT NULL,
    borrowed_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, due_date TEXT NOT NULL, returned_at TEXT,
    status TEXT NOT NULL DEFAULT 'active', renew_count INTEGER NOT NULL DEFAULT 0,
    fulfillment_method TEXT, pickup_deadline TEXT, shipping_fee INTEGER NOT NULL DEFAULT 0,
    receipt_no TEXT, estimated_delivery TEXT, payment_method TEXT, paid_at TEXT, delivery_address TEXT, payment_deadline TEXT,
    history_visible INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id), FOREIGN KEY (book_id) REFERENCES books(id)
);
CREATE TABLE IF NOT EXISTS attendances (
    id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, check_in TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    check_out TEXT, duration INTEGER, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
CREATE TABLE IF NOT EXISTS fines (
    id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, borrowing_id INTEGER UNIQUE,
    days_late INTEGER NOT NULL DEFAULT 0, amount INTEGER NOT NULL, book_price INTEGER, status TEXT NOT NULL DEFAULT 'unpaid',
    type TEXT NOT NULL DEFAULT 'late', notes TEXT, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id), FOREIGN KEY (borrowing_id) REFERENCES borrowings(id)
);
CREATE TABLE IF NOT EXISTS extensions (
    id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, borrowing_id INTEGER NOT NULL,
    requested_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, new_due_date TEXT, status TEXT NOT NULL DEFAULT 'pending',
    reason TEXT, duration_days INTEGER NOT NULL DEFAULT 7,
    admin_note TEXT, reviewed_at TEXT, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id), FOREIGN KEY (borrowing_id) REFERENCES borrowings(id)
);
CREATE TABLE IF NOT EXISTS complaints (
    id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, book_id INTEGER NOT NULL, type TEXT NOT NULL,
    description TEXT, photo_path TEXT, status TEXT NOT NULL DEFAULT 'pending', admin_note TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id), FOREIGN KEY (book_id) REFERENCES books(id)
);
CREATE TABLE IF NOT EXISTS reviews (
    id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, book_id INTEGER NOT NULL,
    rating INTEGER NOT NULL, review TEXT NOT NULL, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id), FOREIGN KEY (book_id) REFERENCES books(id)
);
CREATE TABLE IF NOT EXISTS fine_reminders (
    id INTEGER PRIMARY KEY AUTOINCREMENT, fine_id INTEGER NOT NULL UNIQUE, user_id INTEGER NOT NULL,
    next_reminder_at TEXT NOT NULL, ends_at TEXT NOT NULL, last_sent_at TEXT, sent_count INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (fine_id) REFERENCES fines(id), FOREIGN KEY (user_id) REFERENCES users(id)
);
CREATE TABLE IF NOT EXISTS app_migrations (name TEXT PRIMARY KEY, applied_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS budgets (
    id INTEGER PRIMARY KEY AUTOINCREMENT, category TEXT NOT NULL, description TEXT NOT NULL, amount INTEGER NOT NULL,
    type TEXT NOT NULL, date TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
SQL);

    $userColumns = array_column($db->query('PRAGMA table_info(users)')->fetchAll(), 'name');
    if (!in_array('photo_path', $userColumns, true)) { $db->exec('ALTER TABLE users ADD COLUMN photo_path TEXT'); }
    if (!in_array('nik', $userColumns, true)) { $db->exec('ALTER TABLE users ADD COLUMN nik TEXT'); }
    if (!in_array('nis', $userColumns, true)) { $db->exec('ALTER TABLE users ADD COLUMN nis TEXT'); }
    if (!in_array('kelas', $userColumns, true)) { $db->exec('ALTER TABLE users ADD COLUMN kelas TEXT'); }
    $db->exec("UPDATE users SET address='Perpustakaan SMA GLITZ, Jl. Glitz No. 1, Semarang' WHERE member_id='ADMIN001'");
    $db->exec("UPDATE publishers SET location='Semarang' WHERE name IN ('Gramedia Pustaka Utama','Erlangga','Mizan','Kompas') AND location IN ('Jakarta','Bandung')");
    $bookColumns = array_column($db->query('PRAGMA table_info(books)')->fetchAll(), 'name');
    if (!in_array('synopsis', $bookColumns, true)) { $db->exec('ALTER TABLE books ADD COLUMN synopsis TEXT'); }

    $borrowingColumns = array_column($db->query('PRAGMA table_info(borrowings)')->fetchAll(), 'name');
    foreach (['fulfillment_method'=>'TEXT','pickup_deadline'=>'TEXT','shipping_fee'=>'INTEGER NOT NULL DEFAULT 0','receipt_no'=>'TEXT','estimated_delivery'=>'TEXT','payment_method'=>'TEXT','paid_at'=>'TEXT','delivery_address'=>'TEXT','payment_deadline'=>'TEXT'] as $column=>$definition) {
        if (!in_array($column, $borrowingColumns, true)) { $db->exec("ALTER TABLE borrowings ADD COLUMN {$column} {$definition}"); }
    }
    if (!in_array('history_visible', $borrowingColumns, true)) { $db->exec('ALTER TABLE borrowings ADD COLUMN history_visible INTEGER NOT NULL DEFAULT 1'); }
    $extensionColumns = array_column($db->query('PRAGMA table_info(extensions)')->fetchAll(), 'name');
    foreach (['reason'=>'TEXT','duration_days'=>'INTEGER NOT NULL DEFAULT 7'] as $column=>$definition) {
        if (!in_array($column, $extensionColumns, true)) { $db->exec("ALTER TABLE extensions ADD COLUMN {$column} {$definition}"); }
    }

    // Upgrade databases created before member reporting and reviews were added.
    $complaintColumns = array_column($db->query('PRAGMA table_info(complaints)')->fetchAll(), 'name');
    if (!in_array('photo_path', $complaintColumns, true)) { $db->exec('ALTER TABLE complaints ADD COLUMN photo_path TEXT'); }

    if ((int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0) {
        seed_database($db);
    }

    $seedMigration=$db->prepare('SELECT 1 FROM app_migrations WHERE name=?');
    $seedMigration->execute(['member_mbr001_ongoing_loan_v1']);
    if(!$seedMigration->fetchColumn()){
        $member=$db->query("SELECT id FROM users WHERE member_id='MBR001' AND role='member'")->fetchColumn();
        if($member && !(int)$db->query("SELECT COUNT(*) FROM borrowings WHERE user_id=".(int)$member." AND status='active'")->fetchColumn()){
            $book=$db->query("SELECT id,available FROM books WHERE book_id='BK004'")->fetch();
            if($book && (int)$book['available']>0){
                $start=(new DateTimeImmutable('now',new DateTimeZone('Asia/Jakarta')))->modify('-2 days');
                $due=$start->modify('+7 days');
                $insert=$db->prepare("INSERT INTO borrowings(user_id,book_id,borrowed_at,due_date,status,fulfillment_method) VALUES(?,?,?,?, 'active','pickup')");
                $insert->execute([(int)$member,(int)$book['id'],$start->format('Y-m-d H:i:s'),$due->format('Y-m-d H:i:s')]);
                $db->prepare('UPDATE books SET available=available-1 WHERE id=?')->execute([(int)$book['id']]);
            }
        }
        $db->prepare('INSERT OR IGNORE INTO app_migrations(name) VALUES(?)')->execute(['member_mbr001_ongoing_loan_v1']);
    }
    $hideExample=$db->prepare('SELECT 1 FROM app_migrations WHERE name=?');
    $hideExample->execute(['hide_mbr001_bk004_history_v1']);
    if(!$hideExample->fetchColumn()){
        $db->exec("UPDATE borrowings SET history_visible=0 WHERE id=(SELECT br.id FROM borrowings br JOIN users u ON u.id=br.user_id JOIN books b ON b.id=br.book_id WHERE u.member_id='MBR001' AND b.book_id='BK004' AND br.status='active' AND br.fulfillment_method='pickup' ORDER BY br.id DESC LIMIT 1)");
        $db->prepare('INSERT OR IGNORE INTO app_migrations(name) VALUES(?)')->execute(['hide_mbr001_bk004_history_v1']);
    }

    $glitzMigration = $db->prepare('SELECT 1 FROM app_migrations WHERE name=?');
    $glitzMigration->execute(['sma_glitz_dummy_data_v2']);
    if (!$glitzMigration->fetchColumn()) {
        $db->exec("UPDATE users SET name='Petugas Perpustakaan SMA GLITZ', email='admin@smaglitz.sch.id', address='Perpustakaan SMA GLITZ, Jl. Glitz No. 1, Semarang' WHERE member_id='ADMIN001'");

        $students = [
            ['MBR001', '20241001', 'Jolanda Aurelia Susanto', 'XII MIPA 1', 'jolanda@smaglitz.sch.id', '081122334455', 'female', 'Jl. Merdeka No. 15, Semarang', '3374012345670001'],
            ['MBR002', '20241002', 'Theodora Trixie Sundari', 'XII IPS 1', 'theodora@smaglitz.sch.id', '082233445566', 'female', 'Jl. Pahlawan No. 28, Semarang', '3374012345670002'],
            ['MBR003', '20241003', 'Alexandrina Immanuela Sahar', 'XII IPS 2', 'alexandrina@smaglitz.sch.id', '083344556677', 'female', 'Jl. Pemuda No. 102, Semarang', '3374012345670003'],
            ['MBR004', '20241004', 'Bonifasius Frith Farandy', 'XI MIPA 2', 'bonifasius@smaglitz.sch.id', '084455667788', 'male', 'Jl. Diponegoro No. 44, Semarang', '3374012345670004'],
            ['MBR005', '20241005', 'Thomas Nova Wibisono', 'XI IPS 1', 'thomas@smaglitz.sch.id', '085566778899', 'male', 'Jl. Sudirman No. 89, Semarang', '3374012345670005'],
            ['MBR006', '20251006', 'Aditiya Pratama', 'X-A', 'aditiya@smaglitz.sch.id', '081234567801', 'male', 'Jl. Gajah Mada No. 17, Semarang', '3374012345670006'],
            ['MBR007', '20251007', 'Clarissa Putri Anindya', 'X-B', 'clarissa@smaglitz.sch.id', '081234567802', 'female', 'Jl. Pandanaran No. 56, Semarang', '3374012345670007'],
            ['MBR008', '20241008', 'Dimas Arya Wicaksana', 'XI MIPA 3', 'dimas@smaglitz.sch.id', '081234567803', 'male', 'Jl. Erlangga Tengah No. 9, Semarang', '3374012345670008'],
            ['MBR009', '20231009', 'Felicia Evelyn Hartanto', 'XII MIPA 2', 'felicia@smaglitz.sch.id', '081234567804', 'female', 'Jl. Sumbing No. 14, Semarang', '3374012345670009'],
            ['MBR010', '20251010', 'Kevin Jonathan Kusumo', 'X-C', 'kevin@smaglitz.sch.id', '081234567805', 'male', 'Jl. Siranda No. 3, Semarang', '3374012345670010'],
        ];

        $passHash = password_hash('member123', PASSWORD_DEFAULT);
        $checkStmt = $db->prepare('SELECT id FROM users WHERE member_id=?');
        $updateStmt = $db->prepare('UPDATE users SET nis=?, name=?, kelas=?, email=?, phone=?, gender=?, address=?, nik=?, role="member", status="active" WHERE member_id=?');
        $insertStmt = $db->prepare('INSERT INTO users (member_id, nis, name, kelas, email, phone, gender, address, nik, password, role, status) VALUES (?,?,?,?,?,?,?,?,?,?,"member","active")');

        foreach ($students as [$memberId, $nis, $name, $kelas, $email, $phone, $gender, $address, $nik]) {
            $checkStmt->execute([$memberId]);
            if ($checkStmt->fetchColumn()) {
                $updateStmt->execute([$nis, $name, $kelas, $email, $phone, $gender, $address, $nik, $memberId]);
            } else {
                $insertStmt->execute([$memberId, $nis, $name, $kelas, $email, $phone, $gender, $address, $nik, $passHash]);
            }
        }
        $db->prepare('INSERT OR IGNORE INTO app_migrations(name) VALUES(?)')->execute(['sma_glitz_dummy_data_v2']);
    }

    $coversMigration = $db->prepare('SELECT 1 FROM app_migrations WHERE name=?');
    $coversMigration->execute(['sma_glitz_book_covers_v1']);
    if (!$coversMigration->fetchColumn()) {
        $coverMap = [
            'BK001' => 'assets/covers/BK001.jpg',
            'BK002' => 'assets/covers/BK002.jpg',
            'BK003' => 'assets/covers/BK003.jpg',
            'BK004' => 'assets/covers/BK004.jpg',
            'BK005' => 'assets/covers/BK005.jpg',
            'BK006' => 'assets/covers/BK006.jpg',
            'BK007' => 'assets/covers/BK007.jpg',
            'BK008' => 'assets/covers/BK008.jpg',
            'BK009' => 'assets/covers/BK009.jpg',
            'BK010' => 'assets/covers/BK010.jpg',
            'BK011' => 'assets/covers/BK011.jpg',
            'BK012' => 'assets/covers/BK012.jpg',
        ];
        $updateCover = $db->prepare('UPDATE books SET cover_url=? WHERE book_id=?');
        foreach ($coverMap as $bookId => $coverUrl) {
            $updateCover->execute([$coverUrl, $bookId]);
        }
        $db->prepare('INSERT OR IGNORE INTO app_migrations(name) VALUES(?)')->execute(['sma_glitz_book_covers_v1']);
    }
}

function seed_database(PDO $db): void
{
    $db->beginTransaction();
    try {
        $insertUser = $db->prepare('INSERT INTO users (member_id,nis,name,kelas,email,phone,gender,address,password,role,status,nik) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
        $insertUser->execute(['ADMIN001', null, 'Petugas Perpustakaan SMA GLITZ', null, 'admin@smaglitz.sch.id', '081234567890', 'male', 'Perpustakaan SMA GLITZ, Jl. Glitz No. 1, Semarang', password_hash('admin123', PASSWORD_DEFAULT), 'admin', 'active', '3374010101800001']);
        $passHash = password_hash('member123', PASSWORD_DEFAULT);
        foreach ([
            ['MBR001', '20241001', 'Jolanda Aurelia Susanto', 'XII MIPA 1', 'jolanda@smaglitz.sch.id', '081122334455', 'female', 'Jl. Merdeka No. 15, Semarang', '3374012345670001'],
            ['MBR002', '20241002', 'Theodora Trixie Sundari', 'XII IPS 1', 'theodora@smaglitz.sch.id', '082233445566', 'female', 'Jl. Pahlawan No. 28, Semarang', '3374012345670002'],
            ['MBR003', '20241003', 'Alexandrina Immanuela Sahar', 'XII IPS 2', 'alexandrina@smaglitz.sch.id', '083344556677', 'female', 'Jl. Pemuda No. 102, Semarang', '3374012345670003'],
            ['MBR004', '20241004', 'Bonifasius Frith Farandy', 'XI MIPA 2', 'bonifasius@smaglitz.sch.id', '084455667788', 'male', 'Jl. Diponegoro No. 44, Semarang', '3374012345670004'],
            ['MBR005', '20241005', 'Thomas Nova Wibisono', 'XI IPS 1', 'thomas@smaglitz.sch.id', '085566778899', 'male', 'Jl. Sudirman No. 89, Semarang', '3374012345670005'],
            ['MBR006', '20251006', 'Aditiya Pratama', 'X-A', 'aditiya@smaglitz.sch.id', '081234567801', 'male', 'Jl. Gajah Mada No. 17, Semarang', '3374012345670006'],
            ['MBR007', '20251007', 'Clarissa Putri Anindya', 'X-B', 'clarissa@smaglitz.sch.id', '081234567802', 'female', 'Jl. Pandanaran No. 56, Semarang', '3374012345670007'],
            ['MBR008', '20241008', 'Dimas Arya Wicaksana', 'XI MIPA 3', 'dimas@smaglitz.sch.id', '081234567803', 'male', 'Jl. Erlangga Tengah No. 9, Semarang', '3374012345670008'],
            ['MBR009', '20231009', 'Felicia Evelyn Hartanto', 'XII MIPA 2', 'felicia@smaglitz.sch.id', '081234567804', 'female', 'Jl. Sumbing No. 14, Semarang', '3374012345670009'],
            ['MBR010', '20251010', 'Kevin Jonathan Kusumo', 'X-C', 'kevin@smaglitz.sch.id', '081234567805', 'male', 'Jl. Siranda No. 3, Semarang', '3374012345670010'],
        ] as [$mId, $nis, $name, $kelas, $email, $phone, $gender, $addr, $nik]) {
            $insertUser->execute([$mId, $nis, $name, $kelas, $email, $phone, $gender, $addr, $passHash, 'member', 'active', $nik]);
        }
        $insertPublisher = $db->prepare('INSERT INTO publishers (name,location) VALUES (?,?)');
        foreach ([['Gramedia Pustaka Utama','Semarang'], ['Erlangga','Semarang'], ['Mizan','Semarang'], ['Kompas','Semarang']] as $publisher) {
            $insertPublisher->execute($publisher);
        }
        $insertBook = $db->prepare('INSERT INTO books (book_id,title,author,year,pages,genre,stock,available,publisher_id,cover_url) VALUES (?,?,?,?,?,?,?,?,?,?)');
        $books = [
            ['BK001','Laskar Pelangi','Andrea Hirata',2005,529,'Novel',3,2,3,'assets/covers/BK001.jpg'],
            ['BK002','Bumi Manusia','Pramoedya Ananta Toer',1980,535,'Novel',2,2,1,'assets/covers/BK002.jpg'],
            ['BK003','Pemrograman Web dengan Next.js','Ahmad Fauzi',2023,420,'Teknologi',4,3,2,'assets/covers/BK003.jpg'],
            ['BK004','Dasar-dasar Sistem Informasi','Budi Sutedjo',2022,310,'Teknologi',5,5,2,'assets/covers/BK004.jpg'],
            ['BK005','Filosofi Teras','Henry Manampiring',2019,316,'Filsafat',3,1,1,'assets/covers/BK005.jpg'],
            ['BK006','Atomic Habits','James Clear',2018,319,'Pengembangan Diri',4,4,1,'assets/covers/BK006.jpg'],
            ['BK007','Sapiens: Riwayat Singkat Umat Manusia','Yuval Noah Harari',2011,512,'Sejarah',2,2,1,'assets/covers/BK007.jpg'],
            ['BK008','The Alchemist','Paulo Coelho',1988,197,'Novel',3,3,3,'assets/covers/BK008.jpg'],
            ['BK009','Pengantar Basis Data','Raghu Ramakrishnan',2020,440,'Teknologi',3,2,2,'assets/covers/BK009.jpg'],
            ['BK010','Rich Dad Poor Dad','Robert T. Kiyosaki',1997,207,'Keuangan',2,2,4,'assets/covers/BK010.jpg'],
            ['BK011','Negeri 5 Menara','Ahmad Fuadi',2009,423,'Novel',2,2,1,'assets/covers/BK011.jpg'],
            ['BK012','Desain UI/UX Modern','Siti Rahma',2024,280,'Teknologi',3,3,2,'assets/covers/BK012.jpg'],
        ];
        foreach ($books as $book) { $insertBook->execute($book); }
        $db->exec("INSERT INTO borrowings (user_id,book_id,borrowed_at,due_date,status) VALUES (2,1,datetime('now','-10 days'),datetime('now','-3 days'),'overdue')");
        $db->exec("INSERT INTO fines (user_id,borrowing_id,days_late,amount,status,type) VALUES (2,1,3,3000,'unpaid','late')");
        $db->exec("INSERT INTO borrowings (user_id,book_id,borrowed_at,due_date,status,renew_count) VALUES (3,5,datetime('now','-3 days'),datetime('now','+4 days'),'active',1)");
        $db->exec("INSERT INTO borrowings (user_id,book_id,borrowed_at,due_date,returned_at,status) VALUES (4,9,datetime('now','-14 days'),datetime('now','-7 days'),datetime('now','-7 days'),'returned')");
        $db->exec("INSERT INTO attendances (user_id,check_in) VALUES (2,datetime('now','-2 hours'))");
        $insertBudget = $db->prepare('INSERT INTO budgets (category,description,amount,type) VALUES (?,?,?,?)');
        foreach ([
            ['salary','Gaji Project Manager (2 bulan)',16000000,'expense'], ['salary','Gaji System Analyst & Designer (2 bulan)',14000000,'expense'],
            ['salary','Gaji Developer Backend (2 bulan)',16000000,'expense'], ['salary','Gaji Developer Frontend (2 bulan)',14000000,'expense'],
            ['salary','Gaji Tester (1 bulan)',5000000,'expense'], ['infrastructure','Hosting/VPS 1 tahun, domain, email',5000000,'expense'],
            ['equipment','2 scanner barcode, 2 webcam, printer, stiker',5500000,'expense'], ['books','Pengadaan ~200 buku awal',15000000,'expense'],
            ['operations','Biaya operasional 3 bulan awal',7500000,'expense'],
        ] as $budget) { $insertBudget->execute($budget); }
        $db->commit();
    } catch (Throwable $error) {
        $db->rollBack();
        throw $error;
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) { redirect('login.php'); }
    return $user;
}

function require_admin(): array
{
    $user = current_user();
    if (!$user || $user['role'] !== 'admin') { redirect('admin/login.php'); }
    return $user;
}

function login(string $memberId, string $password, ?string $role = null): bool
{
    $query = 'SELECT * FROM users WHERE (member_id = ? OR nis = ?)';
    $params = [$memberId, $memberId];
    if ($role !== null) { $query .= ' AND role = ?'; $params[] = $role; }
    $statement = pdo()->prepare($query);
    $statement->execute($params);
    $user = $statement->fetch();
    if (!$user || $user['status'] === 'blocked' || !password_verify($password, $user['password'])) {
        return false;
    }
    unset($user['password']);
    $_SESSION['user'] = $user;
    return true;
}

function logout(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function rows(string $query, array $params = []): array
{
    $statement = pdo()->prepare($query);
    $statement->execute($params);
    return $statement->fetchAll();
}

function one(string $query, array $params = []): ?array
{
    $statement = pdo()->prepare($query);
    $statement->execute($params);
    $result = $statement->fetch();
    return $result ?: null;
}

function execute(string $query, array $params = []): bool
{
    $statement = pdo()->prepare($query);
    return $statement->execute($params);
}

function count_of(string $table, string $where = '1=1', array $params = []): int
{
    return (int) one("SELECT COUNT(*) AS amount FROM {$table} WHERE {$where}", $params)['amount'];
}

function rupiah(int|float $number): string
{
    return 'Rp ' . number_format((float) $number, 0, ',', '.');
}

function status_badge(string $status): string
{
    return '<span class="badge badge-' . e($status) . '">' . e(ucfirst($status)) . '</span>';
}

function date_id(?string $value, bool $withTime = false): string
{
    if (!$value) { return '-'; }
    $time = strtotime($value);
    return date($withTime ? 'd/m/Y H:i' : 'd/m/Y', $time);
}
