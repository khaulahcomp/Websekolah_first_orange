<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Login extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->library('form_validation');
		$this->load->helper(array('cookie', 'url', 'security_app'));
		$this->load->library('admin_guard');   // juga menjalankan migrasi database otomatis
		$this->load->library('throttle');
	}

	public function index()
	{
		$data['title'] =  $this->Settings_model->general()["app_name"];
		$this->load->view('admin/auth/login',$data);
	}

	public function proses(){
		if (strtoupper($this->input->method(FALSE)) !== 'POST') { echo 'error'; return; }

		$username = mb_substr(trim((string) $this->input->post('username')), 0, 50);
		$password = (string) $this->input->post('password');
		$remember = $this->input->post('cookie');
		$ip  = $this->input->ip_address();
		$win = (int) $this->config->item('login_window');
		$maxUI = (int) $this->config->item('login_max_user_ip');
		$maxIP = (int) $this->config->item('login_max_ip');
		$kui = 'login:ui:' . sha1(strtolower($username) . '|' . $ip);
		$ki  = 'login:ip:' . $ip;

		// Terkunci sementara bila terlalu banyak percobaan gagal
		if ($this->throttle->count($kui, $win) >= $maxUI || $this->throttle->count($ki, $win) >= $maxIP) {
			$left = max($this->throttle->seconds_left($kui, $win, $maxUI), $this->throttle->seconds_left($ki, $win, $maxIP));
			$this->admin_guard->log('login_dikunci', $username, 0, $username);
			echo 'locked:' . max(1, (int) ceil($left / 60));
			return;
		}

		$admin = $this->db->get_where('admin', ['username' => $username])->row_array();
		// Selalu jalankan verifikasi (hash kosong bila akun tidak ada) agar waktu respons serupa
		$hash = $admin ? $admin['password'] : '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG';
		$ok = password_verify($password, $hash) && $admin;

		if (!$ok) {
			$this->throttle->hit($kui);
			$this->throttle->hit($ki);
			$this->admin_guard->log('login_gagal', $username, 0, $username);
			usleep(random_int(250000, 450000));
			echo 'error';
			return;
		}

		$this->throttle->clear($kui);
		$this->session->sess_regenerate(TRUE);   // ID sesi baru (cegah session fixation)
		$this->session->unset_userdata('csrf_admin');
		$this->session->set_userdata(array(
			'admin' => TRUE,
			'id' => $admin['id'],
			'level' => $admin['level'],
			'wajib_ganti' => ((int) $admin['wajib_ganti'] === 1) ? 1 : 0,
		));

		if ($remember != NULL) {
			$this->admin_guard->issue_remember_cookie($admin['id']);
		} else {
			delete_cookie('djehbicd');
		}
		if (password_needs_rehash($admin['password'], PASSWORD_DEFAULT)) {
			$this->db->where('id', $admin['id'])->update('admin', array('password' => password_hash($password, PASSWORD_DEFAULT)));
		}
		$this->db->where('id', $admin['id'])->update('admin', array('last_login' => date('Y-m-d H:i:s')));
		$this->admin_guard->log('login_sukses', $remember != NULL ? 'ingat saya' : '', $admin['id'], $admin['username']);
		echo 'success';
	}

	public function logout(){
		$id = $this->session->userdata('id');
		$this->admin_guard->log('logout', '', $id ?: 0);
		$this->admin_guard->revoke_remember($id);   // cabut token "ingat saya" (DB + cookie)
		$this->session->sess_destroy();
		redirect('login/admin','refresh');
	}
}
