<?php
/*
	Plugin Name: Genesis Responsive Slider
	Plugin URI: http://www.studiopress.com
	Description: A responsive featured slider for the Genesis Framework.
	Author: StudioPress
	Author URI: http://www.studiopress.com

	Version: 0.9.5

	Text Domain: genesis-responsive-slider
	Domain Path: /languages

	License: GNU General Public License v2.0 (or later)
	License URI: http://www.opensource.org/licenses/gpl-license.php
*/

/**
 * Props to Rafal Tomal, Nick Croft, Nathan Rice, Ron Rennick, Josh Byers and Brian Gardner for collaboratively writing this plugin.
 */

 /**
 * Thanks to Tyler Smith for creating the awesome jquery FlexSlider plugin - http://flex.madebymufffin.com/.
 */

define( 'GENESIS_RESPONSIVE_SLIDER_SETTINGS_FIELD', 'genesis_responsive_slider_settings' );
define( 'GENESIS_RESPONSIVE_SLIDER_VERSION', '0.9.5' );

add_action( 'after_setup_theme', 'GenesisResponsiveSliderInit', 15 );
/**
 * Loads required files and adds image via Genesis Init Hook
 */
function GenesisResponsiveSliderInit() {

	/** require Genesis */
	if( ! function_exists( 'genesis_get_option' ) )
		return;

	// translation support
	load_plugin_textdomain( 'genesis-responsive-slider', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

	/** hook all frontend slider functions here to ensure Genesis is active **/
	add_action( 'wp_enqueue_scripts', 'genesis_responsive_slider_scripts' );
	add_action( 'wp_print_styles', 'genesis_responsive_slider_styles' );
	add_action( 'wp_head', 'genesis_responsive_slider_head', 1 );
	add_action( 'wp_footer', 'genesis_responsive_slider_flexslider_params' );
	add_action( 'widgets_init', 'genesis_responsive_sliderRegister' );

	/** Include Admin file */
	if ( is_admin() ) require_once( dirname( __FILE__ ) . '/admin.php' );

	/** Add new image size */
	add_image_size( 'slider', ( int ) genesis_get_responsive_slider_option( 'slideshow_width' ), ( int ) genesis_get_responsive_slider_option( 'slideshow_height' ), TRUE );

}

add_action( 'genesis_settings_sanitizer_init', 'genesis_responsive_slider_sanitization' );
/**
 * Add settings to Genesis sanitization
 *
 */
function genesis_responsive_slider_sanitization() {
	genesis_add_option_filter( 'one_zero', GENESIS_RESPONSIVE_SLIDER_SETTINGS_FIELD,
		array(
			'slideshow_arrows',
			'slideshow_excerpt_show',
			'slideshow_title_show',
			'slideshow_loop',
			'slideshow_hide_mobile',
			'slideshow_no_link',
			'slideshow_pager'
		) );
	genesis_add_option_filter( 'no_html', GENESIS_RESPONSIVE_SLIDER_SETTINGS_FIELD,
		array(
			'post_type',
			'posts_term',
			'exclude_terms',
			'include_exclude',
			'post_id',
			'posts_num',
			'posts_offset',
			'orderby',
			'slideshow_timer',
			'slideshow_delay',
			'slideshow_height',
			'slideshow_width',
			'slideshow_effect',
			'slideshow_excerpt_content',
			'slideshow_excerpt_content_limit',
			'slideshow_more_text',
			'slideshow_excerpt_width',
			'location_vertical',
			'location_horizontal',
		) );
}

/**
 * Load the script files
 */
function genesis_responsive_slider_scripts() {

	/** easySlider JavaScript code */
	wp_enqueue_script( 'flexslider', plugins_url('js/jquery.flexslider.js', __FILE__), array( 'jquery' ), GENESIS_RESPONSIVE_SLIDER_VERSION, TRUE );

}

/**
 * Load the CSS files
 */
function genesis_responsive_slider_styles() {

	/** standard slideshow styles */
	wp_register_style( 'slider_styles', plugins_url('style.css', __FILE__), array(), GENESIS_RESPONSIVE_SLIDER_VERSION );
	wp_enqueue_style( 'slider_styles' );

}

/**
 * Loads scripts and styles via wp_head hook.
 */
function genesis_responsive_slider_head() {

		$height = ( int ) genesis_get_responsive_slider_option( 'slideshow_height' );
		$width = ( int ) genesis_get_responsive_slider_option( 'slideshow_width' );

		$slideInfoWidth = ( int ) genesis_get_responsive_slider_option( 'slideshow_excerpt_width' );
		$slideNavTop = ( int ) ( ($height - 60) * .5 );

		$vertical = genesis_get_responsive_slider_option( 'location_vertical' );
		$horizontal = genesis_get_responsive_slider_option( 'location_horizontal' );
		$display = ( genesis_get_responsive_slider_option( 'posts_num' ) >= 2 && genesis_get_responsive_slider_option( 'slideshow_arrows' ) ) ? 'top: ' . $slideNavTop . 'px' : 'display: none';

		$hide_mobile = genesis_get_responsive_slider_option( 'slideshow_hide_mobile' );
		$slideshow_pager = genesis_get_responsive_slider_option( 'slideshow_pager' );

		echo '
		<style type="text/css">
			.slide-excerpt { width: ' . $slideInfoWidth . '%; }
			.slide-excerpt { ' . $vertical . ': 0; }
			.slide-excerpt { '. $horizontal . ': 0; }
			.flexslider { max-width: ' . $width . 'px; max-height: ' . $height . 'px; }
			.slide-image { max-height: ' . $height . 'px; }
		</style>';

		if ( $hide_mobile == 1 ) {
		echo '
		<style type="text/css">
			@media only screen
			and (min-device-width : 320px)
			and (max-device-width : 480px) {
				.slide-excerpt { display: none !important; }
			}
		</style> ';
		}
}

/**
 * Outputs slider script on wp_footer hook.
 */
function genesis_responsive_slider_flexslider_params() {

	$timer = ( int ) genesis_get_responsive_slider_option( 'slideshow_timer' );
	$duration = ( int ) genesis_get_responsive_slider_option( 'slideshow_delay' );
	$effect = genesis_get_responsive_slider_option( 'slideshow_effect' );
	$controlnav = genesis_get_responsive_slider_option( 'slideshow_pager' );
	$directionnav = genesis_get_responsive_slider_option( 'slideshow_arrows' );

	$output = 'jQuery(document).ready(function($) {
				$(".flexslider").flexslider({
					controlsContainer: "#genesis-responsive-slider",
					animation: "' . esc_js( $effect ) . '",
					directionNav: ' . $directionnav . ',
					controlNav: ' . $controlnav . ',
					animationDuration: ' . $duration . ',
					slideshowSpeed: ' . $timer . '
			    });
			  });';

	$output = str_replace( array( "\n", "\t", "\r" ), '', $output );

	echo '<script type=\'text/javascript\'>' . $output . '</script>';
}

