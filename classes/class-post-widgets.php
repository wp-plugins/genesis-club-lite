<?php
class Genesis_Club_Post_Specific_Widget extends Genesis_Club_Widget {

   const  WIDGET_CONTENT_META_KEY = '_genesis_club_widget_content';
	
 	private $tips = array(
			'text' => array('heading' => 'Text', 'tip' => 'Widget Content'),
			'autop' =>  array('heading' => 'Auto-paragraph', 'tip' => 'Click to convert automatically convert new lines to paragraph breaks.'),
			'background' =>  array('heading' => 'Background', 'tip' => 'This can be a background color or an background image, or both'),
			'border' =>  array('heading' => 'Border', 'tip' => 'For example, to put a thin box around the widget: 1px solid gray'),
			'margin' =>  array('heading' => 'Margin', 'tip' => 'For example, to put no margin on each side and 20px above and 25px below use: 20px 0 25px 0'),
			'padding' =>  array('heading' => 'Padding', 'tip' => 'For example, for 10px padding use: 10px'),
			);
 
   static private $widget_defaults = array ('html_title' => '', 'text' => '', 'autop' => false,
         'background' => '', 'border' => '', 'margin' => '', 'padding' => '') ;

   private $defaults ;
	

   static function get_widget_defaults() {
      return self::$widget_defaults;
   }

	function __construct() {
	   $this->defaults = self::get_widget_defaults();
	   $control_ops = array();
		$widget_ops = array('description' => __('Widget that gets its content from the current post', GENESIS_CLUB_DOMAIN) );
		parent::__construct('genesis-club-post-specific', __('Genesis Club Post Specific', GENESIS_CLUB_DOMAIN), $widget_ops, $control_ops, $this->defaults );
	}

	function widget( $args, $instance ) {
		if ( is_singular()
		&& ($post_id = Genesis_Club_Utils::get_post_id())
		&& ($content = Genesis_Club_Utils::get_meta($post_id, self::WIDGET_CONTENT_META_KEY) )
		&& !empty($content['text'])) {
         $instance['html_title'] = $content['html_title'] ; 
         $args = $this->override_args($args, $instance) ;
         $text = do_shortcode($content['text']);
         extract( $args );
         echo $before_widget;
         printf('<div class="textwidget">%1$s</div>', $content['autop']  ?  wpautop($text) : html_entity_decode($text) );
         echo $after_widget;
      }
	}

	function update( $new_instance, $old_instance ) {
		$instance = $this->update_instance( $new_instance, $old_instance );
		$instance['background'] = trim($new_instance['background']);
		$instance['border'] = trim($new_instance['border']);
		$instance['margin'] = trim($new_instance['margin']);
		$instance['padding'] = trim($new_instance['padding']);
		return $instance;
	}

	function form( $instance ) {
		$this->form_init ($instance, $this->tips);	
		printf ('<h4>%1$s</h4>', __('Styling', GENESIS_CLUB_DOMAIN));
		$this->print_form_field('background', 'text', array(), array('class' => 'widefat' ));
		$this->print_form_field('border', 'text', array(), array('class' => 'widefat' ));
		$this->print_form_field('margin', 'text', array(), array('class' => 'widefat'));
		$this->print_form_field('padding', 'text', array(), array('class' => 'widefat'));			
	}

	function add_custom_css() { 
      $all_instances = $this->get_settings();
      foreach ( $all_instances as $id => $inst) {
         $widget_id = $this->id_base.'-'.$id;
        		
        	if (is_active_widget( false, $widget_id, $this->id_base, false )) {
        	   $instance = wp_parse_args( $inst, $this->get_defaults() );
            $style = '';
            if (isset($instance['background']) && $instance['background']) $style .= sprintf('background: %1$s;',$instance['background']);
            if (isset($instance['border']) && $instance['border']) $style .= sprintf('border: %1$s;',$instance['border']);
            if (isset($instance['margin']) && $instance['margin']) $style .= sprintf('margin: %1$s;',$instance['margin']);			
            if (isset($instance['padding']) && $instance['padding']) $style .= sprintf('padding: %1$s;',$instance['padding']);	
            if (!empty($style)) {
               $element = sprintf('#%1$s .widget-wrap', $widget_id);         
               $css = sprintf('%1$s {%2$s}', $element, $style); 			
               Genesis_Club_Post::add_css($css);
			   }
         }
      }
	}

}


