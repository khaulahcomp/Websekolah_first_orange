-- Skema SQLite untuk Website Sekolah (versi mulai dari nol)
-- Dibuat ulang dengan:  sqlite3 sekolah.sqlite < schema.sql
PRAGMA journal_mode = DELETE;

CREATE TABLE admin (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  username TEXT NOT NULL DEFAULT '',
  password TEXT NOT NULL DEFAULT '',
  cookie TEXT NOT NULL DEFAULT '',
  level TEXT, photo_profile TEXT, email TEXT, nama TEXT
);
CREATE UNIQUE INDEX idx_admin_username ON admin(username);

CREATE TABLE general (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  app_name TEXT NOT NULL DEFAULT '', nama_sekolah TEXT NOT NULL DEFAULT '',
  host_mail TEXT NOT NULL DEFAULT '', port_mail TEXT NOT NULL DEFAULT '', crypto_smtp TEXT NOT NULL DEFAULT '',
  account_gmail TEXT NOT NULL DEFAULT '', pass_gmail TEXT NOT NULL DEFAULT '', whatsapp TEXT NOT NULL DEFAULT '',
  logo TEXT, alamat TEXT, instagram TEXT, facebook TEXT, twitter TEXT, youtube TEXT,
  jurusan TEXT, breadcumb TEXT, map TEXT,
  theme_color TEXT, theme_color2 TEXT
);

CREATE TABLE slider (id INTEGER PRIMARY KEY AUTOINCREMENT, foto TEXT, keterangan TEXT, button_capt TEXT, button_link TEXT);
CREATE TABLE profil (id INTEGER PRIMARY KEY AUTOINCREMENT, profil TEXT);
CREATE TABLE visi (id INTEGER PRIMARY KEY AUTOINCREMENT, visi TEXT);
CREATE TABLE struktur (id INTEGER PRIMARY KEY AUTOINCREMENT, struktur TEXT);
CREATE TABLE denah (id INTEGER PRIMARY KEY AUTOINCREMENT, denah TEXT);
CREATE TABLE denahbangunan (id INTEGER PRIMARY KEY AUTOINCREMENT, denahbangunan TEXT);
CREATE TABLE sambutan (id INTEGER PRIMARY KEY AUTOINCREMENT, sambutan TEXT, foto_kepsek TEXT, excerpt TEXT);
CREATE TABLE siswa (id INTEGER PRIMARY KEY AUTOINCREMENT, kelas TEXT, jumlah TEXT);
CREATE TABLE guru (id INTEGER PRIMARY KEY AUTOINCREMENT, nama TEXT, nbm TEXT, pendidikan TEXT, alumni TEXT, jabatan TEXT, foto TEXT);

CREATE TABLE berita (
  id INTEGER PRIMARY KEY AUTOINCREMENT, user INTEGER, judul TEXT, slug TEXT, isi TEXT,
  waktu TEXT, foto TEXT, view_count INTEGER DEFAULT 0, excerpt TEXT
);
CREATE INDEX idx_berita_slug ON berita(slug);

CREATE TABLE ekskul    (id INTEGER PRIMARY KEY AUTOINCREMENT, judul TEXT, foto TEXT, "desc" TEXT, slug TEXT);
CREATE TABLE jurusan   (id INTEGER PRIMARY KEY AUTOINCREMENT, judul TEXT, foto TEXT, "desc" TEXT, slug TEXT);
CREATE TABLE keutamaan (id INTEGER PRIMARY KEY AUTOINCREMENT, judul TEXT, foto TEXT, "desc" TEXT, slug TEXT);
CREATE TABLE industri  (id INTEGER PRIMARY KEY AUTOINCREMENT, judul TEXT, foto TEXT, slug TEXT);
CREATE TABLE testimoni (id INTEGER PRIMARY KEY AUTOINCREMENT, nama TEXT, foto TEXT, "desc" TEXT, slug TEXT);
CREATE TABLE file      (id INTEGER PRIMARY KEY AUTOINCREMENT, file TEXT, keterangan TEXT);
CREATE TABLE video     (id INTEGER PRIMARY KEY AUTOINCREMENT, url TEXT, judul TEXT, "desc" TEXT, waktu TEXT, user INTEGER, slug TEXT);
CREATE TABLE kontak    (id INTEGER PRIMARY KEY AUTOINCREMENT, nama TEXT, email TEXT, subjek TEXT, pesan TEXT, waktu TEXT);
CREATE TABLE tbl_rencanakegiatan (
  id INTEGER PRIMARY KEY AUTOINCREMENT, tanggal TEXT, tanggal_selesai TEXT, nama_kegiatan TEXT, keterangan TEXT
);
CREATE TABLE visitors (
  id INTEGER PRIMARY KEY AUTOINCREMENT, ip_address TEXT NOT NULL DEFAULT '', visit_time TEXT NOT NULL DEFAULT ''
);
CREATE INDEX idx_visitors_time ON visitors(visit_time);

-- Galeri Foto berbasis Album
CREATE TABLE album (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  judul TEXT NOT NULL DEFAULT '', slug TEXT NOT NULL DEFAULT '', deskripsi TEXT,
  cover TEXT, waktu TEXT, user INTEGER
);
CREATE UNIQUE INDEX idx_album_slug ON album(slug);
CREATE TABLE album_foto (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  album_id INTEGER NOT NULL, file TEXT NOT NULL DEFAULT '', keterangan TEXT,
  urutan INTEGER NOT NULL DEFAULT 0, waktu TEXT
);
CREATE INDEX idx_album_foto_album ON album_foto(album_id);
