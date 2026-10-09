<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Helper YouTube
 * Admin cukup menempelkan (copy-paste) link video dari YouTube.
 * Format yang dikenali:
 *   https://www.youtube.com/watch?v=ID      (juga dengan &list=..., &t=90s)
 *   https://youtu.be/ID
 *   https://www.youtube.com/shorts/ID
 *   https://www.youtube.com/live/ID
 *   https://www.youtube.com/embed/ID
 *   https://m.youtube.com/... , https://music.youtube.com/...
 *   kode <iframe ...> lama dari YouTube (tetap didukung)
 *
 * Iframe yang tampil SELALU dibuat oleh sistem dari ID video yang sudah
 * divalidasi (11 karakter), sehingga tidak ada HTML mentah dari input admin
 * yang dicetak ke halaman.
 */

if ( ! function_exists('youtube_id'))
{
	/**
	 * Ambil ID video (11 karakter) dari link/kode apa pun. FALSE bila tidak valid.
	 */
	function youtube_id($input)
	{
		$input = trim(html_entity_decode((string) $input, ENT_QUOTES, 'UTF-8'));
		if ($input === '')
		{
			return FALSE;
		}

		// Kode iframe lama: ambil nilai src-nya saja
		if (stripos($input, '<iframe') !== FALSE)
		{
			if ( ! preg_match('/\bsrc\s*=\s*["\']([^"\']+)["\']/i', $input, $m))
			{
				return FALSE;
			}
			$input = trim($m[1]);
		}

		// ID polos
		if (preg_match('/^[A-Za-z0-9_-]{11}$/', $input))
		{
			return $input;
		}

		// Boleh tanpa https:// (mis. "youtube.com/watch?v=...")
		if ( ! preg_match('#^(?:https?:)?//#i', $input))
		{
			$input = 'https://'.$input;
		}
		elseif (strpos($input, '//') === 0)
		{
			$input = 'https:'.$input;
		}

		$parts = @parse_url($input);
		if ( ! $parts OR empty($parts['host']))
		{
			return FALSE;
		}

		$host = strtolower(preg_replace('/^(www\.|m\.|music\.)/', '', $parts['host']));
		$path = isset($parts['path']) ? $parts['path'] : '';
		$query = array();
		if (isset($parts['query']))
		{
			parse_str($parts['query'], $query);
		}

		$id = '';
		if ($host === 'youtu.be')
		{
			$seg = explode('/', trim($path, '/'));
			$id = $seg[0];
		}
		elseif ($host === 'youtube.com' OR $host === 'youtube-nocookie.com')
		{
			if ($path === '/watch' && ! empty($query['v']) && is_string($query['v']))
			{
				$id = $query['v'];
			}
			elseif (preg_match('#^/(?:embed|shorts|live|v)/([^/?&\#]+)#', $path, $m))
			{
				$id = $m[1];
			}
		}

		return preg_match('/^[A-Za-z0-9_-]{11}$/', $id) ? $id : FALSE;
	}
}

if ( ! function_exists('youtube_start'))
{
	/**
	 * Detik mulai putar dari parameter t= / start= (mis. 90, 90s, 1m30s, 1h2m3s).
	 */
	function youtube_start($input)
	{
		$input = html_entity_decode((string) $input, ENT_QUOTES, 'UTF-8');
		if ( ! preg_match('/[?&#](?:t|start)=([0-9hms]+)/i', $input, $m))
		{
			return 0;
		}
		$v = strtolower($m[1]);
		if (ctype_digit($v))
		{
			return (int) $v;
		}
		if (preg_match('/^(?:(\d+)h)?(?:(\d+)m)?(?:(\d+)s)?$/', $v, $t))
		{
			return ((int) (isset($t[1]) ? $t[1] : 0)) * 3600
				+ ((int) (isset($t[2]) ? $t[2] : 0)) * 60
				+ ((int) (isset($t[3]) ? $t[3] : 0));
		}
		return 0;
	}
}

if ( ! function_exists('youtube_watch_url'))
{
	/**
	 * Bentuk link baku yang disimpan ke database. FALSE bila tidak valid.
	 */
	function youtube_watch_url($input)
	{
		$id = youtube_id($input);
		if ($id === FALSE)
		{
			return FALSE;
		}
		$start = youtube_start($input);
		return 'https://www.youtube.com/watch?v='.$id.($start > 0 ? '&t='.$start.'s' : '');
	}
}

if ( ! function_exists('youtube_embed'))
{
	/**
	 * Iframe responsif 16:9 yang aman. String kosong bila link tidak valid.
	 *
	 * @param string $input  link YouTube / kode iframe lama
	 * @param string $title  judul untuk atribut title (aksesibilitas)
	 * @param string $max_width  mis. '240px' untuk pratinjau kecil di tabel admin
	 */
	function youtube_embed($input, $title = 'Video YouTube', $max_width = '')
	{
		$id = youtube_id($input);
		if ($id === FALSE)
		{
			return '';
		}
		$start = youtube_start($input);
		$src = 'https://www.youtube.com/embed/'.$id.'?rel=0'.($start > 0 ? '&start='.$start : '');

		$wrap = 'position:relative;width:100%;padding-top:56.25%;background:#000;border-radius:8px;overflow:hidden;';
		if ($max_width !== '')
		{
			$wrap = 'max-width:'.preg_replace('/[^0-9a-z.%]/i', '', $max_width).';'.$wrap;
		}

		return '<div class="yt-embed" style="'.$wrap.'">'
			.'<iframe src="'.htmlspecialchars($src, ENT_QUOTES, 'UTF-8').'"'
			.' title="'.htmlspecialchars($title, ENT_QUOTES, 'UTF-8').'"'
			.' style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;"'
			.' loading="lazy" referrerpolicy="strict-origin-when-cross-origin"'
			.' allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"'
			.' allowfullscreen></iframe></div>';
	}
}
