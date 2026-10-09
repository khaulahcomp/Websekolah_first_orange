<?php $this->load->view('admin/partial/header'); ?>
<div class="container-xxl flex-grow-1 container-p-y">
	<h4 class="fw-semibold py-3 mb-4"><span class="text-muted fw-light"><?= $judul ?> /</span> <?= $subjudul ?></h4>
	<div class="alert alert-info">
		Foto dikelompokkan dalam <strong>album</strong>. Buat album, lalu unggah beberapa foto ke dalamnya.
		Di website, pengunjung membuka album dan dapat melihat foto satu per satu dengan tombol sebelumnya/berikutnya.
	</div>
	<div class="card mb-4">
		<h5 class="card-header">Daftar Album</h5>
		<div class="card-body">
			<button type="button" class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#modal-add">
				<i class="fas fa-plus-circle"></i> Buat Album Baru
			</button>
			<div class="table-responsive text-nowrap">
				<table id="album" class="table">
					<thead>
						<tr>
							<th>No</th>
							<th>Sampul</th>
							<th>Judul Album</th>
							<th>Jumlah</th>
							<th>Tanggal</th>
							<th>Aksi</th>
						</tr>
					</thead>
					<tbody class="table-border-bottom-0"></tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<?php $this->load->view('admin/partial/footer'); ?>

<div class="modal fade" id="modal-add" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Buat Album Baru</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="mb-3">
					<label class="form-label" for="a-judul">Judul Album</label>
					<input type="text" class="form-control" id="a-judul" maxlength="100" placeholder="Contoh: Wisuda Kelas 6 Tahun 2026">
				</div>
				<div class="mb-3">
					<label class="form-label" for="a-desk">Deskripsi (opsional)</label>
					<textarea class="form-control" id="a-desk" rows="3" placeholder="Keterangan singkat tentang album ini"></textarea>
				</div>
				<div class="form-text">Setelah disimpan, Anda langsung diarahkan untuk mengunggah foto ke album ini.</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
				<button type="button" class="btn btn-primary" id="btn-add"><i class="fa fa-save"></i> Simpan &amp; Unggah Foto</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal-edit" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Ubah Album</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<input type="hidden" id="e-id">
				<div class="mb-3">
					<label class="form-label" for="e-judul">Judul Album</label>
					<input type="text" class="form-control" id="e-judul" maxlength="100">
				</div>
				<div class="mb-3">
					<label class="form-label" for="e-desk">Deskripsi (opsional)</label>
					<textarea class="form-control" id="e-desk" rows="3"></textarea>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
				<button type="button" class="btn btn-primary" id="btn-edit"><i class="fa fa-save"></i> Simpan Perubahan</button>
			</div>
		</div>
	</div>
</div>

<script>
	$(document).ready(function () {
		var base = "<?= base_url() ?>";
		var table = $('#album').DataTable({
			"responsive": true, "lengthChange": true, "autoWidth": false, "paging": true,
			"ajax": { url: base + "albumfoto/getdata", type: 'GET' },
			"language": { "emptyTable": "Belum ada album. Klik \"Buat Album Baru\"." }
		});

		$('#btn-add').on('click', function () {
			var btn = $(this).prop('disabled', true);
			$.post(base + 'albumfoto/simpan', { judul: $('#a-judul').val(), deskripsi: $('#a-desk').val() }, null, 'json')
				.done(function (r) {
					if (r.status === 'success') {
						toastr.success('Album dibuat. Silakan unggah foto.');
						window.location = base + 'albumfoto/kelola/' + r.id;
					} else {
						toastr.warning(r.msg || 'Album gagal disimpan');
						btn.prop('disabled', false);
					}
				})
				.fail(function () { toastr.error('Terjadi kesalahan pada server'); btn.prop('disabled', false); });
		});

		$('#album').on('click', '.bedit', function () {
			var id = $(this).attr('data');
			$.getJSON(base + 'albumfoto/showedit', { id: id }, function (r) {
				if (r.status !== 'success') { toastr.error('Data album tidak ditemukan'); return; }
				$('#e-id').val(r.id); $('#e-judul').val(r.judul); $('#e-desk').val(r.deskripsi || '');
				$('#modal-edit').modal('show');
			});
		});

		$('#btn-edit').on('click', function () {
			$.post(base + 'albumfoto/simpanedit', { id: $('#e-id').val(), judul: $('#e-judul').val(), deskripsi: $('#e-desk').val() }, null, 'json')
				.done(function (r) {
					if (r.status === 'success') {
						$('#modal-edit').modal('hide');
						toastr.success('Album diperbarui');
						table.ajax.reload();
					} else { toastr.warning(r.msg || 'Album gagal diperbarui'); }
				})
				.fail(function () { toastr.error('Terjadi kesalahan pada server'); });
		});

		$('#album').on('click', '.bhapus', function () {
			var id = $(this).attr('data');
			Swal.fire({
				title: 'Hapus album ini?',
				text: 'Semua foto di dalam album juga akan dihapus permanen.',
				icon: 'warning', type: 'warning',
				showCancelButton: true, confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal'
			}).then(function (result) {
				if (result.value || result.isConfirmed) {
					$.post(base + 'albumfoto/hapus', { id: id }, null, 'json').done(function (r) {
						if (r.status === 'success') { toastr.success('Album dihapus'); table.ajax.reload(); }
						else { toastr.error('Album gagal dihapus'); }
					}).fail(function () { toastr.error('Terjadi kesalahan pada server'); });
				}
			});
		});
	});
</script>
