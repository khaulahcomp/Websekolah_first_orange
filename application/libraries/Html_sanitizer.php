<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pembersih HTML berbasis daftar putih (DOMDocument).
 * Membuang skrip, atribut on*, URL berbahaya, dan iframe selain video/peta tepercaya,
 * sambil mempertahankan hasil editor (Summernote): paragraf, tabel, gambar, tautan, dll.
 */
class Html_sanitizer
{
	protected static $tags = array(
		'a' => array('href', 'target', 'title', 'name'),
		'abbr' => array('title'), 'b' => array(), 'blockquote' => array(), 'br' => array(), 'caption' => array(),
		'code' => array(), 'col' => array('width'), 'colgroup' => array('width'), 'dd' => array(), 'div' => array(),
		'dl' => array(), 'dt' => array(), 'em' => array(), 'figcaption' => array(), 'figure' => array(),
		'font' => array('color', 'size', 'face'), 'h1' => array(), 'h2' => array(), 'h3' => array(), 'h4' => array(),
		'h5' => array(), 'h6' => array(), 'hr' => array(), 'i' => array(),
		'img' => array('src', 'alt', 'title', 'width', 'height'),
		'li' => array(), 'ol' => array('start', 'type'), 'p' => array(), 'pre' => array(), 's' => array(),
		'small' => array(), 'span' => array(), 'strike' => array(), 'strong' => array(), 'sub' => array(), 'sup' => array(),
		'table' => array('border', 'cellpadding', 'cellspacing', 'width', 'align'), 'tbody' => array(),
		'td' => array('colspan', 'rowspan', 'align', 'valign', 'width', 'height'), 'tfoot' => array(),
		'th' => array('colspan', 'rowspan', 'align', 'valign', 'width', 'height'), 'thead' => array(),
		'tr' => array('align', 'valign'), 'u' => array(), 'ul' => array(), 'center' => array(),
		'iframe' => array('src', 'width', 'height', 'frameborder', 'allowfullscreen', 'title', 'scrolling'),
	);
	protected static $drop = array('script', 'style', 'object', 'embed', 'applet', 'form', 'input', 'button', 'textarea',
		'select', 'option', 'meta', 'link', 'base', 'noscript', 'template', 'svg', 'math', 'frame', 'frameset',
		'title', 'head', 'audio', 'video', 'source', 'canvas', 'xml', 'isindex', 'bgsound', 'layer', 'ilayer');
	protected static $iframeHosts = array('www.youtube.com', 'www.youtube-nocookie.com', 'player.vimeo.com',
		'www.google.com', 'maps.google.com', 'www.dailymotion.com');
	protected static $styleProps = array('color', 'background-color', 'background', 'font-size', 'font-weight', 'font-style',
		'font-family', 'text-align', 'text-decoration', 'text-indent', 'line-height', 'width', 'height', 'max-width',
		'min-width', 'max-height', 'margin', 'margin-top', 'margin-bottom', 'margin-left', 'margin-right', 'padding',
		'padding-top', 'padding-bottom', 'padding-left', 'padding-right', 'border', 'border-top', 'border-bottom',
		'border-left', 'border-right', 'border-color', 'border-style', 'border-width', 'border-collapse', 'float',
		'clear', 'vertical-align', 'list-style-type', 'white-space', 'letter-spacing', 'word-spacing');

	public static function clean($html)
	{
		if (!class_exists('DOMDocument')) { return self::fallback($html); }
		$prev = libxml_use_internal_errors(TRUE);
		$dom = new DOMDocument('1.0', 'UTF-8');
		$ok = $dom->loadHTML('<!DOCTYPE html><html><head><meta charset="utf-8"></head><body><div id="__r">' . $html . '</div></body></html>', LIBXML_NONET);
		libxml_clear_errors();
		libxml_use_internal_errors($prev);
		if (!$ok) { return htmlspecialchars(strip_tags($html), ENT_QUOTES, 'UTF-8'); }
		$dom->encoding = 'UTF-8';
		$root = null;
		foreach ($dom->getElementsByTagName('div') as $d) { if ($d->getAttribute('id') === '__r') { $root = $d; break; } }
		if (!$root) { return ''; }
		self::walk($root);
		$out = '';
		foreach ($root->childNodes as $c) { $out .= $dom->saveHTML($c); }
		return $out;
	}

	protected static function walk(DOMNode $parent)
	{
		$kids = array();
		foreach ($parent->childNodes as $c) { $kids[] = $c; }
		foreach ($kids as $n) {
			if ($n->nodeType === XML_TEXT_NODE) { continue; }
			if ($n->nodeType !== XML_ELEMENT_NODE) { $parent->removeChild($n); continue; } // komentar, CDATA, PI
			$tag = strtolower($n->nodeName);
			if (in_array($tag, self::$drop, TRUE)) { $parent->removeChild($n); continue; }
			self::walk($n);                                   // bersihkan anak lebih dulu
			if (!isset(self::$tags[$tag])) {                  // tag tak dikenal: buang tag, simpan isi
				while ($n->firstChild) { $parent->insertBefore($n->firstChild, $n); }
				$parent->removeChild($n);
				continue;
			}
			if (!self::attrs($n, $tag)) { $parent->removeChild($n); }
		}
	}

