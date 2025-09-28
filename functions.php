<?php
// ---------------------------------------------
// Vite + WordPress enqueue helper
// - Dev: loads @vite/client and your entry as ES modules from the Vite server
// - Prod: reads dist/.vite/manifest.json or dist/manifest.json and enqueues built assets
// ---------------------------------------------

// env reader - getenv -> WP constant -> default
if (!function_exists('vite_env')) {
	function vite_env($key, $default = null)
	{
		$v = getenv($key);
		if ($v !== false && $v !== null && $v !== '') return $v;
		if (defined($key)) return constant($key);
		return $default;
	}
}

// detect current site host - stable for LocalWP and admin context
if (!function_exists('vite_detect_host')) {
	function vite_detect_host()
	{
		$host = parse_url(home_url(), PHP_URL_HOST);
		if ($host) return $host;
		if (!empty($_SERVER['HTTP_HOST'])) {
			$parts = explode(':', $_SERVER['HTTP_HOST']);
			return $parts[0];
		}
		return 'localhost';
	}
}

// compute dev server origin parts
if (!function_exists('vite_server_origin')) {
	function vite_server_origin()
	{
		// scheme - default https for LocalWP to avoid mixed content
		$scheme = vite_env('VITE_SCHEME', 'https');
		// host - env override, otherwise site host
		$host   = vite_env('VITE_HOST', vite_detect_host());
		// if VITE_DOMAIN was used earlier, honor it too
		if (!$host) $host = vite_env('VITE_DOMAIN', 'localhost');
		// port
		$port   = (int) vite_env('VITE_PORT', 5173);
		return [$scheme, $host, $port];
	}
}

// tiny TCP probe for dev detection
if (!function_exists('is_vite_running')) {
	function is_vite_running($host, $port, $timeout = 0.25)
	{
		// try the configured host first - keeps HMR host consistent
		$fp = @fsockopen($host, (int)$port, $errno, $errstr, $timeout);
		if ($fp) {
			fclose($fp);
			return true;
		}
		// fallback to loopback in case DNS for custom host is not resolvable from PHP
		$fp2 = @fsockopen('127.0.0.1', (int)$port, $errno, $errstr, $timeout);
		if ($fp2) {
			fclose($fp2);
			return true;
		}
		return false;
	}
}

// manifest reader with simple runtime cache
if (!function_exists('vite_get_manifest')) {
	function vite_get_manifest($manifest_path)
	{
		static $cache = [];
		if (isset($cache[$manifest_path])) return $cache[$manifest_path];
		if (!file_exists($manifest_path)) return $cache[$manifest_path] = [];
		$json = file_get_contents($manifest_path);
		$data = json_decode($json, true);
		return $cache[$manifest_path] = (is_array($data) ? $data : []);
	}
}

// fetch an entry from manifest
if (!function_exists('vite_manifest_entry')) {
	function vite_manifest_entry($key, $manifest_path)
	{
		$manifest = vite_get_manifest($manifest_path);
		return $manifest[$key] ?? null;
	}
}

// add type="module" to specific handles
add_filter('script_loader_tag', function ($tag, $handle, $src) {
	$module_handles = ['vite-client', 'app'];
	if (in_array($handle, $module_handles, true)) {
		return sprintf(
			'<script type="module" crossorigin="anonymous" src="%s" id="%s"></script>' . "\n",
			esc_url($src),
			esc_attr($handle)
		);
	}
	return $tag;
}, 10, 3);

