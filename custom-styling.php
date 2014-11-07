<?php
/*
Plugin Name: Custom Styling (for developers)
Plugin URI:
Description: This plugin simply holds custom CSS that's independent from everything else so that you're able to update Wordpress, themes, and plugins (including this one) without having your changes overwritten.
Version: 1.0.2
Author: brandon@itsak.com
Author URI: http://www.itscanfixthat.com
*/

namespace ITSAlaska\plugins\customStyling;
// Seriously developers; use namespaces. http://www.php.net/manual/en/language.namespaces.rationale.php

require_once 'defines.php';

// Do some magic stuff to avoid having to call wp-load.php directly (even though this method is slow...)

add_action( 'init', __NAMESPACE__.'\hook_init');
add_action( 'parse_request', __NAMESPACE__.'\hook_redir');

function hook_init(){
	add_rewrite_endpoint(SLUG, EP_ROOT);
}

function hook_redir( $request ){
	if(!isset($request->query_vars[SLUG])) return;
	
	$lang = rtrim(array_pop(explode('.',$request->query_vars[SLUG])),'/');
	$opts = get_option(SLUG);
	
	switch($lang){
		case 'js':
			$mime= 'javascript';
		break;
	
		default:
			$mime = $lang;
		break;
	}
	
	header('Content-type: text/'.$mime);
	header('Etag: '.md5_file($_SERVER['SCRIPT_FILENAME']));

	if($opts['cache_'.$lang]){
		header('Cache-Control: max-age=1209600, public');
		header('Expires: '.gmdate('D, d M Y H:i:s', time() + 1209600).' GMT');
		header('Pragma: cache');
	}
	
	if($opts['minimize_'.$lang])	echo $opts[$lang.'_code_min'];
	else							echo $opts[$lang.'_code'];
	
	die;
}

// Enqueue the stylesheets
add_action( 'wp_enqueue_scripts', __NAMESPACE__.'\stylesheets', 999 );

function stylesheets(){
	$opts = get_option(SLUG);
	if(!$opts['enable_css'])	return;
	
	if(!empty($opts['css_cdn']))	$site_url = $opts['css_cdn_url'];
	else							$site_url = site_url();
	
	wp_register_style(SLUG.'_css', $site_url.'/'.SLUG.'/'.$opts['css_timestamp'].'.css/');
	wp_enqueue_style(SLUG.'_css');
}

// Then enqueue the scripts
add_action( 'wp_enqueue_scripts', __NAMESPACE__.'\scripts', 999 );

function scripts(){
	$opts = get_option(SLUG);
	if(!$opts['enable_js'])	return;
	
	if(!empty($opts['js_cdn']))	$site_url = $opts['js_cdn_url'];
	else						$site_url = site_url();
	
	wp_register_script(SLUG.'_js', $site_url.'/'.SLUG.'/'.$opts['js_timestamp'].'.js/');
	wp_enqueue_script(SLUG.'_js');
}

// And finally, the admin pages
add_action('admin_menu',__NAMESPACE__.'\add_to_admin_menu');

function add_to_admin_menu(){
    add_menu_page( NAME, NAME, 'edit_theme_options', SLUG, __NAMESPACE__.'\page', plugins_url( basename(__DIR__).'/logo.gif' ) ); 
	add_submenu_page( SLUG, 'Custom JS/JQ', 'Custom JS/JQ', 'edit_theme_options', SLUG.'_js', __NAMESPACE__.'\page' );
	add_submenu_page( SLUG, 'Settings', 'Settings', 'edit_theme_options', SLUG.'_settings', __NAMESPACE__.'\page' );
}

function page(){
	wp_backend_header();
	
	$page = trim(str_replace(SLUG,'',$_GET['page']),'_');
	
	if($page == '')	$page = __NAMESPACE__.'\css_page';
	else			$page = __NAMESPACE__.'\\'.$page.'_page';
	
	$page();
	wp_backend_footer();
}

