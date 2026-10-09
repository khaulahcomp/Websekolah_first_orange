<?php $this->load->view('admin/partial/header'); ?>
<div class="container-xxl flex-grow-1 container-p-y">
	<h4 class="fw-semibold py-3 mb-4"><span class="text-muted fw-light"><?= $judul ?> /</span> <?= $subjudul ?></h4>
	<div class="card mb-4">
		<h5 class="card-header">Data </span> <?= $subjudul ?></h5>
		<div class="card-body">
			<button type="button" class="btn btn-primary mb-5" data-bs-toggle="modal" data-bs-target="#modal-add">
				<i class="fas fa-plus-circle"></i> Tambah Data
			</button>
			<div class="table-responsive text-nowrap">
				<table id="video" class="table">
					<thead>
						<tr>
							<th>No</th>
							<th>Video</th>
							<th>Tanggal Video</th>
							<th>Judul Video</th>
							<th>Author</th>
							<th>Action</th>
						</tr>
					</thead>
					<tbody class="table-border-bottom-0">

					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<?php $this->load->view('admin/partial/footer'); ?>

<div class="modal fade" id="modal-add" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<form enctype="multipart/form-data" id="inputform">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel4">Tambah <?= $subjudul ?></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">

					<div class="row">
						<div class="col-md-12 mb-3">
							<div class="form-group">
								<label for="post_title" class="form-label">Judul video</label>
								<input id="post_title" name="post_title" class="form-control " type="text"
									placeholder="Judul video..." value="" required>
							</div>
						</div>
					</div>
                    <div class="row">
						<div class="col-md-12 mb-3">
							<div class="form-group">
								<label for="url" class="form-label">Link Video YouTube</label>
								<input type="text" class="form-control yt-input" name="url" id="url" data-preview="#pv-add" placeholder="Tempel link YouTube di sini, contoh: https://www.youtube.com/watch?v=xxxxxxxxxxx" autocomplete="off" required>
								<div class="form-text">Cukup <strong>salin link dari YouTube lalu tempel</strong> (watch, youtu.be, atau shorts). Tidak perlu kode embed.</div>
								<div id="pv-add" class="mt-2" style="max-width:420px"></div>
								
							</div>
						</div>
					</div>
					<div class="row g-2">
						<div class="col mb-0 form-group">
							<label for="emailExLarge" class="form-label">Deskripsi Video</label>
							<textarea class="form-control " name="desc" id="desc"></textarea>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
						Close
					</button>
					<button type="submit" class="btn btn-primary " id="btn-simpan "><i class="fas fa-save"></i>
						Simpan</button>
				</div>

		</div>
		</form>
	</div>
</div>
<div class="modal fade" id="modal-edit" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<form enctype="multipart/form-data" id="editform">
				<div class="modal-header">
					<h5 class="modal-title" id="exampleModalLabel4">Edit <?= $subjudul ?></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">

					<div class="row">
						<div class="col-md-12 mb-3">
							<div class="form-group">
								<label for="post_title" class="form-label">Judul video</label>
								<input id="epost_title" name="epost_title" class="form-control " type="text"
									placeholder="Judul video..." value="" required>
								<input type="hidden" name="kodedit" id="kodedit" value="">

							</div>
						</div>
					</div>
                    <div class="row">
						<div class="col-md-12 mb-3">
							<div class="form-group">
								<label for="eurl" class="form-label">Link Video YouTube</label>
								<input type="text" class="form-control yt-input" name="eurl" id="eurl" data-preview="#pv-edit" placeholder="Tempel link YouTube di sini, contoh: https://www.youtube.com/watch?v=xxxxxxxxxxx" autocomplete="off" required>
								<div class="form-text">Cukup <strong>salin link dari YouTube lalu tempel</strong> (watch, youtu.be, atau shorts). Tidak perlu kode embed.</div>
								<div id="pv-edit" class="mt-2" style="max-width:420px"></div>
								
							</div>
						</div>
					</div>
					<div class="row g-2">
						<div class="col mb-0 form-group">
							<label for="emailExLarge" class="form-label">Deskripsi Video</label>
							<textarea class="form-control " name="edesc" id="edesc"></textarea>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
						Close
					</button>
					<button type="submit" class="btn btn-primary " id="btn-simpan "><i class="fas fa-save"></i>
						Simpan</button>
				</div>

		</div>
		</form>
	</div>