// enqueue assets for front
add_action('wp_enqueue_scripts', function () {
	// entry keys must match your source files that appear in the Vite manifest
	$css_entry = defined('VITE_CSS_ENTRY') ? VITE_CSS_ENTRY : 'scss/main.scss';
	$js_entry  = defined('VITE_JS_ENTRY')  ? VITE_JS_ENTRY  : 'js/init.js';

	[$scheme, $host, $port] = vite_server_origin();

	// detect dev by probing the configured host, then loopback as fallback
	$dev = is_vite_running($host, (int)$port);

	$theme_dir = get_stylesheet_directory();
	$theme_uri = get_stylesheet_directory_uri();

	// prefer dist/.vite/manifest.json - fallback to dist/manifest.json
	$manifest_path = file_exists($theme_dir . '/dist/.vite/manifest.json')
		? $theme_dir . '/dist/.vite/manifest.json'
		: $theme_dir . '/dist/manifest.json';

	if ($dev) {
		// serve from Vite in development
		$origin = sprintf('%s://%s:%s', $scheme, $host, $port);

		// resource hints
		add_filter('wp_resource_hints', function ($urls, $relation_type) use ($host) {
			if ($relation_type === 'dns-prefetch') $urls[] = '//' . $host;
			return $urls;
		}, 10, 2);

		// optional preconnect for faster HMR handshake
		add_action('wp_head', function () use ($scheme, $host, $port) {
			printf(
				'<link rel="preconnect" href="%1$s://%2$s:%3$s" crossorigin />' . "\n",
				esc_attr($scheme),
				esc_attr($host),
				esc_attr($port)
			);
		}, 1);

		// vite HMR client and your entry as ES modules
		wp_enqueue_script('vite-client', $origin . '/@vite/client', [], null, false);
		wp_enqueue_script('app', $origin . '/' . ltrim($js_entry, '/'), [], null, true);

		// note - CSS is injected by Vite in dev
		return;
	}

	// production - read manifest and enqueue generated files
	if (!file_exists($manifest_path)) {
		error_log('[Vite] manifest not found at: ' . $manifest_path);
		return;
	}

	// CSS entry
	$cssEntry = vite_manifest_entry($css_entry, $manifest_path);
	if ($cssEntry && !empty($cssEntry['file'])) {
		wp_enqueue_style('main', $theme_uri . '/dist/' . $cssEntry['file'], [], null);
	} elseif (file_exists($theme_dir . '/dist/css/main.css')) {
		// optional fallback if you output a stable css file
		wp_enqueue_style('main', $theme_uri . '/dist/css/main.css', [], null);
	}

	// JS entry
	$jsEntry = vite_manifest_entry($js_entry, $manifest_path);
	if ($jsEntry && !empty($jsEntry['file'])) {
		wp_enqueue_script('app', $theme_uri . '/dist/' . $jsEntry['file'], [], null, true);

		// if JS emitted CSS chunks - enqueue them too
		if (!empty($jsEntry['css']) && is_array($jsEntry['css'])) {
			foreach ($jsEntry['css'] as $i => $cssRel) {
				wp_enqueue_style('app-css-' . $i, $theme_uri . '/dist/' . $cssRel, [], null);
			}
		}

		// optionally preload dynamic imports
		if (!empty($jsEntry['imports']) && is_array($jsEntry['imports'])) {
			foreach ($jsEntry['imports'] as $imp) {
				$impEntry = vite_manifest_entry($imp, $manifest_path);
				if ($impEntry && !empty($impEntry['file'])) {
					add_action('wp_head', function () use ($theme_uri, $impEntry) {
						printf(
							'<link rel="modulepreload" href="%s" />' . "\n",
							esc_url($theme_uri . '/dist/' . $impEntry['file'])
						);
					});
					if (!empty($impEntry['css']) && is_array($impEntry['css'])) {
						foreach ($impEntry['css'] as $j => $cssRel) {
							wp_enqueue_style('app-css-imp-' . $j, $theme_uri . '/dist/' . $cssRel, [], null);
						}
					}
				}
			}
		}
	} else {
		error_log('[Vite] JS entry not found in manifest for key: ' . $js_entry);
	}
}, 20);







// function enqueue_custom_scripts() {

//     wp_enqueue_style( 'style', get_template_directory_uri() . '/dist/css/main.css' );

//     wp_enqueue_script( 'lib', get_template_directory_uri() . '/js/lib.js', '', '1.0', true );    
//     wp_enqueue_script( 'app', get_template_directory_uri() . '/dist/js/app.js', '', '1.0', true );

// }

// add_action( 'wp_enqueue_scripts', 'enqueue_custom_scripts' );



