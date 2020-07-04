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

	
	public function PB_ChartsCodes_shortcode_function( $atts ) 
	{
		/*
		 * Default Pie Chart Shortcode Function
		 */
		$colorli = '';
		$colval = array("AA","66","99","CC","ff","55");
		for ($farbb = 0; $farbb <= 5; ++$farbb) {
			for ($farbc = 0; $farbc <= 5; ++$farbc) {
				$colorli .= "#00".$colval[$farbb].$colval[$farbc].",";
			}
		}
		$colorli = rtrim($colorli,",");
		
		ob_start();
		$input = shortcode_atts( array(
				'title'		=> '',
				'absolute' => '',
				'values' 	=> '',
				'labels'	=> '',
				'fontfamily' => 'Arial',
				'fontstyle' => 'normal',
				'colors'	=> $colorli,
			), $atts );
		$quotes = array( "\"", "'" );
		$absolute 		= $input['absolute']; 
		$title 			= $input['title']; 
		$fontfamily 	= esc_attr( $input['fontfamily'] ); 
		$fontstyle 		= esc_attr( $input['fontstyle'] ); 
		$percentages 	= explode( ',', str_replace( $quotes, '', $input['values'] ) );
		$labels 		= explode( ',', str_replace( "\"", '', $input['labels'] ) );
		$colors 		= explode( ',', str_replace( $quotes, '', $input['colors'] ) );
		$radius			= array( 120, 120, 120, 120, 120, 120, 120, 120, 120, 120,120 );
		$id 			= uniqid( 'tp_pie_', false ); 
		?>
		<div class="tp-piebuilderWrapper" data-id="tp_pie_data_<?php echo esc_attr( $id ); ?>">
			<h3 class="pie-title"><?php echo esc_html( $title ); ?></h3>
			<canvas id="<?php echo esc_attr( $id ); ?>" width="600" height="400">
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

	public function PB_ChartsCodes_doughnut_shortcode_function( $atts ) 
	{
		/*
		 * Donut Pie Chart Shortcode Function
		 */
		
		$colorli = '';
		$colval = array("AA","66","99","CC","ff","55");
		for ($farbb = 0; $farbb <= 5; ++$farbb) {
			for ($farbc = 0; $farbc <= 5; ++$farbc) {
				$colorli .= "#00".$colval[$farbb].$colval[$farbc].",";
			}
		}
		$colorli = rtrim($colorli,",");

		ob_start();
		$input = shortcode_atts( array(
				'title'		=> '',
				'absolute' => '',
				'values' 	=> '',
				'labels'	=> '',
				'fontfamily' => 'Arial',
				'fontstyle' => 'normal',
				'colors'	=> $colorli,
			), $atts );
		$quotes = array( "\"", "'" );
		$title 			= $input['title']; 
		$absolute 		= $input['absolute']; 
		$fontfamily 	= esc_attr( $input['fontfamily'] ); 
		$fontstyle 		= esc_attr( $input['fontstyle'] ); 
		$percentages 	= explode( ',', str_replace( $quotes, '', $input['values'] ) );
		$labels 		= explode( ',', str_replace( "\"", '', $input['labels'] ) );
		$colors 		= explode( ',', str_replace( $quotes, '', $input['colors'] ) );
		$radius			= array( 120, 120, 120, 120, 120, 120, 120, 120, 120, 120, 120 );
		$id 			= uniqid( 'tp_doughnut_', false ); 
		?>
		<div class="tp-piebuilderWrapper" data-id="tp_pie_data_<?php echo esc_attr( $id ); ?>">
			<?php if ( ! empty( $title ) ) : ?>
				<h3 class="pie-title"><?php echo esc_html( $title ); ?></h3>
			<?php endif; ?>
			<canvas id="<?php echo esc_attr( $id ); ?>" width="600" height="400">
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

	public function PB_ChartsCodes_polar_shortcode_function( $atts ) 
	{
		/*
		 * Polar Pie Chart Shortcode Function
		 */
		$colorli = '';
		$colval = array("AA","66","99","CC","ff","55");
		for ($farbb = 0; $farbb <= 5; ++$farbb) {
			for ($farbc = 0; $farbc <= 5; ++$farbc) {
				$colorli .= "#00".$colval[$farbb].$colval[$farbc].",";
			}
		}
		$colorli = rtrim($colorli,",");
		
		ob_start();
		$input = shortcode_atts( array(
				'title'		=> '',
				'absolute' => '',
				'values' 	=> '',
				'labels'	=> '',
				'fontfamily' => 'Arial',
				'fontstyle' => 'normal',
				'colors'	=> $colorli,
			), $atts );
		$quotes = array( "\"", "'" );
		$title 			= $input['title']; 
		$absolute 		= $input['absolute']; 
		$fontfamily 	= esc_attr( $input['fontfamily'] ); 
		$fontstyle 		= esc_attr( $input['fontstyle'] ); 
		$percentages 	= explode( ',', str_replace( $quotes, '', $input['values'] ) );
		$labels 		= explode( ',', str_replace( "\"", '', $input['labels'] ) );
		$colors 		= explode( ',', str_replace( $quotes, '', $input['colors'] ) );
		$radius			= array( 125, 135, 130, 140, 135, 130, 120, 130, 140, 130 );
		$id 			= uniqid( 'tp_polar_', false ); 
		?>
		<div class="tp-piebuilderWrapper" data-id="tp_pie_data_<?php echo esc_attr( $id ); ?>">
			<?php if ( ! empty( $title ) ) : ?>
				<h3 class="pie-title"><?php echo esc_html( $title ); ?></h3>
			<?php endif; ?>
			<canvas id="<?php echo esc_attr( $id ); ?>" width="600" height="400">
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

	public function PB_ChartsCodes_bar_shortcode_function( $atts ) 
	{
		/*
		 * vertical Bar Graph Shortcode Function
		 */

		$colorli = '';
		$colval = array("AA","66","99","CC","ff","55");
		for ($farbb = 0; $farbb <= 5; ++$farbb) {
			for ($farbc = 0; $farbc <= 5; ++$farbc) {
				$colorli .= "#00".$colval[$farbb].$colval[$farbc].",";
			}
		}
		$colorli = rtrim($colorli,",");
		
		ob_start();
			$input = shortcode_atts( array(
					'title'		=> '',
				    'absolute' => '',
				    'values' 	=> '',
				    'labels'	=> '',
				    'colors'	=> $colorli,
				), $atts );
			$quotes = array( "\"", "'" );
			$title 			= $input['title']; 
			$absolute 		= $input['absolute']; 
			$percentages 	= explode( ',', str_replace( $quotes, '', $input['values'] ) );;
			$labels 		= explode( ',', str_replace( "\"", '', $input['labels'] ) );
			$colors 		= explode( ',', str_replace( $quotes, '', $input['colors'] ) );
			$count 			= count( $labels )-1;
			$id 			= uniqid( 'tp_bar_', false ); 
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
						<div class="outer-box">
							<div id="<?php echo esc_attr( $id ) . '_' . $i; ?>" class="inner-fill" style="background-color: <?php echo esc_attr( $colors[$i] ); ?>; height: <?php echo $balkhoehe . '%'; ?>">
								<span class="percent-value"><?php echo $balkenanzeige; ?></span>
							</div><!-- .inner-fill -->
						<?php echo '<span class="tp-axislabels" style="background-color:'.esc_attr( $colors[$i] ).';"> &nbsp; &nbsp;'.esc_html( $labels[$i] ).' </span>'; ?>
						</div><!-- .outer-box -->
						<?php 
						endfor;  
					endif; ?>
				</div><!-- .skills-bar -->

				<!-- Legende alternativ
				<ul class="tp-skill-items">
					<?php if ( $count > 0 ) :
						for ( $i = 0; $i <= $count; $i++ ) : 
						?>
						<li><span class="color" style="background-color: <?php echo esc_attr( $colors[$i] ); ?>"></span><span><?php echo esc_html( $labels[$i] ); ?></span></li>
						<?php 
						endfor; 
					endif; ?>
				</ul>
				-->
			</div>
		<?php 
		return ob_get_clean();
	}

	public function PB_ChartsCodes_horizontal_bar_shortcode_function( $atts ) 
	{
		/*
		 * Horizontal Bar Graph Shortcode Function
		 */
		$colorli = '';
		$colval = array("AA","66","99","CC","ff","55");
		for ($farbb = 0; $farbb <= 5; ++$farbb) {
			for ($farbc = 0; $farbc <= 5; ++$farbc) {
				$colorli .= "#00".$colval[$farbb].$colval[$farbc].",";
			}
		}
		$colorli = rtrim($colorli,",");
		
		ob_start();
		$input = shortcode_atts( array(
				'title'		=> '',
				'absolute' => '',
				'values' 	=> '',
				'labels'	=> '',
				'colors'	=> $colorli,
			), $atts );
		$quotes = array( "\"", "'" );
		$title 			= $input['title']; 
		$absolute 		= $input['absolute']; 
		$percentages 	= explode( ',', str_replace( $quotes, '', $input['values'] ) );;
		$labels 		= explode( ',', str_replace( "\"", '', $input['labels'] ) );
		$colors 		= explode( ',', str_replace( $quotes, '', $input['colors'] ) );
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
							if ( absint( $percentages[$i]) > 0 ) { $balkenanzeige .= '%|'.absint( $percentages[$i]); }
						} else {
							$balkenanzeige = absint( $percentages[$i] );
							$balkhoehe = absint( $percentages[$i] );
						}
					?>
					<div class="outer-box">
						<div id="<?php echo esc_attr( $id ) . '_' . $i; ?>" class="inner-fill" style="background-color: <?php echo esc_attr( $colors[$i] ); ?>; width: <?php echo $balkhoehe . '%'; ?>">
							<span class="percent-value"><?php echo $balkenanzeige; ?></span>
						</div><!-- .inner-fill -->
						<span class="skill-name"><?php echo esc_html( $labels[$i] ); ?></span>
					</div><!-- .outer-box -->
					<?php 
					endfor;  
				endif; ?>
			</div><!-- .skills-bar -->
		</div>
		<?php 
		return ob_get_clean();
	}


/// Posts und Pages pro Monat für letzte 12 Monate als Chart
function wpse60859_shortcode_alt_cb($atts)
{
	$input = shortcode_atts( array(	'months' => 12,	), $atts );
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
	$out = '[chartscodes_bar absolute="1" title="Beiträge/Seiten letzte '.$monate.' Monate" ';
    foreach($res as $r) {
		// echo $r->post_count .'/M:'.$r->post_month .'<br>';
        $valu .= isset($r->post_month) ? floor($r->post_count) : 0;
		$valu .= ',';
		$labl .= $monnamen[$r->post_month] . ',';
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
		 * Create Shortcodes  für Charts und für die Post per Mont Statistik
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
