<?php $this->load->view('admin/partial/header'); ?>
<div class="container-xxl flex-grow-1 container-p-y">
	<h4 class="fw-semibold py-3 mb-4"><span class="text-muted fw-light"><?= $judul ?> /</span> <?= $subjudul ?></h4>
	<div class="alert alert-info">Catatan login dan semua aksi yang mengubah data (1.000 kejadian terakhir; disimpan 1 tahun).
		Perhatikan baris <strong>login_gagal</strong>, <strong>login_dikunci</strong>, dan <strong>csrf_ditolak</strong> yang tidak Anda kenali.</div>
	<div class="card mb-4">
		<h5 class="card-header">Log Aktivitas</h5>
		<div class="card-body">
			<div class="table-responsive">
				<table id="logtbl" class="table">
					<thead><tr><th>Waktu</th><th>Pengguna</th><th>Kejadian</th><th>Detail</th><th>IP</th></tr></thead>
					<tbody></tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<?php $this->load->view('admin/partial/footer'); ?>
<script>
	$(document).ready(function () {
		$('#logtbl').DataTable({
			"responsive": true, "autoWidth": false, "order": [[0, 'desc']], "pageLength": 25,
			"ajax": { url: "<?= base_url('logaktivitas/getdata') ?>", type: 'GET' }
		});
	});
</script>
