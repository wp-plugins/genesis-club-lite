<?php
class Genesis_Club_Text_Widget extends Genesis_Club_Widget {

	private $tips = array(
			'text' => array('heading' => 'Text', 'tip' => 'Widget Content'),
			'autop' =>  array('heading' => 'Auto-paragraph', 'tip' => 'Click to convert automatically convert new lines to paragraph breaks.'),
			);
	
    private	$defaults = array('title' => '', 'html_title' => '', 'text' => '', 'autop' => false);

	
	function __construct() {
		$widget_ops = array('description' => __('Displays a Text widget with enhanced Title', GENESIS_CLUB_DOMAIN) );
		$control_ops = array();
		parent::__construct('genesis-club-text', __('Genesis Club Text Widget', GENESIS_CLUB_DOMAIN), $widget_ops, $control_ops, $this->defaults);
	}

	function widget( $args, $instance ) {
      $args = $this->override_args($args, $instance) ;
      extract($args);
      echo $before_widget;
      $text = apply_filters( 'widget_text', empty( $instance['text'] ) ? '' : $instance['text'], $instance );
      printf('<div class="textwidget">%1$s</div>', empty( $instance['filter'] ) ? $text : wpautop( $text ) );
      echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $this->update_instance( $new_instance, $old_instance );
		if (current_user_can('unfiltered_html') )
			$instance['text'] = $new_instance['text'];
		else
			$instance['text'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['text']) ) ); // wp_filter_post_kses() expects slashed
		$instance['autop'] = isset($new_instance['autop']);
		return $instance;
	}

	function form( $instance ) {
		$this->form_init ($instance, $this->tips);
		$this->print_form_field('text', 'textarea', array(), array('rows' => 16, 'cols' => 30, 'class' => 'widefat' ));
		$this->print_form_field('autop', 'checkbox');		
	}

}




class Genesis_Club_Post_Image_Gallery_Widget extends Genesis_Club_Widget {

	private $tips = array(
			'size' => array('heading' => 'Size', 'tip' => 'Image size'),
			'posts_per_page' => array('heading' => 'Images', 'tip' => 'Maximum number of images to show in the sidebar'),
			'lightbox' => array('heading' => 'Show In Thickbox', 'tip' => 'Click to show larger photo in  lightbox'),
			'hide_featured' => array('heading' => 'Hide Featured Image', 'tip' => 'Hide featured image to avoid duplication.'),
			);
	
    private	$defaults = array('title' => 'Gallery', 
    	'size' => 'medium', 'hide_featured' => false, 'lightbox' => false,
    	'posts_per_page' => 3); //# of visible images);
	
	function __construct() {
		$widget_ops = array('description' => __('Displays a Post Image Gallery in a sidebar widget with optional lightbox', GENESIS_CLUB_DOMAIN) );
		$control_ops = array('width' => 400, 'height' => 350);
		parent::__construct('genesis-club-post-image-gallery', __('Genesis Club Post Images', GENESIS_CLUB_DOMAIN), $widget_ops, $control_ops, $this->defaults );
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

		$gargs = array('columns'=>1, 'link'=>'file', 'orderby' => 'rand', 'size' => $instance['size']);
		if ($instance['hide_featured']
		&& ($featured_image = get_post_thumbnail_id($post->ID))) 
			$gargs['exclude'] = $featured_image;	
		$gallery = gallery_shortcode($gargs);
		if (empty($gallery)) return; //no gallery so do not display an empty widget

      $args = $this->override_args($args, $instance) ;
      extract($args);
      echo $before_widget;
		echo $gallery;
		echo $after_widget;
		self::limit_images($post->ID, $instance['posts_per_page'],$instance['lightbox']);
	}

	function update( $new_instance, $old_instance ) {
		$instance = $this->update_instance( $new_instance, $old_instance );
		$instance['size'] = strip_tags( $new_instance['size'] );
		$instance['hide_featured'] = empty($new_instance['hide_featured']) ? 0 : 1;
		$instance['lightbox'] = empty($new_instance['lightbox']) ? 0 : 1;
		$instance['posts_per_page'] = $new_instance['posts_per_page'];	
		return $instance;
	}

