<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model Album Foto
 * Struktur: album (1) -> album_foto (banyak).
 * File gambar: uploads/album/<file>  dan thumbnail: uploads/album/thumbs/<file>
 */
class Album_model extends CI_Model {

    public $dir;
    public $thumbs;

    public function __construct()
    {
        parent::__construct();
        $this->dir    = FCPATH . 'uploads/album/';
        $this->thumbs = FCPATH . 'uploads/album/thumbs/';
    }

    // Daftar album + jumlah foto. $only_with_photos = TRUE untuk tampilan publik.
    public function all_with_count($only_with_photos = FALSE, $limit = NULL)
    {
        $sql = "SELECT a.*, (SELECT COUNT(*) FROM album_foto f WHERE f.album_id = a.id) AS jumlah FROM album a";
        if ($only_with_photos) {
            $sql .= " WHERE EXISTS (SELECT 1 FROM album_foto f2 WHERE f2.album_id = a.id)";
        }
        $sql .= " ORDER BY a.waktu DESC, a.id DESC";
        if ($limit) {
            $sql .= " LIMIT " . (int) $limit;
        }
        return $this->db->query($sql)->result();
    }

    public function by_id($id)
    {
        return $this->db->get_where('album', array('id' => (int) $id))->row();
    }

    public function by_slug($slug)
    {
        return $this->db->get_where('album', array('slug' => (string) $slug))->row();
    }

    public function photos($album_id)
    {
        $this->db->order_by('urutan', 'ASC');
        $this->db->order_by('id', 'ASC');
        return $this->db->get_where('album_foto', array('album_id' => (int) $album_id))->result();
    }

    public function count_photos($album_id)
    {
        return (int) $this->db->where('album_id', (int) $album_id)->count_all_results('album_foto');
    }

    // Slug unik dari judul album (contoh: "Wisuda 2026" -> "wisuda-2026", lalu -2, -3, ...)
    public function unique_slug($title, $ignore_id = 0)
    {
        $base = trim(url_title(convert_accented_characters($title), '-', TRUE), '-');
        if ($base === '') {
            $base = 'album';
        }
        $slug = $base;
        $n = 1;
        while (TRUE) {
            $this->db->where('slug', $slug);
            if ($ignore_id) {
                $this->db->where('id !=', (int) $ignore_id);
            }
            if ($this->db->count_all_results('album') == 0) {
                return $slug;
            }
            $n++;
            $slug = $base . '-' . $n;
        }
    }

    // Hapus berkas gambar dan thumbnail-nya. Hanya nama berkas (basename) yang dipakai.
    public function delete_files($file)
    {
        $file = basename((string) $file);
        if ($file === '' || $file === '.' || $file === '..') {
            return;
        }
        foreach (array($this->dir . $file, $this->thumbs . $file) as $p) {
            if (is_file($p)) {
                @unlink($p);
            }
        }
    }

    // Pastikan sampul album selalu menunjuk ke foto yang masih ada.
    public function refresh_cover($album_id)
    {
        $album = $this->by_id($album_id);
        if (!$album) {
            return;
        }
        $ok = FALSE;
        if ($album->cover !== NULL && $album->cover !== '') {
            $ok = $this->db->where('album_id', (int) $album_id)->where('file', $album->cover)->count_all_results('album_foto') > 0;
        }
        if (!$ok) {
            $first = $this->photos($album_id);
            $this->db->where('id', (int) $album_id)->update('album', array('cover' => $first ? $first[0]->file : ''));
        }
    }
}
