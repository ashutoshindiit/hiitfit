<?php
namespace Bridge\Shortcodes\InteractiveProjectList;

use Bridge\Shortcodes\Lib\ShortcodeInterface;

/**
 * Class InteractiveProjectList
 */
class InteractiveProjectList implements ShortcodeInterface {
	private $base;

	function __construct() {
		$this->base = 'qode_interactive_project_list';

		add_action('vc_before_init', array($this, 'vcMap'));
	}

	/**
	 * Returns base for shortcode
	 * @return string
	 */
	public function getBase() {
		return $this->base;
	}

	public function vcMap() {

		vc_map(array(
			'name'                      => esc_html__('Interactive Project List', 'bridge-core'),
			'base'                      => $this->base,
			'category'                  => 'by QODE',
			'icon'                      => 'icon-wpb-interactive-project-list extended-custom-icon-qode',
			'allowed_container_element' => 'vc_row',
			'params'                    => array(
				array(
					'type'        => 'textfield',
					'param_name'  => 'number_of_items',
					'heading'     => esc_html__( 'Number of Projects Per Page', 'bridge-core' ),
					'description' => esc_html__( 'Set number of items for the project list. Enter -1 to show all.', 'bridge-core' ),
					'value'       => '-1'
				),
				array(
					'type'        => 'autocomplete',
					'param_name'  => 'category',
					'heading'     => esc_html__( 'One-Category Project List', 'bridge-core' ),
					'description' => esc_html__( 'Enter one category slug (leave empty for showing all categories)', 'bridge-core' )
				),
				array(
					'type'        => 'autocomplete',
					'param_name'  => 'selected_projects',
					'heading'     => esc_html__( 'Show Only Projects with Listed IDs', 'bridge-core' ),
					'settings'    => array(
						'multiple'      => true,
						'sortable'      => true,
						'unique_values' => true
					),
					'description' => esc_html__( 'Delimit ID numbers by comma (leave empty for all)', 'bridge-core' )
				),
				array(
					'type'        => 'autocomplete',
					'param_name'  => 'tag',
					'heading'     => esc_html__( 'One-Tag Project List', 'bridge-core' ),
					'description' => esc_html__( 'Enter one tag slug (leave empty for showing all tags)', 'bridge-core' )
				),
				array(
					'type'        => 'dropdown',
					'param_name'  => 'orderby',
					'heading'     => esc_html__( 'Order By', 'bridge-core' ),
					'value'       => array_flip( bridge_qode_get_query_order_by_array() ),
					'save_always' => true
				),
				array(
					'type'        => 'dropdown',
					'param_name'  => 'order',
					'heading'     => esc_html__( 'Order', 'bridge-core' ),
					'value'       => array_flip( bridge_qode_get_query_order_array() ),
					'save_always' => true
				),
				array(
					'type'       => 'dropdown',
					'param_name' => 'title_tag',
					'heading'    => esc_html__( 'Title Tag', 'bridge-core' ),
					'value'      => array_flip( bridge_qode_get_title_tag(true) ),
					'group'      => esc_html__( 'Content Layout', 'bridge-core' )
				),
				array(
					'type'       => 'textfield',
					'heading'    => 'Title Font Size',
					'param_name' => 'title_font_size',
					'group'      => esc_html__( 'Content Layout', 'bridge-core' )
				),
				array(
					'type' => 'colorpicker',
					'heading' => 'Left Section Background Color',
					'param_name' => 'left_section_bg_color',
					'group'      => esc_html__( 'Content Layout', 'bridge-core' )
				),
				array(
					'type' => 'attach_image',
					'heading' => 'Right Section Background Image',
					'param_name' => 'right_section_bg_image',
					'group'      => esc_html__( 'Content Layout', 'bridge-core' )
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Left section widget area'),
					'param_name' => 'left_section_widget',
					'value' => $this->getAllCustomWidgetAreas(),
					'description' => esc_html__( 'Select widget area to display on the left section.', 'bridge-core' )
				),
				array(
					'type' => 'dropdown',
					'heading' => esc_html__( 'Right section widget area'),
					'param_name' => 'right_section_widget',
					'value' => $this->getAllCustomWidgetAreas(),
					'description' => esc_html__( 'Select widget area to display on the right section.', 'bridge-core' )
				)
			)
		));

	}

