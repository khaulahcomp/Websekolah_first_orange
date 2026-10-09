<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Pembatas laju sederhana berbasis tabel `throttle` (dipakai login dan form kontak). */
class Throttle
{
	protected $CI;
	public function __construct() { $this->CI =& get_instance(); }

	public function hit($key) { $this->CI->db->insert('throttle', array('k' => $key, 't' => time())); }

	public function count($key, $window)
	{
		return (int) $this->CI->db->where('k', $key)->where('t >=', time() - (int) $window)->count_all_results('throttle');
	}

	// Detik tersisa sampai hitungan terlama keluar dari jendela
	public function seconds_left($key, $window, $limit)
	{
		$rows = $this->CI->db->select('t')->where('k', $key)->where('t >=', time() - (int) $window)->order_by('t', 'DESC')->limit((int) $limit)->get('throttle')->result();
		if (count($rows) < $limit) { return 0; }
		$oldest = (int) end($rows)->t;
		return max(0, $oldest + (int) $window - time());
	}

	public function clear($key) { $this->CI->db->where('k', $key)->delete('throttle'); }
}