/**
 * Registers the slider widget
 */
function genesis_responsive_sliderRegister() {
	register_widget( 'genesis_responsive_sliderWidget' );
}

/** Creates read more link after excerpt */
function genesis_responsive_slider_excerpt_more( $more ) {
	global $post;
	static $read_more = null;

	if ( $read_more === null )
		$read_more = genesis_get_responsive_slider_option( 'slideshow_more_text' );

	if ( !$read_more )
		return '';

	return '&hellip; <a href="'. get_permalink( $post->ID ) . '">' . __( $read_more, 'genesis-responsive-slider' ) . '</a>';
}

/**
 * Slideshow Widget Class
 */
class genesis_responsive_sliderWidget extends WP_Widget {

		function __construct() {
			$widget_ops = array( 'classname' => 'genesis_responsive_slider', 'description' => __( 'Displays a slideshow inside a widget area', 'genesis-responsive-slider' ) );
			$control_ops = array( 'width' => 200, 'height' => 250, 'id_base' => 'genesisresponsiveslider-widget' );
			parent::__construct( 'genesisresponsiveslider-widget', __( 'Genesis - Responsive Slider', 'genesis-responsive-slider' ), $widget_ops, $control_ops );
		}

		function save_settings( $settings ) {
			$settings['_multiwidget'] = 0;
			update_option( $this->option_name, $settings );
		}

