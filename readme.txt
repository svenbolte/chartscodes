=== Charts QR-Barcodes ===
Tags: post-timeline, line chart, pie chart, chart, graph, polar chart, doughnut chart, bar graph, horizontal bar graph, absolute, percent, QRCode, IPFlag, webcounter,useragent 
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html
Version: 11.1.112
Stable tag: 11.1.112
Requires at least: 6.0
Tested up to: 6.7
Requires PHP: 8.2

== Description ==
Webcounter, shortcodes for QRCodes, IP2Flag, bar, line and Pie, Donut Pie, Polar Pie, Radar, Horizontal Bar, monthly post archive as bar chart, use as html widget too

[carlogo] displays maufacturer logo for car brands
[complogo] displays maufactorer logo for computer brands
[qrcode] creates qrcodes, [ipflag] shows country name of visitor (IP shortened for GDPR) - visitor info browser and details optional
[webcounter] shortcode to gather and admin-display stats about visitors (ip shortened for GRPR compliance)

Color palette for charts can be accentcolor with shares or random (colorful light colors) or given values

== Installation ==
= Uploading in WordPress Dashboard =
* Navigate to the 'Add New' in the plugins dashboard
* Navigate to the 'Upload' area
* Select chartscodes.zip from your computer
* Click 'Install Now'
* Activate the plugin in the Plugin dashboard

