<?php $this->load->view('admin/partial/header'); ?>
<style>
	#dropzone { border: 2px dashed #b8bdd6; border-radius: 12px; padding: 28px 16px; text-align: center; background: #fafbff; transition: .15s; }
	#dropzone.over { border-color: #696cff; background: #eef; }
	.ph-card .ph-img { width: 100%; aspect-ratio: 4/3; object-fit: cover; border-radius: 8px 8px 0 0; background: #eee; }
	.ph-card .badge-cover { position: absolute; top: 8px; left: 8px; }
	#queue .q-item { font-size: 13px; padding: 6px 0; border-bottom: 1px solid #eee; display: flex; gap: 8px; align-items: center; }
	#queue .q-ok { color: #28a745; } #queue .q-err { color: #dc3545; }
</style>
<div class="container-xxl flex-grow-1 container-p-y">
	<h4 class="fw-semibold py-3 mb-2"><span class="text-muted fw-light">Album Foto /</span> <?= htmlspecialchars($album->judul, ENT_QUOTES, 'UTF-8') ?></h4>
	<div class="mb-4">
		<a href="<?= base_url('albumfoto') ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Kembali ke daftar album</a>
		<a href="<?= base_url('album-detail/' . $album->slug) ?>" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm"><i class="fas fa-external-link-alt"></i> Lihat di website</a>
	</div>

	<div class="card mb-4">
		<h5 class="card-header">Unggah Foto</h5>
		<div class="card-body">
			<div id="dropzone">
				<i class="fas fa-cloud-upload-alt fa-2x text-primary mb-2"></i>
				<p class="mb-2">Tarik dan lepas foto di sini, atau pilih dari perangkat.<br>
					<small class="text-muted">Bisa memilih banyak foto sekaligus. Format JPG, PNG, GIF. Maksimal 10 MB per foto.</small></p>
				<input type="file" id="files" accept="image/jpeg,image/png,image/gif" multiple class="d-none">
				<button type="button" class="btn btn-primary" id="pick"><i class="fas fa-images"></i> Pilih Foto</button>
			</div>
			<div id="queue" class="mt-3"></div>
		</div>
	</div>

	<div class="card mb-4">
		<h5 class="card-header d-flex justify-content-between align-items-center">
			<span>Foto dalam Album <span class="badge bg-label-primary ms-1" id="count"><?= count($photos) ?></span></span>
		</h5>
		<div class="card-body">
			<p id="empty" class="text-muted <?= $photos ? 'd-none' : '' ?>">Belum ada foto. Unggah foto pertama di atas.</p>
			<div class="row g-3" id="grid">
				<?php foreach ($photos as $p) { $is_cover = ($album->cover === $p->file); ?>
				<div class="col-6 col-md-4 col-lg-3 ph-col" data-id="<?= (int) $p->id ?>" data-file="<?= htmlspecialchars($p->file, ENT_QUOTES, 'UTF-8') ?>">
					<div class="card ph-card h-100 position-relative">
						<span class="badge bg-success badge-cover <?= $is_cover ? '' : 'd-none' ?>"><i class="fas fa-star"></i> Sampul</span>
						<img class="ph-img" src="<?= base_url('uploads/album/thumbs/' . rawurlencode($p->file)) ?>" alt="">
						<div class="p-2">
							<input type="text" class="form-control form-control-sm ph-cap" maxlength="150" placeholder="Keterangan foto (opsional)" value="<?= htmlspecialchars((string) $p->keterangan, ENT_QUOTES, 'UTF-8') ?>">
							<div class="d-flex gap-1 mt-2">
								<button type="button" class="btn btn-outline-success btn-sm flex-fill ph-cover" title="Jadikan sampul album"><i class="fas fa-star"></i> Sampul</button>
								<button type="button" class="btn btn-outline-danger btn-sm ph-del" title="Hapus foto"><i class="fa fa-trash"></i></button>
							</div>
						</div>
					</div>
				</div>
				<?php } ?>
			</div>
		</div>
	</div>
</div>
<?php $this->load->view('admin/partial/footer'); ?>

<script>
	$(document).ready(function () {
		var base = "<?= base_url() ?>", albumId = <?= (int) $album->id ?>, MAX = 10 * 1024 * 1024;
		var $grid = $('#grid');

		function esc(s) { return $('<div>').text(s == null ? '' : s).html(); }
		function refreshCount() {
			var n = $grid.children('.ph-col').length;
			$('#count').text(n); $('#empty').toggleClass('d-none', n > 0);
		}
		function cardHtml(f) {
			return '<div class="col-6 col-md-4 col-lg-3 ph-col" data-id="' + f.id + '" data-file="' + esc(f.file) + '">' +
				'<div class="card ph-card h-100 position-relative">' +
				'<span class="badge bg-success badge-cover d-none"><i class="fas fa-star"></i> Sampul</span>' +
				'<img class="ph-img" src="' + esc(f.thumb) + '" alt="">' +
				'<div class="p-2"><input type="text" class="form-control form-control-sm ph-cap" maxlength="150" placeholder="Keterangan foto (opsional)">' +
				'<div class="d-flex gap-1 mt-2">' +
				'<button type="button" class="btn btn-outline-success btn-sm flex-fill ph-cover" title="Jadikan sampul album"><i class="fas fa-star"></i> Sampul</button>' +
				'<button type="button" class="btn btn-outline-danger btn-sm ph-del" title="Hapus foto"><i class="fa fa-trash"></i></button>' +
				'</div></div></div></div>';
		}

		// ---- Unggah: satu berkas per permintaan, berurutan ----
		var busy = false, pending = [];
		function enqueue(list) {
			Array.prototype.forEach.call(list, function (file) {
				var $row = $('<div class="q-item"><span class="flex-grow-1 text-truncate">' + esc(file.name) + '</span><span class="q-state text-muted">menunggu...</span></div>').appendTo('#queue');
				if (!/\.(jpe?g|png|gif)$/i.test(file.name)) { $row.find('.q-state').addClass('q-err').text('format tidak didukung'); return; }
				if (file.size > MAX) { $row.find('.q-state').addClass('q-err').text('lebih dari 10 MB'); return; }
				pending.push({ file: file, row: $row });
			});
			run();
		}
		function run() {
			if (busy || !pending.length) return;
			busy = true;
			var job = pending.shift(), fd = new FormData();
			fd.append('foto', job.file);
			job.row.find('.q-state').text('mengunggah...');
			$.ajax({
				url: base + 'albumfoto/upload/' + albumId, type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json',
				xhr: function () {
					var x = $.ajaxSettings.xhr();
					if (x.upload) x.upload.addEventListener('progress', function (e) {
						if (e.lengthComputable) job.row.find('.q-state').text(Math.round(e.loaded / e.total * 100) + '%');
					});
					return x;
				}
			}).done(function (r) {
				if (r && r.ok) {
					job.row.find('.q-state').addClass('q-ok').text('selesai');
					$grid.append(cardHtml(r));
					if ($grid.children('.ph-col').length === 1) { $grid.find('.badge-cover').removeClass('d-none'); }
					refreshCount();
				} else {
					job.row.find('.q-state').addClass('q-err').text((r && r.msg) ? r.msg : 'gagal');
				}
			}).fail(function (xhr) {
				job.row.find('.q-state').addClass('q-err').text(xhr.status === 413 ? 'terlalu besar untuk server' : 'gagal (server)');
			}).always(function () { busy = false; run(); });
		}
		$('#pick').on('click', function () { $('#files').click(); });
		$('#files').on('change', function () { enqueue(this.files); this.value = ''; });
		$('#dropzone').on('dragover dragenter', function (e) { e.preventDefault(); $(this).addClass('over'); })
			.on('dragleave drop', function (e) { e.preventDefault(); $(this).removeClass('over'); })
			.on('drop', function (e) { enqueue(e.originalEvent.dataTransfer.files); });

		// ---- Aksi pada foto ----
		$grid.on('change', '.ph-cap', function () {
			var id = $(this).closest('.ph-col').data('id'), $i = $(this);
			$.post(base + 'albumfoto/simpan_keterangan', { id: id, keterangan: $i.val() }, null, 'json')
				.done(function (r) { if (r.status === 'success') toastr.success('Keterangan disimpan'); else toastr.error('Gagal menyimpan keterangan'); });
		});
		$grid.on('click', '.ph-cover', function () {
			var $c = $(this).closest('.ph-col');
			$.post(base + 'albumfoto/jadikan_sampul', { id: $c.data('id') }, null, 'json').done(function (r) {
				if (r.status === 'success') {
					$grid.find('.badge-cover').addClass('d-none'); $c.find('.badge-cover').removeClass('d-none');
					toastr.success('Sampul album diganti');
				} else { toastr.error('Gagal mengganti sampul'); }
			});
		});
		$grid.on('click', '.ph-del', function () {
			var $c = $(this).closest('.ph-col');
			Swal.fire({ title: 'Hapus foto ini?', text: 'Foto dihapus permanen dari album.', icon: 'warning', type: 'warning', showCancelButton: true, confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal' })
				.then(function (res) {
					if (!(res.value || res.isConfirmed)) return;
					var wasCover = !$c.find('.badge-cover').hasClass('d-none');
					$.post(base + 'albumfoto/hapus_foto', { id: $c.data('id') }, null, 'json').done(function (r) {
						if (r.status === 'success') {
							$c.remove(); refreshCount();
							if (wasCover) { $grid.children('.ph-col').first().find('.badge-cover').removeClass('d-none'); }
							toastr.success('Foto dihapus');
						} else { toastr.error('Gagal menghapus foto'); }
					});
				});
		});
	});
</script>