function css_page(){
	$opts = get_option(SLUG);

	if(count($_POST) && $opts['enable_css']){
		$post = stripslashes_deep($_POST);
		
		if($opts['css_code'] != $post[SLUG.'_css']){
			echo 'Your CSS was sucessfully updated. ';
			$opts['css_timestamp'] = date('U');
			$opts['css_code'] = $post[SLUG.'_css'];
			
			if($opts['minimize_css']){
				require_once('css-min.php');
				
				$result = \CssMin::minify($post[SLUG.'_css']);
				
				if($opts['css_code_min'] != $result){
					echo 'Your CSS has sucessfully been minimized. ';
					$opts['css_code_min'] = $result;
				}
			}
		}
		
		update_option(SLUG,$opts);
	}

	echo '<h1>Custom CSS</h1>
	<label for="'.SLUG.'-css">Enter your custom CSS into the text field below:</label>
	<form id="'.SLUG.'_form" method="post" class="css_form">
		<textarea'.(!$opts['enable_css'] ? ' disabled="disabled"' : '').' id="'.SLUG.'-css" name="'.SLUG.'_css">'.$opts['css_code'].'</textarea><br />
		<input class="button-primary" type="submit" value="Publish" />
	</form>';
	
	if (isset($post["ajax"])) { die(); }
}
add_action('wp_ajax_css_page',__NAMESPACE__.'\css_page');

function js_page(){
	$opts = get_option(SLUG);

	if(count($_POST) && $opts['enable_js']){
		$post = stripslashes_deep($_POST);
	
		if($opts['js_code'] != $post[SLUG.'_js']){
			echo 'Your JS/JQ was sucessfully updated. ';
			$opts['js_timestamp'] = date('U');
			$opts['js_code'] = $post[SLUG.'_js'];

			if($opts['minimize_js']){
				require_once('js-min.php');
				$opts['js_code_min'] = \JShrink\Minifier::minify($opts['js_code'], array('flaggedComments' => false));
				$opts['minimize_js'] = true;
			}
		}
	
		update_option(SLUG,$opts);
	}

	echo '<h1>Custom JS/JQ</h1>
	<label for="'.SLUG.'-js">Enter your custom JS or JQ into the text field below:</label>
	<br /><br />
	If using JQ, remember to use <a href="http://codex.wordpress.org/Function_Reference/wp_enqueue_script#jQuery_noConflict_Wrappers" target="_blank">jQuery noConflict wrappers</a>.
	<form id="'.SLUG.'_form" method="post" class="js_form">
		<textarea'.(!$opts['enable_js'] ? ' disabled="disabled"' : '').' id="'.SLUG.'-js" name="'.SLUG.'_js">'.$opts['js_code'].'</textarea><br />
		<input class="button-primary" type="submit" value="Publish" />
	</form>';
	
	if (isset($post["ajax"])) { die(); }
}
add_action('wp_ajax_js_page',__NAMESPACE__.'\js_page');

