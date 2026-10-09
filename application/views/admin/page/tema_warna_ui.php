<?php $theme = $this->Settings_model->theme(); ?>
<style>
	#theme-card .theme-range {
		-webkit-appearance: none; appearance: none; width: 100%; height: 14px; border-radius: 999px;
		background: var(--track, #ddd); outline: none; margin: 6px 0 14px; cursor: pointer;
		box-shadow: inset 0 0 0 1px rgba(0,0,0,.12);
	}
	#theme-card .theme-range::-webkit-slider-runnable-track { background: transparent; height: 14px; border-radius: 999px; }
	#theme-card .theme-range::-webkit-slider-thumb {
		-webkit-appearance: none; appearance: none; width: 26px; height: 26px; border-radius: 50%;
		background: #fff; border: 3px solid var(--thumb, #666); margin-top: -6px;
		box-shadow: 0 1px 4px rgba(0,0,0,.35);
	}
	#theme-card .theme-range::-moz-range-track { background: transparent; height: 14px; }
	#theme-card .theme-range::-moz-range-thumb {
		width: 20px; height: 20px; border-radius: 50%; background: #fff;
		border: 3px solid var(--thumb, #666); box-shadow: 0 1px 4px rgba(0,0,0,.35);
	}
	#theme-card .theme-swatch { width: 44px; height: 44px; border-radius: 12px; margin-right: 12px; box-shadow: inset 0 0 0 1px rgba(0,0,0,.15); flex: none; }
	#theme-card .theme-preset {
		border: 2px solid transparent; border-radius: 999px; padding: 4px 12px 4px 4px; background: #f5f5f9;
		display: inline-flex; align-items: center; gap: 8px; font-size: 13px; cursor: pointer;
	}
	#theme-card .theme-preset:hover { border-color: #b8bdd6; }
	#theme-card .theme-preset i { display: inline-block; width: 26px; height: 26px; border-radius: 50%; }
	#theme-card .theme-preset i + i { margin-left: -14px; box-shadow: -2px 0 0 #fff; }
	/* Pratinjau */
	#theme-card .tp { border: 1px solid #d9dee3; border-radius: 12px; overflow: hidden; background: #fff; font-size: 13px; }
	#theme-card .tp-nav { display: flex; gap: 14px; align-items: center; padding: 10px 14px; border-bottom: 1px solid #eee; }
	#theme-card .tp-nav b { color: #19232B; margin-right: auto; }
	#theme-card .tp-nav span { color: #555; }
	#theme-card .tp-nav .tp-on { color: var(--pv-p); font-weight: 600; }
	#theme-card .tp-hero { padding: 22px 16px; background: #F2F3F5; }
	#theme-card .tp-tag { color: var(--pv-p); letter-spacing: 2px; font-weight: 700; font-size: 11px; }
	#theme-card .tp-hero h4 { color: var(--pv-s); margin: 6px 0 14px; }
	#theme-card .tp-btn { border: 0; color: #fff; padding: 8px 16px; border-radius: 8px; margin-right: 6px; font-weight: 600; }
	#theme-card .tp-b1 { background: var(--pv-p); }
	#theme-card .tp-b2 { background: var(--pv-s); }
	#theme-card .tp-foot { background: var(--pv-s); color: #fff; padding: 12px 16px; font-size: 12px; }
</style>

