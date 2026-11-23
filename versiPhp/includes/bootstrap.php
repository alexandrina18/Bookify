<?php
declare(strict_types=1);

/* Bookify PHP edition: shared bootstrapping, SQLite schema, and helpers. */
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

const APP_NAME = 'Bookify';
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
    role TEXT NOT NULL DEFAULT 'member', status TEXT NOT NULL DEFAULT 'active',
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS publishers (
    id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, location TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS books (
    id INTEGER PRIMARY KEY AUTOINCREMENT, book_id TEXT NOT NULL UNIQUE, title TEXT NOT NULL, author TEXT NOT NULL,
    year INTEGER, pages INTEGER, genre TEXT, stock INTEGER NOT NULL DEFAULT 1, available INTEGER NOT NULL DEFAULT 1,
    cover_url TEXT, publisher_id INTEGER, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (publisher_id) REFERENCES publishers(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS borrowings (
    id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, book_id INTEGER NOT NULL,
    borrowed_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, due_date TEXT NOT NULL, returned_at TEXT,
    status TEXT NOT NULL DEFAULT 'active', renew_count INTEGER NOT NULL DEFAULT 0,
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
    admin_note TEXT, reviewed_at TEXT, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id), FOREIGN KEY (borrowing_id) REFERENCES borrowings(id)
);
CREATE TABLE IF NOT EXISTS complaints (
    id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, book_id INTEGER NOT NULL, type TEXT NOT NULL,
    description TEXT, status TEXT NOT NULL DEFAULT 'pending', admin_note TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id), FOREIGN KEY (book_id) REFERENCES books(id)
);
CREATE TABLE IF NOT EXISTS budgets (
    id INTEGER PRIMARY KEY AUTOINCREMENT, category TEXT NOT NULL, description TEXT NOT NULL, amount INTEGER NOT NULL,
    type TEXT NOT NULL, date TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
SQL);

    if ((int) $db->query('SELECT COUNT(*) FROM users')->fetchColumn() === 0) {
        seed_database($db);
    }
}

function seed_database(PDO $db): void
{
    $db->beginTransaction();
    try {
        $insertUser = $db->prepare('INSERT INTO users (member_id,name,email,phone,gender,address,password,role,status) VALUES (?,?,?,?,?,?,?,?,?)');
        $insertUser->execute(['ADMIN001', 'Administrator', 'admin@bookify.id', '081234567890', 'male', 'Perpustakaan Bookify', password_hash('admin123', PASSWORD_DEFAULT), 'admin', 'active']);
        foreach ([
            ['MBR001', 'Jolanda Aurelia Susanto', 'andi@email.com', '081111111111', 'female', 'Jl. Merdeka No. 1'],
            ['MBR002', 'Theodora Trixie Sundari', 'sari@email.com', '082222222222', 'female', 'Jl. Pahlawan No. 5'],
            ['MBR003', 'Alexandrina Immanuela Sahar', 'budi@email.com', '083333333333', 'female', 'Jl. Pemuda No. 10'],
            ['MBR004', 'Bonifasius Frith Farandy', 'lina@email.com', '084444444444', 'male', 'Jl. Diponegoro No. 20'],
            ['MBR005', 'Thomas Nova Wibisono', 'reza@email.com', '085555555555', 'male', 'Jl. Sudirman No. 15'],
        ] as $member) {
            $insertUser->execute([...$member, password_hash('member123', PASSWORD_DEFAULT), 'member', 'active']);
        }
        $insertPublisher = $db->prepare('INSERT INTO publishers (name,location) VALUES (?,?)');
        foreach ([['Gramedia Pustaka Utama','Jakarta'], ['Erlangga','Jakarta'], ['Mizan','Bandung'], ['Kompas','Jakarta']] as $publisher) {
            $insertPublisher->execute($publisher);
        }
        $insertBook = $db->prepare('INSERT INTO books (book_id,title,author,year,pages,genre,stock,available,publisher_id) VALUES (?,?,?,?,?,?,?,?,?)');
        $books = [
            ['BK001','Laskar Pelangi','Andrea Hirata',2005,529,'Novel',3,2,3], ['BK002','Bumi Manusia','Pramoedya Ananta Toer',1980,535,'Novel',2,2,1],
            ['BK003','Pemrograman Web dengan Next.js','Ahmad Fauzi',2023,420,'Teknologi',4,3,2], ['BK004','Dasar-dasar Sistem Informasi','Budi Sutedjo',2022,310,'Teknologi',5,5,2],
            ['BK005','Filosofi Teras','Henry Manampiring',2019,316,'Filsafat',3,1,1], ['BK006','Atomic Habits','James Clear',2018,319,'Pengembangan Diri',4,4,1],
            ['BK007','Sapiens: Riwayat Singkat Umat Manusia','Yuval Noah Harari',2011,512,'Sejarah',2,2,1], ['BK008','The Alchemist','Paulo Coelho',1988,197,'Novel',3,3,3],
            ['BK009','Pengantar Basis Data','Raghu Ramakrishnan',2020,440,'Teknologi',3,2,2], ['BK010','Rich Dad Poor Dad','Robert T. Kiyosaki',1997,207,'Keuangan',2,2,4],
            ['BK011','Negeri 5 Menara','Ahmad Fuadi',2009,423,'Novel',2,2,1], ['BK012','Desain UI/UX Modern','Siti Rahma',2024,280,'Teknologi',3,3,2],
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
    $query = 'SELECT * FROM users WHERE member_id = ?';
    $params = [$memberId];
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