function settings_page(){
	$opts = get_option(SLUG);

	if(count($_POST)){
		$enable_css		= isset($_POST['enable_css']);
		$enable_js		= isset($_POST['enable_js']);
		$minimize_css	= isset($_POST['minimize_css']);
		$minimize_js	= isset($_POST['minimize_js']);
		$cache_css		= isset($_POST['cache_css']);
		$cache_js		= isset($_POST['cache_js']);
		$css_cdn		= isset($_POST['css_cdn']);
		$js_cdn			= isset($_POST['js_cdn']);
		$css_cdn_url	= rtrim($_POST['css_cdn_url'], '/');
		$js_cdn_url		= rtrim($_POST['js_cdn_url'], '/');
	
		if($opts['enable_css'] != $enable_css){
			$opts['enable_css'] = $enable_css;
			echo 'CSS has been '.($enable_css ? 'en' : 'dis').'abled. ';
		}
		
		if($opts['enable_js'] != $enable_js){
			$opts['enable_js'] = $enable_js;
			echo 'JS/JQ has been '.($enable_js ? 'en' : 'dis').'abled. ';
		}
		
		if($opts['minimize_css'] != $minimize_css){
			if(!$minimize_css){
				$opts['css_code_min'] = '';
				$opts['minimize_css'] = false;
			}else{
				require_once('css-min.php');
				$opts['css_code_min'] = \CssMin::minify($opts['css_code']);
				$opts['minimize_css'] = true;
			}
			
			echo 'CSS is no'.($minimize_css ? 'w' : ' longer').' set to be minimized. ';
		}
		
		if($opts['minimize_js'] != $minimize_js){
			if(!$minimize_js){
				$opts['js_code_min'] = '';
				$opts['minimize_js'] = false;
			}else{
				require_once('js-min.php');
				$opts['js_code_min'] = \JShrink\Minifier::minify($opts['js_code'], array('flaggedComments' => false));
				$opts['minimize_js'] = true;
			}
			
			echo 'JS is no'.($minimize_js ? 'w' : ' longer').' set to be minimized. ';
		}
		
		if($opts['cache_css'] != $cache_css){
			$opts['cache_css'] = $cache_css;
			echo 'CSS browser caching has been '.($css_cdn ? 'en' : 'dis').'abled. ';
		}
		
		if($opts['cache_js'] != $cache_js){
			$opts['cache_js'] = $cache_js;
			echo 'JS/JQ browser caching has been '.($css_cdn ? 'en' : 'dis').'abled. ';
		}
		
		if($opts['css_cdn'] != $css_cdn){
			$opts['css_cdn'] = $css_cdn;
			echo 'CSS alternate domain has been '.($css_cdn ? 'en' : 'dis').'abled. ';
		}
		
		if($opts['js_cdn'] != $js_cdn){
			$opts['js_cdn'] = $js_cdn;
			echo 'JS/JQ alternate domain has been '.($js_cdn ? 'en' : 'dis').'abled. ';
		}
		
		if($opts['css_cdn_url'] != $css_cdn_url){
			$opts['css_cdn_url'] = $css_cdn_url;
			echo 'Your CSS alternate domain has been updated. ';
		}
		
		if($opts['js_cdn_url'] != $js_cdn_url){
			$opts['js_cdn_url'] = $js_cdn_url;
			echo 'Your JS/JQ alternate domain has been updated. ';
		}
		
		update_option(SLUG,$opts);
	}

	echo '<h1 style="margin-bottom: 1em;">Settings</h1>
	<form id="'.SLUG.'_settings" method="post">
		<span class="subhead">Enable or Disable CSS/JS/JQ. When disabled your, code will remain saved but will not be edittable or loaded by any of your pages.</span><br />
		<label for="enable_css">Enable CSS?</label><input id="enable_css" name="enable_css" type="checkbox"'.($opts['enable_css'] ? ' checked="checked"' : '').' /><br />
		<label for="enable_js">Enable JS/JQ?</label><input id="enable_js" name="enable_js" type="checkbox"'.($opts['enable_js'] ? ' checked="checked"' : '').' /><br />
		<span class="subhead">If checked, this plugin will save a second, smaller, minified version of your code that is loaded by your web pages. This is useful if you have a lot of code.</span><br />
		<label for="minimize_css">Minimize CSS?</label><input id="minimize_css" name="minimize_css" type="checkbox"'.($opts['minimize_css'] ? ' checked="checked"' : '').' /><br />
		<label for="minimize_js">Minimize JS/JQ?</label><input id="minimize_js" name="minimize_js" type="checkbox"'.($opts['minimize_js'] ? ' checked="checked"' : '').' /><br />
		<span class="subhead">This will enable browser caching for your CSS/JS/JQ, which tells the browser to use its cache instead of asking for this file again. Because of a technique called versioning, your code will always be reloaded when modified.</span><br />
		<label for="cache_css" style="width: 135px;">Browser Cache CSS?</label><input id="cache_css" name="cache_css" type="checkbox"'.($opts['cache_css'] ? ' checked="checked"' : '').' /><br />
		<label for="cache_js" style="width: 135px;">Browser Cache JS/JQ?</label><input id="cache_js" name="cache_js" type="checkbox"'.($opts['cache_js'] ? ' checked="checked"' : '').' /><br />
		<span class="subhead">This is for specifying a CDN or <a href="http://www.ravelrumba.com/blog/static-cookieless-domain/" target="_blank">cookieless domain</a>, and doesn\'t conflict with the <a href="http://wordpress.org/plugins/ossdl-cdn-off-linker/" target="_blank">CDN Linker</a> plugin. If the domain points to another website directory or server, you\'ll need move the dummy files and modify them to fit your needs.</span><br />
		<label for="css_cdn" style="width: 185px;">Enable CSS Alternate Domain?</label><input id="css_cdn" name="css_cdn" type="checkbox"'.($opts['css_cdn'] ? ' checked="checked"' : '').' /><br />
		<label for="css_cdn_url" style="width: 185px;">CSS Alternate Domain:</label><input id="css_cdn_url" name="css_cdn_url" placeholder="http://" type="text"'.(!empty($opts['css_cdn_url']) ? ' value="'.$opts['css_cdn_url'].'"' : '').' />
		<br /><br />
		<label for="js_cdn" style="width: 185px;">Enable JS/JQ Alternate Domain?</label><input id="js_cdn" name="js_cdn" type="checkbox"'.($opts['js_cdn'] ? ' checked="checked"' : '').' /><br />
		<label for="js_cdn_url" style="width: 185px;">JS/JQ Alternate Domain:</label><input id="js_cdn_url" name="js_cdn_url" placeholder="http://" type="text"'.(!empty($opts['js_cdn_url']) ? ' value="'.$opts['js_cdn_url'].'"' : '').' />
		<br /><br />
		<input class="button-primary" type="submit" value="Update" />
	</form>';
}

