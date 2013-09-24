<?php
class GenesisClubProfile {
    const CLASSNAME = 'GenesisClubProfile'; //this class
	const DOMAIN = 'GenesisClub';
    const SIGNATURE_URL_KEY = 'genesis_author_signature';
    const SIGNATURE_ON_POSTS_KEY = 'genesis_signature_on_posts'; 
    const HIDE_SIGNATURE_METAKEY = '_genesis_hide_signature';
    const SHOW_SIGNATURE_METAKEY = '_genesis_show_signature'; 

	static function init() {
		if ( ! GenesisClubOptions::get_option('profile_disabled')) {
			if (!is_admin())  add_action('wp',array(self::CLASSNAME,'prepare'));
		}
	}	

	static function prepare() {
		 if (is_single()) add_filter( 'the_content', array(self::CLASSNAME, 'append_signature'),5);
		 if (is_single() || is_page()) add_shortcode('gc-sig', array(self::CLASSNAME, 'add_signature'));										
	}
    
	static function append_signature($content='') {
		global $post;
		if ($post 
		&& $post->post_author 
		&& self::get_signature_visibility($post->post_author, $post->ID, $post->post_type)
		&& ($sig = self::get_author_signature($post->post_author)))
			return $content . sprintf('<p><img src="%1$s" alt="Author Signature"/></p>',$sig);
		else
			return $content;
	}

	static function add_signature() {
		print self::append_signature();
	}

    static function signature_on_posts($author_id) {
		return get_user_meta($author_id,self::SIGNATURE_ON_POSTS_KEY,true);
    }    

    static function get_author_signature($author_id) {
		return get_user_meta($author_id, self::SIGNATURE_URL_KEY, true) ;
    }

    static function get_signature_visibility($author_id, $post_id, $post_type) {
		if (('post'==$post_type)  && self::signature_on_posts($author_id))
			return ! get_post_meta($post_id, self::HIDE_SIGNATURE_METAKEY, true);
		else
			return get_post_meta($post_id, self::SHOW_SIGNATURE_METAKEY, true);
    }

}
?>