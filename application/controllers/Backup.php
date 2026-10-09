<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Admin: unduh cadangan database (hanya akun level admin). */
class Backup extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->helper(array('url', 'form'));
		$this->load->library('session');
		$this->load->library('admin_guard');
		$this->admin_guard->require_admin();
	}

	public function index()
	{
		$src = $this->db->database;
		$this->load->view('admin/page/backup', array(
			'judul' => 'Setting', 'subjudul' => 'Backup Database',
			'ukuran' => is_file($src) ? round(filesize($src) / 1024) : 0,
			'luar'   => (strpos(realpath($src) ?: $src, realpath(FCPATH) ?: FCPATH) !== 0),
		));
	}

	// POST + token CSRF (diverifikasi guard)
	public function unduh()
	{
		if (strtoupper($this->input->method(FALSE)) !== 'POST') { redirect('backup'); }
		$src = $this->db->database;
		$tmp = APPPATH . 'cache/backup-' . bin2hex(random_bytes(6)) . '.sqlite';
		$ok = $this->db->query("VACUUM INTO '" . str_replace("'", "''", $tmp) . "'");
		if (!$ok || !is_file($tmp)) { @copy($src, $tmp); }   // cadangan bila VACUUM INTO tak didukung
		if (!is_file($tmp)) { show_error('Cadangan gagal dibuat.', 500); }
		$this->admin_guard->log('backup_unduh', '');
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="sekolah-' . date('Ymd-His') . '.sqlite"');
		header('Content-Length: ' . filesize($tmp));
		header('Cache-Control: no-store');
		readfile($tmp);
		@unlink($tmp);
		exit;
	}
}
