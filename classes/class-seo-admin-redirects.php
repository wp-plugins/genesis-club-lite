<?php
class Genesis_Club_Redirects_Admin extends Genesis_Club_Seo_Admin {
   const CODE = 'genesis-club-redirects'; //prefix ID of CSS elements

   const YOAST_REDIRECT_METAKEY = '_yoast_wpseo_redirect';

	private $redirect_tips = array(
			'redirect_url' => array('heading' => 'Redirect URL', 'tip' => 'Specify the full URL where you this page to be redirected'),
			'redirect_status' => array('heading' => 'Redirect Status', 'tip' => 'Choose the redirect status'),
	);
	
	private $tips = array(
		'copy_seo_redirects' => array('heading' => 'Synchronize SEO Redirects', 'tip' => 'Click the button to synchronize Redirects between Yoast SEO and Genesis Club.')
	);

	function init() {
		add_action('do_meta_boxes', array( $this, 'do_meta_boxes'), 20, 2 );
		add_action('save_post', array( $this, 'save_postmeta'));
	}

	function save_postmeta($post_id) {
		$keys = array( 'redirect' => Genesis_Club_Seo::REDIRECT_METAKEY);
		$defaults =  array( 'redirect' =>  Genesis_Club_Seo::redirect_defaults());		
		foreach ($keys as $key => $metakey)  
			if (array_key_exists('genesis_club_'.$key, $_POST)) {
			   if (is_array($_POST[$metakey])) {
			      foreach ($_POST[$metakey] as $k => $v) $_POST[$metakey][$k] = stripslashes(trim($v));
				   $val = array_key_exists($metakey, $_POST) ? Genesis_Club_Options::validate_options($defaults[$key], $_POST[$metakey] ) : false;
            } else {
               $val = stripslashes(trim($_POST[$metakey]));  
            }
				update_post_meta( $post_id, $metakey, $val );				               
			}	
	}


	function do_meta_boxes( $post_type, $context) {
		$post_types=get_post_types();
		if ( in_array($post_type, $post_types ) && ('advanced' === $context )) {
         add_filter( 'genesis_club_post_settings', array($this, 'add_post_panel'), 10, 2);	//add to plugin metabox		    	
		}
	}

	function add_post_panel($content, $post) {
		return $content + array ( 'Redirect' => $this->redirect_panel($post)) ;
	}	 
 
	function redirect_panel($post) {
		$form_data = $this->get_meta_form_data(Genesis_Club_Seo::REDIRECT_METAKEY, 'redirect_', Genesis_Club_Seo::redirect_defaults());
		$this->set_tooltips($this->redirect_tips);
		return sprintf ('<div class="diy-wrap">%1$s%2$s<p class="meta-options"><input type="hidden" name="genesis_club_redirect" value="1" /></p></div>',
			$this->meta_form_field($form_data, 'url', 'text', array(), array('size' => 50)),
			$this->meta_form_field($form_data, 'status', 'radio', Genesis_Club_Seo::redirect_options()));      
    }

	public function load_page() {
 		$message = isset($_POST['options_update']) ? $this->copy_meta() : ''; 
		if (isset($_REQUEST['redirects']) && isset($_REQUEST['act']) && ($_REQUEST['act'] == 'delete'))
         $message = $this->delete_redirects($_REQUEST['redirects']);
		$callback_params = array ('message' => $message);
		$this->add_meta_box('redirects-intro','Introduction', 'intro_panel', $callback_params);
		$this->add_meta_box('redirects-copy','SEO Redirects', 'copy_panel', $callback_params);
		if (isset($_REQUEST['redirects']) && isset($_REQUEST['act']) && ($_REQUEST['act'] == 'export'))
         $this->add_meta_box('redirects-export','Export Redirects', 'export_panel', $callback_params);
		
		$this->add_meta_box('news', 'Genesis Club News', 'news_panel',$callback_params, 'advanced');	
		$this->set_tooltips($this->tips);
	}

	private function copy_meta($reverse = false) {
		$yoast = $this->get_yoast_redirects();
		$gc = $this->get_gc_redirects();
		return $this->copy_post_meta($yoast, $gc, $reverse) ;
	}	

