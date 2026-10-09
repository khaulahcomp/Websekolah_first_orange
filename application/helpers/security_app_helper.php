<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Fungsi bantu keamanan aplikasi (dimuat otomatis lewat autoload).
 */

// Cadangan bila ekstensi mbstring tidak aktif di hosting
if (!function_exists('mb_substr')) { function mb_substr($s, $start, $len = NULL, $enc = NULL) { return $len === NULL ? substr($s, $start) : substr($s, $start, $len); } }
if (!function_exists('mb_strlen')) { function mb_strlen($s, $enc = NULL) { return strlen($s); } }

if (!function_exists('is_https')) {
	function is_https()
	{
		if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') { return TRUE; }
		if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') { return TRUE; }
		if (isset($_SERVER['HTTP_CF_VISITOR']) && strpos($_SERVER['HTTP_CF_VISITOR'], 'https') !== FALSE) { return TRUE; }
		return FALSE;
	}
}

if (!function_exists('security_headers')) {
	// Cadangan header keamanan lewat PHP (bila .htaccess/mod_headers tidak aktif).
	function security_headers()
	{
		if (headers_sent()) { return; }
		header_remove('X-Powered-By');
		header('X-Content-Type-Options: nosniff');
		header('X-Frame-Options: SAMEORIGIN');
		header('Referrer-Policy: strict-origin-when-cross-origin');
		header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=()');
		header("Content-Security-Policy: object-src 'none'; base-uri 'self'; frame-ancestors 'self'; form-action 'self'");
		// Kebijakan penuh dalam mode "report-only": hanya melapor di konsol browser (F12), tidak memblokir.
		header("Content-Security-Policy-Report-Only: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; img-src 'self' data: https:; font-src 'self' data: https://fonts.gstatic.com; frame-src https://www.youtube.com https://www.youtube-nocookie.com https://www.google.com https://maps.google.com https://player.vimeo.com; connect-src 'self'; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'");
		if (is_https()) { header('Strict-Transport-Security: max-age=15552000'); }
	}
}

if (!function_exists('app_secret')) {
	// Kunci rahasia unik per situs (dibuat otomatis sekali, disimpan di application/config/app_secret.php).
	function app_secret()
	{
		static $key = NULL;
		if ($key !== NULL) { return $key; }
		$file = APPPATH . 'config/app_secret.php';
		if (is_file($file)) {
			$k = include $file;
			if (is_string($k) && strlen($k) >= 32) { return $key = $k; }
		}
		$k = base64_encode(random_bytes(32));
		@file_put_contents($file, "<?php defined('BASEPATH') OR exit('No direct script access allowed');\nreturn '" . $k . "';\n", LOCK_EX);
		if (!is_file($file)) { $k = hash('sha256', __FILE__ . php_uname() . APPPATH); }
		return $key = $k;
	}
}

if (!function_exists('safe_url')) {
	// Hanya http/https. "youtube.com/@x" -> "https://youtube.com/@x". Selain itu -> "#".
	function safe_url($u)
	{
		$u = trim((string) $u);
		if ($u === '' || $u === '#') { return '#'; }
		if (strpos($u, '//') === 0) { $u = 'https:' . $u; }
		elseif (!preg_match('/^[a-z][a-z0-9+.\-]*:/i', $u)) { $u = 'https://' . ltrim($u, '/'); }
		$p = @parse_url($u);
		if (!$p || empty($p['host']) || !in_array(strtolower(isset($p['scheme']) ? $p['scheme'] : ''), array('http', 'https'), TRUE)) { return '#'; }
		if (!preg_match('/^[a-z0-9.\-]+$/i', $p['host']) || strpos($p['host'], '.') === FALSE) { return '#'; }
		return $u;
	}
}

if (!function_exists('tel_number')) {
	// 0882... -> +62882..., 62882... -> +62882...
	function tel_number($n)
	{
		$d = preg_replace('/[^0-9]/', '', (string) $n);
		if ($d === '') { return ''; }
		if ($d[0] === '0') { $d = '62' . substr($d, 1); }
		return '+' . $d;
	}
}

if (!function_exists('plain_text')) {
	// Teks polos: tanpa tag, tanpa karakter kontrol, spasi dirapikan, dipotong $max karakter.
	function plain_text($s, $max = 200)
	{
		$s = strip_tags((string) $s);
		$s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $s);
		$s = preg_replace('/[ \t]+/u', ' ', $s);
		$s = trim($s);
		return mb_substr($s, 0, $max, 'UTF-8');
	}
}

if (!function_exists('clean_html')) {
	// Bersihkan HTML buatan editor (daftar putih tag/atribut). Dipakai saat menampilkan ke pengunjung.
	function clean_html($html)
	{
		if ($html === NULL || $html === '') { return ''; }
		require_once APPPATH . 'libraries/Html_sanitizer.php';
		return Html_sanitizer::clean((string) $html);
	}
}

if (!function_exists('password_policy_error')) {
	// Kembalikan pesan kesalahan, atau '' bila password memenuhi syarat.
	function password_policy_error($pw, $username = '')
	{
		$pw = (string) $pw;
		if (mb_strlen($pw, 'UTF-8') < 10) { return 'Password minimal 10 karakter.'; }
		if (strlen($pw) > 72) { return 'Password maksimal 72 karakter.'; }
		if (!preg_match('/[A-Za-z]/', $pw) || !preg_match('/[0-9]/', $pw)) { return 'Password harus mengandung huruf dan angka.'; }
		$l = strtolower($pw);
		$leet = strtr($l, array('0' => 'o', '1' => 'l', '3' => 'e', '4' => 'a', '5' => 's', '7' => 't', '@' => 'a', '$' => 's', '!' => 'i'));
		if ($username !== '' && (stripos($l, strtolower($username)) !== FALSE || stripos($leet, strtolower($username)) !== FALSE)) { return 'Password tidak boleh mengandung username.'; }
		foreach (array('password', 'passw0rd', '12345678', 'qwerty', 'abcdef', 'sekolah', 'admin123', 'letmein', 'welcome', 'awal-') as $bad) {
			if (strpos($l, $bad) !== FALSE || strpos($leet, $bad) !== FALSE) { return 'Password terlalu mudah ditebak.'; }
		}
		if (preg_match('/^(.)\1+$/', $pw) || preg_match('/(.)\1{5,}/', $pw)) { return 'Password terlalu mudah ditebak.'; }
		return '';
	}
}

if (!function_exists('admin_csrf_token')) {
	// Token CSRF sesi admin (untuk meta tag/form).
	function admin_csrf_token()
	{
		$CI =& get_instance();
		$t = $CI->session->userdata('csrf_admin');
		if (!is_string($t) || strlen($t) < 32) {
			$t = bin2hex(random_bytes(32));
			$CI->session->set_userdata('csrf_admin', $t);
		}
		return $t;
	}
}

if (!function_exists('contact_form_fields')) {
	// Kolom tersembunyi form kontak: token sesi + honeypot (jebakan bot).
	function contact_form_fields()
	{
		$CI =& get_instance();
		$tok = $CI->session->userdata('ct_tok');
		$ts = (int) $CI->session->userdata('ct_ts');
		if (!is_string($tok) || $tok === '' || (time() - $ts) > 7200) {
			$tok = bin2hex(random_bytes(16));
			$CI->session->set_userdata(array('ct_tok' => $tok, 'ct_ts' => time()));
		}
		return '<input type="hidden" name="ct" value="' . html_escape($tok) . '">'
			. '<div style="position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden" aria-hidden="true">'
			. '<label>Jangan diisi <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>';
	}
}
