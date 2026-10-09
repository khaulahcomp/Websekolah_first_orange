<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migrasi database otomatis & aman (idempotent).
 * Versi tersimpan di PRAGMA user_version. Hanya MENAMBAH tabel/kolom; tidak menghapus atau
 * mengubah data pengguna (kecuali men-hash token "ingat saya" lama agar tidak tersimpan polos).
 */
class Migrator
{
	const VERSION = 1;
	protected $CI;

	public function __construct() { $this->CI =& get_instance(); }

	public function ensure()
	{
		static $done = FALSE;
		if ($done) { return; }
		$done = TRUE;
		$db = $this->CI->db;
		if (!isset($db->conn_id) || !is_object($db->conn_id)) { return; }
		if (method_exists($db->conn_id, 'busyTimeout')) { $db->conn_id->busyTimeout(8000); }
		$r = $db->query('PRAGMA user_version');
		if (!$r) { return; }
		$row = $r->row_array();
		if ((int) (isset($row['user_version']) ? $row['user_version'] : 0) >= self::VERSION) { return; }
		$this->up();
	}

	protected function q($sql)
	{
		$r = $this->CI->db->query($sql);
		if ($r === FALSE) { throw new Exception('SQL gagal: ' . $sql); }
		return $r;
	}

	protected function columns($table)
	{
		$cols = array();
		$q = $this->CI->db->query('PRAGMA table_info(' . $table . ')');
		if ($q) { foreach ($q->result_array() as $c) { $cols[] = $c['name']; } }
		return $cols;
	}

	protected function up()
	{
		$db = $this->CI->db;
		$db->query('BEGIN IMMEDIATE');
		// periksa ulang di dalam kunci (proses lain mungkin sudah menyelesaikan)
		$row = $db->query('PRAGMA user_version')->row_array();
		if ((int) $row['user_version'] >= self::VERSION) { $db->query('COMMIT'); return; }
		try {
			$this->q('CREATE TABLE IF NOT EXISTS throttle (id INTEGER PRIMARY KEY AUTOINCREMENT, k TEXT NOT NULL, t INTEGER NOT NULL)');
			$this->q('CREATE INDEX IF NOT EXISTS idx_throttle_k ON throttle(k, t)');
			$this->q('CREATE TABLE IF NOT EXISTS audit_log (id INTEGER PRIMARY KEY AUTOINCREMENT, waktu TEXT NOT NULL, admin_id INTEGER, username TEXT, aksi TEXT NOT NULL, detail TEXT, ip TEXT, ua TEXT)');
			$this->q('CREATE INDEX IF NOT EXISTS idx_audit_waktu ON audit_log(waktu)');
			$this->q('CREATE TABLE IF NOT EXISTS app_meta (k TEXT PRIMARY KEY, v TEXT)');

			$cols = $this->columns('admin');
			if (!in_array('cookie_expires', $cols, TRUE)) { $this->q('ALTER TABLE admin ADD COLUMN cookie_expires INTEGER'); }
			if (!in_array('wajib_ganti', $cols, TRUE))    { $this->q('ALTER TABLE admin ADD COLUMN wajib_ganti INTEGER NOT NULL DEFAULT 0'); }
			if (!in_array('last_login', $cols, TRUE))     { $this->q('ALTER TABLE admin ADD COLUMN last_login TEXT'); }

			$days = (int) ($this->CI->config->item('remember_days') ?: 14);
			foreach ($this->q('SELECT id, password, cookie FROM admin')->result_array() as $a) {
				// token ingat-saya lama (polos) -> disimpan sebagai hash SHA-256; cookie di browser tetap berlaku
				if (preg_match('/^[A-Za-z0-9]{64}$/', (string) $a['cookie'])) {
					$db->where('id', $a['id'])->update('admin', array('cookie' => hash('sha256', $a['cookie']), 'cookie_expires' => time() + $days * 86400));
				}
				// akun yang masih memakai password awal paket wajib menggantinya
				foreach (array('Awal-mYEyw2Ruug', 'Awal-pbBfWiy') as $default_pw) {
					if (password_verify($default_pw, (string) $a['password'])) {
						$db->where('id', $a['id'])->update('admin', array('wajib_ganti' => 1));
						break;
					}
				}
			}
			$this->q('PRAGMA user_version = ' . (int) self::VERSION);
			$this->q('COMMIT');
		} catch (Exception $e) {
			$db->query('ROLLBACK');
			log_message('error', 'Migrasi gagal: ' . $e->getMessage());
			return;
		}
		$this->CI->load->helper('security_app');
		app_secret(); // buat kunci rahasia situs bila belum ada
	}
}
