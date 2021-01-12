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
			<h6 class="pie-title"><?php echo esc_html( $title ); ?></h3>
			<canvas id="<?php echo esc_attr( $id ); ?>" width="900" height="370" style="width:100%;height:100%">
			</canvas>
		</div>
		<?php  
		$sumperc = array_sum( $percentages );
		if (intval($sumperc) > 0) {
			if ( $absolute == '1' ){ 
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
				<h6 class="pie-title"><?php echo esc_html( $title ); ?></h3>
			<?php endif; ?>
			<canvas id="<?php echo esc_attr( $id ); ?>" width="900" height="370" style="width:100%;height:100%">
			</canvas>
		</div>
		<?php  
		$sumperc = array_sum( $percentages );
		if (intval($sumperc) > 0) {
			if ( $absolute == '1' ){ 
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
				<h6 class="pie-title"><?php echo esc_html( $title ); ?></h3>
			<?php endif; ?>
			<canvas id="<?php echo esc_attr( $id ); ?>" width="900" height="370" style="width:100%;height:100%">
			</canvas>
		</div>
		<?php  
		$sumperc = array_sum( $percentages );
		if (intval($sumperc) > 0) {
			if ( $absolute == '1' ){ 
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
			<h6 class="pie-title"><?php echo esc_html( $title ); ?></h3>
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
		$balksum = 0;
		?>
		<div class="tp-horizontalbar" data-id="tp_horizontalbar_data_<?php echo esc_attr( $id ); ?>">
			<?php if ( ! empty( $title ) ) : ?>
				<h6 class="pie-title"><?php echo esc_html( $title ); ?></h3>
			<?php endif; ?>
			<div class="tp-skills-horizontalbar">
				<?php if ( $count > 0 ) :
					$hundproz = max($percentages);
					for ( $i = 0; $i <= $count; $i++ ) : 
						if ( $absolute == '1' ){
							$balkenanzeige = absint( $percentages[$i] / $hundproz * 100 );
							$balkhoehe = absint( $percentages[$i] / $hundproz * 100 );
							$balksum += absint($percentages[$i]);
							if ( absint( $percentages[$i]) > 0 ) { $balkenanzeige .= '% &nbsp; '.absint( $percentages[$i]); }
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
	//  Posts und Pages pro Monat für letzte 12 Monate als Bar Chart (ruft Bar chart shortcode auf)
	//
	function wpse60859_shortcode_alt_cb($atts)
	{
		$input = shortcode_atts( array(	
			'fromdate' => date("Y-m-d H:i:s"),                //  NOW() Startdate like: 2020-07-01
			'months' => 12,
		    'accentcolor' => false, 
		), $atts );
		$accentcolor=$input['accentcolor'];
		$fromdate = $input['fromdate']; 
		if ( is_archive() ) {  // If Archiv then show stats from 12 months before the archive month
			$yearnum  = get_query_var('year');
			$monthnum = get_query_var('monthnum');
			$fromdate = $yearnum . '-'. $monthnum.'-01 00:00:00';
		}
		$monate = $input['months']-1; 
		$monnamen = array ("","Jan","Feb","Mär","Apr","Mai","Jun","Jul","Aug","Sep","Okt","Nov","Dez");
		global $wpdb;
		$res = $wpdb->get_results(
			"SELECT MONTH(post_date) as post_month, YEAR(post_date) as post_year, COUNT(ID) as post_count " .
			"FROM {$wpdb->posts} " .
			"WHERE post_date BETWEEN DATE_SUB('".$fromdate."', INTERVAL ".$monate." MONTH) AND '".$fromdate."' " .
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
			$nyear = $r->post_year;
			$axislink=get_home_url( '/' ).'/'.$nyear.'/'.$r->post_month;
			$labl .= '<a href='.$axislink.'>'.$monnamen[$r->post_month] .' ' . substr($nyear,2,2) . '</a>,';
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
			<h6 class="pie-title"><?php echo esc_html( $title ); ?></h3>
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

	public function PB_ChartsCodes_create_shortcode() {
		/*
		 * Create Shortcodes  für Charts und für die Post per Month Statistik
		 */
		add_shortcode( 'chartscodes', array( $this, 'PB_ChartsCodes_shortcode_function' ) );
		add_shortcode( 'chartscodes_donut', array( $this, 'PB_ChartsCodes_doughnut_shortcode_function' ) );
		add_shortcode( 'chartscodes_polar', array( $this, 'PB_ChartsCodes_polar_shortcode_function' ) );
		add_shortcode( 'chartscodes_bar', array( $this, 'PB_ChartsCodes_bar_shortcode_function' ) );
		add_shortcode( 'chartscodes_horizontal_bar', array( $this, 'PB_ChartsCodes_horizontal_bar_shortcode_function' ) );
		add_shortcode( 'posts_per_month_last', array( $this, 'wpse60859_shortcode_alt_cb' ) );
		add_shortcode( 'chartscodes_line', array( $this, 'PB_ChartsCodes_line_shortcode_function' ) );
	}
}
new PB_ChartsCodes_Shortcode();

// ========================================== Shortcode Timeline from posts ====================================================

// get shortcode attributes, pass to display function
function timeline_shortcode($atts){
	$args = shortcode_atts( array(
		      'catname' => '',     // insert slugs of all post types you want, sep by comma, empty for all types
		      'type' => 'post,wpdoodle',         // separate type slugs by comma
			  'items' => 1000,     // Maximal 1000 Posts paginiert anzeigen
			  'perpage' => 20,     // posts per page for pagination
			  'view' => '',         // set to "calendar" for calender display instead of timeline 
			  'pics' => 1,         // 1 or 0 - Show images (Category-Image, Post-Thumb or first image in post)
			  'dateformat' => 'D d.m.Y H:i',
     		), $atts );
     return display_timeline($args);
 }
add_shortcode('wp-timeline', 'timeline_shortcode');

// Calendar display month - draws a calendar
function timeline_calendar( $month,$year,$eventarray ) {
	setlocale (LC_ALL, 'de_DE.utf8', 'de_DE@euro', 'de_DE', 'de', 'ge'); 
	/* days and weeks vars now ... */
	$calheader = date('Y-m-d',mktime(0,0,0,$month,1,$year));
	$running_day = date('w',mktime(0,0,0,$month,1,$year));
	if ( $running_day == 0 ) { $running_day = 7; }
	$days_in_month = date('t',mktime(0,0,0,$month,1,$year));
	$days_in_this_week = 1;
	$day_counter = 0;
	$dates_array = array();
	/* draw table */
	$calendar = '<table><thead><th style="text-align:center" colspan=8>' . strftime('%B %Y', mktime(0,0,0,$month,1,$year) ) . '</th></thead>';
	/* table headings */
	$headings = array('MO','DI','MI','DO','FR','SA','SO','Kw');
	$calendar.= '<tr><td style="padding:2px;text-align:center">'.implode('</td><td style="padding:2px;text-align:center">',$headings).'</td></tr>';
	/* row for week one */
	$calendar.= '<tr class="calendar-row">';
	/* print "blank" days until the first of the current week */
	for($x = 1; $x < $running_day; $x++):
		$calendar.= '<td class="calendar-day-np"></td>';
		$days_in_this_week++;
	endfor;
	/* keep going with days.... */
	for($list_day = 1; $list_day <= $days_in_month; $list_day++):
		$calendar.= '<td class="calendar-day">';
		/* add in the day number */
		$running_week = date('W',mktime(0,0,0,$month,$list_day,$year));
		$calendar.= '<div class="day-number">'.$list_day.'</div>';
		/** QUERY THE DATABASE FOR AN ENTRY FOR THIS DAY !!  IF MATCHES FOUND, PRINT THEM !! **/
		foreach ($eventarray as $calevent) {
			if ( substr(get_the_time('Ymd', $calevent->ID),0,8) == date('Ymd',mktime(0,0,0,$month,$list_day,$year)) ) {
				$calendar .= '<span style="word-break:break-all"><a href="' . get_permalink($calevent->ID) . '" title="'.$calevent->title.'">' . get_the_title( $calevent->ID ) . '</a></span> <br> ';
			}
		}	
		$calendar.= '</td>';
		if($running_day == 7):
			$calendar.= '<td style="text-align:center;font-size:0.9em;padding:2px">'.$running_week.'</td></tr>';
			if(($day_counter+1) != $days_in_month):
				$calendar.= '<tr class="calendar-row">';
			endif;
			$running_day = 0;
			$days_in_this_week = 0;
		endif;
		$days_in_this_week++; $running_day++; $day_counter++;
	endfor;
	/* finish the rest of the days in the week */
	if($days_in_this_week < 8 && $days_in_this_week > 1):
		for($x = 1; $x <= (8 - $days_in_this_week); $x++):
			$calendar.= '<td class="calendar-day-np"></td>';
		endfor;
	$calendar.= '<td style="text-align:center;font-size:0.9em;padding:2px">'.$running_week.'</td></tr>';
	endif;
	/* end the table */
	$calendar.= '</table>';
	/* all done, return result */
	return $calendar;
}


//  display the timeline
function display_timeline($args){
	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
		$post_args = array(
			'post_type' => explode( ',', $args['type'] ),
			'numberposts' => $args['items'],
			'posts_per_page' => $args['perpage'],
			'paged' => $paged,
			'page' => $paged,
			'category_name' => $args['catname'],
			'orderby' => 'post_date',
			'order' => 'DESC',
			'post_status' => 'publish',
		);
		$tpostarg = array(
			'numberposts' => -1,
			'post_type' => explode( ',', $args['type'] ),
			'category_name' => $args['catname'],
			'post_status' => 'publish',
		);
		$tpostcount = count(get_posts( $tpostarg ));
		if ( $tpostcount > intval($args['items']) ) $tpostcount = intval($args['items']);
		$posts = get_posts( $post_args );
		$out='';
		if ( $args['view'] == 'calendar' ) {
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
		} else {	
			$out .=  '<div id="timeline">';
			$out .=   '<ul>';
			$prevdate = '';
			foreach ( $posts as $post ) : setup_postdata($post);
				$out .=  '<li><div>';
				$out .=  '<nobr><a href="' . get_permalink($post->ID) . '" title="'.$post->title.'"><h6 class="headline">';
				$out .=  ' '.get_the_title($post->ID). '</h4></a></nobr>';
				$out .=  '<span class="timeline-datebild" style="background-color:'. get_theme_mod( 'link-color', '#888' ) .'">';
				$out .=  get_the_time( 'D', $post->ID ).'<br><span style="font-size:1.5em">'.get_the_time( 'd', $post->ID ).'</span><br>'.get_the_time( 'M', $post->ID );
				$out .=  '</span>';
				if (  $args['pics'] == 1 ) {
					$out .=  '<div class="timeline-image">';
					if ( has_post_thumbnail( $post->ID ) ) {
						$out .=  get_the_post_thumbnail( $post->ID, 'large' );
					} else {
						$first_img='';
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
						$out .= $first_img;
					}	
					$out .= '</div>';
				}
				if (  $args['pics'] == 1 ) { $imgon=''; $exwordcount = 15; } else { $imgon ='noimages'; $exwordcount = 30; }
				$out .= '<span class="timeline-text '.$imgon.'" ><abbr>';
				if ( !empty($prevdate)) $out .= ' <i title="älter als voriger Beitrag" class="fa fa-arrows-h"></i> '.human_time_diff($prevdate,get_the_time( 'U', $post->ID ));
				$out .= ' <i class="fa fa-calendar-o"></i> ';
				$out .=  get_the_time($args['dateformat'], $post->ID);
				$out .=  ' vor '. human_time_diff( get_the_time( 'U', $post->ID ), current_time( 'timestamp' ) );
				$out .=  ' &nbsp; <i class="fa fa-newspaper-o"></i> '.wp_trim_words(get_the_excerpt( $post->ID ), $exwordcount );
				$out .=  '</abbr></span></div></li>';
				$prevdate = get_the_time( 'U', $post->ID );
			endforeach;
			$out .=  '</ul>';
			$out .=  '</div> <!-- #timeline -->';
		}	
		$big = 999999999; // need an unlikely integer
		$out .= paginate_links( array(
			'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format' => '?paged=%#%',
			'current' => max( 1, get_query_var('paged') ),
			'total' => intval($tpostcount / 16),
		) );
		wp_reset_postdata();
		return $out;
}

//is shortcode active on page? if so, add styles to header
function has_timeline_shortcode( $posts ) {
        if ( empty($posts) )
            return $posts;
        $shortcode_found = false;
        foreach ($posts as $post) {
            if ( !( stripos($post->post_content, '[wp-timeline') === false ) ) {
                $shortcode_found = true;
                break;
            }
        }
        if ( $shortcode_found ) {
            add_timeline_styles();
        }
        return $posts;
    }
add_action('the_posts', 'has_timeline_shortcode');

//add styles to header
function add_timeline_styles(){
	add_action('wp_print_styles', 'timeline_styles');
}
function timeline_styles(){
	wp_register_style($handle = 'timeline', $src = plugins_url('timeline.css', __FILE__), $deps = array(), $ver = '1.0.0', $media = 'all');
	wp_enqueue_style('timeline');
}

//do shortcode for get_the_excerpt() && get_the_content()
add_filter('get_the_content', 'do_shortcode');
add_filter('get_the_excerpt', 'do_shortcode');
?>
