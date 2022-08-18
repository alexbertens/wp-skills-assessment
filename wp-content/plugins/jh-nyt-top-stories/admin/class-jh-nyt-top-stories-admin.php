<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.janushenderson.com/
 * @since      1.0.0
 *
 * @package    Jh_Nyt_Top_Stories
 * @subpackage Jh_Nyt_Top_Stories/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Jh_Nyt_Top_Stories
 * @subpackage Jh_Nyt_Top_Stories/admin
 * @author     Janus Henderson <webtechteam@janushenderson.com>
 */
class Jh_Nyt_Top_Stories_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Jh_Nyt_Top_Stories_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Jh_Nyt_Top_Stories_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/jh-nyt-top-stories-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Jh_Nyt_Top_Stories_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Jh_Nyt_Top_Stories_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/jh-nyt-top-stories-admin.js', array( 'jquery' ), $this->version, false );

	}

}

add_action( 'admin_menu', 'bertex_add_menu_page');

function bertex_add_menu_page() {
    add_menu_page(
        'NYT Top Stories',
        'NYT Top Stories',
        'manage_options',
        'nyt-books-api.php',
        //'get_books_api',
        'run_all_functions',
        'dashicons-book',
        16
    );
}


function run_all_functions() {
	get_nyt_top_stories();
}



// Create Custom Post Type
function nyt_custom_post_type() {

	register_post_type( 'nyt_top_stories', array(
		'labels' => array(
			'name' 			=> __( 'New York Times Top Stories' ),
			'singular_name' => __( 'NYT Top Story' ),
			'menu_name' 	=> __( 'NYT Top Stories' )
		),
		'public' => 'true',
		'hierarchichal' 		=> 'false',
		'taxonomies' 			=> array( 'category', 'post_tag'),
		'exclude_from_search' 	=> true,
		'show_in_menu' 			=> true,
		'show_in_admin_bar'  	=> true,
		'has_archive'			=> true,
        'menu_position'       	=> 5,
		'menu-icon'				=> 'dashicons-shield',
		'supports' => array('title','thumbnail','editor','page-attributes','excerpt'),
	) 
);
}
// Activate Custom Post Type
add_action( 'init', 'nyt_custom_post_type' );

// Get NYT Data API
function get_nyt_top_stories() {

	// These should be in an env file
	$key = 'UFZZUUbnIWQnBnrPTrUF4PCR3em8ARkB';
	$url = "https://api.nytimes.com/svc/topstories/v2/home.json?api-key=$key";

	$stories = json_decode( wp_remote_retrieve_body ( wp_remote_get( $url ) ), true );
	//var_dump( $stories );
	//$stories_data = json_decode( $stories, true );
	//var_dump( $stories_data );
	$stories_results = $stories['results'];
	//var_dump( $stories_results );
	
	foreach ($stories_results as $story) {
		//  Add categories
		//$story_cat = wp_set_post_categories( $story['section'], true );
		$story_cat = wp_create_category( $story['section'] );
		$story_slug = get_category_by_slug($story['section']);
		//$story_title = get_page_by_title( $story['title'], OBJECT, 'nyt_top_stories' );
		$story_title = $story['title'];
		
		$story_img_url =  $story['multimedia']['1']['url'];
		//echo $story_img_url;
		$img_html = '<img src="'.$story_img_url.'"></img>';
		

		$stories_post = array( 
			'post_type'		=> 'nyt_top_stories',
			'post_title' 	=> $story_title,
			'post_content'	=> $img_html,
			'post_excerpt'	=> $story['abstract'],
			'post_date'		=> $story['published_date'],
			'meta_input'	=> array(
					'URL'		=> $story['url'],
					'byline'	=> $story['byline']
			),
			'post_category'	=> array($story_slug->term_id),
			'tags_input'	=> $story['des_facet'],
			'post_status'	=> 'publish'
		); 
		
		//var_dump($stories_post);
		
		
		if ( post_exists($story_title,'','','','') == NULL ){
			$story_post = wp_insert_post( $stories_post );
			echo 'Post <p style="font-weight:bold;"> "'. $story_title.'"
			
			
			added.</p>' . '<br>';
		}
		else {
			echo "Post already exists" . "<br>";
		}
	
		
		
	}
}
function disable_wp_auto_p( $content ) {
	if ( is_singular( 'page' ) ) {
	  remove_filter( 'the_content', 'wpautop' );
	  remove_filter( 'the_excerpt', 'wpautop' );
	}
	return $content;
  }
  add_filter( 'the_content', 'disable_wp_auto_p', 0 );

// Shortcode

function register_shortcodes() {
	add_shortcode( 'nyt-articles', 'shortcode_nyt_articles');
}

add_action( 'init', 'register_shortcodes');

function shortcode_nyt_articles( $atts ){
	global $wp_query,
		$post;

	$atts = shortcode_atts( array( 
		'cat'		=> '',
		'tag'		=> '',
		'order'		=> 'ASC',
		'orderby'	=> 'post_date'
	), $atts);

	$loop = new WP_Query( array( 
		'posts_per_page'	=> 5,
		'post_type'			=> 'nyt_top_stories',
		'orderby'           => 'post-date',
        'order'             => 'DESC'   
		) 
	);

	if( ! $loop->have_posts() ) {
        return false;
    }
	$output = '';
	while( $loop->have_posts() ) {
		
		//$int_url = the_guid();
        $loop->the_post(); 
		
		$output .= '<li style="padding-bottom:1rem;"><a href="'. get_the_permalink() .'">'. get_the_title() .'</a><br>'.get_post_meta( get_the_ID(), 'byline', true).'</li>';
		
		
		
    }
	return '<ul>'. $output .'</ul>';
	wp_reset_postdata();
	
}

// Need to run cron using wp_schedule_event()

wp_schedule_event( time(), 'hourly', 'get_stories_hourly' );

add_action( 'get_stories_hourly', 'get_nyt_top_stories' );
// Need to add to DB using global $wpdb
