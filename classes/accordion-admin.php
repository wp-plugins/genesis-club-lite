<?php
class GenesisClubAccordionAdmin {
    const CLASSNAME = 'GenesisClubAccordionAdmin'; //this class
    const CODE = 'genesis-club'; //prefix ID of CSS elements
    const DOMAIN = 'GenesisClub'; //text domain for translation
	const SLUG = 'accordion';

    private static $initialized = false;
    private static $parenthook;
    private static $slug;
    private static $screen_id;

    private static $keys;
	private static $tooltips;
	private static $tips = array(
		'accordion_enabled' => array('heading' => 'Accordion Enabled', 'tip' => 'Click to enable the accordion on this %1$spage'),
		'accordion_header' => array('heading' => 'Override Header Style', 'tip' => 'Enter a custom class if you want to override the accordion header styling.'),
		'accordion_content' => array('heading' => 'Override Content Style', 'tip' => 'Enter a custom class if you want to override the accordion content styling'),
		);

	
	public static function init() {		
		if ( ! GenesisClubOptions::get_option('accordion_disabled')) {
			self::$parenthook = GENESIS_CLUB_PLUGIN_NAME;
	    	self::$slug = self::$parenthook . '-' . self::SLUG;
	    	self::$screen_id = self::$parenthook.'_page_' . self::$slug;
	    	self::$keys = array_keys(self::$tips);
			self::$tooltips = new DIYTooltip(self::$tips);
			add_action('load-edit-tags.php', array(self::CLASSNAME, 'load_archive_page'));	
			add_action('edit_term', array(self::CLASSNAME, 'save_archive'), 10, 2 );	
			add_action('do_meta_boxes', array( self::CLASSNAME, 'do_meta_boxes'), 30, 2 );
			add_action('save_post', array( self::CLASSNAME, 'save_postmeta'));
			add_action('admin_menu',array(self::CLASSNAME, 'admin_menu'));
		}
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
		add_submenu_page(self::get_parenthook(), __('Accordion'), __('Accordion'), 'manage_options', 
			self::get_slug(), array(self::CLASSNAME,'settings_panel'));
		add_action('load-'.self::get_screen_id(), array(self::CLASSNAME, 'load_page'));
	}

	public static function load_page() {
		GenesisClubAccordion::add_accordion (array('enabled' => true, 'header_class' => '', 'content_class' => '')); 
 		add_action ('admin_enqueue_scripts',array(self::CLASSNAME, 'enqueue_admin_styles'));
	}

	public static function load_archive_page() {
		add_action( $_REQUEST['taxonomy'] . '_edit_form', array(self::CLASSNAME, 'archive_panel'), 10, 2 );	
	}
	
	public static function do_meta_boxes( $post_type, $context) {
		$post_types=get_post_types();
		if ( in_array($post_type, $post_types ) && ('advanced' === $context )) {
			add_meta_box( self::CODE.'-accordion', 'Accordion Settings', array( self::CLASSNAME, 'accordion_panel' ), $post_type, 'advanced', 'low' );
 			add_action ('admin_enqueue_scripts',array(self::CLASSNAME, 'enqueue_tooltip_styles'));		
		}
	}

	public static function enqueue_admin_styles() {
		wp_enqueue_style(self::CODE.'-admin', plugins_url('styles/admin.css',dirname(__FILE__)), array(),GENESIS_CLUB_VERSION);
 	}
 	
	public static function enqueue_tooltip_styles() {
		wp_enqueue_style(self::CODE.'-tooltip', plugins_url('styles/tooltip.css',dirname(__FILE__)), array(),GENESIS_CLUB_VERSION);
 	}

