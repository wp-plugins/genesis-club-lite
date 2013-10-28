<?php
class GenesisClubDisplayAdmin {
    const CLASSNAME = 'GenesisClubDisplayAdmin'; //this class
    const CODE = 'genesis-club'; //prefix ID of CSS elements
    const DOMAIN = 'GenesisClub'; //text domain for translation
	const SLUG = 'display';

    private static $parenthook;
    private static $slug;
    private static $screen_id;
    private static $keys;
	private static $tooltips;
	private static $tips = array(
		'remove_blog_title' => array('heading' => 'Remove Blog Title Text', 'tip' => 'Click to remove text and h1 tags from the title on the home page. This feature allows you to place h1 tags elsewhere on the home page and just use a logo image in the #title element.'),
		'logo' => array('heading' => 'Logo URL', 'tip' => 'Enter the full URL of a logo image that will appear in the title element on the left hand side of the header. Consult the theme instructions for recommendation on the logo dimensions, normally a size of around 400px by 200px is okay. The image file can be located in your media library, on Amazon S3 or a CDN.'),
		'comment_invitation' => array('heading' => 'Invitation To Comment', 'tip' => 'Enter your enticement to comment. This will replace "Speak Your Mind".'),
		'read_more_text' => array('heading' => 'Read More', 'tip' => 'Enter the text that appears at the end of an excerpt. This will replace "[...]".'),
		'breadcrumb_prefix' => array('heading' => 'Breadcrumb Prefix', 'tip' => 'Enter the text that prefixes the breadcrumb. This will replace "You are here:".'),
		'breadcrumb_archive' => array('heading' => 'Breadcrumb Archives', 'tip' => 'Enter the text that appears at the start of the archive breadcrumb. This will replace "Archives for".'),
		'before_archive' => array('heading' => 'Before Archive', 'tip' => 'Click to add a widget area before the archive. This can be used to add a slider at the top of an archive page.'),
		'after_archive' => array('heading' => 'After Archive', 'tip' => 'Click to add a widget area after the archive loop. This can be used to add a call to action or maybe an ad.'),
		'before_entry_content' => array('heading' => 'Before Entry Content', 'tip' => 'Click to add add a widget area immediately before the content. This is typically used to add social media icons for sharing the content.'),
		'after_entry_content' => array('heading' => 'After Entry Content', 'tip' => 'Click to add add a widget area immediately after the content. If your child theme already has this area then there is no need to create another one. This area is typically used to add social media icons for sharing the content.'),
		'facebook_likebox_bgcolor' => array('heading' => 'LikeBox Background Color', 'tip' => 'Enter the 6 character color code preceded by a hash(#) that will be used as the background color of the Facebook LikeBox. The Facebook Likebox widget only gives you light and dark options; this allows you to choose a background color that better suits your WordPress theme'),
		'responsive_menu_threshold' => array('heading' => 'Device Threshold', 'tip' => 'Enter the size in pixels at which the full menu is collapsed into the "hamburger" icon or leave blank to disable this feature.'),
		'responsive_menu_icon_color' => array('heading' => 'Hamburger Icon Color', 'tip' => 'Color of the menu icon (e.g #808080), or leave blank if you want the icon to adopt the same color as the links in the menu.'),
		'postinfo_shortcodes' => array('heading' => 'Post Info Short Codes', 'tip' => 'Content of the byline that is placed typically below or above the post title. Leave blank to use the child theme defaults or enter here to override. <br/>For example: <br/><code>[post_date format=\'M j, Y\'] by [post_author_posts_link] [post_comments] [post_edit]</code>'),
		'postmeta_shortcodes' => array('heading' => 'Post Meta Short Codes', 'tip' => 'Content of the line that is placed typically after the post content. <br/> Leave blank to use the child theme defaults or enter here to override. <br/> For example: <br/><code>[post_categories before=\'More Articles About \'] [post_tags]</code>')
		);
	
	public static function init() {
	    self::$keys = array_keys(self::$tips);
		self::$parenthook = GENESIS_CLUB_PLUGIN_NAME;
	    self::$slug = self::$parenthook . '-' . self::SLUG;
	    self::$screen_id = self::$parenthook.'_page_' . self::$slug;
		add_action('admin_menu',array(self::CLASSNAME, 'admin_menu'));
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
		return self::$keys;
	}
	
 	public static function get_url($id='', $noheader = false) {
		return admin_url('admin.php?page='.self::get_slug().(empty($id) ? '' : ('&amp;id='.$id)).(empty($noheader) ? '' : '&amp;noheader=true'));
	}
	
