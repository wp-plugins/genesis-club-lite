<?php
class Genesis_Club_Bar_Admin {
    const CODE = 'genesis-club'; //prefix ID of CSS elements
	const SLUG = 'bar';
    const TOGGLE_BAR = 'genesis_club_toggle_bar';

    private static $parenthook = GENESIS_CLUB_PLUGIN_NAME;
    private static $slug;
    private static $screen_id;
	private static $tips = array(
			'bar_title' => array('heading' => 'Title', 'tip' => 'Only displayed on admin site for labelling purposes'),
			'bar_enabled' => array('heading' => 'Enable Default Bar', 'tip' => 'Click to enable this Top Bar on the home page, archives, posts, etc. If not enabled then any settings below are ignored and top bar widgets will be displayed'),
			'bar_full_message' => array('heading' => 'Full Message', 'tip' => 'Enter the full message you want to display. This can be HTML so can have a link or a button. This message will be displayed on all sizes of device if you leave the following fields blank.'),
			'bar_laptop_message' => array('heading' => 'Laptop Message', 'tip' => 'Enter the message you want to display on laptop devices - device width is between 800 and 1024 px. Leave blank to use the full message.'),
			'bar_tablet_message' => array('heading' => 'Tablet Message', 'tip' => 'Enter the message you want to display on tablets - device width is between 480 and 800 px. Leave blank to use the full message or the laptop size message if you have specified one.'),
			'bar_short_message' => array('heading' => 'Mobile Message', 'tip' => 'Enter the message you want to display on mobile devices under a width of 480px. This could be short message probably no more than 20 characters or maybe just a button with some text on it. Leave blank to use the full message, the laptop size message or the tablet size message if you have specified one.'),
			'bar_font_color' => array('heading' => 'Font Color', 'tip' => 'Enter the color of the font text.'),
			'bar_background' => array('heading' => 'Background', 'tip' => 'Enter the background for the bar. This can be a simple color, or a background image. For example, #FED444 url(http://images.site.com/bg.jpg) no-repeat fixed center top'),
			'bar_show_timeout'=> array('heading' => 'Delay to Show The Bar', 'tip' => 'Enter the number of seconds to wait before displaying the bar. Leave blank or set to zero if you want the bar to load immediately'),
			'bar_hide_timeout' => array('heading' => 'Delay to Hide The Bar', 'tip' => 'Enter the number of seconds to wait before hiding the bar. Leave blank or set to zero to have the bar remain visible.'),
			'bar_bounce' => array('heading' => 'Bounce', 'tip' => 'Click to have the bar bounce on being displayed to attract attention.'),
			'bar_shadow' => array('heading' => 'Shadow', 'tip' => 'Click to add a shadow under the bar.'),
			'bar_opener' => array('heading' => 'Opener Tab', 'tip' => 'Here you can choose have a Tab/Button on the right hand side on the bar that can be used to open the bar. This also adds a button on the bar to close it.'),
			);
	private static $tooltips;
	