		// display widget
		function widget( $args, $instance ) {
			extract( $args );

			echo $before_widget;

			$title = apply_filters( 'widget_title', $instance['title'], $instance, $this->id_base );
			if ( $title )
				echo $before_title . $title . $after_title;

			$term_args = array( );

			if ( 'page' != genesis_get_responsive_slider_option( 'post_type' ) ) {

				if ( genesis_get_responsive_slider_option( 'posts_term' ) ) {

					$posts_term = explode( ',', genesis_get_responsive_slider_option( 'posts_term' ) );

					if ( 'category' == $posts_term['0'] )
						$posts_term['0'] = 'category_name';

					if ( 'post_tag' == $posts_term['0'] )
						$posts_term['0'] = 'tag';

					if ( isset( $posts_term['1'] ) )
						$term_args[$posts_term['0']] = $posts_term['1'];

				}

				if ( !empty( $posts_term['0'] ) ) {

					if ( 'category' == $posts_term['0'] )
						$taxonomy = 'category';

					elseif ( 'post_tag' == $posts_term['0'] )
						$taxonomy = 'post_tag';

					else
						$taxonomy = $posts_term['0'];

				} else {

					$taxonomy = 'category';

				}

				if ( genesis_get_responsive_slider_option( 'exclude_terms' ) ) {

					$exclude_terms = explode( ',', str_replace( ' ', '', genesis_get_responsive_slider_option( 'exclude_terms' ) ) );
					$term_args[$taxonomy . '__not_in'] = $exclude_terms;

				}
			}

			if ( genesis_get_responsive_slider_option( 'posts_offset' ) ) {
				$myOffset = genesis_get_responsive_slider_option( 'posts_offset' );
				$term_args['offset'] = $myOffset;
			}

			if ( genesis_get_responsive_slider_option( 'post_id' ) ) {
				$IDs = explode( ',', str_replace( ' ', '', genesis_get_responsive_slider_option( 'post_id' ) ) );
				if ( 'include' == genesis_get_responsive_slider_option( 'include_exclude' ) )
					$term_args['post__in'] = $IDs;
				else
					$term_args['post__not_in'] = $IDs;
			}

			$query_args = array_merge( $term_args, array(
				'post_type' => genesis_get_responsive_slider_option( 'post_type' ),
				'posts_per_page' => genesis_get_responsive_slider_option( 'posts_num' ),
				'orderby' => genesis_get_responsive_slider_option( 'orderby' ),
				'order' => genesis_get_responsive_slider_option( 'order' ),
				'meta_key' => genesis_get_responsive_slider_option( 'meta_key' )
			) );

			$query_args = apply_filters( 'genesis_responsive_slider_query_args', $query_args );
			add_filter( 'excerpt_more', 'genesis_responsive_slider_excerpt_more' );

?>

		<div id="genesis-responsive-slider">
			<div class="flexslider">
				<ul class="slides">
					<?php
						$slider_posts = new WP_Query( $query_args );
						if ( $slider_posts->have_posts() ) {
							$show_excerpt = genesis_get_responsive_slider_option( 'slideshow_excerpt_show' );
							$show_title = genesis_get_responsive_slider_option( 'slideshow_title_show' );
							$show_type = genesis_get_responsive_slider_option( 'slideshow_excerpt_content' );
							$show_limit = genesis_get_responsive_slider_option( 'slideshow_excerpt_content_limit' );
							$more_text = genesis_get_responsive_slider_option( 'slideshow_more_text' );
							$no_image_link = genesis_get_responsive_slider_option( 'slideshow_no_link' );
						}
						while ( $slider_posts->have_posts() ) : $slider_posts->the_post();
					?>
					<li>

					<?php if ( $show_excerpt == 1 || $show_title == 1 ) { ?>
						<div class="slide-excerpt slide-<?php the_ID(); ?>">
							<div class="slide-background"></div><!-- end .slide-background -->
							<div class="slide-excerpt-border ">
								<?php
									if ( $show_title == 1 ) {
								?>
								<h2><a href="<?php the_permalink() ?>" rel="bookmark"><?php the_title(); ?></a></h2>
								<?php
									}
									if ( $show_excerpt ) {
										if ( $show_type != 'full' )
											the_excerpt();
										elseif ( $show_limit )
											the_content_limit( (int)$show_limit, esc_html( $more_text ) );
										else
											the_content( esc_html( $more_text ) );
									}
								?>
							</div><!-- end .slide-excerpt-border  -->
						</div><!-- end .slide-excerpt -->
					<?php } ?>

						<div class="slide-image">
					<?php
						if ( $no_image_link ) {
					?>
							<img src="<?php genesis_image( 'format=url&size=slider' ); ?>" alt="<?php the_title(); ?>" />
					<?php
						} else {
					?>
							<a href="<?php the_permalink() ?>" rel="bookmark"><img src="<?php genesis_image( 'format=url&size=slider' ); ?>" alt="<?php the_title(); ?>" /></a>
					<?php

						} // $no_image_link
					?>
						</div><!-- end .slide-image -->

					</li>
				<?php endwhile; ?>
				</ul><!-- end ul.slides -->
			</div><!-- end .flexslider -->
		</div><!-- end #genesis-responsive-slider -->

<?php
		echo $after_widget;
		wp_reset_query();
		remove_filter( 'excerpt_more', 'genesis_responsive_slider_excerpt_more' );

		}