class Genesis_Club_Posts_Widget extends Genesis_Club_Widget {

	private $tips = array(
		'title' => array('heading' => 'Title', 'tip' => 'Widget Title'),
		'show_title' => array('heading' => 'Show Title', 'tip' => 'Show Title'),
		'show_excerpt' => array('heading' => 'Show Excerpt', 'tip' => 'Show official excerpt of post'),
		'show_content' => array('heading' => 'Show Content', 'tip' => 'Show some content from the post'), 
		'show_limit' => array('heading' => 'Character Limit', 'tip' => 'Number of characters to show from content of the post'), 
		'show_more' => array('heading' => 'Show More Link', 'tip' => 'Show more link'), 
		'show_image' => array('heading' => 'Show Image', 'tip' => 'Show Image'),
		'image_size' => array('heading' => 'Image Size', 'tip' => 'Size of image to show above the post'),
		'image_align' => array('heading' => 'Image Alignment', 'tip' => 'Horizontal alignment of image'),
		'source_type' => array('heading' => 'Source', 'tip' => 'Choosing <i>Specific term</i> means that the selection can be based on a specific category, tag or other taxonomy term,<br/><i>Specific list of posts</i> is  choice based on post ID,<br/>,<i>Current context</i> means they are chosen based on the current page so on a category archive page the selection is based on posts in that category <br/><i>Current top parent</i> means that posts are chosen based the same top level category as the current post'),
		'post_type' => array('heading' => 'Post Type', 'tip' => 'Would you like to use posts or pages?'),
		'posts_term' => array('heading' => 'Terms To Include', 'tip' => 'Select the posts for inclusion'),
		'exclude_terms' => array('heading' => 'Terms To Exclude', 'tip' => 'List which category, tag or other taxonomy IDs to exclude. (1,2,3,4 for example)'),
		'include_exclude' => array('heading' => 'Include/Exclude', 'tip' => 'Choose whether to include or exclude the posts below from your slider'),
		'post_id' => array('heading' => 'Post IDs', 'tip' => 'List which post IDs to include / exclude. (1,2,3,4 for example'),
		'posts_num' => array('heading' => '# of posts to show', 'tip' => 'Show up to a maximum of this number of posts'),
		'posts_offset' => array('heading' => '# of posts to offset', 'tip' => 'Return content from posts not from the first post but with an offset'),
		'orderby' => array('heading' => 'Order by', 'tip' => 'Choose the order in which you wants the posts to be displayed')
	);
	
    private $defaults = array('title' => '',
					'show_title' => false,
					'show_image' => false,
					'show_excerpt' => false,
					'show_content' => false, 
					'show_limit' => false, 
					'show_more' => false, 
					'image_size' => 'thumbnail', 
					'image_align' => 'none',
					'source_type' => '',
					'post_type' => 'post',
					'posts_term' => '',
					'exclude_terms' => '',
					'include_exclude' => '',
					'posts_num' => 5,
					'posts_offset' => 0,
					'post_id' => '',
					'orderby' => 'date'
				) ;

	function __construct() {
		$widget_ops = array(
         'classname'   => 'featured-content genesis-club-posts',
         'description' => __( "Displays a postshow using post content", GENESIS_CLUB_DOMAIN ) );
		$control_ops = array('width' => 420, 'height' => 500);
		parent::__construct('genesis-club-posts', __('Genesis Club Posts', GENESIS_CLUB_DOMAIN), $widget_ops, $control_ops, $this->defaults);
	}

	function align_options() {
		return array(
			'none' => __('None', GENESIS_CLUB_DOMAIN ),
			'alignleft' => __('Left', GENESIS_CLUB_DOMAIN ),
			'alignright' => __('Right', GENESIS_CLUB_DOMAIN ),
		   'aligncenter' => __('Center', GENESIS_CLUB_DOMAIN ),
         );
	}
		
	function size_options() {
		$options = array();
		$sizes = genesis_get_image_sizes();	
		foreach ($sizes as $size => $dims) $options[$size] = $size . '('.$dims['width'].'x'.$dims['height'].')';
		return $options;
	}
	
	function post_type_options() {
		$options = array();
		$post_types = get_post_types( array('public' => true ), 'names', 'and');
		foreach ( $post_types as $post_type ) $options[$post_type] = $post_type;
		return $options;
	}