	protected static function attrs(DOMElement $el, $tag)
	{
		$allowed = array_merge(self::$tags[$tag], array('class', 'style', 'dir', 'lang'));
		$names = array();
		foreach ($el->attributes as $a) { $names[] = $a->nodeName; }
		foreach ($names as $name) {
			$l = strtolower($name); $v = $el->getAttribute($name);
			if (strpos($l, 'on') === 0 || !in_array($l, $allowed, TRUE)) { $el->removeAttribute($name); continue; }
			if ($l === 'href') {
				$u = self::url($v, FALSE); if ($u === NULL) { $el->removeAttribute($name); } else { $el->setAttribute($name, $u); }
			} elseif ($l === 'src' && $tag === 'img') {
				$u = self::url($v, TRUE); if ($u === NULL) { $el->removeAttribute($name); } else { $el->setAttribute($name, $u); }
			} elseif ($l === 'src' && $tag === 'iframe') {
				$u = self::iframe_src($v); if ($u === NULL) { return FALSE; } $el->setAttribute($name, $u);
			} elseif ($l === 'style') {
				$s = self::style($v); if ($s === '') { $el->removeAttribute($name); } else { $el->setAttribute($name, $s); }
			} elseif ($l === 'class') {
				$c = trim(preg_replace('/[^A-Za-z0-9_\- ]/', '', $v)); if ($c === '') { $el->removeAttribute($name); } else { $el->setAttribute($name, $c); }
			} elseif ($l === 'target') {
				$el->setAttribute('target', '_blank');
			} elseif (in_array($l, array('width', 'height', 'colspan', 'rowspan', 'border', 'cellpadding', 'cellspacing', 'start', 'size'), TRUE)) {
				if (!preg_match('/^[0-9]{1,4}(%|px)?$/', trim($v))) { $el->removeAttribute($name); }
			} elseif ($l === 'color') {
				if (!preg_match('/^(#[0-9a-f]{3,8}|[a-z]{3,20}|rgba?\([0-9., %]+\))$/i', trim($v))) { $el->removeAttribute($name); }
			} elseif ($l === 'frameborder' || $l === 'scrolling' || $l === 'face' || $l === 'type' || $l === 'align' || $l === 'valign' || $l === 'name' || $l === 'title' || $l === 'alt' || $l === 'dir' || $l === 'lang') {
				$el->setAttribute($name, preg_replace('/[\x00-\x1F"<>]/', '', $v));
			}
		}
		if ($tag === 'a') {
			if ($el->hasAttribute('target')) { $el->setAttribute('rel', 'noopener noreferrer'); }
		}
		if ($tag === 'iframe' && !$el->hasAttribute('src')) { return FALSE; }
		if ($tag === 'iframe') {
			$el->setAttribute('loading', 'lazy');
			$el->setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');
		}
		return TRUE;
	}

	protected static function url($v, $img)
	{
		$v = trim($v);
		$norm = preg_replace('/[\x00-\x20\x7F]+/', '', $v);
		if ($norm === '') { return NULL; }
		if (preg_match('/^([a-z][a-z0-9+.\-]*):/i', $norm, $m)) {
			$sch = strtolower($m[1]);
			if ($sch === 'data') {
				return ($img && preg_match('#^data:image/(png|jpe?g|gif|webp);base64,[A-Za-z0-9+/=]+$#i', $norm)) ? $norm : NULL;
			}
			$ok = $img ? array('http', 'https') : array('http', 'https', 'mailto', 'tel');
			return in_array($sch, $ok, TRUE) ? $v : NULL;
		}
		return $v; // relatif, "#", "/", "?"
	}

	protected static function iframe_src($v)
	{
		$v = trim($v);
		if (strpos($v, '//') === 0) { $v = 'https:' . $v; }
		$p = @parse_url($v);
		if (!$p || strtolower(isset($p['scheme']) ? $p['scheme'] : '') !== 'https' || empty($p['host'])) { return NULL; }
		$h = strtolower($p['host']);
		if (!in_array($h, self::$iframeHosts, TRUE)) { return NULL; }
		if (($h === 'www.google.com' || $h === 'maps.google.com') && strpos(isset($p['path']) ? $p['path'] : '', '/maps') !== 0) { return NULL; }
		return $v;
	}

	protected static function style($v)
	{
		$out = array();
		foreach (explode(';', $v) as $decl) {
			if (strpos($decl, ':') === FALSE) { continue; }
			list($p, $val) = array_map('trim', explode(':', $decl, 2));
			$p = strtolower($p);
			if (!in_array($p, self::$styleProps, TRUE) || $val === '' || strlen($val) > 200) { continue; }
			if (preg_match('/(expression|javascript:|vbscript:|url\s*\(|@import|behavior|binding|\\\\|<|>|&#|\/\*)/i', $val)) { continue; }
			$out[] = $p . ': ' . $val;
		}
		return implode('; ', $out);
	}

	protected static function fallback($html)
	{
		$allow = '<' . implode('><', array_keys(self::$tags)) . '>';
		$html = preg_replace('#<(script|style|iframe|object|embed)\b.*?</\1>#is', '', $html);
		$html = strip_tags($html, str_replace('<iframe>', '', $allow));
		$html = preg_replace('/\s+on[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $html);
		$html = preg_replace('/(href|src)\s*=\s*("|\')\s*(javascript|vbscript|data):[^"\']*\2/i', '$1=$2#$2', $html);
		return $html;
	}
}
