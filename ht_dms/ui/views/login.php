<?php
$stylesheet = get_stylesheet();
if ( $stylesheet === 'ht_dms_theme' ) {
	htdms_theme_header();
}
elseif ( $stylesheet === 'app_starter' ) {
	app_starter_header();
}
else {
	get_header();
}

echo holotree_dms_ui()->login()->output();

if ( $stylesheet === 'ht_dms_theme' ) {
	htdms_theme_footer();
}
elseif ( $stylesheet === 'app_starter' ) {
	app_starter_footer();
}
else {
get_footer();
}

?>