	function source_type_options() {
		return array(
			'term' => __('Specific term', GENESIS_CLUB_DOMAIN ),
			'post' => __('Specific list of posts', GENESIS_CLUB_DOMAIN ),
			'current' => __('Current context', GENESIS_CLUB_DOMAIN ),
		   'parent' => __('Current top parent', GENESIS_CLUB_DOMAIN ),
         );
	}

	function get_top_term_suffixed($term,$suffix) {
			if ($top_term = $this->get_top_term($term))
				return get_term_by('slug',$top_term->slug.$suffix,$top_term->taxonomy);
			else
				return false;
		}
		
	function get_top_term($term) {
			if ($term) {
				$term_tree = get_category_parents($term->term_id, FALSE, ':', true);
				$terms = explode(':',$term_tree);
				return get_term_by('slug',$terms[0],$term->taxonomy);					
			}
			return false;
		}
		
	function get_post_top_cat_slug() {
			$category = get_the_category(); 
			if (count($category) >0) {
				$term =  $this->get_top_term($category[0]);
				if ($term) return $term->slug;
			}
			return '';			
		}

	function get_current_term() {
			global $wp_query;
			if (is_tax() || is_category() || is_tag()) {
				if (is_category())
					$term = get_term_by('slug',get_query_var('category_name'),'category') ;
				elseif (is_tag())
					$term = get_term_by('slug',get_query_var('tag_name'),'post_tags') ;
				else
					$term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy')) ;						
				return $term;
			} elseif (is_single('post')) {
				global $post;
				$myCategories = array();
				$postCategories = get_the_category($post->ID);
				foreach ( $postCategories as $postCategory ) {
					$myCategories[] = get_term_by('id', $postCategory->cat_ID, 'category');
				}
				if (count($myCategories) > 0) return $myCategories[0];
			}
			return false;
		}

	function get_instance_term($fld) {
		$posts_term = explode(',', $fld );
		return count($posts_term) == 2 ?  get_term_by('slug',$posts_term['1'],$posts_term['0']) : false;
	}
	
	function widget( $args, $instance ) {
      $args = $this->override_args($args, $instance) ;
		extract( $args );
		$term_args = array( );
		$terms = array();
		$term = $this->get_current_term();
		if ('page' != $instance['post_type'] ) {
			switch ($instance['source_type'])  {
					case "parent":	{ $term = $this->get_top_term($term); break;}	
					case "current": { break; }
					default: $term = $this->get_instance_term($instance['posts_term']);
			}
			if ($term)  {
				$term_args['tax_query'] = array(
					array('taxonomy' => $term->taxonomy, 'field' => 'id', 
						'terms' => count($terms) > 0 ? $terms : $term->term_id)
					);				
				if ( $instance['exclude_terms'] ) {
					$exclude_terms = explode(',', str_replace(' ', '', $instance['exclude_terms' ] ) );
						$term_args[$term->taxonomy . '__not_in'] = $exclude_terms;
				}
			}
		}
		if ( $instance['post_id'] ) {
			$IDs = explode(',', str_replace(' ', '', $instance['post_id'] ) );
			if ('include' == $instance['include_exclude'])
				$term_args['post__in'] = $IDs;
			else
				$term_args['post__not_in'] = $IDs;
		}
		if ( $instance['posts_offset']) {
			$myOffset = $instance['posts_offset'];
			$term_args['offset'] = $myOffset;
		}
		$query_args = array_merge( $term_args, array(
			'post_type' => $instance[ 'post_type'],
			'posts_per_page' => $instance['posts_num'],
			'orderby' => $instance['orderby']
		) );
      $wrap =  $instance['show_excerpt'];
		
		echo $before_widget;
		$posts = array();
		$posts = new WP_Query( $query_args );
		while ( $posts->have_posts()) : $posts->the_post();
         $permalink = get_permalink();
			$title = get_the_title();
			$post_title = $instance['show_title'] ? sprintf('<a rel="bookmark" href="%1$s">%2$s</a>', $permalink, esc_html($title)) : '';
			if ( $instance['show_image'] 
			&& ($image = genesis_get_image( array(
				  'format'  => 'html',
				  'size'    => $instance['image_size'],
				  'context' => 'featured-post-widget',
				  'attr'    => genesis_parse_attr( 'entry-image-widget' )) )))
			   $post_image = sprintf( '<a href="%s" title="%s" class="%s">%s</a>', 
				     $permalink, the_title_attribute( 'echo=0' ), esc_attr( $instance['image_align'] ), $image );
			else
			   $post_image = '';

			if ( $instance['show_excerpt'] ) 
			   if ($instance['show_content'])
               if ( $instance['show_limit'])
                  $post_excerpt = get_the_content_limit( (int)$instance['show_limit'] );
					else
                  $post_excerpt = get_the_content( $instance['show_more'] ? null : '');
				else
               $post_excerpt = get_the_excerpt();
         else 
			   $post_excerpt = '';

         if ($wrap)
		       genesis_markup( array(
				  'html5'   => '<article %s>',
				  'xhtml'   => sprintf( '<div class="%s">', implode( ' ', get_post_class() ) ),
				  'context' => 'entry') );

			if (! empty( $post_title) || ! empty( $post_image))
				printf( genesis_html5() ?  
				     '<header class="entry-header"><h2 class="entry-title">%1$s%2$s</h2></header>' :
				    '<h2>%1$s%2$s</h2>', $post_image,  $post_title );

			if ( ! empty( $post_excerpt) ) {
				printf( genesis_html5() ?  
				     '<div class="entry-content">%1$s</div>' : '%1$s', $post_excerpt );
			}

         if ($wrap)
			   genesis_markup( array(
				  'html5' => '</article>',
				  'xhtml' => '</div>',
			) );
		endwhile; 
		echo $after_widget;
		wp_reset_query();
	}

