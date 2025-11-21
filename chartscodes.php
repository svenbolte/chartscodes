<?php
/*
Plugin Name: Charts QRcodes
Description: Webcounter, shortcodes for QRCodes, IP2Flag, bar, line and Pie, Donut Pie, Polar Pie, Radar, Horizontal Bar, monthly post archive as bar chart, use as html widget too
Author: PBMod und andere
Plugin URI: https://github.com/svenbolte/chartcodes
Author URI: https://github.com/svenbolte
License: GPLv3
Tags: QRCode, Shortcode, Horizontal Barchart,Linechart, Piechart, Barchart, Donutchart, IPflag, Visitorinfo
Text Domain: pb-chartscodes
Domain Path: /languages/
Version: 11.2.120
Stable tag: 11.2.120
Requires at least: 6.0
Tested up to: 6.8.2
Requires PHP: 8.2
*/

if ( ! defined( 'ABSPATH' ) ) {	exit; } // Exit if accessed directly.

add_action( 'plugins_loaded', 'chartscodes_textdomain' );
function chartscodes_textdomain() {
	load_plugin_textdomain( 'pb-chartscodes', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

// Fontawesomeplus laden, wenn nicht penguin style für browsericon und os icons
function ccode_enqueue_scripts( $page ) {
	global $post;
	// load fontawesome 4.7 plus if not penguin theme
	$wpxtheme = wp_get_theme(); // gets the current theme
	if ( 'pb-chartscodes' == $wpxtheme->name || 'pb-chartscodes' == $wpxtheme->parent_theme ) { $xpenguin = true;} else { $xpenguin=false; }
	if (!$xpenguin) wp_enqueue_style('font-awesome', plugin_dir_url( __FILE__ ) . '/assets/fontawesomeplus.min.css', true);
}
add_action( 'wp_enqueue_scripts', 'ccode_enqueue_scripts' );

// ---------------- page für den Webcounter Adminbereich  erzeugen-------------------------------
function cc_page_by_title($pagetitle) {
	$query = new WP_Query(
		array(
			'post_type'              => array('post', 'page'),
			'title'                  => $pagetitle,
			'post_status'            => 'all',
			'posts_per_page'         => 1,
			'no_found_rows'          => true,
			'ignore_sticky_posts'    => true,
			'update_post_term_cache' => false,
			'update_post_meta_cache' => false,
			'orderby'                => 'post_date ID',
			'order'                  => 'ASC',
		)
	);
	if ( ! empty( $query->post ) ) {
		$page_got_by_title = $query->post;
	} else {
		$page_got_by_title = null;
	}
	return $page_got_by_title;
}

register_activation_hook( __FILE__, 'create_webcounter' );
function create_webcounter() {
	// Webcounterseite für admin erzeugen
	$new_page_title = 'Webcounter Stats';
	$slug = 'webcounter';
	$new_page_content = '[webcounter admin=1]';
	$new_page_template = ''; //ex. template-custom.php. Leave blank for default
	$page_check = cc_page_by_title($new_page_title);
	$new_page = array(
		'post_type' => 'page',
		'post_name'  =>   $slug,
		'post_title' => $new_page_title,
		'post_content' => $new_page_content,
		'post_status' => 'private',
		'post_author' => 1,
		'comment_status' => 'closed',   // if you prefer
		'ping_status' => 'closed',      // if you prefer
   );
	if(!isset($page_check->ID)){
		$new_page_id = wp_insert_post($new_page);
		if(!empty($new_page_template)){
			update_post_meta($new_page_id, '_wp_page_template', $new_page_template);
		}
	}
}


// ------------------------- Class für Chart-Diagramme und Shortcode gd_charts ----------------------------------------------- 

if (!class_exists('WPGDCharts')) {
    class WPGDCharts {

        public function __construct() {
            add_action('init', [$this, 'init_hooks']);
        }

        public function init_hooks() {
            $this->register_shortcode();
        }

        public function register_shortcode() {
            add_shortcode('gd_chart', [$this, 'shortcode']);
        }

        private function default_atts() {
            return [
                'type' => 'line', // line|pie|hbar|vbar|polar
                'width' => 900,
                'height' => 400,
                'bg' => '#ffffff',  // 'transparent' also possible
                'fg' => '#333333',
                'grid' => '#dddddd',
                'colors' => '',
                'title' => ' ',
                'legend' => 'true',
                'data' => 'A:10|B:20|C:30',
                'max' => '',
                'dpi' => 1,
                'responsive' => 'true',
                'cache' => 'true',
                'class' => 'gd-chart',
                'style' => '',
                'alt' => '',
                'table' => 'false',
                'table_pos' => 'below',
                'table_class' => 'gd-chart-table',
                'table_style' => 'margin-top:8px;',
                'table_caption' => '',
                'decimals' => '0',
                'base' => get_theme_mod('link-color', '#006060'), // Standardfarbe aus Theme oder fallback
                'palette' => 'accent',
                'ncolors' => '',
            ];
        }

        public function shortcode($atts) {
            $atts = shortcode_atts($this->default_atts(), $atts, 'gd_chart');
            $parsed = $this->parse_data($atts['data']);
            $colors = $this->parse_colors($atts['colors']);
            if (empty($colors)) {
                $need = $this->compute_needed_colors($parsed, strtolower($atts['type']));
                $base = trim((string)($atts['base'] ?? ''));
                if ($base !== '') {
                    $colors = ($atts['palette']==='mono') ? $this->palette_mono($base, $need) : $this->palette_accent($base, $need);
                }
            }
            if (empty($colors)) {
                $colors = ['#1e88e5','#e53935','#43a047','#fb8c00','#8e24aa','#00acc1','#6d4c41','#fdd835'];
            }
            ob_start();
            $this->render_chart($atts, $parsed, $colors);
            $img_data = ob_get_clean();
            $base64 = base64_encode($img_data);
            $alt = esc_attr($atts['alt'] ?: ($atts['title'] ?: 'Chart'));
            $class = esc_attr(trim(($atts['class'] ?? '') . ' gd-chart--inline'));
            $style = esc_attr(trim((string)($atts['style'] ?? 'max-width:100%;height:auto;')));
            $img_html = sprintf('<img decoding="async" loading="lazy" class="%s" style="%s" src="data:image/png;base64,%s" alt="%s" />', $class, $style, $base64, $alt);
			$table_html = '';
            $want_table = strtolower($atts['table']) === 'true' || strtolower($atts['table']) === '1';
            if ($want_table) {
                $table_html = $this->build_table_html($parsed, $atts, $colors);
            }

            $pos = strtolower($atts['table_pos']);
            if (!$want_table) return $img_html;
            if ($pos === 'only') return $table_html;
            if ($pos === 'above') return $table_html . $img_html;
            return $img_html . $table_html;
        }

        public function render_chart($atts, $parsed, $colors) {
            $type = strtolower($atts['type']);
            $w = max(100, min(4096, (int)$atts['width']));
            $h = max(100, min(4096, (int)$atts['height']));
            $dpi = max(1, min(3, (int)$atts['dpi']));
            $bg = $this->sanitize_hex($atts['bg']);
            $fg = $this->sanitize_hex($atts['fg']);
            $grid = $this->sanitize_hex($atts['grid']);
            $colors = $colors;
            $title = $atts['title'];
            $legend = strtolower($atts['legend']) !== 'false';
            if ($type === 'line') {
                $legend = false;
            }
            $max = isset($atts['max']) && $atts['max'] !== '' ? floatval($atts['max']) : null;
            if ($parsed['mode'] === 'empty') return;
            if (!function_exists('imagecreatetruecolor')) return;

            $img_w = $w * $dpi;
            $img_h = $h * $dpi;
            $im = imagecreatetruecolor($img_w, $img_h);
			imageantialias($im, true);
            imagesavealpha($im, true);
            imagealphablending($im, true);

            $col_bg = $this->alloc_color($im, $bg);
			if ( $bg == 'transparent') {
				imagealphablending($im, false);
				$transparency = imagecolorallocatealpha($im, 0,0,0,127);
				imagefill($im, 0, 0, $transparency);
				imagesavealpha($im, true);
			} else imagefilledrectangle($im, 0, 0, $img_w, $img_h, $col_bg);
            $col_fg = $this->alloc_color($im, $fg);
            $col_grid = $this->alloc_color($im, $grid);
            $col_white = imagecolorallocate($im, 255, 255, 255);
            $series_colors = array_map(fn($hex) => $this->alloc_color($im, $hex), $colors);

            $legend_pad = (int)round(5 * $dpi);
            $labels = [];
            if (in_array($type, ['pie', 'hbar', 'vbar', 'line'], true)) {
                $labels = array_keys($parsed['data']);
            }
            $longest_label_len = 0;
            foreach ($labels as $label) {
                $longest_label_len = max($longest_label_len, strlen($label));
            }
            $pad = (int)round(12 * $dpi);
            $title_h = $title ? (int)round(20 * $dpi) : 0;
            $legend_w = $legend ? ($this->get_text_width($longest_label_len, 3) + $this->get_legend_swatch_width() + $pad * 2) : 0;
            $plot_x = $pad;
            $plot_y = $pad + $title_h;
            $plot_w = $img_w - $pad * 2;
            if ($type === 'line') {
                $data_min = null;
                $data_max = null;
                foreach ($parsed['data'] as $k => $v) {
                    $v = (float)$v;
                    if ($data_min === null || $v < $data_min) $data_min = $v;
                    if ($data_max === null || $v > $data_max) $data_max = $v;
                }
                if ($data_min === null) {
                    $data_min = 0;
                }
                if ($data_max === null) {
                    $data_max = 0;
                }
                if ($data_max === $data_min) {
                    $data_max = $data_min + 1;
                }
                $steps = 6;
                $max_len = 0;
                for ($s = 0; $s <= $steps; $s++) {
                    $val = $data_min + ($data_max - $data_min) * ($s / $steps);
                    $label = number_format($val, 0, ',', '.');
                    $max_len = max($max_len, strlen($label));
                }
                $label_w = $this->get_text_width($max_len, 3);
                $left_pad = $label_w + (int)round(10 * $dpi);
                $plot_x += $left_pad;
                $plot_w -= $left_pad;
            }

            $plot_h = $img_h - $pad * 2 - $title_h;
            $x_label_h = (int)round(44 * $dpi);
            if (!in_array($type, ['pie', 'polar'], true)) {
                $plot_h -= $x_label_h;
            }

            if (in_array($type, ['pie'], true)) {
                if ($legend && $type !== 'line') {
                    $plot_w -= $legend_w;
                }
            } else {
                if ($legend && $type !== 'line') {
                    $plot_w -= $legend_w + $legend_pad;
                } else {
                    $plot_w = $img_w - $pad * 2;
                }
            }

            if ($title) {
                $this->draw_text($im, $title, $pad, (int)round(8 * $dpi), $col_fg, 5, true);
            }
            if ($type === 'line' && !$legend) {
                $plot_w = $img_w - $pad * 2 - 20;
            }

            if ($type === 'pie') {
                $labels = [];
                $values = [];
                $data_single = $parsed['data'];
                $labels = array_keys($data_single);
                $this->draw_pie($im, $data_single, $plot_x, $plot_y, $plot_w, $plot_h, $series_colors, $col_fg, $col_white);
                if ($legend && $type !== 'line') $this->draw_legend($im, $labels, $series_colors, $plot_x + $plot_w, $plot_y, $legend_w, $plot_h, $legend_pad);
            } elseif ($type === 'polar') {
                $labels = array_keys($parsed['data']);
                $series = [['name' => 'Series', 'points' => $parsed['data']]];
                $this->draw_polar($im, $series, $labels, $plot_x, $plot_y, $plot_w, $plot_h, $series_colors, $col_fg, $col_grid, $max, $col_white);
            } else {
                if ($type === 'line') {
                    $data_min = null;
                    $data_max = null;
                    foreach ($parsed['data'] as $k => $v) {
                        $v = (float)$v;
                        if ($data_min === null || $v < $data_min) $data_min = $v;
                        if ($data_max === null || $v > $data_max) $data_max = $v;
                    }
                    if ($data_min === null) {
                        $data_min = 0;
                    }
                    if ($data_max === null) {
                        $data_max = 0;
                    }
                    if ($data_max === $data_min) {
                        $data_max = $data_min + 1;
                    }
                    $steps = 6;
                    for ($s = 0; $s <= $steps; $s++) {
                        $val = $data_min + ($data_max - $data_min) * ($s / $steps);
                        $yy = (int)round($plot_y + $plot_h - (($val - $data_min) / ($data_max - $data_min) * $plot_h));
                        $label = number_format($val, 0, ',', '.');
                        $txt_w = $this->get_text_width(strlen($label), 3);
                        $tx = max(0, $plot_x - $txt_w - (int)round(6 * $dpi));
                        $this->draw_text($im, $label, $tx, $yy - 6, $col_fg, 3, false);
                    }
                    $this->draw_line_single($im, $parsed['data'], $plot_x, $plot_y, $plot_w, $plot_h, $series_colors[0], $col_fg, $col_grid, $max, $col_white);
                } else {
                    $this->draw_bar_single($im, $parsed['data'], $plot_x, $plot_y, $plot_w, $plot_h, $series_colors, $col_fg, $col_grid, ($type === 'hbar'), $max, $col_white);
                    if ($legend && $type !== 'line') $this->draw_legend($im, array_keys($parsed['data']), $series_colors, $plot_x + $plot_w, $plot_y, $legend_w, $plot_h, $legend_pad);
                }
            }
            
            imagepng($im);
            imagedestroy($im);
        }

        private function parse_data($raw) {
            $raw = trim((string)$raw);
            if ($raw === '') return ['mode' => 'empty'];
            $parts = array_filter(array_map('trim', explode('|', $raw)), fn($p) => $p !== '');
            $out = [];
            foreach ($parts as $p) {
                $pos = strpos($p, ':');
                $label = $pos !== false ? trim(substr($p, 0, $pos)) : trim($p);
                $val = $pos !== false ? trim(substr($p, $pos + 1)) : '0';
                $label = $label !== '' ? $label : (string)(count($out) + 1);
                $out[$label] = (float)str_replace(',', '.', $val);
            }
            return ['mode' => 'single', 'data' => $out];
        }

        private function build_table_html($parsed, $atts, $hexColors) {
            $decimals = max(0, (int)$atts['decimals']);
            $caption = trim((string)$atts['table_caption']);
            $class = esc_attr($atts['table_class']);
            $style = esc_attr($atts['table_style']);
            $td_l = 'style="text-align:left"';
            $td_r = 'style="text-align:right"';
            $swatch = function ($hex) {
                $hex = esc_attr($hex);
                return '<span style="display:inline-block;width:10px;height:10px;background:' . $hex . ';border:1px solid #999;margin-right:6px;vertical-align:middle"></span>';
            };
            $maxValue = 0;
            $sumValue = 0;
            $avgValue = 0;
            if (!empty($parsed['data'])) {
                $maxValue = max(array_values($parsed['data']));
                $sumValue = array_sum($parsed['data']);
                $numrows = count($parsed['data']);
                $avgValue = $numrows > 0 ? $sumValue / $numrows : 0;
            } else {
                $numrows = 0;
            }
            $html = '<table class="' . $class . '" style="' . $style . '">';
            if ($caption !== '') {
                $html .= '<caption>' . esc_html($caption) . '</caption>';
            }
            $html .= '<thead><tr><th>Label</th><th>Wert</th><th>Prozent</th><th>Graph</th></tr></thead><tbody>';
            foreach ($parsed['data'] as $label => $v) {
                $percentage = ($maxValue > 0) ? ($v / $maxValue * 100) : 0;
                $tablabel = esc_html($label);
                if ((bool)preg_match('/^(\d{4}-\d{2}-\d{2}|\d{2}-\d{2}-\d{2}|\d{2}\.\d{2}\.\d{4})(?:\s+\d{2}:\d{2}(?::\d{2})?)?$/x', $tablabel)) {
                    $tablabel = colordatebox(strtotime($tablabel), NULL, NULL, 1);
                }
                $html .= '<tr><td ' . $td_l . '>' . $tablabel . '</td>';
                $html .= '<td ' . $td_r . '>' . $this->fmt_num((float)$v, $decimals, NULL) . '</td>';
                $html .= '<td ' . $td_r . '>' . number_format($percentage, 2, ',', '.') . '%</td>';
                $html .= '<td width="65%" ' . $td_r . '>
                    <progress style="width:100%" max="100" value="' . number_format($percentage, 0, ',', '.') . '"></progress>
                    </td></tr>';
            }
            $html .= '</tbody><tfoot><tr><td colspan="4">' .
                $numrows . ' Werte | ' . $maxValue . ' max &nbsp; ∑ ' .
                $this->fmt_num($sumValue, $decimals, NULL) . ' &nbsp; Ø ' .
                $this->fmt_num($avgValue, 2, NULL) . ' </td></tr></tfoot></table>';
            return $html;
        }

        private function fmt_num($v, $decimals, $percent) {
            if ($percent) {
                return number_format($v, max(0, $decimals)) . '%';
            }
            return number_format($v, max(0, $decimals), ',', '.');
        }

        private function palette_mono($base_hex, $n) {
            $base_hex = $this->sanitize_hex($base_hex);
            list($r, $g, $b) = $this->hex_to_rgb($base_hex);
            list($h, $s, $v) = $this->rgb_to_hsv($r, $g, $b);
            if ($s < 0.25) $s = 0.35;
            if ($v < 0.35) $v = 0.45;
            $out = [];
            $steps = max(1, (int)$n);
            $variants = [
                [-0.10, +0.10], [-0.05, +0.05], [0.0, 0.0], [+0.05, -0.05], [+0.10, -0.10],
                [-0.15, +0.15], [+0.15, -0.15]
            ];
            $i = 0;
            while (count($out) < $steps) {
                $sv = $variants[$i % count($variants)];
                $ss = max(0.20, min(0.98, $s + $sv[0]));
                $vv = max(0.20, min(0.98, $v + $sv[1]));
                list($rr, $gg, $bb) = $this->hsv_to_rgb($h, $ss, $vv);
                $out[] = $this->rgb_to_hex($rr, $gg, $bb);
                $i++;
            }
            return $out;
        }

        private function compute_needed_colors($parsed, $type) {
            if (($type === 'pie') || ($type === 'hbar') || ($type === 'vbar')) {
                return max(1, count($parsed['data'] ?? []));
            }
            return 8;
        }

        private function hex_to_rgb($hex) {
            $hex = ltrim($hex, '#');
            return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
        }

        private function rgb_to_hex($r, $g, $b) {
            $r = max(0, min(255, (int)$r));
            $g = max(0, min(255, (int)$g));
            $b = max(0, min(255, (int)$b));
            return sprintf('#%02X%02X%02X', $r, $g, $b);
        }

        private function rgb_to_hsv($r, $g, $b) {
            $r /= 255;
            $g /= 255;
            $b /= 255;
            $max = max($r, $g, $b);
            $min = min($r, $g, $b);
            $d = $max - $min;
            $h = 0.0;
            if ($d == 0) {
                $h = 0;
            } else if ($max == $r) {
                $h = fmod((($g - $b) / $d), 6.0);
            } else if ($max == $g) {
                $h = (($b - $r) / $d) + 2.0;
            } else {
                $h = (($r - $g) / $d) + 4.0;
            }
            $h *= 60.0;
            if ($h < 0) $h += 360.0;
            $s = $max == 0 ? 0.0 : $d / $max;
            $v = $max;
            return [$h, $s, $v];
        }

        private function hsv_to_rgb($h, $s, $v) {
            $h = fmod($h, 360.0);
            if ($h < 0) $h += 360.0;
            $c = $v * $s;
            $x = $c * (1 - abs(fmod($h / 60.0, 2) - 1));
            $m = $v - $c;
            $r = $g = $b = 0.0;
            if ($h < 60) {
                $r = $c;
                $g = $x;
                $b = 0;
            } elseif ($h < 120) {
                $r = $x;
                $g = $c;
                $b = 0;
            } elseif ($h < 180) {
                $r = 0;
                $g = $c;
                $b = $x;
            } elseif ($h < 240) {
                $r = 0;
                $g = $x;
                $b = $c;
            } elseif ($h < 300) {
                $r = $x;
                $g = 0;
                $b = $c;
            } else {
                $r = $c;
                $g = 0;
                $b = $x;
            }
            return [($r + $m) * 255, ($g + $m) * 255, ($b + $m) * 255];
        }

        private function palette_accent($base_hex, $n) {
            $base_hex = $this->sanitize_hex($base_hex);
            list($r, $g, $b) = $this->hex_to_rgb($base_hex);
            list($h, $s, $v) = $this->rgb_to_hsv($r, $g, $b);
            if ($s < 0.25) $s = 0.35;
            if ($v > 0.85) $v = 0.75;
            $out = [];
            $steps = max(1, (int)$n);
            $v_step = (0.95 - $v) / $steps;
            for ($i = 0; $i < $steps; $i++) {
                $vv = min(0.95, $v + ($i + 1) * $v_step);
                list($rr, $gg, $bb) = $this->hsv_to_rgb($h, $s, $vv);
                $out[] = $this->rgb_to_hex($rr, $gg, $bb);
            }
            return $out;
        }

        private function draw_polar($im, $series, $categories, $x, $y, $w, $h, $series_colors, $fg, $grid, $max = null, $white_color = null) {
            $n = max(3, count($categories));
            $vals = [];
            foreach ($series as $s) {
                foreach ($categories as $c) {
                    $vals[] = (float)($s['points'][$c] ?? 0);
                }
            }
            if (empty($vals)) return;
            $maxVal = ($max !== null) ? $max : max(1, max($vals));
            $diam = min($w, $h) - 10;
            $cx = (int)($x + $w / 2);
            $cy = (int)($y + $h / 2);
            $r = (int)($diam / 2);
            $rings = 5;
            for ($i = 1; $i <= $rings; $i++) {
                $rr = (int)round($r * $i / $rings);
                imageellipse($im, $cx, $cy, $rr * 2, $rr * 2, $grid);
            }
            for ($i = 0; $i < $n; $i++) {
                $ang = (2 * M_PI * $i / $n) - M_PI / 2;
                $tx = (int)round($cx + cos($ang) * $r);
                $ty = (int)round($cy + sin($ang) * $r);
                imageline($im, $cx, $cy, $tx, $ty, $grid);
            }
            for ($i = 0; $i < $n; $i++) {
                $ang = (2 * M_PI * $i / $n) - M_PI / 2;
                $tx = (int)round($cx + cos($ang) * ($r + 10));
                $ty = (int)round($cy + sin($ang) * ($r + 10));
                $this->draw_text($im, (string)$categories[$i], $tx - 10, $ty - 6, $fg, 2);
            }
            imagesetthickness($im, 2);
            foreach ($series as $si => $s) {
                $col = $series_colors[$si % count($series_colors)];
                $pts = [];
                for ($i = 0; $i < $n; $i++) {
                    $v = (float)($s['points'][$categories[$i]] ?? 0);
                    $ratio = max(0, $v / $maxVal);
                    $ang = (2 * M_PI * $i / $n) - M_PI / 2;
                    $px = (int)round($cx + cos($ang) * $r * $ratio);
                    $py = (int)round($cy + sin($ang) * $r * $ratio);
                    $pts[] = [$px, $py];
                    $this->draw_text_with_bg($im, number_format($v, 0, ',', '.'), (int)($px - 10), (int)($py - 10), $fg, $white_color);
                }
                for ($i = 0; $i < $n; $i++) {
                    $j = ($i + 1) % $n;
                    imageline($im, $pts[$i][0], $pts[$i][1], $pts[$j][0], $pts[$j][1], $col);
                    imagefilledellipse($im, $pts[$i][0], $pts[$i][1], 6, 6, $col);
                }
            }
        }

        private function sanitize_hex($hex) {
            $hex = trim((string)$hex);
            if (!preg_match('/^#?[0-9A-Fa-f]{6}$/', $hex)) return '#000000';
            return '#' . ltrim($hex, '#');
        }

        private function parse_colors($csv) {
            $arr = array_filter(array_map('trim', explode(',', (string)$csv)));
            $out = [];
            foreach ($arr as $h) {
                $out[] = $this->sanitize_hex($h);
            }
            return $out;
        }

        private function alloc_color($im, $hex) {
            $hex = ltrim($hex, '#');
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
            return imagecolorallocate($im, $r, $g, $b);
        }

        private function get_text_width($len, $size) {
			$font = min(5, max(1, (int)$size));
			$fw = function_exists('imagefontwidth') ? (int)imagefontwidth($font) : 6;
			return ((int)$len) * $fw;
		}

        private function get_legend_swatch_width() {
            return 12 + 8;
        }

        private function draw_text($im, $text, $x, $y, $color, $size = 4, $bold = false) {
            $this->gd_imagestring($im, min(5, max(1, $size)), (int)$x, (int)$y, (string)$text, $color);
            if ($bold) $this->gd_imagestring($im, min(5, max(1, $size)), (int)$x + 1, (int)$y, (string)$text, $color);
        }

        private function draw_text_with_bg($im, $text, $x, $y, $fg_color, $bg_color, $size = 2) {
            $char_w = 6;
            $char_h = 13;
            $text_w = strlen((string)$text) * $char_w;
            $text_h = $char_h;
            $pad = 2;
            imagefilledrectangle($im, (int)$x - $pad, (int)$y - $pad, (int)($x + $text_w + $pad), (int)($y + $text_h + $pad), $bg_color);
            $this->gd_imagestring($im, min(5, max(1, $size)), (int)$x, (int)$y, (string)$text, $fg_color);
        }

        private function draw_legend($im, $labels, $colors, $x, $y, $w, $h, $legend_pad) {
            $pad = 8;
            $box = 12;
            $line_h = $box + 8;
            $yy = (int)$y;
            $font_w = 6;
            $font_size = 3;
            foreach (array_values($labels) as $idx => $label) {
                if ($yy > $y + $h - $line_h) break;
                $swatch_x = $x + $legend_pad;
                imagefilledrectangle($im, (int)$swatch_x, $yy, (int)($swatch_x + $box), $yy + $box, $colors[$idx % count($colors)]);
                imagerectangle($im, (int)$swatch_x, $yy, (int)($swatch_x + $box), $yy + $box, imagecolorallocate($im, 0, 0, 0));
                $text_x = $swatch_x + $box + $pad;
                $this->draw_text($im, (string)$label, $text_x, $yy, imagecolorallocate($im, 30, 30, 30), $font_size);
                $yy += $line_h;
            }
        }

        private function draw_grid($im, $x, $y, $w, $h, $color, $countX = 5, $countY = 5) {
            for ($i = 0; $i <= $countX; $i++) {
                $xx = (int)round($x + $i * $w / $countX);
                imageline($im, $xx, $y, $xx, $y + $h, $color);
            }
            for ($j = 0; $j <= $countY; $j++) {
                $yy = (int)round($y + $j * $h / $countY);
                imageline($im, $x, $yy, $x + $w, $yy, $color);
            }
        }

        private function draw_axes($im, $x, $y, $w, $h, $color) {
            imageline($im, $x, $y + $h, $x + $w, $y + $h, $color);
            imageline($im, $x, $y, $x, $y + $h, $color);
        }

        private function draw_line_single($im, $data, $x, $y, $w, $h, $series_color, $fg, $grid, $max = null, $white_color = null) {
            $values = array_values($data);
            $labels = array_keys($data);
            $n = count($values);
            $right_inset = 5;
            $x_eff_w = max(1, $w - $right_inset);
            $x_step = ($n > 1) ? ($x_eff_w / ($n - 1)) : 0;
            $max_x = (int)($x + $x_eff_w);
            $maxVal = ($max !== null) ? $max : max(1, max($values));
            $minVal = min(0, min($values));
            $this->draw_grid($im, $x, $y, $w, $h, $grid, min(10, max(3, $n - 1)), 5);
            $this->draw_axes($im, $x, $y, $w, $h, $fg);
            imagesetthickness($im, 2);
            for ($i = 0; $i < $n - 1; $i++) {
                $x1 = min((int)round($x + $x_step * $i), $max_x);
                $x2 = min((int)round($x + $x_step * ($i + 1)), $max_x);
                $y1 = (int)round($y + $h - ($values[$i] - $minVal) / ($maxVal - $minVal) * $h);
                $y2 = (int)round($y + $h - ($values[$i + 1] - $minVal) / ($maxVal - $minVal) * $h);
                imageline($im, $x1, $y1, $x2, $y2, $series_color);
                imagefilledellipse($im, $x1, $y1, 6, 6, $series_color);
            }
            if ($n > 0) {
                $xl = $max_x;
                $yl = (int)round($y + $h - ($values[$n - 1] - $minVal) / ($maxVal - $minVal) * $h);
                imagefilledellipse($im, $xl, $yl, 6, 6, $series_color);
            }
            $step = max(1, (int)floor($n / 8));
            for ($i = 0; $i < $n; $i += $step) {
                $xx = min((int)round($x + $x_step * $i), $max_x);
                $this->draw_text_with_bg($im, (string)$labels[$i], max($x, $xx - 20), $y + $h + 10, $fg, $white_color, 3);
                if (($maxVal - $minVal) != 0) {
                    $val = isset($values[$i]) ? $values[$i] : 0;
                    $yy = (int)round($y + $h - (($val - $minVal) / ($maxVal - $minVal) * $h));
                    $val_label = number_format($val, 0, ',', '.');
                    $this->draw_text_with_bg($im, $val_label, max($x, $xx - 10), $yy - 18, $fg, $white_color, 2);
                } else {
                    $val = isset($values[$i]) ? $values[$i] : 0;
                    $yy = (int)round($y + $h / 2);
                    $val_label = number_format($val, 0, ',', '.');
                    $this->draw_text_with_bg($im, $val_label, max($x, $xx - 10), $yy - 18, $fg, $white_color, 2);
                }
            }
        }

        private function draw_bar_single($im, $data, $x, $y, $w, $h, $colors, $fg, $grid, $horizontal = false, $max = null, $white_color = null) {
            $values = array_values($data);
            $labels = array_keys($data);
            $n = count($values);
            $maxVal = ($max !== null) ? $max : max(1, max($values));
            $this->draw_grid($im, $x, $y, $w, $h, $grid, 5, 5);
            $this->draw_axes($im, $x, $y, $w, $h, $fg);
            $gap = 8;
            if ($horizontal) {
                $bar_h = max(6, (int)floor(($h - $gap * ($n + 1)) / $n));
                for ($i = 0; $i < $n; $i++) {
                    $yy = (int)($y + $gap + $i * ($bar_h + $gap));
                    $len = $values[$i] / $maxVal * $w;
                    $col = $colors[$i % count($colors)];
                    imagefilledrectangle($im, $x + 1, $yy, (int)($x + $len), $yy + $bar_h, $col);
                    imagerectangle($im, $x, $yy, (int)($x + $len), $yy + $bar_h, $fg);
                    $this->draw_text_with_bg($im, (string)$labels[$i], $x + 5, $yy + (int)($bar_h / 2) - 6, $fg, $white_color, 3);
                    $this->draw_text_with_bg($im, (string)$values[$i], (int)($x + $len + 4), $yy + (int)($bar_h / 2) - 6, $fg, $white_color, 2);
                }
            } else {
                $bar_w = max(6, (int)floor(($w - $gap * ($n + 1)) / $n));
                for ($i = 0; $i < $n; $i++) {
                    $xx = (int)($x + $gap + $i * ($bar_w + $gap));
                    $h_px = $values[$i] / $maxVal * $h;
                    $col = $colors[$i % count($colors)];
                    imagefilledrectangle($im, $xx, (int)($y + $h - $h_px), $xx + $bar_w, $y + $h - 1, $col);
                    imagerectangle($im, $xx, (int)($y + $h - $h_px), $xx + $bar_w, $y + $h - 1, $fg);
                    $this->draw_wrapped_centered_label($im, (string)$labels[$i], (int)($xx + $bar_w / 2), $y + $h + 10, $bar_w, $fg, $white_color, 3);
                    $this->draw_text_with_bg($im, (string)$values[$i], $xx, (int)($y + $h - $h_px) - 14, $fg, $white_color, 2);
                }
            }
        }

		private function draw_pie($im, $data_single, $x, $y, $w, $h, $series_colors, $col_fg, $col_white) {
			// Guard: avoid division by zero when total is 0
			if (isset($data_values) && is_array($data_values)) {
				$total_value = array_sum($data_values);
				if ($total_value <= 0) { return; }
			}

            $data_values = is_array($data_single) ? array_values($data_single) : [];
            $data_labels = is_array($data_single) ? array_keys($data_single) : [];
            
            // --- Normalisierung & Sichtbarkeit ---
            $orig_values = $data_values; // Originalwerte für Labelanzeige behalten
            if (is_array($data_values) && count($data_values) > 1) {
                $n = count($data_values);

                // 1) Dominante Werte (>2× größte andere) für Darstellung kappen
                $outliers = [];
                $max_other_nonzero = 0;
                for ($i = 0; $i < $n; $i++) {
                    $max_other = 0;
                    for ($j = 0; $j < $n; $j++) {
                        if ($j === $i) continue;
                        if ($data_values[$j] > $max_other) $max_other = $data_values[$j];
                    }
                    if ($max_other > 0) {
                        if ($data_values[$i] > 2 * $max_other) {
                            $outliers[$i] = true;
                            if (isset($data_labels[$i]) && is_string($data_labels[$i]) && strpos($data_labels[$i], 'viel größer') === false) {
                                $data_labels[$i] .= ' (viel größer)';
                            }
                        }
                        if ($max_other > $max_other_nonzero) $max_other_nonzero = $max_other;
                    }
                }
                if (!empty($outliers) && $max_other_nonzero > 0) {
                    $cap = 2 * $max_other_nonzero;
                    for ($i = 0; $i < $n; $i++) {
                        if (!empty($outliers[$i]) && $data_values[$i] > $cap) {
                            $data_values[$i] = $cap; // nur Darstellungskap
                        }
                    }
                }

                // 2) Mindestwinkel für sehr kleine Segmente (z. B. 3°)
                $MIN_DEG = 3.0;
                $sum_val = array_sum($data_values);
                if ($sum_val > 0) {
                    $deg = [];
                    $extra_needed = 0.0;
                    $eligible_reduce = [];
                    for ($i = 0; $i < $n; $i++) {
                        $deg[$i] = ($data_values[$i] / $sum_val) * 360.0;
                        if ($data_values[$i] > 0 && $deg[$i] < $MIN_DEG) {
                            $extra_needed += ($MIN_DEG - $deg[$i]);
                            $deg[$i] = $MIN_DEG;
                        } else {
                            $eligible_reduce[$i] = true;
                        }
                    }
                    if ($extra_needed > 0.0) {
                        // Reduziere von großen Segmenten proportional
                        $total_reduce_pool = 0.0;
                        for ($i = 0; $i < $n; $i++) {
                            if (!isset($eligible_reduce[$i])) continue;
                            if ($deg[$i] > $MIN_DEG) {
                                $total_reduce_pool += ($deg[$i] - $MIN_DEG);
                            }
                        }
                        if ($total_reduce_pool > 0.0) {
                            $scale = $extra_needed / $total_reduce_pool;
                            for ($i = 0; $i < $n; $i++) {
                                if (!isset($eligible_reduce[$i])) continue;
                                if ($deg[$i] > $MIN_DEG) {
                                    $deg[$i] -= ($deg[$i] - $MIN_DEG) * $scale;
                                }
                            }
                        }
                        // Schreibe Winkel zurück in Werte (verhältnistreu zu ursprünglicher Summe)
                        $new_vals = [];
                        for ($i = 0; $i < $n; $i++) {
                            $new_vals[$i] = ($deg[$i] / 360.0) * $sum_val;
                        }
                        $data_values = $new_vals;
                    }
                }
            }
            // --- Ende Normalisierung ---

			$total_value = array_sum($data_values);
			if ($total_value == 0) {
				$total_value = 1;
			}
			$center_x = $x + $w / 2;
			$center_y = $y + $h / 2;
			$radius_x = min($w, $h) * 0.7;
			$radius_y = $radius_x * 0.7;
			$start_angle = 0;
			$queued_labels = [];
			$queued_labels = [];
			$depth = 10;
			foreach ($data_values as $index => $value) {
				$sweep_angle = ($value / $total_value) * 360;
				$darker_color = $this->alloc_color($im, '#444444');
				for ($i = $depth; $i > 0; $i--) {
					imagefilledarc($im, (int)$center_x, (int)($center_y + $i), (int)($radius_x * 2), (int)($radius_y * 2), (int)$start_angle, (int)($start_angle + $sweep_angle), $darker_color, IMG_ARC_PIE);
				}
				$start_angle += $sweep_angle;
			}
			$start_angle = 0;
			$used_label_rects = [];
			$font_for_labels = 2;
			$fw_lbl = function_exists('imagefontwidth') ? imagefontwidth($font_for_labels) : 6;
			$fh_lbl = function_exists('imagefontheight') ? imagefontheight($font_for_labels) : 13;
			$label_angle_step = 6; // Grad pro Versuch
			$min_sep_px = 4;       // Mindestabstand in Pixeln zwischen Label-Rechtecken

            foreach ($data_values as $index => $value) {
                $sweep_angle = ($value / $total_value) * 360;
                $color = $series_colors[$index % count($series_colors)];
                imagefilledarc($im, (int)$center_x, (int)$center_y, (int)($radius_x * 2), (int)($radius_y * 2), (int)$start_angle, (int)($start_angle + $sweep_angle), $color, IMG_ARC_PIE);
                
                // Berechne Label-Text: Prozent + Originalwert
                $pct = ($total_value > 0) ? round(($value / $total_value) * 100) : 0;
                $orig_val = isset($orig_values[$index]) ? $orig_values[$index] : $value;
                if (is_numeric($orig_val) && floor($orig_val) == $orig_val) {
                    $value_str = number_format((int)$orig_val, 0, ',', '.');
                } else {
                    $value_str = rtrim(rtrim(number_format((float)$orig_val, 2, ',', '.'), '0'), ',');
                }
                $label_text = $data_labels[$index] . ' (' . $pct . '% – ' . $value_str . ')';

                // Zielwinkel (Mitte des Segments), dann Konflikte mit existierenden Label-Rechtecken vermeiden
                $label_deg = $start_angle + ($sweep_angle / 2.0);
                $mid_angle = deg2rad($label_deg);
                $radius_factor = ($sweep_angle < 6) ? 0.9 : 0.75;
                $attempt = 0;
                $max_attempts = 60;
                do {
                    $lx = (int)round($center_x + ($radius_x * $radius_factor) * cos($mid_angle));
                    $ly = (int)round($center_y + ($radius_y * $radius_factor) * sin($mid_angle));

                    // Näherungsweise Textbreite/-höhe
                    $calc = method_exists($this, 'gd_utf8') ? $this->gd_utf8($label_text) : $label_text;
                    $tw = strlen($calc) * $fw_lbl;
                    $th = $fh_lbl;

                    // draw_text_with_bg zeichnet von (x, y) aus mit kleinem Padding
                    $rx1 = $lx - 20 - 2;
                    $ry1 = $ly -  6 - 2;
                    $rx2 = $rx1 + $tw + 4;
                    $ry2 = $ry1 + $th + 4;
                    $rect = [$rx1, $ry1, $rx2, $ry2];

                    $ok = true;
                    foreach ($used_label_rects as $ur) {
                        if (!((($rect[2]) < $ur[0]) || (($ur[2]) < $rect[0]) || (($rect[3]) < $ur[1]) || (($ur[3]) < $rect[1]))) { $ok = false; break; }
                    }
                    if ($ok) break;

                    // Korrektur: alternierend Winkel verschieben und Radius leicht erhöhen
                    $attempt++;
                    $delta_deg = ($attempt % 2 ? +1 : -1) * $label_angle_step * intval(($attempt) / 2) + 1;
                    $label_deg += $delta_deg;
                    $mid_angle = deg2rad($label_deg);
                    $radius_factor = min(0.98, $radius_factor + 0.02);
                } while ($attempt < $max_attempts);

                // --- Clamp Label-Rechteck in Bildgrenzen ---
				$minX = $x + 2; $minY = $y + 2; $maxX = $x + $w - 2; $maxY = $y + $h - 2;
				$dx = 0; $dy = 0;
				if ($rx1 < $minX) { $dx += ($minX - $rx1); }
				if ($rx2 > $maxX) { $dx -= ($rx2 - $maxX); }
				if ($ry1 < $minY) { $dy += ($minY - $ry1); }
				if ($ry2 > $maxY) { $dy -= ($ry2 - $maxY); }
				if ($dx != 0 || $dy != 0) {
					$lx += $dx; $ly += $dy;
					$rx1 = $lx - 20 - 2; $ry1 = $ly - 6 - 2;
					$rx2 = $rx1 + $tw + 4; $ry2 = $ry1 + $th + 4;
					$rect = [$rx1, $ry1, $rx2, $ry2];
				}
				// --- Ende Clamp ---

				$used_label_rects[] = $rect;
                $queued_labels[] = [$label_text, $lx - 20, $ly - 6];
                $start_angle += $sweep_angle;

            }

            // Draw queued labels on top (final pass)
            if (isset($queued_labels) && is_array($queued_labels)) {
                foreach ($queued_labels as $lbl) {
                    $this->draw_text_with_bg($im, $lbl[0], $lbl[1], $lbl[2], $col_fg, $col_white, 2);
                }
            }
		}


		/**
		 * Wrap text into multiple lines for vbar x-axis labels.  Bitmap metrics: 6x13 per char for GD font size 3.
		 */
		private function gd_wrap_label_lines($text, $max_px, $font_w = 6) {
		$max_px = (int)$max_px; $font_w = max(1, (int)$font_w);

		$tmp = $this->gd_utf8((string)$text);
		$s = is_string($tmp) ? $tmp : (string)$text;

		$words = preg_split('/\s+/u', $s, -1, PREG_SPLIT_NO_EMPTY);
		if (!is_array($words)) { $words = strlen($s) ? [$s] : []; }

		$lines = [];
		$current_line = '';

		foreach ($words as $word) {
			$word_width = $this->gd_text_width($word, $font_w);

			if ($word_width > $max_px) {
				if ($current_line !== '') { $lines[] = $current_line; $current_line = ''; }
				$chars = preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY);
				if (!is_array($chars)) { $chars = str_split((string)$word); }
				$temp_line = '';
				foreach ($chars as $char) {
					if ($this->gd_text_width($temp_line . $char, $font_w) <= $max_px) {
						$temp_line .= $char;
					} else {
						if ($temp_line !== '') { $lines[] = $temp_line; }
						$temp_line = $char;
					}
				}
				if ($temp_line !== '') { $lines[] = $temp_line; }
			} else {
				$candidate = ($current_line === '') ? $word : $current_line . ' ' . $word;
				if ($this->gd_text_width($candidate, $font_w) <= $max_px) {
					$current_line = $candidate;
				} else {
					if ($current_line !== '') { $lines[] = $current_line; }
					$current_line = $word;
				}
			}
		}
		if ($current_line !== '') { $lines[] = $current_line; }
		return $lines;
		}


		private function gd_text_width($text, $font_w) {
			$font_w = max(1, (int)$font_w);
			$s = $this->gd_utf8((string)$text);
			if (!is_string($s)) { $s = (string)$text; }
			return strlen($s) * $font_w;
		}


    /**
     * Draw wrapped, centered label below a vbar.
     */
    private function draw_wrapped_centered_label($im, $text, $center_x, $top_y, $max_width, $fg_color, $bg_color, $font_size = 3, $line_spacing = 2) {
		// Echte GD-Fontmaße
		$font = min(5, max(1, (int)$font_size));
		$font_w = function_exists('imagefontwidth')  ? (int)imagefontwidth($font)  : 6;
		$font_h = function_exists('imagefontheight') ? (int)imagefontheight($font) : 13;

		// Schmalere Wrap-Breite: 2px Padding je Seite + 1 Zeichen Sicherheitsabstand
		$wrap_px = max($font_w, (int)floor($max_width - 4 - $font_w));

		// Umbrechen mit realer Zeichenbreite
		$lines = $this->gd_wrap_label_lines((string)$text, $wrap_px, $font_w);
		if (!is_array($lines) || !$lines) return;

		// Zeichnen (zentriert)
		$y = (int)$top_y;
		foreach ($lines as $ln) {
			$calc = method_exists($this, 'gd_utf8') ? (string)$this->gd_utf8($ln) : (string)$ln;
			$w = (int)(strlen($calc) * $font_w);
			$x = (int)round($center_x - $w / 2);

			if (is_int($bg_color)) {
				imagefilledrectangle($im, $x - 2, $y - 2, $x + $w + 2, $y + $font_h + 2, $bg_color);
			}
			if (method_exists($this, 'gd_imagestring')) {
				$this->gd_imagestring($im, $font, $x, $y, $ln, $fg_color);
			} else {
				imagestring($im, $font, $x, $y, $ln, $fg_color);
			}
			$y += $font_h + $line_spacing;
		}
	}
		
        private function gd_utf8($s) {
            if ($s === null) return '';
            if (function_exists('mb_detect_encoding') && mb_detect_encoding($s, 'UTF-8', true)) {
                if (function_exists('iconv')) {
                    $converted = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $s);
                    if ($converted !== false) return $converted;
                }
                return utf8_decode($s);
            }
            return $s;
        }

        private function gd_imagestring($im, $font, $x, $y, $string, $color) {
            return call_user_func('imagestring', $im, $font, $x, $y, $this->gd_utf8($string), $color);
        }

        private function gd_imagestringup($im, $font, $x, $y, $string, $color) {
            return call_user_func('imagestringup', $im, $font, $x, $y, $this->gd_utf8($string), $color);
        }

        private function gd_imagechar($im, $font, $x, $y, $c, $color) {
            $s = $this->gd_utf8($c);
            $byte = $s !== '' ? $s[0] : '';
            return call_user_func('imagechar', $im, $font, $x, $y, $byte, $color);
        }

        private function gd_imagecharup($im, $font, $x, $y, $c, $color) {
            $s = $this->gd_utf8($c);
            $byte = $s !== '' ? $s[0] : '';
            return call_user_func('imagecharup', $im, $font, $x, $y, $byte, $color);
        }
    }
    new WPGDCharts();
}



