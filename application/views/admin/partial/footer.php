  <!-- / Content -->
  <!-- Footer -->
  <?php
  $setting = $this->db->get('general')->row_array();  
?>
  <footer class="content-footer footer bg-footer-theme">
  	<div class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
  		<div class="mb-2 mb-md-0">
  			©
  			<script>
  				document.write(new Date().getFullYear());

  			</script>
  			, made with ❤️ by
  			<a href="<?= base_url('admin') ?>" class="footer-link fw-bolder"><?=$setting['nama_sekolah'];?></a>
  		</div>
  	</div>
  </footer>
  <!-- / Footer -->
  <div class="content-backdrop fade"></div>
  </div>
  </div>
  </div>
  <div class="layout-overlay layout-menu-toggle"></div>
  </div>
  <script src="<?= base_url('') ?>assets/vendor/libs/jquery/jquery.js"></script>
  <script src="<?= base_url('') ?>assets/vendor/libs/popper/popper.js"></script>
  <script src="<?= base_url('') ?>assets/vendor/js/bootstrap.js"></script>
  <script src="<?= base_url('') ?>assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js"></script>
  <script src="<?= base_url('') ?>assets/vendor/js/menu.js"></script>
  <script src="<?php echo base_url() ?>assets/toastr/toastr.min.js"></script>
  <script>
    // Keamanan: sertakan token CSRF pada semua permintaan AJAX/formulir POST dari panel admin
    (function ($) {
      var tok = $('meta[name="csrf-token"]').attr('content');
      $(document).ajaxSend(function (e, xhr, s) {
        if (tok && !/^(GET|HEAD|OPTIONS)$/i.test(s.type || 'GET') && !s.crossDomain) { xhr.setRequestHeader('X-CSRF-Token', tok); }
      });
      $(document).ajaxError(function (e, xhr) {
        if (xhr.status === 403 && xhr.responseJSON && xhr.responseJSON.status === 'csrf' && window.toastr) {
          toastr.warning(xhr.responseJSON.msg, '', { timeOut: 9000 });
        }
      });
      $(document).on('submit', 'form', function () {
        var f = this;
        if (tok && String(f.method).toLowerCase() === 'post' && !f.querySelector('input[name="csrf_token"]')) {
          $('<input type="hidden" name="csrf_token">').val(tok).appendTo(f);
        }
      });
    })(jQuery);
  </script>
  <script src="<?php echo base_url() ?>assets/datatable/datatables/jquery.dataTables.min.js"></script>
  <script src="<?php echo base_url() ?>assets/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
  <script src="<?php echo base_url() ?>assets/datatable/datatables-responsive/js/dataTables.responsive.min.js">
  </script>
  <script src="<?php echo base_url() ?>assets/datatable/datatables-responsive/js/responsive.bootstrap4.min.js">
  </script>
  <script src="<?php echo base_url() ?>assets/datatable/datatables-buttons/js/dataTables.buttons.min.js">
  </script>
  <script src="<?php echo base_url() ?>assets/datatable/datatables-buttons/js/buttons.bootstrap4.min.js">
  </script>
  <script src="<?php echo base_url() ?>assets/sweetalert2/sweetalert2.min.js"></script>
  <script src="<?php echo base_url() ?>assets/datatable/datatables-buttons/js/buttons.html5.min.js"></script>
  <script src="<?php echo base_url() ?>assets/datatable/datatables-buttons/js/buttons.print.min.js"></script>
  <script src="<?php echo base_url() ?>assets/datatable/datatables-buttons/js/buttons.colVis.min.js"></script>
  <script src="<?php echo base_url() ?>assets/ekko-lightbox/ekko-lightbox.min.js"></script>
  <script src="<?= base_url('') ?>assets/js/main.js"></script>
<script src="<?= base_url('assets/vendor/libs/jquery-validate/') ?>jquery.validate.js"></script>
<script src="<?php echo base_url() ?>assets/fullcalendar/main.js"></script>

<script src="<?= base_url('') ?>assets/summernote/summernote-bs4.min.js"></script>

  <script>
   

  	toastr.options = {
  		"closeButton": true,
  		"debug": false,
  		"newestOnTop": false,
  		"progressBar": true,
  		"positionClass": "toast-top-right",
  		"preventDuplicates": false,
  		"onclick": null,
  		"showDuration": "300",
  		"hideDuration": "1000",
  		"timeOut": "5000",
  		"extendedTimeOut": "1000",
  		"showEasing": "swing",
  		"hideEasing": "linear",
  		"showMethod": "fadeIn",
  		"hideMethod": "fadeOut"
  	}
    <?php if($this->session->flashdata('success')){ ?>
                    toastr.success("<?php echo $this->session->flashdata('success'); ?>");
                    
                <?php }elseif($this->session->flashdata('failed')){ ?>

                    toastr.warning("<?php echo $this->session->flashdata('failed'); ?>");

                <?php } ?>

  </script>
  
  </body>

  </html>
