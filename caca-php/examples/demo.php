#!/usr/bin/php5
<?

function main() {
	$cv = caca_create_canvas(0, 0);
	if (!$cv) {
		die("Error while creating canvas\n");
	}

	$dp = caca_create_display($cv);
	if (!$dp) {
		die("Error while attaching canvas to display\n");
	}


	caca_set_display_time($dp, 40000);

	/* Disable cursor */
	caca_set_mouse($dp, 0);

	/* Main menu */
	display_menu($cv, $dp, $bounds, $outline);
	caca_refresh_display($dp);


	/* Go ! */
	$bounds = $outline = $dithering = 0;

	$ev = caca_create_event();
	while(!$quit) {
		$menu = $mouse = $xmouse = $ymouse = 0;

		while (caca_get_event($dp, CACA_EVENT_ANY, $ev, 0)) {
			if ($demo and (caca_get_event_type($ev) & CACA_EVENT_KEY_PRESS)) {
				$menu = 1;
				$demo = false;
			}
			else if (caca_get_event_type($ev) & CACA_EVENT_KEY_PRESS) {
				switch (caca_get_event_key_ch($ev)) {
					case ord('q'):
					case ord('Q'):
					case CACA_KEY_ESCAPE:
						$demo = false;
						$quit = 1;
						break;
					case ord('o'):
					case ord('O'):
						$outline = ($outline + 1) % 3;
						display_menu($cv, $dp, $bounds, $outline);
						break;
					case ord('b'):
					case ord('B'):
						$bounds = ($bounds + 1) % 2;
						display_menu($cv, $dp, $bounds, $outline);
						break;
					case ord('d'):
					case ord('D'):
						$dithering = ($dithering + 1) % 5;
						caca_set_feature($cv, $dithering);
						display_menu($cv, $dp, $bounds, $outline);
						break;
					case ord('f'):
					case ord('F'):
						$demo = "demo_all";
						break;
					case ord('1'):
						$demo = "demo_dots";
						break;
					case ord('2'):
						$demo = "demo_lines";
						break;
					case ord('3'):
						$demo = "demo_boxes";
						break;
					case ord('4'):
						$demo = "demo_triangles";
						break;
					case ord('5'):
						$demo = "demo_ellipses";
						break;
					case ord('s'):
					case ord('S'):
						$demo = "demo_sprites";
						break;
					case ord('r'):
					case ord('R'):
						$demo = "demo_render";
						break;
				}

				if ($demo) {
					caca_set_color_ansi($cv, CACA_LIGHTGRAY, CACA_BLACK);
					caca_clear_canvas($cv);
				}
			}
			else if(caca_get_event_type($ev) & CACA_EVENT_MOUSE_MOTION) {
				$mouse = 1;
				$xmouse = caca_get_event_mouse_x($ev);
				$ymouse = caca_get_event_mouse_y($ev);
			}
			else if(caca_get_event_type($ev) & CACA_EVENT_RESIZE) {
				$mouse = 1; /* old hack */
			}
		}

		if ($menu || ($mouse && !$demo)) {
			display_menu($cv, $dp, $bounds, $outline);
			if ($mouse && !$demo) {
				caca_set_color_ansi($cv, CACA_RED, CACA_BLACK);
				caca_put_str($cv, $xmouse, $ymouse,".");
				caca_put_str($cv, $xmouse, $ymouse + 1, "|\\");
			}
			caca_refresh_display($dp);
			$mouse = $menu = 0;
		}

		if ($demo) {
			if (function_exists($demo))
				$demo($cv, $bounds, $outline, $dithering);

			caca_set_color_ansi($cv, CACA_LIGHTGRAY, CACA_BLACK);
			caca_draw_thin_box($cv, 1, 1, caca_get_canvas_width($cv) - 2, caca_get_canvas_height($cv) - 2);
			$rate = sprintf("%01.2f", 1000000 / caca_get_display_time($dp));
			caca_put_str($cv, 4, 1, "[$rate fps]----");
			caca_refresh_display($dp);
		}
	}
}

