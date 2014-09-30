<?php
/**
 * @package EJLS Easy Json-ld Setter
 * @version 1.0
 */
/*
Plugin Name: EJLS Easy Json-ld Setter
Plugin URI: http://wordpress.org/plugins/test-use-sparql-for-saigoku-33/
Description: Easy set JSON-ld data on your blog.Now you can set only "article" on schema.org.
Author: Hidetaka Okamoto
Version: 1.0
Author URI: http://wp-kyoto.net/
*/
add_action('wp_head','ejls_insert_json_ld');
function ejls_esc_html($ejlsCnt) {
	return esc_html(str_replace(array("\r\n","\n","\r","\t"), "", strip_tags($ejlsCnt)));
}

function ejls_get_article () {
	if (is_page() || is_single()) {
		if (have_posts()) : while (have_posts()) : the_post();
			$type = 'Article';
			$name = get_the_title();
			$authorType = 'Person';
			$authorName = get_the_author();
			$dataPublished = get_the_date('Y-n-j');
			$image = wp_get_attachment_url(get_post_thumbnail_id());
			$articleBody =ejls_esc_html(get_the_content());
			$url = get_permalink();
			$publisherType = 'Organization';
			$publisherName = get_bloginfo('name');
			$json= '{
					"@type" : "'.$type.'",
					"name" : "'.$name.'",
					"author" : {
						"@type" : "'.$authorType.'",
						"name" : "'.$authorName.'"
					 },
					"datePublished" : "'.$dataPublished.'",
					"image" : "'.$image.'",
					"articleBody" : "'.$articleBody.'",
					"url" : "'.$url.'",
					"publisher" : {
						"@type" : "'.$publisherType.'",
						"name" : "'.$publisherName.'"
					}
					}';
		endwhile; endif;
		rewind_posts();
		return $json;
	}
}
function ejls_get_search_Action(){
	if (is_front_page()) {
	$homeUrl = get_home_url();
	$json = '
	"potentialAction": {
		"@type": "SearchAction",
			"target": "' . $homeUrl . '/?s={search_term}",
			"query-input": "required name=search_term"
	},';
	return $json;
	}
}

function ejls_insert_json_ld(){
	$searchAction = ejls_get_search_Action();
	$article = ejls_get_article();
	$homeUrl = get_home_url();

	$json = '
	<script type="application/ld+json">
	{
		"@context" : "http://schema.org",
		"@type": "WebSite",
		"url": "' . $homeUrl . '",
		' . $searchAction . '
		"@graph" : [
			' . $article . '
		]
	}
	</script>';
	echo $json;
}

// URL Rewrite
// via http://firegoby.jp/archives/5309
	register_activation_hook( __FILE__ , 'ejls_activation_callback' );
	register_deactivation_hook( __FILE__ , 'ejls_deactivation_callback' );

	add_action( 'delete_option', 'ejls_delete_option', 10, 1 );
	add_filter('query_vars', 'ejls_query_vars');
	add_action('template_redirect', 'ejls_template_redirect');

	function ejls_activation_callback() {
		/*
		 * is_plugin_active()'s return is false.
		 */
		add_rewrite_endpoint('json-ld', EP_ROOT);
		update_option( 'ejls_plugin_activated', true );
		flush_rewrite_rules();
	}

	function ejls_deactivation_callback() {
		/*
		 * is_plugin_active()'s return is true.
		 */
		delete_option( 'ejls_plugin_activated' );
		flush_rewrite_rules();
	}

	// delete_option
	function ejls_delete_option($option){
		if ( 'rewrite_rules' === $option && get_option('ejls_plugin_activated') ) { 
			add_rewrite_endpoint( 'json', EP_ROOT );
		}
	}


	function ejls_query_vars($vars) {
		$vars[] = 'json-ld';
		return $vars;
	}


	function ejls_template_redirect() {
		global $wp_query;
		ejls_get_json_ld_header();
		$homeUrl = get_home_url();
		$json = '{
		"@context" : "http://schema.org",
		"@type": "WebSite",
		"url": "' . $homeUrl . '",
		"@graph" : [
		';
		if (isset($wp_query->query['json-ld'])) {
			$json .= ejls_get_wp_query($wp_query);
		}
		$json .= ']}';
		echo $json;
		exit;
	}

	function ejls_get_json_ld_header() {
			$expires = 3600000;
			header('Last-Modified: Fri Jan 01 2010 00:00:00 GMT');
			header('Expires: ' . gmdate('D, d M Y H:i:s T', time() + $expires));
			header('Cache-Control: private, max-age=' . $expires);
			header('Pragma: ');
			header("Content-Type: application/ld+json; charset=utf-8");
	}

	function ejls_get_wp_query($wp_query) {
			$args = array(
			'post_type' =>'post',
			'posts_per_page' => -1,
					'paged' => $paged
			);
			$the_query = new WP_Query( $args );
				if (have_posts()) : while (have_posts()) : the_post();
					$type = 'Article';
					$name = get_the_title();
					$authorType = 'Person';
					$authorName = get_the_author();
					$dataPublished = get_the_date('Y-n-j');
					$image = wp_get_attachment_url(get_post_thumbnail_id());
					$articleBody =ejls_esc_html(get_the_content());
					$url = get_permalink();
					$publisherType = 'Organization';
					$publisherName = get_bloginfo('name');
					$json .= '{
							"@type" : "'.$type.'",
							"name" : "'.$name.'",
							"author" : {
								"@type" : "'.$authorType.'",
								"name" : "'.$authorName.'"
							 },
							"datePublished" : "'.$dataPublished.'",
							"image" : "'.$image.'",
							"articleBody" : "'.$articleBody.'",
							"url" : "'.$url.'",
							"publisher" : {
								"@type" : "'.$publisherType.'",
								"name" : "'.$publisherName.'"
							}
							},';
				endwhile; endif;
		$json .= '{}';
		return $json;

	}
?>