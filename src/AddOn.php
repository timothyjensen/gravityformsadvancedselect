<?php

namespace GravityFormsAdvancedSelect;

use GF_Fields;
use GFAddOn;

class AddOn extends GFAddOn {

	protected $_version = '0.1.0';
	protected $_min_gravityforms_version = '2.5';
	protected $_slug = 'gravityformsadvancedselect';
	protected $_path = 'gravityformsadvancedselect/gravityformsadvancedselect.php';
	protected $_full_path = GF_ADVANCED_SELECT_FILE;
	protected $_title = 'Gravity Forms Advanced Select Add-On';
	protected $_short_title = 'Advanced Select Add-On';

	/**
	 * @var string[]
	 */
	protected static $registered_fields = [];

	/**
	 * @var object $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Returns an instance of this class, and stores it in the $_instance property.
	 *
	 * @return object $_instance An instance of this class.
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public static function register_field( $field_class ) {
		self::$registered_fields[ $field_class::$field_type ] = $field_class;
	}

	/**
	 * @var string[]
	 */
	public static function registered_field_types() {
		return array_keys( self::$registered_fields );
	}

	/**
	 * Include the field early so it is available when entry exports are being performed.
	 */
	public function pre_init() {
		parent::pre_init();

		if ( ! $this->is_gravityforms_supported()
			 || ! class_exists( 'GF_Field' )
		) {
			return;
		}

		foreach ( self::$registered_fields as $field ) {
			GF_Fields::register( new $field() );
		}
	}

	public function init_admin() {
		parent::init_admin();

		add_filter( 'gform_tooltips', [ $this, 'tooltips' ] );
		add_action( 'gform_field_standard_settings', [ $this, 'field_standard_settings' ], 10, 2 );
	}

	/**
	 * Returns the URL for the compiled JS or CSS file.
	 *
	 * @param string $asset_path Asset path relative to the assets directory.
	 * @return string
	 */
	public function asset_url( string $asset_path ): string {
		static $manifest = null;

		$asset_path = '/assets/' . trim( $asset_path, '/' );

		if ( null === $manifest ) {
			$manifest = json_decode( (string) file_get_contents( dirname( GF_ADVANCED_SELECT_FILE ) . '/mix-manifest.json' ), true );
		}

		$versioned_filename = $manifest[ $asset_path ] ?? $asset_path;

		return $this->get_base_url() . $versioned_filename;
	}

