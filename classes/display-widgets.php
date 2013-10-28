<?php
class GenesisClubDisplayWidgets {
    const CLASSNAME = 'GenesisClubDisplayWidgets'; //this class
	const DOMAIN = 'GenesisClub';
	
	public static function init() {
		if (! GenesisClubOptions::get_option('display_widgets_disabled')) {
			add_action('widgets_init',array(self::CLASSNAME,'register_widgets'));		
			if (!is_admin())  add_action('wp',array(self::CLASSNAME,'prepare'));
		}
	}	

	public static function register_widgets() {
		register_widget( 'GenesisClubPostImageGalleryWidget' );
	}	

	public static function prepare() {
		if (is_active_widget( false, false, 'genesis-club-post-image-gallery', false )) add_thickbox();
	}	

}


class GenesisClubPostImageGalleryWidget extends WP_Widget	{
    const DOMAIN = 'GenesisClub'; //text domain for translation
    private	$defaults = array('title' => 'Gallery', 
    	'size' => 'medium', 'hide_featured' => 'false', 'lightbox' => 'false',
    	'posts_per_page' => 3); //# of visible images);

	function __construct() {
		$widget_ops = array('description' => __('Displays a Post Image Gallery in a sidebar widget with optional lightbox', self::DOMAIN) );
		parent::__construct('genesis-club-post-image-gallery', __('Genesis Club Post Image Gallery', self::DOMAIN), $widget_ops );
	}

	function add_lightbox($id, $number) {
		$lbox = 'thickbox-'.$id;
		$gid = '.galleryid-'.$id;
		print <<< SCRIPT
<script type="text/javascript">
	jQuery('{$gid}').find('a').addClass('thickbox').attr('rel','{$lbox}');
	jQuery('{$gid}').find('.gallery-item').slice({$number}).hide();
</script>
SCRIPT;
	}

	function widget( $args, $instance ) {
		if (!is_singular()) return;  //only run on single post/page/custom post_type pages
		$post = get_post();
		if (is_null($post)) return; //we have a post to work with
		extract( $args );
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		$args = array('columns'=>1, 'link'=>'file', 'orderby' => 'rand', 'size' => $instance['size']);
		if (($instance['hide_featured']=='true')
		&& ($featured_image = get_post_thumbnail_id($post->ID))) 
			$args['exclude'] = $featured_image;	
		if ($instance['lightbox']=='false') $args['posts_per_page'] = $instance['posts_per_page'];	
		$gallery = gallery_shortcode($args);
		if (empty($gallery)) return; //no gallery so do not display an empty widget
		$title = apply_filters('widget_title', $instance['title'] );
		echo $before_widget;
		if ($title) echo $before_title . $title . $after_title;
		echo $gallery;
		echo $after_widget;
		if ($instance['lightbox']=='true') self::add_lightbox($post->ID, $instance['posts_per_page']);
	}

	function update( $new_instance, $old_instance ) {
		$instance = wp_parse_args( (array) $old_instance, $this->defaults );;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['size'] = strip_tags( $new_instance['size'] );
		$instance['hide_featured'] = empty($new_instance['hide_featured']) ? 'false' : 'true';
		$instance['lightbox'] = empty($new_instance['lightbox']) ? 'false' : 'true';
		$instance['posts_per_page'] = $new_instance['posts_per_page'];	
		return $instance;
	}

	function form_field($fld, $label,  $value, $type, $options = array(), $args = array()) {
		$fld_id = $this->get_field_id($fld);
		$fld_name = $this->get_field_name($fld);
		if ($args) extract($args);
		$input = '';
		$label = sprintf('<label for="%1$s">%2$s</label>', $fld_id, __($label));
		switch ($type) {
			case 'text':
				$input .= sprintf('<input type="text" id="%1$s" name="%2$s" value="%3$s" %4$s %5$s %6$s/> %7$s',
					$fld_id, $fld_name, $value, 
					isset($size) ? ('size="'.$size.'"') : '', isset($maxlength) ? ('maxlength="'.$maxlength.'"') : '',
					isset($class) ? ('class="'.$class.'"') : '', isset($suffix) ? $suffix : '');
				return sprintf('<p>%1$s%2$s</p>', $label, $input);
				break;
			case 'checkbox':
				$input .= sprintf('<input type="checkbox" class="checkbox" id="%1$s" name="%2$s" %3$svalue="1"/>',
					$fld_id, $fld_name, checked($value, 'true', false));
				return sprintf('%1$s%2$s<br/>', $input, $label);
				break;
			case 'radio': 
				if (is_array($options)) 
					foreach ($options as $optkey => $optlabel)
						$input .= sprintf('<input type="radio" id="%1$s" name="%2$s" %3$s value="%4$s" />&nbsp;%5$s&nbsp;&nbsp;',
							$fld_id, $fld_name, checked($optkey, $value, false), $optkey, $optlabel); 
				return sprintf('<p>%1$s%2$s</p>', $label, $input);							
				break;		
			case 'select': 
				if (is_array($options)) 
					foreach ($options as $optkey => $optlabel)
						$input .= sprintf('<option%1$s value="%2$s">%3$s</option>',
							selected($optkey, $value, false), $optkey, $optlabel); 
				return sprintf('<p>%1$s<select id="%2$s" name="%3$s">%4$s</select></p>', $label, $fld_id, $fld_name, $input);							
				break;		
		}
	}

	function form( $instance ) {
		$sizes = array_keys(genesis_get_image_sizes());
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		echo $this->form_field('title', 'Title: ', $instance['title'], 'text');
		echo $this->form_field('size', 'Size: ', $instance['size'], 'select', array_combine($sizes,$sizes));
		echo $this->form_field('posts_per_page', '# Of Images To Show: ', $instance['posts_per_page'], 'text', array(), 
			array('size' => 3 ,'maxlength' => 3));
		echo $this->form_field('lightbox', ' Show Images in LightBox', $instance['lightbox'], 'checkbox');
		echo $this->form_field('hide_featured', ' Exclude Featured Image', $instance['hide_featured'], 'checkbox');
	}
}