	function form( $instance ) {
		$this->form_init ($instance, $this->tips);		
		$sizes = array_keys(genesis_get_image_sizes());
		$this->print_form_field('size', 'select', array_combine($sizes,$sizes));
		$this->print_form_field('posts_per_page',  'text', array(), array('size' => 3 ,'maxlength' => 3));
		$this->print_form_field('lightbox', 'checkbox');
		$this->print_form_field('hide_featured', 'checkbox');
	}

}

class Genesis_Club_Facebook_Likebox_Widget extends Genesis_Club_Widget {
    const DOMAIN = 'GenesisClub'; //text domain for translation

    private	$defaults = array('title' => 'Like Us', 'href' => 'https://www.facebook.com/DIYWebMastery', 
    		'header' => false, 'faces' => true, 'border' => false, 'stream' => false,
    		'colorscheme' => 'light', 'width' => 290, 'height' => '');

	private $tips = array(
			'href' => array('heading' => 'Facebook URL', 'tip' => 'URL of Facebook page. For example,  https://www,facebook.com/yourpage/'),
			'header' => array('heading' => 'Show Header', 'tip' => 'Show Header.'),
			'faces' => array('heading' => 'Show Faces', 'tip' => 'Show faces of those who liked this site'),
			'border' => array('heading' => 'Show Border', 'tip' => 'Show solid border.'),
			'stream' => array('heading' => 'Show Stream', 'tip' => 'Show recent posts.'),
			'colorscheme' => array('heading' => 'Color Scheme', 'tip' => 'Light or dark'),
			'width' => array('heading' => 'Width', 'tip' => 'Set the width in pixels according to the width of your sidebar. Around 280px is typical.'),
			'height' => array('heading' => 'Height', 'tip' => 'Set the height in pixels based upon how many rows of face you want to display. Around 400px is good.'),
			);

	function __construct() {
		$widget_ops = array('description' => __('Displays a Facebook Likebox', GENESIS_CLUB_DOMAIN) );
		$control_ops = array();
		parent::__construct('genesis-club-likebox', __('Genesis Club Likebox', GENESIS_CLUB_DOMAIN), $widget_ops,$control_ops, $this->defaults );
	}


	function widget( $args, $instance ) {
      $args = $this->override_args($args, $instance) ;
      extract($args);
		echo $before_widget;
		printf( '<div class="fb-like-box" data-href="%1$s" data-header="%2$s" data-show-faces="%3$s" data-stream="%4$s" data-show-border="%5$s" data-colorscheme="%6$s" data-width="%7$s" %8$s></div>', 
			$instance['href'], 
			$instance['header'] ? 'true' : 'false', 
			$instance['faces'] ? 'true' : 'false', 
			$instance['stream'] ? 'true' : 'false', 
			$instance['border'] ? 'true' : 'false', 
			$instance['colorscheme'], 
			$instance['width'], 
			empty($instance['height']) ? '' : sprintf('data-height="%1$s"', $instance['height'])); 
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $this->update_instance( $new_instance, $old_instance );
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

	function form( $instance ) {
		$this->form_init ($instance, $this->tips);
		$this->print_form_field('href', 'textarea', array(), array('cols' => 30, 'rows'=> 4));
		$this->print_form_field('width', 'text',array(), array('size' => 4 ,'maxlength' => 4, 'suffix' => 'px'));
		$this->print_form_field('height', 'text',array(), array('size' => 4 ,'maxlength' => 4, 'suffix' => 'px'));
		$this->print_form_field('colorscheme', 'select', array('light' => 'Light', 'dark' => 'Dark'));
		$this->print_form_field('faces', 'checkbox');
		$this->print_form_field('header','checkbox');
		$this->print_form_field('border', 'checkbox');
		$this->print_form_field('stream', 'checkbox');
	}

}