</div>

<script>
	$(document).ready(function () {
		// Pratinjau video langsung saat link ditempel
		function ytId(v) {
			v = $.trim(v || '');
			if (!v) return null;
			var m = v.match(/src\s*=\s*["']([^"']+)["']/i);
			if (/<iframe/i.test(v) && m) v = m[1];
			if (/^[A-Za-z0-9_-]{11}$/.test(v)) return v;
			m = v.match(/(?:youtu\.be\/|youtube(?:-nocookie)?\.com\/(?:embed\/|shorts\/|live\/|v\/)|[?&]v=)([A-Za-z0-9_-]{11})/);
			return m ? m[1] : null;
		}
		function ytPreview(input) {
			var box = $($(input).data('preview')), id = ytId($(input).val());
			if (!$.trim($(input).val())) { box.empty(); return; }
			if (!id) { box.html('<small class="text-danger">Link YouTube belum valid</small>'); return; }
			box.html('<div style="position:relative;padding-top:56.25%;background:#000;border-radius:8px;overflow:hidden"><iframe src="https://www.youtube.com/embed/' + id + '?rel=0" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></div>');
		}
		var ytTimer;
		$(document).on('input paste change', '.yt-input', function () {
			var el = this; clearTimeout(ytTimer);
			ytTimer = setTimeout(function () { ytPreview(el); }, 400);
		});
		$('#modal-add, #modal-edit').on('hidden.bs.modal', function () { $('#pv-add, #pv-edit').empty(); });


		// Ketika gambar di klik
		$('#video').on('click', '.imgvideo', function () {
			// Ambil path gambar dari attribute src
			var imgPath = $(this).attr('src');
			console.log(imgPath);
			// Set path gambar pada modal-img
			$('#modal-img').attr('src', imgPath);
		});

		tampildata();

		function tampildata() {
			$('#video').DataTable({
				"responsive": true,
				"lengthChange": true,
				"autoWidth": false,
				"paging": true,
				"ajax": {
					url: "<?php echo base_url() ?>video/getdata",
					type: 'GET'
				},
			});
		}
		// INPUT
		$("#desc").summernote({
			height: "400px",
			toolbar: [
				['style', ['bold', 'italic', 'underline', 'clear']],
				['font', ['strikethrough', 'superscript', 'subscript']],
				['color', ['color']],
				['para', ['ul', 'ol', 'paragraph']],
				['table', ['table']],
				['misc', ['fullscreen', 'codeview', 'help']],
			],
			
		});

		

		$.validator.setDefaults({
			submitHandler: function (form) {
				var formData = new FormData($(form)[0]);
				$.ajax({
					type: "POST",
					url: "<?= base_url() ?>video/simpan_video",
					data: formData,
					processData: false,
					contentType: false,
					cache: false,
					async: false,

					success: function (response) {
						if (response == "success") {

							$('#video').DataTable().ajax.reload();
							$('[name="post_title"]').val("");
							$('#desc').summernote('code', '');
							$('[name="url"]').val("");
							$('#modal-add').modal('hide');
							$('body').removeClass('modal-open');
							$('.modal-backdrop').remove();
							toastr.success('Data berhasil disimpan');
						} else if (response == "invalid") {
							toastr.warning('Link YouTube tidak valid. Salin ulang link dari YouTube.');
						} else {
							toastr.error('Data Gagal disimpan');
						}

						console.log(response)
					},

					error: function (response) {
						toastr.error('Data Gagal disimpan');

					}
				});
			}
		});
		$('#inputform').validate({
			rules: {
				post_title: {
					required: true,
				},
				url: {
					required: true,
				},
				desc: {
					required: true
				},
			},
			messages: {
				post_title: {
					required: 'masukkan Judul',
				},
				url: {
					required: 'Masukkan URL Youtube',
				},
				desc: {
					required: 'Masukkan Deskripsi Video',
				},
			},
			errorElement: 'span',
			errorPlacement: function (error, element) {
				error.addClass('invalid-feedback');
				element.closest('.form-group').append(error);
			},
			highlight: function (element, errorClass, validClass) {
				$(element).addClass('is-invalid');
			},
			unhighlight: function (element, errorClass, validClass) {
				$(element).removeClass('is-invalid');
			}
		});


		// PROSES EDIT

		$("#edesc").summernote({
			height: "400px",
			toolbar: [
				['style', ['bold', 'italic', 'underline', 'clear']],
				['font', ['strikethrough', 'superscript', 'subscript']],
				['color', ['color']],
				['para', ['ul', 'ol', 'paragraph']],
				['table', ['table']],
				['insert', ['link', 'picture', 'video']],
				['misc', ['fullscreen', 'codeview', 'help']],
			],
			
		});

		

		$('#editform').submit(function (e) {
			e.preventDefault();
			$.ajax({
				type: "POST",
				url: "<?= base_url() ?>video/simpanedit",
				data: new FormData(this),
				processData: false,
				contentType: false,
				cache: false,
				async: false,
				success: function (response) {
					if (response == "success") {

						$('[name="epost_title"]').val("");
						$('#edesc').summernote('code', '');
						$('[name="eurl"]').val("");
						$('#modal-edit').modal('hide');
						$('body').removeClass('modal-open');
						$('.modal-backdrop').remove();
						toastr.success('Data berhasil disimpan');
						$('#video').DataTable().ajax.reload();
					} else if (response == "invalid") {
						toastr.warning('Link YouTube tidak valid. Salin ulang link dari YouTube.');
					} else {
						toastr.error('Data gagal diedit');
					}
					console.log(response)
				},
				error: function (response) {
					toastr.error('Terjadi kesalahan pada server');

				}
			});
		})

		// showedit data
		$('#video').on('click', '.bedit', function () {
			var id = $(this).attr('data');
			$.ajax({
				type: "GET",
				url: "<?= base_url() ?>video/showedit",
				dataType: "JSON",
				data: {
					id: id
				},
				success: function (data) {
					$.each(data, function () {
						$('#modal-edit').modal('show');
						$('[name="kodedit"]').val(id);
						$('[name="epost_title"]').val(data.judul);
						$('[name="eurl"]').val(data.url);
						ytPreview($('[name="eurl"]'));
						$('[name="edesc"]').summernote('code', data.desc)
					});
				}
			});
			return false;
		});


		$('#video').on('click', '.bhapus', function () {
			var id = $(this).attr('data');
			swal.fire({
				title: 'Yakin Menghapus data video ini?',
				text: "Tekan ya jika anda yakin",
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: 'Yes'
			}).then(function (result) {
				if (result.value) {
					$.ajax({
						url: '<?= base_url() ?>video/hapus_video',
						type: 'POST',
						dataType: 'json',
						data: {
							id: id
						},

						success: function (jqXHR, textStatus) {
							if (respon == "success") {

								$('#video').DataTable().ajax.reload();

							} else {


							}

							console.log(response)
						},
						complete: function () {
							swal.hideLoading();
						},

						error: function (a, textStatus) {
							if (a.responseText == 'success') {
								$('#video').DataTable().ajax.reload();
								toastr.success('Data berhasil dihapus');

							} else if (a.responseText == 'ada') {

								toastr.failed('Data gagal dihapus');



							}

							console.log(a.responseText)
						}

					});
				}
			});

		});

	})

</script>