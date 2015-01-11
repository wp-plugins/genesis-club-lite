<?php
class Genesis_Club_Accordion_Admin extends Genesis_Club_Admin {
	protected $tips = array(
		'accordion_enabled' => array('heading' => 'Accordion Enabled', 'tip' => 'Click to enable the accordion on this page'),
		'accordion_header_class' => array('heading' => 'Override Header Style', 'tip' => 'Enter a custom class if you want to override the accordion header styling.'),
		'accordion_content_class' => array('heading' => 'Override Content Style', 'tip' => 'Enter a custom class if you want to override the accordion content styling'),
		'accordion_container_class' => array('heading' => 'Override Container Style', 'tip' => 'Enter a custom class if you want to override the accordion container styling'),
		'accordion_nopaging' => array('heading' => 'No Paging', 'tip' => 'Click to checkbox to disable paging and hence show all the FAQ posts in the accordion'),
		);
	
	function init() {		
		add_action('admin_menu',array($this, 'admin_menu'));
		add_action('load-edit-tags.php', array($this, 'load_archive_page'));	
		add_action('load-post.php', array($this, 'load_post_page'));	
		add_action('load-post-new.php', array($this, 'load_post_page'));	
		add_action('edit_term', array($this, 'save_archive'), 10, 2 );	
		add_action('do_meta_boxes', array($this, 'do_meta_boxes'), 20, 2 );
		add_action('save_post', array($this, 'save_postmeta'));
	}
	
	function admin_menu() {
		$this->screen_id = add_submenu_page($this->get_parent_slug(), __('Accordion'), __('Accordion'), 'manage_options', 
			$this->get_slug(), array($this,'page_content'));
		add_action('load-'.$this->get_screen_id(), array($this, 'load_page'));
	}

	function page_content() {
 		$title = $this->admin_heading('Accordion Settings', GENESIS_CLUB_ICON);				
		$this->print_admin_form_with_sidebar_start($title); 
		do_meta_boxes($this->get_screen_id(), 'side', null); 
		$this->print_admin_form_with_sidebar_middle();
		do_meta_boxes($this->get_screen_id(), 'normal', null); 
		$this->print_admin_form_end(__CLASS__);
	} 	
	
	function load_page() {
		Genesis_Club_Accordion::add_accordion (array('enabled' => true, 'header_class' => '', 'content_class' => '.accordion-content no-margin'));
		$this->add_meta_box('intro', 'Instructions',  'intro_panel');
		$this->add_meta_box('help', 'Help On Accordion Settings',  'help_panel');
		$this->add_meta_box('tips', 'Tips On Setting Up A FAQ',  'tips_panel');
		$this->add_meta_box('example','Example Of An Accordion In Action', 'example_panel');
		$this->add_meta_box('news', 'Genesis Club News', 'news_panel', null, 'side');
		add_action ('admin_enqueue_scripts',array($this, 'enqueue_admin_styles'));
		add_action ('admin_enqueue_scripts',array($this, 'enqueue_postbox_scripts'));
	}

	function load_post_page() {
		$this->set_tooltips($this->tips);
	}

	function load_archive_page() {
		add_action( $_REQUEST['taxonomy'] . '_edit_form', array($this, 'archive_panel'), 10, 2 );	
		$this->set_tooltips($this->tips);
	}
	
	function do_meta_boxes( $post_type, $context) {
		$post_types=get_post_types();
		if ( in_array($post_type, $post_types ) && ('advanced' === $context )) {
			$this->add_meta_box( 'accordion', 'Genesis Club Accordion Settings', 'accordion_panel' , null, 'advanced', 'low', $post_type);	
		}
	}
	
 	function news_panel($post,$metabox){	
		Genesis_Club_Feed_Widget::display_feeds(array(GENESIS_CLUB_NEWS));
	}	
	
	function intro_panel() {
		$url = admin_url('edit.php');
		$url2 = admin_url('edit-tags.php?taxonomy=category');
		print <<< INTRO
<p class="attention">There are no settings on this page.</p>
<p class="attention">However, links are provided to where you can set up accordions for your FAQs.</p>
<p class="bigger">For a short single page FAQ go to the <a href="{$url}>?post_type=page">Page Editor</a>, click to edit your FAQ page, and then scroll down and edit the Accordion section.</p>
<p class="bigger">For a FAQ that is made up of individual posts in the FAQ category go to the <a href="{$url2}">Category Editor</a>, choose you FAQ category and then scroll down and edit the Accordion section.</p>
INTRO;
	}
	
	function help_panel() {
 		$screenshot = plugins_url('images/accordion.jpg',dirname(__FILE__));
		print <<< HELP
<p>Below is a annotated screenshot of the <i>Accordion Settings</i>.</p>
<p>Simply click the checkbox to enable the accordion</p>
<p>If you want to add your own styling you have the option to override the CSS class for the header (the question), and the CSS class for the content (the answer). </p>
<p><img src="{$screenshot}" alt="Screenshot on Accordion Settings" /></p>
<p>If the section does not appear in the Post or Category Editor then click the <i>Screen Options</i> at the top of the page and make sure you click the checkboxes to show the Accordion Settings.</p>
<p>For an accordion made from posts in a category, then the plugin uses the post titles as the question, and the post content as the answers.<p>
<p>For an accordion made on a single page then the page should consist of a &lt;h3&gt; heading for each question with a single paragraph beneath it for each answer. You can have multi-paragraph answers but in this case to you need to wrap all the paragraphs that hold the answer in a DIV element.</p>
HELP;
	}