		/** Widget options */
		function form( $instance ) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => '') );
			$title = $instance['title'];
?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'genesis-responsive-slider' ); ?> <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></label></p>
<?php
			echo '<p>';
			printf( __( 'To configure slider options, please go to the <a href="%s">Slider Settings</a> page.', 'genesis-responsive-slider' ), menu_page_url( 'genesis_responsive_slider', 0 ) );
			echo '</p>';
		}

		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
			$new_instance = wp_parse_args( (array) $new_instance, array( 'title' => '' ) );
			$instance['title'] = strip_tags( $new_instance['title'] );
			return $instance;
		}

}

/**
 * Used to exclude taxonomies and related terms from list of available terms/taxonomies in widget form().
 *
 * @since 0.9
 * @author Nick Croft
 *
 * @param string $taxonomy 'taxonomy' being tested
 * @return string
 */
function genesis_responsive_slider_exclude_taxonomies( $taxonomy ) {

	$filters = array( '', 'nav_menu' );
	$filters = apply_filters( 'genesis_responsive_slider_exclude_taxonomies', $filters );

	return ( ! in_array( $taxonomy->name, $filters ) );

}

/**
 * Used to exclude post types from list of available post_types in widget form().
 *
 * @since 0.9
 * @author Nick Croft
 *
 * @param string $type 'post_type' being tested
 * @return string
 */
function genesis_responsive_slider_exclude_post_types( $type ) {

	$filters = array( '', 'attachment' );
	$filters = apply_filters( 'genesis_responsive_slider_exclude_post_types', $filters );

	return ( ! in_array( $type, $filters ) );

}

/**
 * Returns Slider Option
 *
 * @param string $key key value for option
 * @return string
 */
function genesis_get_responsive_slider_option( $key ) {
	return genesis_get_option( $key, GENESIS_RESPONSIVE_SLIDER_SETTINGS_FIELD );
}

/**
 * Echos Slider Option
 *
 * @param string $key key value for option
 */
function genesis_responsive_slider_option( $key ) {

	if ( ! genesis_get_responsive_slider_option( $key ) )
		return false;

	echo genesis_get_responsive_slider_option( $key );
}