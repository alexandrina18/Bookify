-- Bookify SQLite export, includes schema, IDs, foreign keys, and current data.
-- Import into a SQLite database.
PRAGMA foreign_keys=OFF;
BEGIN TRANSACTION;
CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT, member_id TEXT NOT NULL UNIQUE, name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE, phone TEXT, gender TEXT, address TEXT, password TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'member', status TEXT NOT NULL DEFAULT 'active',
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
, photo_path TEXT, nik TEXT);
CREATE TABLE publishers (
    id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, location TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE books (
    id INTEGER PRIMARY KEY AUTOINCREMENT, book_id TEXT NOT NULL UNIQUE, title TEXT NOT NULL, author TEXT NOT NULL,
    year INTEGER, pages INTEGER, genre TEXT, stock INTEGER NOT NULL DEFAULT 1, available INTEGER NOT NULL DEFAULT 1,
    cover_url TEXT, publisher_id INTEGER, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, synopsis TEXT,
    FOREIGN KEY (publisher_id) REFERENCES publishers(id) ON DELETE SET NULL
);
CREATE TABLE borrowings (
    id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, book_id INTEGER NOT NULL,
    borrowed_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, due_date TEXT NOT NULL, returned_at TEXT,
    status TEXT NOT NULL DEFAULT 'active', renew_count INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, fulfillment_method TEXT, pickup_deadline TEXT, shipping_fee INTEGER NOT NULL DEFAULT 0, receipt_no TEXT, estimated_delivery TEXT, payment_method TEXT, paid_at TEXT, delivery_address TEXT, payment_deadline TEXT, history_visible INTEGER NOT NULL DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id), FOREIGN KEY (book_id) REFERENCES books(id)
);
CREATE TABLE attendances (
    id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, check_in TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    check_out TEXT, duration INTEGER, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);
