=== Charts QR-Barcodes ===
Tags: pie chart, chart, graph, polar chart, doughnut chart, bar graph, horizontal bar graph, absolute, percent, Barcode, QRCode, IPFlag 
Requires at least: 4.5
Tested up to: 5.4.2
Stable tag: 11.1.12
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

== Description ==
This Plugin provides you an elegent Bar Graph and Pie Charts with multiple designs and colors. ie. Default Pie Chart, Doughnut Pie Chart and Polar Pie Chart.
It also adds the functionality to output barcodes and qrcodes by use of the shortcodes.
Flags and Country name and code can be shown by shortcode [ipflag] - visitor info browser and details optional
Color palette for charts can be accentcolor with shares or random (colorful light colors) or given values

== Installation ==
= Using The WordPress Dashboard =
* Navigate to the 'Add New' in the plugins dashboard
* Search for TP PieBuilder
* Click Install Now
* Activate the plugin on the Plugin dashboard
= Uploading in WordPress Dashboard =
* Navigate to the 'Add New' in the plugins dashboard
* Navigate to the 'Upload' area
* Select chartscodes.zip from your computer
* Click 'Install Now'
* Activate the plugin in the Plugin dashboard


== Shortcodes for pies and bnars and last post barchart ==
 = Defaults Atts = 
	* title = '', // Optional
	* absolute = '' // optional, if set to "1" given values must be absolute, percents calculated automatically, if not set they must be percent values 
	* values = '', // * in percentage (%) ( should be seperated by comma (','). ie: 60, 40 )
	* labels = '', // * ( should be seperated by comma (','). ie: Design, Development )
	* colors = '' // Optional till 10 elements else * ( should be seperated by ','. ie: #E6E6FA, #E0FFFF )
	* accentcolor = false     values 0 and 1 can be given by shortcode   to make colorful palette or accent color shades

 = Alt Atts for Pie Charts only = 
	* fontfamily = 'arial', // Optional, you can change the defult font family
	* fontstyle = 'italic', // Optional, you can change the defult font style to normal or bold

 = Default Piechart Shortcode = 
 	[chartscodes absolute="1" accentcolor=1 title="Pie Chart" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]

 = Donut Piechart Shortcode = 
	[chartscodes_donut title="Donut Pie Chart" absolute="1" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]
	
 = Polar Piechart Shortcode = 
 	[chartscodes_polar title="Polar Chart mit Segmenten" absolute="1" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]

 = Bar Graph Shortcode = 
 	[chartscodes_bar title="Balkenchart" absolute="1" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]

 = Horizontal Bar Graph Shortcode = 
 	[chartscodes_horizontal_bar title="Balken horizontal" absolute="1" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]

 = Bar chart of number of wordpress posts per month for last 1-12 months =
	[posts_per_month_last months=x]
	
==================================  Barcodes and CRCodes Usage =============================================
= How to use &#91;barcode&#93; =

In order to output barcodes, &#91;barcode&#93; will be used. There are some attributes for &#91;barcode&#93;.

text ... A text that should be in the image barcode. 
type ... The barcode type: code39, int25, ean13, upca, upce, code128, ean8, and postnet. Default: ean13
imgtype ... The image type that will be generated: gif, jpg, and png. Default: png
height ... The image height (px). Default: 60
width ... The image width. Default: 1
showText ... The text should be placed under barcode. Default: 1
rotation ... The rotation angle. Default: 0
transparency ... The transparent background. Default: 0
remake ... The barcode will be regenerated every time. Default: 0
textfilename ... The text value will be the file name. Default: 0

Example: &#91;barcode text=4930127000019 height=100 wdith=2 transparency=1&#93;

= How to use &#91;qrcode&#93; =
In order to output qrcodes, &#91;qrcode&#93; will be used. There are some attributes for &#91;qrcode&#93;.

text ... A text that should be in the image qrcode. 
eclevel ... Error correction level. 0(Level L), 1(Level M), 2(Level Q), and 3(Level H) Default: 3 
height ... The image height (px). Default: 60
width ... The image width (px). Default: 60
transparency ... The transparent background. Default: 0
remake ... The barcode will be regenerated every time. Default: 0
textfilename ... The text value will be the file name. Default: 0