== Shortcodes for pies and bars and last post barchart ==
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

 = Default Linechart Shortcode = 
 	[chartscodes_line accentcolor=1 title="Obst Line Chart" xaxis="Obstsorte" yaxis="Umsatz" values="10,20,10,5,30,20,5" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi,Cranberry,Mango"]

 = Default Piechart Shortcode = 
 	[chartscodes absolute="1" accentcolor=1 title="Pie Chart" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]

 = Donut Piechart Shortcode = 
	[chartscodes_donut title="Donut Pie Chart" absolute="1" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]
	
 = Polar Piechart Shortcode = 
 	[chartscodes_polar title="Polar Chart mit Segmenten" absolute="1" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]

 = Radar-Chart Shortcode = 
 	[chartscodes_radar title="Radar Chart" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi"]

 = Bar Graph Shortcode = 
 	[chartscodes_bar title="Balkenchart" absolute="1" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]

 = Horizontal Bar Graph Shortcode = 
 	[chartscodes_horizontal_bar title="Balken horizontal" absolute="1" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]

 = Bar chart of number of wordpress posts per month for last 1-12 months =
	[posts_per_month_last months=x]

	
==================================  QRCodes Shortcode Usage ==========================================================

Shortcode for creating qrcode and barcode images locally hosted. no external sources used, just php:

[qrcode type="code-39" text="Hallo Welt" ]
[qrcode type="ean-13" text="9780201379624" ]
[qrcode text="tel:+49304030568956834058340" ]

Barcode QRCode library taken from: https://github.com/kreativekorp/barcode
In order to output barcodes, [qrcode] will be used. Attributes:

format ... One of:
    png
    gif
    jpeg
    svg

 text ... A text that should be in the image qrcode. 
 size ... Size of the qrcode (2 for x2)
 margin ... margin in pixel 
 type  ... one of:
    upc-a          code-39         qr     dmtx
    upc-e          code-39-ascii   qr-l   dmtx-s
    ean-8          code-93         qr-m   dmtx-r
    ean-13         code-93-ascii   qr-q   gs1-dmtx
    ean-13-pad     code-128        qr-h   gs1-dmtx-s
    ean-13-nopad   codabar                gs1-dmtx-r
    ean-128        itf

``` Additional options to be set in code 
w - Width of image. Overrides sf or sx.
h - Height of image. Overrides sf or sy.
sf - Scale factor. Default is 1 for linear barcodes or 4 for matrix barcodes.
sx - Horizontal scale factor. Overrides sf.
sy - Vertical scale factor. Overrides sf.
p - Padding. Default is 10 for linear barcodes or 0 for matrix barcodes.
pv - Top and bottom padding. Default is value of p.
ph - Left and right padding. Default is value of p.
pt - Top padding. Default is value of pv.
pl - Left padding. Default is value of ph.
pr - Right padding. Default is value of ph.
pb - Bottom padding. Default is value of pv.
bc - Background color in #RRGGBB format.
cs - Color of spaces in #RRGGBB format.
cm - Color of modules in #RRGGBB format.
tc - Text color in #RRGGBB format. Applies to linear barcodes only.
tf - Text font for SVG output. Default is monospace. Applies to linear barcodes only.
ts - Text size. For SVG output, this is in points and the default is 10. For PNG, GIF, or JPEG output, this is the GD library built-in font number from 1 to 5 and the default is 1. Applies to linear barcodes only.
th - Distance from text baseline to bottom of modules. Default is 10. Applies to linear barcodes only.
ms - Module shape. One of: s for square, r for round, or x for X-shaped. Default is s. Applies to matrix barcodes only.
md - Module density. A number between 0 and 1. Default is 1. Applies to matrix barcodes only.
wq - Width of quiet area units. Default is 1. Use 0 to suppress quiet area.
wm - Width of narrow modules and spaces. Default is 1.
ww - Width of wide modules and spaces. Applies to Code 39, Codabar, and ITF only. Default is 3.
wn - Width of narrow space between characters. Applies to Code 39 and Codabar only. Default is 1.
```

======================== Girocode Shortcode ==================================================================

Erstellen eines EPC QR-Codes für SEPA Überweisungen (Girocode ganannt)
	`[girocode noheader=0 iban="DE1234544455454545"]` ...
		'noheader' => 0, // set to 1 if you want only the QR-Code, nothing else
		'ibangen' => 0,
		'iban' => 'DE43370000000038001501',
		'bic' => 'MARKDEF1370',	
		'rec' => '',	// z.B. Max Mustermann, wenn leer kommt Formular
		'cur' => 'EUR',
		'sum' => 1.99,
		'subj' => 'Rechnung 123456789, Konto 123434',
		'comm' => 'Kommentar zur Ueberweisung',


===================================== IPFflag  Usage =================================================================

Resolves IP address to ISO 3166-1 alpha-2 two-letter country code and name and displays country flag image if required.
IPFflag resolves IP address to [ISO 3166-1 alpha-2](http://en.wikipedia.org/wiki/ISO_3166-1_alpha-2) two-letter country code and name using [ip-countryside](http://code.google.com/p/ip-countryside/) generated database and displays country flag if required. In contrast to other IP to country solutions this one allows you to create and update database your self using 5 Regional Internet Registrars (RIR) databases: AFRINIC, APNIC, ARIN, LACNIC and RIPE and ip-countryside open source application that does the work of generating database for you. You can also update IP to country database automatically with single click or schedule weekly automatic updates.
Big thanks to [Markus Goldstein](http://madm.dfki.de/goldstein/) for ip-countryside project, weekly database updates and automatic update server used in the past. To offload Markus server, database updates are currently generated by me and stored inside [GitHub repository](https://github.com/Marko-M/ip-countryside-db)

IPFflag feature highlights
*   IPFflag database can be updated using single click without updating IPFflag plugin.
*   IPFflag database can be auto updated weekly without updating IPFflag plugin.
*   Because of the way IPFflag database is created it has probably the most accurate IP to country database you can find.
*   Database updates are generated using open source [ip-countryside](http://code.google.com/p/ip-countryside/) application.
*   IPFflag provides PHP function to retrieve country code and country name for given IP address (see FAQ for more)
*   IPFflag provides PHP function to retrieve country flag image for given country, 248 flag images provided by [Mark James](http://www.famfamfam.com)

== IPFlag Shortcode ==
	`[ipflag ip="123.20.30.0" iso="mx" details=1 browser=1]`
			'ip' => null,  // provide an ip like 10.20.30.40
			'iso' => null, // provide ISO code to get country flag
			'name' => null,  // provide country name in english please, you will result a flag in german
			'showland' => 0  // Land und ISO mit anzeigen
			'details' => 0,   // get more details like ip net and referrer
			'browser' => 0,  // show user agent string and browser info
				liefert eine Flagge und das Land zu einer IP odr einem IP-Netz. Die letzte IP-Ziffer wird wegen DSGVO anonymisiert<br>
				iso="xx" liefert die Flagge zum ISO-Land oder die EU-Flagge für private und unbekannte Netzwerke<br>
				showland=1 zeigt ISO und Land hinter der Flagge an
				browser=1 liefert Betriebssystem und Browser des Besuchers, details=1 liefert den Referrer, das IP-Netz<br><br>
				<code>[webcounter admin=0]</code> zählt Seitenzugriffe und füllt Statistikdatenbank, admin=1 zum Auswerten mit Adminrechten<br>
				Ist die Admin /webcounter-Seite aufgerufen, kann über das Eingabefeld oder den optionalen URL-Parameter ?items=x die Ausgabe-Anzahl einiger Listeneinträge verändert werden.
				
	`[webcounter admin=0]` zählt Seitenzugriffe und füllt Statistikdatenbank, admin=1 zum Auswerten mit Adminrechten<br>
				Ist die Admin /webcounter-Seite aufgerufen, kann über das Eingabefeld oder den optionalen URL-Parameter ?items=x die Ausgabe-Anzahl einiger Listeneinträge verändert werden.

== Frequently Asked Questions ==
= How do I test is IPFflag installed properly? =
You can place `[ipflag]` shortcode to add current IP address country name and flag image to your page or post. To display country name and flag image of IP address other than current you can use this shortcode like  `[ipflag ip="some_ip_address"]`. For more details to display there are shortcode parameters: details=1 and browser=1, both defaulting to 0

= Can you provide example for fetching country info and flag for imaginary `123.123.123.123` IP address from country Croatia? =
You can use something like this:

```
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
```
=========================================== Carlogo Shortcode ===============================================
Use Shortcode: [carlogo brand="mercedes" scale="sm"]

		      'scale' => '',     		// sm = 32px  xs=21px
		      'brand' => '0unknown',  // Autohersteller  - all=show all logos with countries and flag
to display logo and link to german webpage or car manufacturer

=========================================== Computerbrand logo Shortcode ===============================================
Use Shortcode: [complogo brand="lenovo" scale="sm"]

		      'scale' => '',     		// sm = 32px  xs=21px
		      'brand' => '0unknown',  // Computerhersteller
to display logo and link to german webpage or computer manufacturer

=====================================================================================================================

== Changelog ==

= 11.1.109 =
fontawesome updates, some icons added. only needed when not penguin-mod theme

= 11.1.107 =
[Carlogos] updated and their origin land iso added to display flag of origin on shortcode use.
Shortcode parameter "brand=all" added to display a panel aof all supported car brands with origin country 

= 11.1.101 =
girocode improved and iban generator für germany added
forms to convert blz und konto to iban
iban and bic prüfung (international)
EPC-Code (Girocode) generieren, mit und ohne Text (noformat=1)

= 11.1.92 =
ipflag shortcode showland=1 added to display land and iso in clear text

= 11.1.91 =
counter logos updated
documentation updated for barcode qrcode library usage
wp62 and php82 compatibility checks

= 11.1.83 =
car logos updated to mfgs 2023 designs (css and optimized sprite PNG)

= 11.1.80 =
PHP 8.1 compatibility checks
Webstats improved with white/yellow newlabel formatting and progress display on referrers
lastxvisitiors function nw callable from outside ipflag class

= 11.1.77 =
Timeline moved to penguin mod theme

= 11.1.76 =
improved timediff btw. articles display
box style for number/counts

= 11.1.72 =
Added product category filter, if woocommerce is found

= 11.1.71 =
improved timeline style and functions. clean up display, acrylic effect

= 11.1.70 =
Webcounter now counts rss feed visits and displays statistics about daily counter ans calls
theme must have webcounter on feeds implemented (like penguin mod does)
	// Webcounter für RSS Feeds zufügen - add to functions.php of your child theme
	add_action('wp', 'rsscounter');
	function rsscounter() {
		if ( class_exists('PB_ChartsCodes') && is_feed() ) { do_shortcode('[webcounter]');	}
	}

= 11.1.68 =
webcounter shortcode - identify and count clicks on home page and list them in statistics
theme (here: penguin mod) index.php must have the following code snippet at the end:

	if (class_exists('PB_ChartsCodes')) {
		if ( is_front_page() && is_home() ) do_shortcode('[webcounter]');
	}
	
Integrate this snippet to other index.php and page-templates and create a webcounter page with the shortcode for use with other themes	

= 11.1.67 =
wp-timeline: Add url parameter, shortcode attr and dropdownlist to filter on tags added
bugfixes on category select box: default value was -1 must be empty ""

= 11.1.57-66 =
Code optimizations, wp593 testing
Radar Chart shortcode added - takes accent color or grey gradient for filling
gets absolute values and aligns them to maximum value

= 11.1.55-56 =
piechart shows absolute values next to percentage
add function lastxvisitors that can be called by theme templates or template parts
added to penguin theme meta-bottom.php

= 11.1.55-61 =
calendar improvements and timeline shows categories if not filtered

= 11.1.54 =
WP590 compatibility
stats record now if page/post was called by logged in user (with display name) or guest

= 11.1.53 =
WP 583 compatibility
Date statistics added in timeline style, created, modified, time diffs and yellow for new

= 11.1.51-52 =
does not require penguin theme any more, made ccago function for ago time
print optimizations for timeline

= 11.1.49-50 =
strftime replaced by i18n since deprecated from php 8.1 
internal statistics improved

= 11.1.48 =
html decode entity for qrcodes containing & in url (it was replaced by &amp; before so Links did not work)

= 11.1.47 =
Piechart/donut and polarchart responsiveness optimizations

= 11.1.46 =
Bar Graph percentage optimizations

= 11.1.45 =
Car logos shortcode added - displays a logo and link of car manufacturer

= 11.1.43-44 =
Replace 250 gif flags by one png. Flags are called as css sprites now and displayed as div. css code from freakflags. Many thanks.
flag css minimized for performance. it will only be loaded (enqueued) when get_flag function is used

= 11.1.41-42 =
Add selectbox to filter on category near pagination links
css optimizations, cat selectbox only shown when no shortcode categories are set

= 11.1.40 =
Styles optimized. timeline styles integrated into main styles file and all minified

= 11.1.39 =
Timeline can display a calendar with linked posts per day now: Shortvode parameter: view="calendar"

= 11.1.38 =
changed barcode and qrcode library for PHP8 compatibility
Shortcode QRCode can display 2D-Barcodes now as well

= 11.1.37 =
german translations and description fixed

= 11.1.36 =
merged timeline shortcode plugin to insert timelines (of posts and cpt) on cursor position
timeline: fixed some bugs, removed options and options page, parameters can be given on shortcode:
timeline: added pagination by 16 posts per page, changed style to show 2 events next to each other

= 11.1.35 =
Fixes div by 0 in charts when only zero values
line chart improvements, responsive and sharpen fit canvas. height parameter to set height of line chart (defaults to 350 px)

= 11.1.34 =
add Website facts to count all posts, post-formats, pages, authors, comments, custom post types: list, doodlez, downloads

= 11.1.33 =
Barcode libraries removed because of eol of library and because it stored multiple images in upload dir
QR-Code library and code removed
New QR-code Library with same shortcode as before added [qrcode text=""]. It generates pictures on the fly and does not store images in upload
Library doese not require external servers and is up to date.

= 11.1.32 =
css optimizing, display of percent values monospaced, shadows replaced by silver borders

= 11.1.31 =
version compatibility WP 5.5.3

= 11.1.30 =
Added Jquery and shortcode for line chart with x and y-axis labels. Code will be only enqueued when shortcode is used.
uses colorset, title and values like the other shortcodes
with grid and value display on bullets in the graph.

= 11.1.27 =
last 12 month post stats x axis year fix
when on archive page, list 12 months before shown archive month

= 11.1.26 =
webcounter: more statistics, diameter values, totals, time recordes yet (days)
minor bugfixes

= 11.1.25 =
Styling with pie charts - font class and sizes
recording time for webcounter entries can be set in options (min. 30 days, max. 999 days)
number of displayed entries can be set in gui / sanitation of inputs added

= 11.1.24 =
speedup: load jquery pie javascript in footer

= 11.1.23 =
get ip function renamed to cc_get_user_ip

= 11.1.22 =
Webcounter: delete entries older 30 days automatically

= 11.1.20 =
posts last months shortcode x-axis values with archive links to the given month
minor bug fixes

= 11.1.17 =
[webcounter] Browser and Operating Systems icons will be displayed on admin page webstats
/webcounter page: use optional url parameter ?items=xx to display xx items in some of the lists
translation updates: german and german formal, documentation and screenshots in settings page updated

= 11.1.16 =
CSS fixes and beautifying horizontal and vertical bar charts

= 11.1.15 =
Sanitizing of some variables for security
Stats and graphs for top pages, countries, browsers, countperday (list for one, pie for more)

= 11.1.14 =
Penguin PBMod Theme integration: auf allen singular posts/pages/custom pages wird webcounter aufgerufen und speichert die Stats, wenn dieses Plugin aktiv ist.
Plugin erstellt eine private page, die nur für admins erreichbar ist /webcounter zum Abruf der Statistik. Alternativ kann auf einer anderen geschützten page der Shortcode angegeben werden
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
