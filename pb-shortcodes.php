<?php
/**
 * Charts QRCodes Barcodes Shortcode
 *
 * @package Charts QRCodes Barcodes
 * @since 0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class PB_ChartsCodes_Shortcode {

	public function __construct() 
	{
		$this->PB_ChartsCodes_create_shortcode();
	}

	
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
			$new_hex .= str_pad( dechex( $dec ) , 2, 0, STR_PAD_LEFT );
		}		
		return $new_hex;
	}


	public function farbpalette ($accolor) {
		$colorl = '';
		if ( $accolor == '1' ) {
			// Palette in Akzentfarbe
			for ($row = 0; $row < 30; ++$row) {
				$colort = get_theme_mod( 'link-color' ) ?:'#666666';
				$randd = mt_rand(1,100) /100;
				$colorl .= $this->color_luminance( $colort, $randd ) . ',';
			}
		} else {
			// Bunte Palette
			$colorli = '';
			for ($row = 0; $row < 50; ++$row) {
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
				'fontfamily' => 'Arial',
				'fontstyle' => 'normal',
			    'accentcolor' => false, 
				'colors'	=>  $this->farbpalette('0'),
			), $atts );
		$colorli= $input['colors'];
		$accentcolor=$input['accentcolor'];
		if ( $accentcolor ) { $colorli= $this->farbpalette(1); }
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
			<h3 class="pie-title"><?php echo esc_html( $title ); ?></h3>
			<canvas id="<?php echo esc_attr( $id ); ?>" width="900" height="370" style="width:100%;height:100%">
			</canvas>
		</div>
		<?php  
		if ( $absolute == '1' ){ 
			$sumperc = array_sum( $percentages );
			for ( $i = 0; $i <= count($percentages)-1; $i++ ) {
				$percentages[$i] = round($percentages[$i]/ $sumperc * 100);
			}
		}
		$tp_pie_data = array(
			'canvas_id'	=> $id,
			'percent'	=> $percentages,
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
				'fontfamily' => 'Arial',
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
				<h3 class="pie-title"><?php echo esc_html( $title ); ?></h3>
			<?php endif; ?>
			<canvas id="<?php echo esc_attr( $id ); ?>" width="900" height="370" style="width:100%;height:100%">
			</canvas>
		</div>
		<?php  
		if ( $absolute == '1' ){ 
			$sumperc = array_sum( $percentages );
			for ( $i = 0; $i <= count($percentages)-1; $i++ ) {
				$percentages[$i] = round($percentages[$i]/ $sumperc * 100);
			}
		}
		$tp_pie_data = array(
			'canvas_id'	=> $id,
			'percent'	=> $percentages,
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
				'fontfamily' => 'Arial',
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
				<h3 class="pie-title"><?php echo esc_html( $title ); ?></h3>
			<?php endif; ?>
			<canvas id="<?php echo esc_attr( $id ); ?>" width="900" height="370" style="width:100%;height:100%">
			</canvas>
		</div>
		<?php  
		if ( $absolute == '1' ){ 
			$sumperc = array_sum( $percentages );
			for ( $i = 0; $i <= count($percentages)-1; $i++ ) {
				$percentages[$i] = round($percentages[$i]/ $sumperc * 100);
			}
		}
		$tp_pie_data = array(
			'canvas_id'	=> $id,
			'percent'	=> $percentages,
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
			<h3 class="pie-title"><?php echo esc_html( $title ); ?></h3>
			<?php endif; ?>
			<div class="tp-skills-bar">
				<?php if ( $count > 0 ) :
				$hundproz = max($percentages);
				for ( $i = 0; $i <= $count; $i++ ) : 
				if ( $absolute == '1' ){
					$balkenanzeige = absint( $percentages[$i] / $hundproz * 100 );
					$balkhoehe = absint( $percentages[$i] / $hundproz * 100 );
					if ( absint( $percentages[$i]) > 0 ) { $balkenanzeige .= '% | '.absint( $percentages[$i]); }
				} else {
					$balkenanzeige = absint( $percentages[$i] );
					$balkhoehe = absint( $percentages[$i] );
				}
				?>
				<div class="outer-box" <?php echo $barbreite ?> >
					<div id="<?php echo esc_attr( $id ) . '_' . $i; ?>" class="inner-fill" style="background-color: <?php echo esc_attr( $colors[$i] ); ?>; height: <?php echo $balkhoehe . '%'; ?>">
						<div class="percent-value"><?php echo $balkenanzeige; ?></div>
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
		?>
		<div class="tp-horizontalbar" data-id="tp_horizontalbar_data_<?php echo esc_attr( $id ); ?>">
			<?php if ( ! empty( $title ) ) : ?>
				<h3 class="pie-title"><?php echo esc_html( $title ); ?></h3>
			<?php endif; ?>
			<div class="tp-skills-horizontalbar">
				<?php if ( $count > 0 ) :
					$hundproz = max($percentages);
					for ( $i = 0; $i <= $count; $i++ ) : 
						if ( $absolute == '1' ){
							$balkenanzeige = absint( $percentages[$i] / $hundproz * 100 );
							$balkhoehe = absint( $percentages[$i] / $hundproz * 100 );
							if ( absint( $percentages[$i]) > 0 ) { $balkenanzeige .= '% | '.absint( $percentages[$i]); }
						} else {
							$balkenanzeige = absint( $percentages[$i] );
							$balkhoehe = absint( $percentages[$i] );
						}
					?>
					<div class="outer-box">
						<div id="<?php echo esc_attr( $id ) . '_' . $i; ?>" class="inner-fill" style="background-color: <?php echo esc_attr( $colors[$i] ); ?>; width: <?php echo $balkhoehe . '%'; ?>">
							<span class="percent-value"><?php echo $balkenanzeige; ?></span>
							<span class="skill-name"><?php echo esc_html( $labels[$i] ); ?></span><!-- .inner-fill -->
						</div>
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
	//  Posts und Pages pro Monat für letzte 12 Monate als Bar Chart (ruft Bar chart shortcode auf)
	//
	function wpse60859_shortcode_alt_cb($atts)
	{
		$input = shortcode_atts( array(	
			'months' => 12,
		    'accentcolor' => false, 
		), $atts );
		$accentcolor=$input['accentcolor'];
		$monate = $input['months']-1; 
		$monnamen = array ("","Jan","Feb","Mär","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez");
		global $wpdb;
		$res = $wpdb->get_results(
			"SELECT MONTH(post_date) as post_month, COUNT(ID) as post_count " .
			"FROM {$wpdb->posts} " .
			"WHERE post_date BETWEEN DATE_SUB(NOW(), INTERVAL ".$monate." MONTH) AND NOW() " .
			"AND post_status = 'publish' AND post_type = 'post' " .
			"GROUP BY post_month ORDER BY post_date ASC", OBJECT_K
		);
		$valu="";
		$labl="";
		$out = '[chartscodes_bar accentcolor='.$accentcolor.' absolute="1" title="Beiträge/Seiten letzte '.$monate.' Monate" ';
		$nmonth = date('n');
		foreach($res as $r) {
			$valu .= isset($r->post_month) ? floor($r->post_count) : 0;
			$valu .= ',';
			if ($r->post_month > $nmonth) { $nyear=date('Y') - 1; } else { $nyear=date('Y'); }
			$axislink=get_home_url( '/' ).'/'.$nyear.'/'.$r->post_month;
			$labl .= '<a href='.$axislink.'>'.$monnamen[$r->post_month] .' ' . $nyear . '</a>,';
		}
		$labl = rtrim($labl,",");
		$valu = rtrim($valu,",");
		$out .= ' values="'.$valu.'" labels="'.$labl.'"]';
	// 
		return do_shortcode($out);
	}


	public function PB_ChartsCodes_create_shortcode() 
	{
		/*
		 * Create Shortcodes  für Charts und für die Post per Month Statistik
		 */
		add_shortcode( 'chartscodes', array( $this, 'PB_ChartsCodes_shortcode_function' ) );
		add_shortcode( 'chartscodes_donut', array( $this, 'PB_ChartsCodes_doughnut_shortcode_function' ) );
		add_shortcode( 'chartscodes_polar', array( $this, 'PB_ChartsCodes_polar_shortcode_function' ) );
		add_shortcode( 'chartscodes_bar', array( $this, 'PB_ChartsCodes_bar_shortcode_function' ) );
		add_shortcode( 'chartscodes_horizontal_bar', array( $this, 'PB_ChartsCodes_horizontal_bar_shortcode_function' ) );
		add_shortcode( 'posts_per_month_last', array( $this, 'wpse60859_shortcode_alt_cb' ) );
	}

}

new PB_ChartsCodes_Shortcode();
