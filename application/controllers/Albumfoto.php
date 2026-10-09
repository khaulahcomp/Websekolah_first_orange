<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Admin: Galeri > Album Foto
 * Satu album berisi banyak foto. Foto diunggah satu per satu (AJAX) sehingga
 * tetap aman terhadap batas ukuran upload di shared hosting.
 */
class Albumfoto extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->helper(array('form', 'url', 'cookie', 'text'));
        $this->load->model('Album_model');

        // cek session login
        $this->load->library('admin_guard');
		$this->admin_guard->require_admin();
    }

    private function _json($arr)
    {
        $this->output->set_content_type('application/json')->set_output(json_encode($arr));
    }

    // ---------- Daftar album ----------
    public function index()
    {
        $this->load->view('admin/page/albumfoto', array(
            'judul'    => 'Galeri',
            'subjudul' => 'Album Foto',
        ));
    }

    public function getdata()
    {
        $data = array();
        $no = 0;
        foreach ($this->Album_model->all_with_count() as $a) {
            $no++;
            $cover = ($a->cover !== '' && $a->cover !== NULL)
                ? '<img src="' . base_url('uploads/album/thumbs/' . rawurlencode($a->cover)) . '" alt="" style="width:90px;height:60px;object-fit:cover;border-radius:6px">'
                : '<span class="text-muted small">Belum ada foto</span>';
            $data[] = array(
                $no,
                $cover,
                htmlspecialchars($a->judul, ENT_QUOTES, 'UTF-8'),
                '<span class="badge bg-label-primary">' . (int) $a->jumlah . ' foto</span>',
                '<span class="badge bg-label-info me-1"><i class="fas fa-calendar-alt"></i> ' . date('d M Y', strtotime($a->waktu)) . '</span>',
                '<a href="' . base_url('albumfoto/kelola/' . (int) $a->id) . '" class="btn btn-primary btn-sm"><i class="fas fa-images"></i> Kelola Foto</a> '
                . '<a href="javascript:;" class="btn btn-warning btn-sm bedit" data="' . (int) $a->id . '" title="Ubah judul/deskripsi"><i class="fa fa-edit nav-icon"></i></a> '
                . '<a href="javascript:;" class="btn btn-danger btn-sm bhapus" data="' . (int) $a->id . '" title="Hapus album"><i class="fa fa-trash nav-icon"></i></a>',
            );
        }
        $this->_json(array('data' => $data));
    }

    public function simpan()
    {
        $judul = trim((string) $this->input->post('judul'));
        $deskripsi = trim((string) $this->input->post('deskripsi'));
        if ($judul === '' || mb_strlen($judul) > 100) {
            return $this->_json(array('status' => 'invalid', 'msg' => 'Judul album wajib diisi (maksimal 100 karakter).'));
        }
        $ok = $this->db->insert('album', array(
            'judul'     => $judul,
            'slug'      => $this->Album_model->unique_slug($judul),
            'deskripsi' => $deskripsi,
            'cover'     => '',
            'waktu'     => date('Y-m-d H:i:s'),
            'user'      => (int) $this->session->userdata('id'),
        ));
        $this->_json($ok ? array('status' => 'success', 'id' => $this->db->insert_id()) : array('status' => 'error'));
    }

    public function showedit()
    {
        $a = $this->Album_model->by_id($this->input->get('id'));
        if (!$a) {
            return $this->_json(array('status' => 'error'));
        }
        $this->_json(array('status' => 'success', 'id' => $a->id, 'judul' => $a->judul, 'deskripsi' => $a->deskripsi));
    }

    public function simpanedit()
    {
        $id = (int) $this->input->post('id');
        $judul = trim((string) $this->input->post('judul'));
        if (!$this->Album_model->by_id($id) || $judul === '' || mb_strlen($judul) > 100) {
            return $this->_json(array('status' => 'invalid', 'msg' => 'Judul album wajib diisi (maksimal 100 karakter).'));
        }
        // Slug tidak diubah agar tautan album yang sudah dibagikan tetap berlaku.
        $ok = $this->db->where('id', $id)->update('album', array(
            'judul'     => $judul,
            'deskripsi' => trim((string) $this->input->post('deskripsi')),
        ));
        $this->_json(array('status' => $ok ? 'success' : 'error'));
    }

    public function hapus()
    {
        $id = (int) $this->input->post('id');
        if (!$this->Album_model->by_id($id)) {
            return $this->_json(array('status' => 'error'));
        }
        foreach ($this->Album_model->photos($id) as $p) {
            $this->Album_model->delete_files($p->file);
        }
        $this->db->where('album_id', $id)->delete('album_foto');
        $this->db->where('id', $id)->delete('album');
        $this->_json(array('status' => 'success'));
    }

    // ---------- Kelola foto dalam satu album ----------
    public function kelola($id = 0)
    {
        $album = $this->Album_model->by_id($id);
        if (!$album) {
            show_404();
        }
        $this->load->view('admin/page/albumfoto_kelola', array(
            'judul'    => 'Album Foto',
            'subjudul' => $album->judul,
            'album'    => $album,
            'photos'   => $this->Album_model->photos($album->id),
        ));
    }

    // Terima SATU berkas per permintaan (field: foto)
    public function upload($album_id = 0)
    {
        $album = $this->Album_model->by_id($album_id);
        if (!$album) {
            return $this->_json(array('ok' => FALSE, 'msg' => 'Album tidak ditemukan.'));
        }
        if (empty($_FILES['foto']['name'])) {
            return $this->_json(array('ok' => FALSE, 'msg' => 'Tidak ada berkas yang dikirim.'));
        }
        if (!is_dir($this->Album_model->thumbs)) {
            @mkdir($this->Album_model->thumbs, 0755, TRUE);
        }

        $this->upload->initialize(array(
            'upload_path'   => $this->Album_model->dir,
            'allowed_types' => 'jpg|jpeg|png|gif',
            'max_size'      => 10240,   // 10 MB per foto
            'encrypt_name'  => TRUE,
        ));
        if (!$this->upload->do_upload('foto')) {
            return $this->_json(array('ok' => FALSE, 'msg' => trim(strip_tags($this->upload->display_errors('', ' ')))));
        }
        $up = $this->upload->data();
        $file = $up['file_name'];
        $src = $this->Album_model->dir . $file;
        $dst = $this->Album_model->thumbs . $file;
        $is_gif = (strtolower($up['file_ext']) === '.gif');

        $this->load->library('image_lib');
        if (!$is_gif) {
            // tegakkan foto sesuai orientasi kamera (EXIF), mis. foto dari HP
            if (in_array(strtolower($up['file_ext']), array('.jpg', '.jpeg'))) {
                $this->_fix_orientation($src);
            }
            // perkecil foto besar (maks 1600 px) agar hemat ruang & cepat dibuka
            $size = @getimagesize($src);
            if ($size && ($size[0] > 1600 || $size[1] > 1600)) {
                $this->image_lib->clear();
                $this->image_lib->initialize(array(
                    'image_library' => 'gd2', 'source_image' => $src, 'maintain_ratio' => TRUE,
                    'width' => 1600, 'height' => 1600, 'quality' => '82%',
                ));
                $this->image_lib->resize();
            }
        }
        // thumbnail 600 px; bila gagal, salin berkas asli agar tampilan tetap berjalan
        $this->image_lib->clear();
        $this->image_lib->initialize(array(
            'image_library' => 'gd2', 'source_image' => $src, 'new_image' => $dst,
            'maintain_ratio' => TRUE, 'width' => 600, 'height' => 600, 'quality' => '80%',
        ));
        if (!$this->image_lib->resize() || !is_file($dst)) {
            @copy($src, $dst);
        }

        $row = $this->db->select_max('urutan', 'mx')->where('album_id', $album->id)->get('album_foto')->row();
        $this->db->insert('album_foto', array(
            'album_id'   => $album->id,
            'file'       => $file,
            'keterangan' => '',
            'urutan'     => ((int) $row->mx) + 1,
            'waktu'      => date('Y-m-d H:i:s'),
        ));
        $fid = $this->db->insert_id();
        $this->Album_model->refresh_cover($album->id);

        $this->_json(array(
            'ok' => TRUE, 'id' => $fid, 'file' => $file,
            'thumb' => base_url('uploads/album/thumbs/' . $file),
        ));
    }

    // Koreksi orientasi EXIF dengan GD (CodeIgniter 3.1 tidak punya fungsi ini).
    private function _fix_orientation($path)
    {
        if (!function_exists('exif_read_data') || !function_exists('imagecreatefromjpeg') || !function_exists('imagerotate')) {
            return;
        }
        $exif = @exif_read_data($path);
        $o = (is_array($exif) && isset($exif['Orientation'])) ? (int) $exif['Orientation'] : 1;
        if ($o < 2 || $o > 8) {
            return;
        }
        $im = @imagecreatefromjpeg($path);
        if (!$im) {
            return;
        }
        if (in_array($o, array(2, 5, 7))) { imageflip($im, IMG_FLIP_HORIZONTAL); }
        if ($o == 4) { imageflip($im, IMG_FLIP_VERTICAL); }
        $angle = array(3 => 180, 4 => 0, 5 => 90, 6 => -90, 7 => -90, 8 => 90);
        if (!empty($angle[$o])) {
            $rot = imagerotate($im, $angle[$o], 0);
            if ($rot) { imagedestroy($im); $im = $rot; }
        }
        imagejpeg($im, $path, 90);
        imagedestroy($im);
    }

    public function hapus_foto()
    {
        $p = $this->db->get_where('album_foto', array('id' => (int) $this->input->post('id')))->row();
        if (!$p) {
            return $this->_json(array('status' => 'error'));
        }
        $this->Album_model->delete_files($p->file);
        $this->db->where('id', $p->id)->delete('album_foto');
        $this->Album_model->refresh_cover($p->album_id);
        $this->_json(array('status' => 'success'));
    }

    public function jadikan_sampul()
    {
        $p = $this->db->get_where('album_foto', array('id' => (int) $this->input->post('id')))->row();
        if (!$p) {
            return $this->_json(array('status' => 'error'));
        }
        $this->db->where('id', $p->album_id)->update('album', array('cover' => $p->file));
        $this->_json(array('status' => 'success', 'file' => $p->file));
    }

    public function simpan_keterangan()
    {
        $id = (int) $this->input->post('id');
        $ket = mb_substr(trim((string) $this->input->post('keterangan')), 0, 150);
        if (!$this->db->get_where('album_foto', array('id' => $id))->row()) {
            return $this->_json(array('status' => 'error'));
        }
        $this->db->where('id', $id)->update('album_foto', array('keterangan' => $ket));
        $this->_json(array('status' => 'success'));
    }
}