	public static function enable_screen($show_screen,$screen) {
		if ($screen->id == self::get_screen_id())
			return true;
		else
			return $show_screen;
	}	

	public static function admin_menu() {
		add_submenu_page(self::get_parenthook(), __('Display'), __('Display'), 'manage_options', 
			self::get_slug(), array(self::CLASSNAME,'settings_panel'));
		add_action('load-'.self::get_screen_id(), array(self::CLASSNAME, 'load_page'));
	}
	
	public static function load_page() {
 		$message =  isset($_POST['options_update']) ? self::save() : '';	
		$options = GenesisClubOptions::get_options();
		$callback_params = array ('options' => $options, 'message' => $message);
		add_meta_box(self::CODE.'-intro', __('Intro',self::DOMAIN), array(self::CLASSNAME, 'intro_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-title', __('Responsive Logo',self::DOMAIN), array(self::CLASSNAME, 'logo_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-menu', __('Responsive Menu',self::DOMAIN), array(self::CLASSNAME, 'responsive_menu_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-labelling', __('Labels',self::DOMAIN), array(self::CLASSNAME, 'labelling_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-meta', __('Post Info and Post Meta',self::DOMAIN), array(self::CLASSNAME, 'meta_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-extras', __('Extra Widgets',self::DOMAIN), array(self::CLASSNAME, 'extras_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_meta_box(self::CODE.'-facebook', __('Facebook',self::DOMAIN), array(self::CLASSNAME, 'facebook_panel'), self::get_screen_id(), 'normal', 'core', $callback_params);
		add_action ('admin_enqueue_scripts',array(self::CLASSNAME, 'enqueue_styles'));
		add_action ('admin_enqueue_scripts',array(self::CLASSNAME, 'enqueue_scripts'));
		self::$tooltips = new DIYTooltip(self::$tips);
	}
	
	public static function enqueue_styles() {
		wp_enqueue_style(self::CODE.'-admin', plugins_url('styles/admin.css',dirname(__FILE__)), array(),GENESIS_CLUB_VERSION);
		wp_enqueue_style(self::CODE.'-tooltip', plugins_url('styles/tooltip.css',dirname(__FILE__)), array(),GENESIS_CLUB_VERSION);
	}

	public static function enqueue_scripts() {
		wp_enqueue_script('common');
		wp_enqueue_script('wp-lists');
		wp_enqueue_script('postbox');	
		add_action('admin_footer-'.self::get_screen_id(), array(self::CLASSNAME, 'toggle_postboxes'));
 	}		

	public static function save() {
		check_admin_referer(self::CLASSNAME);
		$recheck_licence = false;
  		$page_options = explode(',', stripslashes($_POST['page_options']));
  		if ($page_options) {
  			$options = GenesisClubOptions::get_options();
  			$updates = false; 
    		foreach ($page_options as $option) {
       			$val = array_key_exists($option, $_POST) ? trim(stripslashes($_POST[$option])) : '';
				$options[$option] = $val;
    		} //end for
   			$saved =  GenesisClubOptions::save_options($options) ;
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
  			$class, __(GENESIS_CLUB_FRIENDLY_NAME,self::DOMAIN), __($message,self::DOMAIN));
	}

    public static function toggle_postboxes() {
    $hook = self::get_screen_id();
    print <<< SCRIPT
	<script type="text/javascript">
		//<![CDATA[
		jQuery(document).ready( function($) {
			// close postboxes that should be closed
			$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
			// postboxes setup
			postboxes.add_postbox_toggles('{$hook}');
		});
		//]]>
	</script>
SCRIPT;
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
		$tip1 = self::$tooltips->tip('remove_blog_title');		
		$tip2 = self::$tooltips->tip('logo');		
		$remove_blog_title = $options['remove_blog_title'] ? 'checked="checked"' : '';	
		print <<< LOGO_PANEL
<label>{$tip1}</label><input type="checkbox" id="remove_blog_title" name="remove_blog_title" {$remove_blog_title} value="1" /><br/>
<label>{$tip2}</label><input type="text" name="logo" size="80" value="{$options['logo']}" /><br/>
LOGO_PANEL;
	}
 
	public static function facebook_panel($post,$metabox){	
		$options = $metabox['args']['options'];	 
		$tip1 = self::$tooltips->tip('facebook_likebox_bgcolor');		
		print <<< FACEBOOK_PANEL
<label>{$tip1}</label><input type="text" name="facebook_likebox_bgcolor" size="7" value="{$options['facebook_likebox_bgcolor']}" /><br/>
FACEBOOK_PANEL;
	}
	
	public static function extras_panel($post,$metabox){	
		$options = $metabox['args']['options'];	 	
		$tip1 = self::$tooltips->tip('before_archive');		
		$tip2 = self::$tooltips->tip('after_archive');
		$tip3 = self::$tooltips->tip('before_entry_content');		
		$tip4 = self::$tooltips->tip('after_entry_content');	
		$before_archive = $options['before_archive'] ? 'checked="checked"' : '';	
		$after_archive = $options['after_archive'] ? 'checked="checked"' : '';	
		$before_entry_content = $options['before_entry_content'] ? 'checked="checked"' : '';	
		$after_entry_content = $options['after_entry_content'] ? 'checked="checked"' : '';	
		print <<< EXTRAS_PANEL
<label>{$tip1}</label><input type="checkbox" name="before_archive" {$before_archive} value="1" /><br/>
<label>{$tip2}</label><input type="checkbox" name="after_archive" {$after_archive} value="1" /><br/>
<label>{$tip3}</label><input type="checkbox" name="before_entry_content" {$before_entry_content} value="1" /><br/>
<label>{$tip4}</label><input type="checkbox" name="after_entry_content" {$after_entry_content} value="1" /><br/>
EXTRAS_PANEL;
	}	

	public static function meta_panel($post,$metabox){	
		$options = $metabox['args']['options'];	 	
		$tip1 = self::$tooltips->tip('postinfo_shortcodes');		
		$tip2 = self::$tooltips->tip('postmeta_shortcodes');		
		print <<< LABELLING_PANEL
<label>{$tip1}</label><input type="text" name="postinfo_shortcodes" size="60" value="{$options['postinfo_shortcodes']}" /><br/>
<label>{$tip2}</label><input type="text" name="postmeta_shortcodes" size="60" value="{$options['postmeta_shortcodes']}" /><br/>
LABELLING_PANEL;
	}

	public static function labelling_panel($post,$metabox){	
		$options = $metabox['args']['options'];	 	
		$tip1 = self::$tooltips->tip('read_more_text');		
		$tip2 = self::$tooltips->tip('comment_invitation');		
		$tip3 = self::$tooltips->tip('breadcrumb_prefix');	
		$tip4 = self::$tooltips->tip('breadcrumb_archive');	
		print <<< LABELLING_PANEL
<label>{$tip1}</label><input type="text" name="read_more_text" size="40" value="{$options['read_more_text']}" /><br/>
<label>{$tip2}</label><input type="text" name="comment_invitation" size="40" value="{$options['comment_invitation']}" /><br/>
<label>{$tip3}</label><input type="text" name="breadcrumb_prefix" size="40" value="{$options['breadcrumb_prefix']}" /><br/>
<label>{$tip4}</label><input type="text" name="breadcrumb_archive" size="40" value="{$options['breadcrumb_archive']}" /><br/>
LABELLING_PANEL;
	}	

	public static function responsive_menu_panel($post,$metabox){	
		$options = $metabox['args']['options'];	 	
		$tip1 = self::$tooltips->tip('responsive_menu_threshold');		
		$tip2 = self::$tooltips->tip('responsive_menu_icon_color');
		print <<< RESPONSIVE_PANEL
<label>{$tip1}</label><input type="text" name="responsive_menu_threshold" size="4" value="{$options['responsive_menu_threshold']}" /> px<br/>
<label>{$tip2}</label><input type="text" name="responsive_menu_icon_color" size="7" value="{$options['responsive_menu_icon_color']}" /><br/>
RESPONSIVE_PANEL;
	}	

	public static function settings_panel() {
 		$this_url = $_SERVER['REQUEST_URI'];
		$keys = implode(',',self::get_keys());
		$title = sprintf('<h2>%1$s</h2>', __('Display Settings', self::DOMAIN));		
?>
<div class="wrap">
    <?php screen_icon(); echo $title; ?>
    <div id="poststuff" class="metabox-holder">
        <div id="post-body">
            <div id="post-body-content">
			<form id="display_options" method="post" action="<?php echo $this_url; ?>">
			<?php do_meta_boxes(self::get_screen_id(), 'normal', null); ?>
			<p class="submit">
			<input type="submit"  class="button-primary" name="options_update" value="Save Changes" />
			<input type="hidden" name="page_options" value="<?php echo $keys; ?>" />
			<?php wp_nonce_field(self::CLASSNAME); ?>
			<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
			<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
			</p>
			</form>
 			</div>
        </div>
        <br class="clear"/>
    </div>
</div>
<?php
	}    
}
?>