	function update( $new_instance, $old_instance ) {
		$instance = $this->update_instance( $new_instance, $old_instance );
		$instance['show_title'] = !empty($new_instance['show_title']);
		$instance['show_excerpt'] = !empty($new_instance['show_excerpt']);
		$instance['show_more'] = !empty($new_instance['show_more']);
		$instance['show_content'] = !empty($new_instance['show_content']);
		$instance['show_limit'] = $new_instance['show_limit'];		
		$instance['show_image'] = !empty($new_instance['show_image']);		
		$instance['image_size'] = strip_tags( $new_instance['image_size']);
		$instance['image_align'] = $new_instance['image_align'] ;
		$instance['source_type'] = $new_instance['source_type'];
		$instance['post_type'] = $new_instance['post_type'] ;
		$instance['posts_term'] = $new_instance['posts_term'] ;
		$instance['exclude_terms'] = $new_instance['exclude_terms'] ;
		$instance['include_exclude'] = $new_instance['include_exclude'] ;
		$instance['posts_num'] = !empty($new_instance['posts_num']) ? $new_instance['posts_num'] : 5 ;
		$instance['posts_offset'] = !empty($new_instance['posts_offset']) ? $new_instance['posts_offset'] :0;
		$instance['post_id'] = $new_instance['post_id'] ;
		$instance['orderby'] = $new_instance['orderby'] ;
		return $instance;
	}

	function form( $instance ) {
		$this->form_init ($instance, $this->tips);
		print('<h4>General Settings</h4>');
		$this->print_form_field('show_title', 'checkbox');
		$this->print_form_field('show_excerpt', 'checkbox');
		$this->print_form_field('show_more', 'checkbox');
		$this->print_form_field('show_content', 'checkbox');
		$this->print_form_field('show_limit', 'text', array(), array('size' => 5));
		$this->print_form_field('show_image', 'checkbox');
		$this->print_form_field('image_size', 'select', $this->size_options());
		$this->print_form_field('image_align', 'select', $this->align_options());
		print ('<hr/><h4>Post Selection</h4>');
		$this->print_form_field('post_type', 'select', $this->post_type_options());
		$this->print_form_field('source_type', 'select', $this->source_type_options());
		print ('<hr/>Include or Exclude by Terms');
		$this->print_form_field('posts_term','select', $this->taxonomy_options('posts_term'), array('multiple' => true));
		$this->print_form_field('exclude_terms', 'text', array(), array('size' => 10));		
		print ('<hr/>Include or Exclude by Post ID');
		$this->print_form_field('include_exclude', 'select', array('include' => 'Include', 'exclude' => 'Exclude'));
		$this->print_form_field('post_id', 'text', array(), array('size' => 10));		
		print ('<hr/><h4>Numbers And Ordering</h4>');
		$this->print_form_field('posts_num', 'text', array(), array('size' => 4));		
		$this->print_form_field('posts_offset', 'text', array(), array('size' => 4));		
		$this->print_form_field('orderby', 'select', array('date' => 'Date', 'ID' => 'ID', 'rand' => 'Random', 'title' => 'Title'));
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

}
