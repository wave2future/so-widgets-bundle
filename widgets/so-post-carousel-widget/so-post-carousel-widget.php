<?php
/*
Widget Name: Post carousel widget
Description: Gives you a widget to display your posts as a carousel.
Author: Greg Priday
Author URI: http://siteorigin.com
*/

/**
 * Add the carousel image sizes
 */
function sow_carousel_register_image_sizes(){
	add_image_size('sow-carousel-default', 272, 182, true);
}
add_action('init', 'sow_carousel_register_image_sizes');

function sow_carousel_get_next_posts_page() {
	if ( empty( $_REQUEST['_widgets_nonce'] ) || !wp_verify_nonce( $_REQUEST['_widgets_nonce'], 'widgets_action' ) ) return;
	$query = wp_parse_args(
		siteorigin_widget_post_selector_process_query($_GET['query']),
		array(
			'post_status' => 'publish',
			'posts_per_page' => 10,
			'paged' => empty( $_GET['paged'] ) ? 1 : $_GET['paged']
		)
	);

	$posts = new WP_Query($query);
	ob_start();
	while($posts->have_posts()) : $posts->the_post(); ?>
		<li class="sow-carousel-item">
			<div class="sow-carousel-thumbnail">
				<?php if( has_post_thumbnail() ) : $img = wp_get_attachment_image_src(get_post_thumbnail_id(), 'sow-carousel-default'); ?>
					<a href="<?php the_permalink() ?>" style="background-image: url(<?php echo esc_url($img[0]) ?>)">
						<span class="overlay"></span>
					</a>
				<?php else : ?>
					<a href="<?php the_permalink() ?>" class="sow-carousel-default-thumbnail"><span class="overlay"></span></a>
				<?php endif; ?>
			</div>
			<h3><a href="<?php the_permalink() ?>"><?php the_title() ?></a></h3>
		</li>
	<?php endwhile; wp_reset_postdata();
	$result = array( 'html' => ob_get_clean() );
	header('content-type: application/json');
	echo json_encode( $result );

	exit();
}
add_action( 'wp_ajax_sow_carousel_load', 'sow_carousel_get_next_posts_page' );
add_action( 'wp_ajax_nopriv_sow_carousel_load', 'sow_carousel_get_next_posts_page' );

class SiteOrigin_Widget_PostCarousel_Widget extends SiteOrigin_Widget {
	function __construct() {
		parent::__construct(
			'sow-post-carousel',
			__('SiteOrigin Post Carousel', 'siteorigin-widgets'),
			array(
				'description' => __('Display your posts as a carousel.', 'siteorigin-widgets'),
				'help' => 'http://siteorigin.com/widgets-bundle/'
			),
			array(

			),
			array(
				'title' => array(
					'type' => 'text',
					'label' => __('Title', 'siteorigin-widgets'),
				),

				'posts' => array(
					'type' => 'posts',
					'label' => __('Posts query', 'siteorigin-widgets'),
				),
			),
			plugin_dir_path(__FILE__).'../'
		);
	}

	function enqueue_frontend_scripts(){
		$js_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

		wp_enqueue_style('sow-carousel-basic', siteorigin_widget_get_plugin_dir_url('post-carousel') . 'css/style.css', array(), SOW_BUNDLE_VERSION);
		wp_register_script( 'touch-swipe' , plugin_dir_url(SOW_BUNDLE_BASE_FILE). 'base/js/jquery.touchSwipe' . $js_suffix . '.js' , array( 'jquery' ), '1.6.6' );
		wp_enqueue_script('sow-carousel-basic', siteorigin_widget_get_plugin_dir_url('post-carousel') . 'js/carousel' . $js_suffix . '.js', array( 'jquery', 'touch-swipe' ), SOW_BUNDLE_VERSION, true );
	}

	function get_template_name($instance){
		return 'base';
	}

	function get_style_name($instance){
		return false;
	}
}

siteorigin_widget_register('post-carousel', __FILE__);