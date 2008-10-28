#!/usr/bin/php5
<?php

//--- Just for fun ---//

function just_for_fun() {

$moo = <<<EOT
	 (__)  
	 (oo) 
   /------\/ 
  / |    ||   
 *  /\---/\ 
    ~~   ~~   
EOT;

	$cv = caca_create_canvas(0, 0);
	caca_set_color_ansi($cv, CACA_LIGHTBLUE, CACA_DEFAULT);
	caca_import_string($cv, $moo, "text");

	for($j = 0; $j < caca_get_canvas_height($cv); $j++) {
		for($i = 0; $i < caca_get_canvas_width($cv); $i += 2) {
			caca_set_color_ansi($cv, (caca_rand(1, 10) > 5 ? CACA_LIGHTBLUE  : CACA_WHITE), CACA_DEFAULT);
			$a = caca_get_attr($cv, -1, -1);
			caca_put_attr($cv, $i, $j, $a);
			caca_put_attr($cv, $i + 1, $j, $a);
		}
	}
	caca_set_color_ansi($cv, CACA_LIGHTGREEN, CACA_DEFAULT);
	caca_put_str($cv, 8, 0, "Moo!");
	echo caca_export_string($cv, "utf8");
}
	

just_for_fun();

//--- Show caca's information ---//

echo "libcaca version: ".caca_get_version()."\n\n";
echo "Available drivers:\n";
$list = caca_get_display_driver_list();
foreach($list as $type => $name)
	echo "* $name ($type)\n";
echo "\n";

echo "Available import formats:\n";
$list = caca_get_import_list();
foreach($list as $format => $name)
	echo "* $name ($format)\n";
echo "\n";

echo "Available export formats:\n";
$list = caca_get_export_list();
foreach($list as $format => $name)
	echo "* $name ($format)\n";
echo "\n";

echo "Available caca fonts:\n";
$list = caca_get_font_list();
foreach($list as $name)
	echo "* $name\n";
echo "\n";
