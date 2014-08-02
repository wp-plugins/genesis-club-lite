<?php
class Genesis_Club_Post_Image_Gallery_Widget extends WP_Widget {

	private $instance;
	private $tooltips;

	private $tips = array(
			'title' => array('heading' => 'Title', 'tip' => 'Widget Title'),
			'size' => array('heading' => 'Size', 'tip' => 'Image size'),
			'posts_per_page' => array('heading' => 'Images', 'tip' => 'Maximum number of images to show in the sidebar'),
			'lightbox' => array('heading' => 'Show In Thickbox', 'tip' => 'Click to show larger photo in  lightbox'),
			'hide_featured' => array('heading' => 'Hide Featured Image', 'tip' => 'Hide featured image to avoid duplication.'),
			);
	
    private	$defaults = array('title' => 'Gallery', 
    	'size' => 'medium', 'hide_featured' => false, 'lightbox' => false,
    	'posts_per_page' => 3); //# of visible images);

	function get_defaults() {
		return $this->defaults;
	}
	
	function __construct() {
		$widget_ops = array('description' => __('Displays a Post Image Gallery in a sidebar widget with optional lightbox', GENESIS_CLUB_DOMAIN) );
		parent::__construct('genesis-club-post-image-gallery', __('Genesis Club Post Images', GENESIS_CLUB_DOMAIN), $widget_ops );
	}

	function limit_images($id, $number, $lightbox) {
		$gid = '.galleryid-'.$id;
		$lbox = 'thickbox-'.$id;		
		$add_lbox = '';
		if ($lightbox) {
			$add_lbox .= sprintf('jQuery("%1$s").find("a").addClass("thickbox").attr("rel","%2$s");',$gid,$lbox); 
			$add_lbox .= 'jQuery("<style type=\"text/css\">#TB_caption {height: auto;}</style>").appendTo("head");';
		}
		print <<< SCRIPT
<script type="text/javascript"> {$add_lbox}
	jQuery('{$gid}').find('br').slice({$number}).hide();
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
		if ($instance['hide_featured']
		&& ($featured_image = get_post_thumbnail_id($post->ID))) 
			$args['exclude'] = $featured_image;	
		$gallery = gallery_shortcode($args);
		if (empty($gallery)) return; //no gallery so do not display an empty widget
		$title = apply_filters('widget_title', $instance['title'] );
		echo $before_widget;
		if ($title) echo $before_title . $title . $after_title;
		echo $gallery;
		echo $after_widget;
		self::limit_images($post->ID, $instance['posts_per_page'],$instance['lightbox']=='true');
	}

	function update( $new_instance, $old_instance ) {
		$instance = wp_parse_args( (array) $old_instance, $this->defaults );;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['size'] = strip_tags( $new_instance['size'] );
		$instance['hide_featured'] = empty($new_instance['hide_featured']) ? 0 : 1;
		$instance['lightbox'] = empty($new_instance['lightbox']) ? 0 : 1;
		$instance['posts_per_page'] = $new_instance['posts_per_page'];	
		return $instance;
	}

	function print_form_field($fld, $type, $options = array(), $args = array()) {
		print Genesis_Club_Options::form_field( 
			$this->get_field_id($fld), $this->get_field_name($fld), 
			$this->tooltips->tip($fld), 
			$this->instance[$fld],
			$type, $options, $args, 'br');
	}

	function form( $instance ) {
		$this->instance = wp_parse_args( (array) $instance, $this->get_defaults() );
		$this->tooltips = new DIY_Tooltip($this->tips);
		print '<div class="diy-wrap">';		
		$sizes = array_keys(genesis_get_image_sizes());
		$this->print_form_field('title', 'text', array(), array('size' => 12));
		$this->print_form_field('size', 'select', array_combine($sizes,$sizes));
		$this->print_form_field('posts_per_page',  'text', array(), array('size' => 3 ,'maxlength' => 3));
		$this->print_form_field('lightbox', 'checkbox');
		$this->print_form_field('hide_featured', 'checkbox');
		print '</div>';		
	}
}

class Genesis_Club_Facebook_Likebox_Widget extends WP_Widget {
    const DOMAIN = 'GenesisClub'; //text domain for translation
	private $instance;
	private $tooltips;

    private	$defaults = array('title' => 'Like Us', 'href' => 'https://www.facebook.com/DIYWebMastery', 
    		'header' => false, 'faces' => true, 'border' => false, 'stream' => false,
    		'colorscheme' => 'light', 'width' => 290, 'height' => '');