   private function get_yoast_redirects() {
         global $wpdb;
         $redirects = array();
			$select = sprintf('SELECT id, post_name, meta_value FROM %1$spostmeta pm, %1$sposts p WHERE p.id = pm.post_id AND meta_key = \'%2$s\' AND meta_value != \'\';',
				$wpdb->prefix, self::YOAST_REDIRECT_METAKEY); 
			$results = $wpdb->get_results($select);
			foreach ( $results as $result ) {
               $redirects['p'.$result->id] = array('post_id' => $result->id, 'slug' => $result->post_name, 'urly' => $result->meta_value, 'status' => 301); 
			}
         return $redirects;
   }

   private function get_gc_redirects() {
         global $wpdb;
         $redirects = array();
			$select = sprintf('SELECT id, post_name, meta_value FROM %1$spostmeta pm, %1$sposts p WHERE p.id = pm.post_id AND meta_key = \'%2$s\' AND meta_value != \'\';',
				$wpdb->prefix, Genesis_Club_Seo::REDIRECT_METAKEY); 
			$results = $wpdb->get_results($select);
			foreach ( $results as $result ) {
			   if (($redirect = maybe_unserialize($result->meta_value)) 
			   && is_array($redirect)
			   && array_key_exists('url', $redirect)
			   && $redirect['url'])
               $redirects['p'.$result->id] = array('post_id' => $result->id,'slug' => $result->post_name, 'url' => $redirect['url'], 'status' => isset($redirect['status']) ? $redirect['status']: 301); 
			}	
         return $redirects;
   }

   private function delete_redirects($target) {
         global $wpdb;
         $table = sprintf('%1$spostmeta', $wpdb->prefix);
			$deletions = $wpdb->delete($table, array('meta_key' => $target == 'gc' ? Genesis_Club_Seo::REDIRECT_METAKEY : self::YOAST_REDIRECT_METAKEY));
         return $deletions ? sprintf('<div class="updated"><p>%1$s %2$s</p></div>', $deletions, __('redirects have been deleted',GENESIS_CLUB_DOMAIN)):'';
   }

	private function copy_post_meta($yoast, $gc, $reverse = false) {
		$updates = 0;	
      if ($reverse) {
         foreach ($gc as $redirect) {
            if (update_post_meta( $redirect['post_id'], self::YOAST_REDIRECT_METAKEY, $redirect['url'] )) $updates++;	            
         }
      } else {
         foreach ($yoast as $redirect) {
            if (update_post_meta( $redirect['post_id'], Genesis_Club_Seo::REDIRECT_METAKEY, array('url' => $redirect['urly'], 'status' => 301) ) ) $updates++;	            
         }         
      }
      return sprintf('%1$s %2$s', $updates, __('redirects updated', GENESIS_CLUB_DOMAIN));  
	}

	private function get_button_label($reverse = false) {
		return __($reverse ? 'Copy Redirects From Genesis Club to Yoast' : 'Copy Redirects From Yoast to Genesis Club' , GENESIS_CLUB_DOMAIN);	
	}

 	public function intro_panel($post,$metabox){		
		print <<< INTRO_PANEL
<p>The 301 redirect facility was partially removed from the free version of the Yoast WordPress SEO plugin in v2.3 for reasons of performance and visibility. Redirects still take place for existing posts and pages, however you cannot add new redirects.</p>
<p>The feature below allows you to migrate any page 301 redirects from Yoast into this plugin. You can also export the redirects in a format suitable for inclusion in a <i>.htaccess</i> file and then delete them from the Yoast WordPress SEO configuration. </p>
<ol>
<li>Either COPY your 301 redirects from Yoast into this plugin or EXPORT them and add them to your .htaccess file </li>
<li>Delete the 301 redirects from the Yoast configuration</li>
</ol>
INTRO_PANEL;
	}

