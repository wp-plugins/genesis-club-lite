<?php
class GenesisClubAdmin {
    const CLASSNAME = 'GenesisClubAdmin'; //this class
    const CODE = 'genesis-club';
    const DOMAIN = 'GenesisClub';
    
	private static $plugin = GENESIS_CLUB_PLUGIN_NAME;
	private static $path = GENESIS_CLUB_PLUGIN_PATH;
    private static $screen_id;
    private static $keys;
	private static $tips = array(
		'accordion_disabled' => array('heading' => 'Disable Accordion', 'tip' => 'Click to remove the Accordion if you do not have FAQ'),
		'page_control_disabled' => array('heading' => 'Disable Page Controls', 'tip' => 'Click to remove page level controls if you do not need to make adjustments at a page by page or post by post basis'),
		'profile_disabled' => array('heading' => 'Disable Profile', 'tip' => 'Click to remove the profile if you do not want to use signatures')
	);

	private static $tooltips;

    private static function get_slug(){
		return self::$plugin;
	}

	private static function get_path() {
		return self::$path;
	}
		
    private static function get_screen_id(){
		return self::$screen_id;
	}

    private static function get_keys(){
		return self::$keys;
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
		add_action('admin_menu',array(self::CLASSNAME, 'admin_menu'));
	}

