<?php
class Genesis_Club_Signature {
    const SIGNATURE_URL_KEY = 'genesis_author_signature';
    const SIGNATURE_ON_POSTS_KEY = 'genesis_signature_on_posts'; 
    const FIX_USER_NICENAME = 'genesis_signature_fix_nicename'; 
    const HIDE_SIGNATURE_METAKEY = '_genesis_hide_signature';
    const SHOW_SIGNATURE_METAKEY = '_genesis_show_signature'; 

	static function init() {
		if (!is_admin())  add_action('wp',array(__CLASS__,'prepare'));
	}	

	static function prepare() {
		 add_shortcode('genesis-club-signature', array(__CLASS__, 'add_signature'));	
		 add_shortcode('genesis_club_signature', array(__CLASS__, 'add_signature'));										
		 if (is_single()) add_filter( 'the_content', array(__CLASS__, 'append_signature'),5);
	}
    
	static function append_signature($content) {
		return $content. self::get_post_signature();
	}

	static function add_signature($attr) {
		return self::get_post_signature(true); //signature should always be applied
	}

	static function get_post_signature($always_visible = false) {
		global $post;
		if (($user_id = self::get_post_author())
		&& ($always_visible || self::get_signature_visibility($user_id, $post->ID, $post->post_type)))
			return self::get_signature_by_id($post->post_author);
		else
			return '';
	}

	static function get_post_author() {
		global $post;
		if ($post && $post->post_author)
			return $post->post_author;
		else
			return false;
	}

	static function get_signature_by_id($user_id) {
		if ($user_id && ($sig = self::get_author_signature($user_id)))
			return sprintf('<p><img src="%1$s" alt="Author Signature"/></p>',$sig);
		else
			return '';
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

	static function get_toggle_meta_key( $post_type, $post_author = 0) {
		return (('post'==$post_type)  && self::signature_on_posts($post_author)) 
			? self::HIDE_SIGNATURE_METAKEY : self::SHOW_SIGNATURE_METAKEY;
	}

}
