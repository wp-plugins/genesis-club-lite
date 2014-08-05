<?php
class Genesis_Club_Accordion_Admin {
    const CODE = 'genesis-club'; //prefix ID of CSS elements
	const SLUG = 'accordion';

    private static $initialized = false;
    private static $parenthook;
    private static $slug;
    private static $screen_id;

    private static $keys;
	private static $tooltips;
	private static $tips = array(
		'accordion_enabled' => array('heading' => 'Accordion Enabled', 'tip' => 'Click to enable the accordion on this page'),
		'accordion_header_class' => array('heading' => 'Override Header Style', 'tip' => 'Enter a custom class if you want to override the accordion header styling.'),
		'accordion_content_class' => array('heading' => 'Override Content Style', 'tip' => 'Enter a custom class if you want to override the accordion content styling'),
		'accordion_container_class' => array('heading' => 'Override Container Style', 'tip' => 'Enter a custom class if you want to override the accordion container styling'),
		);
	
	public static function init() {		
		self::$parenthook = GENESIS_CLUB_PLUGIN_NAME;
	    self::$slug = self::$parenthook . '-' . self::SLUG;
	    self::$keys = array_keys(self::$tips);
		add_action('admin_menu',array(__CLASS__, 'admin_menu'));
		add_action('load-edit-tags.php', array(__CLASS__, 'load_archive_page'));	
		add_action('edit_term', array(__CLASS__, 'save_archive'), 10, 2 );	
		add_action('do_meta_boxes', array( __CLASS__, 'do_meta_boxes'), 20, 2 );
		add_action('save_post', array( __CLASS__, 'save_postmeta'));
		add_action('load-edit.php', array( 'Genesis_Club_Options', 'add_tooltip_support'));
		add_action('load-post.php', array( 'Genesis_Club_Options', 'add_tooltip_support'));
		add_action('load-post-new.php', array( 'Genesis_Club_Options', 'add_tooltip_support'));
	}

    private static function get_keys(){
		return self::$keys;
	}
	
    private static function get_parenthook(){
		return self::$parenthook;
	}

    public static function get_slug(){
		return self::$slug;
	}
		
    private static function get_screen_id(){
		return self::$screen_id;
	}
	
	public static function enable_screen($show_screen,$screen) {
		if ($screen->id == self::get_screen_id())
			return true;
		else
			return $show_screen;
	}	
	

	public static function admin_menu() {
		self::$screen_id = add_submenu_page(self::get_parenthook(), __('Accordion'), __('Accordion'), 'manage_options', 
			self::get_slug(), array(__CLASS__,'settings_panel'));
		add_action('load-'.self::get_screen_id(), array(__CLASS__, 'load_page'));
	}

	public static function load_page() {
		Genesis_Club_Accordion::add_accordion (array('enabled' => true, 'header_class' => '', 'content_class' => '.accordion-content no-margin')); 
 		add_action ('admin_enqueue_scripts',array(__CLASS__, 'enqueue_admin_styles'));
	}

	public static function load_archive_page() {
		add_action( $_REQUEST['taxonomy'] . '_edit_form', array(__CLASS__, 'archive_panel'), 10, 2 );	
 		add_action ('admin_enqueue_scripts',array(__CLASS__, 'enqueue_tooltip_styles'));		
	}
	
	public static function do_meta_boxes( $post_type, $context) {
		$post_types=get_post_types();
		if ( in_array($post_type, $post_types ) && ('advanced' === $context )) {
			add_meta_box( self::CODE.'-accordion', 'Genesis Club Accordion Settings', array( __CLASS__, 'accordion_panel' ), $post_type, 'advanced', 'low' );
 			add_action ('admin_enqueue_scripts',array(__CLASS__, 'enqueue_tooltip_styles'));		
		}
	}

	public static function enqueue_admin_styles() {
		wp_enqueue_style('genesis-club-admin');
 	}	 	
 	
	public static function enqueue_tooltip_styles() {
		wp_enqueue_style('genesis-club-tooltip' );
 	}

	private static function accordion_section($accordion, $as_table){	
		self::$tooltips = new DIY_Tooltip(self::$tips);
		$defaults = array('enabled' => '', 'header_class' => '', 'content_class' => '', 'container_class' => '');
		$accordion = is_array($accordion) ?  shortcode_atts($defaults,$accordion) : $defaults;
		if ($as_table) {  //use table on archive pages
			$start_wrap = '<h3>Accordion Settings</h3><table class="form-table">';
			$end_wrap = '</table>';		
		} else {  //use div on page editor
			$start_wrap = '<div class="diy-wrap">';			
			$end_wrap ='</div>';		
		}

		print $start_wrap;	
		self::print_form_field($as_table, 'enabled', $accordion['enabled'], 'checkbox');
		self::print_form_field($as_table, 'header_class', $accordion['header_class'], 'text', array('size' => 20));
		self::print_form_field($as_table, 'content_class', $accordion['content_class'], 'text', array('size' => 20));
		self::print_form_field($as_table, 'container_class', $accordion['container_class'], 'text', array('size' => 20));
		print $end_wrap;
	}

	private static function print_form_field($as_table, $fld, $val, $type, $args = array()) {
		$id = 'accordion_'.$fld;
		$name = 'accordion['.$fld.']';	
		$options = array();
		print Genesis_Club_Options::form_field($id, $name, self::$tooltips->tip($id), $val, $type, $options, $args, $as_table ? 'tr': 'p') ;
	}