/* TG WP Title */
function tg_wp_title($title, $seperator)
{
	global $paged, $page;

	if (is_feed()) {
		return $title;
	}
	$title .= ' ' . $seperator . ' ' . get_bloginfo('name');
	$description = get_bloginfo('description', 'display');
	if ($description && (is_front_page())) {
		$title = "$title $seperator $description";
	}
	if ($paged >= 2 || $page >= 2) {
		$title = "$title $seperator " . sprintf(__('Page %s'), max($paged, $page));
	}

	return trim($title, ' ' . $seperator . ' ');
}
add_filter('wp_title', 'tg_wp_title', 10, 2);
/* End of TG WP Title */

/* Body Class */
function tg_body_class($class)
{
	return $class;
}


//show_admin_bar(false);
add_filter('body_class', 'tg_body_class');
/* End of Body Class */

/* Register JS Scripts & CSS Styles */
function paradise_place_scripts()
{
	global $post;

	wp_localize_script('ajax-script', 'ajax_object', [
		'ajaxurl' => admin_url('admin-ajax.php')
	]);
}
add_action('wp_enqueue_scripts', 'paradise_place_scripts');

/*
function remove_wp_block_library_css(){
    wp_dequeue_style( 'wp-block-library' ); // Remove WordPress core CSS
    wp_dequeue_style( 'wp-block-library-theme' ); // Remove WordPress theme core CSS
    wp_dequeue_style( 'classic-theme-styles' ); // Remove global styles inline CSS
    wp_dequeue_style( 'wc-block-style' ); // Remove WooCommerce block CSS
    wp_dequeue_style( 'global-styles' ); // Remove theme.json css
}
add_action( 'wp_enqueue_scripts', 'remove_wp_block_library_css', 100 );
*/
/* End of Register/Deregister JS Scripts & CSS Styles */

/* Remove Script Type Attribute */

add_action(
	'after_setup_theme',
	function () {
		add_theme_support('html5', ['script', 'style']);
	}
);

/* End of Remove Script Type Attribute */


/* Register Nav Areas */
//register_nav_menus( 
//	array( 
//		'primary'  	=>	__( 'Menu Top' ), 
//		'secondary'	=>	__( 'Menu Sidebar' )
//	 )
// ); 
/* End of Register Nav Areas */



/* Add Theme Support */
add_theme_support('post-thumbnails');
add_theme_support('automatic-feed-links');
/* End of Add Theme Support */

/* Add Theme Image Sizes */
//function custom_image_sizes() {
//    add_image_size('article-img', 600, 300, false); 
//}
//add_action('after_setup_theme', 'custom_image_sizes');
//
//function add_custom_image_size_to_dropdown($sizes) {
//    return array_merge($sizes, array(
//        'article-img' => __('Article Image', 'iwd'),
//    ));
//}
//add_filter('image_size_names_choose', 'add_custom_image_size_to_dropdown');

/* End of Add Theme Image Sizes */

/* Register Sidebars */
/*
if ( function_exists( 'register_sidebar' ) ) {
	
	register_sidebar( array( 
		'name'=> 'Front Page Widget Section', 
		'id' => 'front-page', 
		'before_widget' => '', 
		'after_widget' => '', 
		'before_title' => '<h2>', 
		'after_title' => '</h2>', 
	) );	
	 
}
*/
/* End of Register Sidebars */

/* Set Custom Excerpt Length */
function custom_excerpt_length($length)
{
	return 45;
}

add_filter('excerpt_length', 'custom_excerpt_length', 999);
/* End of Set Custom Excerpt Length */

/* New Excerpt More */
function new_excerpt_more($more)
{
	return ' ... ';
}

add_filter('excerpt_more', 'new_excerpt_more');
/* End of New Excerpt More */


/* Allow SVG through WordPress Media Uploader */

function allow_svg_upload($mime_types)
{
	$mime_types['svg'] = 'image/svg+xml';
	return $mime_types;
}
add_filter('upload_mimes', 'allow_svg_upload');

function wpa_fix_svg_thumb()
{
	echo '<style>td.media-icon img[src$=".svg"], img[src$=".svg"].attachment-post-thumbnail {width: 100% !important;height: auto !important}</style>';
}
add_action('admin_head', 'wpa_fix_svg_thumb');
