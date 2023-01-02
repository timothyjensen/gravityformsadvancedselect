<?php

namespace GravityFormsAdvancedSelect;

use GFAddOn;
use GFForms;

\spl_autoload_register( 'GravityFormsAdvancedSelect\autoload_classes' );

function autoload_classes( $class ) {
	$namespace = __NAMESPACE__;

	$should_autoload = \preg_match( "/^$namespace\\\\(?P<class_name>.*)$/", $class, $matches );

	if ( ! $should_autoload ) {
		return;
	}

	$class_name = \str_replace( '\\', '/', $matches['class_name'] );

	include_once __DIR__ . "/src/$class_name.php";
}

\add_action( 'gform_loaded', 'GravityFormsAdvancedSelect\register_addon', 5 );
/**
 * Registers the Gravity Forms Advanced Select addon.
 *
 * @return void
 */
function register_addon() {
	if ( ! \method_exists( 'GFForms', 'include_addon_framework' ) ) {
		return;
	}

	GFForms::include_addon_framework();

	AddOn::register_field( PostMultiSelect::class );
	AddOn::register_field( TermMultiSelect::class );

	\do_action( 'gravityformsadvancedselect_pre_register' );

	GFAddOn::register( AddOn::class );
}
