<?php $this->load->view('dashboardnew/partial/header'); ?>
<?php $this->load->view('dashboardnew/partial/menu'); ?>
<?php
  $general = $this->db->get('general')->row_array();
?>

<main class="main">

<div class="site-breadcrumb" style="background-image: url(<?= base_url('assets/upload/images/breadcumb/').$general['breadcumb']?>)">
		<div class="container">
			<h2 class="breadcrumb-title"><?= $subjudul ?></h2>
			<ul class="breadcrumb-menu">
				<li><a href="<?= base_url('') ?>">Home</a></li>
				<li class="active"><?= $subjudul ?></li>
			</ul>
		</div>
	</div>


	<div class="gallery-area py-120">
	<div class="container">
		<div class="row">
			<div class="col-lg-6 mx-auto">
				<div class="site-heading text-center">
					<span class="site-title-tagline"><i class="far fa-book-open-reader"></i> Galeri Foto</span>
					<h2 class="site-title">Album <span>Foto</span></h2>
					
				</div>
			</div>
		</div>
		<div class="row">
		<?php if (empty($album)) { ?>
			<p class="text-center text-muted">Belum ada album foto.</p>
		<?php } ?>
		<?php foreach ($album as $a) { ?>
			<div class="col-md-6 col-lg-4 wow fadeInUp" data-wow-delay=".25s">
				<a class="album-card" href="<?= base_url('album-detail/' . $a->slug) ?>">
					<div class="album-cover">
						<img src="<?= base_url('uploads/album/thumbs/' . rawurlencode($a->cover)) ?>" alt="<?= htmlspecialchars($a->judul, ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
						<span class="album-count"><i class="far fa-images"></i> <?= (int) $a->jumlah ?> Foto</span>
					</div>
					<div class="album-info">
						<h3 class="album-title"><?= htmlspecialchars($a->judul, ENT_QUOTES, 'UTF-8') ?></h3>
						<div class="album-meta"><?= date('d M Y', strtotime($a->waktu)) ?></div>
					</div>
				</a>
			</div>
		<?php } ?>
		</div>
	</div>
	</div>

</main>

<?php $this->load->view('dashboardnew/partial/footer'); ?>
