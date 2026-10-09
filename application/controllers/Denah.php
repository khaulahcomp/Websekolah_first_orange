<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Denah extends CI_Controller {
	public function __construct(){
        parent::__construct();
        $this->load->helper('cookie');
        $this->load->library('form_validation');
        $this->load->helper('date');
        $this->load->library('admin_guard');
		$this->admin_guard->require_admin();
    }
	public function index()
	{
        $data = [
			'judul'		=> 'Profil Sekolah',
			'subjudul'	=> 'Denah Lokasi ',
		];
		$this->load->view('admin/page/denah',$data);
	}

    public function showdata()
    {
        $denah = $this->db->get_where('denah', ['id' => '1']);
        if ($denah->num_rows() > 0) {
            foreach ($denah->result() as $data) {
                $hasil = array(
                    'denah' => $data->denah,
                );
            }
        }
        echo json_encode($hasil);
    }

        function simpan()
		{
			$denah = $this->input->post('denah');
			$datasimpan = array(
				'denah' => $denah,
			);
			$cek = $this->db->get_where('denah', array('id' => '1'));
			if ($cek->num_rows() > 0) {
				$simpan = $this->db->update('denah', $datasimpan);
				if ($simpan) {
					echo 'success';
				} else {
					echo 'error';
				}
			} else {
				$data = $this->db->insert('denah', $datasimpan);
				if ($data) {
					echo 'success';
				} else {
					echo 'error';
				}
			}
		}




}
