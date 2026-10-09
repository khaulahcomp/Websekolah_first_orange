<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Dashboard extends CI_Controller {

	public function __construct(){
        parent::__construct();
        $this->load->helper('cookie');
        $this->load->library('form_validation');
        $this->load->helper('date');
        $this->load->model('Visitor_model');
    }
	public function index()
	{
		 // Dapatkan alamat IP pengunjung
         $ip_address = $this->input->ip_address();

         // Periksa apakah pengunjung dengan IP yang sama telah mengunjungi pada hari yang sama
         if (!$this->Visitor_model->check_ip_visit_today($ip_address)) {
             // Jika belum, tambahkan pengunjung ke database
             $this->Visitor_model->add_visitor($ip_address);
         }

        $data['slider']  = $this->db->query('SELECT * FROM slider ORDER BY id ASC')->result();
        $data['guru']  = $this->db->query('SELECT * FROM guru ORDER BY id DESC LIMIT 4')->result();
        $data['ekskul']  = $this->db->get('ekskul')->result();
        $this->load->model('Album_model');
        $data['album'] = $this->Album_model->all_with_count(TRUE, 6);
        $data['siswa']  = $this->db->query('SELECT * FROM siswa ORDER BY id DESC ')->result();
        $data['jumlahsiswa']  = $this->db->query('SELECT SUM(jumlah) AS jumlahsiswa FROM siswa')->row();
        $data['sambutankepsek']  = $this->db->query('SELECT * FROM sambutan WHERE id = 1  ')->row();
        $data['denah']  = $this->db->query('SELECT * FROM denah WHERE id = 1  ')->row();
        $data['berita'] = $this->M_dashboard->get_datadashboard();
        $data['denahbangunan']  = $this->db->query('SELECT * FROM denahbangunan WHERE id = 1  ')->row();
        $data['keutamaan']  = $this->db->get('keutamaan')->result();
        $data['jurusan']  = $this->db->get('jurusan')->result();
        $data['industri']  = $this->db->get('industri')->result();
        $data['testimoni']  = $this->db->get('testimoni')->result();
        $data['video'] = $this->db->order_by('id', 'desc')->limit(3)->get('video')->result();





        
		$this->load->view('dashboardnew/page/dashboard',$data);
		
	}

    public function kirimpesan()
    {
        $back = 'contacts';
        if (strtoupper($this->input->method(FALSE)) !== 'POST') { redirect($back); }
        $this->load->library('throttle');
        $ip   = $this->input->ip_address();
        $fail = function ($msg) use ($back) { $this->session->set_flashdata('error_msg', $msg); redirect($back); };

        // Jebakan bot: kolom tersembunyi terisi / formulir dikirim terlalu cepat -> pura-pura berhasil
        $tok = $this->session->userdata('ct_tok');
        $age = time() - (int) $this->session->userdata('ct_ts');
        $trap = (trim((string) $this->input->post('website')) !== '') || ($age < (int) $this->config->item('kontak_min_seconds'));
        if (!is_string($tok) || $tok === '' || !hash_equals($tok, (string) $this->input->post('ct'))) {
            $fail('Sesi formulir kedaluwarsa. Muat ulang halaman lalu kirim kembali.');
        }
        if ($trap) {
            $this->session->set_flashdata('success_msg', 'Pesan Anda telah berhasil dikirim!');
            redirect($back);
        }

        // Pembatasan laju per IP
        $key = 'kontak:ip:' . $ip;
        if ($this->throttle->count($key, 600) >= (int) $this->config->item('kontak_max_10min') || $this->throttle->count($key, 86400) >= (int) $this->config->item('kontak_max_day')) {
            $fail('Terlalu banyak pesan dari alamat Anda. Silakan coba lagi nanti.');
        }

        // Validasi & pembersihan (teks polos, tanpa HTML)
        $nama   = plain_text($this->input->post('name'), 100);
        $email  = trim((string) $this->input->post('email'));
        $subjek = plain_text($this->input->post('subject'), 100);
        $pesan  = plain_text($this->input->post('message'), 2000);
        if (mb_strlen($nama, 'UTF-8') < 2)   { $fail('Nama minimal 2 karakter.'); }
        if (strlen($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)) { $fail('Alamat email tidak valid.'); }
        if (mb_strlen($subjek, 'UTF-8') < 3) { $fail('Subjek minimal 3 karakter.'); }
        if (mb_strlen($pesan, 'UTF-8') < 10) { $fail('Pesan minimal 10 karakter.'); }
        if (preg_match_all('#https?://#i', $pesan . ' ' . $subjek) > 2) { $fail('Pesan tidak dapat dikirim (terlalu banyak tautan).'); }

        $simpan = $this->db->insert('kontak', array(
            'nama' => $nama, 'email' => $email, 'subjek' => $subjek, 'pesan' => $pesan, 'waktu' => date('Y-m-d H:i:s'),
        ));
        $this->throttle->hit($key);
        $this->session->unset_userdata(array('ct_tok', 'ct_ts'));
        if ($simpan) {
            $this->session->set_flashdata('success_msg', 'Pesan Anda telah berhasil dikirim!');
        } else {
            $this->session->set_flashdata('error_msg', 'Maaf, terjadi kesalahan saat mengirim pesan. Silakan coba lagi nanti.');
        }
        redirect($back);
    }

    public function profil_sekolah(){
       
         // Dapatkan alamat IP pengunjung
         $ip_address = $this->input->ip_address();

         // Periksa apakah pengunjung dengan IP yang sama telah mengunjungi pada hari yang sama
         if (!$this->Visitor_model->check_ip_visit_today($ip_address)) {
             // Jika belum, tambahkan pengunjung ke database
             $this->Visitor_model->add_visitor($ip_address);
         }

        $data = [
			'judul'		=> 'Profil',
			'subjudul'	=> 'Profil Sekolah ',
            'profil'     => $this->db->query('SELECT * FROM profil WHERE id = 1  ')->row(),
		];
        
		$this->load->view('dashboardnew/page/profil',$data);

    }

    public function kontak(){
       
        // Dapatkan alamat IP pengunjung
        $ip_address = $this->input->ip_address();

        // Periksa apakah pengunjung dengan IP yang sama telah mengunjungi pada hari yang sama
        if (!$this->Visitor_model->check_ip_visit_today($ip_address)) {
            // Jika belum, tambahkan pengunjung ke database
            $this->Visitor_model->add_visitor($ip_address);
        }

       $data = [
           'judul'		=> 'Kontak',
           'subjudul'	=> 'Kontak Sekolah ',
           'profil'     => $this->db->query('SELECT * FROM profil WHERE id = 1  ')->row(),
       ];
       
       $this->load->view('dashboardnew/page/kontak',$data);

   }

    public function visi_misi(){

         // Dapatkan alamat IP pengunjung
         $ip_address = $this->input->ip_address();

         // Periksa apakah pengunjung dengan IP yang sama telah mengunjungi pada hari yang sama
         if (!$this->Visitor_model->check_ip_visit_today($ip_address)) {
             // Jika belum, tambahkan pengunjung ke database
             $this->Visitor_model->add_visitor($ip_address);
         }
       
        $data = [
			'judul'		=> 'Profil',
			'subjudul'	=> 'Visi Misi Sekolah ',
            'visi'     => $this->db->query('SELECT * FROM visi WHERE id = 1  ')->row(),
		];
        
		$this->load->view('dashboardnew/page/visimisi',$data);

    }

    public function sambutandetail()
    {
        $data = [
			'judul'		=> 'Profil',
			'subjudul'	=> 'Sambutan Kepala Sekolah',
            'sambutankepsek'     =>  $this->db->get_where('sambutan', ['id' => '1'])->row()
		];
       
		$this->load->view('dashboardnew/page/detailsambutan',$data);

        
    }

    public function guru(){

         // Dapatkan alamat IP pengunjung
         $ip_address = $this->input->ip_address();

         // Periksa apakah pengunjung dengan IP yang sama telah mengunjungi pada hari yang sama
         if (!$this->Visitor_model->check_ip_visit_today($ip_address)) {
             // Jika belum, tambahkan pengunjung ke database
             $this->Visitor_model->add_visitor($ip_address);
         }
       
        $data = [
			'judul'		=> 'Informasi',
			'subjudul'	=> 'Guru',
            'guru'     => $this->db->query('SELECT * FROM guru ORDER BY id ASC ')->result(),
		];
        
		$this->load->view('dashboardnew/page/guru',$data);

    }

    public function file(){

         // Dapatkan alamat IP pengunjung
         $ip_address = $this->input->ip_address();

         // Periksa apakah pengunjung dengan IP yang sama telah mengunjungi pada hari yang sama
         if (!$this->Visitor_model->check_ip_visit_today($ip_address)) {
             // Jika belum, tambahkan pengunjung ke database
             $this->Visitor_model->add_visitor($ip_address);
         }
       
        $data = [
			'judul'		=> 'Informasi',
			'subjudul'	=> 'File Download',
            'file'     => $this->db->query('SELECT * FROM file ORDER BY id ASC ')->result(),
		];
        
		$this->load->view('dashboardnew/page/file',$data);

    }


    public function kegiatan(){

         // Dapatkan alamat IP pengunjung
         $ip_address = $this->input->ip_address();

         // Periksa apakah pengunjung dengan IP yang sama telah mengunjungi pada hari yang sama
         if (!$this->Visitor_model->check_ip_visit_today($ip_address)) {
             // Jika belum, tambahkan pengunjung ke database
             $this->Visitor_model->add_visitor($ip_address);
         }
       
        $data = [
			'judul'		=> 'Kegiatan',
			'subjudul'	=> 'Kalender Sekolah',
            
		];
        
		$this->load->view('dashboardnew/page/kegiatan',$data);

    }

    public function denahbangunan(){

         // Dapatkan alamat IP pengunjung
         $ip_address = $this->input->ip_address();

         // Periksa apakah pengunjung dengan IP yang sama telah mengunjungi pada hari yang sama
         if (!$this->Visitor_model->check_ip_visit_today($ip_address)) {
             // Jika belum, tambahkan pengunjung ke database
             $this->Visitor_model->add_visitor($ip_address);
         }
       
        $data = [
			'judul'		=> 'Denah Bangunan',
			'subjudul'	=> 'Peta Denah Bangunan Sekolah',
            'denahbangunan' => $this->db->query('SELECT * FROM denahbangunan WHERE id = 1  ')->row()
            
		];
        
		$this->load->view('dashboardnew/page/denahbangunan',$data);

    }

    public function load()
	{
		$dataa = $this->db->get("tbl_rencanakegiatan")->result_array();
   
        foreach ($dataa as $row ) 
		{
			$data[] = array(
				// 'id' => $row['id'],
				'title' => $row['nama_kegiatan'],
				'start' => $row['tanggal'],
				'end' => date('Y-m-d', strtotime($row['tanggal_selesai'].'+ 1 days ')),
				'description' => $row['keterangan'],
				'tanggal' => date('d M Y', strtotime($row['tanggal'])),
				'selesai' => date('d M Y', strtotime($row['tanggal_selesai']))
			   );
			
            // $data['data'][$key]['title'] = $value->nama_kegiatan;
            // $data['data'][$key]['start'] = $value->tanggal;
            // $data['data'][$key]['end'] =  date('Y-m-d', strtotime($value->tanggal_selesai.'+ 1 days '));;
            // $data['data'][$key]['description'] = $value->keterangan;
            // $data['data'][$key]['tanggal'] = date('d M Y', strtotime($value->tanggal));
            // $data['data'][$key]['selesai'] = date('d M Y', strtotime($value->tanggal_selesai));
            // // $data['data'][$key]['backgroundColor'] = "#00a65a";
        }

		echo json_encode($data);
	}

    public function video(){

         // Dapatkan alamat IP pengunjung
         $ip_address = $this->input->ip_address();

         // Periksa apakah pengunjung dengan IP yang sama telah mengunjungi pada hari yang sama
         if (!$this->Visitor_model->check_ip_visit_today($ip_address)) {
             // Jika belum, tambahkan pengunjung ke database
             $this->Visitor_model->add_visitor($ip_address);
         }
       
        $data = [
			'judul'		=> 'Galeri',
			'subjudul'	=> 'Video',
            'video'     => $this->db->query('SELECT * FROM video ORDER BY id DESC ')->result(),
		];
        
		$this->load->view('dashboardnew/page/video',$data);

    }

    public function foto(){
        $this->load->model('Album_model');

         // Dapatkan alamat IP pengunjung
         $ip_address = $this->input->ip_address();

         // Periksa apakah pengunjung dengan IP yang sama telah mengunjungi pada hari yang sama
         if (!$this->Visitor_model->check_ip_visit_today($ip_address)) {
             // Jika belum, tambahkan pengunjung ke database
             $this->Visitor_model->add_visitor($ip_address);
         }
       
        $data = [
			'judul'		=> 'Galeri',
			'subjudul'	=> 'Foto',
            'album'    => $this->Album_model->all_with_count(TRUE),
		];
        
		$this->load->view('dashboardnew/page/foto',$data);

    }

    public function struktur_organisasi(){

         // Dapatkan alamat IP pengunjung
         $ip_address = $this->input->ip_address();

         // Periksa apakah pengunjung dengan IP yang sama telah mengunjungi pada hari yang sama
         if (!$this->Visitor_model->check_ip_visit_today($ip_address)) {
             // Jika belum, tambahkan pengunjung ke database
             $this->Visitor_model->add_visitor($ip_address);
         }
       
        $data = [
			'judul'		=> 'Profil',
			'subjudul'	=> 'Struktur Organisasi Sekolah ',
            'struktur'     => $this->db->query('SELECT * FROM struktur WHERE id = 1  ')->row(),
		];
        
		$this->load->view('dashboardnew/page/struktur',$data);

    }

    public function ekskuldetail($slug = "")
    {

        
        if (!$slug) {
            redirect('dashboard');
        }
		
        $cekdulu = $this->M_dashboard->get_ekskul_by_slug($slug);
       

        if ($cekdulu->num_rows() < 1) {
            redirect('my404');
        } else {
            $data = [
                'judul'		=> 'Ekstrakulikuler',
                'ekskul'   => $this->db->get('ekskul')->result()
            ];
            $data['detail']   = $this->M_dashboard->get_ekskul_by_slug($slug)->row();
            $data['berita'] = $this->M_dashboard->get_datadashboard();
            $data['subjudul'] = $data['detail']->judul;

            $this->load->view('dashboardnew/page/detailekskul',$data);
        }

    }

    public function jurusandetail($slug = "")
    {
        if (!$slug) {
            redirect('dashboard');
        }
		
        $cekdulu = $this->M_dashboard->get_jurusan_by_slug($slug);
       

        if ($cekdulu->num_rows() < 1) {
            redirect('my404');
        } else {
            $data = [
                'judul'		=> 'Jurusan',
                'jurusan'   => $this->db->get('jurusan')->result(),
                'ekskul'   => $this->db->get('ekskul')->result()

            ];
            $data['detail']   = $this->M_dashboard->get_jurusan_by_slug($slug)->row();
            $data['berita'] = $this->M_dashboard->get_datadashboard();
            $data['subjudul'] = $data['detail']->judul;

            $this->load->view('dashboardnew/page/detailjurusan',$data);
        }

    }

    public function berita_list()
    {
         // Dapatkan alamat IP pengunjung
         $ip_address = $this->input->ip_address();

         // Periksa apakah pengunjung dengan IP yang sama telah mengunjungi pada hari yang sama
         if (!$this->Visitor_model->check_ip_visit_today($ip_address)) {
             // Jika belum, tambahkan pengunjung ke database
             $this->Visitor_model->add_visitor($ip_address);
         }

        $data = [
			'judul'		=> 'Informasi',
			'subjudul'	=> 'Daftar Berita',
           
		];
        $config['base_url'] = site_url('berita-sekolah-list'); //site url
        $config['total_rows'] = $this->db->get('berita')->num_rows(); //total row
        $config['per_page'] = 6;  //show record per halaman
        $config["uri_segment"] = 2;  // uri parameter
        $choice = $config["total_rows"] / $config["per_page"];
        $config["num_links"] = floor($choice);
 
    
        $config['first_link']       = 'First';
        $config['last_link']        = 'Last';
        $config['next_link']        = '<span aria-hidden="true"><i class="far fa-arrow-right"></i></span>';
        $config['prev_link']        = '<span aria-hidden="true"><i class="far fa-arrow-left"></i></span>';
        $config['full_tag_open']    = '<ul class="pagination">';
        $config['full_tag_close']   = '</ul>';
        $config['num_tag_open']     = '<li class="page-item">';
        $config['num_tag_close']    = '</span></li>';
        $config['cur_tag_open']     = '<li class="page-item active"><a class="page-link" href="#">';
        $config['cur_tag_close']    = '</a></li>';
        $config['next_tag_open']    = '<li class="page-item">';
        $config['next_tagl_close']  = '</li>';
        $config['prev_tag_open']    = '<li class="page-item">';
        $config['prev_tagl_close']  = '</li>';
        $config['first_tag_open']   = '<li class="page-item">';
        $config['first_tagl_close'] = '</li>';
        $config['last_tag_open']    = '<li class="page-item">';
        $config['last_tagl_close']  = '</li>';
        $config['attributes'] = array('class' => 'page-link'); // Menambahkan kelas 'page-link' ke tag anchor

 
        $this->pagination->initialize($config);
        $data['page'] = ($this->uri->segment(1)) ? $this->uri->segment(2) : 0;

       
        $start = $this->uri->segment(2);
        $data['beritalists'] = $this->M_dashboard->get_all_post($config['per_page'], $start);

        
        $data['pagination'] = $this->pagination->create_links();
 
        $data['berita'] = $this->M_dashboard->get_datadashboard();
        $data['ekskul']  = $this->db->get("ekskul")->result();


        $this->load->view('dashboardnew/page/list_berita',$data);

        
    }

    public function cariberita()
    {

        if ($this->input->post('cari')!=''){
        
           
        
         // Dapatkan alamat IP pengunjung
         $ip_address = $this->input->ip_address();

         // Periksa apakah pengunjung dengan IP yang sama telah mengunjungi pada hari yang sama
         if (!$this->Visitor_model->check_ip_visit_today($ip_address)) {
             // Jika belum, tambahkan pengunjung ke database
             $this->Visitor_model->add_visitor($ip_address);
         }

        $data = [
			'judul'		=> 'Informasi',
			'subjudul'	=> 'Daftar Berita',
			'keyword'	=> plain_text($this->input->post('cari'), 100),
           
		];
        
        $data['beritalists'] = $this->M_dashboard->get_all_post_cari(plain_text($this->input->post('cari'), 100));

        
       
 
        $data['berita'] = $this->M_dashboard->get_datadashboard();
        $data['ekskul']  = $this->db->get("ekskul")->result();


        $this->load->view('dashboardnew/page/cariberita',$data);
    }else{
        redirect("berita-sekolah-list");
    }

        
    }


   


    public function beritadetail($slug = "")
    {
         // Dapatkan alamat IP pengunjung
         $ip_address = $this->input->ip_address();
         $this->load->library('disqus');

         // Periksa apakah pengunjung dengan IP yang sama telah mengunjungi pada hari yang sama
         if (!$this->Visitor_model->check_ip_visit_today($ip_address)) {
             // Jika belum, tambahkan pengunjung ke database
             $this->Visitor_model->add_visitor($ip_address);
         }

        if (!$slug) {
            redirect('dashboard');
        }
		
        $cekdulu = $this->M_dashboard->get_post_by_slug($slug);
        $data['berita'] = $this->M_dashboard->get_datadashboard();
        if ($cekdulu->num_rows() < 1) {
            redirect('my404');
        } else {
            
            $berita =$this->M_dashboard->get_post_by_slug($slug)->row();
            $new_views_count = $berita->view_count + 1;
        
            // Update nilai views_count di database
            $this->db->where('slug', $slug);
            $this->db->update('berita', array('view_count' => $new_views_count));
    
            $data = [
                'judul'		=> 'Berita Detail',
                'ekskul'   => $this->db->get('ekskul')->result()
            ];
           

            
            $data['detail']   = $this->M_dashboard->get_post_by_slug($slug)->row();
            $data['berita'] = $this->M_dashboard->get_datadashboard();
            $data['subjudul'] = 'Berita Detail';
            $disqus = $this->disqus->get_html();
            $data['disqus']  = $disqus;
            $this->load->view('dashboardnew/page/detailberita',$data);
        }

    }
    

    // Galeri Foto: isi satu album (foto dapat dibuka satu per satu dengan tombol sebelumnya/berikutnya)
    public function albumdetail($slug = '')
    {
        $this->load->model('Album_model');
        $album = $this->Album_model->by_slug($slug);
        if (!$album) {
            show_404();
        }

        $ip_address = $this->input->ip_address();
        if (!$this->Visitor_model->check_ip_visit_today($ip_address)) {
            $this->Visitor_model->add_visitor($ip_address);
        }

        $this->load->view('dashboardnew/page/album_detail', array(
            'judul'    => 'Galeri',
            'subjudul' => $album->judul,
            'album'    => $album,
            'photos'   => $this->Album_model->photos($album->id),
        ));
    }


    // Kebijakan privasi
    public function privasi()
    {
        $ip_address = $this->input->ip_address();
        if (!$this->Visitor_model->check_ip_visit_today($ip_address)) {
            $this->Visitor_model->add_visitor($ip_address);
        }
        $this->load->view('dashboardnew/page/privasi', array('judul' => 'Privasi', 'subjudul' => 'Kebijakan Privasi'));
    }

}
