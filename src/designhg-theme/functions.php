<?php

if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/adboard/adboard-admin.php';
require_once __DIR__ . '/adboard/adboard-public.php';

add_action(
	'admin_enqueue_scripts',
	function() {
		wp_enqueue_style(
			'designhg-style',
			get_stylesheet_directory_uri() . '/style.css',
			null,
			filemtime(get_stylesheet_directory() . '/style.css')
		);
	}
);
