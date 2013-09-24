<?php
class GenesisClubProfileAdmin {
    const CLASSNAME = 'GenesisClubProfileAdmin'; //this class
    const CODE = 'genesis-club'; //prefix ID of CSS elements
    const DOMAIN = 'GenesisClub'; //text domain for translation
	const SLUG = 'user';

    private static $parenthook;
    private static $slug;
    private static $screen_id;
	
	public static function init() {		
		if ( ! GenesisClubOptions::get_option('profile_disabled')) {
			self::$parenthook = GENESIS_CLUB_PLUGIN_NAME;
		    self::$slug = self::$parenthook . '-' . self::SLUG;
		    self::$screen_id = self::$parenthook.'_page_' . self::$slug;
			add_action('load-profile.php', array(self::CLASSNAME, 'load_profile'));	
			add_action('load-user-edit.php', array(self::CLASSNAME, 'load_profile'));	
			add_action('personal_options_update', array(self::CLASSNAME, 'save_profile'));		
			add_action('edit_user_profile_update', array(self::CLASSNAME, 'save_user'));
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
		add_submenu_page(self::get_parenthook(), __('Profile'), __('Profile'), 'manage_options', 
			self::get_slug(), array(self::CLASSNAME,'settings_panel'));
 		add_action('load-'.self::get_screen_id(), array(self::CLASSNAME, 'load_page')); 		
	}
	
	public static function load_page() {
 		add_action ('admin_enqueue_scripts',array(self::CLASSNAME, 'enqueue_styles'));		
	}

	public static function enqueue_styles() {
		wp_enqueue_style(self::CODE.'-admin', plugins_url('styles/admin.css',dirname(__FILE__)), array(),GENESIS_CLUB_VERSION);
 	}	
 	
	static function get_user() {
		global $user_id;
		wp_reset_vars(array('user_id'));  //get ID of user being edited if not editing own profile 
		return (isset($user_id) && ($user_id > 0)) ? new WP_User((int) $user_id) : wp_get_current_user();	        
	}

	static function add_form_type () {
		echo ' enctype="multipart/form-data"';
	}

	static function load_profile() {
		add_action('user_edit_form_tag', array(self::CLASSNAME,'add_form_type'));
		
		$profile = self::get_user();
		if (!self::is_profile() || current_user_can('manage_options')) {
			add_action(self::is_profile() ? 'show_user_profile' :'edit_user_profile', array(self::CLASSNAME,'show_authors_panel'),12,2);
			$current_screen = get_current_screen();
			if (method_exists($current_screen,'add_help_tab')) 
    		   $current_screen->add_help_tab( array(
        		'id'	=> 'genesis_club_instructions_tab',
        		'title'	=> __('Genesis Club Instructions'),
        		'content'	=> '<h3>Genesis Club Instructions For Authors</h3>
<ol>
<li>Upload your signature file below. The image size should typically be about 500 by 200px.</li>
<li>You can choose whether or not you want it to appear at the foot of each post by default: Signatures on Posts is ON</li>
<li>If you choose to have signatures on, then you can disable the signature on individual posts using a checkbox in the post editor</li>
<li>If you choose to have signatures off, then you can enable the signature on individual posts using a checkbox in the post editor</li>
<li>You can also use the short code [gc-signature] if you want to add a signature at a particular location on the page - for example if you want to add a PS after the signature.</li>
</ol>') );
		}
	}    
    
	static function is_profile() {
		return defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE; 
	}
	
	static function save_profile($user_id) {
		if (self::is_profile()) self::save($user_id);
	}

	static function save_user($user_id) {
		if ( ! self::is_profile()) self::save($user_id);
	}

	static function save($user_id) {
		$key1 = GenesisClubProfile::SIGNATURE_URL_KEY;
		$old_val = get_user_option($key1, $user_id);		
		$new_val = $old_val;
  		foreach ($_FILES as $gcsig) {
      		$file = wp_handle_upload($gcsig, array( 'test_form' => false ));
      		if (isset($file['url'])) $new_val = $file['url'];
      	}
		if ($old_val != $new_val) update_usermeta( $user_id, $key1, $new_val);		
		
		$key2 = GenesisClubProfile::SIGNATURE_ON_POSTS_KEY;
		$old_val =  get_user_option($key2, $user_id);		
		$new_val = array_key_exists($key2,$_POST) ? $_POST[$key2] : '';
		if ($old_val != $new_val) update_usermeta( $user_id, $key2, $new_val );			
	}	

	static function show_authors_panel($user) {
		$key1 = GenesisClubProfile::SIGNATURE_URL_KEY;
		$key2 = GenesisClubProfile::SIGNATURE_ON_POSTS_KEY;			
		$sig_url = get_user_option($key1, $user->ID);
		$show_sig = get_user_option($key2, $user->ID)   ? 'checked="checked"' : '';
		$sig_img = empty($sig_url) ? '' : sprintf('<img alt="Author Signature" src="%1$s" />',$sig_url);		
		print <<< SIGNATURE_PANEL
<h3 id="genesis-club-signature">Signature Settings</h3>
<table class="form-table">
<tr>
	<th><label for="gcsig">Author Signature</label></th>
	<td>{$sig_img}<br/><input id="gcsig" name="gcsig" type="file" size="80" accept="image/*" value="{$sig_url}" /><br/>
	<span class="description">Upload an image file of your signature with approximate dimensions of say, 400px by 200px.</span></td>
</tr>
<tr>
	<th><label for="{$key2}">Show Signature On Posts</label></th>
	<td><input id="{$key2}" name="{$key2}" type="checkbox" class="valinp" {$show_sig} value="1" /><br/>
	<span class="description">Check this box to have your signature appear at the foot of all posts by default.
			You can override this setting on a post by post basis.</span></td>
</tr>
</table>
SIGNATURE_PANEL;
    }    
	
	static function settings_panel() {
 		$this_url = $_SERVER['REQUEST_URI'];
		$url = admin_url('profile.php#genesis-club-signature');
		$title = sprintf('<h2>%1$s</h2>', __('User Profile', self::DOMAIN));		
		$screenshot = plugins_url('images/profile-admin.jpg',dirname(__FILE__));
		$usersig = GenesisClubProfile::get_author_signature(get_current_user_id());
		$sig = !empty($usersig) ? sprintf('<p><img src="%1$s" alt="Author Signature"/></p>',$usersig) : 
			plugins_url('images/sample-signature.png',dirname(__FILE__));		
?>
<div class="wrap">
<?php screen_icon(); echo $title; ?>
<div id="poststuff" class="metabox-holder"><div id="post-body"><div id="post-body-content">
<p class="notice">There are no settings on this page.</p>
<p class="notice">However, a link is provided to where you need to go to add a signature, 
as well as some useful tips.</p>
<p class="important">To create or edit your signature go to your <a href="<?php echo $url; ?>">Profile page</a> and upload a signature.</p>
<h3>Help On Adding A Signature</h3>
<p>Look for the section called <i>Signature Settings</i>:</p>
<p><img class="dashed-border" alt="User Profile signature settings" src="<?php echo $screenshot; ?>"></p>
<p>Every author on your site can upload their own signature of whatever size they prefer.</p>
<p>And, of course, for reasons of security, rather than use your own real signature I suggest you create 
a stylized one at <a href="http://www.mylivesignature.com/">My Live Signature</a>.
<P>HINT: Save your signature in the PNG format so it has a transparent background and hence looks fine on any of your sites.</p>
<p>You can find more detailed instructions on creating and using author signatures 
<a href="http://www.diywebmastery.com/3098/how-to-add-an-author-signature-at-the-foot-of-a-post" target="_blank">here</a>.</p>
<h3>Example Signature</h3>
<?php echo $sig;?>
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