function wp_backend_header(){
	// Thank Stack Overflow for the tab indent bit. http://stackoverflow.com/questions/6637341/use-tab-to-indent-in-textarea

	echo '<style>
		#wpbody-content > *:not(#custom_styling){
			display: none;
		}
	
		#'.SLUG.'{
			padding: 30px;
		}
		
		#'.SLUG.' ul > li{
			list-style: disc;
			margin-left: 30px;
		}
		
		#'.SLUG.'_form input[type="submit"]{
			margin: 20px 0 0 530px;
			width: 70px;
		}
		
		#'.SLUG.'_form textarea{
			font: 12px monospace;
			height: 400px;
			width: 600px;
		}
		
		#'.SLUG.'_form textarea[disabled]{
			background: #BBBBBB;
		}
		
		#'.SLUG.'_settings input[type="submit"]{
			margin-top: 5px;
		}
		
		#'.SLUG.'_settings input[type="text"]{
			width: 200px;
		}
		
		#'.SLUG.'_settings label{
			display: inline-block;
			margin-left: 20px;
			width: 100px;
		}
		
		#'.SLUG.'_settings > *{
			margin-bottom: 5px;
		}
		
		#'.SLUG.'_settings > .subhead{
			border: 1px solid #DDD;
			border-width: 1px 0;
			display: inline-block;
			font-size: 14px;
			margin-bottom: 8px;
			padding: 2px 0;
		}
		
		#'.SLUG.'_settings > .subhead:not(:first-child){
			margin-top: 25px;
		}
	</style>
	<script>
		jQuery(document).delegate(\'#'.SLUG.'_form > textarea:first-child\', \'keydown\', function(e) {
			var keyCode = e.keyCode || e.which;

			if (keyCode == 9) {
				e.preventDefault();
				var start = jQuery(this).get(0).selectionStart;
				var end = jQuery(this).get(0).selectionEnd;

				// set textarea value to: text before caret + tab + text after caret
				jQuery(this).val(jQuery(this).val().substring(0, start) + "\t" + jQuery(this).val().substring(end));

				// put caret at right position again
				jQuery(this).get(0).selectionStart = jQuery(this).get(0).selectionEnd = start + 1;
			}
		});
		
		jQuery(document).on("submit", ".css_form, .js_form", function() {
			var form = jQuery(this);
			var type = form.hasClass("css_form") ? "css" : "js";
			var data = {
				action: type + "_page",
				ajax: true
			}
			
			data["'.SLUG.'_" + type] = jQuery("#'.SLUG.'-" + type).val()
			jQuery.post(ajaxurl, data, function(response) {
				clearTimeout(window.savedTimout);
				jQuery(".saved").remove()
				jQuery("<p class=\"saved\" style=\"position: absolute; \">Saved.</p>").hide().insertBefore(form.find("input:last")).fadeIn();
				window.savedTimout = setTimeout(function(){ 
					jQuery(".saved").remove();
				}, 1500);
			});
			return false;
		});
	</script>
	<div id="'.SLUG.'">';
}

function wp_backend_footer(){
    echo '</div>';
}

// Also, an instillation handler
register_activation_hook(FILE,__NAMESPACE__.'\install');

function install(){
	if(!current_user_can('activate_plugins'))	return;
	
	add_option(SLUG,array(
		'css_cdn'		=> false,
		'css_cdn_url'	=> '',
		'css_code'		=> '',
		'css_code_min'	=> '',
		'css_timestamp'	=> date('U'),
		'js_cdn'		=> false,
		'js_cdn_url'	=> '',
		'js_code'		=> '',
		'js_code_min'	=> '',
		'js_timestamp'	=> date('U'),
		'cache_css'		=> true,
		'cache_js'		=> true,
		'enable_js'		=> true,
		'enable_css'	=> true,
		'minimize_js'	=> true,
		'minimize_css'	=> true
	),' ','no');
	
	add_rewrite_endpoint(SLUG, EP_ROOT);
	flush_rewrite_rules(false);
}

// And an uninstall handler
register_uninstall_hook(FILE,__NAMESPACE__.'\uninstall');

function uninstall(){
	if(!current_user_can('activate_plugins'))	return;
    check_admin_referer('bulk-plugins');
	
	delete_option(SLUG);
}