function demo_all($cv, $dp, $bounds, $outline) {
	global $i;

	$i++;

	caca_set_color_ansi($cv, CACA_LIGHTGRAY, CACA_BLACK);
	caca_clear_canvas($cv);

	/* Draw the sun */
	caca_set_color_ansi($cv, CACA_YELLOW, CACA_BLACK);
	$xo = caca_get_canvas_width($cv) / 4;
	$yo = caca_get_canvas_height($cv) / 4 + 5 * sin(0.03 * $i);

	for ($j = 0; $j < 16; $j++) {
		$xa = $xo - (30 + sin(0.03 * $i) * 8) * sin(0.03 * $i + M_PI * $j / 8);
		$ya = $yo + (15 + sin(0.03 * $i) * 4) * cos(0.03 * $i + M_PI * $j / 8);
		caca_draw_thin_line($cv, $xo, $yo, $xa, $ya);
	}

	$j = 15 + sin(0.03 * $i) * 8;
	caca_set_color_ansi($cv, CACA_WHITE, CACA_BLACK);
	caca_fill_ellipse($cv, $xo, $yo, $j, $j / 2, '#');
	caca_set_color_ansi($cv, CACA_YELLOW, CACA_BLACK);
	caca_draw_ellipse($cv, $xo, $yo, $j, $j / 2, '#');

	/* Draw the pyramid */
	$xo = caca_get_canvas_width($cv) * 5 / 8;
	$yo = 2;

	$xa = caca_get_canvas_width($cv) / 8 + sin(0.03 * $i) * 5;
	$ya = caca_get_canvas_height($cv) / 2 + cos(0.03 * $i) * 5;

	$xb = caca_get_canvas_width($cv) - 10 - cos(0.02 * $i) * 10;
	$yb = caca_get_canvas_height($cv) * 3 / 4 - 5 + sin(0.02 * $i) * 5;

	$xc = caca_get_canvas_width($cv) / 4 - sin(0.02 * $i) * 5;
	$yc = caca_get_canvas_height($cv) * 3 / 4 + cos(0.02 * $i) * 5;

	caca_set_color_ansi($cv, CACA_GREEN, CACA_BLACK);
	caca_fill_triangle($cv, $xo, $yo, $xb, $yb, $xa, $ya, '%');
	caca_set_color_ansi($cv, CACA_YELLOW, CACA_BLACK);
	caca_draw_thin_triangle($cv, $xo, $yo, $xb, $yb, $xa, $ya);

	caca_set_color_ansi($cv, CACA_RED, CACA_BLACK);
	caca_fill_triangle($cv, $xa, $ya, $xb, $yb, $xc, $yc, '#');
	caca_set_color_ansi($cv, CACA_YELLOW, CACA_BLACK);
	caca_draw_thin_triangle($cv, $xa, $ya, $xb, $yb, $xc, $yc);

	caca_set_color_ansi($cv, CACA_BLUE, CACA_BLACK);
	caca_fill_triangle($cv, $xo, $yo, $xb, $yb, $xc, $yc, '%');
	caca_set_color_ansi($cv, CACA_YELLOW, CACA_BLACK);
	caca_draw_thin_triangle($cv, $xo, $yo, $xb, $yb, $xc, $yc);

	/* Draw a background triangle */
	$xa = 2;
	$ya = 2;

	$xb = caca_get_canvas_width($cv) - 3;
	$yb = caca_get_canvas_height($cv) / 2;

	$xc = caca_get_canvas_width($cv) / 3;
	$yc = caca_get_canvas_height($cv) - 3;

	caca_set_color_ansi($cv, CACA_CYAN, CACA_BLACK);
	caca_draw_thin_triangle($cv, $xa, $ya, $xb, $yb, $xc, $yc);

	$xo = caca_get_canvas_width($cv) / 2 + cos(0.027 * $i) * caca_get_canvas_width($cv) / 3;
	$yo = caca_get_canvas_height($cv) / 2 - sin(0.027 * $i) * caca_get_canvas_height($cv) / 2;

	caca_draw_thin_line($cv, $xa, $ya, $xo, $yo);
	caca_draw_thin_line($cv, $xb, $yb, $xo, $yo);
	caca_draw_thin_line($cv, $xc, $yc, $xo, $yo);

	/* Draw a trail behind the foreground sprite */
	for($j = $i - 60; $j < $i; $j++) {
		$delta = caca_rand(-5, 6);
		caca_set_color_ansi($cv, caca_rand(0, 16), caca_rand(0, 16));
		caca_put_char($cv, 
			caca_get_canvas_width($cv) / 2 + cos(0.02 * $j) * ($delta + caca_get_canvas_width($cv) / 4),
			caca_get_canvas_height($cv) / 2 + sin(0.02 * $j) * ($delta + caca_get_canvas_height($cv) / 3),
			'#');
	}
}

