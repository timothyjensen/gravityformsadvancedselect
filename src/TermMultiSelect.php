<?php

namespace GravityFormsAdvancedSelect;

/**
 * TermMultiSelect class.
 */
class TermMultiSelect extends AbstractMultiSelect {

	/**
	 * @var string $type The field type.
	 */
	public static $field_type = 'term_multiselect';

	/**
	 * Return the field title, for use in the form editor.
	 *
	 * @return string
	 */
	public function get_form_editor_field_title() {
		return esc_attr__( 'Term Multi Select', 'gravityformsadvancedselect' );
	}

	/**
	 * The settings which should be available on the field in the form editor.
	 *
	 * @return array
	 */
	function get_form_editor_field_settings() {
		return array_merge( parent::get_form_editor_field_settings(), [ 'include_taxonomies_setting' ] );
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
            
			jQuery(document).on('gform_load_field_settings', function (event, field, form) {
                if ( '$multiselect_field_type' !== field.type ) {
                    return;
                }
                
                jQuery('#include_taxonomies_setting').val(typeof field.selectedTaxonomies == 'undefined' ? '' : field.selectedTaxonomies)
                
                try {
			        new TomSelect('#include_taxonomies_setting', { plugins: [ 'remove_button' ] } );
                } catch (e) {
					document.getElementById('include_taxonomies_setting').tomselect?.sync();
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
		if ( empty ( $this->selectedTaxonomies ) ) {
			return parent::get_choices( $value );
		}

		$dropdown_args = [
			'echo'         => false,
			'hide_empty'   => false,
			'hierarchical' => true,
			'orderby'      => 'name',
			'selected'     => '', // Empty due to Walker not able to handle array values. The value is set via JS.
			'show_count'   => false,
			'value_field'  => 'term_id',
		];

		$selected_terms = array_map( 'get_term', array_filter( (array) $value ) );

		$terms = get_terms( [
			'taxonomy'   => $this->selectedTaxonomies,
			'number'     => $this->get_pagination_per_page( 0 ),
			'hide_empty' => false,
		] );

		$terms = array_merge( $selected_terms, $terms );

		return walk_category_dropdown_tree( $terms, 0, $dropdown_args );
	}

	/**
	 * Format term IDs as term slugs for better readability.
	 *
	 * @param $value
	 * @param $currency
	 * @param $use_text
	 * @param $format
	 * @param $media
	 *
	 * @return array|string
	 */
	public function get_value_entry_detail( $value, $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {
		if ( empty( $value ) || ( $format == 'text' && $this->storageType !== 'json' ) ) {
			return $value;
		}

		$items = $this->to_array( $value );

		foreach ( $items as &$item ) {
			$term = get_term( (int) $item );

			if ( ! is_a( $term, \WP_Term::class ) ) {
				continue;
			}

			$item = $term->slug;
		}

		return parent::get_value_entry_detail( $items, $currency, $use_text, $format, $media );
	}

	/**
	 * Returns the merged Tom Select settings for this field type.
	 *
	 * @param mixed $value
	 * @return array
	 */
	public function get_tomselect_settings( $value = '' ) {
		$settings = parent::get_tomselect_settings( $value );

		$settings['searchType']    = 'term';
		$settings['searchSubtype'] = $this->selectedTaxonomies;

		return $settings;
	}
}
