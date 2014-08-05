<?php
class Genesis_Club_Admin {
    const CODE = 'genesis-club';
    
	private static $path = GENESIS_CLUB_PLUGIN_PATH;
    private static $screen_id;
    private static $keys;
	private static $tooltips;

    private static function get_slug(){
		return GENESIS_CLUB_PLUGIN_NAME;
	}
		
    private static function get_screen_id(){
		return self::$screen_id;
	}

    private static function get_keys(){
		return self::$keys;
	}

	public static function init() {
		add_action('admin_menu',array(__CLASS__, 'admin_menu'));
        add_action('admin_enqueue_scripts', array('Genesis_Club_Options', 'register_styles'));		
		add_action('admin_print_styles', array(__CLASS__, 'style_icon'));        	
	}

	public static function style_icon() {
		print <<< ICON
<style type="text/css">
#adminmenu .menu-icon-generic.toplevel_page_genesis-club-lite div.wp-menu-image:before { content: '\\f116'; }
</style>
ICON;
	}

	public static function get_nonces() {
		return wp_nonce_field(__CLASS__,'_wpnonce', true, false).
			wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false, false ).
			wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false, false);
	}

	public static function admin_menu() {
		self::$screen_id = add_menu_page(GENESIS_CLUB_FRIENDLY_NAME, GENESIS_CLUB_FRIENDLY_NAME, 'manage_options', 
			GENESIS_CLUB_PLUGIN_NAME, array(__CLASS__,'settings_panel') );
		$intro = sprintf('Dashboard (v%1$s)', GENESIS_CLUB_VERSION);				
		add_submenu_page(GENESIS_CLUB_PLUGIN_NAME, GENESIS_CLUB_FRIENDLY_NAME, $intro, 'manage_options', GENESIS_CLUB_PLUGIN_NAME,array(__CLASS__,'settings_panel') );
 		add_action('load-'.self::get_screen_id(), array(__CLASS__, 'load_page')); 		
	}

	public static function load_page() {
 		if (isset($_POST['options_update'])) self::save();
		add_action ('admin_enqueue_scripts',array(__CLASS__, 'enqueue_styles'));		
 		add_action ('admin_enqueue_scripts',array(__CLASS__, 'enqueue_scripts'));		
		Genesis_Club_Options::add_tooltip_support();
	}

	public static function enqueue_styles() {
		wp_enqueue_style(self::CODE.'-dashboard', plugins_url('styles/dashboard.css',dirname(__FILE__)), array(),GENESIS_CLUB_VERSION);
 	}	
 	
	public static function enqueue_scripts() {
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');	
		wp_enqueue_script('mixitup', plugins_url('scripts/jquery.mixitup.min.js',dirname(__FILE__)), array( 'jquery' ), GENESIS_CLUB_VERSION );
		add_action('admin_footer-'.self::get_screen_id(), array(__CLASS__, 'show_modules'));
 	}

    public static function show_modules() {
    	$hook = self::get_screen_id();
    	print <<< SCRIPT
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready( function($) {
	$('.products_grid').mixitup();

	$('#cb-select-all').click(function(){
        var checkboxes = $(".products_grid").find(':checkbox').not(':disabled');
        if($(this).prop('checked')) {
          checkboxes.prop('checked', true);
        } else {
          checkboxes.prop('checked', false);
        }
    });
});
//]]>
</script>
SCRIPT;
    }	

    private static function fetch_message() {
		$message = '' ;
		if (isset($_REQUEST['message']) && ! empty($_REQUEST['message'])) { 
			$message = urldecode($_REQUEST['message']);
			$_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
			$style = strpos($message,'success') !== FALSE ? ' success' : (strpos($message,'fail') !== FALSE ? ' failure' : '');
			$message = sprintf('<div class="updated %2$$">%1$s</div>',$message,$style); 
		}
		return $message;
    } 

	public static function save() {
		check_admin_referer(__CLASS__);
		$modules = Genesis_Club_Plugin::get_modules_present();
		$new_options = array();
		$checked =  array_key_exists('checked_modules', $_POST) ? (array) $_POST['checked_modules'] : array();
		foreach ( $modules as $module => $info ) {
			$key = Genesis_Club_Plugin::get_disabled_key($module);
			$new_options[$key] = ! in_array($module, $checked); 
		}
  		$updates = Genesis_Club_Options::save_options($new_options); 
   		$message = $updates ? 'Genesic Club Settings saved.' : 
   				'No Genesis Club settings were changed since last update.';
		$redir = add_query_arg( array('message' => urlencode(__($message,GENESIS_CLUB_DOMAIN))), $_SERVER['REQUEST_URI'] ); //add the message 
    	wp_redirect( $redir ); 
    	exit;
	}

	private static function checkbox_helper($module, $checked, $disabled = false) {
		return sprintf ('<input type="checkbox" id="cb-select-%1$s" name="checked_modules[]" %2$svalue="%1$s" %3$s/>',
			$module, $checked ? 'checked="checked" ' : '', $disabled ? 'disabled="disabled" ' : '');			
	}

	public static function settings_panel() {
 		$this_url = $_SERVER['REQUEST_URI'];
 		$friendly = __(GENESIS_CLUB_FRIENDLY_NAME);
    	$title = sprintf('<h2 class="title">%1$s %2$s</h2>',$friendly, GENESIS_CLUB_VERSION);				
 		$pro = sprintf('<a target="_blank" rel="external" href="%1$s">Genesis Club Pro</a>', GENESIS_CLUB_PRO_URL); 
		$nonces = self::get_nonces();
		$message = self::fetch_message();
		$modules = Genesis_Club_Plugin::get_modules();
		print <<< SETTINGS_PANEL
<div class="wrap">
{$title}
<div id="poststuff"><div id="post-body"><div id="post-body-content">
{$message}
<form id="admin_options" method="post" action="{$this_url}">
<p class="save"><input type="submit" class="button-primary" name="options_update" value="Save Changes" /></p>
<div class="actions"><input id="cb-select-all" type="checkbox" />Select/Deselect All or individually select the Genesis Club modules you need.  Or click the link to find out more about {$pro} features.</div>
<ul class="products_grid" class="wrap">
SETTINGS_PANEL;
		foreach ( $modules as $module => $info ) {
			$present = Genesis_Club_Plugin::module_exists($module);
			$enabled = $present && Genesis_Club_Plugin::is_module_enabled($module);
			$verbose_status = $present ? ($enabled ? '' :  __('Inactive', GENESIS_CLUB_DOMAIN)) :  __('Pro', GENESIS_CLUB_DOMAIN);
			printf ('<li class="mix product-card"><div class="status-action clear"><span class="status">%1$s</span>%2$s</div><h2>%3$s</h2><div class="summary">%4$s</div></li>',
				$verbose_status, self::checkbox_helper($module, $enabled, ! $present), $info['heading'], $info['tip']);
		}
		print <<< SETTINGS_END_PANEL
</ul>
<p class="save"><input type="submit" class="button-primary" name="options_update" value="Save Changes" /></p>
<p>
{$nonces}
</p>
</form>
</div></div><br class="clear"/></div>
</div>
SETTINGS_END_PANEL;
	} 

}
