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
Version: 11.1.100
Stable tag: 11.1.100
Requires at least: 5.1
Tested up to: 6.2.2
Requires PHP: 8.0
*/

if ( ! defined( 'ABSPATH' ) ) {	exit; } // Exit if accessed directly.

add_action( 'plugins_loaded', 'chartscodes_textdomain' );
function chartscodes_textdomain() {
	load_plugin_textdomain( 'pb-chartscodes', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

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


if ( ! class_exists( 'PB_ChartsCodes' ) ) :
	final class PB_ChartsCodes {
		public function __construct() {
			$this->PB_ChartsCodes_constant();
			$this->PB_ChartsCodes_hooks();
			$this->PB_ChartsCodes_includes();
		}

		public function PB_ChartsCodes_constant() {
			define( 'PB_ChartsCodes_BASE_PATH', dirname(__FILE__ ) );
			define( 'PB_ChartsCodes_URL_PATH', plugin_dir_url(__FILE__ ) );
			define( 'PB_ChartsCodes_PLUGIN_BASE_PATH', plugin_basename(__FILE__) );
		}

		public function PB_ChartsCodes_hooks() {
			// enqueue admin scripts
			add_action( 'wp_enqueue_scripts', array( $this, 'PB_ChartsCodes_enqueue' ) );
		}

		public function PB_ChartsCodes_enqueue() {
            // Load chartcodes style
            wp_enqueue_style( 'pb-chartscodes-style', PB_ChartsCodes_URL_PATH . 'ccstyle.min.css' );
	        // Load Charts custom pie js and radar JS
			wp_register_script( 'pb-chartscodes-script', PB_ChartsCodes_URL_PATH . 'js/pie.min.js', array( 'jquery' ), null, true );
	        wp_register_script( 'pb-chartscodes-initialize', PB_ChartsCodes_URL_PATH . 'js/pie-initialize.min.js', array( 'jquery', 'pb-chartscodes-script' ) );
	        wp_register_script( 'pb-chartscodes-radar', PB_ChartsCodes_URL_PATH . 'js/radar2.min.js', array( 'jquery' ) );
		}

	    public function PB_ChartsCodes_includes() {
			// Shortcode Page
			include_once('pb-shortcodes.php');
		}
	}
	new PB_ChartsCodes();
endif;

// -------------------------- Jetzt den QRCode Generator noch -----------------------------------------------

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
	if (isset($_POST["berechnen"])) {
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
		$out = 'IBAN <code>'.formatIBAN($iban).'</code> ';
		if (checkIBAN($iban)) $out .= 'ist gültig'; else $out .= '<span style="color:tomato">ist ungültig</span>';
		if (!empty($bic)) {
			$out .= ' | BIC <code>'.$bic.'</code> ';
			if (swift_validate($bic)) $out .= 'ist gültig'; else $out .= ' | <span style="color:tomato">ist ungültig</span>';
		}	
		return $out;
	} else {
		$out = '<p><form method="post">';
		$out .= 'BLZ<input name="blz" id="blz" type="int" size="8">';
		$out .= ' Kontonr<input name="kontonr" id="kontonr" type="int" size="10">';
		$out .= ' BIC/SWIFT<input name="bic" id="bic" type="text" size="10">';
		$out .= ' IBAN<input name="iban" id="iban" type="text" size="22">';
		$out .='<br><input style="width:100%" name="berechnen" id="berechnen" type="submit" class="submit" value="IBAN Berechnen oder IBAN und BIC prüfen">';
		$out .='</form></p>';
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
	$args = shortcode_atts( array(
		'ibangen' => 0,
		'iban' => 'DE43370000000038001501',
		'bic' => 'MARKDEF1370',	
		'rec' => 'Max Mustermann',
		'cur' => 'EUR',
		'sum' => 1.99,
		'subj' => 'Rechnung 123456789, Konto 123434',
		'comm' => 'Kommentar zur Ueberweisung',
	), $atts );
	if ( $args['ibangen'] == 1 ) {
		if (!isset($_GET['noheader'])) return makeiban();
	} else {
		// Daten von der Befehlszeile
		//cmdline:	?iban=DE43370000000038001501&bic=MARKDEF1370&rec=Maxine Mustermann&cur=EUR&sum=9.99&subj=Rechnung 123456789 Konto 123434&comm=Kommentar zur Ueberweisung
		if (isset($_GET['iban'])) $iban = sanitize_text_field($_GET['iban']); else $iban = $args['iban'];
		if (isset($_GET['bic'])) $bic = sanitize_text_field($_GET['bic']); else $bic = $args['bic'];
		if (isset($_GET['rec'])) $rec = sanitize_text_field($_GET['rec']); else $rec = $args['rec'];
		if (isset($_GET['cur'])) $cur = sanitize_text_field($_GET['cur']); else $cur = $args['cur'];
		if (isset($_GET['sum'])) $sum = sanitize_text_field($_GET['sum']); else $sum = $args['sum'];
		if (isset($_GET['subj'])) $subj = sanitize_text_field($_GET['subj']); else $subj = $args['subj'];
		if (isset($_GET['comm'])) $comm = sanitize_text_field($_GET['comm']); else $comm = $args['comm'];
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
		if (isset($_GET['noheader'])) {   // wenn in single.php der Parameter gesetzt, keinen Header zeigen
			if (checkIBAN($iban)) return do_shortcode('[qrcode text="'.$data.'" size=3 margin=3]');
		} else {
			// QR Code generieren
			if (! swift_validate($bic)) return "<b style='color:#FF0000;'>BIC (SWIFT code) <i>is not</i> valid.</b>";
			if (checkIBAN($iban)) return '<div class="timeline"><div style="text-align:center">'
				. do_shortcode('[qrcode text="'.$data.'" size=3 margin=3]')
				.'</div><div><pre>'.$data.'</pre></div></div>';
			else return '<span style="color:tomato">IBAN '.$iban.' '.__('is not a valid IBAN', 'pb-chartscodes').'</span>';
		}	
	}
}
add_shortcode('girocode', 'girocode_qr');


// --------------------------- Nun die ipflag Funktionsklasse registrieren --------------------------------------------

class ipflag {
    const version = '9.2.12';
    const name = 'ipflag';
    const slug = 'ipflag';
    const safe_slug = 'ipflag';
    const default_db_version = '6';
    const db_filename = 'ip2country.db';
    const db_dirname = 'database';
    const ip_ranges_table_suffix = 'ipflag_ip_ranges';
    const countries_table_suffix = 'ipflag_countries';
    const db_zip_filename = 'ip2country.zip';
    const db_version_filename = 'ip2country.version';
    const http_timeout = 60;
    const remote_offset = 2;

    public $url;
    public $flag_url;
    public $remote_db_url = 'https://github.com/Markus-Go/ip-countryside/raw/downloads/ip2country.zip';
    public $remote_ts_url = 'https://github.com/Markus-Go/ip-countryside/raw/downloads/ip2country.version';

    protected $path;
    protected $db_version;
    protected $options;
    protected $db_zip_file;
    protected $db_version_file;
    protected $db_file;

    public function __construct() {

        $this->url = plugin_dir_url(__FILE__ );
        $this->flag_url = $this->url . '/flags';
        $this->path =  dirname(__FILE__ );
        $this->db_version = get_option(self::safe_slug.'_db_version');
        $this->options = get_option(self::safe_slug.'_options');

        $this->db_zip_file = $this->path . '/' . self::db_dirname . '/' . self::db_zip_filename;
        $this->db_version_file = $this->path . '/' . self::db_dirname . '/' . self::db_version_filename;
        $this->db_file = $this->path . '/' . self::db_dirname . '/' . self::db_filename;

        add_action('plugins_loaded', array($this, 'update_db_check'));
        add_action('admin_init', array($this, 'settings_init'));
        add_action('admin_menu', array($this, 'add_options_page'));
        add_shortcode('ipflag', array($this, 'shortcode'));
		add_shortcode( 'webcounter', array($this, 'writevisitortodatabase') );

        if(isset($this->options['auto_update'])){
            add_action(self::safe_slug.'_update', array($this, 'do_auto_update'));
            add_filter('cron_schedules', array($this, 'custom_schedule'));
            register_deactivation_hook(__FILE__, array($this, 'deschedule_update'));
            $this->schedule_update();
        }else{
            $this->deschedule_update();
        }
    }

    public function schedule_update(){
        if(!wp_next_scheduled(self::safe_slug.'_update')){
            wp_schedule_event(time(), self::safe_slug.'_weekly', self::safe_slug.'_update');
        }
    }

    public function deschedule_update(){
        if(wp_next_scheduled(self::safe_slug.'_update')){
            wp_clear_scheduled_hook(self::safe_slug.'_update');
        }
    }

    public function custom_schedule($schedules){
        /* Please do not configure cron to interval
         * less than 604800 (7 days) because GitHub might
         * disable our db update repository due to server load
         */
        $schedules[self::safe_slug.'_weekly'] = array(
            'interval'=> 604800,
            'display'=>  __('every week', 'pb-chartscodes')
        );
        return $schedules;
    }

    public function get_info($ip = null){
        global $wpdb;
        $ip_ranges_table_name = $wpdb->prefix . self::ip_ranges_table_suffix;
        $countries_table_name = $wpdb->prefix . self::countries_table_suffix;

        if($ip === null){
            if(isset($_SERVER['HTTP_X_FORWARD_FOR']))
                $ip = $_SERVER['HTTP_X_FORWARD_FOR'];
            else
                $ip = $_SERVER['REMOTE_ADDR'];
        }
        /* ip2long could return signed integer on 32-bit systems.
         * We use sprintf to make sure it is unsigned.
         */
        $sql=   'SELECT
                    "'.self::version.'" as version,
                    "'.$ip.'" as ip,
                    code,
                    name,
                    latitude,
                    longitude
                FROM '.$countries_table_name.'
                INNER JOIN '.$ip_ranges_table_name.'
                    USING(cid)
                WHERE '.sprintf("%u", ip2long($ip)).'
                    BETWEEN fromip AND toip';

        $info = $wpdb->get_row($sql);

        if($info === null) 
            return false;

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
	
	
	public function country_code ($lang = null , $code = null) {
	  if (empty ($countries)) $countries = array (
		  'en' => 
		  array (
			'AF' => 'Afghanistan',
			'AL' => 'Albania',
			'DZ' => 'Algeria',
			'AS' => 'American Samoa',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AQ' => 'Antarctica',
			'AG' => 'Antigua and Barbuda',
			'AR' => 'Argentina',
			'AM' => 'Armenia',
			'AW' => 'Aruba',
			'AT' => 'Austria',
			'AU' => 'Australia',
			'AZ' => 'Azerbaijan',
			'BS' => 'Bahamas',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Belarus',
			'BE' => 'Belgium',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivia',
			'BA' => 'Bosnia and Herzegovina',
			'BW' => 'Botswana',
			'BV' => 'Bouvet Island',
			'BR' => 'Brazil',
			'IO' => 'British Indian Ocean Territory',
			'BN' => 'Brunei Darussalam',
			'BG' => 'Bulgaria',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'KH' => 'Cambodia',
			'CM' => 'Cameroon',
			'CA' => 'Canada',
			'CV' => 'Cape Verde',
			'KY' => 'Cayman Islands',
			'CF' => 'Central African Republic',
			'TD' => 'Chad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CC' => 'Christmas Island',
			'CO' => 'Cocos (Keeling) Islands',
			'CX' => 'Colombia',
			'KM' => 'Comoros',
			'CD' => 'Congo Democratic Republic of the',
			'CG' => 'Congo',
			'CK' => 'Cook Islands',
			'CR' => 'Costa Rica',
			'CI' => 'Cote dIvoire',
			'HR' => 'Croatia',
			'CU' => 'Cuba',
			'CZ' => 'Czech Republic',
			'CS' => 'Czechoslovakia (former)',
			'DK' => 'Denmark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'TP' => 'East Timor',
			'EC' => 'Ecuador',
			'EG' => 'Egypt',
			'SV' => 'El salvador',
			'GQ' => 'Equatorial Guinea',
			'ER' => 'Eritrea',
			'EE' => 'Estonia',
			'EU' => 'European Union',
			'ET' => 'Ethiopia',
			'FK' => 'Falkland Islands',
			'FO' => 'Faroe Islands',
			'FJ' => 'Fiji',
			'FI' => 'Finland',
			'FR' => 'France',
			'FX' => 'France (used only in europe)',
			'GF' => 'French Guiana',
			'PF' => 'French Polynesia',
			'GA' => 'Gabon',
			'GM' => 'Gambia',
			'GE' => 'Georgia',
			'DE' => 'Germany',
			'DD' => 'German Dep Rep (former)',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GR' => 'Greece',
			'GS' => 'South Georgia und South Sandwich Islands',
			'GL' => 'Greenland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GU' => 'Guam',
			'GY' => 'Guinea Bissau',
			'GT' => 'Guatemala',
			'GN' => 'Guinea',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'VA' => 'Vatican',
			'HK' => 'Hong Kong',
			'HM' => 'Heard and McDonald Islands',
			'HN' => 'Honduras',
			'HU' => 'Hungary',
			'IS' => 'Iceland',
			'IN' => 'India',
			'ID' => 'Indonesia',
			'IR' => 'Iran',
			'IQ' => 'Iraq',
			'IE' => 'Ireland',
			'IL' => 'Israel',
			'IT' => 'Italy',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'JO' => 'Jordan',
			'KN' => 'St. Kitts Nevis Anguilla',
			'KZ' => 'Kazakstan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'KW' => 'Kuwait',
			'KG' => 'Kyrgystan',
			'LA' => 'Lao',
			'LV' => 'Latvia',
			'LB' => 'Lebanon',
			'LC' => 'Saint Lucia',
			'LS' => 'Lesotho',
			'LI' => 'Liechtenstein',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'MO' => 'Macau',
			'MK' => 'Macedonia ',
			'MG' => 'Madagascar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'MV' => 'Maldives',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MR' => 'Mauritania',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexico',
			'FM' => 'Micronesia',
			'MD' => 'Moldova',
			'MQ' => 'Martinique',
			'MH' => 'Marshall Inseln',
			'ME' => 'Montenegro',
			'MC' => 'Monaco',
			'MN' => 'Mongolia',
			'MS' => 'Montserrat',
			'MA' => 'Morocco',
			'MP' => 'Northern Mariana Islands',
			'MZ' => 'Mozambique',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'NL' => 'Netherlands',
			'NM' => 'Northern Macadonia',
			'NZ' => 'New Zealand',
			'NC' => 'New Caledonia',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Island',
			'KP' => 'North Korea',
			'NO' => 'Norway',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PW' => 'Palau',
			'PA' => 'Panama',
			'PG' => 'Papua New Guinea',
			'PM' => 'St. Pierre and Miquelon',
			'PN' => 'Pitcairn',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Philippines',
			'PL' => 'Poland',
			'PT' => 'Portugal',
			'PR' => 'Puerto Rico',
			'QA' => 'Qatar',
			'RE' => 'Reunion',
			'RS' => 'Serbia',
			'RO' => 'Romania',
			'RU' => 'Russia',
			'RW' => 'Rwanda',
			'WS' => 'Samoa',
			'SM' => 'San Marino',
			'SA' => 'Saudi Arabia',
			'SH' => 'Saint Helena Ascension and Tristan da Cunha',
			'SN' => 'Senegal',
			'SC' => 'Seychelles',
			'SL' => 'Sierra Leone',
			'SG' => 'Singapore',
			'SK' => 'Slovakia',
			'SI' => 'Slovenia',
			'SB' => 'Solomon Islands',
			'SO' => 'Somalia',
			'SS' => 'South Sudan',
			'ST' => 'Sao Tome',
			'ZA' => 'South Africa',
			'KR' => 'South Korea',
			'SU' => 'Sowiet-Union (former)',
			'ES' => 'Spain',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SJ' => 'Svalbard and Jan Mayen Islands',
			'SZ' => 'Swaziland (Eswatini)',
			'SE' => 'Sweden',
			'CH' => 'Switzerland',
			'SY' => 'Syria',
			'TW' => 'Taiwan',
			'TF' => 'French Southern Territories',
			'TJ' => 'Tajikistan',
			'TZ' => 'Tanzania',
			'TH' => 'Thailand',
			'TG' => 'Togo',
			'TO' => 'Tonga',
			'TK' => 'Tokelau',
			'TL' => 'East Timor',
			'TT' => 'Trinidad and Tobago',
			'TN' => 'Tunisia',
			'TR' => 'Turkey',
			'TM' => 'Turkmenistan',
			'TV' => 'Tuvalu',
			'UG' => 'Uganda',
			'UA' => 'Ukraine',
			'WF' => 'Wallis und Futuna',
			'AE' => 'United Arab Emirates',
			'GB' => 'United Kingdom',
			'UK' => 'United Kingdom Great Britain',
			'UN' => 'United Nations',
			'US' => 'United States of America',
			'UM' => 'United States Minor Outlying Islands',
			'UY' => 'Uruguay',
			'UZ' => 'Uzbekistan',
			'VU' => 'Vanuatu',
			'VE' => 'Venezuela',
			'VN' => 'Vietnam',
			'VG' => 'Virgin Islands',
			'VI' => 'Virgin Islands (US)',
			'EH' => 'Western Sahara',
			'YE' => 'Yemen',
			'YU' => 'Yugoslavia',
			'ZR' => 'Zaire',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe',
			'ZZ' => 'worldwide',
		  ),
		  'de' => 
		  array (
			'AF' => 'Afghanistan',
			'AL' => 'Albanien',
			'DZ' => 'Algerien',
			'AS' => 'Amerikanisch Samoa',
			'AD' => 'Andorra',
			'AO' => 'Angola',
			'AI' => 'Anguilla',
			'AQ' => 'Antarktis',
			'AG' => 'Antigua und Barbuda',
			'AR' => 'Argentinien',
			'AM' => 'Armenien',
			'AW' => 'Aruba',
			'AT' => 'Österreich',
			'AU' => 'Australien',
			'AZ' => 'Aserbaidschan',
			'BS' => 'Bahamas',
			'BH' => 'Bahrain',
			'BD' => 'Bangladesh',
			'BB' => 'Barbados',
			'BY' => 'Weißrussland',
			'BE' => 'Belgien',
			'BZ' => 'Belize',
			'BJ' => 'Benin',
			'BM' => 'Bermuda',
			'BT' => 'Bhutan',
			'BO' => 'Bolivien',
			'BA' => 'Bosnien Herzegowina',
			'BW' => 'Botswana',
			'BV' => 'Bouvet Island',
			'BR' => 'Brasilien',
			'IO' => 'Britisch-Indischer Ozean',
			'BN' => 'Brunei Darussalam',
			'BG' => 'Bulgarien',
			'BF' => 'Burkina Faso',
			'BI' => 'Burundi',
			'KH' => 'Kambodscha',
			'CM' => 'Kamerun',
			'CA' => 'Kanada',
			'CV' => 'Kap Verde',
			'KY' => 'Cayman Inseln',
			'CF' => 'Zentralafrikanische Republik',
			'TD' => 'Tschad',
			'CL' => 'Chile',
			'CN' => 'China',
			'CC' => 'Christmas Inseln',
			'CO' => 'Kokosinseln',
			'CO' => 'Kolumbien',
			'KM' => 'Comoros',
			'CG' => 'Kongo',
			'CD' => 'Demokratische Republik Kongo',
			'CK' => 'Cook Inseln',
			'CR' => 'Costa Rica',
			'CI' => 'Elfenbeinküste',
			'HR' => 'Kroatien',
			'CU' => 'Kuba',
			'CZ' => 'Tschechien',
			'CS' => 'Tschechoslowakei (ehemals)',
			'DK' => 'Dänemark',
			'DJ' => 'Djibouti',
			'DM' => 'Dominica',
			'DO' => 'Dominikanische Republik',
			'TP' => 'Osttimor',
			'EC' => 'Ecuador',
			'EG' => 'Ägypten',
			'SV' => 'El Salvador',
			'GQ' => 'Äquatorial Guinea',
			'ER' => 'Eritrea',
			'EE' => 'Estland',
			'ET' => 'Äthiopien',
			'EU' => 'Europäische Union',
			'FK' => 'Falkland Inseln',
			'FO' => 'Faroe Inseln',
			'FJ' => 'Fiji',
			'FI' => 'Finland',
			'FR' => 'Frankreich',
			'FX' => 'Frankreich (nur in Europa)',
			'GF' => 'Französisch Guiana',
			'PF' => 'Französisch Polynesien',
			'GA' => 'Gabon',
			'GM' => 'Gambia',
			'GE' => 'Georgien',
			'DE' => 'Deutschland',
			'DD' => 'DDR (ehemals)',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GR' => 'Griechenland',
			'GS' => 'South Georgia und South Sandwich Inseln',
			'GL' => 'Grönland',
			'GD' => 'Grenada',
			'GP' => 'Guadeloupe',
			'GU' => 'Guam',
			'GT' => 'Guatemala',
			'GN' => 'Guinea',
			'GY' => 'Guinea Bissau',
			'GY' => 'Guyana',
			'HT' => 'Haiti',
			'VA' => 'Vatikan',
			'HK' => 'Hong Kong',
			'HM' => 'Heard und McDonald Inseln',
			'HN' => 'Honduras',
			'HU' => 'Ungarn',
			'IS' => 'Island',
			'IN' => 'Indien',
			'ID' => 'Indonesien',
			'IR' => 'Iran',
			'IQ' => 'Irak',
			'IE' => 'Irland',
			'IL' => 'Israel',
			'IT' => 'Italien',
			'JM' => 'Jamaika',
			'JP' => 'Japan',
			'JO' => 'Jordanien',
			'KZ' => 'Kasachstan',
			'KE' => 'Kenia',
			'KI' => 'Kiribati',
			'KW' => 'Kuwait',
			'KG' => 'Kirgistan',
			'LA' => 'Laos',
			'LV' => 'Lettland',
			'LB' => 'Libanon',
			'LC' => 'Saint Lucia',
			'LS' => 'Lesotho',
			'LI' => 'Liechtenstein',
			'LT' => 'Litauen',
			'LU' => 'Luxemburg',
			'MP' => 'Marianen',
			'MQ' => 'Martinique',
			'ME' => 'Montenegro',
			'MO' => 'Macau',
			'MK' => 'Mazedonien',
			'NM' => 'Nord-Mazedonien',
			'MG' => 'Madagaskar',
			'MW' => 'Malawi',
			'MY' => 'Malaysia',
			'MV' => 'Malediven',
			'MH' => 'Marshall Inseln',
			'ML' => 'Mali',
			'MT' => 'Malta',
			'MR' => 'Mauretanien',
			'MU' => 'Mauritius',
			'YT' => 'Mayotte',
			'MX' => 'Mexiko',
			'FM' => 'Mikronesien',
			'MD' => 'Moldavien',
			'MC' => 'Monaco',
			'MN' => 'Mongolei',
			'MS' => 'Montserrat',
			'MA' => 'Marokko',
			'MZ' => 'Mosambik',
			'MM' => 'Myanmar',
			'NA' => 'Namibia',
			'NR' => 'Nauru',
			'NP' => 'Nepal',
			'NL' => 'Niederlande',
			'NZ' => 'Neuseeland',
			'NC' => 'Neu Kaledonien',
			'NI' => 'Nicaragua',
			'NE' => 'Niger',
			'NG' => 'Nigeria',
			'NU' => 'Niue',
			'NF' => 'Norfolk Inseln',
			'KP' => 'Nord Korea',
			'NO' => 'Norwegen',
			'OM' => 'Oman',
			'PK' => 'Pakistan',
			'PW' => 'Palau',
			'PA' => 'Panama',
			'PG' => 'Papua Neu Guinea',
			'PY' => 'Paraguay',
			'PE' => 'Peru',
			'PH' => 'Philippinen',
			'PL' => 'Polen',
			'PM' => 'St. Pierre und Miquelon',
			'PN' => 'Pitcairn',
			'PT' => 'Portugal',
			'PR' => 'Puerto Rico',
			'RO' => 'Rumänien',
			'RU' => 'Russland',
			'RW' => 'Ruanda',
			'QA' => 'Qatar',
			'RE' => 'Reunion',
			'RS' => 'Serbien',
			'WS' => 'Samoa',
			'SH' => 'Saint Helena Ascension und Tristan da Cunha',
			'SM' => 'San Marino',
			'SA' => 'Saudi-Arabien',
			'SN' => 'Senegal',
			'SC' => 'Seychellen',
			'SJ' => 'Svalbard und Jan Mayen Inseln',
			'SL' => 'Sierra Leone',
			'SS' => 'Süd-Sudan',
			'SU' => 'Sowiet-Union (obsolet)',
			'ST' => 'Sao Tome and Principe',
			'SG' => 'Singapur',
			'SK' => 'Slowakei',
			'SI' => 'Slowenien',
			'SB' => 'Solomon Inseln',
			'SO' => 'Somalia',
			'KN' => 'St. Kitts Nevis Anguilla',
			'ZA' => 'Südafrika',
			'KR' => 'Südkorea',
			'ES' => 'Spanien',
			'LK' => 'Sri Lanka',
			'SD' => 'Sudan',
			'SR' => 'Suriname',
			'SZ' => 'Swasiland',
			'SE' => 'Schweden',
			'CH' => 'Schweiz',
			'SY' => 'Syrien',
			'TW' => 'Taiwan',
			'TJ' => 'Tadschikistan',
			'TL' => 'Osttimor',
			'TZ' => 'Tansania',
			'TH' => 'Thailand',
			'TF' => 'French Southern Territories',
			'TG' => 'Togo',
			'TK' => 'Tokelau',
			'TO' => 'Tonga',
			'TT' => 'Trinidad und Tobago',
			'TN' => 'Tunesien',
			'TR' => 'Türkei',
			'TM' => 'Turkmenistan',
			'TV' => 'Tuvalu',
			'UG' => 'Uganda',
			'UK' => 'Großbritannien (UK)',
			'UA' => 'Ukraine',
			'AE' => 'Vereinigte Arabische Emirate',
			'UN' => 'Vereinte Nationen',
			'GB' => 'Vereinigtes Königreich',
			'US' => 'Vereinigte Staaten von Amerika',
			'UM' => 'US - kleinere Inseln außerhalb',
			'UY' => 'Uruguay',
			'UZ' => 'Usbekistan',
			'VU' => 'Vanuatu',
			'VE' => 'Venezuela',
			'VN' => 'Vietnam',
			'VI' => 'Virgin Island (USA)',
			'VG' => 'Virgin Island (Brit.)',
			'WF' => 'Wallis et Futuna',
			'EH' => 'Westsahara',
			'YE' => 'Jemen',
			'YU' => 'Jugoslawien (ehemals)',
			'ZR' => 'Zaire',
			'ZM' => 'Sambia',
			'ZW' => 'Simbabwe',
			'ZZ' => 'weltweit',
		  ),
		);
	  if (null == $lang) { return ($countries); }
	  $lang = strtolower ($lang);
	  if (null == $code) { return (isset ($countries[$lang]) ? $countries[$lang] : false); }
	  $code = strtoupper ($code);
	  return (isset ($countries[$lang][$code]) ? $countries[$lang][$code] : false);
	}

    public function get_flag($info){
		// Load flag freaky style for flags
		wp_enqueue_style( 'pb-chartscodes-flagstyle', PB_ChartsCodes_URL_PATH . 'flags/freakflags.min.css' );
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


	private static function is_bot( $user_agent ) {
		$user_agent = strtolower( $user_agent );
		$identifiers = array(
			'bot', 'slurp', 'crawler', 'spider', 'curl', 'facebook', 'lua-resty', 'fetch', 'python', 'scrubby',
			'wget', 'monitor', 'mediapartners', 'baidu','chrome/3','chrome/4','chrome/5','chrome/6','chrome/7','chrome/8','chrome/9',
			'firefox/3','firefox/4','firefox/5','firefox/6','firefox/7','firefox/8','firefox/9',
		);
		foreach ( $identifiers as $identifier ) {
			if ( strpos( $user_agent, $identifier ) !== false ) {
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
			'/windows nt 11.0/i'    =>  'Windows 11',
			'/windows nt 10.1/i'    =>  'Windows 11',
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
			'/iphone/i'             =>  'iPhone',
			'/ipod/i'               =>  'iPod',
			'/ipad/i'               =>  'iPad',
			'/android/i'            =>  'Android',
			'/blackberry/i'         =>  'BlackBerry',
			'/webos/i'              =>  'Mobile'
		);
		foreach ($os_array as $regex => $value) { 
			if (preg_match($regex, $u_agent)) { $platform    =   $value; }
		}

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
		$ip = long2ip(ip2long($ip) & 0xFFFFFF00);
		return apply_filters( 'wpb_get_ip', $ip );
	}
	
	// Browser und OS icons anzeigen
	public function showbrowosicon($xname) {
		switch ( $xname ) :
			case 'Google Chrome' : $xicon = 'Image/chrome.png'; break;
			case 'Microsoft Edge' : $xicon = 'Image/edgenew.png'; break;
			case 'Mozilla Firefox' : $xicon = 'Image/firefox.png'; break;
			case 'Edge legacy' : $xicon = 'Image/edge.png'; break;
			case 'Internet Explorer' : $xicon = 'Image/msie.png'; break;
			case 'Apple Safari' : $xicon = 'Image/safari.png'; break;
			case 'MS-Office' : $xicon = 'Image/office.png'; break;
			case 'Outlook' : $xicon = 'Image/outlook.png'; break;
			case 'Windows 10' : $xicon = 'Image/win8-10.png'; break;
			case 'Windows 8' : $xicon = 'Image/win8-10.png'; break;
			case 'Windows XP' : $xicon = 'Image/winxp.png'; break;
			case 'Windows 7' : $xicon = 'Image/win7.png'; break;
			case 'Ubuntu' : $xicon = 'Image/ubuntu.png'; break;
			case 'Blackberry' : $xicon = 'Image/blackberry.png'; break;
			case 'Android' : $xicon = 'Image/android.png'; break;
			case 'MAC OS X' : $xicon = 'Image/mac.png'; break;
			case 'Windows Server 2003/XP x64' : $xicon = 'Image/winxp.png'; break;
			case 'Windows 8.1/S2012R2' : $xicon = 'Image/win8-10.png'; break;
			case 'iPhone' : $xicon = 'Image/iphone.png'; break;
			default : $xicon = 'Image/surf.png'; break;
		endswitch;
		return '<img src="' .PB_ChartsCodes_URL_PATH . $xicon . '">';
	}

// Display WP-Stats Admin Page
function website_display_stats() {
    global $wpdb;
    $wsstats = '<div class="wrap"><strong>Website-Fakten</strong> ';
    $totalposts = (int) $wpdb->get_var("SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type = 'post' AND post_status = 'publish'");
    $wsstats .= '&nbsp; Beiträge <span title="Total" class="newlabel white">'. number_format_i18n($totalposts,0).'</span>';
    $totalpages = (int) $wpdb->get_var("SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type = 'page' AND post_status = 'publish'");
    $wsstats .= ' &nbsp; Seiten <span title="Total" class="newlabel white">'. $totalpages.'</span>';
	$cargs = array('get' => 'all','hide_empty' => 0,'taxonomy'=>array('category','ddownload_category','quizcategory','product_cat'));
	$wsstats .= ' &nbsp; Kategorien <span title="Gesamt Seiten" class="newlabel white">'. number_format_i18n(count(get_categories( $cargs )),0).'</span>';
	$cargs = array('get' => 'all','hide_empty' => 0);
	$wsstats .= '&nbsp; Themen <span title="Gesamt Themen" class="newlabel white">'. number_format_i18n(count(get_tags( $cargs )),0).'</span>';
	$totalauthors = (int) $wpdb->get_var("SELECT COUNT(ID) FROM $wpdb->users LEFT JOIN $wpdb->usermeta ON $wpdb->usermeta.user_id = $wpdb->users.ID WHERE $wpdb->users.user_activation_key = '' AND $wpdb->usermeta.meta_key = '".$wpdb->prefix."user_level' AND (meta_value+0.00) > 1");
    $wsstats .= ' &nbsp; Autoren <span title="Total" class="newlabel white">'. $totalauthors.'</span>';
	$args = array(  'public'   => true,  '_builtin' => false );
	$output = 'names'; // 'names' or 'objects' (default: 'names')
	$operator = 'and'; // 'and' or 'or' (default: 'and')
	$post_types = get_post_types( $args, $output, $operator );
	$wsstats .= ' &nbsp; ' . __('custom post types','penguin') . ': ';
	if ( $post_types ) { // If there are any custom public post types.
		foreach ( $post_types  as $post_type ) {
			$post30days = get_days_ago_post_count_by_categories('',$post_type);
			if ( $post30days > 0 ) {
				$count30 = '<span title="NEU 30 T" class="newlabel yellow">'.$post30days.'</span>';
			} else $count30 = '';
			$count_posts = wp_count_posts( $post_type )->publish;
			$gpot = $post_type;
			if ($post_type == 'w4pl') $gpot = 'list';
			if ($post_type == 'product') $gpot = 'shop';
			if ($post_type == 'dedo_download') $gpot = 'downloads';
			$wsstats .= ' &nbsp; <a href="'.esc_url(site_url().'/'.$gpot).'">' . strtoupper($gpot) . '</a> <span title="Total" class="newlabel white">'.$count_posts.'</span> '.$count30.'';
		}
		// Post-Formate
		if ( current_theme_supports( 'post-formats' ) ) {
			$post_formats = get_theme_support( 'post-formats' );
			if ( is_array( $post_formats[0] ) ) {
				foreach ($post_formats[0] as $pf) {
					$args = array( 'post_type'=> 'post', 'post_status' => 'publish', 'order' => 'DESC', 'tax_query' => array(
							array( 'taxonomy' => 'post_format','field' => 'slug', 'terms' => array( 'post-format-'.$pf ) ) ) );
					$asides = get_posts( $args );
					$wsstats .= ' &nbsp; <a href="'.get_site_url().'/type/'.$pf.'">'.get_post_format_string($pf).'</a> <span title="Total" class="newlabel white"> '. count($asides) . '</span>';
				} 
			}
		}
	}
	$totalcomments = (int) $wpdb->get_var("SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_approved = '1'");
	$wsstats .= ' &nbsp; <a href="'.get_site_url().'/alle-kommentare/"><i class="fa fa-comments"></i> Kommentare</a> <span title="Total" class="newlabel white">'. $totalcomments. '</span>';
    $wsstats .= ' &nbsp; <a target="_blank" href="'.get_bloginfo('url').'/sitemap/"><i class="fa fa-map"></i> (Mehr Details siehe Sitemap)</a>';
    $wsstats .= '</div><br>';
	return $wsstats;
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
			if (isset($_GET['suchfilter'])) {
				$suchfilter = sanitize_text_field($_GET['suchfilter']);
				$sqlsuchfilter = " AND ( usertype LIKE '%".$suchfilter."%'
					OR username LIKE '%".$suchfilter."%'
					OR browser LIKE '%".$suchfilter."%'
					OR postid LIKE '%".$suchfilter."%'	) ";
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
			$html  = $this->website_display_stats();
			$html .= '<div style="text-align:right"><form name="wcitems" method="get">'.$totales;
			$html .=' &nbsp; <input type="text" size="3" style="width:50px" id="zeitraum" name="zeitraum" value="'.$zeitraum.'">/'.$keepdays.' Tg ';
			$html .='<input type="text" size="3" style="width:50px" id="items" name="items" value="'.$items.'"> Zeilen ';
			$html .='<input type="text" size="20" title="filtern nach Browser, username, usertyp, Einzelbeitrag" placeholder="Suchfilter" id="suchfilter" name="suchfilter" value="'.$suchfilter.'">';
			$html .= '</select><input type="submit" value="'.__('show items', 'pb-chartscodes').'" /></form></div>';

			//	Klicks pro Tag auf Zeitraum
			$labels="";$values='';$label2="";
			$customers = $wpdb->get_results("SELECT datum, COUNT(SUBSTRING(datum,1,10)) AS viscount, datum FROM " . $table . " WHERE 1=1 ".$sqlsuchfilter." GROUP BY SUBSTRING(datum,1,10) ORDER BY datum desc LIMIT ". $zeitraum);
			$html .='<h6>'.sprintf(__('clicks last %s days', 'pb-chartscodes'),$zeitraum).'</h6><table>';
			foreach($customers as $customer){
				$datum = date_i18n(get_option('date_format'), strtotime($customer->datum) + get_option( 'gmt_offset' ) * 3600 );	
				if ( count($customers)==1 )	$html .= '<tr><td>' . number_format_i18n($customer->viscount,0) . '</td><td>' . $datum . '</td></tr>';
				$labels.= $datum .',';
				$label2.= substr($customer->datum,8,2).'.'.substr($customer->datum,5,2).',';
				$values.= $customer->viscount.',';
			}	
			$labels = rtrim($labels, ",");
			$label2 = rtrim($label2, ",");
			$values = rtrim($values, ",");
			$html .= do_shortcode('[chartscodes_line accentcolor=1 yaxis="Klicks pro Tag" xaxis="Datum rückwärts" values="'.$values.'" labels="'.$label2.'"]');
			$html .= do_shortcode('[chartscodes_horizontal_bar absolute="1" accentcolor=1 values="'.$values.'" labels="'.$labels.'"]');
			$html .= '</table>';

			if ( empty($suchfilter) ) {
				
				//	Top x Seiten/Beiträge auf Zeitraum
				$xsum=0;
				$labels="";$values='';
				$customers = $wpdb->get_results("SELECT postid, COUNT(*) AS pidcount FROM " . $table . " WHERE datum >= DATE_ADD( NOW(), INTERVAL -".$zeitraum." DAY ) GROUP BY postid ORDER BY pidcount desc LIMIT ".$items );
				$html .='<h6>'.sprintf(__('top %1s pages last %2s days', 'pb-chartscodes'),$items,$zeitraum).$startday.'</h6><table>';
				foreach($customers as $customer){
					if ( get_post_meta( $customer->postid, 'post_views_count', true ) > 0 ) {
						$labels.= get_the_title($customer->postid).',';
						$values.= $customer->pidcount.',';
						$xsum += absint($customer->pidcount);
						$html .= '<tr><td>' . $customer->pidcount . '</td><td><a title="Post aufrufen" href="'.get_the_permalink($customer->postid).'">' . get_the_title($customer->postid) . '</a></td><td>';
						$diff = time() - strtotime(get_the_date( 'd. F Y', $customer->postid ));
						if (round((intval($diff) / 86400), 0) < 30) {
							$newcolor = "#ffd800";
						} else {
							$newcolor = "#fff";
						}
						$html .= '<i class="fa fa-calendar-o"></i> <span class="newlabel" style="background-color:'.$newcolor.'">'.date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime(get_the_date( 'd. F Y', $customer->postid )) );
						$html .= ' '.ago(get_the_date( 'U', $customer->postid ));
						$html .= '</span></td><td><i class="fa fa-eye"></i>'.sprintf(__(', visitors alltime: %s', 'pb-chartscodes'),number_format_i18n( (float) get_post_meta( $customer->postid, 'post_views_count', true ),0) ) . '</td></tr>';
					}	
				}	
				$html .= '<tfoot><tr><td colspan=4>'.sprintf(__('<strong>%s</strong> sum of values', 'pb-chartscodes'),number_format_i18n($xsum,0)).' <strong>&Oslash; '.number_format_i18n( ($xsum/count($customers)), 2 ).'</strong></td></tr></tfoot>';
				$labels = rtrim($labels, ",");
				$values = rtrim($values, ",");
				$html .= '</table>';
				$html .= do_shortcode('[chartscodes_horizontal_bar accentcolor=1 absolute="1" values="'.$values.'" labels="'.$labels.'"]');

				//	Top x Herkunftsseiten auf Zeitraum
				$xsum=0;
				$customers = $wpdb->get_results("SELECT referer, COUNT(*) AS refcount FROM " . $table . " WHERE datum >= DATE_ADD( NOW(), INTERVAL -".$zeitraum." DAY ) GROUP BY referer ORDER BY refcount desc LIMIT ".$items );
				$html .='<h6>'.sprintf(__('top %1s referers last %2s days', 'pb-chartscodes'),$items,$zeitraum).$startday.'</h6><table>';
				if (!empty($customers)) $toprefer = $customers[0]->refcount; else $toprefer=0;
				foreach($customers as $customer){
					$xsum += absint($customer->refcount);
					$html .= '<tr><td><nobr>';
					$html .= '<progress style="width:400px" max="100" value="'.round($customer->refcount / $toprefer *100).'"></progress> ';
					$html .= number_format_i18n($customer->refcount,0) . '</nobr></td><td>' . $customer->referer . '</td></tr>';
				}	
				$html .= '<tr><td colspan=2>'.sprintf(__('<strong>%s</strong> sum of values', 'pb-chartscodes'),number_format_i18n($xsum,0)).' &Oslash; '.number_format_i18n( ($xsum/count($customers)), 2 ).'</td></tr></table>';
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
				$diff = time() - strtotime($customer->datum);
				if (round((intval($diff) / 86400), 0) < 30) {
					$newcolor = "#ffd800";
				} else {
					$newcolor = "#fff";
				}
				$html .= '<td><span class="newlabel" style="background-color:'.$newcolor.'">' . $datum . ' ' . ago(strtotime($customer->datum)).'</span></td></tr>';
			}	
			$html .= '</table>';

			//	Besucher nach Stunde auf Zeitraum
			$labels="";$values='';
			$customers = $wpdb->get_results("SELECT SUBSTRING(datum,12,2) AS stunde, COUNT(SUBSTRING(datum,12,2)) AS viscount, datum FROM " . $table . " WHERE datum >= DATE_ADD( NOW(), INTERVAL -".$zeitraum." DAY ) ".$sqlsuchfilter." GROUP BY SUBSTRING(datum,12,2) ORDER BY SUBSTRING(datum,12,2) ");
			$html .='<h6>'.sprintf(__('clicks by hour %1s last %2s days', 'pb-chartscodes'),$filtertitle,$zeitraum).$startday.'</h6><table>';
			foreach($customers as $customer){
				if ( count($customers)==1 ) $html .= '<tr><td>' . $customer->viscount . '</td><td>' . $datum . '</td></tr>';
				$labels.= $customer->stunde.',';
				$values.= $customer->viscount.',';
			}	
			$labels = rtrim($labels, ",");
			$values = rtrim($values, ",");
			$html .= do_shortcode('[chartscodes_bar accentcolor=1 absolute="1" values="'.$values.'" labels="'.$labels.'"]');
			$html .= '</table>';

			//	Besucher nach Wochentag auf Zeitraum
			$labels="";$values='';
			$customers = $wpdb->get_results("SELECT WEEKDAY(SUBSTRING(datum,1,10)) AS wotag, COUNT(WEEKDAY(SUBSTRING(datum,1,10))) AS viscount, datum FROM " . $table . " WHERE datum >= DATE_ADD( NOW(), INTERVAL -".$zeitraum." DAY ) ".$sqlsuchfilter." GROUP BY WEEKDAY(SUBSTRING(datum,1,10)) ORDER BY SUBSTRING(datum,1,10) ");
			$html .='<h6>'.sprintf(__('clicks by weekday %2s last %1s days', 'pb-chartscodes'),$filtertitle,$zeitraum) . $startday . '</h6><table>';
			foreach($customers as $customer){
				if ( count($customers)==1 ) $html .= '<tr><td>' . $customer->viscount . '</td><td>' . $datum . '</td></tr>';
				$labels.= $tage[$customer->wotag].',';
				$values.= $customer->viscount.',';
			}	
			$labels = rtrim($labels, ",");
			$values = rtrim($values, ",");
			$html .= do_shortcode('[chartscodes_bar accentcolor=1 absolute="1" values="'.$values.'" labels="'.$labels.'"]');
			$html .= '</table>';

			//	Top x Browser auf Zeitraum
			$labels="";$values='';
			$customers = $wpdb->get_results("SELECT browser, COUNT(browser) AS bcount FROM " . $table . " WHERE datum >= DATE_ADD( NOW(), INTERVAL -".$zeitraum." DAY ) ".$sqlsuchfilter." GROUP BY browser ORDER BY bcount desc LIMIT ".$items);
			$html .='<h6>'.sprintf(__('Top %1s Browsers %2s last %3s days', 'pb-chartscodes'),$items,$filtertitle,$zeitraum).'</h6><table>';
			foreach($customers as $customer){
				if ( count($customers)==1 ) $html .= '<tr><td>' . $customer->bcount . '</td><td>' . $customer->browser . '</td></tr>';
				$labels.= $customer->browser.',';
				$values.= $customer->bcount.',';
			}	
			$labels = rtrim($labels, ",");
			$values = rtrim($values, ",");
			$html .= do_shortcode('[chartscodes_polar accentcolor=1 absolute="1" values="'.$values.'" labels="'.$labels.'"]');
			$html .= '</table>';

			//	Top x Länder auf Zeitraum
			$labels="";$values='';
			$customers = $wpdb->get_results("SELECT country, COUNT(country) AS ccount, datum FROM " . $table . " WHERE datum >= DATE_ADD( NOW(), INTERVAL -".$zeitraum." DAY ) GROUP BY country ORDER BY ccount desc LIMIT ".$items);
			$html .='<h6>'.sprintf(__('Top %1s countries %2s last %3s days', 'pb-chartscodes'),$items,$filtertitle,$zeitraum).'</h6><table>';
			foreach($customers as $customer){
				if ( count($customers)==1 ) $html .= '<tr><td>' . $customer->ccount . '</td><td>' . $this->country_code('de',$customer->country) . '</td></tr>';
				$labels.= $this->country_code('de',$customer->country) . ',';
				$values.= $customer->ccount . ',';
			}	
			$labels = rtrim($labels, ",");
			$values = rtrim($values, ",");
			$html .= do_shortcode('[chartscodes_polar accentcolor=1 absolute="1" values="'.$values.'" labels="'.$labels.'"]');
			$html .= '</table>';

			//	Archive: Beiträge pro Monat letzte 36 Monate
			if ( empty($suchfilter) ) {
				$html .= do_shortcode('[posts_per_month_last accentcolor=1 months=44]');
				$html .= '</table>';
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
						$country = 'EUROPEANUNION';
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
			'details' => 0,   // get more details like ip net and referrer
			'browser' => 0,  // show user agent string and browser info
		), $atts ));
		$yourdetails='';
		if ( $details ) {
			if(!empty($_SERVER['HTTP_CLIENT_IP'])){
				$ip = $_SERVER['HTTP_CLIENT_IP'];
			} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
				$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
			}
			// ip anonymisieren wegen dsgvo
			$ip = long2ip(ip2long($ip) & 0xFFFFFF00);
		 	$referer = wp_get_referer();
			$yourdetails = "<br><strong>".__('ip network', 'pb-chartscodes').":</strong> ". $ip . "<br><strong>".__('referer', 'pb-chartscodes')."</strong> " . $referer;
		}
		$yourbrowser='';
		if ( $browser ) {
			$ua=$this->getBrowser();
			$yourbrowser = "<br><strong>Angemeldet als</strong> ". $ua['username'] . ' ' . $ua['usertype'];
			$yourbrowser .= "<br><strong>".__('browser', 'pb-chartscodes')."</strong> " . $ua['name'] . " " . $ua['version'] . " unter " .$ua['platform']  . " " .substr($ua['language'],0,2) . "<br><small>" . $ua['userAgent']."</small>";
		}
        if (($info = $this->get_info($ip)) != false) {
            $flag = '<div style="display:inline">'.$this->country_code('de',$info->code).' ('.$info->code.') &nbsp; '.$this->get_flag($info).'</div>';
		} else {
            $flag = '<div style="display:inline">privates Netzwerk &nbsp; '.$this->get_flag($info).'</div>';
		}	
		if ( !empty($iso) ) {
			$flag =  $this->get_flag( (object) [ 'code' => strtoupper($iso) ]);
			if ($showland) $flag .= '&nbsp'.$this->get_country( (object) [ 'code' => strtoupper($iso)]);
		}
		if ( !empty($name) ) {
			$flag =  $this->get_flag( (object) [ 'code' => $this->get_isofromland($name)->code ]);
			if ( $details ) {
				$flag .= ' &nbsp; ' . $this->get_isofromland($name)->code.' '.$this->get_isofromland($name)->name;
				$flag .= ' &nbsp; ' . $this->country_code('de',$this->get_isofromland($name)->code);
			} 
		}
		return $flag . $yourbrowser . $yourdetails;
    }

    public function options_validate($input) {
        if(isset($input['db_update'])){
            try {
                $this->update_db_file();
                $this->update_db();
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
            $this->update_db_file();
            $this->update_db();
        } catch(Exception $e){
        }
    }

    public function install(){
        if (self::safe_slug.'_db_version' < 2){
			global $wpdb;
            $old_table_name = $wpdb->prefix . 'ipflag';
            $wpdb->query('DROP TABLE IF EXISTS '.$old_table_name.';');
        }

        if (self::safe_slug.'_db_version' < 5){
            $this->options['auto_update'] = '1';
        }
        try {
            $this->update_db();
        } catch(Exception $e){
        }
        update_option(self::safe_slug.'_options', $this->options);
        update_option(self::safe_slug.'_db_version', self::default_db_version);
    }

    public function update_db_check() {
        if ($this->db_version != self::default_db_version) {
            $this->install();
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
			<div class="postbox">
			<p><code>[ipflag ip="123.20.30.0" iso="mx" showland=0/1 details=0/1 browser=1]</code>
				liefert eine Flagge und das Land zu einer IP odr einem IP-Netz. Die letzte IP-Ziffer wird wegen DSGVO anonymisiert<br>
				iso="xx" liefert die Flagge zum ISO-Land oder die EU-Flagge für private und unbekannte Netzwerke<br>
				showland=1 zeigt ISO und Land hinter der Flagge an
				browser=1 liefert Betriebssystem und Browser des Besuchers, details=1 liefert den Referrer, das IP-Netz<br><br>
				<code>[webcounter admin=0]</code> zählt Seitenzugriffe und füllt Statistikdatenbank, admin=1 zum Auswerten mit Adminrechten<br>
				Ist die Admin /webcounter-Seite aufgerufen, kann über das Eingabefeld oder den optionalen URL-Parameter ?items=x die Ausgabe-Anzahl einiger Listeneinträge verändert werden.
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
			<div class="postbox">
				<p><code>[qrcode type="code-39" text="Hallo Welt" ]</code>
				<p><code>[qrcode type="ean-13" text="9780201379624" ]</code>
				<p><code>[qrcode text="tel:+49304030568956834058340" ]</code>
				<p><code>[qrcode text="tel:00492307299607" size=3 margin=3]</code>
				erstellt QR-Codes als Shortcode an der Cursorposition (Dokumentation und Parameter siehe Readme.txt)</p>                    
            </div></div>
			<div class="img-wrap">
				<h2><?php esc_html_e( 'Bar and Piecharts', 'pb-chartscodes' ); ?></h2>
				<div class="postbox">
				<p>Shortcode Parameter: absolute="1" wenn keine Prozentwerte mitgegeben werden, sondern absolute Werte<br>
					fontfamily="Arial" fontstyle="bold". Für die PieCharts sollten maximal 20 Werte angegeben werden, bei den Bar Charts bis zu 50, beim horizontal Bar 200 und beim Linechart 50<br>
					Bar Charts: bei absoluten Werten wird größter Wert in der Folge 100%, Werte werden angezeigt wenn >0<br> 
					Bleibt der Parameter "colors" leer, werden bei "accentcolor=0" zufällige bunte helle Farben gewählt, bei "accentcolor=1" Akzentfarben aus der Linkfarbe des Themes bezogen
					<br> accentcolor=0/1 kann auch für die post per month Statistik und als HTML Widget angewendet werden
					</p>
				</div></div>
			<div class="img-wrap postbox">
				<img src="<?php echo PB_ChartsCodes_URL_PATH . 'assets/screenshot-1.png' ?>"  alt="<?php esc_attr_e( 'Default Pie Chart', 'pb-chartscodes' ); ?>">
                <p><code>[chartscodes accentcolor=false absolute="1" title="Pie Chart" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]</code>
				<?php esc_attr_e( 'Default Pie Chart', 'pb-chartscodes' ); ?> </p>                    
            </div>
            <div class="img-wrap postbox">
                <img src="<?php echo PB_ChartsCodes_URL_PATH . 'assets/screenshot-2.png' ?>"  alt="<?php esc_attr_e( 'Doughnut Pie Chart', 'pb-chartscodes' ); ?>">
                <p><code>[chartscodes_donut title="Donut Pie Chart" absolute="1" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]</code>
				<?php esc_attr_e( 'Doughnut Pie Chart', 'pb-chartscodes' ); ?> </p>
			</div>
            <div class="img-wrap postbox">
                <img src="<?php echo PB_ChartsCodes_URL_PATH . 'assets/screenshot-3.png' ?>"  alt="<?php esc_attr_e( 'Polar Pie Chart', 'pb-chartscodes' ); ?>">
                <p><code>[chartscodes_polar title="Polar Chart mit Segmenten" absolute="1" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]</code>
				<?php esc_attr_e( 'Polar Pie Chart', 'pb-chartscodes' ); ?></p>                    
            </div>
            <div class="img-wrap postbox">
                <img src="<?php echo PB_ChartsCodes_URL_PATH . 'assets/screenshot-8.png' ?>"  alt="<?php esc_attr_e( 'Radar Chart', 'pb-chartscodes' ); ?>">
                <p><code>[chartscodes_radar title="Radar Chart" values="10,20,22,8,33,21" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi,Erdbeeren"]</code>
				<?php esc_attr_e( 'Radar Chart', 'pb-chartscodes' ); ?> </p>                    
            </div>
			<div class="img-wrap postbox">
                <img src="<?php echo PB_ChartsCodes_URL_PATH . 'assets/screenshot-4.png' ?>"  alt="<?php esc_attr_e( 'Bar Graph Chart', 'pb-chartscodes' ); ?>">
                <p><code>[chartscodes_bar title="Balkenchart" absolute="1" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]</code>
				<?php esc_attr_e( 'Bar Graph Chart', 'pb-chartscodes' ); ?> </p>                    
            </div>
            <div class="img-wrap postbox">
                <img src="<?php echo PB_ChartsCodes_URL_PATH . 'assets/screenshot-5.png' ?>"  alt="<?php esc_attr_e( 'Horizontal Bar Graph Chart', 'pb-chartscodes' ); ?>">
                <p><code>[chartscodes_horizontal_bar title="Balken horizontal" absolute="1" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]</code>
				<?php esc_attr_e( 'Horizontal Bar Graph Chart', 'pb-chartscodes' ); ?></p>
            </div>
			<div class="img-wrap postbox">
				<img src="<?php echo PB_ChartsCodes_URL_PATH . 'assets/screenshot-7.png' ?>"  alt="<?php esc_attr_e( 'Default Line Chart', 'pb-chartscodes' ); ?>">
                <p><code>[chartscodes_line accentcolor=1 title="Obst Line Chart" xaxis="Obstsorte" yaxis="Umsatz" height="350" values="10,20,10,5,30,20,5" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi,Cranberry,Mango"]</code>
				<?php esc_attr_e( 'Default Line Chart', 'pb-chartscodes' ); ?> </p>                    
            </div>
			<div class="img-wrap postbox">
                <img src="<?php echo PB_ChartsCodes_URL_PATH . 'assets/screenshot-6.png' ?>"  alt="<?php esc_attr_e( 'Horizontal Bar Graph Chart', 'pb-chartscodes' ); ?>">
				<p><code>[posts_per_month_last months=x]</code> zeigt die letzen 1-12 Monate Posts per Month als Bargraph an, wenn Months nicht angegeben für 12 Monate
                </p>                    
            </div>
        </div> <?php
    }

    public function settings_init(){
        register_setting(self::safe_slug.'_options', self::safe_slug.'_options', array($this, 'options_validate'));
        add_settings_section('database_section', __('ipflag database options', 'pb-chartscodes'), array($this, 'settings_section_database'), __FILE__);
        add_settings_field(self::safe_slug.'_webcounterkeepdays', __('delete webhits older than (days):', 'pb-chartscodes'), array($this, 'settings_field_webcounterkeepdays'), __FILE__, 'database_section');
        add_settings_field(self::safe_slug.'_auto_update', __('Enable automatic weekly database update check:', 'pb-chartscodes'), array($this, 'settings_field_auto_update'), __FILE__, 'database_section');
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

    public function settings_field_db_status(){
        $local_timestamp =  $this->local_timestamp();
        if($local_timestamp !== 0){
            $gmt_offset = get_option('gmt_offset');
            $date_format = get_option('date_format');
            $time_format = get_option('time_format');

            $h_time = date_i18n($date_format.' @ '.$time_format, $local_timestamp + ($gmt_offset  * 3600));

            echo $h_time;
        }else{
            echo  __('Database missing or corrupted, please update', 'pb-chartscodes');
        }
    }

    public function settings_field_db_update(){
        echo '<input id="'.self::safe_slug.'_db_update" name="'.self::safe_slug.'_options[db_update]" class="button-secondary" type="submit" value="'.__('Update', 'pb-chartscodes').'" />';
    }

    public function update_db_file(){
        $remote_timestamp = $this->remote_timestamp();
        $local_timestamp = $this->local_timestamp();

        if($remote_timestamp <= $local_timestamp){
            throw new Exception(__('ipflag database already up to date.', 'pb-chartscodes'), 1);
        }else{
            $response = wp_remote_get($this->remote_db_url, array('timeout' => self::http_timeout));
            if(is_wp_error($response) || !isset($response['body']) || $response['body'] === ''){
                throw new Exception(__('Couldn\'t fetch ipflag database zip file. Fetching remote content not supported or remote server is down.', 'pb-chartscodes'), 2);
            } else {
                $new_db_zip = $response['body'];

                require_once(ABSPATH . 'wp-admin/includes/file.php');
                WP_Filesystem();
                global $wp_filesystem;

                if (!$wp_filesystem->put_contents($this->db_zip_file, $new_db_zip, FS_CHMOD_FILE)) {
                    throw new Exception(__( 'Couldn\'t write ipflag database zip file to local file system. Please check permissions.', 'pb-chartscodes'), 3);
                }

                if (!unzip_file($this->db_zip_file, dirname($this->db_zip_file))) {
                    throw new Exception(__('Couldn\'t unzip ipflag database zip file to local file system. Please check permissions and your server unzip capabilities.', 'pb-chartscodes'), 4);
                }

                unlink($this->db_zip_file);

                if (!$wp_filesystem->put_contents($this->db_version_file, $this->parse_timestamp($remote_timestamp), FS_CHMOD_FILE)) {
                    throw new Exception(__('Couldn\'t write ipflag database version file to local file system. Please check permissions.', 'pb-chartscodes'), 5);
                }
            }
        }
    }

    protected function update_db(){
        if(!file_exists($this->db_file)){
            try {
                $this->update_db_file();
            } catch(Exception $e){}
        }
        // To protect update server deschedule all cron jobs on plugin update
        $this->deschedule_update();
        global $wpdb;
        $ip_ranges_table_name = $wpdb->prefix . self::ip_ranges_table_suffix;
        $countries_table_name = $wpdb->prefix . self::countries_table_suffix;
        $wpdb->query('DROP TABLE IF EXISTS '.$ip_ranges_table_name.';');
        $wpdb->query('DROP TABLE IF EXISTS '.$countries_table_name.';');

        $sql_countries = 'CREATE TABLE '.$countries_table_name.' (
        cid INT(4) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        code CHAR(2) NOT NULL,
        name VARCHAR(150) NOT NULL,
        latitude FLOAT NOT NULL,
        longitude FLOAT NOT NULL) ENGINE=MyISAM DEFAULT CHARACTER SET utf8, COLLATE utf8_general_ci;';

        $sql_ip_ranges = 'CREATE TABLE '.$ip_ranges_table_name.' (
        id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        fromip INT(10) UNSIGNED NOT NULL,
        toip INT(10) UNSIGNED NOT NULL,
        cid INT(4) UNSIGNED NOT NULL,
        INDEX (fromip ASC, toip ASC, cid ASC)) ENGINE=MyISAM DEFAULT CHARACTER SET utf8, COLLATE utf8_general_ci;';

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_countries);
        dbDelta($sql_ip_ranges);
        require_once(dirname(__FILE__) . '/iso-3166-2.php');

        $sql = '';
        foreach($country_data as $code => $code_data){
            $sql .= '('.$code_data['cid'].', "'.$code.'", "'.$code_data['name'].'", '.$code_data['latitude'].', '.$code_data['longitude'].'), ';
        }
        $wpdb->query('INSERT INTO '.$countries_table_name.' (cid, code, name, latitude, longitude) VALUES '.substr($sql, 0, -2));

        $limit_no_insert = 1000;
        $counter = 0;
        $sql = '';
        if (($input = fopen($this->db_file, 'r')) !== false) {
            while (($file_data = fgetcsv($input, 1000, ' ')) !== false) {
                if(isset($country_data[$file_data[2]])){
                    $counter++;
                    $sql .= '('.$file_data[0].', '.$file_data[1].', '.$country_data[$file_data[2]]['cid'].'), ';

                    if($counter == $limit_no_insert){
                        $wpdb->query('INSERT INTO '.$ip_ranges_table_name.' (fromip, toip, cid) VALUES '.substr($sql,0,-2));
                        $counter = 0;
                        $sql = '';
                    }
                }
            }
            $wpdb->query('INSERT INTO '.$ip_ranges_table_name.' (fromip, toip, cid) VALUES '.substr($sql,0,-2));
            fclose($input);
        } else {
            throw new Exception(__('Couldn\'t read ipflag database file from local file system. Please check permissions.', 'pb-chartscodes'), 6);
        }
    }

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
// ------------------------------ IPFlag Klasse Ende ----------------------------

// Zeitdifferenz ermitteln und gestern/vorgestern/morgen schreiben: chartscodes, dedo, foldergallery, timeclock, w4-post-list
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
			if ($xlang == 'de-DE') {
				$prepo = __('vor', 'penguin');
				$postpo = '';
			} else {
				$prepo = '';
				$postpo = __('ago', 'penguin');
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
			$hdate = ' ' . $prepo . ' ' . human_time_diff(intval($timestamp), $now) . ' ' . $postpo;
		}
		return $hdate;
	}
}	

// ========  Letze X Besucher der Seite anzeigen (nur als Admin) - pageid leer lassen für Gesamtstatistik  ===
// Aufruf in penguin,template-parts/meta-bottom.php
function lastxvisitors ($items,$pageid) {
	$brosicons = new ipflag();
	if (!empty($pageid)) { $pagefilter='AND postid = '.$pageid; } else {$pagefilter='';}
	global $wpdb;
	$table = $wpdb->prefix . "sitevisitors";
	$customers = $wpdb->get_results("SELECT * FROM " . $table . " WHERE datum >= DATE_ADD( NOW(), INTERVAL -90 DAY ) ".$pagefilter." ORDER BY datum desc LIMIT ".$items);
	$html ='<div class="noprint"><h6>'.__("Last Visitors","pb-chartscodes").'</h6><table style="table-layout:fixed">';
	foreach($customers as $customer){
		$datum = date_i18n( get_option('date_format') .' H:i:s', strtotime($customer->datum) );
		$diff = time() - strtotime($customer->datum);
		if (round((intval($diff) / 86400), 0) < 30) { $newcolor = "#ffd80088"; } else { $newcolor = "#fff"; }
		$html .= '<tr><td><abbr title="#'.$customer->id.' - '.$customer->useragent.'">' . $brosicons->showbrowosicon($customer->browser) . ' ' . $customer->browser .' ' . $customer->browserver .'</abbr></td>';
		$html .= '<td><abbr>' .$brosicons->showbrowosicon($customer->platform).' '. substr($customer->platform,0,19). ' ' . substr($customer->language,0,2) .'</abbr>';
		$html .= ' <i class="fa fa-map-marker"></i> <abbr>' . $customer->userip .'</abbr></td>';
		if ($customer->country == 'EUROPEANUNION') $customer->country = 'EU';
		$html .= '<td>' .do_shortcode('[ipflag iso="'.$customer->country.'"]') .' ';
		$html .= '<i class="fa fa-user"></i> <abbr>'. $customer->username . ' | '.$customer->usertype .'</abbr></td>';
		if (empty($pageid)) $html .= '<td><abbr><a title="Post aufrufen" href="'.get_the_permalink($customer->postid).'">' . get_the_title($customer->postid) .'</abbr></a></td>';
		$html .= '<td><span class="newlabel" style="background-color:'.$newcolor.'">' . $datum . ' ' . ago(strtotime($customer->datum)).'</span></td></tr>';
	}	
	$html .= '</table></div>';
	return $html;
}

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
?>
