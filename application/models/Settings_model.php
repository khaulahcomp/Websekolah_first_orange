<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Settings_model extends CI_Model {

    public function getSetting(){
        return $this->db->get('settings')->row_array();
    }

    public function create_slug($title)
    {
        $extract = explode(" ", $title, 6);
        unset($extract[5]);
        $combine = implode(" ", $extract);
        $lowercase = strtolower($combine);
        $preslug = url_title($lowercase);
        $slug = $preslug;
        $this->db->like('slug', $preslug, 'after');
        $checkslug = $this->db->get('berita');
        if ($checkslug->num_rows() > 0) {
            $num = (int)$checkslug->num_rows() + 1;
            $slug = $preslug . "-" . $num;
        }

        return $slug;
    }

    public function general(){
      return $this->db->get('general')->row_array();
    }



    // ------------------------------------------------------------------
    // Tema warna website (bisa diatur dari menu Setting)
    // ------------------------------------------------------------------
    const THEME_PRIMARY   = '#FF7350'; // warna utama bawaan
    const THEME_SECONDARY = '#125875'; // warna sekunder bawaan

    public function valid_hex($c){
      return is_string($c) && preg_match('/^#[0-9a-fA-F]{6}$/', $c) === 1;
    }

    // Warna yang sedang berlaku: pakai pilihan admin bila valid, selain itu bawaan.
    public function theme($general = NULL){
      if ($general === NULL) {
        $general = $this->general();
      }
      $p = isset($general['theme_color'])  ? $general['theme_color']  : NULL;
      $s = isset($general['theme_color2']) ? $general['theme_color2'] : NULL;
      $custom = $this->valid_hex($p) && $this->valid_hex($s);
      return array(
        'primary'   => $custom ? strtoupper($p) : self::THEME_PRIMARY,
        'secondary' => $custom ? strtoupper($s) : self::THEME_SECONDARY,
        'custom'    => $custom,
      );
    }

    // Campur warna dengan putih (untuk varian lebih terang)
    private function lighten($hex, $pct){
      $hex = ltrim($hex, '#');
      $out = '#';
      foreach (array(0, 2, 4) as $i) {
        $v = hexdec(substr($hex, $i, 2));
        $v = (int) round($v + (255 - $v) * $pct);
        $out .= str_pad(dechex(max(0, min(255, $v))), 2, '0', STR_PAD_LEFT);
      }
      return strtoupper($out);
    }

    // Tag <style> untuk front-end. Kosong bila admin belum mengubah tema,
    // sehingga tampilan bawaan tidak berubah sama sekali.
    public function theme_style($general = NULL){
      $th = $this->theme($general);
      if (!$th['custom']) {
        return '';
      }
      return '<style id="theme-custom">:root{'
        . '--theme-color:' . $th['primary'] . ';'
        . '--theme-color-light:' . $this->lighten($th['primary'], 0.12) . ';'
        . '--theme-color2:' . $th['secondary'] . ';'
        . '--footer-bg2:' . $th['secondary'] . ';'
        . '}</style>' . "\n";
    }

    // Tambah kolom otomatis bila belum ada (tidak perlu import SQL manual)
    public function ensure_theme_columns(){
      if ($this->db->field_exists('theme_color', 'general') && $this->db->field_exists('theme_color2', 'general')) {
        return TRUE;
      }
      $cols = array();
      if (!$this->db->field_exists('theme_color', 'general'))  { $cols[] = 'ADD `theme_color` VARCHAR(7) NULL DEFAULT NULL'; }
      if (!$this->db->field_exists('theme_color2', 'general')) { $cols[] = 'ADD `theme_color2` VARCHAR(7) NULL DEFAULT NULL'; }
      return (bool) $this->db->query('ALTER TABLE `general` ' . implode(', ', $cols));
    }

    // Simpan warna. Kirim NULL, NULL untuk mengembalikan ke bawaan.
    public function save_theme($primary, $secondary){
      if (!$this->ensure_theme_columns()) {
        return FALSE;
      }
      $this->db->where('id', 1);
      return (bool) $this->db->update('general', array(
        'theme_color'  => $primary,
        'theme_color2' => $secondary,
      ));
    }

}