	private static function accordion_section($accordion, $as_table){	
		$tip1 = self::$tooltips->tip('accordion_enabled');		
		$tip2 = self::$tooltips->tip('accordion_header');	
		$tip3 = self::$tooltips->tip('accordion_content');	
		if ($accordion) 
			$accordion['enabled'] = array_key_exists('enabled',$accordion) ? 'checked="checked"' : '';
		else
			$accordion = array('enabled' => '', 'header_class' => '', 'content_class' => '');
		if ($as_table){
			$start_table = '<h3>Accordion Settings</h3><table class="form-table">';
			$end_table = '</table>';
			$start_row ='<tr>';		
			$end_row ='</tr>';		
			$start_label ='<th>';		
			$end_label ='</th>';		
			$start_input ='<td>';		
			$end_input ='<p class="description">%1$s</p></td>';		
		} else {
			$start_row = $start_table = $end_table = $start_label = $end_label =$start_input = $end_input ='';			
			$end_row ='<br/>';		
		}
		print $start_table;	
		printf ('%1$s%2$s<label>%3$s</label>%4$s%5$s<input type="checkbox" name="accordion[enabled]" id="accordion_enabled" %6$s value="1" />%7$s%8$s',
			$start_row, $start_label, sprintf(self::$tooltips->label('accordion_enabled',$as_table),$as_table ? 'archive ':'') , $end_label, $start_input, 
				$accordion['enabled'], empty($end_input) ? '' : sprintf($end_input, sprintf(self::$tooltips->text('accordion_enabled'),$as_table ? 'archive ':'')), $end_row);
		printf ('%1$s%2$s<label>%3$s</label>%4$s%5$s<input type="text" name="accordion[header_class]" size="20" value="%6$s" />%7$s%8$s',
			$start_row, $start_label, self::$tooltips->label('accordion_header',$as_table), $end_label, $start_input, 
				$accordion['header_class'], empty($end_input) ? '' : sprintf($end_input,self::$tooltips->text('accordion_header')), $end_row);
		printf ('%1$s%2$s<label>%3$s</label>%4$s%5$s<input type="text" name="accordion[content_class]" size="20" value="%6$s" />%7$s%8$s',
			$start_row, $start_label, self::$tooltips->label('accordion_content',$as_table), $end_label, $start_input, 
				$accordion['content_class'], empty($end_input) ? '' : sprintf($end_input,self::$tooltips->text('accordion_content')), $end_row);
		print $end_table;				
ACCORDION_PANEL;
	}

	public static function accordion_panel($post,$metabox){	 		
		self::accordion_section(GenesisClubAccordion::get_accordion('posts', $post->ID), false) ;
	}

	public static function archive_panel($term, $tt_id) {
		self::accordion_section(GenesisClubAccordion::get_accordion('terms', $term->term_id), true) ;
    }	
	
	public static function save_archive($term_id, $tt_id) {
		return isset( $_POST['accordion'] ) ?
			GenesisClubAccordion::save_accordion('terms', $term_id, (array) $_POST['accordion']) : false;
	}	

	public static function save_postmeta($post_id) {
		return isset( $_POST['accordion'] ) ?  
			GenesisClubAccordion::save_accordion('posts', $post_id, (array) $_POST['accordion']) : false;
	}
	
	static function settings_panel() {
 		$this_url = $_SERVER['REQUEST_URI'];
		$title = sprintf('<h2>%1$s</h2>', __('Accordion Settings', self::DOMAIN));		
		$screenshot = plugins_url('images/accordion.jpg',dirname(__FILE__));
		$url = admin_url('edit.php');
		$url2 = admin_url('edit-tags.php?taxonomy=category');
?>
<div class="wrap">
<?php screen_icon(); echo $title; ?>
<div id="poststuff" class="metabox-holder"><div id="post-body"><div id="post-body-content">
<p class="notice">There are no settings on this page.</p>
<p class="notice">However, links are provided to where you can set up accordions for your FAQs.</p>

<p class="important">For a short single page FAQ go to the <a href="<?php echo $url;?>?post_type=page">Page Editor</a>, click to edit your FAQ page,
and then scroll down and edit the Accordion section.</p>

<p class="important">For a FAQ that is made up of individual posts in the FAQ category go to the <a href="<?php echo $url2;?>">Category Editor</a>, choose you FAQ category
and then scroll down and edit the Accordion section.</p>

<h2>Help On Accordion Settings</h2>
<p>Below is a annotated screenshot of the <i>Accordion Settings</i>.</p>

<p>Simply click the checkbox to enable the accordion</p>

<p>If you want to add your own styling you have the option to override the CSS class for the header (the question), and the CSS class
for the content (the answer). </p>

<p><img src="<?php echo $screenshot;?>" alt="Screenshot on Accordion Settings" /></p>
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

<form id="accordion_options" method="post" action="<?php echo $this_url; ?>">
<p>
<?php wp_nonce_field(self::CLASSNAME); ?>
<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
</p>
</form>
</div></div><br class="clear"/></div></div>
<?php
	}
}
?>