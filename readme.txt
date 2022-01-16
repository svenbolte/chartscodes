=== Charts QR-Barcodes ===
Tags: post-timeline, line chart, pie chart, chart, graph, polar chart, doughnut chart, bar graph, horizontal bar graph, absolute, percent, QRCode, IPFlag, webcounter,useragent 
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Version: 11.1.53
Stable tag: 11.1.53
Requires at least: 5.1
Tested up to: 5.8.3
Requires PHP: 8.0

== Description ==
Shortcode collection for creating Line Charts, Bar Graph and Pie Charts (normal, donut, polar) with multiple colors.
[carlogo] displays maufactorer logo for car brands
[qrcode] creates qrcodes, [ipflag] shows country name of visitor (IP shortened for GDPR) - visitor info browser and details optional
[webcounter] shortcode to gather and display stats about visitors (ip shortened for GRPR compliance)
[wp-timeline] shortcodes shows posts in a timeline, paged, filters are category slug list, post type list
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

 = Bar Graph Shortcode = 
 	[chartscodes_bar title="Balkenchart" absolute="1" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]

 = Horizontal Bar Graph Shortcode = 
 	[chartscodes_horizontal_bar title="Balken horizontal" absolute="1" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]

 = Bar chart of number of wordpress posts per month for last 1-12 months =
	[posts_per_month_last months=x]

	
==================================  QRCodes Shortcode Usage ==========================================================

Barcode QRCode library taken from: https://github.com/kreativekorp/barcode
In order to output barcodes, [barcode] will be used. Attributes:
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
				liefert eine Flagge und das Land zu einer IP odr einem IP-Netz. Die letzte IP-Ziffer wird wegen DSGVO anonymisiert
				iso="xx" liefert die Flagge zum ISO-Land oder die EU-Flagge für private und unbekannte Netzwerke
				browser=1 liefert Betriebssystem und Browser des Besuchers, details=1 liefert den Referrer, das IP-Netz
				
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
		      'brand' => '0unknown',  // Autohersteller
to display logo and link to german webpage or car manufacturer

=========================================== WordPress Posts Timeline ===============================================

Output your WordPress posts or custom post types as a timeline with options.

== Shortcode usage abnd defaults ==
[wp-timeline]

	'catname' => '',     		// insert slugs of all post types you want, sep by comma, empty for all types
	'type' => 'post,wpdoodle',  // separate type slugs by comma
	'items' => 1000,    	 	// Maximal 1000 Posts paginiert anzeigen
	'perpage' => 20,     		// posts per page for pagination
	'view' => 'timeline',     // set to "calendar" for calender display, to "calendar,timeline" for both 
	'pics' => 1,        		// 1 or 0 - Show images (Category-Image, Post-Thumb or first image in post)
	'dateformat' => 'D d.m.Y H:i',

=====================================================================================================================

== Changelog ==

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
