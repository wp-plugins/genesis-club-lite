<?php
class GenesisClubAdmin {
    const CLASSNAME = 'GenesisClubAdmin'; //this class
    const CODE = 'genesis-club';
    const DOMAIN = 'GenesisClub';
    
	private static $plugin = GENESIS_CLUB_PLUGIN_NAME;
	private static $path = GENESIS_CLUB_PLUGIN_PATH;
    private static $screen_id;
    private static $keys;
    private static $initialized = false;

    private static function get_slug(){
		return self::$plugin;
	}

	private static function get_path() {
		return self::$path;
	}
		
    private static function get_screen_id(){
		return self::$screen_id;
	}


	public static function deactivate() {
		if (is_plugin_active(self::$path)) deactivate_plugins( self::$path); 
	}

	public static function activation() { //called on plugin activation
    	if ( basename( TEMPLATEPATH ) != 'genesis' ) {
        	self::deactivate();
       		 wp_die(  __( sprintf('Sorry, you cannot use %1$s unless you are using a child theme based on the StudioPress Genesis theme framework. The %1$s plugin has been deactivated. Go to the WordPress <a href="%2$s">Plugins page</a>.',
        		GENESIS_CLUB_FRIENDLY_NAME, get_admin_url(null, 'plugins.php')), GENESIS_CLUB_PLUGIN_NAME ));
   		} 
	}

	public static function init() {
	    if (self::$initialized) return true;
		self::$initialized = true;
		self::$path = self::get_path();	
		add_filter('screen_layout_columns', array(self::CLASSNAME, 'screen_layout_columns'), 10, 2);
		add_action('admin_menu',array(self::CLASSNAME, 'admin_menu'));
	}

	public static function plugin_action_links( $links, $file ) {
		if ( is_array($links) && (self::$path == $file )) {
			$settings_link = '<a href="' . admin_url( 'admin.php?page='.GENESIS_CLUB_PLUGIN_NAME) . '">Settings</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
	}
	
	public static function screen_layout_columns($columns, $screen) {
		if (!defined( 'WP_NETWORK_ADMIN' ) && !defined( 'WP_USER_ADMIN' )) {
			if ($screen == self::get_screen_id()) {
				$columns[self::get_screen_id()] = 2;
			}
		}
		return $columns;
	}

	public static function get_nonces() {
		return wp_nonce_field(self::CLASSNAME,'_wpnonce', true, false).
			wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false, false ).
			wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false, false);
	}

	public static function admin_menu() {
		self::$screen_id = add_menu_page(GENESIS_CLUB_FRIENDLY_NAME, GENESIS_CLUB_FRIENDLY_NAME, 'manage_options', 
			GENESIS_CLUB_PLUGIN_NAME, array(self::CLASSNAME,'settings_panel'),plugins_url('images/icon-16.png',dirname(__FILE__)) );
		add_submenu_page(GENESIS_CLUB_PLUGIN_NAME, GENESIS_CLUB_FRIENDLY_NAME, 'Intro', 'manage_options', GENESIS_CLUB_PLUGIN_NAME,array(self::CLASSNAME,'settings_panel') );
 		add_action('load-'.self::get_screen_id(), array(self::CLASSNAME, 'load_page')); 		
	}

	public static function load_page() {
 		$message =  isset($_POST['options_update']) ? self::save_licence() : '';	
		$options = GenesisClubOptions::get_options();
		$callback_params = array ('options' => $options, 'message' => $message);
		add_meta_box(self::CODE.'-intro', __('Introduction',self::DOMAIN), array(self::CLASSNAME, 'intro_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
 		add_action ('admin_enqueue_scripts',array(self::CLASSNAME, 'enqueue_styles'));		
	}

	public static function enqueue_styles() {
		wp_enqueue_style(self::CODE.'-admin', plugins_url('styles/admin.css',dirname(__FILE__)), array(),GENESIS_CLUB_VERSION);
		wp_enqueue_style(self::CODE.'-tootip', plugins_url('styles/tooltip.css',dirname(__FILE__)), array(),GENESIS_CLUB_VERSION);
 	}	
 	
	public static function intro_panel($post, $metabox) {
		$message = $metabox['args']['message'];	 		
    	$display_url = admin_url(sprintf('admin.php?page=%1$s', GenesisClubDisplayAdmin::get_slug()));
    	$user_url = admin_url(sprintf('admin.php?page=%1$s', GenesisClubProfileAdmin::get_slug()));
    	$page_url = admin_url(sprintf('admin.php?page=%1$s', GenesisClubPostAdmin::get_slug()));
		print <<< INTRO_PANEL
{$message}
<h4>Current Features</h4>
<ul>
<li><a href="{$display_url}">Display Settings</a> - add a logo, add widget areas, change excerpt, comment and breadcrumb labels</li>
<li><a href="{$page_url}">Page Control</a> - exercise page level control on features such as user signatures and the 404 sitemap.</li>
<li><a href="{$user_url}">Signatures</a> - upload and add a signature to the foot of your posts</li>
</ul>
<h4>Coming Soon</h4>
<ul>
<li>Enhanced Featured Posts Widget - allows you to control which categories to include or exclude in post selection</li>
</ul>
INTRO_PANEL;
	}

	public static function settings_panel() {
 		$this_url = $_SERVER['REQUEST_URI'];
    	$title = sprintf('<h2>%1$s %2$s</h2>',GENESIS_CLUB_FRIENDLY_NAME, GENESIS_CLUB_VERSION);				
		$icon = get_screen_icon();
		$nonces = self::get_nonces();
		print <<< SETTINGS_PANEL
<div class="wrap">
{$icon} {$title}
<div id="poststuff" class="metabox-holder"><div id="post-body"><div id="post-body-content">
<form id="admin_options" method="post" action="{$this_url}">
SETTINGS_PANEL;
do_meta_boxes(self::get_screen_id(), 'normal', null); 
		print <<< SETTINGS_END_PANEL
{$nonces}
</p>
</form>
</div></div><br class="clear"/></div>
</div>
SETTINGS_END_PANEL;
	} 
}
?>