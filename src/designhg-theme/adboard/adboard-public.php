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
		if (!$ad) return $classes;

		$classes[] = 'adboard';
		$position = dhg_adboard_get_position($ad->ID) ?: 'sides';
		$classes[] = 'adboard-' . $position;
		$link = dhg_adboard_get_link_url($ad->ID);
		if ($link) {
			$classes[] = 'adboard-has-link';
		}
		$has_heading = magplus_get_opt('title-wrapper-enable') || !class_exists('ReduxFramework') && !is_single();
		$classes[] = ($has_heading) ? 'has-title-heading' : 'no-title-heading';

		return $classes;
	}
);