// -------------------------- Jetzt den QRCode Generator als Klasse -----------------------------------------------

if ( defined( 'DOQRCODE_V' ) ) { return; }
define( 'DOQRCODE_V', '1.2.1' ) ;

! defined( 'DOQRCODE_DIR' ) && define( 'DOQRCODE_DIR', dirname( __FILE__ ) . '/' ) ;// Full absolute path '/usr/local/***/wp-content/plugins/doqrcode/' or MU

/**
 * Core class
 */
defined( 'WPINC' ) || exit ;

class DoQRCode {
	private static $_instance ;

	/** * Init */
	private function __construct() {
		add_shortcode( 'qrcode', array( $this, 'shortcode_handler' ) ) ;
	}

	/** * Shortcode handler */
	public function shortcode_handler( $atts, $content ) {
		require_once DOQRCODE_DIR . 'barcode.php' ;
		$symbology = 'qr-m';
		if ( ! empty( $atts[ 'type' ] ) ) {
			$symbology = $atts[ 'type' ] ;
		}
		if ( (preg_match('/\bqr\b/', $symbology)) ) { $pb = 0;$th = 0; $size = 3; } else { $pb = 15; $th = 15;$size = 1; }
		if ( ! empty( $atts[ 'size' ] ) ) {
			$size = (int) $atts[ 'size' ] ;
		}
		$margin = 3;
		if ( ! empty( $atts[ 'margin' ] ) ) {
			$margin = (int) $atts[ 'margin' ] ;
		}
		if ( ! empty( $atts[ 'text' ] ) ) {
			$textinput = html_entity_decode($atts[ 'text' ]) ;
		} else { $textinput ='no data'; }
		$options =['sf'=>$size,'p'=>$margin,'pb'=>$pb,'th'=>$th];
		$generator = new barcode_generator();
		/* Generate SVG markup. */
		$svg = $generator->render_svg($symbology, $textinput, $options);
		return $svg;
	}

