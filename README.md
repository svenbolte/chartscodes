# Chartcodes
Liniendiagramme, Balken und Kuchendiagramme, QRCodes, Länderflaggen und Besucherstatistik - alles als Shortcode und lokal gehostet, IP-Adressen gekürzt.

## Warum dieses Plugin?
Ursprungsplugins sind entweder tot oder veraltet. Funktionen sind aber nützlich,
daher dieses Projekt, dass die Einzelfunktionen zusammen fasst und um viele neue Funktionen erweitert wurde - siehe readme.txt

## Shortcodes für die Funktionen

[qrcode type="code-39" text="Hallo Welt" ]

[qrcode type="ean-13" text="9780201379624" ]

[qrcode text="tel:+49304030568956834058340" ]

[qrcode text="https://test.com" size=2 margin=5]

[girocode iban=DE43370000000038001501 bic=MARKDEF1370 rec="Maxine Mustermann" cur=EUR sum=9.99 subj="Rechnung 123456789 Konto 123434" comm="Kommentar zur Ueberweisung"]

[ipflag ip="10.10.10.0" iso="UA" name="Ukraine" details=1 browser=1]

[webcounter admin=0]

[carlogo brand="mercedes" scale="sm/xs"]

[complogo brand="lenovo" scale="sm/xs"]

[bulawappen land="Bremen/HB" scale="sm/xs"]

## gd_chart Shortcode

== Usage ==

Line chart:
```
[gd_chart type="line" title="Visits" data="Jan:120|Feb:180|Mar:150"]
```

vertical bar:
```
[gd_chart type="vbar" title="Visits" data="Jan:120|Feb:180|Mar:150"]
```

horizontal bar:
```
[gd_chart type="hbar" title="Visits" data="Jan:120|Feb:180|Mar:150"]
```

Polar (radar):
```
[gd_chart type="polar" title="Skills" data="Jan:120|Feb:180|Mar:150" table=1 table_pos=only]
```

Pie:
```
[gd_chart type="pie" title="Browsers" data="Chrome:62|Safari:20|Firefox:10|Edge:8"]
```

Table:
add : table=1 parameter and table_pos=only   oder table_pos=above (zusätzlich). Wenn nur table=1 wird sie unter dem Bild gezeigt.

== Shortcode Attributes ==
- `type` (string): `line|pie|vbar|hbar|polar` (default `line`)
- `data` (string): `Label:Value|Label2:Value2`  
- `title` (string): Chart title (optional)
- `legend` (`true|false`): show legend (default `true`)
- `width` (int): render width in px (default `640`)
- `height` (int): render height in px (default `360`)
- `bg`, `fg`, `grid` (hex): colors like `#ffffff`
- `colors` (csv hex): series colors, e.g. `#1e88e5,#e53935,#43a047`
- `max` (float): axis max (ignored for pie/polar; for 100% stacked also ignored)
- `dpi` (1..3): render scaling for HiDPI (server-side); combine with responsive display
- `responsive` (`true|false`): outputs `<img>` without width/height and with `srcset` (default `true`)
- `class`, `style`, `alt`: passed to `<img>`
