<?php
if (!class_exists('Genesis_Club_Dashboard')) {
  class Genesis_Club_Dashboard extends Genesis_Club_Admin {
    
	protected $code = 'genesis-club';

	function init() {
		add_action('admin_menu',array($this, 'admin_menu'));
	}

	function admin_menu() {
		$this->screen_id = add_menu_page(GENESIS_CLUB_FRIENDLY_NAME, GENESIS_CLUB_FRIENDLY_NAME, 'manage_options', 
			GENESIS_CLUB_PLUGIN_NAME, array($this,'page_content'), GENESIS_CLUB_ICON );
		$intro = sprintf('Dashboard (v%1$s)', GENESIS_CLUB_VERSION);				
		add_submenu_page(GENESIS_CLUB_PLUGIN_NAME, GENESIS_CLUB_FRIENDLY_NAME, $intro, 'manage_options', GENESIS_CLUB_PLUGIN_NAME,array($this,'page_content') );
		add_action('admin_enqueue_scripts', array($this, 'register_admin_styles'));
		add_action('admin_enqueue_scripts', array($this, 'register_tooltip_styles'));
 		add_action('load-'.$this->get_screen_id(), array($this, 'load_page')); 		
		add_action('load-widgets.php', array( $this, 'add_tooltip_support'));
 		add_action('load-edit.php', array( $this, 'add_tooltip_support'));
 		add_action('load-post.php', array( $this, 'add_tooltip_support'));
 		add_action('load-post-new.php', array( $this, 'add_tooltip_support'));
	}

	function page_content() {
 		$title = $this->admin_heading('Genesis Club Dashboard',GENESIS_CLUB_ICON);				
		$this->print_admin_form_with_sidebar($title, __CLASS__); 
	} 

	function load_page() {
 		if (isset($_POST['options_update'])) $this->save_dashboard();
		$callback_params = array ( 'message' => $this->fetch_message());
		$this->add_meta_box('modules','Genesis Club Modules', 'modules_panel', $callback_params);
		$this->add_meta_box('news', 'Genesis Club News', 'news_panel', null, 'side');
		add_action ('admin_enqueue_scripts',array($this, 'enqueue_admin_styles'));		
		add_action ('admin_enqueue_scripts',array($this, 'enqueue_dashboard_styles'));		
  		add_action ('admin_enqueue_scripts',array($this, 'enqueue_scripts'));		
		add_action ('admin_enqueue_scripts',array($this, 'enqueue_postbox_scripts'));
	}

	function save_dashboard() {
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

	function enqueue_dashboard_styles() {
		wp_enqueue_style($this->get_code('dashboard'), plugins_url('styles/dashboard.css',dirname(__FILE__)), array(), $this->get_version());
 	}	
 	
	function enqueue_scripts() {
		wp_enqueue_script('mixitup', plugins_url('scripts/jquery.mixitup.min.js',dirname(__FILE__)), array( 'jquery' ), $this->get_version() );
		add_action('admin_footer-'.$this->get_screen_id(), array($this, 'show_modules'));
 	}

	private function checkbox_helper($module, $checked, $disabled = false) {
		return sprintf ('<input type="checkbox" id="cb-select-%1$s" name="checked_modules[]" %2$svalue="%1$s" %3$s/>',
			$module, $checked ? 'checked="checked" ' : '', $disabled ? 'disabled="disabled" ' : '');			
	}

	function modules_panel($post, $metabox) {
		print $metabox['args']['message'];	
  		$pro = sprintf('<a target="_blank" rel="external" href="%1$s">Genesis Club Pro</a>', GENESIS_CLUB_PRO_URL); 
      print $this->submit_button();
		print <<< SETTINGS_PANEL
<div class="actions"><input id="cb-select-all" type="checkbox" />Select/Deselect All or individually select the Genesis Club modules you need.  Or click the link to find out more about {$pro} features.</div>
<ul class="products_grid" class="wrap">
SETTINGS_PANEL;
		$modules = Genesis_Club_Plugin::get_modules();
		foreach ( $modules as $module => $info ) {
			$present = Genesis_Club_Plugin::module_exists($module);
			$enabled = $present && Genesis_Club_Plugin::is_module_enabled($module);
			$verbose_status = $present ? ($enabled ? '' :  __('Inactive', GENESIS_CLUB_DOMAIN)) :  __('Pro', GENESIS_CLUB_DOMAIN);
			printf ('<li class="mix product-card"><div class="status-action clear"><span class="status">%1$s</span>%2$s</div><h2>%3$s</h2><div class="summary">%4$s</div></li>',
				$verbose_status, $this->checkbox_helper($module, $enabled, ! $present), $info['heading'], $info['tip']);
		}
		print '</ul>';
	}

    function show_modules() {
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
	
  }
}