	private $tips = array(
			'title' => array('heading' => 'Title', 'tip' => 'Widget Title'),
			'href' => array('heading' => 'Facebook Page URL', 'tip' => 'URL of Facebook page. For example,  https://www,facebook.com/yourpage/'),
			'header' => array('heading' => 'Show Header', 'tip' => 'Show Header.'),
			'faces' => array('heading' => 'Show Faces', 'tip' => 'Show faces of those who liked this site'),
			'border' => array('heading' => 'Show Border', 'tip' => 'Show solid border.'),
			'stream' => array('heading' => 'Show Stream', 'tip' => 'Show recent posts.'),
			'colorscheme' => array('heading' => 'Color Scheme', 'tip' => 'Light or dark'),
			'width' => array('heading' => 'Width', 'tip' => 'Set the width in pixels according to the width of your sidebar. Around 280px is typical.'),
			'height' => array('heading' => 'Height', 'tip' => 'Set the height in pixels based upon how many rows of face you want to display. Around 400px is good.'),
			);

	function get_defaults() {
		return $this->defaults;
	}

	function __construct() {
		$widget_ops = array('description' => __('Displays a Facebook Likebox', GENESIS_CLUB_DOMAIN) );
		$control_ops = array('width' => 420, 'height' => 500);
		parent::__construct('genesis-club-likebox', __('Genesis Club Likebox', GENESIS_CLUB_DOMAIN), $widget_ops );
	}


	function widget( $args, $instance ) {
		extract( $args );
		$instance = wp_parse_args( (array) $instance, $this->defaults );
		$height = empty($instance['height']) ? '' : sprintf('data-height="%1$s"', $instance['height']);
		$title = apply_filters('widget_title', $instance['title'] );
		echo $before_widget;
		if ($title) echo $before_title . $title . $after_title;
		printf( '<div class="fb-like-box" data-href="%1$s" data-header="%2$s" data-show-faces="%3$s" data-stream="%4$s" data-show-border="%5$s" data-colorscheme="%6$s" data-width="%7$s" %8$s></div>', 
			$instance['href'], 
			$instance['header'] ? 'true' : 'false', 
			$instance['faces'] ? 'true' : 'false', 
			$instance['stream'] ? 'true' : 'false', 
			$instance['border'] ? 'true' : 'false', 
			$instance['colorscheme'], $instance['width'], $height); 
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = wp_parse_args( (array) $old_instance, $this->defaults );;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['href'] = strip_tags( $new_instance['href'] );
		$instance['header'] = empty($new_instance['header']) ? 0 : 1;
		$instance['faces'] = empty($new_instance['faces']) ? 0 : 1;
		$instance['stream'] = empty($new_instance['stream']) ? 0 : 1;	
		$instance['border'] = empty($new_instance['border']) ? 0 : 1;
		$instance['colorscheme'] = $new_instance['colorscheme'];
		$instance['width'] = $new_instance['width'];	
		$instance['height'] = $new_instance['height'];	
		return $instance;
	}

	function print_form_field($fld, $type, $options = array(), $args = array()) {
		print Genesis_Club_Options::form_field(
			$this->get_field_id($fld), $this->get_field_name($fld), 
			$this->tooltips->tip($fld), 
			$this->instance[$fld],
			$type, $options, $args, 'br');
	}

	function form( $instance ) {
		print '<div class="diy-wrap">';		
		$this->instance = wp_parse_args( (array) $instance, $this->get_defaults() );
		$this->tooltips = new DIY_Tooltip($this->tips);
		$this->print_form_field('title', 'text', array(), array('size' => 25 ));
		print '<hr/>';
		$this->print_form_field('href', 'textarea', array(), array('cols' => 25, 'rows'=> 2));
		$this->print_form_field('width', 'text',array(), array('size' => 4 ,'maxlength' => 4, 'suffix' => 'px'));
		$this->print_form_field('height', 'text',array(), array('size' => 4 ,'maxlength' => 4, 'suffix' => 'px'));
		$this->print_form_field('colorscheme', 'select', array('light' => 'Light', 'dark' => 'Dark'));
		$this->print_form_field('faces', 'checkbox');
		$this->print_form_field('header','checkbox');
		$this->print_form_field('border', 'checkbox');
		$this->print_form_field('stream', 'checkbox');
		print '</div>';
	}
}