<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Admin: Log Aktivitas (hanya akun level admin). */
class Logaktivitas extends CI_Controller
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
		$this->load->view('admin/page/logaktivitas', array('judul' => 'Setting', 'subjudul' => 'Log Aktivitas'));
	}

	public function getdata()
	{
		$rows = $this->db->order_by('id', 'DESC')->limit(1000)->get('audit_log')->result();
		$badge = array(
			'login_sukses' => 'success', 'login_cookie' => 'success', 'logout' => 'secondary', 'aksi' => 'info',
			'login_gagal' => 'danger', 'login_dikunci' => 'danger', 'csrf_ditolak' => 'warning', 'akses_ditolak' => 'warning',
			'ganti_password' => 'primary', 'backup_unduh' => 'primary',
		);
		$data = array();
		foreach ($rows as $r) {
			$cls = isset($badge[$r->aksi]) ? $badge[$r->aksi] : 'secondary';
			$data[] = array(
				html_escape($r->waktu),
				html_escape($r->username),
				'<span class="badge bg-label-' . $cls . '">' . html_escape($r->aksi) . '</span>',
				html_escape($r->detail),
				html_escape($r->ip),
			);
		}
		$this->output->set_content_type('application/json')->set_output(json_encode(array('data' => $data)));
	}
}
