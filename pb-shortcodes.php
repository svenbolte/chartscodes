<?php
/**
* Charts Shortcodes
* 	'chartscodes', 'chartscodes_radar', 'chartscodes_donut' 'chartscodes_polar'
*	'chartscodes_bar', 'chartscodes_horizontal_bar','posts_per_month_last'
*	'pb_last_months_chart', 'chartscodes_line'
*/

if ( ! defined( 'ABSPATH' ) ) {	exit; } // Exit if accessed directly.

class PB_ChartsCodes_Shortcode {

	public function __construct() {	$this->PB_ChartsCodes_create_shortcode(); }
	
	// Für die Akzentfarbe Nunancen errechnen
	public function color_luminance( $hex, $percent ) {
		// validate hex string
		$hex = preg_replace( '/[^0-9a-f]/i', '', $hex );
		$new_hex = '#';
		if ( strlen( $hex ) < 6 ) {
			$hex = $hex[0] + $hex[0] + $hex[1] + $hex[1] + $hex[2] + $hex[2];
		}
		// convert to decimal and change luminosity
		for ($i = 0; $i < 3; $i++) {
			$dec = hexdec( substr( $hex, $i*2, 2 ) );
			$dec = min( max( 0, $dec + $dec * $percent ), 255 ); 
			$new_hex .= str_pad( dechex( (int) $dec ) , 2, 0, STR_PAD_LEFT );
		}		
		return $new_hex;
	}

	public function farbpalette ($accolor) {
		$colorl = '';
		if ( $accolor == '1' ) {
			// Palette in Akzentfarbe
			$colort = get_theme_mod( 'link-color' ) ?:'#666666';
			for ($row = 0; $row < 200; ++$row) {
				$randd = mt_rand(1,100) /100;
				$colorl .= $this->color_luminance( $colort, $randd ) . ',';
			}
		} else {
			// Bunte Palette
			$colorli = '';
			for ($row = 0; $row < 200; ++$row) {
				$colorl .= sprintf('#%06X', mt_rand(0xAAAAAA, 0xEEEEEE)) . ',';
			}
		}
	return rtrim($colorl,",");
	}

	//	
	//	Default Pie Chart Shortcode Function
	//	
	public function PB_ChartsCodes_shortcode_function( $atts ) 	{
		ob_start();
		$input = shortcode_atts( array(
				'title'		=> '',
				'absolute' => '',
				'values' 	=> '',
				'labels'	=> '',
				'fontfamily' => 'Arial, sans-serif',
				'fontstyle' => 'normal',
			    'accentcolor' => false, 
				'colors'	=>  $this->farbpalette('0'),
			), $atts );
		$colorli= $input['colors'];
		$accentcolor=$input['accentcolor'];
		if ( $accentcolor ) { $colorli= $this->farbpalette(1); }
		$pvalues = array();
		$quotes = array( "\"", "'" );
		$absolute 		= $input['absolute']; 
		$title 			= $input['title']; 
		$fontfamily 	= esc_attr( $input['fontfamily'] ); 
		$fontstyle 		= esc_attr( $input['fontstyle'] ); 
		$percentages 	= explode( ',', str_replace( $quotes, '', $input['values'] ) );
		$labels 		= explode( ',', str_replace( "\"", '', $input['labels'] ) );
		$colors 		= explode( ',', str_replace( $quotes, '', $colorli ) );
		$radius			= array( 120, 120, 120, 120, 120, 120, 120, 120, 120, 120,120 );
		$id 			= uniqid( 'tp_pie_', false ); 
		?>
		<div class="tp-piebuilderWrapper" data-id="tp_pie_data_<?php echo esc_attr( $id ); ?>">
			<h6 class="pie-title"><?php echo esc_html( $title ); ?></h6>
			<canvas id="<?php echo esc_attr( $id ); ?>" width="900" height="400" style="max-width:100vw;max-height:400px;object-fit:contain">
			</canvas>
		</div>
		<?php  
		$sumperc = array_sum( $percentages );
		if (intval($sumperc) > 0) {
			if ( $absolute == '1' ){ 
				for ( $i = 0; $i <= count($percentages)-1; $i++ ) {
					$pvalues[$i] = $percentages[$i];
					$percentages[$i] = round($percentages[$i]/ $sumperc * 100);
				}
			} else { $pvalues = array ('','','','','','','','','','','','','','','','','');}
			$tp_pie_data = array(
				'canvas_id'	=> $id,
				'percent'	=> $percentages,
				'percvalues'   => $pvalues,
				'label'		=> $labels,
				'color'		=> $colors,
				'circle'	=> 0,
				'radius'	=> $radius,
				'fontstyle'	=> $fontstyle,
				'fontfamily' => $fontfamily,
				);

			wp_localize_script( 'pb-chartscodes-initialize', 'tp_pie_data_'.$id, $tp_pie_data );
			// enqueue bar js
			wp_enqueue_script( 'pb-chartscodes-initialize' );
		}
		return ob_get_clean();
	}