	function tips_panel() {
		print <<< TIPS
<p>Use a single page for your FAQ when you have relatively few questions and short answers and you do not envisage your FAQ changing much over time.</p>
<p>Conversely, if you have many questions, long answers or frequent updates then choosing the FAQ category approach will be better from the point of view of SEO, the user experience, and from  administration of the FAQ.</p>
<p>Also remember that if you have a lot of frequently asked questions you may want to break it up into separate FAQs which can be subcategories. For example, on the <a href="http://www.genesisclub.co">Genesis Club website</a>, we have a Genesis FAQ, a Membership FAQ and a WordPress FAQ.</p>
TIPS;
	}
	
	function example_panel() {
		Genesis_Club_Accordion::add_accordion(array('enabled' => true));
		$support_url = GENESIS_CLUB_SUPPORT_URL;
		print <<< EXAMPLE
<p>Click the questions below to see an accordion FAQ in action.</p>
<div class="accordion">
<h3>What Is A FAQ?</h3>
<p>A FAQ is a list of frequently asked questions, and the answers.</p>
<h3>What Is An Accordion FAQ?</h3>
<p>It is a way of just displaying the questions, and only exposing each answer when the user clicks the question.</p>
<h3>What Are The Two Ways Of Creating FAQs?</h3>
<ul>
<li>Use a Single Page - Create a page with a title of FAQ, on something similar, and add questions and answers where the question is a h3 header and the answer is a single paragraph.</li>
<li>Use a FAQ Category - create a post category called FAQ, or something similar, and write a post every time you have a question. The post title should be the question, and the content of the post is the answer.</li>
</ul>
<h3>But What If I Want Multiple FAQs?</h3>
<p>Not a problem. Either use different categories or sub-categories for each FAQ. You can put FAQ as an entry on the menu, and use sub menu options for each sub-category FAQ. It is all good!</p>
<h3>I Have Set Up A FAQ But It Is Not FAQing Working!!!</h3>
<p>Calm down dear! Help is at hand. Head over to the <a target="_blank" href="https://www.facebook.com/DIYWebmastery">DIYWebmastery page on Facebook</a> and ask your question.  Or, if you have an up to date licence you can get priority support at  <a href="{$support_url}">Genesis Club Pro Support</a>.</p>  
</div>
EXAMPLE;
	}
	
	function save_archive($term_id, $tt_id) {
		return isset( $_POST['accordion'] ) ?
			Genesis_Club_Accordion::save_accordion('terms', $term_id, (array) $_POST['accordion']) : false;
	}	

	function save_postmeta($post_id) {
		return isset( $_POST['accordion'] ) ?  
			Genesis_Club_Accordion::save_accordion('posts', $post_id, (array) $_POST['accordion']) : false;
	}

	function accordion_panel($post,$metabox){	 		
		$this->accordion_section(Genesis_Club_Accordion::get_accordion('posts', $post->ID), false) ;
	}

	function archive_panel($term, $tt_id) {
		$this->accordion_section(Genesis_Club_Accordion::get_accordion('terms', $term->term_id), true) ;
    }	

	private function accordion_section($accordion, $is_archive){
		$defaults = array('enabled' => '', 'header_class' => '', 'content_class' => '', 'container_class' => '', 'nopaging' => false);
		$accordion = is_array($accordion) ?  shortcode_atts($defaults,$accordion) : $defaults;
		if ($is_archive) {  //use table on archive pages
			$start_wrap = '<h3>Accordion Settings</h3><table class="form-table">';
			$end_wrap = '</table>';
			$wrap = 'tr';
		} else {  //use div on page editor
			$start_wrap = '<div class="diy-wrap">';			
			$end_wrap ='</div>';		
			$wrap = 'div';
		}

		print $start_wrap;	
		$this->print_accordion_form_field('enabled', $accordion['enabled'], 'checkbox', array(), $wrap);
		$this->print_accordion_form_field('header_class', $accordion['header_class'], 'text', array('size' => 20), $wrap);
		$this->print_accordion_form_field('content_class', $accordion['content_class'], 'text', array('size' => 20), $wrap);
		$this->print_accordion_form_field('container_class', $accordion['container_class'], 'text', array('size' => 20), $wrap);
		if ($is_archive)
			$this->print_accordion_form_field('nopaging', $accordion['nopaging'], 'checkbox', array(), $wrap);
		print $end_wrap;
	}

	private function print_accordion_form_field($fld, $value, $type, $args, $wrap) {
		$id = 'accordion_'.$fld;
		$name = 'accordion['.$fld.']';	
		$options = array();
		print $this->form_field($id, $name, false, $value, $type, $options, $args, $wrap);
	}

}
