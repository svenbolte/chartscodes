<?php
/**
* Charts QRCodes Barcodes Shortcode
* @package Charts QRCodes Barcodes
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
			//wp_enqueue_script( 'pb-chartscodes-radar-script', PB_ChartsCodes_URL_PATH . 'assets/js/radar2.js', array(), '1.9', true  );
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
					echo sprintf(__('<strong>%1s</strong> values, <strong>%2s</strong> sum of values', 'pb-chartscodes'),($count+1),$balksum).', &Oslash; <strong>'.number_format_i18n( ($balksum/($count+1)), 2 ).'</strong>';
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
		wp_enqueue_script( 'pb-chartscodes-line-script', PB_ChartsCodes_URL_PATH . 'assets/js/canvaschart.min.js', array(), '1.8', true  );
		// Load Charts QRCodes Barcodes custom line js
		wp_register_script( 'pb-chartscodes-line-initialize', PB_ChartsCodes_URL_PATH . 'assets/js/line-initialize.js', array( 'jquery', 'pb-chartscodes-script' ) );
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

// ==================== Hardwaremarkenlogos anzeigen Shortcode ======================================
function complogo_shortcode($atts){
	$args = shortcode_atts( array(
		      'scale' => '',     		// sm = 32px  xs=21px
		      'brand' => '',  // Herstellermarke
     		), $atts );
		// Load comp freaky style for brands
		wp_enqueue_style( 'pb-complogo-style', PB_ChartsCodes_URL_PATH . 'flags/computerbrands.min.css' );
		$complogo = '<a target="_blank" href="https://'.strtolower($args['brand']).'.de"><i class="comp comp-'.strtolower($args['brand']).' fc-'.$args['scale'].'" title=" Herstellerseite: '.strtoupper($atts['brand']).' aufrufen"></i></a>';
        return $complogo;
}
add_shortcode('complogo', 'complogo_shortcode');

// ==================== Automarkenlogos anzeigen Shortcode ======================================
function carlogo_shortcode($atts){
	$args = shortcode_atts( array(
		      'scale' => '',     		// sm = 32px  xs=21px
		      'brand' => '0unknown',  // Autohersteller
     		), $atts );
		// Load car freaky style for car
		wp_enqueue_style( 'pb-autologo-style', PB_ChartsCodes_URL_PATH . 'flags/car-logos.min.css' );
		$autologo = '<a target="_blank" href="https://'.strtolower($args['brand']).'.de"><i class="fcar fcar-'.strtolower($args['brand']).' fc-'.$args['scale'].'" title=" Herstellerseite: '.strtoupper($atts['brand']).' aufrufen"></i></a>';
        return $autologo;
}
add_shortcode('carlogo', 'carlogo_shortcode');


// ========================================== Shortcode Timeline from posts ====================================================

// get shortcode attributes, pass to display function
function timeline_shortcode($atts){
	$args = shortcode_atts( array(
		      'catname' => '',     		// insert slugs of all post types you want, sep by comma, empty for all types
		      'type' => 'post,wpdoodle',  // separate type slugs by comma
			  'items' => 1000,    	 	// Maximal 1000 Posts paginiert anzeigen
			  'perpage' => 20,     		// posts per page for pagination
			  'view' => 'timeline',     // set to "calendar" for calender display, to "calendar,timeline" for both 
			  'pics' => 1,        		// 1 or 0 - Show images (Category-Image, Post-Thumb or first image in post)
			  'dateformat' => 'D d.m.Y H:i',
     		), $atts );
     return display_timeline($args);
 }
add_shortcode('wp-timeline', 'timeline_shortcode');

// Calendar display month - draws a calendar for the timeline
function timeline_calendar( $month,$year,$eventarray ) {
	setlocale (LC_ALL, 'de_DE.utf8', 'de_DE@euro', 'de_DE', 'de', 'ge'); 
	$calheader = date('Y-m-d',mktime(2,0,0,$month,1,$year));
	$running_day = date('w',mktime(2,0,0,$month,1,$year));
	if ( $running_day == 0 ) { $running_day = 7; }
	$days_in_month = date('t',mktime(2,0,0,$month,1,$year));
	$days_in_this_week = 1;
	$day_counter = 0;
	$dates_array = array();
	$calendar = '<table><thead><th style="text-align:center" colspan=8>' . date_i18n('F Y', mktime(2,0,0,$month,1,$year) ) . '</th></thead>';
	$headings = array('MO','DI','MI','DO','FR','SA','SO','Kw');
	$calendar.= '<tr><td style="font-weight:700;text-align:center">'.implode('</td><td style="font-weight:700;padding:2px;text-align:center">',$headings).'</td></tr>';
	/* row for week one */
	$calendar.= '<tr style="padding:2px">';
	/* print "blank" days until the first of the current week */
	for($x = 1; $x < $running_day; $x++):
		$calendar.= '<td style="text-align:center;padding:2px;background:rgba(222,222,222,0.1);"></td>';
		$days_in_this_week++;
	endfor;
	/* keep going with days.... */
	for($list_day = 1; $list_day <= $days_in_month; $list_day++):
		$calendar.= '<td style="padding:2px;text-align:center;vertical-align:top">';
		/* add in the day number */
		$running_week = date('W',mktime(2,0,0,$month,$list_day,$year));
		$calendar.= '<div>'.$list_day.'</div>';
		/** QUERY THE DATABASE FOR AN ENTRY FOR THIS DAY !!  IF MATCHES FOUND, PRINT THEM !! **/
		foreach ($eventarray as $calevent) {
			if ( substr(get_the_time('Ymd', $calevent->ID),0,8) == date('Ymd',mktime(2,0,0,$month,$list_day,$year)) ) {
				$calendar .= '<span style="word-break:break-all;font-size:12px"><a href="' . get_permalink($calevent->ID) . '" title="'.$calevent->title.'">' . get_the_title( $calevent->ID ) . '</a></span> <br> ';
			}
		}	
		$calendar.= '</td>';
		if($running_day == 7):
			$calendar.= '<td style="text-align:center;font-size:0.9em;padding:2px">'.$running_week.'</td></tr>';
			if(($day_counter+1) != $days_in_month):
				$calendar.= '<tr>';
			endif;
			$running_day = 0;
			$days_in_this_week = 0;
		endif;
		$days_in_this_week++; $running_day++; $day_counter++;
	endfor;
	/* finish the rest of the days in the week */
	if($days_in_this_week < 8 && $days_in_this_week > 1):
		for($x = 1; $x <= (8 - $days_in_this_week); $x++):
			$calendar.= '<td style="text-align:center;padding:2px"></td>';
		endfor;
	$calendar.= '<td style="text-align:center;font-size:0.9em;padding:2px">'.$running_week.'</td></tr>';
	endif;
	$calendar.= '</table>';
	return $calendar;
}

