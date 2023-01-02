<?php

namespace GravityFormsAdvancedSelect;

/**
 * PostMultiSelect class.
 */
class PostMultiSelect extends MultiSelect {

	/**
	 * @var string The field type.
	 */
	public static $field_type = 'post_multiselect';

	/**
	 * Return the field title, for use in the form editor.
	 *
	 * @return string
	 */
	public function get_form_editor_field_title() {
		return esc_attr__( 'Post Multi-select', 'gravityformsadvancedselect' );
	}

	/**
	 * The settings which should be available on the field in the form editor.
	 *
	 * @return array
	 */
	function get_form_editor_field_settings() {
		return array_merge( parent::get_form_editor_field_settings(), [ 'include_post_types_setting' ] );
	}

	/**
	 * The scripts to be included in the form editor.
	 *
	 * @return string
	 */
	public function get_form_editor_inline_script_on_page_render() {
		$parent_script = parent::get_form_editor_inline_script_on_page_render();

		$multiselect_field_type = $this->type;

		return <<<JS
            $parent_script
            
			jQuery(document).bind('gform_load_field_settings', function (event, field, form) {
                if ( '$multiselect_field_type' !== field.type ) {
                    return;
                }         
                
                jQuery('#include_post_types_setting').val(typeof field.selectedPostTypes == 'undefined' ? '' : field.selectedPostTypes)
                
                try {
                   new TomSelect('#include_post_types_setting', { plugins: [ 'remove_button' ] } );
                } catch (e) {
                }
			});
JS;
	}

	/**
	 * Returns the HTML options for the select field.
	 *
	 * @param mixed $value
	 * @return string
	 */
	public function get_choices( $value ) {
		if ( empty ( $this->selectedPostTypes ) ) {
			return parent::get_choices( $value );
		}

		$args = [
			'echo'         => false,
			'hide_empty'   => false,
			'hierarchical' => true,
			'selected'     => $value,
			'value_field'  => 'ID',
		];

		$selected_posts = array_map( 'get_post', array_filter( (array) $value ) );

		$posts = get_posts( [
			'post_type'      => $this->selectedPostTypes,
			'posts_per_page' => $this->maxOptions,
			'orderby'        => 'title',
			'order'          => 'ASC',
		] );

		$posts = array_merge( $selected_posts, $posts );

		return walk_page_dropdown_tree( $posts, 0, $args	);
	}

	/**
	 * Returns the merged Tom Select settings for this field type.
	 *
	 * @param mixed $value
	 * @return array
	 */
	public function get_tomselect_settings( $value = '' ) {
		return array_merge(
			parent::get_tomselect_settings( $value ),
			[
				'searchType'    => 'post',
				'searchSubtype' => $this->selectedPostTypes,
			]
		);
	}
}
