import TomSelect from "tom-select/dist/js/tom-select.complete.js";

/*
const plugins = [
	'caret_position',
	'change_listener',
	'checkbox_options',
	'clear_button',
	// 'drag_drop',
	'dropdown_header',
	'dropdown_input',
	'input_autogrow',
	'no_active_items',
	'no_backspace_delete',
	'optgroup_columns',
	'remove_button',
	'restore_on_backspace',
	'virtual_scroll',
];

plugins.forEach(plugin => TomSelect.define(plugin, require(`tom-select/dist/js/plugins/${plugin}.js`)));
*/

window.TomSelect = TomSelect;
