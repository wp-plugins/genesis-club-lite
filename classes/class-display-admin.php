<?php
class Genesis_Club_Display_Admin {
    const CODE = 'genesis-club'; //prefix ID of CSS elements
	const SLUG = 'display';
    const INDICATOR = 'genesis_club_hiding';
    const HIDE_FROM_SEARCH = 'genesis_club_not_on_404';
    const HIDE_TITLE = 'genesis_club_hide_title';
    
    private static $parenthook = GENESIS_CLUB_PLUGIN_NAME;
    private static $slug;
    private static $screen_id;
	private static $tooltips;
	private static $tips = array(
		'remove_blog_title' => array('heading' => 'Remove Blog Title Text', 'tip' => 'Click to remove text and h1 tags from the title on the home page. This feature allows you to place h1 tags elsewhere on the home page and just use a logo image in the #title element.'),
		'logo' => array('heading' => 'Logo URL', 'tip' => 'Enter the full URL of a logo image that will appear in the title element on the left hand side of the header. Consult the theme instructions for recommendation on the logo dimensions, normally a size of around 400px by 200px is okay. The image file can be located in your media library, on Amazon S3 or a CDN.'),
		'comment_invitation' => array('heading' => 'Invitation To Comment', 'tip' => 'Enter your enticement to comment. This will replace "Leave A Reply" in HTML5 sites or "Speak Your Mind" in XHTML sites.'),
		'read_more_text' => array('heading' => 'Read More', 'tip' => 'Enter the text that appears at the end of an excerpt. This will replace "[...]".'),
		'breadcrumb_prefix' => array('heading' => 'Breadcrumb Prefix', 'tip' => 'Enter the text that prefixes the breadcrumb. This will replace "You are here:".'),
		'breadcrumb_archive' => array('heading' => 'Breadcrumb Archives', 'tip' => 'Enter the text that appears at the start of the archive breadcrumb. This will replace "Archives for".'),
		'before_archive' => array('heading' => 'Before Archive', 'tip' => 'Click to add a widget area before the archive. This can be used to add a slider at the top of an archive page.'),
		'after_archive' => array('heading' => 'After Archive', 'tip' => 'Click to add a widget area after the archive loop. This can be used to add a call to action or maybe an ad.'),
		'before_entry_content' => array('heading' => 'Before Entry Content', 'tip' => 'Click to add a widget area immediately before the post content. This is typically used to add social media icons for sharing the content.'),
		'after_entry_content' => array('heading' => 'After Entry Content', 'tip' => 'Click to add a widget area immediately after the post content. If your child theme already has this area then there is no need to create another one. This area is typically used to add social media icons for sharing the content.'),
		'facebook_app_id' => array('heading' => 'Facebook App ID', 'tip' => 'Enter your Facebook App ID (15 characters) as found at https://developers.facebook.com/apps'),
		'facebook_likebox_bgcolor' => array('heading' => 'LikeBox Background Color', 'tip' => 'Choose the background color of the Facebook LikeBox. The Facebook Likebox widget only gives you light and dark options; this allows you to choose a background color that better suits your WordPress theme'),
		'postinfo_shortcodes' => array('heading' => 'Post Info Short Codes', 'tip' => 'Content of the byline that is placed typically below or above the post title. Leave blank to use the child theme defaults or enter here to override. <br/>For example: <br/><code>[post_date format=\'M j, Y\'] by [post_author_posts_link] [post_comments] [post_edit]</code><br/>or to hide Post Info entirely use <code>[]</code>'),
		'postmeta_shortcodes' => array('heading' => 'Post Meta Short Codes', 'tip' => 'Content of the line that is placed typically after the post content. <br/> Leave blank to use the child theme defaults or enter here to override. <br/> For example: <br/><code>[post_categories before=\'More Articles About \'] [post_tags]</code><br/>or to hide Post Meta entirely use <code>[]</code>'),
		'no_page_postmeta' => array('heading' => 'Remove On Pages', 'tip' => 'Strip any post info from pages.'),
		'no_archive_postmeta' => array('heading' => 'Remove On Archives', 'tip' => 'Strip any post info and post meta from the top and bottom of post excerpts on archive pages.')
		);

