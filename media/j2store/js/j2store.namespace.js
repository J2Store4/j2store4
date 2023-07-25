/**
 * Setup (required for Joomla! 3)
 */
if(typeof(j2store) == 'undefined') {
	var j2store = {};
}
if(typeof(j2store.jQuery) == 'undefined') {
	j2store.jQuery = jQuery.noConflict();
}

if(typeof(j2storeURL) == 'undefined') {
	var j2storeURL = '';
}
