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
Version: 11.1.83
Stable tag: 11.1.83
Requires at least: 5.1
Tested up to: 6.1.1
Requires PHP: 8.0
*/

if ( ! defined( 'ABSPATH' ) ) {	exit; } // Exit if accessed directly.

add_action( 'plugins_loaded', 'chartscodes_textdomain' );
function chartscodes_textdomain() {
	load_plugin_textdomain( 'pb-chartscodes', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
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

class DoQRCode
{
	private static $_instance ;

	/** * Init */
	private function __construct() {
		add_shortcode( 'qrcode', array( $this, 'shortcode_handler' ) ) ;
	}

	/** * Shortcode handler */
	public function shortcode_handler( $atts, $content ) {
		require_once DOQRCODE_DIR . 'barcode.php' ;
		$symbology = 'qr';
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

		// Webcounterseite für admin erzeugen
		$new_page_title = 'Webcounter Stats';
		$slug = 'webcounter';
		$new_page_content = '[webcounter admin=1]';
		$new_page_template = ''; //ex. template-custom.php. Leave blank for default
		$page_check = get_page_by_title($new_page_title);
		$new_page = array(
			'post_type' => 'page',
			'post_name'         =>   $slug,
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
  if (empty ($countries)) $countries = unserialize ('a:2:{s:2:"en";a:204:{s:2:"AF";s:11:"Afghanistan";s:2:"AL";s:7:"Albania";s:2:"AS";s:14:"American Samoa";s:2:"AD";s:7:"Andorra";s:2:"AO";s:6:"Angola";s:2:"AI";s:8:"Anguilla";s:2:"AQ";s:10:"Antarctica";s:2:"AG";s:19:"Antigua and Barbuda";s:2:"AR";s:9:"Argentina";s:2:"AM";s:7:"Armenia";s:2:"AW";s:5:"Aruba";s:2:"AT";s:7:"Austria";s:2:"AU";s:9:"Australia";s:2:"AZ";s:10:"Azerbaijan";s:2:"BS";s:7:"Bahamas";s:2:"BH";s:7:"Bahrain";s:2:"BD";s:10:"Bangladesh";s:2:"BB";s:8:"Barbados";s:2:"BY";s:7:"Belarus";s:2:"BE";s:7:"Belgium";s:2:"BZ";s:6:"Belize";s:2:"BJ";s:5:"Benin";s:2:"BM";s:7:"Bermuda";s:2:"BT";s:6:"Bhutan";s:2:"BO";s:7:"Bolivia";s:2:"BA";s:22:"Bosnia and Herzegovina";s:2:"BW";s:8:"Botswana";s:2:"BV";s:13:"Bouvet Island";s:2:"BR";s:6:"Brazil";s:2:"BN";s:17:"Brunei Darussalam";s:2:"BG";s:8:"Bulgaria";s:2:"BF";s:12:"Burkina Faso";s:2:"BI";s:7:"Burundi";s:2:"KH";s:8:"Cambodia";s:2:"CM";s:8:"Cameroon";s:2:"CA";s:6:"Canada";s:2:"CV";s:10:"Cape Verde";s:2:"KY";s:14:"Cayman Islands";s:2:"CF";s:24:"Central African Republic";s:2:"TD";s:4:"Chad";s:2:"CL";s:5:"Chile";s:2:"CN";s:5:"China";s:2:"CO";s:8:"Colombia";s:2:"KM";s:7:"Comoros";s:2:"CG";s:5:"Congo";s:2:"CK";s:12:"Cook Islands";s:2:"CR";s:10:"Costa Rica";s:2:"CI";s:14:"Côte d\'Ivoire";s:2:"HR";s:7:"Croatia";s:2:"CU";s:4:"Cuba";s:2:"CZ";s:14:"Czech Republic";s:2:"DK";s:7:"Denmark";s:2:"DJ";s:8:"Djibouti";s:2:"DO";s:18:"Dominican Republic";s:2:"TP";s:10:"East Timor";s:2:"EC";s:7:"Ecuador";s:2:"EG";s:5:"Egypt";s:2:"SV";s:11:"El salvador";s:2:"GQ";s:17:"Equatorial Guinea";s:2:"ER";s:7:"Eritrea";s:2:"EE";s:7:"Estonia";s:2:"ET";s:8:"Ethiopia";s:2:"FK";s:16:"Falkland Islands";s:2:"FO";s:13:"Faroe Islands";s:2:"FJ";s:4:"Fiji";s:2:"FI";s:7:"Finland";s:2:"FR";s:6:"France";s:2:"GF";s:13:"French Guiana";s:2:"PF";s:16:"French Polynesia";s:2:"GA";s:5:"Gabon";s:2:"GM";s:6:"Gambia";s:2:"GE";s:7:"Georgia";s:2:"DE";s:7:"Germany";s:2:"GH";s:5:"Ghana";s:2:"GI";s:9:"Gibraltar";s:2:"GR";s:6:"Greece";s:2:"GL";s:9:"Greenland";s:2:"GD";s:7:"Grenada";s:2:"GP";s:10:"Guadeloupe";s:2:"GU";s:4:"Guam";s:2:"GT";s:9:"Guatemala";s:2:"GN";s:6:"Guinea";s:2:"GY";s:6:"Guyana";s:2:"HT";s:5:"Haiti";s:2:"VA";s:7:"Vatican";s:2:"HN";s:8:"Honduras";s:2:"HU";s:7:"Hungary";s:2:"IS";s:7:"Iceland";s:2:"IN";s:5:"India";s:2:"ID";s:9:"Indonesia";s:2:"IR";s:4:"Iran";s:2:"IQ";s:4:"Iraq";s:2:"IE";s:7:"Ireland";s:2:"IL";s:6:"Israel";s:2:"IT";s:5:"Italy";s:2:"JM";s:7:"Jamaica";s:2:"JP";s:5:"Japan";s:2:"JO";s:6:"Jordan";s:2:"KZ";s:9:"Kazakstan";s:2:"KE";s:5:"Kenya";s:2:"KI";s:8:"Kiribati";s:2:"KW";s:6:"Kuwait";s:2:"KG";s:9:"Kyrgystan";s:2:"LA";s:3:"Lao";s:2:"LV";s:6:"Latvia";s:2:"LB";s:7:"Lebanon";s:2:"LS";s:7:"Lesotho";s:2:"LI";s:13:"Liechtenstein";s:2:"LT";s:9:"Lithuania";s:2:"LU";s:10:"Luxembourg";s:2:"MO";s:5:"Macau";s:2:"MK";s:10:"Macedonia ";s:2:"MG";s:10:"Madagascar";s:2:"MW";s:6:"Malawi";s:2:"MY";s:8:"Malaysia";s:2:"MV";s:8:"Maldives";s:2:"ML";s:4:"Mali";s:2:"MT";s:5:"Malta";s:2:"MR";s:10:"Mauritania";s:2:"MU";s:9:"Mauritius";s:2:"YT";s:7:"Mayotte";s:2:"MX";s:6:"Mexico";s:2:"FM";s:10:"Micronesia";s:2:"MD";s:7:"Moldova";s:2:"MC";s:6:"Monaco";s:2:"MN";s:8:"Mongolia";s:2:"MS";s:10:"Montserrat";s:2:"MA";s:7:"Morocco";s:2:"MZ";s:10:"Mozambique";s:2:"MM";s:7:"Myanmar";s:2:"NA";s:7:"Namibia";s:2:"NR";s:5:"Nauru";s:2:"NP";s:5:"Nepal";s:2:"NL";s:11:"Netherlands";s:2:"NZ";s:11:"New Zealand";s:2:"NI";s:9:"Nicaragua";s:2:"NE";s:5:"Niger";s:2:"NG";s:7:"Nigeria";s:2:"NU";s:4:"Niue";s:2:"NF";s:14:"Norfolk Island";s:2:"KP";s:11:"North Korea";s:2:"NO";s:6:"Norway";s:2:"OM";s:4:"Oman";s:2:"PK";s:8:"Pakistan";s:2:"PW";s:5:"Palau";s:2:"PA";s:6:"Panama";s:2:"PG";s:16:"Papua New Guinea";s:2:"PY";s:8:"Paraguay";s:2:"PE";s:4:"Peru";s:2:"PH";s:11:"Philippines";s:2:"PL";s:6:"Poland";s:2:"PT";s:8:"Portugal";s:2:"PR";s:11:"Puerto Rico";s:2:"RO";s:7:"Romania";s:2:"RU";s:6:"Russia";s:2:"RW";s:6:"Rwanda";s:2:"WS";s:5:"Samoa";s:2:"SM";s:10:"San Marino";s:2:"SA";s:12:"Saudi Arabia";s:2:"SN";s:7:"Senegal";s:2:"SC";s:10:"Seychelles";s:2:"SL";s:12:"Sierra Leone";s:2:"SG";s:9:"Singapore";s:2:"SK";s:8:"Slovakia";s:2:"SB";s:15:"Solomon Islands";s:2:"SO";s:7:"Somalia";s:2:"ZA";s:12:"South Africa";s:2:"KR";s:11:"South Korea";s:2:"ES";s:5:"Spain";s:2:"LK";s:9:"Sri Lanka";s:2:"SD";s:5:"Sudan";s:2:"SR";s:8:"Suriname";s:2:"SZ";s:9:"Swaziland";s:2:"SE";s:6:"Sweden";s:2:"CH";s:11:"Switzerland";s:2:"SY";s:5:"Syria";s:2:"TW";s:6:"Taiwan";s:2:"TJ";s:10:"Tajikistan";s:2:"TZ";s:8:"Tanzania";s:2:"TH";s:8:"Thailand";s:2:"TG";s:4:"Togo";s:2:"TO";s:5:"Tonga";s:2:"TT";s:19:"Trinidad and Tobago";s:2:"TN";s:7:"Tunisia";s:2:"TR";s:6:"Turkey";s:2:"TM";s:12:"Turkmenistan";s:2:"TV";s:6:"Tuvalu";s:2:"UG";s:6:"Uganda";s:2:"UA";s:7:"Ukraine";s:2:"AE";s:20:"United Arab Emirates";s:2:"GB";s:14:"United Kingdom";s:2:"US";s:24:"United States of America";s:2:"UY";s:7:"Uruguay";s:2:"UZ";s:10:"Uzbekistan";s:2:"VU";s:7:"Vanuatu";s:2:"VE";s:9:"Venezuela";s:2:"VN";s:7:"Vietnam";s:2:"VG";s:14:"Virgin Islands";s:2:"EH";s:14:"Western Sahara";s:2:"YE";s:5:"Yemen";s:2:"YU";s:10:"Yugoslavia";s:2:"ZR";s:5:"Zaire";s:2:"ZM";s:6:"Zambia";s:2:"ZW";s:8:"Zimbabwe";}s:2:"de";a:204:{s:2:"AF";s:11:"Afghanistan";s:2:"AL";s:8:"Albanien";s:2:"AS";s:18:"Amerikanisch Samoa";s:2:"AD";s:7:"Andorra";s:2:"AO";s:6:"Angola";s:2:"AI";s:8:"Anguilla";s:2:"AQ";s:9:"Antarktis";s:2:"AG";s:19:"Antigua und Barbuda";s:2:"AR";s:11:"Argentinien";s:2:"AM";s:8:"Armenien";s:2:"AW";s:5:"Aruba";s:2:"AT";s:11:"Österreich";s:2:"AU";s:10:"Australien";s:2:"AZ";s:13:"Aserbaidschan";s:2:"BS";s:7:"Bahamas";s:2:"BH";s:7:"Bahrain";s:2:"BD";s:10:"Bangladesh";s:2:"BB";s:8:"Barbados";s:2:"BY";s:13:"Weißrussland";s:2:"BE";s:7:"Belgien";s:2:"BZ";s:6:"Belize";s:2:"BJ";s:5:"Benin";s:2:"BM";s:7:"Bermuda";s:2:"BT";s:6:"Bhutan";s:2:"BO";s:8:"Bolivien";s:2:"BA";s:19:"Bosnien Herzegowina";s:2:"BW";s:8:"Botswana";s:2:"BV";s:13:"Bouvet Island";s:2:"BR";s:9:"Brasilien";s:2:"BN";s:17:"Brunei Darussalam";s:2:"BG";s:9:"Bulgarien";s:2:"BF";s:12:"Burkina Faso";s:2:"BI";s:7:"Burundi";s:2:"KH";s:10:"Kambodscha";s:2:"CM";s:7:"Kamerun";s:2:"CA";s:6:"Kanada";s:2:"CV";s:9:"Kap Verde";s:2:"KY";s:13:"Cayman Inseln";s:2:"CF";s:28:"Zentralafrikanische Republik";s:2:"TD";s:6:"Tschad";s:2:"CL";s:5:"Chile";s:2:"CN";s:5:"China";s:2:"CO";s:9:"Kolumbien";s:2:"KM";s:7:"Comoros";s:2:"CG";s:5:"Kongo";s:2:"CK";s:11:"Cook Inseln";s:2:"CR";s:10:"Costa Rica";s:2:"CI";s:15:"Elfenbeinküste";s:2:"HR";s:8:"Kroatien";s:2:"CU";s:4:"Kuba";s:2:"CZ";s:10:"Tschechien";s:2:"DK";s:9:"Dänemark";s:2:"DJ";s:8:"Djibouti";s:2:"DO";s:23:"Dominikanische Republik";s:2:"TP";s:8:"Osttimor";s:2:"EC";s:7:"Ecuador";s:2:"EG";s:8:"Ägypten";s:2:"SV";s:11:"El Salvador";s:2:"GQ";s:18:"Äquatorial Guinea";s:2:"ER";s:7:"Eritrea";s:2:"EE";s:7:"Estland";s:2:"ET";s:10:"Äthiopien";s:2:"FK";s:15:"Falkland Inseln";s:2:"FO";s:12:"Faroe Inseln";s:2:"FJ";s:4:"Fiji";s:2:"FI";s:7:"Finland";s:2:"FR";s:10:"Frankreich";s:2:"GF";s:19:"Französisch Guiana";s:2:"PF";s:23:"Französisch Polynesien";s:2:"GA";s:5:"Gabon";s:2:"GM";s:6:"Gambia";s:2:"GE";s:8:"Georgien";s:2:"DE";s:11:"Deutschland";s:2:"GH";s:5:"Ghana";s:2:"GI";s:9:"Gibraltar";s:2:"GR";s:12:"Griechenland";s:2:"GL";s:9:"Grönland";s:2:"GD";s:7:"Grenada";s:2:"GP";s:10:"Guadeloupe";s:2:"GU";s:4:"Guam";s:2:"GT";s:9:"Guatemala";s:2:"GN";s:6:"Guinea";s:2:"GY";s:6:"Guyana";s:2:"HT";s:5:"Haiti";s:2:"VA";s:7:"Vatikan";s:2:"HN";s:8:"Honduras";s:2:"HU";s:6:"Ungarn";s:2:"IS";s:6:"Island";s:2:"IN";s:6:"Indien";s:2:"ID";s:10:"Indonesien";s:2:"IR";s:4:"Iran";s:2:"IQ";s:4:"Irak";s:2:"IE";s:6:"Irland";s:2:"IL";s:6:"Israel";s:2:"IT";s:7:"Italien";s:2:"JM";s:7:"Jamaika";s:2:"JP";s:5:"Japan";s:2:"JO";s:9:"Jordanien";s:2:"KZ";s:10:"Kasachstan";s:2:"KE";s:5:"Kenia";s:2:"KI";s:8:"Kiribati";s:2:"KW";s:6:"Kuwait";s:2:"KG";s:9:"Kirgistan";s:2:"LA";s:4:"Laos";s:2:"LV";s:8:"Lettland";s:2:"LB";s:7:"Libanon";s:2:"LS";s:7:"Lesotho";s:2:"LI";s:13:"Liechtenstein";s:2:"LT";s:7:"Litauen";s:2:"LU";s:9:"Luxemburg";s:2:"MO";s:5:"Macau";s:2:"MK";s:10:"Mazedonien";s:2:"MG";s:10:"Madagaskar";s:2:"MW";s:6:"Malawi";s:2:"MY";s:8:"Malaysia";s:2:"MV";s:9:"Malediven";s:2:"ML";s:4:"Mali";s:2:"MT";s:5:"Malta";s:2:"MR";s:11:"Mauretanien";s:2:"MU";s:9:"Mauritius";s:2:"YT";s:7:"Mayotte";s:2:"MX";s:6:"Mexiko";s:2:"FM";s:11:"Mikronesien";s:2:"MD";s:9:"Moldavien";s:2:"MC";s:6:"Monaco";s:2:"MN";s:8:"Mongolei";s:2:"MS";s:10:"Montserrat";s:2:"MA";s:7:"Marokko";s:2:"MZ";s:8:"Mosambik";s:2:"MM";s:7:"Myanmar";s:2:"NA";s:7:"Namibia";s:2:"NR";s:5:"Nauru";s:2:"NP";s:5:"Nepal";s:2:"NL";s:11:"Niederlande";s:2:"NZ";s:10:"Neuseeland";s:2:"NI";s:9:"Nicaragua";s:2:"NE";s:5:"Niger";s:2:"NG";s:7:"Nigeria";s:2:"NU";s:4:"Niue";s:2:"NF";s:14:"Norfolk Inseln";s:2:"KP";s:10:"Nord Korea";s:2:"NO";s:8:"Norwegen";s:2:"OM";s:4:"Oman";s:2:"PK";s:8:"Pakistan";s:2:"PW";s:5:"Palau";s:2:"PA";s:6:"Panama";s:2:"PG";s:16:"Papua Neu Guinea";s:2:"PY";s:8:"Paraguay";s:2:"PE";s:4:"Peru";s:2:"PH";s:11:"Philippinen";s:2:"PL";s:5:"Polen";s:2:"PT";s:8:"Portugal";s:2:"PR";s:11:"Puerto Rico";s:2:"RO";s:9:"Rumänien";s:2:"RU";s:8:"Russland";s:2:"RW";s:6:"Ruanda";s:2:"WS";s:5:"Samoa";s:2:"SM";s:10:"San Marino";s:2:"SA";s:13:"Saudi-Arabien";s:2:"SN";s:7:"Senegal";s:2:"SC";s:10:"Seychellen";s:2:"SL";s:12:"Sierra Leone";s:2:"SG";s:8:"Singapur";s:2:"SK";s:8:"Slovakei";s:2:"SB";s:14:"Solomon Inseln";s:2:"SO";s:7:"Somalia";s:2:"ZA";s:10:"Südafrika";s:2:"KR";s:9:"Südkorea";s:2:"ES";s:7:"Spanien";s:2:"LK";s:9:"Sri Lanka";s:2:"SD";s:5:"Sudan";s:2:"SR";s:8:"Suriname";s:2:"SZ";s:9:"Swasiland";s:2:"SE";s:8:"Schweden";s:2:"CH";s:7:"Schweiz";s:2:"SY";s:6:"Syrien";s:2:"TW";s:6:"Taiwan";s:2:"TJ";s:13:"Tadschikistan";s:2:"TZ";s:8:"Tansania";s:2:"TH";s:8:"Thailand";s:2:"TG";s:4:"Togo";s:2:"TO";s:5:"Tonga";s:2:"TT";s:19:"Trinidad und Tobago";s:2:"TN";s:8:"Tunesien";s:2:"TR";s:7:"Türkei";s:2:"TM";s:12:"Turkmenistan";s:2:"TV";s:6:"Tuvalu";s:2:"UG";s:6:"Uganda";s:2:"UA";s:7:"Ukraine";s:2:"AE";s:28:"Vereinigte Arabische Emirate";s:2:"GB";s:23:"Vereinigtes Königreich";s:2:"US";s:30:"Vereinigte Staaten von Amerika";s:2:"UY";s:7:"Uruguay";s:2:"UZ";s:10:"Usbekistan";s:2:"VU";s:7:"Vanuatu";s:2:"VE";s:9:"Venezuela";s:2:"VN";s:7:"Vietnam";s:2:"VG";s:14:"Virgin Islands";s:2:"EH";s:10:"Westsahara";s:2:"YE";s:5:"Jemen";s:2:"YU";s:11:"Jugoslavien";s:2:"ZR";s:5:"Zaire";s:2:"ZM";s:6:"Sambia";s:2:"ZW";s:8:"Simbabwe";}}');
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

	private static function is_bot( $user_agent ) {
		$user_agent = strtolower( $user_agent );
		$identifiers = array(
			'bot', 'slurp', 'crawler', 'spider', 'curl', 'facebook', 'lua-resty', 'fetch', 'python', 'scrubby',
			'wget', 'monitor', 'mediapartners', 'baidu', 'linux',
		);
		// nur Mainstream Browser (Chromium, Firefix) mitzählen > ver 106
		$browser = get_browser(null, true);
		$broversion = (int) $browser['version'];
		if ($broversion < 106) return true;
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
		else if(preg_match('/bot|crawl|slurp|spider|lua-resty|mediapartners/i',$u_agent)) {
			$bname = 'other Bot/Spider';
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
			case 'Windows 10' : $xicon = 'Image/win8-10.png'; break;
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
			<p><code>[ipflag ip="123.20.30.0" iso="mx" details=1 browser=1]</code>
				liefert eine Flagge und das Land zu einer IP odr einem IP-Netz. Die letzte IP-Ziffer wird wegen DSGVO anonymisiert<br>
				iso="xx" liefert die Flagge zum ISO-Land oder die EU-Flagge für private und unbekannte Netzwerke<br>
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
                <h2>QRCodes generieren</h2>
			<div class="postbox">
				<p><code>[qrcode text="tel:00492307299607" size=3 margin=3]</code>
				erstellt QR-Codes als Shortcode an der Cursorposition (Doku siehe Readme)</p>                    
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
