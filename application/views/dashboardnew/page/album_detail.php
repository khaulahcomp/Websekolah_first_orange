<?php $this->load->view('dashboardnew/partial/header'); ?>
<?php $this->load->view('dashboardnew/partial/menu'); ?>
<?php
  $general = $this->db->get('general')->row_array();
?>

<main class="main">

	<div class="site-breadcrumb" style="background-image: url(<?= base_url('assets/upload/images/breadcumb/').$general['breadcumb']?>)">
		<div class="container">
			<h2 class="breadcrumb-title notranslate" translate="no"><?= htmlspecialchars($album->judul, ENT_QUOTES, 'UTF-8') ?></h2>
			<ul class="breadcrumb-menu">
				<li><a href="<?= base_url('') ?>">Home</a></li>
				<li><a href="<?= base_url('dashboard/foto') ?>">Galeri Foto</a></li>
				<li class="active">Album</li>
			</ul>
		</div>
	</div>

	<div class="gallery-area py-120 album-detail">
		<div class="container">
			<div class="album-back">
				<a href="<?= base_url('dashboard/foto') ?>" class="theme-btn"><i class="fas fa-arrow-left-long"></i> Semua Album</a>
			</div>
			<div class="row">
				<div class="col-lg-8 mx-auto">
					<div class="site-heading text-center">
						<span class="site-title-tagline"><i class="far fa-images"></i> <?= count($photos) ?> Foto &middot; <?= date('d M Y', strtotime($album->waktu)) ?></span>
						<h2 class="site-title notranslate" translate="no"><?= htmlspecialchars($album->judul, ENT_QUOTES, 'UTF-8') ?></h2>
					</div>
				</div>
			</div>
			<?php if (!empty($album->deskripsi)) { ?>
				<p class="album-desc"><?= nl2br(htmlspecialchars($album->deskripsi, ENT_QUOTES, 'UTF-8')) ?></p>
			<?php } ?>

			<?php if (empty($photos)) { ?>
				<p class="text-center text-muted">Album ini belum berisi foto.</p>
			<?php } ?>

			<!-- Klik foto untuk membuka; gunakan panah / tombol keyboard untuk foto sebelumnya & berikutnya -->
			<div class="row popup-gallery">
				<?php foreach ($photos as $p) {
					$cap = htmlspecialchars((string) $p->keterangan, ENT_QUOTES, 'UTF-8');
					$thumb = base_url('uploads/album/thumbs/' . rawurlencode($p->file));
					$full  = base_url('uploads/album/' . rawurlencode($p->file));
				?>
					<div class="col-6 col-md-4 col-lg-3">
						<div class="gallery-item">
							<div class="gallery-img">
								<img src="<?= $thumb ?>" alt="<?= $cap ?>" loading="lazy">
							</div>
							<div class="gallery-content">
								<a class="popup-img gallery-link" href="<?= $full ?>" title="<?= $cap ?>"><i class="fal fa-plus"></i></a>
							</div>
						</div>
					</div>
				<?php } ?>
			</div>
		</div>
	</div>

</main>

<?php $this->load->view('dashboardnew/partial/footer'); ?>