	//
	//  Donut Pie Chart Shortcode Function
	//
	public function PB_ChartsCodes_doughnut_shortcode_function( $atts ) {
		ob_start();
		$input = shortcode_atts( array(
				'title'		=> '',
				'absolute' => '',
				'values' 	=> '',
				'labels'	=> '',
				'fontfamily' => 'Arial, sans-serif',
				'fontstyle' => 'normal',
			    'accentcolor' => false, 
				'colors'	=>  $this->farbpalette('0'),
			), $atts );
		$colorli= $input['colors'];
		$accentcolor=$input['accentcolor'];
		if ( $accentcolor ) { $colorli= $this->farbpalette(1); }
		$quotes = array( "\"", "'" );
		$title 			= $input['title']; 
		$absolute 		= $input['absolute']; 
		$fontfamily 	= esc_attr( $input['fontfamily'] ); 
		$fontstyle 		= esc_attr( $input['fontstyle'] ); 
		$percentages 	= explode( ',', str_replace( $quotes, '', $input['values'] ) );
		$labels 		= explode( ',', str_replace( "\"", '', $input['labels'] ) );
		$colors 		= explode( ',', str_replace( $quotes, '', $colorli ) );
		$radius			= array( 120, 120, 120, 120, 120, 120, 120, 120, 120, 120, 120 );
		$id 			= uniqid( 'tp_doughnut_', false ); 
		?>
		<div class="tp-piebuilderWrapper" data-id="tp_pie_data_<?php echo esc_attr( $id ); ?>">
			<?php if ( ! empty( $title ) ) : ?>
				<h6 class="pie-title"><?php echo esc_html( $title ); ?></h6>
			<?php endif; ?>
			<canvas id="<?php echo esc_attr( $id ); ?>" width="900" height="400" style="max-width:100vw;max-height:400px;object-fit:contain">
			</canvas>
		</div>
		<?php  
		$sumperc = array_sum( $percentages );
		if (intval($sumperc) > 0) {
			if ( $absolute == '1' ){ 
				for ( $i = 0; $i <= count($percentages)-1; $i++ ) {
					$pvalues[$i] = $percentages[$i];
					$percentages[$i] = round($percentages[$i]/ $sumperc * 100);
				}
			}
			$tp_pie_data = array(
				'canvas_id'	=> $id,
				'percent'	=> $percentages,
				'percvalues'   => $pvalues,
				'label'		=> $labels,
				'color'		=> $colors,
				'circle'	=> 45,
				'radius'	=> $radius,
				'fontstyle'	=> $fontstyle,
				'fontfamily' => $fontfamily,
				);

			wp_localize_script( 'pb-chartscodes-initialize', 'tp_pie_data_'.$id, $tp_pie_data );
			// enqueue bar js
			wp_enqueue_script( 'pb-chartscodes-initialize' );
		}
		return ob_get_clean();
	}