<div class="card mb-4" id="theme-card"
	data-primary="<?= $theme['primary'] ?>" data-secondary="<?= $theme['secondary'] ?>"
	data-def-primary="<?= Settings_model::THEME_PRIMARY ?>" data-def-secondary="<?= Settings_model::THEME_SECONDARY ?>"
	data-save="<?= base_url('setting/simpan_tema') ?>" data-reset="<?= base_url('setting/reset_tema') ?>">
	<h5 class="card-header">Tema Warna Website</h5>
	<div class="card-body">
		<p class="text-muted mb-4">Geser slider untuk memilih warna website sesuai selera. Pratinjau di sebelah kanan
			berubah langsung. Perubahan baru tampil di website setelah menekan <strong>Simpan Tema</strong>.</p>

		<div class="row g-4">
			<div class="col-lg-7">
				<!-- Warna utama -->
				<div class="theme-group mb-4" data-key="primary" data-smin="25" data-smax="100" data-lmin="30" data-lmax="70">
					<div class="d-flex align-items-center mb-3">
						<span class="theme-swatch"></span>
						<div>
							<strong>Warna Utama</strong>
							<div class="small text-muted">Tombol, menu aktif, ikon, dan aksen</div>
						</div>
						<code class="ms-auto theme-hex"></code>
					</div>
					<label class="small text-muted">Warna</label>
					<input type="range" class="theme-range theme-h" min="0" max="360" step="1">
					<label class="small text-muted">Intensitas (pucat &#8594; pekat)</label>
					<input type="range" class="theme-range theme-s" step="1">
					<label class="small text-muted">Terang / gelap</label>
					<input type="range" class="theme-range theme-l" step="1">
					<div class="small text-warning theme-warn" style="display:none">Warna ini cukup terang, tulisan putih pada tombol bisa sulit dibaca. Geser "Terang / gelap" ke kiri.</div>
				</div>

				<!-- Warna sekunder -->
				<div class="theme-group mb-3" data-key="secondary" data-smin="20" data-smax="90" data-lmin="10" data-lmax="40">
					<div class="d-flex align-items-center mb-3">
						<span class="theme-swatch"></span>
						<div>
							<strong>Warna Sekunder</strong>
							<div class="small text-muted">Judul, bagian footer, dan tombol kedua</div>
						</div>
						<code class="ms-auto theme-hex"></code>
					</div>
					<label class="small text-muted">Warna</label>
					<input type="range" class="theme-range theme-h" min="0" max="360" step="1">
					<label class="small text-muted">Intensitas (pucat &#8594; pekat)</label>
					<input type="range" class="theme-range theme-s" step="1">
					<label class="small text-muted">Terang / gelap</label>
					<input type="range" class="theme-range theme-l" step="1">
				</div>
			</div>

			<div class="col-lg-5">
				<div class="small text-muted mb-2">Pratinjau tampilan</div>
				<div class="tp" id="theme-preview">
					<div class="tp-nav"><b>NAMA SEKOLAH ANDA</b><span class="tp-on">Beranda</span><span>Profil</span><span>Galeri</span></div>
					<div class="tp-hero">
						<div class="tp-tag">SELAMAT DATANG</div>
						<h4>Madrasah Ibtidaiyah</h4>
						<button type="button" class="tp-btn tp-b1">Daftar Sekarang</button>
						<button type="button" class="tp-btn tp-b2">Selengkapnya</button>
					</div>
					<div class="tp-foot">&copy; Nama Sekolah Anda</div>
				</div>
			</div>
		</div>

		<hr class="my-4">
		<div class="small text-muted mb-2">Atau pilih paduan warna siap pakai</div>
		<div class="d-flex flex-wrap gap-2 mb-4" id="theme-presets">
			<button type="button" class="theme-preset" data-p="#FF7350" data-s="#125875"><i style="background:#FF7350"></i><i style="background:#125875"></i>Bawaan</button>
			<button type="button" class="theme-preset" data-p="#22A06B" data-s="#0F4C3A"><i style="background:#22A06B"></i><i style="background:#0F4C3A"></i>Hijau</button>
			<button type="button" class="theme-preset" data-p="#2F80ED" data-s="#12335B"><i style="background:#2F80ED"></i><i style="background:#12335B"></i>Biru</button>
			<button type="button" class="theme-preset" data-p="#8E5CF7" data-s="#2E1A5B"><i style="background:#8E5CF7"></i><i style="background:#2E1A5B"></i>Ungu</button>
			<button type="button" class="theme-preset" data-p="#D64545" data-s="#5B1A1A"><i style="background:#D64545"></i><i style="background:#5B1A1A"></i>Marun</button>
			<button type="button" class="theme-preset" data-p="#D98E04" data-s="#3D2B0A"><i style="background:#D98E04"></i><i style="background:#3D2B0A"></i>Emas</button>
		</div>

		<button type="button" id="theme-save" class="btn btn-primary me-2"><i class="fa fa-save menu-icon"></i> Simpan Tema</button>
		<button type="button" id="theme-reset" class="btn btn-outline-secondary">Kembalikan ke Warna Bawaan</button>
	</div>
</div>
