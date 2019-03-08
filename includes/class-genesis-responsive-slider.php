<?php
/**
 * Genesis Responsive Slider Class.
 *
 * @package genesis-responsive-slider
 */

/**
 * Genesis Responsive Slider.
 */
class Genesis_Responsive_Slider {
	/**
	 * Constructor.
	 */
	public static function init() {

		// Translation support.
		load_plugin_textdomain( 'genesis-responsive-slider', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );

		/** Hook all frontend slider functions here to ensure Genesis is active. */
		add_action( 'wp_enqueue_scripts', array( 'Genesis_Responsive_Slider', 'genesis_responsive_slider_scripts' ) );
		add_action( 'wp_print_styles', array( 'Genesis_Responsive_Slider', 'genesis_responsive_slider_styles' ) );
		add_action( 'wp_head', array( 'Genesis_Responsive_Slider', 'genesis_responsive_slider_head' ), 1 );
		add_action( 'wp_footer', array( 'Genesis_Responsive_Slider', 'genesis_responsive_slider_flexslider_params' ) );
		add_action( 'widgets_init', array( 'Genesis_Responsive_Slider', 'genesis_responsive_slider_register' ) );

		/** Add new image size */
		add_image_size( 'slider', (int) genesis_get_responsive_slider_option( 'slideshow_width' ), (int) genesis_get_responsive_slider_option( 'slideshow_height' ), true );

		add_action( 'genesis_settings_sanitizer_init', array( 'Genesis_Responsive_Slider', 'genesis_responsive_slider_sanitization' ) );
	}

	/**
	 * Add settings to Genesis sanitization
	 */
	public static function genesis_responsive_slider_sanitization() {
		genesis_add_option_filter(
			'one_zero',
			GENESIS_RESPONSIVE_SLIDER_SETTINGS_FIELD,
			array(
				'slideshow_arrows',
				'slideshow_excerpt_show',
				'slideshow_title_show',
				'slideshow_loop',
				'slideshow_hide_mobile',
				'slideshow_no_link',
				'slideshow_pager',
			)
		);

		genesis_add_option_filter(
			'no_html',
			GENESIS_RESPONSIVE_SLIDER_SETTINGS_FIELD,
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
			)
		);
	}

	/**
	 * Load the script files
	 */
	public static function genesis_responsive_slider_scripts() {

		/** EasySlider JavaScript code */
		wp_enqueue_script( 'flexslider', GENESIS_RESPONSIVE_SLIDER_PLUGIN_URL . '/js/jquery.flexslider.js', array( 'jquery' ), GENESIS_RESPONSIVE_SLIDER_VERSION, true );

	}

	/**
	 * Load the CSS files
	 */
	public static function genesis_responsive_slider_styles() {

		/** Standard slideshow styles */
		wp_register_style( 'slider_styles', GENESIS_RESPONSIVE_SLIDER_PLUGIN_URL . '/style.css', array(), GENESIS_RESPONSIVE_SLIDER_VERSION );
		wp_enqueue_style( 'slider_styles' );

	}

	/**
	 * Loads scripts and styles via wp_head hook.
	 */
	public static function genesis_responsive_slider_head() {

			$height = (int) genesis_get_responsive_slider_option( 'slideshow_height' );
			$width  = (int) genesis_get_responsive_slider_option( 'slideshow_width' );

			$slide_info_width = (int) genesis_get_responsive_slider_option( 'slideshow_excerpt_width' );
			$slide_nav_top    = (int) ( ( $height - 60 ) * .5 );

			$vertical   = genesis_get_responsive_slider_option( 'location_vertical' );
			$horizontal = genesis_get_responsive_slider_option( 'location_horizontal' );
			$display    = ( genesis_get_responsive_slider_option( 'posts_num' ) >= 2 && genesis_get_responsive_slider_option( 'slideshow_arrows' ) ) ? 'top: ' . $slide_nav_top . 'px' : 'display: none';

			$hide_mobile     = genesis_get_responsive_slider_option( 'slideshow_hide_mobile' );
			$slideshow_pager = genesis_get_responsive_slider_option( 'slideshow_pager' );

			echo '
			<style type="text/css">
				.slide-excerpt { width: ' . esc_html( $slide_info_width ) . '%; }
				.slide-excerpt { ' . esc_html( $vertical ) . ': 0; }
				.slide-excerpt { ' . esc_html( $horizontal ) . ': 0; }
				.flexslider { max-width: ' . esc_html( $width ) . 'px; max-height: ' . esc_html( $height ) . 'px; }
				.slide-image { max-height: ' . esc_html( $height ) . 'px; }
			</style>';

		if ( '1' === $hide_mobile ) {
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
	public static function genesis_responsive_slider_flexslider_params() {

		$timer        = (int) genesis_get_responsive_slider_option( 'slideshow_timer' );
		$duration     = (int) genesis_get_responsive_slider_option( 'slideshow_delay' );
		$effect       = genesis_get_responsive_slider_option( 'slideshow_effect' );
		$controlnav   = genesis_get_responsive_slider_option( 'slideshow_pager' );
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

		echo '<script type=\'text/javascript\'>' . wp_kses_post( $output ) . '</script>';
	}

	/**
	 * Registers the slider widget
	 */
	public static function genesis_responsive_slider_register() {
		register_widget( 'Genesis_Responsive_Slider_Widget' );
	}
}

/**
 * Creates read more link after excerpt.
 *
 * @param string $more Content.
 */
function genesis_responsive_slider_excerpt_more( $more ) {
	global $post;
	static $read_more = null;

	if ( null === $read_more ) {
		$read_more = genesis_get_responsive_slider_option( 'slideshow_more_text' );
	}

	if ( ! $read_more ) {
		return '';
	}

	return '&hellip; <a href="' . esc_url( get_permalink( $post->ID ) ) . '">' . $read_more . '</a>';
}