	/** * Get the current instance object. */
	public static function get_instance() {
		if ( ! isset( self::$_instance ) ) {
			self::$_instance = new self() ;
		}
		return self::$_instance ;
	}
}
$__core = DoQRCode::get_instance() ;


// ==================== Girocode erstellen ======================================

function formatIBAN($iban) {
  $iban = preg_replace('/\040/', '', $iban);
  $iban_formated = '';
  for ($i = 0; $i < ceil(strlen($iban) / 4); $i ++) $iban_formated .= substr($iban, $i * 4, 4).' ';
  return trim($iban_formated);
}

function makeiban() {
	global $wp;
	$out ='';
	
	if (isset($_POST["berechnen"])) {
		$out .= '<a href="'.esc_url(home_url(add_query_arg(array(), $wp->request))).'">Neue IBAN erzeugen</a> &nbsp; ';
		if (isset($_POST["blz"])) $blz = sanitize_text_field($_POST["blz"]);
		if (isset($_POST["kontonr"])) $kontonr = sanitize_text_field($_POST["kontonr"]);
		if (isset($_POST["bic"])) $bic = sanitize_text_field($_POST["bic"]);
		if (isset($_POST["iban"])) $iban = sanitize_text_field($_POST["iban"]);
		if ( empty($iban) && !empty($blz) && !empty($kontonr) ) {
			$blz8 = str_pad ( $blz, 8, "0", STR_PAD_RIGHT);
			$kontonr10 = str_pad ( $kontonr, 10, "0", STR_PAD_LEFT);
			$bban = $blz8 . $kontonr10;
			$pruefsumme = $bban . "131400";
			$modulo = (bcmod($pruefsumme,"97"));
			$pruefziffer =str_pad ( 98 - $modulo, 2, "0",STR_PAD_LEFT);
			$iban = "DE" . $pruefziffer . $bban;
		}
		$out .= '<p>IBAN <b>'.$iban.'</b> &nbsp; <code>'.formatIBAN($iban).'</code> ';
		if (checkIBAN($iban)) $out .= 'ist gültig'; else $out .= '<span style="color:tomato">ist ungültig</span>';
		if (!empty($bic)) {
			$out .= '<br>BIC <code>'.$bic.'</code> ';
			if (swift_validate($bic)) $out .= 'ist gültig'; else $out .= '<span style="color:tomato">ist ungültig</span>';
		}	
		return $out.'</p>';
	} else {
		$out .= '<p><form method="post"><table>';
		$out .= '<tr><td>BLZ</td><td><input name="blz" id="blz" type="int" size="8">';
		$out .= '</td><td>Kontonr</td><td><input name="kontonr" id="kontonr" type="int" size="10"></td></tr>';
		$out .= '<tr><td>BIC/SWIFT</td><td><input name="bic" id="bic" type="text" size="10">';
		$out .= '</td><td>IBAN</td><td><input name="iban" id="iban" type="text" size="22"></td></tr>';
		$out .='<tr><td colspan=4><input style="width:100%" name="berechnen" id="berechnen" type="submit" class="submit" value="IBAN (DE) berechnen oder IBAN und BIC prüfen"></td></tr>';
		$out .='</table></form></p>';
		return $out;
	}	
}

