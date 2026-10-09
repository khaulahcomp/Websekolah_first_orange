<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Penjaga terpusat untuk semua controller admin:
 *  - cek login (sesi, atau cookie "ingat saya" yang di-hash & berputar tiap dipakai)
 *  - pembatasan peran (controller khusus admin)
 *  - wajib ganti password bila ditandai
 *  - verifikasi token CSRF + Origin untuk semua permintaan non-GET
 *  - pencatatan aksi (audit log)
 */
class Admin_guard
{
	protected $CI;

	public function __construct()
	{
		$this->CI =& get_instance();
		$this->CI->load->helper(array('url', 'cookie', 'security_app'));
		$this->CI->load->library('migrator');
		$this->CI->migrator->ensure();
	}

	public function require_admin()
	{
		$CI = $this->CI;
		if (!$CI->session->userdata('admin')) {
			if (!$this->login_from_cookie()) {
				redirect(base_url());
			}
		}
		$cls = strtolower($CI->router->class);

		// Peran
		$only = $CI->config->item('admin_only_controllers');
		if (is_array($only) && in_array($cls, $only, TRUE)) {
			$lv = strtolower(trim((string) $CI->session->userdata('level')));
			if ($lv !== 'admin' && $lv !== '') {
				$this->log('akses_ditolak', $cls . '/' . $CI->router->method);
				show_error('Anda tidak memiliki izin untuk membuka halaman ini.', 403, 'Akses ditolak');
			}
		}

		// Wajib ganti password
		if ($CI->session->userdata('wajib_ganti') && $cls !== 'akun') {
			redirect(base_url('setting-akun'));
		}

		// CSRF + audit untuk permintaan yang mengubah data
		if (!in_array(strtoupper($CI->input->method(FALSE)), array('GET', 'HEAD', 'OPTIONS'), TRUE)) {
			$this->verify_csrf();
			$this->audit_post();
		}
	}

	// ---------- "Ingat saya" ----------
	protected function login_from_cookie()
	{
		$CI = $this->CI;
		$raw = get_cookie('djehbicd');
		if (!is_string($raw) || !preg_match('/^[A-Za-z0-9]{64}$/', $raw)) { return FALSE; }
		$row = $CI->db->get_where('admin', array('cookie' => hash('sha256', $raw)))->row_array();
		if (!$row || (int) $row['cookie_expires'] < time()) { return FALSE; }

		$CI->session->sess_regenerate(TRUE);
		$CI->session->set_userdata(array('admin' => TRUE, 'id' => $row['id'], 'level' => $row['level'], 'wajib_ganti' => (int) $row['wajib_ganti'] === 1 ? 1 : 0));
		$CI->session->unset_userdata('csrf_admin');
		$this->issue_remember_cookie($row['id']);   // token berputar tiap dipakai
		$this->log('login_cookie', $row['username'], $row['id'], $row['username']);
		return TRUE;
	}

	public function issue_remember_cookie($admin_id)
	{
		$CI = $this->CI;
		$days = (int) ($CI->config->item('remember_days') ?: 14);
		$token = bin2hex(random_bytes(32));
		$CI->db->where('id', $admin_id)->update('admin', array('cookie' => hash('sha256', $token), 'cookie_expires' => time() + $days * 86400));
		set_cookie(array('name' => 'djehbicd', 'value' => $token, 'expire' => $days * 86400, 'secure' => is_https(), 'httponly' => TRUE, 'samesite' => 'Lax'));
	}

	public function revoke_remember($admin_id = NULL)
	{
		$CI = $this->CI;
		if ($admin_id) {
			$CI->db->where('id', $admin_id)->update('admin', array('cookie' => '', 'cookie_expires' => NULL));
		}
		$raw = get_cookie('djehbicd');
		if (is_string($raw) && preg_match('/^[A-Za-z0-9]{64}$/', $raw)) {
			$CI->db->where('cookie', hash('sha256', $raw))->update('admin', array('cookie' => '', 'cookie_expires' => NULL));
		}
		delete_cookie('djehbicd');
	}

	// ---------- CSRF ----------
	public function csrf_token() { return admin_csrf_token(); }

	protected function same_host($url)
	{
		$h = parse_url($url, PHP_URL_HOST);
		$cur = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
		$cur = preg_replace('/:\d+$/', '', $cur);
		return is_string($h) && strcasecmp($h, $cur) === 0;
	}

	protected function verify_csrf()
	{
		$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
		$ref = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
		if ($origin !== '' && $origin !== 'null' && !$this->same_host($origin)) { $this->csrf_fail('origin'); }
		if ($origin === '' && $ref !== '' && !$this->same_host($ref)) { $this->csrf_fail('referer'); }
		$sent = isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : (string) $this->CI->input->post('csrf_token');
		if (!hash_equals($this->csrf_token(), (string) $sent)) { $this->csrf_fail('token'); }
	}

	protected function csrf_fail($why)
	{
		$this->log('csrf_ditolak', $why . ' ' . $this->CI->router->class . '/' . $this->CI->router->method);
		set_status_header(403);
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode(array('status' => 'csrf', 'msg' => 'Sesi formulir kedaluwarsa atau tidak valid. Muat ulang halaman (Ctrl+F5) lalu coba lagi.'));
		exit;
	}

	// ---------- Audit ----------
	protected function audit_post()
	{
		$CI = $this->CI;
		$detail = $CI->router->class . '/' . $CI->router->method;
		foreach (array('id', 'kodedit', 'kode') as $k) {
			$v = $CI->input->post($k);
			if (is_scalar($v) && preg_match('/^[0-9]{1,10}$/', (string) $v)) { $detail .= ' #' . $v; break; }
		}
		$this->log('aksi', $detail);
	}

	public function log($aksi, $detail = '', $admin_id = NULL, $username = NULL)
	{
		$CI = $this->CI;
		if ($admin_id === NULL) { $admin_id = (int) $CI->session->userdata('id'); }
		if ($username === NULL && $admin_id) {
			$u = $CI->db->select('username')->get_where('admin', array('id' => $admin_id))->row();
			$username = $u ? $u->username : '';
		}
		$CI->db->insert('audit_log', array(
			'waktu'    => date('Y-m-d H:i:s'),
			'admin_id' => $admin_id ?: NULL,
			'username' => mb_substr((string) $username, 0, 50),
			'aksi'     => mb_substr((string) $aksi, 0, 40),
			'detail'   => mb_substr((string) $detail, 0, 300),
			'ip'       => $CI->input->ip_address(),
			'ua'       => mb_substr(isset($_SERVER['HTTP_USER_AGENT']) ? preg_replace('/[\x00-\x1F]/', '', $_SERVER['HTTP_USER_AGENT']) : '', 0, 150),
		));
	}
}