	public function copy_panel() {
	   $this_url = $_SERVER['REQUEST_URI'];
		$reverse = isset($_REQUEST['reverse']);	
		$yoast = $this->get_yoast_redirects();
		$gc = $this->get_gc_redirects();
		$all = array_replace_recursive($yoast, $gc);
      if (count($all) > 0){
         $matches = $copy = 0;
         print('<table class="seo-tags" summary="List of Yoast and Genesis Club Redirects"><thead><tr><th>Post URL</th><th>Yoast Redirect</th><th>Genesis Club Redirect</th><th>Match</th></tr></thead><tbody>');
         foreach ($all as $redirect) {
            $urly = isset($redirect['urly']) ? $redirect['urly']: '';
            $url = isset($redirect['url']) ? $redirect['url'] : '';
            $status = ($url && isset($redirect['status'])) ? (' ('.$redirect['status'].')') : '';
            $match = $urly==$url ? 'yes' : 'no';
            if ($match=='yes') 
               $matches++;
            elseif (($reverse && !empty($url)) || (!$reverse && !empty($urly)))
               $copy++;
            
            printf('<tr><td>/%1$s</td><td>%2$s</td><td>%3$s%4$s</td><td><span class="dashicons dashicons-%5$s"></span></td></tr>', $redirect['slug'], $urly , $url, $status, $match);
         }	
         print('</tbody></table>');
         printf('<p>%1$s %2$s to copy</p>',  $copy == 0 ? 'No' : $copy , $copy==1 ? 'redirect' : 'redirects');
         $this->print_form_field('reverse', isset($_GET['reverse'])? 1 : 0, 'hidden');
         if ($copy > 0) {
            print $this->submit_button($this->get_button_label());
         } else {
            print('<p>&nbsp;</p>');
         }
         printf ('<p>');
         if ((count($yoast) > 0) || (count($gc) > 0) ) printf('<h4>%1$s</h4>', __('Other Functions'));
         if (count($yoast) > 0) {
            $redirects = __('Yoast Redirects', GENESIS_CLUB_DOMAIN);
            printf ('<p><a class="button-primary" href="%1$s">%2$s %3$s</a><span class="note">%4$s</span></p>', 
               add_query_arg( array('act' => 'export', 'redirects' => 'yoast'), $this_url), __('Export', GENESIS_CLUB_DOMAIN), $redirects, 
               __('Export redirects if you plan to implement them in your .htaccess file', GENESIS_CLUB_DOMAIN));
            printf ('<p><a class="button-primary" href="%1$s" onclick="return genesis_club_confirm_delete(\'%3$s\')">%2$s %3$s</a><span class="note">%4$s</span></p>', 
               add_query_arg( array('act' => 'delete', 'redirects' => 'yoast'), $this_url), __('Delete', GENESIS_CLUB_DOMAIN), $redirects, 
               __('Delete redirects once you have copied them into your .htaccess file', GENESIS_CLUB_DOMAIN));
         }
         if (count($gc) > 0) {
            $redirects = __('Genesis Club Redirects', GENESIS_CLUB_DOMAIN);
            printf ('<p><a class="button-primary" href="%1$s">%2$s %3$s</a><span class="note">%4$s</span></p>', 
               add_query_arg( array('act' => 'export', 'redirects' => 'gc'), $this_url), __('Export', GENESIS_CLUB_DOMAIN), $redirects, 
               __('Export redirects if you plan to implement them in your .htaccess file', GENESIS_CLUB_DOMAIN));
            printf ('<p><a class="button-primary" href="%1$s" onclick="return genesis_club_confirm_delete(\'%3$s\')">%2$s %3$s</a><span class="note">%4$s</span></p>', 
               add_query_arg( array('act' => 'delete', 'redirects' => 'gc'), $this_url), __('Delete', GENESIS_CLUB_DOMAIN), $redirects,
               __('Delete redirects once you have copied them into your .htaccess file', GENESIS_CLUB_DOMAIN));
         }
         printf ('</p>');
      } else {
         _e('No SEO redirects found', GENESIS_CLUB_DOMAIN);   
      }
   } 

	public function export_panel() {
      printf('<p>%1$s</p>', __('Copy the redirects below and paste them at the top of your .htaccess file.'));
      $redirects = ($_REQUEST['redirects'] == 'gc') ? $this->get_gc_redirects() : $this->get_yoast_redirects() ;
      $url = ($_REQUEST['redirects'] == 'gc') ? 'url' : 'urly' ;
      $s = '';
      foreach ($redirects as $redirect) {
         $s .= sprintf('Redirect %1$s /%2$s %3$s', $redirect['status'], $redirect['slug'], $redirect[$url] ) . "\n";
      }
      printf('<form><textarea rows="20" cols="80" readonly="readonly">%1$s</textarea></form>', $s);
	}
		   
}
?>