function swift_validate($swift) {
	if(!preg_match('/^([a-zA-Z]){4}([a-zA-Z]){2}([0-9a-zA-Z]){2}([0-9a-zA-Z]{3})?$/', $swift,$matches)) {
		return false;
	} else {
		return true;
	}
}

function checkIBAN($iban) {
    if(strlen($iban) < 5) return false;
    $iban = strtolower(str_replace(' ','',$iban));
    $Countries = array('al'=>28,'ad'=>24,'at'=>20,'az'=>28,'bh'=>22,'be'=>16,'ba'=>20,'br'=>29,'bg'=>22,'cr'=>21,'hr'=>21,'cy'=>28,'cz'=>24,'dk'=>18,'do'=>28,'ee'=>20,'fo'=>18,'fi'=>18,'fr'=>27,'ge'=>22,'de'=>22,'gi'=>23,'gr'=>27,'gl'=>18,'gt'=>28,'hu'=>28,'is'=>26,'ie'=>22,'il'=>23,'it'=>27,'jo'=>30,'kz'=>20,'kw'=>30,'lv'=>21,'lb'=>28,'li'=>21,'lt'=>20,'lu'=>20,'mk'=>19,'mt'=>31,'mr'=>27,'mu'=>30,'mc'=>27,'md'=>24,'me'=>22,'nl'=>18,'no'=>15,'pk'=>24,'ps'=>29,'pl'=>28,'pt'=>25,'qa'=>29,'ro'=>24,'sm'=>27,'sa'=>24,'rs'=>22,'sk'=>24,'si'=>19,'es'=>24,'se'=>24,'ch'=>21,'tn'=>24,'tr'=>26,'ae'=>23,'gb'=>22,'vg'=>24);
    $Chars = array('a'=>10,'b'=>11,'c'=>12,'d'=>13,'e'=>14,'f'=>15,'g'=>16,'h'=>17,'i'=>18,'j'=>19,'k'=>20,'l'=>21,'m'=>22,'n'=>23,'o'=>24,'p'=>25,'q'=>26,'r'=>27,'s'=>28,'t'=>29,'u'=>30,'v'=>31,'w'=>32,'x'=>33,'y'=>34,'z'=>35);
    if(array_key_exists(substr($iban,0,2), $Countries) && strlen($iban) == $Countries[substr($iban,0,2)]){
        $MovedChar = substr($iban, 4).substr($iban,0,4);
        $MovedCharArray = str_split($MovedChar);
        $NewString = "";
        foreach($MovedCharArray AS $key => $value) {
            if(!is_numeric($MovedCharArray[$key])) {
                if(!isset($Chars[$MovedCharArray[$key]])) return false;
                $MovedCharArray[$key] = $Chars[$MovedCharArray[$key]];
            }
            $NewString .= $MovedCharArray[$key];
        }
        if(bcmod($NewString, '97') == 1) return true;
    }
    return false;
}

function girocode_qr($atts){
	global $wp;
	$args = shortcode_atts( array(
		'noheader' => 0, // set to 1 if you want only the QR-Code, nothing else
		'ibangen' => 0,
		'iban' => '',
		'bic' => '',	
		'rec' => '',	// z.B. Max Mustermann, wenn leer kommt Formular
		'cur' => 'EUR',
		'sum' => 1.99,
		'subj' => 'Rechnung 123456789, Konto 123434',
		'comm' => 'Kommentar zur Ueberweisung',
	), $atts );
	if ( $args['ibangen'] == 1 ) {
		if (!isset($_GET['noheader'])) return makeiban();
	} else {
		$out = '<h6>Girocode-Generator</h6>';
		// Daten von der Befehlszeile
		//cmdline:	?noheader=1&iban=DE337002323230150232&bic=ABCEDE&rec=Maxine Mustermann&cur=EUR&sum=9.99&subj=Rechnung 123456789 Konto 123434&comm=Kommentar zur Ueberweisung
		if (isset($_GET['iban'])) $iban = sanitize_text_field($_GET['iban']); else $iban = $args['iban'];
		if (isset($_GET['bic'])) $bic = sanitize_text_field($_GET['bic']); else $bic = $args['bic'];
		if (isset($_GET['rec'])) $rec = sanitize_text_field($_GET['rec']); else $rec = $args['rec'];
		if (isset($_GET['cur'])) $cur = sanitize_text_field($_GET['cur']); else $cur = $args['cur'];
		if (isset($_GET['sum'])) $sum = sanitize_text_field($_GET['sum']); else $sum = $args['sum'];
		if (isset($_GET['subj'])) $subj = sanitize_text_field($_GET['subj']); else $subj = $args['subj'];
		if (isset($_GET['comm'])) $comm = sanitize_text_field($_GET['comm']); else $comm = $args['comm'];
		// oder Daten aus Formular
		if (isset($_POST["girosubmit"])) {
			$iban = sanitize_text_field($_POST["iban"]);			
			$bic = sanitize_text_field($_POST["bic"]);			
			$rec = sanitize_text_field($_POST["rec"]);			
			$cur = sanitize_text_field($_POST["cur"]);			
			$sum = sanitize_text_field($_POST["sum"]);			
			$subj = sanitize_text_field($_POST["subj"]);			
			$comm = sanitize_text_field($_POST["comm"]);
		}	
		if (empty($rec)) {
			// Form anzeigen, wenn $rec leer ist
			$out .= '<p><form id="giroform" method="post"><table>';
			$out .= '<tr><td>IBAN</td><td><input name="iban" id="iban" type="text" size="22" placeholder="'.$iban.'"></td></tr>';
			$out .= '<tr><td>BIC/SWIFT</td><td><input name="bic" id="bic" type="text" size="10" placeholder="'.$bic.'"></td></tr>';
			$out .= '<tr><td>Empfänger</td><td><input name="rec" id="rec" type="text" size="50" max="70" style="width:100%;max-width:100%" placeholder="'.$rec.'"></td></tr>';
			$out .= '<tr><td>Währung/Betrag</td><td><input name="cur" id="cur" type="text" size="3" value="EUR">';
			$out .= ' &nbsp; <input name="sum" id="sum" type="number" step="0.01" value="0.00" placeholder="0.00" size="10"></td></tr>';
			$out .= '<tr><td>Verwendungszweck</td><td><input name="subj" id="subj" type="text" size="50" style="width:100%;max-width:100%" placeholder="'.$subj.'"></td></tr>';
			$out .= '<tr><td>Kommentar</td><td><input name="comm" id="comm" type="text" size="50" max="70" style="width:100%;max-width:100%"></td></tr>';
			$out .='<tr><td colspan=2><input style="width:100%" name="girosubmit" id="girosubmit" form="giroform" type="submit" class="submit" value="Daten prüfen und Girocode erzeugen"></td></tr>';
			$out .='</table></form></p>';
			return $out;
		}

		// Betragsformatierung
		$sum = number_format( str_replace( ",", ".", $sum ), 2, '.', '' );
		// QR Code Daten (Zeilenumbruch beachten)
		$data = "BCD
001
1
SCT
".$bic."
".$rec."
".$iban."
".$cur.$sum."

".$subj."

".$comm;
		if ( isset($_GET['noheader']) || $args['noheader'] == 1 ) {   // wenn in single.php der Parameter gesetzt, keinen Header zeigen
			if (checkIBAN($iban)) return do_shortcode('[qrcode text="'.$data.'" size=3 margin=3]');
		} else {
			// QR Code generieren
			if(current_user_can('administrator')) {
				$out .= '<a href="'.esc_url(home_url(add_query_arg(array(), $wp->request))).'">Neuen Girocode eingeben</a>';
				$out .= ' &nbsp; <a href="'.
				esc_url(home_url(add_query_arg(array('noheader' => 1, 'iban' => $iban, 'bic' => $bic, 'rec' => $rec, 'cur' => $cur, 'sum' => $sum, 'subj' => $subj, 'comm' => $comm ), $wp->request)))
				.'">Direkt-URL</a>';
			}	
			if (! swift_validate($bic)) return "<b style='color:#FF0000;'>BIC (SWIFT code) <i>is not</i> valid.</b>";
			if (checkIBAN($iban)) {
				$out .= '<div class="timeline"><div style="text-align:center">'
				. do_shortcode('[qrcode text="'.$data.'" size=3 margin=3]')
				.'</div><div><pre>'.$data.'</pre></div></div>
				';
				return $out;
			} else return '<span style="color:tomato">IBAN '.$iban.' '.__('is not a valid IBAN', 'pb-chartscodes').'</span>';
		}	
	}
}
add_shortcode('girocode', 'girocode_qr');


// --------------------------- Nun die ipflag Funktionsklasse registrieren --------------------------------------------

class ipflag {
    const version = '9.3.20';
    const name = 'ipflag';
    const slug = 'ipflag';
    const safe_slug = 'ipflag';
    const ip_ranges_table_suffix = 'pb_ipflag_ranges';
    const countries_table_suffix = 'pb_ipflag_countries';
    const http_timeout = 60;
    const remote_offset = 2;

    public $url;
    public $flag_url;
    protected $path;
    protected $options;

    public function __construct() {

        $this->url = plugin_dir_url(__FILE__ );
        $this->flag_url = $this->url . '/flags';
        $this->path =  dirname(__FILE__ );
        $this->options = get_option(self::safe_slug.'_options');

        add_action('admin_init', array($this, 'settings_init'));
        add_action('admin_menu', array($this, 'add_options_page'));
        add_shortcode('ipflag', array($this, 'shortcode'));
		add_shortcode( 'webcounter', array($this, 'writevisitortodatabase') );

        if (isset($this->options['auto_update'])) {
            add_action(self::safe_slug.'_update', array($this, 'do_auto_update'));
            add_filter('cron_schedules', array($this, 'custom_schedule'));
            register_deactivation_hook(__FILE__, array($this, 'deschedule_update'));
            $this->schedule_update();
        } else {
            $this->deschedule_update();
        }
    }

    public function schedule_update(){
        if (!wp_next_scheduled(self::safe_slug.'_update')) {
            wp_schedule_event(time(), self::safe_slug.'_monthly', self::safe_slug.'_update');
        }
    }

    public function deschedule_update(){
        if (wp_next_scheduled(self::safe_slug.'_update')) {
            wp_clear_scheduled_hook(self::safe_slug.'_update');
        }
    }

    public function custom_schedule($schedules){
        /* Please do not configure cron to interval
         * less than 2419900 (7 days) because GitHub might
         * disable our db update repository due to server load
         */
        $schedules[self::safe_slug.'_monthly'] = array(
            'interval'=> 2419800,
            'display'=>  __('every month', 'pb-chartscodes')
        );
        return $schedules;
    }


