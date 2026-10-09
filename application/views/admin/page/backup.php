<?php $this->load->view('admin/partial/header'); ?>
<div class="container-xxl flex-grow-1 container-p-y">
	<h4 class="fw-semibold py-3 mb-4"><span class="text-muted fw-light"><?= $judul ?> /</span> <?= $subjudul ?></h4>
	<div class="card mb-4">
		<h5 class="card-header">Cadangan Database</h5>
		<div class="card-body">
			<p>Unduh salinan seluruh data website (berita, guru, album, pengguna, dll.) sebagai satu berkas <code>.sqlite</code>.
				Ukuran database saat ini: <strong><?= (int) $ukuran ?> KB</strong>.</p>
			<form method="post" action="<?= base_url('backup/unduh') ?>">
				<input type="hidden" name="csrf_token" value="<?= html_escape(admin_csrf_token()) ?>">
				<button type="submit" class="btn btn-primary"><i class="fas fa-download"></i> Unduh Cadangan Database</button>
			</form>
			<hr class="my-4">
			<p class="mb-1"><strong>Penting:</strong></p>
			<ul>
				<li>Simpan berkas di tempat aman (bukan di folder website). Berkas ini berisi data dan hash password admin.</li>
				<li>Foto dan berkas unggahan ada di folder <code>uploads/</code> dan <code>assets/upload/</code>: cadangkan juga lewat cPanel (Backup) atau File Manager.</li>
				<li>Untuk memulihkan: ganti berkas <code>sekolah.sqlite</code> di server dengan berkas cadangan.</li>
			</ul>
			<?php if (!$luar) { ?>
			<div class="alert alert-warning mb-0">File database Anda masih berada <strong>di dalam folder website</strong>. Untuk keamanan maksimal,
				pindahkan ke folder <code>sekolah_data</code> di luar <code>public_html</code> (lihat Panduan Update Keamanan, Langkah 6); aplikasi akan menemukannya otomatis.</div>
			<?php } else { ?>
			<div class="alert alert-success mb-0">File database berada di luar folder website. Konfigurasi sudah ideal.</div>
			<?php } ?>
		</div>
	</div>
</div>
<?php $this->load->view('admin/partial/footer'); ?>
