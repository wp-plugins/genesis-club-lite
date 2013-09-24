<?php
class GenesisClubPostAdmin {
    const CLASSNAME = 'GenesisClubPostAdmin'; //this class
    const CODE = 'genesis-club'; //prefix ID of CSS elements
    const DOMAIN = 'GenesisClub'; //text domain for translation
	const SLUG = 'page-control';
    const FIELDNAME = 'not_on_404';

    private static $parenthook;
    private static $slug;
    private static $screen_id;

	static function init() {		
		if ( ! GenesisClubOptions::get_option('page_control_disabled')) {
			self::$parenthook = GENESIS_CLUB_PLUGIN_NAME;
		    self::$slug = self::$parenthook . '-' . self::SLUG;
		    self::$screen_id = self::$parenthook.'_page_' . self::$slug;
			add_action('do_meta_boxes', array( self::CLASSNAME, 'do_meta_boxes'), 30, 2 );
			add_action('save_post', array( self::CLASSNAME, 'save_postmeta'));
			add_action('admin_menu',array(self::CLASSNAME, 'admin_menu'));
		}
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

	static function admin_menu() {
		add_submenu_page(self::get_parenthook(), __('Page Control'), __('Page Control'), 'manage_options', 
			self::get_slug(), array(self::CLASSNAME,'settings_panel'));
 		add_action('load-'.self::get_screen_id(), array(self::CLASSNAME, 'load_page')); 		
	}
	
	public static function load_page() {
 		add_action ('admin_enqueue_scripts',array(self::CLASSNAME, 'enqueue_styles'));		
	}

	public static function enqueue_styles() {
		wp_enqueue_style(self::CODE.'-admin', plugins_url('styles/admin.css',dirname(__FILE__)), array(),GENESIS_CLUB_VERSION);
 	}	

	static function save_postmeta($post_id) {
		if (array_key_exists('genesis_club_toggle_signature', $_POST)) {
			$key = 'show'==$_POST['genesis_club_toggle_signature'] ? GenesisClubProfile::SHOW_SIGNATURE_METAKEY : GenesisClubProfile::HIDE_SIGNATURE_METAKEY;	
			$val = array_key_exists($key, $_POST) ? $_POST[$key] : false;
			update_post_meta( $post_id, $key, $val );
		}
		if (array_key_exists('page_visibility_toggle', $_POST)) {
			$meta_key = GenesisClubDisplay::get_page_hider_metakey();
			$fldname = self::FIELDNAME;
			$val = array_key_exists($fldname, $_POST) ? $_POST[$fldname] : false;
			update_post_meta( $post_id, $meta_key, $val );
		}		
	}

	static function do_meta_boxes( $post_type, $context) {
		$post_types=get_post_types();
		if ( in_array($post_type, $post_types ) && ('advanced' === $context )) {
			add_meta_box( 'genesis-club-signature-visibility', 'Author Signature Settings', array( self::CLASSNAME, 'signature_visibility_panel' ), $page, 'advanced', 'low' );
			add_meta_box( 'genesis-club-sitemap-visibility', '404 Sitemap Visibility', array( self::CLASSNAME, 'sitemap_visibility_panel' ), $page, 'advanced', 'low' );
			$current_screen = get_current_screen();
			if (method_exists($current_screen,'add_help_tab'))
	    		$current_screen->add_help_tab( array(
			        'id'	=> 'genesis_club_help_tab',
    			    'title'	=> __('Genesis Club'),
        			'content'	=> __('
<p>In the <b>Author Signature Settings</b> section below you can choose whether or not to show an author signature for this post.</p>
<p>In the <b>404 Sitemap Visibility</b> section below you can choose not to show this page on the Genesis 404 page sitemap.</p>')) );
		}
	}

	static function sitemap_visibility_panel($post,$metabox) {
		global $post;
		$meta_key = GenesisClubDisplay::get_page_hider_metakey();
		$key = self::FIELDNAME;
		$hide = get_post_meta($post->ID, $meta_key, true);
		$checked = $hide ? ' checked="checked"' : '' ;		
		$label = __('Do not show this page on the 404 page sitemap');
		print <<< PAGE_VISIBILITY
<p class="meta-options"><input type="hidden" name="page_visibility_toggle" value="1" />
<label><input class="valinp" type="checkbox" name="{$key}" id="{$key}" {$checked} value="1" />&nbsp;{$label}</label></p>
PAGE_VISIBILITY;
    }
		
	static function signature_visibility_panel() {
		global $post;
		$hide = ('post'==$post->post_type) && GenesisClubProfile::signature_on_posts($post->post_author) ;
		$key = $hide ? GenesisClubProfile::HIDE_SIGNATURE_METAKEY : GenesisClubProfile::SHOW_SIGNATURE_METAKEY;
		$toggle = get_post_meta($post->ID, $key, true);
		$signature_toggle = $toggle?' checked="checked"':'';		
		$action = $hide ? 'hide' : 'show'; 
		$label =  __($hide ? 'Do not show the author signature on this page' : 'Show the author signature on this page');
		print <<< SIGNATURE_VISIBILITY
<p id="genesis-club-signature-control" class="meta-options"><input type="hidden" name="genesis_club_toggle_signature" value="{$action}" />
<label><input class="valinp" type="checkbox" name="{$key}" id="{$key}" {$signature_toggle} value="1" />&nbsp;{$label}</label></p>
SIGNATURE_VISIBILITY;
    }
	
	static function settings_panel() {
 		$this_url = $_SERVER['REQUEST_URI'];
		$title = sprintf('<h2>%1$s</h2>', __('Page Control Settings', self::DOMAIN));		
		$screenshot = plugins_url('images/post-admin.jpg',dirname(__FILE__));
		$url = admin_url('edit.php');
?>
<div class="wrap">
<?php screen_icon(); echo $title; ?>
<div id="poststuff" class="metabox-holder"><div id="post-body"><div id="post-body-content">
<p class="notice">There are no settings on this page.</p>
<p class="notice">However, links are provided to where you set up post or page level controls for signatures and the site map.</p>

<p class="important">Go to the <a href="<?php echo $url;?>">Post Editor</a> or the <a href="<?php echo $url;?>?post_type=page">Page Editor</a>, choose the post or page you want to control
and then scroll down and edit the relevant section.</p>

<h3>Help On Page Level Controls</h3>
<p>Below is a annotated screenshot of the two sections; <i>Author Signature Settings</i> and <i>404 Sitemap Visibility</i>.</p>

<p>As for the Author Signature, you can switch it on or off for an individual post (or page ) to override the default setting
that is set by the author of the post on their profile page.</p>

<p>For the 404 page sitemap section then the option is always to exclude the post (or page) from the sitemap 
since the default setting is always to include it on the sitemap.</p>

<p><img src="<?php echo $screenshot;?>" alt="Screenshot on extra sections on Page Editor" /></p>
<p>If the sections do not appear in the Post Editor then click the <i>Screen Options</i> at the top of the page and make 
sure you click the checkboxes to show these options.</p>
<form id="misc_options" method="post" action="<?php echo $this_url; ?>">
<p>
<?php wp_nonce_field(self::CLASSNAME); ?>
<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
</p>
</form>
</div></div><br class="clear"/></div></div>
<?php
	}   
}
?>