Example: &#91;qrcode text=4930127000019 height=100 width=100 transparency=1&#93;

===================================== IPFflag ===========================================================

Resolves IP address to ISO 3166-1 alpha-2 two-letter country code and name and displays country flag image if required.

== Description ==
IPFflag resolves IP address to [ISO 3166-1 alpha-2](http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2) two-letter country code and name using [ip-countryside](http://code.google.com/p/ip-countryside/) generated database and displays country flag if required. In contrast to other IP to country solutions this one allows you to create and update database your self using 5 Regional Internet Registrars (RIR) databases: AFRINIC, APNIC, ARIN, LACNIC and RIPE and ip-countryside open source application that does the work of generating database for you. You can also update IP to country database automatically with single click or schedule weekly automatic updates.

<strong>Big thanks to [Markus Goldstein](http://madm.dfki.de/goldstein/) for ip-countryside project, weekly database updates and automatic update server used in the past. To offload Markus server, database updates are currently generated by me and stored inside [GitHub repository](https://github.com/Marko-M/ip-countryside-db).</strong>

<h4>IPFflag feature highlights</h4>

*   IPFflag database can be updated using single click without updating IPFflag plugin.
*   IPFflag database can be auto updated weekly without updating IPFflag plugin.
*   Because of the way IPFflag database is created it has probably the most accurate IP to country database you can find.
*   Database updates are generated using open source [ip-countryside](http://code.google.com/p/ip-countryside/) application.
*   IPFflag provides PHP function to retrieve country code and country name for given IP address (see FAQ for more)
*   IPFflag provides PHP function to retrieve country flag image for given country, 248 flag images provided by [Mark James](http://www.famfamfam.com)

== Frequently Asked Questions ==
= How do I test is IPFflag installed properly? =
You can place `[ipflag]` shortcode to add current IP address country name and flag image to your page or post. To display country name and flag image of IP address other than current you can use this shortcode like  `[ipflag ip="some_ip_address"]`. For more details to display there are shortcode parameters: details=1 and browser=1, both defaulting to 0

= Can you provide example for fetching country info and flag for imaginary `123.123.123.123` IP address from country Croatia? =
You can use something like this:

`
// Query database with following IP address
$ip_address = '123.123.123.123';

global $ipflag;
if(isset($ipflag) && is_object($ipflag)){
    if(($info = $ipflag->get_info($ip_address)) != false){
        $version = $info->version;      // IPFflag version (float): 2.00
        $ip = $info->ip;                // IP address (string): 122.122.122.0
        $code = $info->code;            // Country code (string): LL
        $name = $info->name;            // Country name (string): Lummerland
        $latitude = $info->latitude;    // Country latitude (float): 45.1667
        $longitude = $info->longitude;  // Country longitude (float): 15.5
        $flag = $ipflag->get_flag($info, 'my-own-css-class'); // CSS class is optional, 'ipflag' by default
    }
}
`


== Changelog ==
= 11.1.14 =
[webcounter admin=1] Shortcode, schreibt browser, useragent, ip (shortened), land, referer und Datum in eine sitevisitors Tabelle
mit admin=1 wird administrativen Usern die Statistik gezeigt

= 11.1.13 =
add visitor, browser and os information. For GDPR (DSGVO) the ip-address is masked with the last number set to 0

= 11.1.7 =
Random color generation of bright tones
Responsiveness of canvas pies, suitable to selected resolution

= 11.1.11 =
Metrics on pie charts corrected for responsiveness and to remove blurry views

= 11.1.6 =
Integrated IPflag function class and Shortcode, added to documentation

= 11.1.3 =
Merged shortcodes for qrcode and barcodes to the project, added documentation

= 11.0.8 =
values can be given absolute, when absolute param set. CSS changes to make it responsiver and modern, color defaults for 13 values preset

= 0.7 =
* Tested in WordPress 5.2

= 0.6 =
* Added link to pro version
* Updated setting page

= 0.5 =
* Added font styling in pie charts

= 0.4 =
* Tested in WordPress 4.9.4

= 0.3 =
* Tested in WordPress 4.8.1
* Added Horizontal Bar Graph

= 0.2 =
* Tested in WordPress 4.8

= 0.1 =
* Initial release.
