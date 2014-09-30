<?php
/**
 * @package EJLS Easy Json-ld Setter
 * @version 1.0
 */
/*
Plugin Name: EJLS Easy Json-ld Setter
Plugin URI: http://wordpress.org/plugins/ejls-easy-json-ld-setter/
Description: Easy set JSON-ld data on your blog.Now you can set only "article" on schema.org.
Author: Hidetaka Okamoto
Version: 1.0
Author URI: http://wp-kyoto.net/
*/
add_action('wp_head','ejls_insert_json_ld');
function ejls_esc_html($ejlsCnt) {
	return esc_html(str_replace(array("\r\n","\n","\r","\t"), "", strip_tags($ejlsCnt)));
}

function ejls_insert_json_ld () {
	if (is_page() || is_single()) {
		if (have_posts()) : while (have_posts()) : the_post();
			$context = 'http://schema.org';
			$type = 'Article';
			$name = get_the_title();
			$authorType = 'Person';
			$authorName = get_the_author();
			$dataPublished = get_the_date('Y-n-j');
			$image = wp_get_attachment_url(get_post_thumbnail_id());
			$articleSection = ejls_esc_html(get_the_excerpt());
			$articleBody =ejls_esc_html(get_the_content());
			$url = get_permalink();
			$publisherType = 'Organization';
			$publisherName = get_bloginfo('name');
			$json= '"@context" : "'.$context.'",
					"@type" : "'.$type.'",
					"name" : "'.$name.'",
					"author" : {
					  "@type" : "'.$authorType.'",
					  "name" : "'.$authorName.'"
					 },
					"datePublished" : "'.$dataPublished.'",
					"image" : "'.$image.'",
					"articleSection" : "'.$articleSection.'",
					"articleBody" : "'.$articleBody.'",
					"url" : "'.$url.'",
					"publisher" : {
					  "@type" : "'.$publisherType.'",
					  "name" : "'.$publisherName.'"
					}';
			echo '<script type="application/ld+json">{'.$json.'}</script>';
		endwhile; endif;
		rewind_posts();
	}
}

?>