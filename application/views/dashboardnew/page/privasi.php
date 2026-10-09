<?php $this->load->view('dashboardnew/partial/header'); ?>
<?php $this->load->view('dashboardnew/partial/menu'); ?>
<?php $general = $this->db->get('general')->row_array(); ?>

<main class="main">
	<div class="site-breadcrumb" style="background-image: url(<?= base_url('assets/upload/images/breadcumb/').html_escape($general['breadcumb']) ?>)">
		<div class="container">
			<h2 class="breadcrumb-title">Kebijakan Privasi</h2>
			<ul class="breadcrumb-menu">
				<li><a href="<?= base_url('') ?>">Home</a></li>
				<li class="active">Kebijakan Privasi</li>
			</ul>
		</div>
	</div>

	<div class="py-120">
		<div class="container" style="max-width:860px">
			<h3>Kebijakan Privasi <?= html_escape($general['nama_sekolah']) ?></h3>
			<p>Halaman ini menjelaskan data apa yang dikumpulkan website ini dan bagaimana data tersebut digunakan.</p>

			<h5 class="mt-4">1. Data yang dikumpulkan</h5>
			<p><strong>Formulir kontak:</strong> nama, alamat email, subjek, dan isi pesan yang Anda tulis sendiri.
				Data ini hanya dapat dilihat oleh pengelola website dan digunakan untuk menanggapi pesan Anda.</p>
			<p><strong>Statistik kunjungan:</strong> alamat IP dan waktu kunjungan dicatat untuk menghitung jumlah pengunjung dan mencegah penyalahgunaan (spam).
				Alamat IP yang sudah lama dikaburkan secara otomatis; jumlah kunjungan tetap tersimpan.</p>
			<p><strong>Cookie:</strong> website memakai cookie sesi yang diperlukan agar halaman dan formulir berfungsi.</p>

			<h5 class="mt-4">2. Layanan pihak ketiga</h5>
			<p>Beberapa konten disajikan oleh layanan lain, misalnya video YouTube, peta Google, huruf Google Fonts, dan penghitung pengunjung.
				Saat konten tersebut dimuat, penyedia layanan dapat menerima data teknis (misalnya alamat IP) sesuai kebijakan mereka masing-masing.</p>

			<h5 class="mt-4">3. Foto dan data peserta didik</h5>
			<p>Foto kegiatan yang memuat peserta didik hanya dipublikasikan dengan sepengetahuan dan persetujuan orang tua/wali.
				Jika Anda ingin sebuah foto dihapus, hubungi kami.</p>

			<h5 class="mt-4">4. Keamanan dan penyimpanan</h5>
			<p>Data disimpan di server website dan hanya diakses oleh pengelola yang berwenang. Pesan kontak disimpan sampai tidak diperlukan lagi.</p>

			<h5 class="mt-4">5. Hak Anda dan kontak</h5>
			<p>Anda dapat meminta akses, perbaikan, atau penghapusan data yang Anda kirimkan melalui halaman
				<a href="<?= base_url('contacts') ?>">Kontak</a><?php if (!empty($general['account_gmail'])) { ?> atau email <?= html_escape($general['account_gmail']) ?><?php } ?>.</p>
		</div>
	</div>
</main>

<?php $this->load->view('dashboardnew/partial/footer'); ?>