	public function render($atts, $content = null) {

		$args = array(
			'number_of_items'          => '-1',
			'category'                 => '',
			'selected_projects'        => '',
			'tag'                      => '',
			'orderby'                  => 'date',
			'order'                    => 'ASC',
			'title_tag'                => 'h2',
			'title_font_size'          => '',
			'left_section_bg_color'    => '',
			'right_section_bg_image'   => '',
			'left_section_widget'      => '',
			'right_section_widget'     => ''
		);

		$params = shortcode_atts($args, $atts);
		
		$query_array                        = $this->getQueryArray( $params );
		$query_results                      = new \WP_Query( $query_array );
		$params['query_results'] = $query_results;
		
		$params['title_tag']      = ! empty( $params['title_tag'] ) ? $params['title_tag'] : $args['title_tag'];
		$params['title_styles']   = $this->getTitleStyles( $params );
		$params['left_section_styles']   = $this->getLeftSectionStyles( $params );
		$params['right_section_styles']   = $this->getRightSectionStyles( $params );
		
		$html = bridge_core_get_shortcode_template_part('templates/interactive-project-list', 'interactive-project-list', '', $params);

		return $html;
	}
	
	public function getQueryArray( $params ) {
		$query_array = array(
			'post_status'    => 'publish',
			'post_type'      => 'portfolio_page',
			'posts_per_page' => $params['number_of_items'],
			'orderby'        => $params['orderby'],
			'order'          => $params['order']
		);
		
		if ( ! empty( $params['category'] ) ) {
			$query_array['portfolio-category'] = $params['category'];
		}
		
		$project_ids = null;
		if ( ! empty( $params['selected_projects'] ) ) {
			$project_ids             = explode( ',', $params['selected_projects'] );
			$query_array['orderby'] = 'post__in';
			$query_array['post__in'] = $project_ids;
		}
		
		if ( ! empty( $params['tag'] ) ) {
			$query_array['portfolio-tag'] = $params['tag'];
		}
		
		return $query_array;
	}
	
	private function getTitleStyles( $params ) {
		$styles = array();
		
		if ( ! empty( $params['title_font_size'] ) ) {
			$styles[] = 'font-size: ' . bridge_qode_filter_px( $params['title_font_size'] ) . 'px';
		}
		
		return implode( ';', $styles );
	}
	
	private function getLeftSectionStyles( $params ) {
		$styles = array();
		
		if ( ! empty( $params['left_section_bg_color'] ) ) {
			$styles[] = 'background-color: ' . $params['left_section_bg_color'];
		}
		
		return implode( ';', $styles );
	}
	
	private function getRightSectionStyles( $params ) {
		$styles = array();
		
		if ( ! empty( $params['right_section_bg_image'] ) ) {
			
			$img = wp_get_attachment_image_src($params['right_section_bg_image'], 'full');
			$styles[] = 'background-image: url( ' . $img[0] . ')';
		}
		
		return implode( ';', $styles );
	}
	
	public function getAllCustomWidgetAreas(){
		$custom_sidebars = get_option( 'qode_sidebars' );
		$formatted_array             = array(
			'' => ''
		);
		
		if ( is_array( $custom_sidebars ) && count( $custom_sidebars ) ) {
			foreach ( $custom_sidebars as $custom_sidebar ) {
				$formatted_array[ sanitize_title( $custom_sidebar ) ] = $custom_sidebar;
			}
		}
		
		return $formatted_array;
	}
}