	public static function plugin_action_links( $links, $file ) {
		if ( is_array($links) && (self::$path == $file )) {
			$settings_link = '<a href="' . admin_url( 'admin.php?page='.GENESIS_CLUB_PLUGIN_NAME) . '">Settings</a>';
			array_unshift( $links, $settings_link );
		}
		return $links;
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
	    self::$keys = array_keys(self::$tips);
		self::$tooltips = new DIYTooltip(self::$tips);	    	
 		$message =  isset($_POST['options_update']) ? self::save() : self::fetch_message();
		$options = GenesisClubOptions::get_options();
		$callback_params = array ('options' => $options, 'message' => $message);
		add_meta_box(self::CODE.'-intro', __('Introduction',self::DOMAIN), array(self::CLASSNAME, 'intro_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-disable', __('Disable Features',self::DOMAIN), array(self::CLASSNAME, 'disable_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
 		add_action ('admin_enqueue_scripts',array(self::CLASSNAME, 'enqueue_styles'));		
 		add_action ('admin_enqueue_scripts',array(self::CLASSNAME, 'enqueue_scripts'));		
	}

	public static function enqueue_styles() {
		wp_enqueue_style(self::CODE.'-admin', plugins_url('styles/admin.css',dirname(__FILE__)), array(),GENESIS_CLUB_VERSION);
		wp_enqueue_style(self::CODE.'-tootip', plugins_url('styles/tooltip.css',dirname(__FILE__)), array(),GENESIS_CLUB_VERSION);
 	}	
 
 	public static function enqueue_scripts() {
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');	
		add_action('admin_footer-'.self::get_screen_id(), array(self::CLASSNAME, 'toggle_postboxes'));
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
 
 	private static function link_helper ( $slug, $title, $desc, $disabled_message, $disabled = false) {
		return sprintf ( $disabled ?  '%2$s - %3$s (%4$s)' : '<a href="%1$s">%2$s</a> - %3$s', 
    				admin_url(sprintf('admin.php?page=%1$s', $slug)), 
    				__($title, self::DOMAIN), __($desc, self::DOMAIN), $disabled_message) ;
	}
 		
	public static function intro_panel($post, $metabox) {
		$message = $metabox['args']['message'];	
		$options = $metabox['args']['options'];	 	
 		$this_url = $_SERVER['REQUEST_URI'];
 		$disabled = __('currently disabled - click below to enable', self::DOMAIN);
    	$profile = self::link_helper ( GenesisClubProfileAdmin::get_slug(), 
    				'Profile Settings', 'upload and add a signature to the foot of your posts', 
    				$disabled, $options['profile_disabled']) ;
    	$page_control = self::link_helper ( GenesisClubPostAdmin::get_slug(), 
    				'Page Control','exercise page level control on features such as the user signatures and the 404 sitemap', 
    				$disabled, $options['page_control_disabled']) ;
    	$display = self::link_helper ( GenesisClubDisplayAdmin::get_slug(), 
    				'Display Settings','add a logo in the title, change excerpt, comment and breadcrumb labels', $disabled) ;
    	$accordion = self::link_helper( GenesisClubAccordionAdmin::get_slug(), 
    				'Accordion Settings',
    				'create one or more accordions to display your frequently answered questions.', 
    				$disabled, $options['accordion_disabled']) ;
		print <<< INTRO_PANEL
{$message}
<h4>Current Features</h4>
<ul>
<li>{$accordion}</li>
<li>{$display}</li>
<li>{$page_control}</li>
<li>{$profile}</li>
<li>Facebook Likebox Widget - drag a Facebook Likebook widget into the sidebar on the Appearance > Widgets page</li>
</ul>
<h4>Coming Soon</h4>
<ul>
<li>Enhanced Featured Posts Widget - allows you to control which categories to include or exclude in post selection</li>
</ul>
INTRO_PANEL;
	}

	private static function checkbox_helper($name, $checked ) {
		return sprintf('<label>%1$s</label><input type="checkbox" name="%2$s" %3$s value="1" /><br/>',
			self::$tooltips->tip($name), $name, $checked ? 'checked="checked"' : '');
	}
	
	public static function disable_panel ($post, $metabox) {
		$options = $metabox['args']['options'];
		$keys = self::get_keys();
		print <<< DISABLE_PANEL
<p>Here you can disable features that you do not intend to use. </p>
DISABLE_PANEL;
		foreach ($keys as $key) print self::checkbox_helper($key, $options[$key]);	
	}

    private static function fetch_message() {
		$message = '' ;
		if (isset($_REQUEST['message']) && ! empty($_REQUEST['message'])) { 
			$message = urldecode($_REQUEST['message']);
			$_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
			$style = strpos($message,'success') !== FALSE ? ' success' : (strpos($message,'fail') !== FALSE ? ' failure' : '');
			$message = sprintf('<div id="message" class="%2$s"><div class="updated">%1$s</div></div>',$message,$style); 
		}
		return $message;
    } 

	public static function save() {
		check_admin_referer(self::CLASSNAME);
		$options = GenesisClubOptions::get_options(false);
		$new_options = array();
		$changed = false;
		$keys = explode(',', stripslashes($_POST['page_options']));
  		if ($keys && is_array($keys)) {
  			$updates = false;
    		foreach ($keys as $option) {
       			$option = trim($option);
       			$val = array_key_exists($option, $_POST) ? trim(stripslashes($_POST[$option])) : '';
       			if ($val != $options[$option]) $new_options[$option] = $val;	
    		} //end for
			$updates = (count($new_options) > 0) && GenesisClubOptions::save_options($new_options); 
  		    $class="updated fade";
   			if ($updates)  {
		 		$redir = $_SERVER['REQUEST_URI'];
       			$message = __("Genesic Club Settings saved.",self::DOMAIN);
				$redir = add_query_arg( array('message' => urlencode($message)), $redir ); //add the message 
    			wp_redirect( $redir ); 
    			exit;
   			} else
       			$message = __("No Genesis Club settings were changed since last update.",self::DOMAIN);
  		} else {
  		    $class="error";
       		$message= "Genesis Club settings not found!";
  		}
  		return sprintf('<div id="message" class="%1$s">%2$s</div>', $class, $message);
	}

	public static function settings_panel() {
 		$this_url = $_SERVER['REQUEST_URI'];
    	$title = sprintf('<h2>%1$s %2$s</h2>',GENESIS_CLUB_FRIENDLY_NAME, GENESIS_CLUB_VERSION);				
		$icon = get_screen_icon();
		$keys = implode(',',self::get_keys());		
		$nonces = self::get_nonces();
		print <<< SETTINGS_PANEL
<div class="wrap">
{$icon} {$title}
<div id="poststuff" class="metabox-holder"><div id="post-body"><div id="post-body-content">
<form id="admin_options" method="post" action="{$this_url}">
SETTINGS_PANEL;
do_meta_boxes(self::get_screen_id(), 'normal', null); 
		print <<< SETTINGS_END_PANEL
<p class="submit"><input type="submit"  class="button-primary" name="options_update" value="Save Changes" />
<input type="hidden" name="page_options" value="{$keys}" />	
{$nonces}
</p>
</form>
</div></div><br class="clear"/></div>
</div>
SETTINGS_END_PANEL;
	} 
}
?>