	/**
	 * Include script when the form contains this field type.
	 *
	 * @return array
	 */
	public function scripts() {
		$scripts = [
			[
				'handle'  => 'tom_select',
				'src'     => $this->asset_url( 'js/tom-select.js' ),
				'version' => null,
				'deps'    => [],
				'enqueue' => [
					[ 'field_types' => self::registered_field_types() ],
					[ 'admin_page' => [ 'form_editor' ] ],
				],
			],
			[
				'handle'  => 'tom_select_init_frontend',
				'src'     => $this->asset_url( 'js/tom-select-init-frontend.js' ),
				'version' => null,
				'deps'    => [ 'tom_select' ],
				'enqueue' => [
					[ 'field_types' => self::registered_field_types() ],
				],
				'strings' => [
					'restUrl' => rest_url(),
				],
			],
		];

		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * Include my_styles.css when the form contains a 'simple' type field.
	 *
	 * @return array
	 */
	public function styles() {
		$styles = [
			[
				'handle'  => 'tom_select',
				'src'     => $this->asset_url( 'css/tom-select.css' ),
				'version' => null,
				'enqueue' => [
					[ 'field_types' => self::registered_field_types() ],
					[ 'admin_page' => [ 'form_editor' ] ],
				],
			],
		];

		return array_merge( parent::styles(), $styles );
	}


	/**
	 * Adds the tooltips for the fields.
	 *
	 * @param array $tooltips Associative array of tooltips.
	 * @return array
	 */
	public function tooltips( $tooltips ) {
		$tooltips['include_taxonomies_setting'] = sprintf(
			'<h6>%s</h6>%s',
			esc_html__( 'Included Taxonomies', 'gravityformsadvancedselect' ),
			esc_html__( 'Limit options to terms in the chosen taxonomies.', 'gravityformsadvancedselect' )
		);

		$tooltips['include_post_types_setting'] = sprintf(
			'<h6>%s</h6>%s',
			esc_html__( 'Included Post Types', 'gravityformsadvancedselect' ),
			esc_html__( 'Limit options to objects in the chosen post types.', 'gravityformsadvancedselect' )
		);

		$tooltips['max_options_setting'] = sprintf(
			'<h6>%s</h6>%s',
			esc_html__( 'Maximum Options', 'gravityformsadvancedselect' ),
			esc_html__( 'Limit the number of options listed.', 'gravityformsadvancedselect' )
		);

		$tooltips['max_items_setting'] = sprintf(
			'<h6>%s</h6>%s',
			esc_html__( 'Maximum Selected Options', 'gravityformsadvancedselect' ),
			esc_html__( 'Limit the number of selected options.', 'gravityformsadvancedselect' )
		);

		return $tooltips;
	}

	/**
	 * Add the custom settings.
	 *
	 * @param int $position The position the settings should be located at.
	 * @param int $form_id  The ID of the form currently being edited.
	 */
	public function field_standard_settings( $position, $form_id ) {
		if ( 1600 === $position ) {
			$taxonomies = get_taxonomies( [ 'public' => true, 'show_in_rest' => true ], 'objects' );
			$post_types = get_post_types( [ 'public' => true, 'show_in_rest' => true ], 'objects' );
			$ts_plugins = [
				'caret_position'       => 'Caret Position',
				'change_listener'      => 'Change Listener',
				'checkbox_options'     => 'Checkbox Options',
				'clear_button'         => 'Clear Button',
				// 'drag_drop'            => 'Drag \'n Drop', // Excluded due to jQuery dependency.
				'dropdown_header'      => 'Dropdown Header',
				'dropdown_input'       => 'Dropdown Input',
				'input_autogrow'       => 'Input Autogrow',
				'no_active_items'      => 'No Active Items',
				'no_backspace_delete'  => 'No Backspace Delete',
				'optgroup_columns'     => 'Option Group Columns',
				'remove_button'        => 'Remove Button',
				'restore_on_backspace' => 'Restore on Backspace',
				'virtual_scroll'       => 'Virtual Scroll',
			]
			?>
			<li class="placeholder field_setting">
				<label for="placeholder_setting">
					<?php
					esc_html_e( 'Placeholder', 'gravityformsadvancedselect' ); ?>
				</label>
				<input id="placeholder_setting" type="text" class="fieldwidth-1"
				       onchange="setAdvancedMultiSelectSetting('placeholder', jQuery(this).val());"
				       value=""/>
			</li>
			<li class="include_taxonomies_setting field_setting">
				<label for="include_taxonomies_setting">
					<?php
					esc_html_e( 'Taxonomies', 'gravityformsadvancedselect' ); ?>
					<?php
					gform_tooltip( 'include_taxonomies_setting' ) ?>
				</label>
				<select id="include_taxonomies_setting" type="text"
				        class="fieldwidth-1"
				        onchange="setAdvancedMultiSelectSetting('selectedTaxonomies', jQuery(this).val());"
				        multiple>
					<?php foreach ( $taxonomies as $taxonomy ) : ?>
						<option value="<?php echo $taxonomy->name; ?>">
							<?php echo $taxonomy->label; ?>
						</option>
					<?php endforeach; ?>
				</select>
			</li>
			<li class="include_post_types_setting field_setting">
				<label for="include_post_types_setting">
					<?php esc_html_e( 'Post Types', 'gravityformsadvancedselect' ); ?>
					<?php gform_tooltip( 'include_post_types_setting' ); ?>
				</label>
				<select id="include_post_types_setting" type="text"
				        class="fieldwidth-1"
				        onchange="setAdvancedMultiSelectSetting('selectedPostTypes', jQuery(this).val());"
				        multiple>
					<?php foreach ( $post_types as $post_type ) : ?>
						<option value="<?php echo $post_type->name; ?>">
							<?php echo $post_type->label; ?>
						</option>
					<?php endforeach; ?>
				</select>
			</li>
			<li class="max_options_setting field_setting">
				<label for="max_options_setting">
					<?php esc_html_e( 'Max options', 'gravityformsadvancedselect' ); ?>
					<?php gform_tooltip( 'max_options_setting' ) ?>
				</label>
				<input id="max_options_setting" type="number"
				       class="fieldwidth-1"
				       onchange="setAdvancedMultiSelectSetting('maxOptions', jQuery(this).val());"
				       value=""/>
			</li>
			<li class="max_items_setting field_setting">
				<label for="max_items_setting">
					<?php esc_html_e( 'Max selected options', 'gravityformsadvancedselect' ); ?>
					<?php gform_tooltip( 'max_items_setting' ) ?>
				</label>
				<input id="max_items_setting" type="number" class="fieldwidth-1"
				       onchange="setAdvancedMultiSelectSetting('maxItems', jQuery(this).val());"
				       value=""/>
			</li>

			<li class="include_plugins_setting field_setting">
				<label for="include_plugins_setting">
					<?php esc_html_e( 'UI Settings', 'gravityformsadvancedselect' ); ?>
					<?php gform_tooltip( 'include_plugins_setting' ) ?>
				</label>
				<select id="include_plugins_setting" type="text"
				        class="fieldwidth-1"
				        onchange="setAdvancedMultiSelectSetting('plugins', jQuery(this).val());"
				        multiple>
					<?php foreach ( $ts_plugins as $plugin_slug => $plugin_name ) : ?>
						<option value="<?php echo $plugin_slug; ?>">
							<?php echo $plugin_name; ?>
						</option>
					<?php endforeach; ?>
				</select>
			</li>
			<?php
		}
	}
}
