<?php
/**
 * Charts QRCodes Barcodes Setting Page
 *
 * @package Charts QRCodes Barcodes
 * @since 0.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class PB_ChartsCodes_Setting_Page
{

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'PB_ChartsCodes_add_plugin_page' ) );
    }

    /**
     * Add options page
     */
    public function PB_ChartsCodes_add_plugin_page()
    {
        // This page will be under "Settings"
        add_options_page(
            esc_html__( 'Settings Admin', 'pb-chartscodes' ), 
            esc_html__( 'Charts QR-Barcodes', 'pb-chartscodes' ),
            'manage_options', 
            'pb-chartscodes-admin', 
            array( $this, 'PB_ChartsCodes_create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function PB_ChartsCodes_create_admin_page()
    {
    ?>

        <div class="wrap">
            <div class="img-wrap">
                <h2>Barcodes und QRCodes generieren</h2>
				<p><tt>erstellt Barcodes oder QR-Codes als Shortcode an der Cursorposition (Doku siehe Readme)<br>
                    <code>[qrcode text=tel:00492307299607 height=100 width=100]</code><br>
					<code>[barcode text=4930127000019 height=100 wdith=2 transparency=1]</code>
                </tt></p>                    
            </div>

			<div class="img-wrap">
				<h2><?php esc_html_e( 'Bar and Piecharts', 'pb-chartscodes' ); ?></h2>
				<p><tt>Shortcode Parameter: absolute="1" wenn keine Prozentwerte mitgegeben werden, sondern absolute Werte<br>
					fontfamily="Armata" fontstyle="bold". Für die PieCharts dürfen maximal 10 Werte angegeben werden, bei den Bar Charts bis zu 50<br>
					Bar Charts: bei absoluten Werten wird größter Wert in der Folge 100%, Werte werden angezeigt wenn >0<br> 
					</tt></p>
				<img src="<?php echo PB_ChartsCodes_URL_PATH . 'assets/screenshot-1.png' ?>"  alt="<?php esc_attr_e( 'Default Pie Chart', 'pb-chartscodes' ); ?>">
                <p><tt>
                    <code>[chartscodes absolute="1" title="Pie Chart" values="20, 30, 50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]</code>
                </tt></p>                    
            </div>

            <br>
            <div class="img-wrap">
                <img src="<?php echo PB_ChartsCodes_URL_PATH . 'assets/screenshot-2.png' ?>"  alt="<?php esc_attr_e( 'Doughnut Pie Chart', 'pb-chartscodes' ); ?>">
                <p><tt>
                    <code>[chartscodes_donut title="Donut Pie Chart" absolute="1" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]</code>                </tt></p>
			</div>
            <br>
            <div class="img-wrap">
                <img src="<?php echo PB_ChartsCodes_URL_PATH . 'assets/screenshot-3.png' ?>"  alt="<?php esc_attr_e( 'Polar Pie Chart', 'pb-chartscodes' ); ?>">
                <p><tt>
                    <code>[chartscodes_polar title="Polar Chart mit Segmenten" absolute="1" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]</code>
                </tt></p>                    
            </div>
            <br>
            <div class="img-wrap">
                <img src="<?php echo PB_ChartsCodes_URL_PATH . 'assets/screenshot-4.png' ?>"  alt="<?php esc_attr_e( 'Bar Graph Chart', 'pb-chartscodes' ); ?>">
                <p>
                    <code>[chartscodes_bar title="Balkenchart" absolute="1" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]</code>
                </tt></p>                    
            </div>

            <br>
            <div class="img-wrap">
                <img src="<?php echo PB_ChartsCodes_URL_PATH . 'assets/screenshot-5.png' ?>"  alt="<?php esc_attr_e( 'Horizontal Bar Graph Chart', 'pb-chartscodes' ); ?>">
                <p><tt>
                    <code>[chartscodes_horizontal_bar title="Balken horizontal" absolute="1" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]</code>
                </tt></p>                    
            </div>
			<br>

			<div class="img-wrap">
                <img src="<?php echo PB_ChartsCodes_URL_PATH . 'assets/screenshot-6.png' ?>"  alt="<?php esc_attr_e( 'Horizontal Bar Graph Chart', 'pb-chartscodes' ); ?>">
				<p><tt> Zeigt die letzen 1-12 Monate Posts per Month als Bargraph an, wenn Months nicht angegeben für 12 Monate</tt><br>
                  <code>[posts_per_month_last months=x]</code></p>                    
            </div>
        </div>
    <?php
    }

}

if( is_admin() )
    new PB_ChartsCodes_Setting_Page();