<script>
	(function () {
		var card = document.getElementById('theme-card');
		if (!card) return;

		var state = {
			primary: card.dataset.primary,
			secondary: card.dataset.secondary
		};
		var preview = document.getElementById('theme-preview');

		function clamp(v, a, b) { return Math.max(a, Math.min(b, v)); }

		function hexToHsl(hex) {
			var r = parseInt(hex.substr(1, 2), 16) / 255,
				g = parseInt(hex.substr(3, 2), 16) / 255,
				b = parseInt(hex.substr(5, 2), 16) / 255;
			var max = Math.max(r, g, b), min = Math.min(r, g, b), h = 0, s = 0, l = (max + min) / 2;
			if (max !== min) {
				var d = max - min;
				s = l > 0.5 ? d / (2 - max - min) : d / (max + min);
				if (max === r) h = (g - b) / d + (g < b ? 6 : 0);
				else if (max === g) h = (b - r) / d + 2;
				else h = (r - g) / d + 4;
				h *= 60;
			}
			return { h: Math.round(h), s: Math.round(s * 100), l: Math.round(l * 100) };
		}

		function hslToHex(h, s, l) {
			s /= 100; l /= 100;
			var k = function (n) { return (n + h / 30) % 12; };
			var a = s * Math.min(l, 1 - l);
			var f = function (n) {
				var c = l - a * Math.max(-1, Math.min(k(n) - 3, Math.min(9 - k(n), 1)));
				return ('0' + Math.round(255 * c).toString(16)).slice(-2);
			};
			return ('#' + f(0) + f(8) + f(4)).toUpperCase();
		}

		// Kontras terhadap putih (WCAG) untuk peringatan keterbacaan
		function contrastWhite(hex) {
			var ch = [1, 3, 5].map(function (i) {
				var v = parseInt(hex.substr(i, 2), 16) / 255;
				return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
			});
			var lum = 0.2126 * ch[0] + 0.7152 * ch[1] + 0.0722 * ch[2];
			return 1.05 / (lum + 0.05);
		}

		var groups = {};
		[].forEach.call(card.querySelectorAll('.theme-group'), function (el) {
			var g = {
				el: el, key: el.dataset.key,
				smin: +el.dataset.smin, smax: +el.dataset.smax,
				lmin: +el.dataset.lmin, lmax: +el.dataset.lmax,
				h: el.querySelector('.theme-h'), s: el.querySelector('.theme-s'), l: el.querySelector('.theme-l'),
				swatch: el.querySelector('.theme-swatch'), hex: el.querySelector('.theme-hex'),
				warn: el.querySelector('.theme-warn')
			};
			g.s.min = g.smin; g.s.max = g.smax;
			g.l.min = g.lmin; g.l.max = g.lmax;
			groups[g.key] = g;
			[g.h, g.s, g.l].forEach(function (inp) {
				inp.addEventListener('input', function () { fromSliders(g); });
			});
		});

		function paint(g, hex) {
			var hs = hexToHsl(hex);
			var H = +g.h.value, S = +g.s.value, L = +g.l.value;
			g.swatch.style.background = hex;
			g.hex.textContent = hex;
			g.h.style.setProperty('--track', 'linear-gradient(to right,' +
				[0, 60, 120, 180, 240, 300, 360].map(function (x) { return hslToHex(x % 360, 85, 50); }).join(',') + ')');
			g.s.style.setProperty('--track', 'linear-gradient(to right,' + hslToHex(H, g.smin, L) + ',' + hslToHex(H, g.smax, L) + ')');
			g.l.style.setProperty('--track', 'linear-gradient(to right,' + hslToHex(H, S, g.lmin) + ',' + hslToHex(H, S, (g.lmin + g.lmax) / 2) + ',' + hslToHex(H, S, g.lmax) + ')');
			[g.h, g.s, g.l].forEach(function (inp) { inp.style.setProperty('--thumb', hex); });
			if (g.warn) g.warn.style.display = contrastWhite(hex) < 2.2 ? 'block' : 'none';
		}

		function render() {
			preview.style.setProperty('--pv-p', state.primary);
			preview.style.setProperty('--pv-s', state.secondary);
			paint(groups.primary, state.primary);
			paint(groups.secondary, state.secondary);
		}

		function fromSliders(g) {
			state[g.key] = hslToHex(+g.h.value, +g.s.value, +g.l.value);
			render();
		}

		function setColor(key, hex) {
			var g = groups[key], hs = hexToHsl(hex);
			var s2 = clamp(hs.s, g.smin, g.smax), l2 = clamp(hs.l, g.lmin, g.lmax);
			g.h.value = hs.h;
			g.s.value = s2;
			g.l.value = l2;
			// Pertahankan hex asli (tanpa pembulatan slider) selama masih dalam rentang;
			// hanya dihitung ulang dari slider bila perlu dipaksa masuk rentang.
			state[key] = (s2 === hs.s && l2 === hs.l) ? hex.toUpperCase() : hslToHex(hs.h, s2, l2);
		}

		function init() {
			setColor('primary', state.primary);
			setColor('secondary', state.secondary);
			render();
		}
		init();

		[].forEach.call(card.querySelectorAll('.theme-preset'), function (b) {
			b.addEventListener('click', function () {
				setColor('primary', b.dataset.p);
				setColor('secondary', b.dataset.s);
				render();
			});
		});

		document.getElementById('theme-save').addEventListener('click', function () {
			var btn = this; btn.disabled = true;
			$.post(card.dataset.save, { theme_color: state.primary, theme_color2: state.secondary })
				.done(function (r) {
					r = $.trim(r);
					if (r === 'success') toastr.success('Tema warna disimpan. Muat ulang website untuk melihat hasilnya.');
					else if (r === 'invalid') toastr.warning('Warna tidak valid.');
					else toastr.error('Gagal menyimpan tema. Pastikan akun database boleh mengubah tabel (ALTER).');
				})
				.fail(function () { toastr.error('Terjadi kesalahan pada server'); })
				.always(function () { btn.disabled = false; });
		});

		document.getElementById('theme-reset').addEventListener('click', function () {
			if (!confirm('Kembalikan warna website ke tampilan bawaan?')) return;
			$.post(card.dataset.reset).done(function (r) {
				if ($.trim(r) === 'success') {
					setColor('primary', card.dataset.defPrimary);
					setColor('secondary', card.dataset.defSecondary);
					render();
					toastr.success('Warna dikembalikan ke bawaan.');
				} else {
					toastr.error('Gagal mengembalikan warna.');
				}
			}).fail(function () { toastr.error('Terjadi kesalahan pada server'); });
		});
	})();
</script>