function display_menu($cv, $dp, $bounds, $outline) {
	$xo = caca_get_canvas_width($cv) - 2;
	$yo = caca_get_canvas_height($cv) - 2;

	caca_set_color_ansi($cv, CACA_LIGHTGRAY, CACA_BLACK);
	caca_clear_canvas($cv);
	caca_draw_thin_box($cv, 1, 1, $xo, $yo);

	caca_put_str($cv, ($xo - strlen("libcaca demo")) / 2, 3, "libcaca demo");
	caca_put_str($cv, ($xo - strlen("==============")) / 2, 4, "==============");

	caca_put_str($cv, 4, 6, "demos:");
	caca_put_str($cv, 4, 7, "'f': full");
	caca_put_str($cv, 4, 8, "'1': dots");
	caca_put_str($cv, 4, 9, "'2': lines");
	caca_put_str($cv, 4, 10, "'3': boxes");
	caca_put_str($cv, 4, 11, "'4': triangles");
	caca_put_str($cv, 4, 12, "'5': ellipses");
	caca_put_str($cv, 4, 13, "'c': colour");
	caca_put_str($cv, 4, 14, "'r': render");

	if ($sprite)
		caca_put_str($cv, 4, 15, "'s': sprites");

	caca_put_str($cv, 4, 16, "settings:");
	caca_put_str($cv, 4, 17, "'o': outline: ".(($outline == 0) ? "none" : (($outline == 1) ? "solid" : "thin")));
	caca_put_str($cv, 4, 18, "'b': drawing boundaries: ".(($bounds == 0) ? "screen" : "infinite"));
	caca_put_str($cv, 4, $yo - 2, "'q': quit");

	caca_refresh_display($dp);
}

function demo_dots($cv, $bounds, $outline, $dithering) {
	$xmax = caca_get_canvas_width($cv);
	$ymax = caca_get_canvas_height($cv);

	$chars = array('+', '-', '*', '#', 'X', '@', '%', '$', 'M', 'W');

	for ($i = 1000; $i--;) {
		/* Putpixel */
		caca_set_color_ansi($cv, caca_rand(0, 16), caca_rand(0, 16));
		caca_put_char($cv, caca_rand(0, $xmax), caca_rand(0, $ymax), $chars[caca_rand(0, 9)]);
	}
}

function demo_lines($cv, $bounds, $outline, $dithering) {
	$w = caca_get_canvas_width($cv);
	$h = caca_get_canvas_height($cv);

	if ($bounds) {
		$xa = caca_rand(- $w, 2 * $w); $ya = caca_rand(- $h, 2 * $h);
		$xb = caca_rand(- $w, 2 * $w); $yb = caca_rand(- $h, 2 * $h);
	}
	else {
		$xa = caca_rand(0, $w); $ya = caca_rand(0, $h);
		$xb = caca_rand(0, $w); $yb = caca_rand(0, $h);
	}

	caca_set_color_ansi($cv, caca_rand(0, 16), CACA_BLACK);
	if ($outline > 1)
		caca_draw_thin_line($cv, $xa, $ya, $xb, $yb);
	else
		caca_draw_line($cv, $xa, $ya, $xb, $yb, '#');
}