	public static function accordion_panel($post,$metabox){	 		
		self::accordion_section(Genesis_Club_Accordion::get_accordion('posts', $post->ID), false) ;
	}

	public static function archive_panel($term, $tt_id) {
		self::accordion_section(Genesis_Club_Accordion::get_accordion('terms', $term->term_id), true) ;
    }	
	
	public static function save_archive($term_id, $tt_id) {
		return isset( $_POST['accordion'] ) ?
			Genesis_Club_Accordion::save_accordion('terms', $term_id, (array) $_POST['accordion']) : false;
	}	

	public static function save_postmeta($post_id) {
		return isset( $_POST['accordion'] ) ?  
			Genesis_Club_Accordion::save_accordion('posts', $post_id, (array) $_POST['accordion']) : false;
	}
	
	public static function settings_panel() {
 		$this_url = $_SERVER['REQUEST_URI'];
		$title = sprintf('<h2 class="title">%1$s</h2>', __('Accordion Settings', GENESIS_CLUB_DOMAIN));		
		$screenshot = plugins_url('images/accordion.jpg',dirname(__FILE__));
		$url = admin_url('edit.php');
		$url2 = admin_url('edit-tags.php?taxonomy=category');
		Genesis_Club_Accordion::add_accordion(array('enabled' => true));
		print <<< ADMIN_START
<div class="wrap">
{$title}
<div id="poststuff"><div id="post-body"><div id="post-body-content">
<p class="notice">There are no settings on this page.</p>
<p class="notice">However, links are provided to where you can set up accordions for your FAQs.</p>

<p class="important">For a short single page FAQ go to the <a href="{$url}>?post_type=page">Page Editor</a>, click to edit your FAQ page,
and then scroll down and edit the Accordion section.</p>

<p class="important">For a FAQ that is made up of individual posts in the FAQ category go to the <a href="{$url2}">Category Editor</a>, choose you FAQ category
and then scroll down and edit the Accordion section.</p>

<h2>Help On Accordion Settings</h2>
<p>Below is a annotated screenshot of the <i>Accordion Settings</i>.</p>

<p>Simply click the checkbox to enable the accordion</p>

<p>If you want to add your own styling you have the option to override the CSS class for the header (the question), and the CSS class
for the content (the answer). </p>

<p><img src="{$screenshot}" alt="Screenshot on Accordion Settings" /></p>
<p>If the section does not appear in the Post or Category Editor then click the <i>Screen Options</i> at the top of the page and make 
sure you click the checkboxes to show the Accordion Settings.</p>

<p>For an accordion made from posts in a category, then the plugin uses the post titles as the question, and the post
content as the answers.<p>

<p>For an accordion made on a single page then the page should consist of a &lt;h3&gt; heading for each
question with a single paragraph beneath it for each answer. You can have multi-paragraph answers but in this case to you
need to wrap all the paragraphs that hold the answer in a DIV element.</p>

<h2>Tips On Setting Up A FAQ</h2>

<p>Use a single page for your FAQ when you have relatively few questions and short answers and you do not
envisage your FAQ changing much over time.</p>

<p>Conversely, if you have many questions, long answers or frequent updates
then choosing the FAQ category approach will be better from the point of view of SEO, the user experience, and from 
administration of the FAQ.</p>

<p>Also remember that if you have a lot of frequently asked questions you may want to break it up 
into separate FAQs which can be subcategories. For example, on the <a href="http://www.genesisclub.co">Genesis Club website</a>, 
we have a Genesis FAQ, a Membership FAQ and a WordPress FAQ.</p>

<h2>Example of a FAQ Accordion</h2>

<p>Click the questions below to see an accordion FAQ in action.</p>
<div class="accordion">
<h3>What Is A FAQ?</h3>
<p>A FAQ is a list of frequently asked questions, and the answers.</p>

<h3>What Is An Accordion FAQ?</h3>
<p>It is a way of just displaying the questions, and only exposing each answer when the user clicks the question.</p>

<h3>What Are The Two Ways Of Creating FAQs?</h3>
<ul>
<li>Use a Single Page - Create a page with a title of FAQ, on something similar, and add questions and answers where the question is a h3 header and the answer is a single paragraph.</li>
<li>Use a FAQ Category - create a post category called FAQ, or something similar, and write a post every time you have a question. The post title should be the question, 
and the content of the post is the answer.</li>
</ul>

<h3>But What If I Want Multiple FAQs?</h3>
<p>Not a problem. Either use different categories or sub-categories for each FAQ. You can put FAQ as an entry on the menu,
and use sub menu options for each sub-category FAQ. It's all good!</p>

<h3>I Have Set Up A FAQ But It Is Not FAQing Working!!!</h3>

<p>Calm down dear! Help is at hand. Head over to the <a target="_blank" href="https://www.facebook.com/DIYWebmastery">DIYWebmastery page on Facebook</a> and ask your question. 
Or, if you have an up to date licence you can get priority support at the <a 
href="http://forums.genesisclub.co/forums/genesis-club-pro-support/">Genesis Club Pro Support Forum</a>.</p>  
</div>

<form id="accordion_options" method="post" action="{$this_url}"><p>
ADMIN_START;
		wp_nonce_field(__CLASS__); 
		wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); 
		wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); 
		print ('</p></form></div></div><br class="clear"/></div></div>');
	}
}