	public function get_info($ip = null){
		global $wpdb;
		$ip_ranges_table_name = $wpdb->prefix . self::ip_ranges_table_suffix;
		$countries_table_name = $wpdb->prefix . self::countries_table_suffix;

		if ($ip === null) {
			if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
				$ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
			}
		}
		// IP-Version ermitteln
		if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			$ip_version = 4;
			$ip_type_label = 'IPv4';
		} elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
			$ip_version = 6;
			$ip_type_label = 'IPv6';
		} else {
			return false;
		}
		// Binär konvertieren
		$ip_bin = inet_pton($ip);
		if ($ip_bin === false) return false;
		// SQL-Query
		$sql = $wpdb->prepare(
			"SELECT %s AS ip, r.code, c.name, c.latitude, c.longitude
			 FROM $ip_ranges_table_name r
			 INNER JOIN $countries_table_name c
				 ON r.code = c.code
			 WHERE r.ip_version = %d
			   AND r.fromip <= %s
			   AND r.toip >= %s
			 LIMIT 1",
			$ip, $ip_version, $ip_bin, $ip_bin
		);
		$info = $wpdb->get_row($sql);
		if (!$info) return false;
		// IP-Typ ergänzen
		$info->ip_type = $ip_type_label;
		$info->ip = "{$info->ip} ({$info->ip_type})";
		return $info;
	}


    public function get_isofromland($land = null){
        global $wpdb;
        $countries_table_name = $wpdb->prefix . self::countries_table_suffix;
        $sql=   "SELECT code, name FROM ".$countries_table_name." WHERE name LIKE '%".$land."%' ";
        $info = $wpdb->get_row($sql);
		if($info === null) return false;
        return $info;
    }
	
	
	// ------------------ Array aus iso-3166-2.php array bzw. von ssl.pbcs.de -------------------------
	public function country_code ($lang = null , $code = null) {
		global $wpdb;
		$table = $wpdb->prefix . self::countries_table_suffix;
		$isoland = $wpdb->get_var( $wpdb->prepare( "SELECT name FROM $table WHERE code = %s LIMIT 1", strtoupper(trim($code)) ));
		return (isset ($isoland) ? $isoland : false);
	}

    public function get_flag($info){
		// Load flag freaky style for flags
		wp_enqueue_style( 'pb-chartscodes-flagstyle', plugin_dir_url( __FILE__ ) . '/flags/freakflags.min.css' );
		$flag = '';
        if($info != null)
			$flag = '<div class="fflag fflag-'.strtoupper($info->code).' ff-sm" title="'.$this->country_code('de',$info->code) . ' '.$info->code.'"></div>';
        else
            $flag = '<div class="fflag fflag-EU ff-sm" title="privates Netzwerk"></div>';
        return $flag;
    }

    public function get_country($info){
		// Load Country to ISO
        if($info != null) $land = $this->country_code('de',$info->code) . ' '.$info->code;
		else $land = '';
        return $land;
    }


	private static function is_bot($user_agent) {
		$user_agent = strtolower($user_agent);

		// Bot-typische Begriffe
		$identifiers = array(
			'bot', 'crawl', 'slurp', 'crawler', 'spider', 'monitor', 'checker',
			'fetch', 'scraper', 'scan', 'search', 'seo', 'libwww-perl',
			'facebookexternalhit', 'facebot', 'googlebot', 'bingbot', 'yandex', 'duckduckbot',
			'baiduspider', 'semrush', 'ahrefsbot', 'mj12bot', 'applebot', 'twitterbot', 'linkedinbot',
			'embedly', 'whatsapp', 'telegrambot', 'discordbot', 'skypeuripreview',
			'python', 'java', 'curl', 'wget', 'axios', 'node-fetch', 'httpclient', 'go-http-client',
			'restsharp', 'guzzlehttp', 'php/', 'lua-resty', 'scrubby',
			'pagespeed', 'pingdom', 'gtmetrix', 'lighthouse', 'uptimerobot'
		);

		foreach ($identifiers as $identifier) {
			if (stripos($user_agent, $identifier) !== false) {
				return true;
			}
		}

		// Alte Chrome-Versionen raus
		if (preg_match('/chrome\/(\d+)\./', $user_agent, $matches)) {
			if ((int) $matches[1] < 128) {
				return true;
			}
		}

		// Alte Firefox-Versionen raus
		if (preg_match('/firefox\/(\d+)\./', $user_agent, $matches)) {
			if ((int) $matches[1] < 128) {
				return true;
			}
		}
		return false;
	}

	
	// Browser des Betrachters rausfinden und anzeigen und Referer
	function getBrowser() {
		// get user fullname if loggedin or guest or comment author by cookie
		$current_user = wp_get_current_user();
		if ( $current_user->ID ) {
			// Check For Member
			$user_id = $current_user->ID;
			$user_name = $current_user->display_name;
			$user_type = 'member';
		} elseif ( !empty( $_COOKIE['comment_author_'.COOKIEHASH] ) ) {
			// Check For Comment Author ( Guest )
			$user_id = 0;
			$user_name = trim( strip_tags( $_COOKIE['comment_author_'.COOKIEHASH] ) );
			$user_type = 'guest';
		} else {
			// Check For Guest
			$user_id = 0;
			$user_name = __( 'Guest', 'pb-chartscodes' );
			$user_type = 'guest';
		}
		$u_agent = esc_attr(htmlspecialchars(wp_strip_all_tags($_SERVER['HTTP_USER_AGENT'], false)));
		$language = esc_attr(htmlspecialchars(wp_strip_all_tags($_SERVER['HTTP_ACCEPT_LANGUAGE'], false)));
		$bname = 'Unknown';
		$platform = 'Unknown';
		$version= "";

		//First get the platform?
		$os_array = array(
			'/windows nt 10.0/i'    =>  'Windows 10',
			'/windows nt 6.3/i'     =>  'Windows 8.1/S2012R2',
			'/windows nt 6.2/i'     =>  'Windows 8',
			'/windows nt 6.1/i'     =>  'Windows 7',
			'/windows nt 6.0/i'     =>  'Windows Vista',
			'/windows nt 5.2/i'     =>  'Windows Server 2003/XP x64',
			'/windows nt 5.1/i'     =>  'Windows XP',
			'/windows xp/i'         =>  'Windows XP',
			'/windows nt 5.0/i'     =>  'Windows 2000',
			'/windows me/i'         =>  'Windows ME',
			'/win98/i'              =>  'Windows 98',
			'/win95/i'              =>  'Windows 95',
			'/win16/i'              =>  'Windows 3.11',
			'/macintosh|mac os x/i' =>  'Mac OS X',
			'/mac_powerpc/i'        =>  'Mac OS 9',
			'/linux/i'              =>  'Linux',
			'/ubuntu/i'             =>  'Ubuntu',
			'/freebsd/i'             =>  'FreeBSD',
			'/debian/i'             =>  'Debian',
			'/iphone/i'             =>  'iPhone',
			'/ipod/i'               =>  'iPod',
			'/ipad/i'               =>  'iPad',
			'/android/i'            =>  'Android',
			'/blackberry/i'         =>  'BlackBerry',
			'/webos/i'              =>  'Mobile',
			'/wordpress/i'          =>  'Wordpress'
		);
		foreach ($os_array as $regex => $value) { 
			if (preg_match($regex, $u_agent)) $platform = $value;
		}

		// Windows 11 oder neuer, Server 2019 und Server 2022 detektieren
			// foreach (getallheaders() as $name => $value) { echo "$name: $value\n"; }
			// print_r( getallheaders()['Sec-Ch-Ua-Platform-Version'] );
		$hintver='';
		$browhints=getallheaders() ?? array();
		if (!empty($browhints['Sec-Ch-Ua-Platform'])) $hintos = str_replace('"', '', $browhints['Sec-Ch-Ua-Platform'] ?? '');
		if (!empty($browhints['sec-ch-ua-platform'])) $hintos = str_replace('"', '', $browhints['sec-ch-ua-platform'] ?? '');
		if (!empty($browhints['Sec-Ch-Ua-Platform-Version'])) 		$hintver = str_replace('"', '', $browhints['Sec-Ch-Ua-Platform-Version'] ?? '');
		if (!empty($browhints['sec-ch-ua-platform-version'])) 		$hintver = str_replace('"', '', $browhints['sec-ch-ua-platform-version'] ?? '');
		if ( $hintos == 'Windows' && $hintver == '3.0.0' ) $platform = 'Windows Server 2016';
		if ( $hintos == 'Windows' && $hintver == '7.0.0' ) $platform = 'Windows Server 2019';
		if ( $hintos == 'Windows' && floatval($hintver) == 12 ) $platform = 'Windows Server 2022';
		if ( $hintos == 'Windows' && floatval($hintver) >= 13 ) $platform = 'Windows 11';

		// Next get the name of the useragent yes seperately and for good reason
		if(preg_match('/MSIE/i',$u_agent) && !preg_match('/Opera/i',$u_agent)) {
			$bname = 'Internet Explorer';
			$ub = "MSIE";
		}
		else if(preg_match('/Trident/i',$u_agent)) {    // this condition is for IE11
			$bname = 'Internet Explorer';
			$ub = "rv";
		}
		else if(preg_match('/Firefox/i',$u_agent)) {
			$bname = 'Mozilla Firefox';
			$ub = "Firefox";
		}
		else if(preg_match('/Chrome/i',$u_agent)) {
			$bname = 'Google Chrome';
			$ub = "Chrome";
			if(preg_match('/Edg/i',$u_agent)) {
					$bname = 'Microsoft Edge';
					$ub = "Edg";
				}
			if(preg_match('/Edge/i',$u_agent)) {
					$bname = 'Edge legacy';
					$ub = "Edge";
				}
		}
		else if(preg_match('/Safari/i',$u_agent)) {
			$bname = 'Apple Safari';
			$ub = "Safari";
		}
		else if(preg_match('/Opera/i',$u_agent)) {
			$bname = 'Opera';
			$ub = "Opera";
		}
		else if(preg_match('/Netscape/i',$u_agent)) {
			$bname = 'Netscape';
			$ub = "Netscape";
		}
		else if(preg_match('/ms-office/i',$u_agent)) {
			$bname = 'MS-Office';
			$ub = "MSOffice";
		}
		else if(preg_match('/feedparser/i',$u_agent)) {
			$bname = 'Feedparser RSS';
			$ub = "Feedparser";
		}
		else if(preg_match('/rss-parser/i',$u_agent)) {
			$bname = 'RSS Parser';
			$ub = "RSSParser";
		}
		else if(preg_match('/Go-http-client/i',$u_agent)) {
			$bname = 'GO HTTP Client';
			$ub = "GOHTTPClient";
		}
		else if(preg_match('/wordpress/i',$u_agent)) {
			$bname = 'Wordpress';
			$ub = "Wordpress";
		}
		else if(preg_match('/outlook/i',$u_agent)) {
			$bname = 'Outlook';
			$ub = "Outlook";
		}
		else if(preg_match('/bot|crawl|slurp|spider|lua-resty|mediapartners/i',$u_agent)) {
			$bname = 'Bot/Spider';
			$ub = "Bot";
		}
		// finally get the correct version number
		$known = array('Version', $ub, 'other');
		$pattern = '#(?<browser>' . join('|', $known) .
		 ')[/|: ]+(?<version>[0-9.|a-zA-Z.]*)#';
		if (!preg_match_all($pattern, $u_agent, $matches)) {
			// we have no matching number just continue
		}
		// see how many we have
		$i = count($matches['browser']);
		if ($i != 1) {
			//we will have two since we are not using 'other' argument yet
			//see if version is before or after the name
			if (strripos($u_agent,"Version") < strripos($u_agent,$ub)){
				$version= $matches['version'][0];
			}
			else {
				$version= $matches['version'][1];
			}
		}
		else {
			$version= $matches['version'][0];
		}
		// check if we have a number
		if ($version==null || $version=="") {$version="?";}
		return array(
			'userAgent' => $u_agent,
			'name'      => $bname,
			'version'   => $version,
			'platform'  => $platform,
			'pattern'    => $pattern,
			'language'    => $language,
			'username'    => $user_name,
			'usertype'    => $user_type,
		);
	}	

	// IP-Adresse des Users bekommen
	function cc_get_the_user_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			//check ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			//to check ip is pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		// letzte Stelle der IP anonymisieren (0 setzen)	
		// $ip = long2ip(ip2long($ip) & 0xFFFFFF00);
		return apply_filters( 'wpb_get_ip', $ip );
	}
	
	// Browser und OS icons anzeigen
	public function showbrowosicon($xname) {
		if (str_contains($xname,'Google Chrome')) $xicon = 'chrome';
		else if (str_contains($xname,'Microsoft Edge')) $xicon = 'edge';
		else if (str_contains($xname,'Mozilla Firefox')) $xicon = 'firefox';
		else if (str_contains($xname,'Opera')) $xicon = 'opera';
		else if (str_contains($xname,'Edge legacy')) $xicon = 'edge-legacy';
		else if (str_contains($xname,'Internet Explorer')) $xicon = 'internet-explorer';
		else if (str_contains($xname,'Apple Safari')) $xicon = 'safari';
		else if (str_contains($xname,'MS-Office')) $xicon = 'microsoft365';
		else if (str_contains($xname,'Outlook')) $xicon = 'microsoft365';
		else if (str_contains($xname,'Windows Server 2022')) $xicon = 'windows11';
		else if (str_contains($xname,'Windows Server 201')) $xicon = 'windows';
		else if (str_contains($xname,'Windows 11')) $xicon = 'windows11';
		else if (str_contains($xname,'Windows 10')) $xicon = 'windows';
		else if (str_contains($xname,'Windows 8')) $xicon = 'windows';
		else if (str_contains($xname,'Windows XP')) $xicon = 'windowsxp';
		else if (str_contains($xname,'Windows 7')) $xicon = 'windowsxp';
		else if (str_contains($xname,'Linux')) $xicon = 'linux';
		else if (str_contains($xname,'Ubuntu')) $xicon = 'ubuntu';
		else if (str_contains($xname,'Debian')) $xicon = 'debian';
		else if (str_contains($xname,'FreeBSD')) $xicon = 'freebsd';
		else if (str_contains($xname,'Blackberry')) $xicon = 'blackberry';
		else if (str_contains($xname,'Android')) $xicon = 'android';
		else if (str_contains($xname,'Mac OS X')) $xicon = 'apple';
		else if (str_contains($xname,'Wordpress')) $xicon = 'wordpress';
		else if (str_contains($xname,'Windows Server 2003/XP x64')) $xicon = 'windowsxp';
		else if (str_contains($xname,'Windows 8.1/S2012R2')) $xicon = 'windows';
		else if (str_contains($xname,'iPhone')) $xicon = 'mobile-phone';
		else $xicon = 'globe';
		return '<i class="fa fa-' . $xicon . '"></i> ';
	}

	// 
	//  Besucher in Datenbank schreiben oder als admin auswerten
	// 

	function writevisitortodatabase($attr) {
		global $wp;
		extract(shortcode_atts(array(
			'admin'     => 0
		), $attr));
		global $wpdb;
		$tage = array("Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag");
		$table = $wpdb->prefix . "sitevisitors";
		if (isset($this->options['webcounterkeepdays'])) {
			$keepdays=intval(sanitize_text_field($this->options['webcounterkeepdays']));
			if ($keepdays < 30) $keepdays = 30;
		} else {
			$keepdays=30;
		}
		// Anzeige für den Admin
		if ( $admin && is_user_logged_in() ) {
			global $wpdb;
			setlocale (LC_ALL, 'de_DE.utf8', 'de_DE@euro', 'de_DE', 'de', 'ge'); 
			$customers = $wpdb->get_results("SELECT MAX(id) as maxid, min(datum) as mindatum, COUNT(id) as xstored FROM " . $table);
			foreach($customers as $customer){
				$totales = sprintf(__('%1s clicks total, %2s since %3s', 'pb-chartscodes'),number_format_i18n($customer->maxid,0),number_format_i18n($customer->xstored,0),date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($customer->mindatum) ) ) .' vor '.human_time_diff( strtotime($customer->mindatum),current_time( 'timestamp' ) ).' ';
				$sdatum = new DateTime($customer->mindatum);
				$edatum = new DateTime('Now');
				$interval = $sdatum->diff($edatum)->days;
			}
			// Suchfiltern
			if (isset($_GET['suchfilter'])) {
				$suchfilter = sanitize_text_field($_GET['suchfilter']);
				if  ($suchfilter != NULL) {
					$sqlsuchfilter = " AND ( usertype LIKE '%".$suchfilter."%'
						OR username LIKE '%".$suchfilter."%'
						OR browser LIKE '%".$suchfilter."%'
						OR platform LIKE '%".$suchfilter."%'
						OR postid LIKE '%".$suchfilter."%'	) ";
					$customers = $wpdb->get_results("SELECT * FROM " . $table ." WHERE 1=1 ".$sqlsuchfilter);
					if (count($customers)==0) {
						$html = '<p>'.sprintf(__('no matches for search criteria {%s}. Try other search words','pb-chartscodes'),$suchfilter); 
						$html .= '. <a href="'.home_url($wp->request).'">'.__('return to counter','pb-chartscodes').'</a></p>';
						return $html;
					}	
				} else	$sqlsuchfilter='';
			} else {
			  $suchfilter = '';
			  $sqlsuchfilter='';
			}
			if (isset($_GET['items'])) {
				$items = intval(sanitize_text_field($_GET['items']));
			} else {
			  $items=20;
			}
			if (isset($_GET['zeitraum'])) {
				$zeitraum = intval(sanitize_text_field($_GET['zeitraum']));
			} else {
			  $zeitraum=$interval;
			}
			if ($items < 1) $items = 1;
			if ($zeitraum > $interval) $zeitraum = $interval;
			if ($zeitraum < 1) $zeitraum = 1;
			$startday = ' - ' . date("d.m.Y", strtotime("-$zeitraum days"));
			// Webseitenstatistik
			$html = '<div style="text-align:right"><form name="wcitems" method="get">';
			$html .= '<a target="_blank" href="'.get_bloginfo('url').'/sitemap/"><i class="fa fa-map"></i> Sitemap</a> &nbsp; ' . $totales;
			$html .=' &nbsp; <input type="text" size="3" style="width:50px" id="zeitraum" name="zeitraum" value="'.$zeitraum.'">/'.$keepdays.' Tg ';
			$html .='<input type="text" size="3" style="width:50px" id="items" name="items" value="'.$items.'"> Zeilen ';
			$html .='<input type="text" size="20" title="filtern nach Browser, username, usertyp, Einzelbeitrag" placeholder="Suchfilter" id="suchfilter" name="suchfilter" value="'.$suchfilter.'">';
			$html .= '</select><input type="submit" value="'.__('show items', 'pb-chartscodes').'" /></form></div>';

			//	Klicks pro Tag auf Zeitraum DE, IE, NL
			$countries = "'DE','IE','NL'";   // Deutschland und Azure Amsterdam und Dublin
			$customers = $wpdb->get_results("SELECT datum, COUNT(SUBSTRING(datum,1,10)) AS viscount, datum FROM " . $table . " WHERE country IN ($countries) ".$sqlsuchfilter." GROUP BY SUBSTRING(datum,1,10) ORDER BY datum desc LIMIT ". $zeitraum);
			if  ( (int) count($customers)>0) {
				$html .='<h6>'.sprintf(__('clicks last %s days', 'pb-chartscodes'),$zeitraum).' '.$countries.'</h6>';
				$linedata=''; $linetable='';
				foreach($customers as $customer){
					$linedata .= substr($customer->datum,8,2).'.'.substr($customer->datum,5,2) . ':' . $customer->viscount.'|';
					$linetable .= substr($customer->datum,0,10) . ':' . $customer->viscount.'|';
				}	
				// Neue GD Charts
				$html .= do_shortcode('[gd_chart width="1360" type="line" data="'.$linedata.'"]');
				// $html .= do_shortcode('[gd_chart width="1360" type="line" table=1 table_pos="only" data="'.$linetable.'"]');
			}	


			//	Klicks pro Tag auf Zeitraum
			$customers = $wpdb->get_results("SELECT datum, COUNT(SUBSTRING(datum,1,10)) AS viscount, datum FROM " . $table . " WHERE 1=1 ".$sqlsuchfilter." GROUP BY SUBSTRING(datum,1,10) ORDER BY datum desc LIMIT ". $zeitraum);
			if ( (int) count($customers)>0) {
				$html .='<h6>'.sprintf(__('clicks last %s days', 'pb-chartscodes'),$zeitraum).'</h6>';
				$linedata=''; $linetable='';
				foreach($customers as $customer){
					$linedata .= substr($customer->datum,8,2).'.'.substr($customer->datum,5,2) . ':' . $customer->viscount.'|';
					$linetable .= substr($customer->datum,0,10) . ':' . $customer->viscount.'|';
				}	
				// Neue GD Charts
				$html .= do_shortcode('[gd_chart width="1360" type="line" data="'.$linedata.'"]');
				$html .= do_shortcode('[gd_chart width="1360" type="line" table=1 table_pos="only" data="'.$linetable.'"]');
			}	


			if ( empty($suchfilter) ) {
				
				//	Top x Seiten/Beiträge auf Zeitraum
				$xsum=0;
				$labels="";$values='';
				$customers = $wpdb->get_results("SELECT postid, COUNT(*) AS pidcount FROM " . $table . " WHERE datum >= DATE_ADD( NOW(), INTERVAL -".$zeitraum." DAY ) GROUP BY postid ORDER BY pidcount desc LIMIT ".$items );
				$html .='<h6>'.sprintf(__('top %1s pages last %2s days', 'pb-chartscodes'),$items,$zeitraum).$startday.'</h6><table>';
				if (!empty($customers)) $toppid = $customers[0]->pidcount; else $toppid=1;
				foreach($customers as $customer){
					if ( get_post_meta( $customer->postid, 'post_views_count', true ) > 0 ) {
						$xsum += absint($customer->pidcount);
						$html .= '<tr><td><progress style="width:400px" max="100" value="'.round($customer->pidcount / $toppid * 100).'"></progress>
						' . $customer->pidcount . '</td><td>
						</td><td><a title="Post aufrufen" href="'.get_the_permalink($customer->postid).'">' . get_the_title($customer->postid) . '</a></td><td>';
						$html .= colordatebox( get_the_date('U', $customer->postid) ,NULL ,NULL,1);
						$html .= '</td><td><i class="fa fa-eye"></i>'.sprintf(__(', visitors alltime: %s', 'pb-chartscodes'),number_format_i18n( (float) get_post_meta( $customer->postid, 'post_views_count', true ),0) ) . '</td></tr>';
					}	
				}	
				$html .= '<tfoot><tr><td colspan=5>'.sprintf(__('<strong>%s</strong> sum of values', 'pb-chartscodes'),number_format_i18n($xsum,0)).' <strong>&Oslash; '.number_format_i18n( ($xsum/count($customers)), 2 ).'</strong></td></tr></tfoot>';
				$html .= '</table>';

				//	Top x Herkunftsseiten auf Zeitraum
				$xsum=0;
				$customers = $wpdb->get_results("SELECT referer, COUNT(*) AS refcount FROM " . $table . " WHERE datum >= DATE_ADD( NOW(), INTERVAL -".$zeitraum." DAY ) GROUP BY referer ORDER BY refcount desc LIMIT ".$items );
				$html .='<h6>'.sprintf(__('top %1s referers last %2s days', 'pb-chartscodes'),$items,$zeitraum).$startday.'</h6><table>';
				if (!empty($customers)) $toprefer = $customers[0]->refcount; else $toprefer=1;
				foreach($customers as $customer){
					$xsum += absint($customer->refcount);
					$html .= '<tr><td><nobr>';
					$html .= '<progress style="width:400px" max="100" value="'.round($customer->refcount / $toprefer * 100).'"></progress> ';
					$html .= number_format_i18n($customer->refcount,0) . '</nobr></td><td>' . $customer->referer . '</td></tr>';
				}	
				$html .= '<tfoot><tr><td colspan=3>'.sprintf(__('<strong>%s</strong> sum of values', 'pb-chartscodes'),number_format_i18n($xsum,0)).'<b> &Oslash; '.number_format_i18n( ($xsum/count($customers)), 2 ).'</b></td></tr></tfoot></table>';
			}	


			// Filter-Anzeige
			if (!empty($suchfilter)) $filtertitle='<i class="fa fa-filter"></i> '.$suchfilter; else $filtertitle=''; 

			//	Top x Besucher mit Details auf Zeitraum mit Filtermöglichkeit
			$customers = $wpdb->get_results("SELECT * FROM " . $table . " WHERE datum >= DATE_ADD( NOW(), INTERVAL -".$zeitraum." DAY ) ".$sqlsuchfilter." ORDER BY datum desc LIMIT ".$items);
			$html .='<h6>'.sprintf(__('last %1s visitors %2s last %3s days', 'pb-chartscodes'),$items,$filtertitle,$zeitraum).$startday.'</h6><table>';
			foreach($customers as $customer){
				$datum = date('d.m.Y H:i:s',strtotime($customer->datum));	
				$html .= '<tr><td><abbr title="#'.$customer->id.' - '.$customer->useragent.'">' . $this->showbrowosicon($customer->browser) . ' ' . $customer->browser .' ' . $customer->browserver .'</abbr></td>';
				$html .= '<td><abbr>' . $this->showbrowosicon($customer->platform). ' ' . substr($customer->platform,0,19). ' ' . substr($customer->language,0,2) .'</abbr></td>';
				if ($customer->country == 'EUROPEANUNION') $customer->country = 'EU';
				if ($customer->postid == '-9999') {
					$cptitle = '<i class="fa fa-home"></i> Homepage';
					$cplink = get_site_url();
				} else if ($customer->postid == '-1000') {
					$cptitle = '<i class="fa fa-rss"></i> RSS Feed';
					$cplink = get_site_url().'/feed';
				} else {
					$cptitle = get_the_title($customer->postid);
					$cplink = get_the_permalink($customer->postid);
				}	
				$html .= '<td>'. $this->get_flag(  (object) [ 'code' => $customer->country ] ).'</td>';
				$html .= '<td><i class="fa fa-user"></i> <abbr>'. $customer->username . ' | '.$customer->usertype .'</abbr></td>';
				$html .= '<td><i class="fa fa-map-marker"></i> <abbr>' . $customer->userip .'</abbr></td><td><abbr>
					<a onclick="document.location.href=\''. esc_url(home_url(add_query_arg(array('suchfilter' => $customer->postid, 'zeitraum' => $zeitraum, 'items' => $items ), $wp->request))).'\'"
					title="filter:'. $customer->postid.'" class="fa fa-filter"></a> &nbsp; ';
				$html .= ' <a title="Post aufrufen" href="'.$cplink.'">' . $cptitle .'</abbr></a></td>';
				$html .= '<td>' .colordatebox( strtotime($customer->datum), NULL, NULL, 1 ) . '</span></td></tr>';
			}	
			$html .= '</table>';


			//	Besucher nach Stunde auf Zeitraum
			$customers = $wpdb->get_results("SELECT SUBSTRING(datum,12,2) AS stunde, COUNT(SUBSTRING(datum,12,2)) AS viscount, datum FROM " . $table . " WHERE datum >= DATE_ADD( NOW(), INTERVAL -".$zeitraum." DAY ) ".$sqlsuchfilter." GROUP BY SUBSTRING(datum,12,2) ORDER BY SUBSTRING(datum,12,2) ");
			$html .='<h6>'.sprintf(__('clicks by hour %1s last %2s days', 'pb-chartscodes'),$filtertitle,$zeitraum).$startday.'</h6>';
			$linedata=''; $linetable='';
			foreach($customers as $customer){
				$linedata .= $customer->stunde . ':' . $customer->viscount.'|';
			}	
			// Neue GD Charts
			$html .= do_shortcode('[gd_chart width="1360" type="vbar" data="'.$linedata.'"]');


			//	Besucher nach Wochentag auf Zeitraum
			$customers = $wpdb->get_results("SELECT WEEKDAY(SUBSTRING(datum,1,10)) AS wotag, COUNT(WEEKDAY(SUBSTRING(datum,1,10))) AS viscount, datum FROM " . $table . " WHERE datum >= DATE_ADD( NOW(), INTERVAL -".$zeitraum." DAY ) ".$sqlsuchfilter." GROUP BY WEEKDAY(SUBSTRING(datum,1,10)) ORDER BY SUBSTRING(datum,1,10) ");
			$html .='<h6>'.sprintf(__('clicks by weekday %2s last %1s days', 'pb-chartscodes'),$filtertitle,$zeitraum) . $startday . '</h6>';
			$linedata=''; $linetable='';
			foreach($customers as $customer){
				$linedata .= $tage[$customer->wotag] . ':' . $customer->viscount.'|';
			}	
			// Neue GD Charts
			$html .= do_shortcode('[gd_chart width="1360" type="hbar" data="'.$linedata.'"]');


			//	Top x calendarweeks last recorded days (180)
			$customers = $wpdb->get_results("
				SELECT YEARWEEK(SUBSTRING(datum,1,10), 3) AS kw, COUNT(*) AS viscount 
				FROM " . $table . " 
				WHERE datum >= DATE_ADD(NOW(), INTERVAL -" . $zeitraum . " DAY ) " . $sqlsuchfilter . " 
				GROUP BY YEARWEEK(SUBSTRING(datum,1,10), 3) 
				ORDER BY YEARWEEK(SUBSTRING(datum,1,10), 3)
			");
			$html .= '<h6>' . sprintf(__('clicks by calendar week %2s last %1s days', 'pb-chartscodes'), $filtertitle, $zeitraum) . $startday . '</h6>';
			$linedata=''; $linetable='';
			foreach($customers as $customer){
				$linedata .= $customer->kw . ':' . $customer->viscount.'|';
			}	
			// Neue GD Charts
			$html .= do_shortcode('[gd_chart width="1360" type="vbar" legend="false" data="'.$linedata.'"]');


			//	Top x Browser auf Zeitraum
			$customers = $wpdb->get_results("SELECT browser, COUNT(browser) AS bcount FROM " . $table . " WHERE datum >= DATE_ADD( NOW(), INTERVAL -".$zeitraum." DAY ) ".$sqlsuchfilter." GROUP BY browser ORDER BY bcount desc LIMIT ".$items);
			$html .='<h6>'.sprintf(__('Top %1s Browsers %2s last %3s days', 'pb-chartscodes'),$items,$filtertitle,$zeitraum).'</h6>';
			$linedata=''; $linetable='';
			foreach($customers as $customer){
				$linedata .= $customer->browser . ':' . $customer->bcount . '|';
			}	
			// Neue GD Charts
			$html .= do_shortcode('[gd_chart width="1360" type="pie" table=1 data="'.$linedata.'"]');
			

			//	Top x Platform (Betriebssysteme) auf Zeitraum
			$customers = $wpdb->get_results("SELECT platform, COUNT(platform) AS bcount FROM " . $table . " WHERE datum >= DATE_ADD( NOW(), INTERVAL -".$zeitraum." DAY ) ".$sqlsuchfilter." GROUP BY platform ORDER BY bcount desc LIMIT ".$items);
			$html .='<h6>'.sprintf(__('Top %1s operating systems %2s last %3s days', 'pb-chartscodes'),$items,$filtertitle,$zeitraum).'</h6>';
			$linedata=''; $linetable='';
			foreach($customers as $customer){
				$linedata .= $customer->platform . ':' . $customer->bcount . '|';
			}	
			// Neue GD Charts
			$html .= do_shortcode('[gd_chart width="1360" type="pie" table=1 data="'.$linedata.'"]');


			//	Top x Länder auf Zeitraum
			$customers = $wpdb->get_results("SELECT country, COUNT(country) AS ccount, datum FROM " . $table . " WHERE datum >= DATE_ADD( NOW(), INTERVAL -".$zeitraum." DAY ) GROUP BY country ORDER BY ccount desc LIMIT ".$items);
			$html .='<h6>'.sprintf(__('Top %1s countries %2s last %3s days', 'pb-chartscodes'),$items,$filtertitle,$zeitraum).'</h6><table>';
			$linedata=''; $linetable='';
			foreach($customers as $customer){
				$linedata .= $this->country_code('de',$customer->country) . ':' . $customer->ccount . '|';
			}	
			// Neue GD Charts
			$html .= do_shortcode('[gd_chart width="1360" type="pie" table=1 data="'.$linedata.'"]');


			//	Archive: Beiträge pro Monat letzte 20 = items Monate, verfügbare monate werden angezeigt und können durch items erhöhen gezeigt werden.
			if ( empty($suchfilter) ) {
				$customers = $wpdb->get_results("SELECT DISTINCT MONTH( post_date ) AS month, YEAR( post_date ) AS year, COUNT( id ) as post_count FROM $wpdb->posts WHERE post_status = 'publish' and post_type = 'post' GROUP BY month, year ORDER BY post_date DESC");
				$statmonsori = count($customers); 
				if ($items < $statmonsori) $statmons = $items; else $statmons = $statmonsori;
				$customers = $wpdb->get_results("SELECT DISTINCT MONTH( post_date ) AS month, YEAR( post_date ) AS year, COUNT( id ) as post_count FROM $wpdb->posts WHERE post_status = 'publish' and post_type = 'post' GROUP BY month, year ORDER BY post_date DESC LIMIT $statmons");
				$html .='<h6>'.sprintf(__('new posts per month last %1s months (%2s available, raise items to show)', 'pb-chartscodes'),$statmons,$statmonsori).'</h6><table>';
				$linedata=''; $linetable='';
				foreach($customers as $customer){
					$valu = isset($customer->month) ? floor($customer->post_count) : 0;
					$labl = date_i18n("M y", mktime(2, 0, 0, $customer->month, 1, $customer->year));
					$linedata .= $labl . ':' . $valu . '|';
				}	
				// Neue GD Charts
				$html .= do_shortcode('[gd_chart width="1360" type="line" table=1 data="'.$linedata.'"]');

			}	

			return $html;

		} else {
		
			// creates visitors in database if not exists
			$charset_collate = $wpdb->get_charset_collate();
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			
			// Datenbank bei Bedarf löschen hier
			//$sql = "DROP TABLE IF EXISTS " . $table;
			//$wpdb->query($sql);
			
			$sql = "CREATE TABLE IF NOT EXISTS " . $table . " (
			id int(11) not null auto_increment,
			browser varchar(100) not null,
			browserver varchar(30) not null,
			language varchar(80) not null,
			platform varchar(100) not null,
			useragent varchar(300) not null,
			referer varchar(300) not null,
			country varchar(90) not null,
			postid varchar(60) not null,
			userip varchar(50) not null,
			username varchar(50) not null,
			usertype varchar(50) not null,
			datum TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (`id`) ) $charset_collate;";
			dbDelta( $sql );
			// update table if columns username usertype missing
			//$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
			//			WHERE table_name = " . $table . " AND column_name = 'username'" );
			// if(empty($row)){ $wpdb->query("ALTER TABLE " . $table . " ADD username varchar(50) not null"); }			
			//$row = $wpdb->get_results( "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS
			//			WHERE table_name = " . $table . " AND column_name = 'usertype'" );
			//if(empty($row)){ $wpdb->query("ALTER TABLE " . $table . " ADD usertype varchar(50) not null"); }			
						
			// does the inserting, in case the form is filled and submitted
			$ua=$this->getBrowser();
			$browser = $ua['name'];
			$browserver = $ua['version'];
			$language = $ua['language'];
			$platform = $ua['platform'];
			$useragent = $ua['userAgent'];
			$username = $ua['username'];
			$usertype = $ua['usertype'];

			// Nur speichern, wenn kein BOT erkannt
			if ( !$this->is_bot( $useragent ) ) {
				if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
					$referer = filter_var( wp_unslash( $_SERVER['HTTP_REFERER'] ), FILTER_SANITIZE_URL );
				} else { $referer = 'none'; }
				$userip = $this->cc_get_the_user_ip();
					if(($info = $this->get_info($userip)) != false)
						$country = $info->code;
					else
						$country = 'EU';
				// Wenn Homepage gezählt wird, Pageid als -9999 speichern
				if ( is_front_page() && is_home() ) { $postid = -9999; } else {	$postid = get_the_ID(); }
				if (is_feed() ) $postid = -1000;   // ID für Feeds geben
				$datum = current_time( "mysql" );
				$wpdb->insert(
					$table,
					array(
						"browser" => $browser,
						"browserver" => $browserver,
						"language" => $language,
						"platform" => $platform,
						"useragent" => $useragent,
						"referer" => $referer,
						"country" => $country,
						"postid" => $postid,
						"userip" => $userip,
						"datum" => $datum,
						"username" => $username,
						"usertype" => $usertype,
					)
				);
			// Alte Datensätze älter 30 Tage (oder den in den Optionen eingestellten Wert) löschen
			$dsql = "DELETE FROM " . $table . " WHERE datum < DATE_ADD( NOW(), INTERVAL -".$keepdays." DAY )";
			$wpdb->query( $dsql );
			}	
		}
	}

	// IP-Informationen Shortcode mit flag, land und browserinfo optional
	function shortcode($atts, $content = null, $code = '') {
        extract(shortcode_atts(array(
			'ip' => null,  // provide an ip like 10.20.30.40
			'iso' => null, // provide ISO code to get country flag
			'name' => null,  // provide country name in english please, you will result a flag in german
			'showland' => 0,   // show country name
			'showgeo' => 0,   // show geocoordinates
			'details' => 0,   // get more details like ip net and referrer about the viewer who called this page
			'browser' => 0,  // show user agent string and browser info
		), $atts ));
		$yourdetails='';
		if ( $details && empty($ip) ) {
			if(!empty($_SERVER['HTTP_CLIENT_IP'])){
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
			}
		}
		if ( $details ) {
		 	$referer = wp_get_referer();
			$yourdetails = "<div><strong>".__('ip network', 'pb-chartscodes')."</strong> ". $ip . "<br><strong>".__('referer', 'pb-chartscodes')."</strong> " . $referer.'</div>';
		}
		$yourbrowser='';
		if ( $browser ) {
			$ua=$this->getBrowser();
			$yourbrowser = '<div><strong>Angemeldet als</strong> '. $ua['username'] . ' ' . $ua['usertype'];
			$yourbrowser .= '<br><strong>'.__('browser', 'pb-chartscodes').'</strong> '. $this->showbrowosicon($ua['name']) .' '.
				$ua['name'] . ' ' . $ua['version'] . ' unter ' .$this->showbrowosicon($ua['platform']).' '.$ua['platform']  . ' ' .
				substr($ua['language'],0,2) . '<br>' . $ua['userAgent'].'</div>';
		}
        if (($info = $this->get_info($ip)) != false) {
            $flag = '<div title="IP: '.$ip.'" style="display:inline">'.$this->country_code('de',$info->code).' ('.$info->code.') &nbsp; '.$this->get_flag($info).'</div>';
			if ($showgeo) $flag .= '&nbsp; GEO: '.(float) $info->latitude . ', '. (float) $info->longitude;
		} else {
            $flag = '<div title="IP: '.$ip.'" style="display:inline">privates Netzwerk &nbsp; '.$this->get_flag($info).'</div>';
		}	
		if ( !empty($iso) ) {
			$flag =  $this->get_flag( (object) [ 'code' => strtoupper($iso) ]);
			if ($showland) $flag .= '&nbsp;'.$this->get_country( (object) [ 'code' => strtoupper($iso)]);
		}
		if ( !empty($name) ) {
			$flag =  $this->get_flag( (object) [ 'code' => $this->get_isofromland($name)->code ]);
			if ($showland) $flag .= '&nbsp;'.$name.'&nbsp;'.$this->get_isofromland($name)->code;
			if ( $details ) {
				$flag .= ' &nbsp; ' . $this->get_isofromland($name)->code.' '.$this->get_isofromland($name)->name;
				$flag .= ' &nbsp; ' . $this->country_code('de',$this->get_isofromland($name)->code);
			} 
		}
		if (!empty($yourbrowser)) $yourbrowser = '<blockquote class="blockbulb">' . $yourbrowser;
		if (!empty($yourdetails)) $yourdetails .= '</blockquote>';
		return $flag . $yourbrowser . $yourdetails;
    }

    public function options_validate($input) {
        if(isset($input['db_update'])){
            try {
				$this->import_dbip_data();
            } catch(Exception $e){
                if($e->getCode() === 1){
                    add_settings_error(self::safe_slug.'_db_update', self::safe_slug.'_db_updated', $e->getMessage(), 'updated');
                } else {
                    add_settings_error(self::safe_slug.'_db_update', self::safe_slug.'_db_update_failed', $e->getMessage());
                }
                return $this->options;
            }
            add_settings_error(self::safe_slug.'_db_update', self::safe_slug.'_db_updated', __('ipflag database updated.', 'pb-chartscodes'), 'updated');
            return $this->options;
        }
        return $input;
    }

    public function do_auto_update(){
        try {
			$this->import_dbip_data();
        } catch(Exception $e){
        }
    }

    public function add_options_page(){
        add_options_page( esc_html__( 'Settings Admin', 'pb-chartscodes' ), 
		esc_html__( 'Charts QRcodes', 'pb-chartscodes' ),
		'manage_options', __FILE__, array($this, 'options_page'));
        add_filter('plugin_action_links', array($this, 'action_links'), 10, 2);
    }

    public function action_links($links, $file){
        if ($file == plugin_basename(__FILE__)) {
            $settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/admin.php?page=chartscodes/chartscodes.php">'.__('Settings', 'pb-chartscodes').'</a>';
            $links[] = $settings_link;
        }

        return $links;
    }

    public function options_page(){
	// für Charts, QR-Codes und Settings der IP-Datenbank
    ?>  <div class="wrap">
            <div class="icon32" id="icon-options-general"><br></div>
            <h2><?php echo esc_attr_e( 'Chartscodes Settings', 'pb-chartscodes' ); ?></h2>
			<div class="wrap">
			 <h3>Allgemeine Shortcodes</h3>
			<p><code>[ipflag ip="123.20.30.0" iso="mx" showland=0/1 details=0/1 browser=1 showgeo=0/1]</code>
				liefert eine Flagge und das Land zu einer IP odr einem IP-Netz. Die letzte IP-Ziffer wird wegen DSGVO anonymisiert<br>
				iso="xx" liefert die Flagge zum ISO-Land oder die EU-Flagge für private und unbekannte Netzwerke<br>
				showland=1 zeigt ISO und Land hinter der Flagge an, showgeo=1 zeigt Länge und breite als float an (z.B. für OSM Karte)<br>
				browser=1 liefert Betriebssystem und Browser des Besuchers, details=1 liefert den Referrer, das IP-Netz des Besuchers
			</p>
			<p><code>[webcounter admin=0]</code> zählt Seitenzugriffe und füllt Statistikdatenbank, admin=1 zum Auswerten mit Adminrechten<br>
				Ist die Admin /webcounter-Seite aufgerufen, kann über das Eingabefeld oder den optionalen URL-Parameter ?items=x die Ausgabe-Anzahl einiger Listeneinträge verändert werden.
			</p>
			<p><code>[bulawappen land="Nordrhein-Westfalen" oder land="nw"]</code>
				liefert das Wappen vom Bundesland in 30x50px, Eingabe Landeskürzel oder Länderbezeichnung mit ue statt ü
			</p>
			<p><code>[carlogo brand="mercedes" scale="sm"]</code>
				liefert das Logo und den Link zum Automobilhersteller  Größen (scale): leer 48px, bei sm: 32px und bei xs:21px
			</p>
			<p><code>[complogo brand="lenovo"]</code>
				liefert das Logo und den Link zum Hardware-Hersteller  Größe 60x60px
			</p>
            <form action="options.php" method="post">
            <?php settings_fields(self::safe_slug.'_options'); ?>
            <?php do_settings_sections(__FILE__); ?>
			</div>
                <input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
            </form>
        </div>
		
        <div class="wrap">
            <div class="img-wrap">
                <h2>QRCodes oder Barcodes generieren</h2>
			<div class="wrap">
				<p><code>[qrcode type="code-39" text="Hallo Welt" ]</code>
				<p><code>[qrcode type="ean-13" text="9780201379624" ]</code>
				<p><code>[qrcode text="tel:+49304030568956834058340" ]</code>
				<p><code>[qrcode text="tel:00492307299607" size=3 margin=3]</code>
				erstellt QR-Codes als Shortcode an der Cursorposition (Dokumentation und Parameter siehe Readme.txt)</p>                    
            </div></div>
			<div class="img-wrap">
				<h2><?php esc_html_e( 'Bar and Piecharts', 'pb-chartscodes' ); ?></h2>
				<div class="wrap">
				<p>siehe readme.txt<br>
					</p>
				</div></div>
        </div> <?php
    }

    public function settings_init(){
        // **** Debug nächsgte Zeile aktivieren, wenn Offset 0 sein soll *****
		//   delete_option('pb_ipflag_offset'); 
		
		register_setting(self::safe_slug.'_options', self::safe_slug.'_options', array($this, 'options_validate'));
        add_settings_section('database_section', __('ipflag database options', 'pb-chartscodes'), array($this, 'settings_section_database'), __FILE__);
        add_settings_field(self::safe_slug.'_webcounterkeepdays', __('delete webhits older than (days):', 'pb-chartscodes'), array($this, 'settings_field_webcounterkeepdays'), __FILE__, 'database_section');
        add_settings_field(self::safe_slug.'_auto_update', __('Enable automatic monthly database update check:', 'pb-chartscodes'), array($this, 'settings_field_auto_update'), __FILE__, 'database_section');
        add_settings_field(self::safe_slug.'_db_status', __('Current database status:', 'pb-chartscodes'), array($this, 'settings_field_db_status'), __FILE__, 'database_section');
        add_settings_field(self::safe_slug.'_db_update', '', array($this, 'settings_field_db_update'), __FILE__, 'database_section');
    }

    public function settings_section_database(){
        echo __('Here you can control all ipflag and webcounter database options:', 'pb-chartscodes');
    }

    public function settings_field_webcounterkeepdays(){
        echo '<input id="'.self::safe_slug.'_webcounterkeepdays" name="'.self::safe_slug.'_options[webcounterkeepdays]" type="text" size="3" ';
        if(isset($this->options['webcounterkeepdays'])) {
			echo ' value="'.$this->options['webcounterkeepdays'].'"';
		} else {
			echo ' value="30"';
		}
	    echo '/>';
    }

    public function settings_field_auto_update(){
        echo '<input id="'.self::safe_slug.'_auto_update" name="'.self::safe_slug.'_options[auto_update]" type="checkbox" value="1" ';
        if(isset($this->options['auto_update'])) echo 'checked="checked"';
        echo '/>';
    }



    // -----------   IP DB Update Anfang ---------------------

    public static function get_chunk_size() {
        $site_url = get_site_url();
        if (strpos($site_url, 'wp.pbcs.de') !== false) {
            return 80000;
        }
        return 800000;
    }

    public function settings_field_db_status() {
		global $wpdb;
        // Anzahl Datesätze in DB
		$ip_table = $wpdb->prefix . 'pb_ipflag_ranges';
		$count_query = "select count(*) from $ip_table";
		$numrecs = $wpdb->get_var($count_query);
        $version_option = 'pb_ipflag_db_version';
        $last_version = get_option($version_option);
		if (!empty($last_version)) {
            $gmt_offset = get_option('gmt_offset');
            $date_format = get_option('date_format');
            $time_format = get_option('time_format');
            $timestamp = strtotime($last_version . '01');
            $h_time = date_i18n($date_format . ' ' . $time_format, $timestamp + ($gmt_offset * 3600));
            echo esc_html($h_time). ' ' . ago($timestamp).' '.number_format_i18n( $numrecs, 0 ).' Datensätze';
        } else {
            echo __('Database missing or corrupted, please update', 'pb-chartscodes');
        }
    }

    public function settings_field_db_update() {
        echo '<input id="' . self::safe_slug . '_db_update" name="' . self::safe_slug . '_options[db_update]"
			class="button-secondary" type="submit" value="' . __('Update', 'pb-chartscodes') . ' ' . number_format_i18n( self::get_chunk_size(), 0 ) . ' Datensätze pro Schritt" />';
        if (get_option('pb_ipflag_offset')) {
            $next_offset = (int)get_option('pb_ipflag_offset');
            echo " &nbsp; " . esc_attr__('Import fortsetzen ab Zeile ' . $next_offset, 'pb-chartscodes');
        }
    }

    public function render_import_button() {
        $nonce = wp_create_nonce('pb_ipflag_import');
        echo '<div class="wrap">';
        echo '<h2>' . esc_html__('DB-IP Import', 'pb-chartscodes') . '</h2>';
        echo '<form method="post">';
        echo '<input type="hidden" name="pb_ipflag_action" value="import" />';
        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr($nonce) . '" />';
        echo '<p><input type="submit" class="button button-primary" value="' . esc_attr__('Import fortsetzen', 'pb-chartscodes') . '" /></p>';
        echo '</form>';
        echo '</div>';
    }

    public function handle_manual_import() {
        if ((isset($_POST['pb_ipflag_action']) && $_POST['pb_ipflag_action'] === 'import' && check_admin_referer('pb_ipflag_import')) || isset($_POST['pb_ipflag_continue'])) {
            try {
                $this->import_dbip_data();
                echo '<div class="updated"><p>' . esc_html__('Import erfolgreich ausgeführt.', 'pb-chartscodes') . '</p></div>';
            } catch (Exception $e) {
                echo '<div class="error"><p>' . esc_html($e->getMessage()) . '</p></div>';
            }
        }
    }

    public function import_dbip_data() {
        global $wpdb;
        $current_version = date('Ym');
        $version_option = 'pb_ipflag_db_version';
        $last_version = get_option($version_option);
        // Tabellennamen für countries und ip ranges
		$ip_table = $wpdb->prefix . 'pb_ipflag_ranges';
        $country_table = $wpdb->prefix . 'pb_ipflag_countries';

        $year = date('Y');
        $month = date('m');
        $url_base = "https://download.db-ip.com/free";
        $filename = "dbip-country-lite-$year-$month.csv.gz";
        $csv_url = "$url_base/$filename";
		// **************************************************** Debug mode, nächste Zeile auskommentieren importiert immer *****************************************
		if ($last_version === $current_version && !isset($_GET['force'])) {
            throw new Exception(__('Die IP-Datenbank ist bereits auf dem neuesten Stand.', 'pb-chartscodes'), 1);
        }

		// Ländertabelle neu erstellen
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$wpdb->query("DROP TABLE IF EXISTS `$country_table`;");
            $sql_country = "CREATE TABLE $country_table (
                cid INT(4) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
                code CHAR(2) NOT NULL,
                name VARCHAR(150) NOT NULL,
                nameeng VARCHAR(150) NOT NULL,
                latitude FLOAT NOT NULL,
                longitude FLOAT NOT NULL
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;";
            dbDelta($sql_country);
		require_once dirname(__FILE__) . '/iso-3166-2.php';
		foreach ($country_data as $c) {
			$wpdb->insert($country_table, [
				'code'      => $c['code'],
				'name'      => $c['name'],
				'nameeng'   => $c['nameeng'],
				'latitude'  => $c['latitude'],
				'longitude' => $c['longitude']
			]);
		}

		// Ranges Tabelle aus dem Internet laden

        $headers = @get_headers($csv_url);
        if (!$headers || strpos($headers[0], '200') === false) {
            $timestamp = strtotime('first day of previous month');
            $year = date('Y', $timestamp);
            $month = date('m', $timestamp);
            $filename = "dbip-country-lite-$year-$month.csv.gz";
            $csv_url = "$url_base/$filename";
            $current_version = date('Ym', $timestamp);
        }

        $upload_dir = wp_upload_dir();
        $base_path = trailingslashit($upload_dir['basedir']) . 'pb-ipflag/';
        if (!file_exists($base_path)) {
            wp_mkdir_p($base_path);
        }
        $gz_file = $base_path . 'dbip.csv.gz';
        $csv_file = $base_path . 'dbip.csv';

        if (!file_exists($csv_file)) {
            file_put_contents($gz_file, fopen($csv_url, 'r'));
            $gz = gzopen($gz_file, 'rb');
            $out = fopen($csv_file, 'wb');
            while (!gzeof($gz)) fwrite($out, gzread($gz, 4096));
            fclose($out); gzclose($gz); unlink($gz_file);
        }

		// Ip Ranges neu erstellen
		if (!get_option('pb_ipflag_offset')) {
            $wpdb->query("DROP TABLE IF EXISTS `$ip_table`;");
			$sql_ip = "CREATE TABLE $ip_table (
				id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
				ip_version TINYINT(1) NOT NULL,
				fromip VARBINARY(16) NOT NULL,
				toip   VARBINARY(16) NOT NULL,
				code CHAR(2) NOT NULL,
				INDEX (ip_version, fromip, toip, code)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1;";
			dbDelta($sql_ip);
        }

        $offset = (int) get_option('pb_ipflag_offset', 0);
        $line = 0;
        $count = 0;
        $chunksize = self::get_chunk_size();

        if (($input = fopen($csv_file, 'r')) !== false) {
            while (($data = fgetcsv($input, 0, ",", '"', "\\")) !== false) {
                $line++;
                if ($line <= $offset) continue;
                if ($count >= $chunksize) break;

                $startip_raw = trim($data[0] ?? '');
                $endip_raw   = trim($data[1] ?? '');
                $code        = isset($data[2]) ? strtoupper(trim($data[2])) : '';
                if (strlen($code) !== 2) continue;

                if (filter_var($startip_raw, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                    $ip_version = 4;
                } elseif (filter_var($startip_raw, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
                    $ip_version = 6;
                } else {
                    continue;
                }

                $startip = inet_pton($startip_raw);
                $endip   = inet_pton($endip_raw);
                if ($startip === false || $endip === false) continue;

                $wpdb->insert($ip_table, [
                    'ip_version' => $ip_version,
                    'fromip'     => $startip,
                    'toip'       => $endip,
                    'code'       => $code
                ]);
                $count++;
            }
            fclose($input);
        }

        if ($count > 0 && $chunksize != 800000) {
            update_option('pb_ipflag_offset', $offset + $count);
        } else {
            unlink($csv_file);
            delete_option('pb_ipflag_offset');
            update_option($version_option, $current_version);
        }
    }

    // -----------   IP DB Update Ende ---------------------

	
	
    protected function remote_timestamp(){
        $response = wp_remote_get($this->remote_ts_url, array('timeout' => self::http_timeout));
        if (!is_wp_error($response) || isset($response['body']) || $response['body'] !== '0000-00-00-00-00-00'){
            $timestamp = $this->parse_time($response['body']);
            if (is_integer($timestamp)){
                return $timestamp;
            }
        }
        throw new Exception(__('Remote ipflag database version not readable.', 'pb-chartscodes'), 7);
    }

    protected function local_timestamp(){
        $local_timestamp = 0;
        if(is_file($this->db_version_file) && is_readable($this->db_version_file)){
            $time_string = file_get_contents($this->db_version_file);
            if($time_string !== false){
                $local_timestamp = $this->parse_time($time_string);
                if(is_integer($local_timestamp)){
                    $local_timestamp = $this->parse_time($time_string);
                }
            }
        }
        return $local_timestamp;
    }

    protected function parse_time($time_string){
        $explode = explode('-', $time_string);
        $timestamp_gmt =
        gmmktime(
            intval($explode[3]) - self::remote_offset,
            intval($explode[4]),
            intval($explode[5]),
            intval($explode[1]),
            intval($explode[2]),
            intval($explode[0])
        );
        return $timestamp_gmt;
    }

    protected function parse_timestamp($timestamp){
        // Remote is in CEST(GMT/UTC+2)
        $date_gmt = date('Y-m-d-H-i-s', $timestamp + (self::remote_offset * 3600));
        return $date_gmt;
    }

}
global $ipflag;
$ipflag = new ipflag();
// ------------------------------ IPFlag Klasse Ende ----------------------------------------------------------------


// ----------------------------------- Funktionen, die in andere Plugins und themes gespiegelt sind ------------------------------------

// Zeitdifferenz ermitteln und gestern/vorgestern/morgen schreiben
//   gespiegelt in: chartcodes.php, delightful-downloads/includes/functions.php, foldergallery.php, penguin/functions.php, timeclock/includes/functions.php
if( !function_exists('ago')) {
	function ago($timestamp) {
		if (empty($timestamp)) return;
		$xlang = get_bloginfo("language");
		date_default_timezone_set('Europe/Berlin');
		$now = time();
		if ($timestamp > $now) {
			$prepo = __('in', 'penguin');
			$postpo = '';
		} else {
			if ($xlang == 'de') {
				$prepo = 'vor';
				$postpo = '';
			} else {
				$prepo = '';
				$postpo = ' ' . __('ago', 'penguin');
			}
		}
		$her = date( 'd.m.Y', intval($timestamp) );
		if ($her == date('d.m.Y',$now - (24 * 3600))) {
			$hdate = __('yesterday', 'penguin');
		} else if ($her == date('d.m.Y',$now - (48 * 3600))) {
			$hdate = __('1 day before yesterday', 'penguin');
		} else if ($her == date('d.m.Y',$now + (24 * 3600))) {
			$hdate = __('tomorrow', 'penguin');
		} else if ($her == date('d.m.Y',$now + (48 * 3600))) {
			$hdate = __('1 day after tomorrow', 'penguin');
		} else {
			$hdate = $prepo . ' ' . human_time_diff(intval($timestamp), $now) . $postpo;
		}
		return $hdate;
	}
}	

// Datumbox farbig mit Wochenende SA gelb und SO rot ausgeben aus createdatum und moddatum. wird nur createdatum gesetzt, wird nur das ausgewertet.
//   gespiegelt in: chartcodes.php, delightful-downloads/includes/functions.php, foldergallery.php, penguin/functions.php
//   Parameter 1: Erstell-Unix-Timestamp | 2: Mod-Timestamp oder NULL=Erstell-Timestamp | 3: NULL=ICON anzeigen, 1=kein Icon | 4: NULL=nur Datum, 1=Datum und AGO, 2=nur AGO
//     test:     echo colordatebox( (time()-86400), NULL, NULL, 1);

	// SA orange, Sonntag rot, gestern hellgrün, heute cyan, 30T gelb, >30T grau
	if (!function_exists('getColorStyles')) {
		function getColorStyles($timestamp) {
			$days = (int)((strtotime(date('Y-m-d', $timestamp)) - strtotime(date('Y-m-d'))) / 86400);
			$bg = match (true) {
				$days === 0   => '#bfd', // heute
				$days === -1  => '#efe', // gestern
				$days < -30   => '#eee', // vergangen >30T
				$days < 0     => '#fe8', // vergangen 1–30T
				$days <= 30   => '#bdf', // zukünftig 1–30T
				default       => '#cef', // zukünftig >30T
			};
			$weekday = (int)date('N', $timestamp);
			$fg = match ($weekday) {
				6 => '#e60', // Samstag
				7 => '#f00', // Sonntag
				default => '#222',
			};
			return ['background' => $bg, 'color' => $fg];
		}
	}

if( !function_exists('colordatebox')) {
	function colordatebox($created, $modified = null, $noicon = null, $showago = null) {
		$modified = $modified ?? $created;
		// Tauschen bei Unix Filesystemen (falls modified < created)
		$unixfile = 0;
		if ($modified < $created) {
			[$created, $modified] = [$modified, $created];
			$unixfile = 1;
		}
		// Datum formatieren
		$erstelldat = str_replace(' 00:00', '', wp_date('D d. M Y H:i', $created));
		$moddat    = str_replace(' 00:00', '', wp_date('D d. M Y H:i', $modified));
		// "vor X" Strings
		$postago = ago($created);
		$modago  = ago($modified);
		// Zeitdifferenzen berechnen
		$diffmod  = $modified - $created;
		$refTime  = $unixfile ? $created : $modified;
		$diff     = time() - $refTime;
		$diffdays = floor($diff / 86400);
		// Tooltip zusammenbauen
		$erstelltitle = __("created", "penguin") . ': ' . $erstelldat . ' ' . $postago . ' ' . $diffdays . ' Tg';
		if ($diffmod !== 0) {
			$erstelltitle .= "\n" . __("modified", "penguin") . ': ' . $moddat . ' ' . $modago;
			$erstelltitle .= "\n" . __("modified after", "penguin") . ': ' . human_time_diff($created, $modified);
		}
		// Angezeigtes Datum & Icon bestimmen
		if ($diffmod > 0 && !$unixfile) {
			$newormod = '🕰️';
			if ($showago === 2) {
				$anzeigedat = $modago;
			} elseif ($showago === 1) {
				$anzeigedat = $moddat . ' ' . $modago;
			} else {
				$anzeigedat = $moddat;
			}
			$cstyles = getColorStyles($modified);
		} else {
			$newormod = '📅';
			if ($showago === 2) {
				$anzeigedat = $postago;
			} elseif ($showago === 1) {
				$anzeigedat = $erstelldat . ' ' . $postago;
			} else {
				$anzeigedat = $erstelldat;
			}
			$cstyles = getColorStyles($created);
		}
		// HTML-Ausgabe generieren
		$colordate = '<span class="newlabel" style="background-color:' . $cstyles['background'] . '">';
		if (!isset($noicon)) {
			$colordate .= $newormod;
		}
		$colordate .= '<span style="color:' . $cstyles['color'] . '" title="' . htmlspecialchars($erstelltitle, ENT_QUOTES) . '">' . $anzeigedat . '</span></span>';
		return $colordate;
	}
}


// ---------------------------------- Spiegelung Ende ------------------------------------------------------------------------


// ========  Letze X Besucher der Seite anzeigen (nur als Admin) - pageid leer lassen für Gesamtstatistik  ===
// nur in diesem Skript, Aufruf in penguin,template-parts/meta-bottom.php
function lastxvisitors ($items,$pageid) {
	$brosicons = new ipflag();
	if (!empty($pageid)) { $pagefilter='AND postid = '.$pageid; } else {$pagefilter='';}
	global $wpdb;
	$table = $wpdb->prefix . "sitevisitors";
	$customers = $wpdb->get_results("SELECT * FROM " . $table . " WHERE datum >= DATE_ADD( NOW(), INTERVAL -90 DAY ) ".$pagefilter." ORDER BY datum desc LIMIT ".$items);
	$counts = count( $customers );
	if ($counts > 0) {
		$html ='<div class="noprint"><h6>'.__("Last Visitors","pb-chartscodes").'</h6><table>';
		foreach($customers as $customer){
			$html .= '<tr><td><abbr title="#'.$customer->id.' - '.$customer->useragent.'">' . $brosicons->showbrowosicon($customer->browser) . ' ' . $customer->browser .' ' . $customer->browserver .'</abbr></td>';
			$html .= '<td><abbr>' .$brosicons->showbrowosicon($customer->platform).' '. substr($customer->platform,0,19). ' ' . substr($customer->language,0,2) .'</abbr>';
			$html .= ' <i class="fa fa-map-marker"></i> <abbr>' . $customer->userip .'</abbr></td>';
			if ($customer->country == 'EUROPEANUNION') $customer->country = 'EU';
			$html .= '<td>' .do_shortcode('[ipflag iso="'.$customer->country.'"]') .' ';
			$html .= '<i class="fa fa-user"></i> <abbr>'. $customer->username . ' | '.$customer->usertype .'</abbr></td>';
			if (empty($pageid)) $html .= '<td><abbr><a title="Post aufrufen" href="'.get_the_permalink($customer->postid).'">' . get_the_title($customer->postid) .'</abbr></a></td>';
			$html .= '<td><span style="font-size:.9em">' . colordatebox( strtotime($customer->datum) ,NULL ,NULL,1) . '</span></td></tr>';
		}	
		$html .= '</table></div>';
		return $html;
	}	
}

// ==================== Bundesländer-Wappen der 16 dt. Bundesländer anzeigen Shortcode ======================================
function bulawappen_shortcode($atts){
	$args = shortcode_atts( array(
	      'scale' => '',     		// sm = 32px  xs=21px
	      'land' => 'Nordrhein-Westfalen',  // Bundesland oder 2 Buchstaben-Kürzel
    ), $atts );
	$buland = $args['land'];
	$bundeslaender = array (
		"BW" => "Baden-Wuerttemberg",
		"BY" =>"Bayern",
		"BE" => "Berlin",
		"BB" => "Brandenburg",
		"HB" => "Bremen",
		"HH" => "Hamburg",
		"HE" => "Hessen",
		"MV" => "Mecklenburg-Vorpommern",
		"NI" => "Niedersachsen",
		"NW" => "Nordrhein-Westfalen",
		"RP" => "Rheinland-Pfalz",
		"SL" => "Saarland",
		"SN" => "Sachsen",
		"ST" => "Sachsen-Anhalt",
		"SH" => "Schleswig-Holstein",
		"TH" => "Thueringen"
	);
	if (strlen($buland) == 2) $buix = array_search(strtoupper($buland), array_keys($bundeslaender),true);
	else $buix = array_search($buland, array_values($bundeslaender),true);
	//    echo $buix.' '. array_keys($bundeslaender)[$buix].' '.array_values($bundeslaender)[$buix];
	// Load comp freaky style for brands
	wp_enqueue_style( 'pb-complogo-style', plugin_dir_url(__FILE__ ) . 'flags/bulawappen.min.css' );
	$complogo = '<i class="fbula fbula-'.array_values($bundeslaender)[$buix].' fbula-'.$args['scale'].'" title=" Bundesland: '.array_keys($bundeslaender)[$buix].' '.array_values($bundeslaender)[$buix].'"></i>';
	return $complogo;
}
add_shortcode('bulawappen', 'bulawappen_shortcode');

// ==================== Hardwaremarkenlogos anzeigen Shortcode ======================================
function complogo_shortcode($atts){
	$args = shortcode_atts( array(
		      'scale' => '',     		// sm = 32px  xs=21px
		      'brand' => '',  // Herstellermarke
     		), $atts );
		// Load comp freaky style for brands
		wp_enqueue_style( 'pb-complogo-style', plugin_dir_url(__FILE__ ) . 'flags/computerbrands.min.css' );
		$complogo = '<a target="_blank" href="http://'.strtolower($args['brand']).'.com"><i class="comp comp-'.strtolower($args['brand']).' comp-'.$args['scale'].'" title=" Herstellerseite: '.strtoupper($atts['brand']).' aufrufen"></i></a>';
        return $complogo;
}
add_shortcode('complogo', 'complogo_shortcode');

// ==================== Automarkenlogos anzeigen Shortcode ======================================
function carlogo_shortcode($atts){
	// Load car freaky style for car
	wp_enqueue_style( 'pb-autologo-style', plugin_dir_url(__FILE__ ) . 'flags/car-logos.min.css' );
	wp_enqueue_style( 'pb-chartscodes-flagstyle', plugin_dir_url(__FILE__ ) . 'flags/freakflags.min.css' );
	$flagland = new ipflag();
	$args = shortcode_atts( array(
		      'scale' => '',     		// sm = 32px  xs=21px
		      'brand' => '0unknown',  // Autohersteller all=alle auflisten
     		), $atts );
	$brand = strtolower($args['brand']);
	// $brand="ktm";
	$carlands = 'Abarth;IT,Acura;US,AlfaRomeo;IT,Alpina;DE,Aprilia;IT,AstonMartin;GB,Audi;DE,Bentley;GB,BMW;DE,BYD;CN,Brilliance;CN,Bugatti;FR,Buick;US,Cadillac;US,Caterham;GB,Chery;CN,Chevrolet;US,Chrysler;US,Citroen;FR,Cupra;ES,Dacia;RO,Daewoo;KR,Daihatsu;JP,Datsun;JP,Dodge;US,Ferrari;IT,Fiat;IT,Ford;US,GMC;US,Geely;CN,Genesis;KR,Alpine;FR,GreatWall;CN,Haval;CN,Holden;AU,Honda;JP,Hummer;US,Hyundai;KR,Infiniti;HK,Isuzu;JP,Jaguar;GB,Jeep;US,Kia;KR,KTM;AT,Lada;RU,Lamborghini;IT,Lancia;IT,LandRover;GB,LDV;CN,Lexus;JP,Lincoln;US,Lotus;GB,Mahindra;IN,Maserati;IT,Maybach;DE,Mazda;JP,McLaren;GB,Mercedes;DE,MG;GB,Mini;GB,Morgan;GB,Mitsubishi;JP,NIO;CN,Nissan;JP,Opel;DE,Pagani;IT,Peugeot;FR,Porsche;DE,Proton;MY,Polestar;SE,RangeRover;GB,Renault;FR,Rimac;HR,RollsRoyce;GB,Rover;GB,Saab;SE,SEAT;ES,Scania;SE,SKODA;CZ,Spyker;NL,Smart;CN,SsangYong;KR,Subaru;JP,Suzuki;JP,Tesla;US,Trabant;DD,Triumph;GB,Toyota;JP,Vauxhall;GB,VW;DE,Volvo;SE,Wartburg;DD,Yugo;RS';
	$carlandarray = explode (",",$carlands);
	$carcountry = '';

	if ('all' === $brand) {
		$autologo = '';
		foreach ($carlandarray as $carland) {
			$ctryfound = explode (";",$carland);
			$carcountry = '<i class="fflag fflag-'.$ctryfound[1].' ff-sm" title="'.__('manufacturer origin')
				.' '.$ctryfound[1].'"></i> '.$flagland->country_code('de',$ctryfound[1]);
			$carurl = preg_replace('/[^a-z]/', '', strtolower($ctryfound[0]));
			$autologo .= '<div style="padding:5px;display:inline-block;height:76px;border:1px solid #ccc;">
				<a target="_blank" href="http://'.$carurl.'.com"><i class="fcar fcar-'.strtolower($ctryfound[0]).' fcar-'.$args['scale'].'" title=" Herstellerseite: '.strtolower($ctryfound[0]).' aufrufen"></i></a> '.strtoupper($ctryfound[0]).' &nbsp; '.$carcountry.'</div>';
		}
	} else {
		foreach ($carlandarray as $carland) {
			if ( str_contains(strtolower($carland),$brand) ) {
				$ctryfound = explode (";",$carland);
				$carcountry = ' <i class="fflag fflag-'.$ctryfound[1].' ff-sm" title="'.__('manufacturer origin')
					.' '.$ctryfound[1].'"></i> &nbsp; '.$flagland->country_code('de',$ctryfound[1]);
			}	
		}
		if (!empty($ctryfound[0])) $carurl = preg_replace('/[^a-z]/', '', strtolower($ctryfound[0])); else $carurl='';
		$autologo = '<a target="_blank" href="http://'.$carurl.'.com"><i class="fcar fcar-'.$brand.' fcar-'.$args['scale'].'" title=" Herstellerseite: '.$brand.' aufrufen"></i></a> '.strtoupper($ctryfound[0] ?? '').' &nbsp; '.$carcountry;
	}	
	return $autologo;
}
add_shortcode('carlogo', 'carlogo_shortcode');
?>