function demo_boxes($cv, $bounds, $outline, $dithering) {
	$w = caca_get_canvas_width($cv);
	$h = caca_get_canvas_height($cv);

	if ($bounds) {
		$xa = caca_rand(- $w, 2 * $w); $ya = caca_rand(- $h, 2 * $h);
		$xb = caca_rand(- $w, 2 * $w); $yb = caca_rand(- $h, 2 * $h);
	}
	else {
		$xa = caca_rand(0, $w); $ya = caca_rand(0, $h);
		$xb = caca_rand(0, $w); $yb = caca_rand(0, $h);
	}

	caca_set_color_ansi($cv, caca_rand(0, 16), caca_rand(0, 16));
	caca_fill_box($cv, $xa, $ya, $xb, $yb, '#');

	caca_set_color_ansi($cv, caca_rand(0, 16), CACA_BLACK);
	if($outline == 2)
		caca_draw_thin_box($cv, $xa, $ya, $xb, $yb);
	else if($outline == 1)
		caca_draw_box($cv, $xa, $ya, $xb, $yb, '#');
}

function demo_ellipses($cv, $bounds, $outline, $dithering) {
	$w = caca_get_canvas_width($cv);
	$h = caca_get_canvas_height($cv);

	if ($bounds) {
		$x = caca_rand(- $w, 2 * $w); $y = caca_rand(- $h, 2 * $h);
		$a = caca_rand(0, $w); $b = caca_rand(0, $h);
	}
	else {
		do {
			$x = caca_rand(0, $w); $y = caca_rand(0, $h);
			$a = caca_rand(0, $w); $b = caca_rand(0, $h);
		} while ($x - $a < 0 || $x + $a >= $w || $y - $b < 0 || $y + $b >= $h);
	}

	caca_set_color_ansi($cv, caca_rand(0, 16), caca_rand(0, 16));
	caca_fill_ellipse($cv, $x, $y, $a, $b, '#');

	caca_set_color_ansi($cv, caca_rand(0, 16), CACA_BLACK);
	if ($outline == 2)
		caca_draw_thin_ellipse($cv, $x, $y, $a, $b);
	else if ($outline == 1)
		caca_draw_ellipse($cv, $x, $y, $a, $b, '#');
}

function demo_triangles($cv, $bounds, $outline, $dithering) {
	$w = caca_get_canvas_width($cv);
	$h = caca_get_canvas_height($cv);

	if ($bounds) {
		$xa = caca_rand(- $w, 2 * $w); $ya = caca_rand(- $h, 2 * $h);
		$xb = caca_rand(- $w, 2 * $w); $yb = caca_rand(- $h, 2 * $h);
		$xc = caca_rand(- $w, 2 * $w); $yc = caca_rand(- $h, 2 * $h);
	}
	else {
		$xa = caca_rand(0, $w); $ya = caca_rand(0, $h);
		$xb = caca_rand(0, $w); $yb = caca_rand(0, $h);
		$xc = caca_rand(0, $w); $yc = caca_rand(0, $h);
	}

	caca_set_color_ansi($cv, caca_rand(0, 16), caca_rand(0, 16));
	caca_fill_triangle($cv, $xa, $ya, $xb, $yb, $xc, $yc, '#');

	caca_set_color_ansi($cv, caca_rand(0, 16), CACA_BLACK);
	if ($outline == 2)
		caca_draw_thin_triangle($cv, $xa, $ya, $xb, $yb, $xc, $yc);
	else if ($outline == 1)
		caca_draw_triangle($cv, $xa, $ya, $xb, $yb, $xc, $yc, '#');
}



main();