CREATE TABLE fines (
    id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, borrowing_id INTEGER UNIQUE,
    days_late INTEGER NOT NULL DEFAULT 0, amount INTEGER NOT NULL, book_price INTEGER, status TEXT NOT NULL DEFAULT 'unpaid',
    type TEXT NOT NULL DEFAULT 'late', notes TEXT, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id), FOREIGN KEY (borrowing_id) REFERENCES borrowings(id)
);
CREATE TABLE extensions (
    id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, borrowing_id INTEGER NOT NULL,
    requested_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, new_due_date TEXT, status TEXT NOT NULL DEFAULT 'pending',
    admin_note TEXT, reviewed_at TEXT, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, reason TEXT, duration_days INTEGER NOT NULL DEFAULT 7,
    FOREIGN KEY (user_id) REFERENCES users(id), FOREIGN KEY (borrowing_id) REFERENCES borrowings(id)
);
CREATE TABLE complaints (
    id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, book_id INTEGER NOT NULL, type TEXT NOT NULL,
    description TEXT, status TEXT NOT NULL DEFAULT 'pending', admin_note TEXT,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, photo_path TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id), FOREIGN KEY (book_id) REFERENCES books(id)
);
CREATE TABLE reviews (
    id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, book_id INTEGER NOT NULL,
    rating INTEGER NOT NULL, review TEXT NOT NULL, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id), FOREIGN KEY (book_id) REFERENCES books(id)
);
CREATE TABLE fine_reminders (
    id INTEGER PRIMARY KEY AUTOINCREMENT, fine_id INTEGER NOT NULL UNIQUE, user_id INTEGER NOT NULL,
    next_reminder_at TEXT NOT NULL, ends_at TEXT NOT NULL, last_sent_at TEXT, sent_count INTEGER NOT NULL DEFAULT 0,
    FOREIGN KEY (fine_id) REFERENCES fines(id), FOREIGN KEY (user_id) REFERENCES users(id)
);
CREATE TABLE budgets (
    id INTEGER PRIMARY KEY AUTOINCREMENT, category TEXT NOT NULL, description TEXT NOT NULL, amount INTEGER NOT NULL,
    type TEXT NOT NULL, date TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP, created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE app_migrations (name TEXT PRIMARY KEY, applied_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP);
INSERT INTO "users" ("id","member_id","name","email","phone","gender","address","password","role","status","created_at","updated_at","photo_path","nik") VALUES (1,'ADMIN001','Administrator','admin@bookify.id','081234567890','male','Perpustakaan Bookify, Semarang','$2y$10$UUPsV.hWlApV3bRFBNeoA.b1bnUFTqjJpVPJeJnGdtgkmZGp1mZv6','admin','active','2026-11-23 00:28:25','2026-11-23 00:28:25',NULL,NULL);
INSERT INTO "users" ("id","member_id","name","email","phone","gender","address","password","role","status","created_at","updated_at","photo_path","nik") VALUES (2,'MBR001','Jolanda Aurelia Susanto','andi@email.com','081111111111','female','Jl. Merdeka No. 1, Semarang','$2y$10$I26b8GjGVTV96XrYyjLdx.pijq13rVTYqOXPtXFdo38rEyB9YYsP2','member','active','2026-11-23 00:28:25','2026-11-23 00:28:25',NULL,NULL);
INSERT INTO "users" ("id","member_id","name","email","phone","gender","address","password","role","status","created_at","updated_at","photo_path","nik") VALUES (3,'MBR002','Theodora Trixie Sundari','sari@email.com','082222222222','female','Jl. Pahlawan No. 5, Semarang','$2y$10$nmwpK6LK1QzLsKbpt7en7.mSJVezF4X1LWRHYUqpGUI9aZ3DkHUUW','member','active','2026-11-23 00:28:25','2026-11-23 00:28:25',NULL,NULL);
INSERT INTO "users" ("id","member_id","name","email","phone","gender","address","password","role","status","created_at","updated_at","photo_path","nik") VALUES (4,'MBR003','Alexandrina Immanuela Sahar','budi@email.com','083333333333','female','Jl. Pemuda No. 10, Semarang','$2y$10$dlFX7c/SMMAlM5p2DLrH4.ebcQpd9oVW0BH0Ms0b8IId143qMU7PW','member','active','2026-11-23 00:28:25','2026-11-23 00:28:25',NULL,NULL);
INSERT INTO "users" ("id","member_id","name","email","phone","gender","address","password","role","status","created_at","updated_at","photo_path","nik") VALUES (5,'MBR004','Bonifasius Frith Farandy','lina@email.com','084444444444','male','Jl. Diponegoro No. 20, Semarang','$2y$10$nXlvWmxMmfn2c1IrvCjTGOEeLWK60qeZRGO1LgrUbv6wQlo9KJJ.C','member','active','2026-11-23 00:28:25','2026-11-23 00:28:25',NULL,NULL);
INSERT INTO "users" ("id","member_id","name","email","phone","gender","address","password","role","status","created_at","updated_at","photo_path","nik") VALUES (6,'MBR005','Thomas Nova Wibisono','reza@email.com','085555555555','male','Jl. Sudirman No. 15, Semarang','$2y$10$btRqyS7T8yozttoFpWOF0eHjJq2lDgLREL5EbyAPAR7m2y.DCs31q','member','active','2026-11-23 00:28:26','2026-11-23 00:28:26',NULL,NULL);
INSERT INTO "publishers" ("id","name","location","created_at","updated_at") VALUES (1,'Gramedia Pustaka Utama','Semarang','2026-11-23 00:28:26','2026-11-23 00:28:26');
INSERT INTO "publishers" ("id","name","location","created_at","updated_at") VALUES (2,'Erlangga','Semarang','2026-11-23 00:28:26','2026-11-23 00:28:26');
INSERT INTO "publishers" ("id","name","location","created_at","updated_at") VALUES (3,'Mizan','Semarang','2026-11-23 00:28:26','2026-11-23 00:28:26');
INSERT INTO "publishers" ("id","name","location","created_at","updated_at") VALUES (4,'Kompas','Semarang','2026-11-23 00:28:26','2026-11-23 00:28:26');
INSERT INTO "books" ("id","book_id","title","author","year","pages","genre","stock","available","cover_url","publisher_id","created_at","updated_at","synopsis") VALUES (1,'BK001','Laskar Pelangi','Andrea Hirata',2005,529,'Novel',3,2,NULL,3,'2026-11-23 00:28:26','2026-11-23 00:28:26',NULL);
INSERT INTO "books" ("id","book_id","title","author","year","pages","genre","stock","available","cover_url","publisher_id","created_at","updated_at","synopsis") VALUES (2,'BK002','Bumi Manusia','Pramoedya Ananta Toer',1980,535,'Novel',2,1,NULL,1,'2026-11-23 00:28:26','2026-11-23 00:28:26',NULL);
INSERT INTO "books" ("id","book_id","title","author","year","pages","genre","stock","available","cover_url","publisher_id","created_at","updated_at","synopsis") VALUES (3,'BK003','Pemrograman Web dengan Next.js','Ahmad Fauzi',2023,420,'Teknologi',4,3,NULL,2,'2026-11-23 00:28:26','2026-11-23 00:28:26',NULL);
INSERT INTO "books" ("id","book_id","title","author","year","pages","genre","stock","available","cover_url","publisher_id","created_at","updated_at","synopsis") VALUES (4,'BK004','Dasar-dasar Sistem Informasi','Budi Sutedjo',2022,310,'Teknologi',5,4,NULL,2,'2026-11-23 00:28:26','2026-11-23 00:28:26',NULL);
INSERT INTO "books" ("id","book_id","title","author","year","pages","genre","stock","available","cover_url","publisher_id","created_at","updated_at","synopsis") VALUES (5,'BK005','Filosofi Teras','Henry Manampiring',2019,316,'Filsafat',3,0,NULL,1,'2026-11-23 00:28:26','2026-11-23 00:28:26',NULL);
INSERT INTO "books" ("id","book_id","title","author","year","pages","genre","stock","available","cover_url","publisher_id","created_at","updated_at","synopsis") VALUES (6,'BK006','Atomic Habits','James Clear',2018,319,'Pengembangan Diri',4,4,NULL,1,'2026-11-23 00:28:26','2026-11-23 00:28:26',NULL);
INSERT INTO "books" ("id","book_id","title","author","year","pages","genre","stock","available","cover_url","publisher_id","created_at","updated_at","synopsis") VALUES (7,'BK007','Sapiens: Riwayat Singkat Umat Manusia','Yuval Noah Harari',2011,512,'Sejarah',2,2,NULL,1,'2026-11-23 00:28:26','2026-11-23 00:28:26',NULL);
INSERT INTO "books" ("id","book_id","title","author","year","pages","genre","stock","available","cover_url","publisher_id","created_at","updated_at","synopsis") VALUES (8,'BK008','The Alchemist','Paulo Coelho',1988,197,'Novel',3,2,NULL,3,'2026-11-23 00:28:26','2026-11-23 00:28:26',NULL);
INSERT INTO "books" ("id","book_id","title","author","year","pages","genre","stock","available","cover_url","publisher_id","created_at","updated_at","synopsis") VALUES (9,'BK009','Pengantar Basis Data','Raghu Ramakrishnan',2020,440,'Teknologi',3,2,NULL,2,'2026-11-23 00:28:26','2026-11-23 00:28:26',NULL);
INSERT INTO "books" ("id","book_id","title","author","year","pages","genre","stock","available","cover_url","publisher_id","created_at","updated_at","synopsis") VALUES (10,'BK010','Rich Dad Poor Dad','Robert T. Kiyosaki',1997,207,'Keuangan',2,2,NULL,4,'2026-11-23 00:28:26','2026-11-23 00:28:26',NULL);
INSERT INTO "books" ("id","book_id","title","author","year","pages","genre","stock","available","cover_url","publisher_id","created_at","updated_at","synopsis") VALUES (11,'BK011','Negeri 5 Menara','Ahmad Fuadi',2009,423,'Novel',2,2,NULL,1,'2026-11-23 00:28:26','2026-11-23 00:28:26',NULL);
INSERT INTO "books" ("id","book_id","title","author","year","pages","genre","stock","available","cover_url","publisher_id","created_at","updated_at","synopsis") VALUES (12,'BK012','Desain UI/UX Modern','Siti Rahma',2024,280,'Teknologi',3,2,NULL,2,'2026-11-23 00:28:26','2026-11-23 00:28:26',NULL);
INSERT INTO "borrowings" ("id","user_id","book_id","borrowed_at","due_date","returned_at","status","renew_count","created_at","updated_at","fulfillment_method","pickup_deadline","shipping_fee","receipt_no","estimated_delivery","payment_method","paid_at","delivery_address","payment_deadline","history_visible") VALUES (1,2,1,'2026-11-13 00:28:26','2026-11-20 00:28:26',NULL,'overdue',0,'2026-11-23 00:28:26','2026-11-23 00:28:26',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,1);
INSERT INTO "borrowings" ("id","user_id","book_id","borrowed_at","due_date","returned_at","status","renew_count","created_at","updated_at","fulfillment_method","pickup_deadline","shipping_fee","receipt_no","estimated_delivery","payment_method","paid_at","delivery_address","payment_deadline","history_visible") VALUES (2,3,5,'2026-11-20 00:28:26','2026-11-27 00:28:26',NULL,'active',1,'2026-11-23 00:28:26','2026-11-23 00:28:26',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,1);
INSERT INTO "borrowings" ("id","user_id","book_id","borrowed_at","due_date","returned_at","status","renew_count","created_at","updated_at","fulfillment_method","pickup_deadline","shipping_fee","receipt_no","estimated_delivery","payment_method","paid_at","delivery_address","payment_deadline","history_visible") VALUES (3,4,9,'2026-11-09 00:28:26','2026-11-16 00:28:26','2026-11-16 00:28:26','returned',0,'2026-11-23 00:28:26','2026-11-23 00:28:26',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,1);
INSERT INTO "borrowings" ("id","user_id","book_id","borrowed_at","due_date","returned_at","status","renew_count","created_at","updated_at","fulfillment_method","pickup_deadline","shipping_fee","receipt_no","estimated_delivery","payment_method","paid_at","delivery_address","payment_deadline","history_visible") VALUES (4,3,5,'2026-11-23 03:16:23','2026-11-30 03:16:23',NULL,'active',0,'2026-11-23 03:16:23','2026-11-23 03:16:23',NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,1);
INSERT INTO "borrowings" ("id","user_id","book_id","borrowed_at","due_date","returned_at","status","renew_count","created_at","updated_at","fulfillment_method","pickup_deadline","shipping_fee","receipt_no","estimated_delivery","payment_method","paid_at","delivery_address","payment_deadline","history_visible") VALUES (5,2,2,'2026-11-27 09:22:55','2026-11-27 17:00:00',NULL,'pickup_pending',0,'2026-11-27 02:22:55','2026-11-27 02:22:55','pickup','2026-11-27 17:00:00',0,NULL,NULL,NULL,NULL,NULL,NULL,1);
INSERT INTO "borrowings" ("id","user_id","book_id","borrowed_at","due_date","returned_at","status","renew_count","created_at","updated_at","fulfillment_method","pickup_deadline","shipping_fee","receipt_no","estimated_delivery","payment_method","paid_at","delivery_address","payment_deadline","history_visible") VALUES (6,2,12,'2026-11-27 09:24:55','2026-12-06 17:00:00',NULL,'in_transit',0,'2026-11-27 02:24:55','2026-11-27 02:24:55','delivery',NULL,15000,'BKP2611271F3CF9','2026-11-29 17:00:00','qris','2026-11-27 09:25:00','Jl. Merdeka No. 1',NULL,1);
INSERT INTO "borrowings" ("id","user_id","book_id","borrowed_at","due_date","returned_at","status","renew_count","created_at","updated_at","fulfillment_method","pickup_deadline","shipping_fee","receipt_no","estimated_delivery","payment_method","paid_at","delivery_address","payment_deadline","history_visible") VALUES (7,2,8,'2026-11-27 09:25:39','2026-12-06 17:00:00',NULL,'awaiting_payment',0,'2026-11-27 02:25:39','2026-11-27 02:25:39','delivery',NULL,15000,'BKP2611275664A4','2026-11-29 17:00:00',NULL,NULL,'Jl. Merdeka No. 1',NULL,1);
INSERT INTO "borrowings" ("id","user_id","book_id","borrowed_at","due_date","returned_at","status","renew_count","created_at","updated_at","fulfillment_method","pickup_deadline","shipping_fee","receipt_no","estimated_delivery","payment_method","paid_at","delivery_address","payment_deadline","history_visible") VALUES (8,2,4,'2026-11-25 10:12:50','2026-12-02 10:12:50',NULL,'active',0,'2026-11-27 03:12:50','2026-11-27 03:12:50','pickup',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,0);
INSERT INTO "attendances" ("id","user_id","check_in","check_out","duration","created_at") VALUES (1,2,'2026-11-22 22:28:26',NULL,NULL,'2026-11-23 00:28:26');
INSERT INTO "fines" ("id","user_id","borrowing_id","days_late","amount","book_price","status","type","notes","created_at","updated_at") VALUES (1,2,1,3,3000,NULL,'unpaid','late',NULL,'2026-11-23 00:28:26','2026-11-23 00:28:26');
INSERT INTO "extensions" ("id","user_id","borrowing_id","requested_at","new_due_date","status","admin_note","reviewed_at","created_at","updated_at","reason","duration_days") VALUES (2,2,8,'2026-11-27 10:19:43',NULL,'pending',NULL,NULL,'2026-11-27 03:19:43','2026-11-27 03:19:43','belum selesai membaca',3);
INSERT INTO "complaints" ("id","user_id","book_id","type","description","status","admin_note","created_at","updated_at","photo_path") VALUES (1,2,1,'lainnya','coba','pending',NULL,'2026-11-27 01:45:10','2026-11-27 01:45:10',NULL);
INSERT INTO "budgets" ("id","category","description","amount","type","date","created_at","updated_at") VALUES (1,'salary','Gaji Project Manager (2 bulan)',16000000,'expense','2026-11-23 00:28:26','2026-11-23 00:28:26','2026-11-23 00:28:26');
INSERT INTO "budgets" ("id","category","description","amount","type","date","created_at","updated_at") VALUES (2,'salary','Gaji System Analyst & Designer (2 bulan)',14000000,'expense','2026-11-23 00:28:26','2026-11-23 00:28:26','2026-11-23 00:28:26');
INSERT INTO "budgets" ("id","category","description","amount","type","date","created_at","updated_at") VALUES (3,'salary','Gaji Developer Backend (2 bulan)',16000000,'expense','2026-11-23 00:28:26','2026-11-23 00:28:26','2026-11-23 00:28:26');
INSERT INTO "budgets" ("id","category","description","amount","type","date","created_at","updated_at") VALUES (4,'salary','Gaji Developer Frontend (2 bulan)',14000000,'expense','2026-11-23 00:28:26','2026-11-23 00:28:26','2026-11-23 00:28:26');
INSERT INTO "budgets" ("id","category","description","amount","type","date","created_at","updated_at") VALUES (5,'salary','Gaji Tester (1 bulan)',5000000,'expense','2026-11-23 00:28:26','2026-11-23 00:28:26','2026-11-23 00:28:26');
INSERT INTO "budgets" ("id","category","description","amount","type","date","created_at","updated_at") VALUES (6,'infrastructure','Hosting/VPS 1 tahun, domain, email',5000000,'expense','2026-11-23 00:28:26','2026-11-23 00:28:26','2026-11-23 00:28:26');
INSERT INTO "budgets" ("id","category","description","amount","type","date","created_at","updated_at") VALUES (7,'equipment','2 scanner barcode, 2 webcam, printer, stiker',5500000,'expense','2026-11-23 00:28:26','2026-11-23 00:28:26','2026-11-23 00:28:26');
INSERT INTO "budgets" ("id","category","description","amount","type","date","created_at","updated_at") VALUES (8,'books','Pengadaan ~200 buku awal',15000000,'expense','2026-11-23 00:28:26','2026-11-23 00:28:26','2026-11-23 00:28:26');
INSERT INTO "budgets" ("id","category","description","amount","type","date","created_at","updated_at") VALUES (9,'operations','Biaya operasional 3 bulan awal',7500000,'expense','2026-11-23 00:28:26','2026-11-23 00:28:26','2026-11-23 00:28:26');
INSERT INTO "app_migrations" ("name","applied_at") VALUES ('member_mbr001_ongoing_loan_v1','2026-11-27 03:12:50');
INSERT INTO "app_migrations" ("name","applied_at") VALUES ('hide_mbr001_bk004_history_v1','2026-11-27 03:18:32');
DELETE FROM sqlite_sequence;
INSERT INTO sqlite_sequence(name,seq) VALUES ('users',6);
INSERT INTO sqlite_sequence(name,seq) VALUES ('publishers',4);
INSERT INTO sqlite_sequence(name,seq) VALUES ('books',12);
INSERT INTO sqlite_sequence(name,seq) VALUES ('borrowings',8);
INSERT INTO sqlite_sequence(name,seq) VALUES ('fines',1);
INSERT INTO sqlite_sequence(name,seq) VALUES ('attendances',1);
INSERT INTO sqlite_sequence(name,seq) VALUES ('budgets',9);
INSERT INTO sqlite_sequence(name,seq) VALUES ('complaints',1);
INSERT INTO sqlite_sequence(name,seq) VALUES ('extensions',2);
COMMIT;
PRAGMA foreign_keys=ON;
