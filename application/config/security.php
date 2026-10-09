<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| Pengaturan keamanan aplikasi. Ubah di sini bila perlu.
*/
// Controller yang hanya boleh dibuka akun level "admin" (operator ditolak).
$config['admin_only_controllers'] = array('usermanajemen', 'setting', 'logaktivitas', 'backup');

// Login: maksimal gagal per (IP + username) dalam 15 menit, dan per IP.
$config['login_max_user_ip'] = 5;
$config['login_max_ip']      = 20;
$config['login_window']      = 900;   // detik

// Cookie "ingat saya": masa berlaku (hari).
$config['remember_days'] = 14;

// Form kontak publik: batas kirim per IP.
$config['kontak_max_10min'] = 3;
$config['kontak_max_day']   = 15;
$config['kontak_min_seconds'] = 4;    // formulir yang dikirim lebih cepat dari ini dianggap bot

// Privasi: alamat IP kunjungan lebih tua dari N hari dikaburkan (jumlah pengunjung tetap utuh). 0 = nonaktif.
$config['visitor_ip_retention_days'] = 180;

// Retensi log aktivitas admin (hari).
$config['audit_retention_days'] = 365;