	//
	//  Polar Pie Chart Shortcode Function
	//
	public function PB_ChartsCodes_polar_shortcode_function( $atts ) {
		ob_start();
		$input = shortcode_atts( array(
				'title'		=> '',
				'absolute' => '',
				'values' 	=> '',
				'labels'	=> '',
				'fontfamily' => 'Arial, sans-serif',
				'fontstyle' => 'normal',
			    'accentcolor' => false, 
				'colors'	=>  $this->farbpalette('0'),
			), $atts );
		$colorli= $input['colors'];
		$accentcolor=$input['accentcolor'];
		if ( $accentcolor ) { $colorli= $this->farbpalette(1); }
		$quotes = array( "\"", "'" );
		$title 			= $input['title']; 
		$absolute 		= $input['absolute']; 
		$fontfamily 	= esc_attr( $input['fontfamily'] ); 
		$fontstyle 		= esc_attr( $input['fontstyle'] ); 
		$percentages 	= explode( ',', str_replace( $quotes, '', $input['values'] ) );
		$labels 		= explode( ',', str_replace( "\"", '', $input['labels'] ) );
		$colors 		= explode( ',', str_replace( $quotes, '', $colorli ) );
		$radius			= array( 125, 135, 130, 140, 135, 130, 120, 130, 140, 130 );
		$id 			= uniqid( 'tp_polar_', false ); 
		?>
		<div class="tp-piebuilderWrapper" data-id="tp_pie_data_<?php echo esc_attr( $id ); ?>">
			<?php if ( ! empty( $title ) ) : ?>
				<h6 class="pie-title"><?php echo esc_html( $title ); ?></h6>
			<?php endif; ?>
			<canvas id="<?php echo esc_attr( $id ); ?>" width="900" height="400" style="max-width:100vw;max-height:400px;object-fit:contain">
			</canvas>
		</div>
		<?php  
		$sumperc = array_sum( $percentages );
		if (intval($sumperc) > 0) {
			if ( $absolute == '1' ){ 
				for ( $i = 0; $i <= count($percentages)-1; $i++ ) {
					$pvalues[$i] = $percentages[$i];
					$percentages[$i] = round($percentages[$i]/ $sumperc * 100);
				}
			}
			$tp_pie_data = array(
				'canvas_id'	=> $id,
				'percent'	=> $percentages,
				'percvalues'   => $pvalues,
				'label'		=> $labels,
				'color'		=> $colors,
				'circle'	=> 15,
				'radius'	=> $radius,
				'fontstyle'	=> $fontstyle,
				'fontfamily' => $fontfamily,
				);

			wp_localize_script( 'pb-chartscodes-initialize', 'tp_pie_data_'.$id, $tp_pie_data );
			// enqueue bar js
			wp_enqueue_script( 'pb-chartscodes-initialize' );
		}	
		return ob_get_clean();
	}