// Differenz zwischen 2 Beiträgen
function german_time_diff( $from, $to ) {
    $diff = human_time_diff($from,$to);
    $replace = array(
        'Tagen'  => 'Tage',
        'Monaten' => 'Monate',
        'Jahren'   => 'Jahre',
    );
    return ' <i title="älter als voriger Beitrag" class="fa fa-arrows-h"></i> ' . strtr($diff,$replace);
}

// Search filter
function my_filter_post_where( $where) {
    global $wpdb;
	global $keyword;
    $where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . $keyword . '%\'';
    return $where;
}

//  display the timeline
function display_timeline($args){
	global $keyword;
	if ( isset( $_GET[ 'cat' ] ) ) { $catfilter = esc_attr($_GET["cat"]); } else { $catfilter=''; }
	if ( isset( $_GET[ 'search' ] ) ) { $keyword = esc_attr($_GET["search"]); } else { $keyword=''; }
	$out = '';
	// Kategorie-Filter von Hand
	$cargs = array(
		'show_option_none' => __( 'all', 'pb-chartscodes' ),
		'show_count'       => 1,
		'orderby'          => 'name',
		'selected' => $catfilter,
		'echo'             => 0,
	);
	$select  = wp_dropdown_categories( $cargs ); 
	$replace = "<select$1 onchange='return this.form.submit()'>";
	$select  = preg_replace( '#<select([^>]*)>#', $replace, $select );
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	$post_args = array(
		'suppress_filters' => false, // important!
		'post_type' => explode( ',', $args['type'] ),
		'numberposts' => $args['items'],
		'posts_per_page' => $args['perpage'],
		'paged' => $paged,
		'page' => $paged,
		'category_name' => $args['catname'],
		'category' =>  $catfilter,
		'orderby' => 'post_date',
		'order' => 'DESC',
		'post_status' => 'publish',
	);
	$tpostarg = array(
		'suppress_filters' => false, // important!
		'numberposts' => -1,
		'post_type' => explode( ',', $args['type'] ),
		'category_name' => $args['catname'],
		'category' =>  $catfilter,
		'post_status' => 'publish',
	);
	add_filter( 'posts_where', 'my_filter_post_where' );
	$tpostcount = count(get_posts( $tpostarg ));
	if ( $tpostcount > intval($args['items']) ) $tpostcount = intval($args['items']);
	$out.= '<div style="text-align:right"><form name="finder" method="get">'.__('number of posts','pb-chartscodes').': '.$tpostcount;
	if (empty($args['catname'])) {
		$out .= ' '.$select; 
		$out .= '<noscript><input type="submit" value="View" /></noscript>';
	}	
	$out.= ' <input type="text" placeholder="Suchbegriff" name="search" id="search" value="'.$keyword.'"> ';
	$out.='</select><input class="noprint" type="submit" value="'. __( 'search', 'pb-chartscodes' ).'" />';
	$out .= '</form></div>';
	$posts = get_posts( $post_args );
	remove_filter( 'posts_where', 'my_filter_post_where' );
	if ( strpos($args['view'], "calendar") !== false ) {
		/// Cal Aufruf
		$outputed_values = array();
		foreach ($posts as $calevent) {
			$workername = substr(get_the_time('Ymd', $calevent->ID),0,6);
			if (!in_array($workername, $outputed_values)){
				$mdatum = substr(get_the_time('Ymd', $calevent->ID),0,4).'-'. substr(get_the_time('Ymd', $calevent->ID),4,2).'-'.substr(get_the_time('Ymd', $calevent->ID),6,2);
				$out .= timeline_calendar(date("m", strtotime($mdatum)),date("Y", strtotime($mdatum)),$posts);
				array_push($outputed_values, $workername);
			}	
		}
	}
	if ( strpos($args['view'], "timeline") !== false ) {	
		$out .=  '<div id="timeline">';
		$out .=   '<ul style="background:url(\''.PB_ChartsCodes_URL_PATH.'/Image/ul-bg.png\') center top repeat-y;">';
		$prevdate = '';
		foreach ( $posts as $post ) : setup_postdata($post);
			$out .=  '<li><div>';
			$out .=  '<span class="timeline-datebild" style="background-color:'. get_theme_mod( 'link-color', '#888' ) .'">';
			$out .=  get_the_time( 'D', $post->ID ).'<br><span style="font-size:1.5em;color:#fff">'.get_the_time( 'd', $post->ID ).'</span><br>'.get_the_time( 'M', $post->ID );
			$out .=  '</span>';
			$cuttext = get_the_title($post->ID);
			if (strlen($cuttext) > 42) { $cuttext=substr(get_the_title($post->ID), 0, 27) . '&mldr;' . substr(get_the_title($post->ID), -15);	}	
			if (  $args['pics'] == 1 ) {
				$out .=  '<div class="timeline-image post-thumbnail">';
				if ( has_post_thumbnail( $post->ID ) ) {
					$out .=  get_the_post_thumbnail( $post->ID, 'large' );
				} else {
					$first_img='';
					$category = get_the_category($post->ID);
					$output = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', get_the_content(), $matches);
					if ($output) { $first_img = '<img src="'. $matches[1][0] . '">'; } else { 
						if ( has_post_thumbnail() == false ) {
							if ( class_exists('ZCategoriesImages') && !empty($category) && z_taxonomy_image_url($category[0]->term_id) != NULL ) {
								$cbild = z_taxonomy_image_url($category[0]->term_id);
								$first_img = '<img src="' . $cbild . '">';	
							} 
						} else {
							$cbild = get_the_post_thumbnail_url();
							$first_img = '<img src="' . $cbild . '">';	
						}
					}
					$out .= $first_img. '<a style="color:#fff;text-shadow:1px 1px 1px #000" href="' . get_permalink($post->ID) . '"><div class="middle" style="top:45%">'.__( "Continue reading", "penguin" ).' &raquo;</a></div>';
				}	
				$out .=  '<div class="timeline-title"><nobr><a style="font-size:1.2em" href="' . get_permalink($post->ID) . '" title="'.$post->title.'">';
				$out .=  ' '.$cuttext. '</a></nobr></div>';
				$out .= '</div>';
			} else {
				$out .=  '<nobr><h6 class="headline" style="margin-right:8px;overflow:hidden"><a href="' . get_permalink($post->ID) . '" title="'.$post->title.'">';
				$out .=  ' '.$cuttext. '</a></h6></nobr>';
			}
			if (  $args['pics'] == 1 ) { $imgon=''; $exwordcount = 15; } else { $imgon ='noimages'; $exwordcount = 30; }
			$out .= '<span class="timeline-text '.$imgon.'" style="background-color:'. get_theme_mod( 'link-color', '#eeeeee' ). '22' .'"><abbr>';
			if ( !empty($prevdate)) $out .= german_time_diff($prevdate,get_the_time( 'U', $post->ID )).' &nbsp; ';
			// Datum-Statistik des Posts mit Farbdarstellung <14Tg alt
			$diff = time() - get_post_time('U', false, $post, true);
			if (round((intval($diff) / 86400), 0) < 30) {
				$newcolor = "#FFD800";
			} else {
				$newcolor = "transparent";
			}
			$erstelldat = get_post_time('l, d. M Y H:i:s', false, $post, true);
			$postago = ago(get_post_time('U, d. F Y H:i:s', false, $post, true));
			$moddat = get_the_modified_time('l, d. M Y H:i:s', false, $post, true);
			$modago = ago(get_the_modified_time('U, d. F Y H:i:s', false, $post, true));
			$diffmod = get_the_modified_time('U', false, $post, true) - get_post_time('U', false, $post, true);
			$erstelltitle = 'erstellt: ' . $erstelldat . ' ' . $postago;
			if ($diffmod > 0) {
				$erstelltitle.= '&#10;verändert: ' . $moddat . ' ' . $modago;
				$erstelltitle.= '&#10;verändert nach: ' . human_time_diff(get_post_time('U', false, $post, true), get_the_modified_time('U', false, $post, true));
			}
			if ($diffmod > 86400) {
				$newormod = 'fa fa-calendar-plus-o';
			} else {
				$newormod = 'fa fa-calendar-o';
			}
			$out .= '<i style="background-color:' . $newcolor . '" title="' . $erstelltitle . '" class="' . $newormod . '"></i> ';
			if (is_singular()) {
				if ($diffmod > 0) {
					$out.= '<span title="' . $erstelltitle . '">' . get_the_modified_time(get_option('date_format'), false, $post, true) . ' ' . $modago . '</span>';
				} else {
					$out.= '<span title="' . $erstelltitle . '">' . get_post_time(get_option('date_format'), false, $post, true) . ' ' . $postago . '</span>';
				}
			}
			if (empty($catfilter) || $catfilter == -1) $out .= '&nbsp; <i class="fa fa-folder-o"></i> '.get_the_category($post->ID)[0]->name;
			$out .=  ' &nbsp; <i class="fa fa-newspaper-o"></i> '.wp_trim_words(get_the_excerpt( $post->ID ), $exwordcount );
			$out .=  '</abbr></span>';
			$out .=  '</div></li>';
			$prevdate = get_the_time( 'U', $post->ID );
		endforeach;
		$out .=  '</ul>';
		$out .=  '</div> <!-- #timeline -->';
	}	
	$big = 999999999; // need an unlikely integer
	$out .= '<div class="nav-links" style="text-align:center">'.paginate_links( array(
		'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
		'format' => '?paged=%#%',
		'current' => max( 1, get_query_var('paged') ),
		'total' => intval($tpostcount / $args['perpage']) + 1,
	) );
	$out .= '</div>';
	wp_reset_postdata();
	return $out;
}
?>
