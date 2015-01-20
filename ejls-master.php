<?php
/**
 * @package Structured Data of JSON-LD
 * @version 2.0
 */
/*
Plugin Name: Structured Data of JSON-LD
Plugin URI: http://wordpress.org/plugins/ejls-easy-json-ld-setter/
Description: Set Structured Data of "JSON-LD" to your WebSite.schema type that you can use is "Article","Person","WebSite" and "searchAction".
Author: Hidetaka Okamoto
Version: 2.0
Author URI: http://wp-kyoto.net/
*/
add_action('wp_head','ejls_insert_json_ld');

function ejls_get_article () {
    if (is_page() || is_single()) {
        if (have_posts()) : while (have_posts()) : the_post();
            $contentArr['@type'] = 'Article';
            $contentArr['name'] = get_the_title();
            $contentArr['image'] = wp_get_attachment_url(get_post_thumbnail_id());
            $contentArr['url'] = get_permalink();
            $contentArr['articleBody'] = get_the_content();

            $contentArr['author']['@type'] = 'Person';
            $contentArr['author']['name']  = get_the_author();

            $contentArr['publisher']['@type'] = 'Organization';
            $contentArr['publisher']['name']  = get_bloginfo('name');

        endwhile; endif;
        rewind_posts();
        return $contentArr;
    }
}
function ejls_get_search_Action($homeUrl){
    $contentArr = array(
        "@type"      => "SearchAction",
        "target"     => "{$homeUrl}/?s={search_term}",
        "query-input"=> "required name=search_term"
    );
    return $contentArr;
}

function ejls_insert_json_ld(){
    $homeUrl = get_home_url();

    $contentArr = array(
        "@context" => "http://schema.org",
    );
    if (is_front_page()) {
        $contentArr['@type']            = "WebSite";
        $contentArr['url']              = $homeUrl;
        $contentArr['potentialAction']  = ejls_get_search_Action($homeUrl);
    } elseif (is_page() || is_single()) {
        $contentArr['@graph'] = ejls_get_article();
    }

    $jsonld = json_encode($contentArr, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

    echo '<script type="application/ld+json">';
    echo $jsonld;
    echo '</script>';
}

?>