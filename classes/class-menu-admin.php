<?php
class Genesis_Club_Menu_Admin {
    const CODE = 'genesis-club'; //prefix ID of CSS elements
	const SLUG = 'menu';

    private static $parenthook = GENESIS_CLUB_PLUGIN_NAME;
    private static $slug;
    private static $screen_id;
	private static $tooltips;
	private static $tips = array(
		'threshold' => array('heading' => 'Device Threshold', 'tip' => 'Enter the size in pixels at which the full menu is collapsed into the "hamburger" icon or leave blank to disable this feature.'),
		'icon_size' => array('heading' => 'Hamburger Icon Size', 'tip' => 'Size of the menu icon measured in rem, or leave blank to use the default which is 2.4 (1.5 times the size of a small icon).'),
		'icon_color' => array('heading' => 'Hamburger Icon Color', 'tip' => 'Color of the hamburger menu icon (e.g #808080), or leave blank if you want the icon to adopt the same color as the links in the menu.'),
		'primary' => array('heading' => 'Primary Responsive Menu', 'tip' => 'Choose where you want the primary menu to be displayed when the hamburger is clicked.'),
		'secondary' => array('heading' => 'Secondary Responsive Menu', 'tip' => 'Choose where you want the secondary menu to be displayed when the hamburger is clicked.'),
		'header' => array('heading' => 'Header Responsive Menu', 'tip' => 'Choose where you want the header right menu to be displayed when the hamburger is clicked.'),
		);
	
	public static function init() {
		self::$slug = self::$parenthook . '-' . self::SLUG;
		add_action('admin_menu',array(__CLASS__, 'admin_menu'));
	}
	
    private static function get_parenthook(){
		return self::$parenthook;
	}

    public static function get_slug(){
		return self::$slug;
	}
		
    private static function get_screen_id(){
		return self::$screen_id;
	}
	
	public static function enable_screen($show_screen,$screen) {
		if ($screen->id == self::get_screen_id())
			return true;
		else
			return $show_screen;
	}	

	public static function admin_menu() {
		self::$screen_id = add_submenu_page(self::get_parenthook(), __('Genesis Club Hamburger Menus'), __('Menus'), 'manage_options', 
			self::get_slug(), array(__CLASS__,'settings_panel'));
		add_action('load-'.self::get_screen_id(), array(__CLASS__, 'load_page'));
	}
	
	public static function load_page() {
 		$message = isset($_POST['options_update']) ? self::save() : '';	
		self::$tooltips = new DIY_Tooltip(self::$tips);
		Genesis_Club_Options::add_tooltip_support();
		$options = Genesis_Club_Menu::get_options();
		$callback_params = array ('options' => $options, 'message' => $message);
		add_meta_box(self::CODE.'-intro', __('Intro',GENESIS_CLUB_DOMAIN), array(__CLASS__, 'intro_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-menu', __('Responsive Menu',GENESIS_CLUB_DOMAIN), array(__CLASS__, 'responsive_menu_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_action ('admin_enqueue_scripts',array(__CLASS__, 'enqueue_scripts'));
	}

	public static function enqueue_scripts() {
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');	
		add_action('admin_footer-'.self::get_screen_id(), array(__CLASS__, 'toggle_postboxes'));
 	}		

	public static function save() {
		check_admin_referer(__CLASS__);
		$recheck_licence = false;
  		$page_options = explode(',', stripslashes($_POST['page_options']));
  		if ($page_options) {
  			$options = Genesis_Club_Menu::get_options();
  			$updates = false; 
    		foreach ($page_options as $option) {
       			$val = array_key_exists($option, $_POST) ? trim(stripslashes($_POST[$option])) : '';
				$options[$option] = $val;
    		} //end for
   			$saved =  Genesis_Club_Menu::save_options( $options ) ;
  		    $class='updated fade';
   			if ($saved)  {
       			$message = 'display settings saved successfully.';
   			} else
       			$message = 'display settings have not been changed.';
  		} else {
  		    $class='error';
       		$message= 'display settings not found!';
  		}
  		return sprintf('<div id="message" class="%1$s "><p>%2$s %3$s</p></div>',
  			$class, __(GENESIS_CLUB_FRIENDLY_NAME,GENESIS_CLUB_DOMAIN), __($message,GENESIS_CLUB_DOMAIN));
	}

    public static function toggle_postboxes() {
    $hook = self::get_screen_id();
    print <<< SCRIPT
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready( function($) {
	$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
	postboxes.add_postbox_toggles('{$hook}');
});
//]]>
</script>
SCRIPT;
    }
 
 	public static function intro_panel($post,$metabox){	
		$message = $metabox['args']['message'];	 	
		print <<< INTRO_PANEL
<p>The following section allows you to set up responsive hamburger menus on the primary, secondary and right header navigation areas.</p>
{$message}
INTRO_PANEL;
	}

	private static function print_responsive_menu_locations($name, $val) {
		$opts = array(
			'0' => 'No responsive menu', 
			'below' => 'Menu slides open below the hamburger');
		$sidr = array(
			'left' => 'Menu slides open on the left of the screen',
			'right' => 'Menu slides open on the right of the screen');	
		if (Genesis_Club_Options::is_html5()) $opts = $opts + $sidr;	
		print Genesis_Club_Options::form_field($name, $name, self::$tooltips->tip($name), $val, 'select', $opts) ;
	}

	private static function print_responsive_text_field($name, $val, $size, $suffix='', $class='') {	
		$args = array('size'=> $size);
		if (!empty($suffix)) $args['suffix'] = $suffix;
		if (!empty($class)) $args['class'] = $class;
		print Genesis_Club_Options::form_field($name, $name, self::$tooltips->tip($name), $val, 'text', array(), $args) ;
	}
  
	public static function responsive_menu_panel($post,$metabox){	
		$options = $metabox['args']['options'];	 	
		self::print_responsive_text_field("threshold",$options['threshold'], 4, 'px') ;
		self::print_responsive_text_field("icon_size",$options['icon_size'], 4, 'rem') ;
		self::print_responsive_text_field("icon_color",$options['icon_color'], 7, '', 'color-picker') ;
		self::print_responsive_menu_locations('primary', $options['primary']);
		self::print_responsive_menu_locations('secondary', $options['secondary']);
		self::print_responsive_menu_locations('header', $options['header']);
	}	

	public static function settings_panel() {
 		$this_url = $_SERVER['REQUEST_URI'];
		$keys = implode(',', array_keys(self::$tips));
		$title = sprintf('<h2 class="title">%1$s</h2>', __('Hamburger Menu Settings', GENESIS_CLUB_DOMAIN));		
		print <<< ADMIN_START
<div class="wrap">
{$title}
<div id="poststuff" class="metabox-holder"><div id="post-body"><div id="post-body-content">
<form id="menu_options" method="post" action="{$this_url}">
ADMIN_START;
		do_meta_boxes(self::get_screen_id(), 'normal', null); 
		wp_nonce_field(__CLASS__); 
		wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); 
		wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); 
		print <<< ADMIN_END
<p class="submit">
<input type="submit" name="options_update" value="Save Changes" class="button-primary" />
<input type="hidden" name="page_options" value="{$keys}" /></p>
</form></div></div><br class="clear"/></div></div>

ADMIN_END;
	}    
}
