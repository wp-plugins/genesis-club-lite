<?php
class Genesis_Club_Signature_Admin extends Genesis_Club_Admin {
    const TOGGLE_SIGNATURE = 'genesis_club_toggle_signature';

	function init() {		
		$this->slug = 'user';
		add_action('load-profile.php', array($this, 'load_profile'));	
		add_action('load-user-edit.php', array($this, 'load_profile'));	
		add_action('personal_options_update', array($this, 'save_profile'));		
		add_action('edit_user_profile_update', array($this, 'save_user'));
		add_action('genesis_club_hiding_settings_show', array($this, 'page_visibility_show'), 20, 1);
		add_action('genesis_club_hiding_settings_save', array($this, 'page_visibility_save'), 20, 1);
		add_action('admin_menu',array($this, 'admin_menu'));
	}
	
	function admin_menu() {
		$this->screen_id = add_submenu_page($this->get_parent_slug(), __('Genesis Club Signatures'), __('Signatures'), 'manage_options', 
			$this->get_slug(), array($this,'page_content'));
 		add_action('load-'.$this->get_screen_id(), array($this, 'load_page')); 		
	}
	
	function page_content() {
 		$title =  $this->admin_heading('Signatures', GENESIS_CLUB_ICON);				
		$this->print_admin_form_start($title); 
		do_meta_boxes($this->get_screen_id(), 'normal', null); 
		$this->print_admin_form_end(__CLASS__);
	} 	
	
	function load_page() {
		$this->add_meta_box('intro', __('Instructions',GENESIS_CLUB_DOMAIN),  'intro_panel');
		$this->add_meta_box('help', __('Help Creating A Signature',GENESIS_CLUB_DOMAIN), 'help_panel');
		$this->add_meta_box('example', __('Example Signature',GENESIS_CLUB_DOMAIN), 'example_panel');
		$this->add_tooltip_support();
		add_action ('admin_enqueue_scripts',array($this, 'enqueue_admin_styles'));
		add_action ('admin_enqueue_scripts',array($this, 'enqueue_postbox_scripts'));
	}
 		
	function get_user() {
		global $user_id;
		wp_reset_vars(array('user_id'));  //get ID of user being edited if not editing own profile 
		return (isset($user_id) && ($user_id > 0)) ? new WP_User((int) $user_id) : wp_get_current_user();	        
	}

	function add_form_type () {
		echo ' enctype="multipart/form-data"';
	}

	function load_profile() {
		add_action('user_edit_form_tag', array($this,'add_form_type'));
		$profile = $this->get_user();
		if (!$this->is_profile() || current_user_can('manage_options')) {
			add_action($this->is_profile() ? 'show_user_profile' :'edit_user_profile', array($this,'show_authors_panel'),12,2);
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
    
	function is_profile() {
		return defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE; 
	}
	
	function save_profile($user_id) {
		if ($this->is_profile()) $this->save_signature($user_id);
	}

	function save_user($user_id) {
		if ( ! $this->is_profile()) $this->save_signature($user_id);
	}

	function save_signature($user_id) {
		$key1 = Genesis_Club_Signature::SIGNATURE_URL_KEY;
  		foreach ($_FILES as $gcsig) {
      		$file = wp_handle_upload($gcsig, array( 'test_form' => false ));
       		if (isset($file['url'])) $_POST[$key1] = $file['url'];
      	}
		$old_val = get_user_option($key1, $user_id);		
		$new_val = $_POST[$key1];
		if ($old_val != $new_val) update_usermeta( $user_id, $key1, $new_val);		
		
		$key2 = Genesis_Club_Signature::SIGNATURE_ON_POSTS_KEY;
		$old_val =  get_user_option($key2, $user_id);		
		$new_val = array_key_exists($key2,$_POST) ? $_POST[$key2] : '';
		if ($old_val != $new_val) update_usermeta( $user_id, $key2, $new_val );			
	}	

	function page_visibility_save($post_id) {
		$post_type = get_post_type( $post_id);
		$post_author = get_post_field( 'post_author', $post_id);	
		$key = $this->TOGGLE_SIGNATURE;
		$meta_key = Genesis_Club_Signature::get_toggle_meta_key($post_type, $post_author);	
		update_post_meta( $post_id, $meta_key, array_key_exists($key, $_POST) ? $_POST[$key] : false);
	}

	function page_visibility_show($post) {
		$meta_key = Genesis_Club_Signature::get_toggle_meta_key($post->post_type, $post->post_author);
		echo $this->form_field($this->TOGGLE_SIGNATURE, $this->TOGGLE_SIGNATURE, 
			__(strpos($meta_key, 'hide') !== FALSE ? 'Do not show the author signature on this page' : 'Show the author signature on this page'), 
			get_post_meta($post->ID, $meta_key, true),  'checkbox', array(), array(), 'br') ;
    }

	function show_authors_panel($user) {
		$key1 = Genesis_Club_Signature::SIGNATURE_URL_KEY;
		$key2 = Genesis_Club_Signature::SIGNATURE_ON_POSTS_KEY;			
		$sig_url = get_user_option($key1, $user->ID);
		$show_sig = get_user_option($key2, $user->ID)   ? 'checked="checked"' : '';
		$sig_img = empty($sig_url) ? '' : sprintf('<img alt="Author Signature" src="%1$s" /><br/>',$sig_url);		
		print <<< SIGNATURE_PANEL
<h3 id="genesis-club-signature">Signature Settings</h3>
<table class="form-table">
<tr>
	<th><label for="gcsig">Author Signature</label></th>
	<td>{$sig_img}
	<input type="text" id="{$key1}" name="{$key1}" size="80" value="{$sig_url}" /><br/>
	<input id="gcsig" name="gcsig" type="file" size="80" accept="image/*" value="{$sig_url}" /><br/>
	<span class="description">Enter the signature URL or upload a new image file of your signature with approximate dimensions of say, 400px by 200px.</span></td>
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
	
	function intro_panel() {
		$url = admin_url('profile.php#genesis-club-signature');
		print <<< INTRO
<p class="attention">There are no settings on this page.</p>
<p class="attention">However, a link is provided to where you need to go to add a signature, 
as well as some useful tips.</p>
<p class="bigger">To create or edit your signature go to your <a href="{$url}">Profile page</a> and upload a signature.</p>
INTRO;
	}

	function help_panel() {
		$screenshot = plugins_url('images/signature-settings.jpg',dirname(__FILE__));
		print <<< HELP
<p>On the <em>User Profile page</em> look for the section called <em>Signature Settings</em>:</p>
<p><img class="dashed-border" alt="User Profile signature settings" src="{$screenshot}"></p>
<p>Every author on your site can upload their own signature of whatever size they prefer.</p>
<p>And, of course, for reasons of security, rather than use your own real signature I suggest you create a stylized one at <a href="http://www.mylivesignature.com/">My Live Signature</a>.</p>
<p>HINT: Save your signature in the PNG format so it has a transparent background and hence looks fine on any of your sites.</p>
<p>You can find more detailed instructions on creating and using author signatures <a href="http://www.diywebmastery.com/3098/how-to-add-an-author-signature-at-the-foot-of-a-post" target="_blank">here</a>.</p>
HELP;
	}	

	function example_panel() {
		$usersig = Genesis_Club_Signature::get_author_signature(get_current_user_id());
		$sig =  sprintf('<p><img src="%1$s" alt="Author Signature"/></p>', 
			!empty($usersig) ? $usersig :  plugins_url('images/signature-example.png',dirname(__FILE__)));		
		print $sig;
	}		
	
}
