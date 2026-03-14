<?php

if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/adboard-common.php';

add_action(
	'wp_enqueue_scripts',
	function() {
		wp_enqueue_style(
			'designhg-adboard-style',
			get_stylesheet_directory_uri() . '/adboard/adboard-style-public.css',
			null,
			filemtime(get_stylesheet_directory() . '/adboard/adboard-style-public.css')
		);
	}
);

/**
 * Query active ads and return active by random
 */
function dhg_adboard_select_active_ad(): ?WP_Post {
	$today = current_time('Y-m-d');

	$meta_query = [
		'relation' => 'AND',
		[
			'key' => DHG_ADBOARD_META_DATE_START,
			'value' => $today,
			'compare' => '<=',
			'type' => 'DATE',
		],
		[
			'key' => DHG_ADBOARD_META_DATE_END,
			'value' => $today,
			'compare' => '>=',
			'type' => 'DATE',
		],
		[
			'key' => DHG_ADBOARD_META_IMAGE_URL,
			'compare' => '!=',
			'value' => '',
		],
	];

	$args = [
		'post_type' => 'adboard',
		'post_status' => 'publish',
		'posts_per_page' => 1,
		'meta_query' => $meta_query,
		'orderby' => 'rand',
		'no_found_rows' => true,
	];

	$result = get_posts($args);
	return !empty($result) ? $result[0] : null;
}

$dhg_adboard_current = false;

function dhg_adboard_get_current(): ?WP_Post {
	global $dhg_adboard_current;
	if ($dhg_adboard_current === false) {
		$dhg_adboard_current = dhg_adboard_select_active_ad();
	}
	return $dhg_adboard_current;
}

add_filter(
	'body_class',
	function($classes) {
		$ad = dhg_adboard_get_current();
		if ($ad) {
			$position = dhg_adboard_get_position($ad->ID) ?: 'sides';
			$classes[] = 'adboard-' . $position;
		}
		return $classes;
	}
);

/**
 * Render a single ad as HTML (returns string).
 */
function dhg_adboard_render_ad(WP_Post $ad): string {
	$link = get_post_meta($ad->ID, '_adboard_link_url', true);
	$img = get_post_meta($ad->ID, '_adboard_image_url', true);
	$title = get_the_title($ad);
	$host = $link ? parse_url($link, PHP_URL_HOST) : '';

	if (!$img) return '';

	$img_tag = '<img src="' . esc_url($img) . '" alt="' . esc_attr($title) . '" loading="lazy">';
	$inner = $link
		? '<a href="' . esc_url($link) . '" target="_blank" rel="noopener sponsored">' . $img_tag . '</a>'
		: $img_tag;

	switch ($style) {
		case 'hero':
			return '<div class="adboard-hero">' . $inner . '<span class="ad-label">Advertisement</span></div>';

		case 'inline':
			$body_inner = $link ? '<a href="' . esc_url($link) . '" target="_blank" rel="noopener sponsored">'
				. $img_tag
				. '<div class="inline-body">'
				. '<span class="ad-tag">Sponsored</span>'
				. '<span class="ad-title">' . esc_html($title) . '</span>'
				. '<span class="ad-url">' . esc_html($host) . '</span>'
				. '</div></a>'
				: $img_tag;
			return '<div class="adboard-inline">' . $body_inner . '</div>';

		case 'sidebar':
		default:
			$footer = $host ? '<div class="widget-footer"><span class="ad-tag">Ad</span><span class="ad-cta">' . esc_html($host) . '</span></div>' : '';
			return '<div class="adboard-widget">' . $inner . $footer . '</div>';
	}
}
