<?php
/**
 * Elementor widget: the breakfast menu.
 *
 * Gives the breakfast menu a first-class home in the editor, so it can be
 * dropped onto any page or template without anyone editing raw HTML. It
 * renders from babayaga_child_breakfast_menu(), which stays the single
 * source of truth - the widget never holds a copy of the prices.
 *
 * @package BabaYagaChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the widget once Elementor is ready for it.
 *
 * @param \Elementor\Widgets_Manager $widgets_manager Elementor's registry.
 * @return void
 */
function babayaga_child_register_breakfast_widget( $widgets_manager ) {
	if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
		return;
	}

	// The class is declared inside this hook because it must extend a class
	// Elementor only defines once it has loaded; re-entry would redeclare it.
	if ( class_exists( 'BabaYaga_Breakfast_Menu_Widget', false ) ) {
		$widgets_manager->register( new BabaYaga_Breakfast_Menu_Widget() );

		return;
	}

	/**
	 * The breakfast menu as an Elementor widget.
	 */
	class BabaYaga_Breakfast_Menu_Widget extends \Elementor\Widget_Base {

		/**
		 * Machine name.
		 *
		 * @return string
		 */
		public function get_name() {
			return 'babayaga-breakfast-menu';
		}

		/**
		 * Label shown in the editor panel.
		 *
		 * @return string
		 */
		public function get_title() {
			return esc_html__( '101 Grill Breakfast Menu', 'babayaga-child' );
		}

		/**
		 * Panel icon.
		 *
		 * @return string
		 */
		public function get_icon() {
			return 'eicon-post-list';
		}

		/**
		 * Editor category.
		 *
		 * @return array
		 */
		public function get_categories() {
			return array( 'general' );
		}

		/**
		 * Search keywords.
		 *
		 * @return array
		 */
		public function get_keywords() {
			return array( 'menu', 'breakfast', '101', 'grill', 'food' );
		}

		/**
		 * Panel controls.
		 *
		 * The menu itself is not editable here on purpose: prices live in
		 * inc/menu-breakfast.php so they cannot drift between placements.
		 *
		 * @return void
		 */
		protected function register_controls() {
			$this->start_controls_section(
				'content',
				array( 'label' => esc_html__( 'Breakfast menu', 'babayaga-child' ) )
			);

			$this->add_control(
				'show_masthead',
				array(
					'label'        => esc_html__( 'Show masthead', 'babayaga-child' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'default'      => 'yes',
					'description'  => esc_html__( 'The framed 101 Grill heading above the menu.', 'babayaga-child' ),
				)
			);

			$this->add_control(
				'note',
				array(
					'type'            => \Elementor\Controls_Manager::RAW_HTML,
					'raw'             => esc_html__( 'Items and prices are edited in the theme file inc/menu-breakfast.php.', 'babayaga-child' ),
					'content_classes' => 'elementor-descriptor',
				)
			);

			$this->end_controls_section();
		}

		/**
		 * Front-end output.
		 *
		 * @return void
		 */
		protected function render() {
			if ( ! function_exists( 'babayaga_child_breakfast_section' ) ) {
				return;
			}

			$settings = $this->get_settings_for_display();
			$masthead = ! isset( $settings['show_masthead'] ) || 'yes' === $settings['show_masthead'];

			echo '<section class="t101-menu"><div class="t101-border-outer"><div class="t101-border-inner">';

			if ( $masthead ) {
				echo '<header class="t101-header">';
				echo '<div class="t101-est">101 Grill &middot; Agoura Hills</div>';
				echo '<h1>Breakfast</h1>';
				echo '<div class="t101-tagline">Served from 9:00 am</div>';
				echo '</header>';
			}

			echo babayaga_child_breakfast_section( true ); // phpcs:ignore WordPress.Security.EscapingOutput -- builder output, escaped at source.
			echo '</div></div></section>';
		}
	}

	$widgets_manager->register( new BabaYaga_Breakfast_Menu_Widget() );
}
add_action( 'elementor/widgets/register', 'babayaga_child_register_breakfast_widget' );
