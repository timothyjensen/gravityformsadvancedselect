<?php

namespace GravityFormsAdvancedSelect;

use GF_Field_MultiSelect;

/**
 * MultiSelect class.
 */
abstract class MultiSelect extends GF_Field_MultiSelect {

	/**
	 * @var string The field type.
	 */
	public static $field_type = 'gravityformsadvancedselect';

	public function __construct( $data = [] ) {
		parent::__construct( $data );

		$this->type = static::$field_type;
	}

	/**
	 * Assign the field button to the Advanced Fields group.
	 *
	 * @return array
	 */
	public function get_form_editor_button() {
		return [
			'group' => 'advanced_fields',
			'text'  => $this->get_form_editor_field_title(),
		];
	}

	/**
	 * The settings which should be available on the field in the form editor.
	 *
	 * @return array
	 */
	function get_form_editor_field_settings() {
		$common_settings = [ 'max_items_setting', 'max_options_setting', 'placeholder', 'include_plugins_setting' ];

		return [
			...$common_settings,
			...array_diff( parent::get_form_editor_field_settings(), [
				'choices_setting',
				'enable_enhanced_ui_setting',
			] ),
		];
	}

	/**
	 * The scripts to be included in the form editor.
	 *
	 * @return string
	 */
	public function get_form_editor_inline_script_on_page_render() {
		$multiselect_field_type = $this->type;

		return <<<JS
			jQuery(document).bind('gform_load_field_settings', function (event, field, form) {
                if ( '$multiselect_field_type' !== field.type ) {
                    return;
                }

                jQuery('#max_items_setting').val(typeof field.maxItems == "undefined" ? "" : field.maxItems)
                jQuery('#max_options_setting').val(typeof field.maxOptions == "undefined" ? "" : field.maxOptions)
                jQuery('#placeholder_setting').val(typeof field.placeholder == "undefined" ? "" : field.placeholder);
                jQuery('#include_plugins_setting').val(typeof field.plugins == "undefined" ? "" : field.plugins)

                try {
					new TomSelect('#include_plugins_setting', { plugins: [ 'remove_button' ] } );
                } catch (e) {
                    document.getElementById('include_plugins_setting').tomselect.sync();
                }
			});

			function setAdvancedMultiSelectSetting(property, value) {
				SetFieldProperty(property, value); 
				
				RefreshSelectedFieldPreview();
			}
JS;
	}

	/**
	 * Returns the field input markup.
	 *
	 * @param array $form
	 * @param mixed $value
	 * @param array $entry
	 * @return string
	 */
	public function get_field_input( $form, $value = '', $entry = null ) {
		$field_html = parent::get_field_input( $form, $value, $entry );

		$document = new \DOMDocument();
		$decoded  = mb_convert_encoding( $field_html, 'HTML-ENTITIES', 'UTF-8' );

		libxml_use_internal_errors( true );

		$document->loadHTML( $decoded, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED );

		$select = $document->getElementsByTagName( 'select' )->item( 0 );

		if ( ! is_a( $select, \DOMElement::class ) ) {
			return $field_html;
		}

		$classes = $select->getAttribute( 'class' );
		$select->setAttribute( 'class', "$classes gfield_select_tomselect" );
		$select->setAttribute( 'data-ts-settings', json_encode( $this->get_tomselect_settings( $value ) ) );

		return $document->saveHTML();
	}

	/**
	 * Returns the settings for Tom Select.
	 *
	 * @param mixed $value
	 * @return array
	 */
	protected function get_tomselect_settings( $value = '' ) {
		return [
			'maxItems'    => ! empty( $this->maxItems ) ? (int) $this->maxItems : null,
			'maxOptions'  => ! empty( $this->maxOptions ) ? (int) $this->maxOptions : null,
			'items'       => $value,
			'placeholder' => $this->placeholder ?? '',
			'plugins'     => $this->plugins ?? [],
		];
	}
}
