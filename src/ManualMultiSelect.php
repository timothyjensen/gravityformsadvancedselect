<?php

namespace GravityFormsAdvancedSelect;

/**
 * PostMultiSelect class.
 */
class ManualMultiSelect extends AbstractMultiSelect {

	/**
	 * @var string The field type.
	 */
	public static $field_type = 'manual_multiselect';

	public function __construct( $data = [] ) {
		parent::__construct( $data );

		$this->plugins = array_diff( (array) $this->plugins, [ 'virtual_scroll' ] );
	}

	/**
	 * Return the field title, for use in the form editor.
	 *
	 * @return string
	 */
	public function get_form_editor_field_title() {
		return esc_attr__( 'Manual Multi Select', 'gravityformsadvancedselect' );
	}

	/**
	 * The settings which should be available on the field in the form editor.
	 *
	 * @return array
	 */
	public function get_form_editor_field_settings() {
		return [
			'choices_setting',
			...array_diff( parent::get_form_editor_field_settings(), [ 'max_options_setting' ] ),
		];
	}
}