	public static function init() {
		self::$slug = self::$parenthook . '-' . self::SLUG;
		add_action('admin_menu',array(__CLASS__, 'admin_menu'));
		add_action('genesis_club_hiding_settings_show', array(__CLASS__, 'page_visibility_show'), 10, 1);
		add_action('genesis_club_hiding_settings_save', array(__CLASS__, 'page_visibility_save'), 10, 1);
		add_action('load-widgets.php', array( 'Genesis_Club_Options', 'add_tooltip_support'));
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

    public static function get_tips(){
		return self::$tips;
	}	
	
    private static function get_keys(){
		return array_keys(self::$tips);;
	}	

	public static function admin_menu() {
		self::$screen_id = add_submenu_page(self::get_parenthook(), __('Bar'), __('Bar'), 'manage_options', 
			self::get_slug(), array(__CLASS__,'settings_panel'));
		add_action('load-'.self::get_screen_id(), array(__CLASS__, 'load_page'));
	}

	public static function load_page() {
 		$message =  isset($_POST['options_update']) ? self::save() : '';	
		self::$tooltips = new DIY_Tooltip(self::$tips);
		Genesis_Club_Options::add_tooltip_support();
		$options = Genesis_Club_Bar::get_options();
		$callback_params = array ('options' => $options, 'message' => $message);
		add_meta_box(self::CODE.'-bar', __('Top Bar',GENESIS_CLUB_DOMAIN), array(__CLASS__, 'bar_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_action('admin_enqueue_scripts',array(__CLASS__, 'enqueue_scripts'));
	}


	public static function enqueue_scripts() {
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');
		add_action('admin_footer-'.self::get_screen_id(), array(__CLASS__, 'toggle_postboxes'));
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

	public static function do_meta_boxes( $post_type, $context) {
		$post_types=get_post_types();
		if ( in_array($post_type, $post_types ) && ('advanced' === $context )) {
			add_meta_box( 'genesis-club-bar-visibility', 'Top Bar Control Settings', array( __CLASS__, 'bar_visibility_panel' ), $post_type, 'advanced', 'low' );
		}
	}

	public static function save_postmeta($post_id) {
		if (array_key_exists('genesis_club_toggle_bar', $_POST)) {
			$key = 'hide'==$_POST['genesis_club_toggle_bar'] ? Genesis_Club_Bar::HIDE_BAR_METAKEY : Genesis_Club_Bar::SHOW_BAR_METAKEY;	
			$val = array_key_exists($key, $_POST) ? $_POST[$key] : false;
			update_post_meta( $post_id, $key, $val );
		}	
	}

	public static function save() {
		check_admin_referer(__CLASS__);
  		$page_options = explode(',', stripslashes($_POST['page_options']));
  		if ($page_options) {
  			$options = Genesis_Club_Options::get_option('bar');
  			$updates = false; 
    		foreach ($page_options as $option) {
       			$val = array_key_exists($option, $_POST) ? trim(stripslashes($_POST[$option])) : '';
				if (substr($option,0,4)=='bar_') {
					$option = substr($option,4);       			
      				$options[$option] = esc_html($val);
 				} 
    		} //end for
   			$saved =  Genesis_Club_Options::save_options(array('bar' => $options)) ;
  		    $class='updated fade';
   			if ($saved)  {
       			$message = 'Bar Settings saved successfully.';
   			} else
       			$message = 'Bar Settings have not been changed.';
  		} else {
  		    $class='error';
       		$message= 'Settings not found!';
  		}
  		return sprintf('<div id="message" class="%1$s"><p>%2$s</p></div>',$class, __($message,GENESIS_CLUB_DOMAIN));
	}

    public static function bar_on_posts($post_type) {
		return 'post'==$post_type;
    }  
    
	static function page_visibility_save($post_id) {
		$key = self::TOGGLE_BAR;
		$post_type = get_post_type( $post_id);
		$meta_key = Genesis_Club_Bar::get_toggle_meta_key($post_type);	
		update_post_meta( $post_id, $meta_key, array_key_exists($key, $_POST) ? $_POST[$key] : false);
	}

	static function page_visibility_show($post) {
		$key = self::TOGGLE_BAR;	
		$meta_key = Genesis_Club_Bar::get_toggle_meta_key($post->post_type);
		echo Genesis_Club_Options::form_field($key, $key, 
			__(strpos($meta_key, 'hide') !== FALSE ? 'Do not show the top bar on this page' : 'Show the top bar on this page'), 
			get_post_meta($post->ID, $meta_key, true),  'checkbox', array(), array(), 'br') ;
    }

	public static function bar_visibility_panel() {
		global $post;
		$hide = self::bar_on_posts($post->post_type) ;
		$key = $hide ? Genesis_Club_Bar::HIDE_BAR_METAKEY : Genesis_Club_Bar::SHOW_BAR_METAKEY;
		$toggle = get_post_meta($post->ID, $key, true);
		$bar_toggle = $toggle?' checked="checked"':'';		
		$action = $hide ? 'hide' : 'show'; 
		$label =  __($hide ? 'Do not show the top bar on this page' : 'Show the top bar on this page');
		print <<< BAR_VISIBILITY
<p class="meta-options"><input type="hidden" name="genesis_club_toggle_bar" value="{$action}" />
<label><input class="valinp" type="checkbox" name="{$key}" id="{$key}" {$bar_toggle} value="1" />&nbsp;{$label}</label></p>
BAR_VISIBILITY;
    }    

	private static function print_form_field($name, $val, $type, $args = array()) {	
		print Genesis_Club_Options::form_field($name, $name, self::$tooltips->tip($name), $val, $type, array(), $args) ;
	}
 
	public static function bar_panel($post,$metabox){	
		$options = $metabox['args']['options'];
		$message = $metabox['args']['message'];			 	
		print <<< BAR_PANEL
{$message}	
<p>The top bar is a responsive bar that allows you add a message at the top of each page: you can display different messages on different devices. For example, you
can specify a click to call button on mobile devices.<p>
<p>Below you can set the default bar settings. Use this feature if you want to have the same message content in the top bar on most of the pages on the site.</p>
<p>You can use the <em>Genesis Club Hiding Settings</em> in the Page Editor to suppress the top bar on pages where you do not want it to appear.</p>
BAR_PANEL;
		self::print_form_field('bar_enabled',$options['enabled'], 'checkbox');
		print('<h4>Messages</h4>');
		self::print_form_field('bar_full_message',$options['full_message'], 'text', array('size' => 80));
		self::print_form_field('bar_laptop_message',$options['laptop_message'], 'text', array('size' => 70));
		self::print_form_field('bar_tablet_message',$options['tablet_message'], 'text', array('size' => 60));
		self::print_form_field('bar_short_message',$options['short_message'], 'text', array('size' => 50));
		print('<h4>Colors</h4>');
		self::print_form_field('bar_font_color',$options['font_color'], 'text', array('size' => 8, 'class' => 'color-picker'));
		self::print_form_field('bar_background',$options['background'], 'text', array('size' => 80));
		print('<h4>Timing</h4>');
		self::print_form_field('bar_show_timeout',$options['show_timeout'], 'text', array('size' => 4, 'suffix' => 'seconds'));
		self::print_form_field('bar_hide_timeout',$options['hide_timeout'], 'text', array('size' => 4, 'suffix' => 'seconds'));
		print('<h4>Effects</h4>');
		self::print_form_field('bar_bounce',$options['bounce'], 'checkbox');
		self::print_form_field('bar_shadow',$options['shadow'], 'checkbox');
		self::print_form_field('bar_opener',$options['opener'], 'checkbox');
	}

	public static function settings_panel() {
 		$this_url = $_SERVER['REQUEST_URI'];
		$keys = implode(',',self::get_keys());
		$title = sprintf('<h2 class="title">%1$s</h2>', __('Bar Settings', GENESIS_CLUB_DOMAIN));		
		print <<< ADMIN_START
<div class="wrap">
{$title}
<div id="poststuff" class="metabox-holder"><div id="post-body"><div id="post-body-content">
<form id="bar_options" method="post" action="{$this_url}">
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
