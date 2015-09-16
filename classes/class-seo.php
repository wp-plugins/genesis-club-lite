<?php
class Genesis_Club_Seo { 

	const REDIRECT_METAKEY = '_genesis_club_redirect';
	
   static function redirect_defaults() {
      return array('url' => '', 'status' => 301) ;      
   }

	static function redirect_options() {
		return array(
			'301' => 'Redirect Permanent (301)',
			'302' => 'Redirect Found (302)',
			'307' => 'Redirect Temporary (307)');
	}	

   static function init() {
		add_action('wp', array(__CLASS__,'maybe_redirect'), 20);
	}	

	public static function maybe_redirect() {
		if (is_singular()) {
         if (($post_id = Genesis_Club_Utils::get_post_id())
		&& ($redirect = Genesis_Club_Options::validate_options(self::redirect_defaults(), Genesis_Club_Utils::get_meta($post_id, self::REDIRECT_METAKEY)))
		&& $redirect['url']) {
         	wp_redirect( $redirect['url'], $redirect['status'] );
			exit;
		}
      }
		return false;
	}


}