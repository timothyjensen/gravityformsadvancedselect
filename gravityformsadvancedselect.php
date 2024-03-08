<?php

/**
 * Gravity Forms Advanced Select
 *
 * @package     GravityFormsAdvancedSelect
 * @author      Tim Jensen <tim@timjensen.us>
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 *
 * Plugin Name: Gravity Forms Advanced Select
 * Plugin URI:  https://github.com/timothyjensen/gravityformsadvancedselect
 * Description: Advanced multi-select field for Gravity Forms. Supports dynamic options for posts and terms.
 * Version:     0.2.5
 * Requires 	PHP: 7.4
 * Author:      Tim Jensen
 * Author URI:  https://www.timjensen.us
 * Text Domain: gravityformsadvancedselect
 * License:     GPL-2.0-or-later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

namespace GravityFormsAdvancedSelect;

if ( ! \defined( 'ABSPATH' ) ) {
	die;
}

if ( version_compare( PHP_VERSION, '7.4.0', '<' ) ) {
	return;
}

\define( 'GF_ADVANCED_SELECT_FILE', __FILE__ );

require_once __DIR__ . '/init.php';
