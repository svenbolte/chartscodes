# Chartcodes
Liniendiagramme, Balken und Kuchendiagramme, QRCodes, Länderflaggen und Besucherstatistik - alles als Shortcode und lokal gehostet, IP-Adressen gekürzt.

## Warum dieses Plugin?
Ursprungsplugins sind entweder tot oder veraltet. Funktionen sind aber nützlich,
daher dieses Projekt, dass die Einzelfunktionen zusammen fasst und um viele neue Funktionen erweitert wurde - siehe readme.txt

## Shortcodes für die Funktionen

[qrcode text="https://test.com" size=2 margin=5]

[ipflag ip="10.10.10.0" details=1 browser=1]

[webcounter admin=0]

[carlogo brand="mercedes" scale="sm"]

[complogo brand="lenovo"]

[chartscodes absolute="1" title="Pie Chart" values="20, 30, 50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]

[chartscodes_donut title="Donut Pie Chart" absolute="1" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]

[chartscodes_polar title="Polar Chart mit Segmenten" absolute="1" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]

[chartscodes_radar title="Radar Chart" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi"]

[chartscodes_bar title="Balkenchart" absolute="1" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]

[chartscodes_horizontal_bar title="Balken horizontal" absolute="1" values="20,30,50,60,70" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi" colors="#003030,#006060,#009090,#00aaaa,#00cccc"]

[chartscodes_line accentcolor=1 title="Obst Line Chart" xaxis="Obstsorte" yaxis="Umsatz" values="10,20,10,5,30,20,5" labels="Bananen,Ananas,Kirschen,Birnen,Kiwi,Cranberry,Mango"]

[posts_per_month_last months=x]

[wp-timeline items=100 view="calendar" type="post" catname="software"]
