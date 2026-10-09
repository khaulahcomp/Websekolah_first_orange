<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Hook keamanan: header, migrasi database, dan pembersihan harian. */
class Security_hook
{
	public function run()
	{
		$CI =& get_instance();
		if (!isset($CI->db)) { return; }
		$CI->load->helper('security_app');
		security_headers();
		$CI->load->library('migrator');
		$CI->migrator->ensure();
		try { $this->housekeeping($CI); } catch (Exception $e) { log_message('error', 'Housekeeping: ' . $e->getMessage()); }
	}

	protected function housekeeping($CI)
	{
		$db = $CI->db;
		$r = $db->query("SELECT v FROM app_meta WHERE k = 'hk_last'");
		if (!$r) { return; }
		$row = $r->row_array();
		$last = $row ? (int) $row['v'] : 0;
		if (time() - $last < 86400) { return; }
		// ambil "giliran" dengan UPDATE bersyarat agar hanya satu proses yang bekerja
		$db->query("INSERT OR IGNORE INTO app_meta (k, v) VALUES ('hk_last', '0')");
		$db->query("UPDATE app_meta SET v = '" . time() . "' WHERE k = 'hk_last' AND CAST(v AS INTEGER) = " . (int) $last);
		if ($db->affected_rows() < 1) { return; }

		$db->where('t <', time() - 172800)->delete('throttle');
		$ad = (int) ($CI->config->item('audit_retention_days') ?: 365);
		$db->where('waktu <', date('Y-m-d H:i:s', time() - $ad * 86400))->delete('audit_log');

		$vd = (int) $CI->config->item('visitor_ip_retention_days');
		if ($vd > 0) {
			$cut = date('Y-m-d H:i:s', time() - $vd * 86400);
			$db->query("UPDATE visitors SET ip_address = '-' WHERE ip_address != '-' AND visit_time < " . $db->escape($cut));
		}
		// log error aplikasi lebih dari 30 hari
		foreach ((array) glob(APPPATH . 'logs/log-*.php') as $lf) {
			if (is_file($lf) && filemtime($lf) < time() - 30 * 86400) { @unlink($lf); }
		}
		// berkas sesi lama di folder sesi aplikasi
		$dir = APPPATH . 'sessions/';
		if (is_dir($dir)) {
			$ttl = 2 * (int) $CI->config->item('sess_expiration');
			$n = 0;
			foreach ((array) glob($dir . 'ci_session*') as $f) {
				if ($n++ > 300) { break; }
				if (is_file($f) && filemtime($f) < time() - max(7200, $ttl)) { @unlink($f); }
			}
		}
	}
}
