<?php
/*
Template Name: Blank With Logo
*/
?>

<!DOCTYPE HTML>
<?php global $blog_id, $current_user, $current_page_name, $digressit_options; ?>
<html <?php language_attributes(); ?>>
<head>
<meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
<title><?php wp_title(''); ?></title>
<?php 
get_currentuserinfo(); 
// Can we remove the framework? What's it used for?
remove_action('wp_head', '_ak_framework_meta_tags');
// There are other things we don't need here. Use remove_action calls to get rid of them.
wp_head(); 
?>
</head>

<body>

<?php 
do_action('add_header_image');
?>
<div style="clear:both;"></div>
<?php
while ( have_posts() ) {
    the_post(); 
    the_content();
}
?>
    
</body>
</html>