	public static function init() {
	    self::$slug = self::$parenthook . '-' . self::SLUG;
		add_action('load-edit.php', array( 'Genesis_Club_Options', 'add_tooltip_support'));
		add_action('load-post.php', array( 'Genesis_Club_Options', 'add_tooltip_support'));
		add_action('load-post-new.php', array( 'Genesis_Club_Options', 'add_tooltip_support'));
		add_action('load-widgets.php', array( 'Genesis_Club_Options', 'add_tooltip_support'));

		add_action('do_meta_boxes', array( __CLASS__, 'do_meta_boxes'), 30, 2 );
		add_action('save_post', array( __CLASS__, 'save_postmeta'));
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

    private static function get_keys(){
		return array_keys(self::$tips);
	}
	
    public static function get_tips(){ 
    	return self::$tips; 
    }

	public static function admin_menu() {
		self::$screen_id = add_submenu_page(self::get_parenthook(), __('Display'), __('Display'), 'manage_options', 
			self::get_slug(), array(__CLASS__,'settings_panel'));
		add_action('load-'.self::get_screen_id(), array(__CLASS__, 'load_page'));
	}
	
	public static function load_page() {
 		$message =  isset($_POST['options_update']) ? self::save() : '';	
		$options = Genesis_Club_Display::get_options();
		$callback_params = array ('options' => $options, 'message' => $message);
		add_meta_box(self::CODE.'-intro', __('Intro',GENESIS_CLUB_DOMAIN), array(__CLASS__, 'intro_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-title', __('Responsive Logo',GENESIS_CLUB_DOMAIN), array(__CLASS__, 'logo_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-labelling', __('Labels',GENESIS_CLUB_DOMAIN), array(__CLASS__, 'labelling_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-meta', __('Post Info and Post Meta',GENESIS_CLUB_DOMAIN), array(__CLASS__, 'meta_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-extras', __('Extra Widget Areas',GENESIS_CLUB_DOMAIN), array(__CLASS__, 'extras_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-facebook', __('Facebook',GENESIS_CLUB_DOMAIN), array(__CLASS__, 'facebook_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_action ('admin_enqueue_scripts',array(__CLASS__, 'enqueue_scripts'));
		Genesis_Club_Options::add_tooltip_support();		
		self::$tooltips = new DIY_Tooltip(self::$tips);
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

	public static function save() {
		check_admin_referer(__CLASS__);
		$recheck_licence = false;
  		$page_options = explode(',', stripslashes($_POST['page_options']));
  		if ($page_options) {
  			$options = Genesis_Club_Display::get_options();
  			$updates = false; 
    		foreach ($page_options as $option) {
       			$val = array_key_exists($option, $_POST) ? trim(stripslashes($_POST[$option])) : '';
				$options[$option] = $val;
    		} //end for
   			$saved =  Genesis_Club_Display::save_options($options) ;
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

	public static function save_postmeta($post_id) {
		if (array_key_exists(self::INDICATOR, $_POST)) {
			$post_type = get_post_type( $post_id);
			$keys = array();
			$keys[self::HIDE_FROM_SEARCH] = Genesis_Club_Display::PAGE_HIDER_METAKEY;
			$keys[self::HIDE_TITLE] = Genesis_Club_Display::HIDE_TITLE_METAKEY;
			foreach ($keys as $key => $metakey)
				update_post_meta( $post_id, $metakey, array_key_exists($key, $_POST) ? $_POST[$key] : false);			
			do_action('genesis_club_hiding_settings_save',$post_id);
		}
	}

	public static function do_meta_boxes( $post_type, $context) {
		$post_types=get_post_types();
		if ( in_array($post_type, $post_types ) && ('advanced' === $context )) {
			add_meta_box( 'genesis-club-hiding', 'Genesis Club Hiding Settings', array( __CLASS__, 'hiding_panel' ), $post_type, 'advanced', 'low' );
			$current_screen = get_current_screen();
			if (method_exists($current_screen,'add_help_tab'))
	    		$current_screen->add_help_tab( array(
			        'id'	=> 'genesis_club_help_tab',
    			    'title'	=> __('Genesis Club'),
        			'content'	=> __('
<p>In the <b>Genesis Club Hiding</b> section below you can choose NOT to show this page in site search page results and to remove the page title.</p>')) );
		}
	}

	public static function hiding_panel($post,$metabox) {
		echo Genesis_Club_Options::form_field(self::INDICATOR, self::INDICATOR, '', 1, 'hidden'); 

		echo Genesis_Club_Options::form_field(self::HIDE_FROM_SEARCH, self::HIDE_FROM_SEARCH, 
			 __('Do not show this page on the site search results page'), 
			get_post_meta($post->ID, Genesis_Club_Display::PAGE_HIDER_METAKEY, true), 
			'checkbox', array(), array(), 'br') ;

		echo Genesis_Club_Options::form_field(self::HIDE_TITLE, self::HIDE_TITLE, 
			 __('Do not show the title on this page'), 
			get_post_meta($post->ID, Genesis_Club_Display::HIDE_TITLE_METAKEY, true), 
			'checkbox', array(), array(), 'br') ;

		do_action('genesis_club_hiding_settings_show',$post);
    }
 
 	private static function print_form_field($name, $val, $type, $args = array()) {	
		print Genesis_Club_Options::form_field($name, $name, self::$tooltips->tip($name), $val, $type, array(), $args) ;
	}
 
 	public static function intro_panel($post,$metabox){	
		$message = $metabox['args']['message'];	 	
		print <<< INTRO_PANEL
<p>The following sections allow you to tweak some Genesis settings you want to change on most sites without having to delve into PHP.</p>
{$message}
INTRO_PANEL;
	}

	public static function logo_panel($post,$metabox){	
		$options = $metabox['args']['options'];	 	
		self::print_form_field('remove_blog_title', $options['remove_blog_title'], 'checkbox');
		self::print_form_field('logo', $options['logo'], 'text', array('size' => 80));
	}
 
	public static function facebook_panel($post,$metabox){	
		$options = $metabox['args']['options'];	 
		self::print_form_field('facebook_app_id', $options['facebook_app_id'], 'text', array('size' => 20));
		self::print_form_field('facebook_likebox_bgcolor', $options['facebook_likebox_bgcolor'], 'text', array('size' => 8, 'class' => 'color-picker'));
	}
	
	public static function extras_panel($post,$metabox){	
		$options = $metabox['args']['options'];	 	
		self::print_form_field('before_archive', $options['before_archive'],  'checkbox');
		self::print_form_field('after_archive', $options['after_archive'],  'checkbox');
		self::print_form_field('before_entry_content', $options['before_entry_content'],  'checkbox');
		self::print_form_field('after_entry_content', $options['after_entry_content'],  'checkbox');
	}	

	public static function meta_panel($post,$metabox){	
		$options = $metabox['args']['options'];	 	
		self::print_form_field('no_archive_postmeta', $options['no_archive_postmeta'],  'checkbox');
		self::print_form_field('no_page_postmeta', $options['no_page_postmeta'],  'checkbox');
		self::print_form_field('postinfo_shortcodes', $options['postinfo_shortcodes'], 'text', array('size' => 60));
		self::print_form_field('postmeta_shortcodes', $options['postmeta_shortcodes'], 'text', array('size' => 60));
	}

	public static function labelling_panel($post,$metabox){	
		$options = $metabox['args']['options'];	 	
		self::print_form_field('read_more_text', $options['read_more_text'], 'text', array('size' => 40));
		self::print_form_field('comment_invitation', $options['comment_invitation'], 'text', array('size' => 40));
		self::print_form_field('breadcrumb_prefix', $options['breadcrumb_prefix'], 'text', array('size' => 40));
		self::print_form_field('breadcrumb_archive', $options['breadcrumb_archive'], 'text', array('size' => 40));
	}	

	public static function settings_panel() {
 		$this_url = $_SERVER['REQUEST_URI'];
		$keys = implode(',',self::get_keys());
		$title = sprintf('<h2 class="title">%1$s</h2>', __('Display Settings', GENESIS_CLUB_DOMAIN));		

		print <<< ADMIN_START
<div class="wrap">
{$title}
<div id="poststuff" class="metabox-holder"><div id="post-body"><div id="post-body-content">
<form id="display_options" method="post" action="{$this_url}">
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