	//	
	//	Radar Chart Shortcode Function (absolute values given)
	//	
	public function PB_ChartsCodes_radar_shortcode_function( $atts ) 	{
		ob_start();
		$input = shortcode_atts( array(
				'title'		=> '',
				'absolute' => '',
				'values' 	=> '',
				'labels'	=> '',
				'legend'	=> true,
				'fontfamily' => 'Arial, sans-serif',
				'fontstyle' => 'normal',
			    'accentcolor' => false, 
				'colors'	=>  $this->farbpalette('0'),
			), $atts );
		$colorli= $input['colors'];
		$accentcolor=$input['accentcolor'];
		if ( $accentcolor ) { $colorli= $this->farbpalette(1); }
		$pvalues = array();
		$quotes = array( "\"", "'" );
		$title 			= $input['title']; 
		$fontfamily 	= esc_attr( $input['fontfamily'] ); 
		$fontstyle 		= esc_attr( $input['fontstyle'] ); 
		$percentages 	= explode( ',', str_replace( $quotes, '', $input['values'] ) );
		$labels 		= explode( ',', str_replace( "\"", '', $input['labels'] ) );
		$colors 		= explode( ',', str_replace( $quotes, '', $colorli ) );
		$id 			= uniqid( 'tp_pie_', false ); 
		$legend			= esc_attr( $input['legend'] );
		echo '<div class="tp-RadarbuilderWrapper"'.$id.' style="max-width:100vw;max-height:500px;object-fit:contain">';
		$sumperc = array_sum( $percentages );
		if (intval($sumperc) > 0) {
			$radarlegende = '';
			$radarval = 'values: {';
			for ( $i = 0; $i <= count($percentages)-1; $i++ ) {
				$pvalues[$i] = $percentages[$i];
				$percentages[$i] = round($percentages[$i]/ $sumperc * 100);
				$radarval .= '"'.$labels[$i].'": '.$percentages[$i].',';
				$radarlegende  .= $labels[$i].': '.$pvalues[$i].' &nbsp; ';
			}
			$radarval .= '}, ';
			$colort = get_theme_mod( 'link-color' ) ?:'#666666';
			list($r, $g, $b) = sscanf($colort, "#%02x%02x%02x");
			$radcolor = "$r, $g, $b";
			// Load radar js
			//wp_enqueue_script( 'pb-chartscodes-radar-script', PB_ChartsCodes_URL_PATH . 'js/radar2.js', array(), '1.9', true  );
			wp_enqueue_script( 'pb-chartscodes-radar' );
			// pass values to radar
			$radaris = '	jQuery(function($){
				var radardata ={color: ['.$radcolor.'], size: [900, 500], step: 1, title: "'.esc_html( $title ).'",'.$radarval.'showAxisLabels: true };
				$(".tp-RadarbuilderWrapper").radarChart(radardata);	});';
			wp_add_inline_script('pb-chartscodes-radar',$radaris);
			echo '</div>';
			if ( $legend ) echo '<div style="border:1px solid #eee;border-radius:3px;padding:3px;font-size:0.8em;text-align:center">'.$radarlegende.' TOTAL: '.number_format($sumperc,0,'.',',').'</div>';
		}
		return ob_get_clean();
	}

	//
	//  vertical Bar Graph Shortcode Function
	//
	public function PB_ChartsCodes_bar_shortcode_function( $atts ) {
		ob_start();
		$input = shortcode_atts( array(
				'title'		=> '',
			    'absolute' => '',
			    'values' 	=> '',
			    'labels'	=> '',
			    'accentcolor' => false, 
				'colors'	=>  $this->farbpalette('0'),
			), $atts );
		$colorli= $input['colors'];
		$accentcolor=$input['accentcolor'];
		if ( $accentcolor ) { $colorli= $this->farbpalette(1); }
		$quotes = array( "\"", "'" );
		$title 			= $input['title']; 
		$absolute 		= $input['absolute']; 
		$percentages 	= explode( ',', str_replace( $quotes, '', $input['values'] ) );;
		$labels 		= explode( ',', str_replace( "\"", '', $input['labels'] ) );
		$colors 		= explode( ',', str_replace( $quotes, '', $colorli ) );
		$count 			= count( $labels )-1;
		$id 			= uniqid( 'tp_bar_', false ); 
		if ( $count > 12 ) { $barbreite = ' style="min-width:24px;max-width:'.intval(100/($count+3)).'%"'; } else { $barbreite=''; }
		?>
		<div class="tp-bar" data-id="tp_bar_data_<?php echo esc_attr( $id ); ?>">
			<?php if ( ! empty( $title ) ) : ?>
			<h6 class="pie-title"><?php echo esc_html( $title ); ?></h6>
			<?php endif; ?>
			<div class="tp-skills-bar">
				<?php if ( $count > 0 ) :
				$hundproz = max($percentages);
				for ( $i = 0; $i <= $count; $i++ ) : 
				if ( $absolute == '1' ){
					$balkenanzeige = absint( $percentages[$i] / $hundproz * 100 );
					$balkhoehe = absint( $percentages[$i] / $hundproz * 100 );
					if ( absint( $percentages[$i]) > 0 ) { $balkenanzeige .= '% '.absint( $percentages[$i]); }
				} else {
					$balkenanzeige = absint( $percentages[$i] );
					$balkhoehe = absint( $percentages[$i] );
				}
				?>
				<div class="outer-box" <?php echo $barbreite ?> >
					<span class="percent-value"><?php echo $balkenanzeige; ?></span>
					<div id="<?php echo esc_attr( $id ) . '_' . $i; ?>" class="inner-fill" style="background-color: <?php echo esc_attr( $colors[$i] ); ?>; height: <?php echo $balkhoehe . '%'; ?>">
					</div><!-- .inner-fill -->
					<?php
					if (strpos($labels[$i], '<a href') !== false ) {
						echo '<span class="tp-axislabels">'.( substr($labels[$i],0,100) ).' </span>';
					} else {
						echo '<span class="tp-axislabels">'.esc_html( substr($labels[$i],0,15) ).' </span>';
					}
					?>
				</div><!-- .outer-box -->
				<?php 
				endfor;  
				endif; ?>
			</div><!-- .skills-bar -->
		</div>
		<?php 
		return ob_get_clean();
	}

	//
	//  Horizontal Bar Graph Shortcode Function
	//
	public function PB_ChartsCodes_horizontal_bar_shortcode_function( $atts ) {
		ob_start();
		$input = shortcode_atts( array(
				'title'		=> '',
				'absolute' => '',
				'values' 	=> '',
				'labels'	=> '',
			    'accentcolor' => false, 
				'colors'	=>  $this->farbpalette('0'),
			), $atts );
		$colorli= $input['colors'];
		$accentcolor=$input['accentcolor'];
		if ( $accentcolor ) { $colorli= $this->farbpalette(1); }
		$quotes = array( "\"", "'" );
		$title 			= $input['title']; 
		$absolute 		= $input['absolute']; 
		$percentages 	= explode( ',', str_replace( $quotes, '', $input['values'] ) );;
		$labels 		= explode( ',', str_replace( "\"", '', $input['labels'] ) );
		$colors 		= explode( ',', str_replace( $quotes, '', $colorli ) );
		$count 			= count( $labels )-1;
		$id 			= uniqid( 'tp_horizontalbar_', false ); 
		$balksum = 0;
		?>
		<div class="tp-horizontalbar" data-id="tp_horizontalbar_data_<?php echo esc_attr( $id ); ?>">
			<?php if ( ! empty( $title ) ) : ?>
				<h6 class="pie-title"><?php echo esc_html( $title ); ?></h6>
			<?php endif; ?>
			<div class="tp-skills-horizontalbar">
				<?php if ( $count > 0 ) :
					$hundproz = max($percentages);
					for ( $i = 0; $i <= $count; $i++ ) : 
						if ( $absolute == '1' ){
							$balkenanzeige = @absint( $percentages[$i] / $hundproz * 100 );
							$balkhoehe = @absint( $percentages[$i] / $hundproz * 100 );
							$balksum += @absint($percentages[$i]);
							if ( @absint( $percentages[$i]) > 0 ) { $balkenanzeige .= '% &nbsp; '.@absint( $percentages[$i]); }
						} else {
							$balkenanzeige = absint( $percentages[$i] );
							$balkhoehe = absint( $percentages[$i] );
							$balksum += $balkhoehe;
						}
					?>
					<div class="outer-box">
						<div id="<?php echo esc_attr( $id ) . '_' . $i; ?>" class="inner-fill" style="background-color: <?php echo esc_attr( $colors[$i] ); ?>; width: <?php echo $balkhoehe . '%'; ?>">
							<span class="skill-name"><?php echo esc_html( $labels[$i] . ' &nbsp; &nbsp;  &nbsp; ' . $balkenanzeige ); ?></span><!-- .inner-fill -->
						</div>
					</div><!-- .outer-box -->
					<?php 
					endfor;  
					echo sprintf(__('<strong>%1s</strong> values, <strong>%2s</strong> sum of values', 'pb-chartscodes'),number_format_i18n(($count+1),0),number_format_i18n($balksum,0)).', &Oslash; <strong>'.number_format_i18n( ($balksum/($count+1)), 2 ).'</strong>';
				endif; ?>
			</div><!-- .skills-bar -->
		</div>
		<?php 
		return ob_get_clean();
	}

	//
	//  Neu erstellte Posts und Pages pro Monat für letzte xx Monate als Bar Chart (ruft Bar chart shortcode auf)
	//
	function pb_last_months_chart($atts) {
		$input = shortcode_atts( array(	
			'months' => 15,
		    'accentcolor' => false, 
		), $atts );
		$accentcolor=$input['accentcolor'];
		$monate = $input['months']; 
		$pmod = 'post_date';
		// if ( 1 === get_theme_mod( 'homesortbymoddate' ) ) {	$pmod = 'post_modified'; } else { $pmod = 'post_date'; }	
		global $wpdb;
		$res = $wpdb->get_results("SELECT DISTINCT MONTH( post_date ) AS month, YEAR( post_date ) AS year, COUNT( id ) as post_count FROM $wpdb->posts WHERE post_status = 'publish' and post_type = 'post' GROUP BY month, year ORDER BY post_date DESC LIMIT ".$monate);
		$valu="";
		$labl="";
		$out = '[chartscodes_bar accentcolor='.$accentcolor.' absolute="1" title="Beiträge/Seiten letzte '.$monate.' Monate" ';
		foreach($res as $r) {
			$valu .= isset($r->month) ? floor($r->post_count) : 0;
			$valu .= ',';
			$axislink=get_home_url( '/' ).'/'.$r->year.'/'.$r->month;
			$labl .= '<a href='.$axislink.'>'.date_i18n("M y", mktime(2, 0, 0, $r->month, 1, $r->year)).'</a>,';
		}
		$labl = rtrim($labl,",");
		$valu = rtrim($valu,",");
		$out .= ' values="'.$valu.'" labels="'.$labl.'"]';
		return do_shortcode($out);
	}

	//	
	//	Chartscodes Line Chart Shortcode Function
	//	
	public function PB_ChartsCodes_line_shortcode_function( $atts ) 	{
		ob_start();
		$input = shortcode_atts( array(
				'title'		=> '',
				'xaxis' => 'Einheit',
				'yaxis' => 'Wert',
				'height' 	=> '350',
				'values' 	=> '',
				'labels'	=> '',
				'fontfamily' => 'Arial, sans-serif',
				'fontstyle' => 'normal',
			    'accentcolor' => false, 
				'colors'	=>  $this->farbpalette('0'),
			), $atts );
		$colorli= $input['colors'];
		$accentcolor=$input['accentcolor'];
		if ( $accentcolor ) { $colorli= $this->farbpalette(1); }
		$quotes = array( "\"", "'" );
		$title 			= $input['title']; 
		$height 			= $input['height']; 
		$fontfamily 	= esc_attr( $input['fontfamily'] ); 
		$fontstyle 		= esc_attr( $input['fontstyle'] ); 
		$percentages 	= explode( ',', str_replace( $quotes, '', $input['values'] ) );
		$labels 		= explode( ',', str_replace( "\"", '', $input['labels'] ) );
		$colors 		= explode( ',', str_replace( $quotes, '', $colorli ) );
		$id 			= uniqid( 'tp_line_', false ); 
		?>
		<div class="tp-linebuilderWrapper" data-id="tp_pie_data_<?php echo esc_attr( $id ); ?>">
			<h6 class="pie-title"><?php echo esc_html( $title ); ?></h6>
			<canvas id="<?php echo esc_attr( $id ); ?>" style="width:100%;height:<?php echo $height; ?>px" >
			</canvas>
			<script>
			var canvas =  document.getElementById("<?php echo esc_attr( $id ); ?>");
			canvas.width = canvas.clientWidth;
			canvas.height = canvas.clientHeight;
			var context = canvas.getContext("2d");
			</script>
		</div>
		<?php  
		$datapts='[ ';
		for ( $i = 0; $i <= count($percentages)-1; $i++ ) {
			$datapts .= "{ x:'".$labels[$i]."', y:".$percentages[$i]."}, ";
		}
		$datapts .= ' ]';
		$tp_pie_data = array(
			'xaxis'	=> $input['xaxis'], 
			'yaxis'	=> $input['yaxis'], 
			'canvas_id'	=> $id,
			'color'		=> $colors,
			'datapts'	=> $datapts,
			'fontfamily' => $fontfamily,
			);
		// Load Charts QRCodes Barcodes line js
		wp_enqueue_script( 'pb-chartscodes-line-script', PB_ChartsCodes_URL_PATH . 'js/canvaschart.min.js', array(), '1.8', true  );
		// Load Charts QRCodes Barcodes custom line js
		wp_register_script( 'pb-chartscodes-line-initialize', PB_ChartsCodes_URL_PATH . 'js/line-initialize.js', array( 'jquery', 'pb-chartscodes-script' ) );
		// Fill data
		wp_localize_script( 'pb-chartscodes-line-initialize', 'tp_pie_data_'.$id, $tp_pie_data );
		// enqueue bar js
		wp_enqueue_script( 'pb-chartscodes-line-initialize' );
		return ob_get_clean();
	}

	//
	// Create Shortcodes  für Charts und für die Post per Month Statistik
	//
	public function PB_ChartsCodes_create_shortcode() {
		add_shortcode( 'chartscodes', array( $this, 'PB_ChartsCodes_shortcode_function' ) );
		add_shortcode( 'chartscodes_radar', array( $this, 'PB_ChartsCodes_radar_shortcode_function' ) );
		add_shortcode( 'chartscodes_donut', array( $this, 'PB_ChartsCodes_doughnut_shortcode_function' ) );
		add_shortcode( 'chartscodes_polar', array( $this, 'PB_ChartsCodes_polar_shortcode_function' ) );
		add_shortcode( 'chartscodes_bar', array( $this, 'PB_ChartsCodes_bar_shortcode_function' ) );
		add_shortcode( 'chartscodes_horizontal_bar', array( $this, 'PB_ChartsCodes_horizontal_bar_shortcode_function' ) );
		add_shortcode( 'posts_per_month_last', array( $this, 'pb_last_months_chart' ) );
		add_shortcode( 'chartscodes_line', array( $this, 'PB_ChartsCodes_line_shortcode_function' ) );
	}
}
new PB_ChartsCodes_Shortcode();
?>
