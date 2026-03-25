<?php

use Zavadil\Common\Helpers\PathHelper;

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/easy-flip-thumbnail.php';

const DESIGNHG_EASY_FLIP_TEMPLATE_ID = 1;

function designhg_flipbook_exists(): bool {
	return function_exists('real3d_flipbook_admin');
}

function designhg_imageext_exists(): bool {
	return extension_loaded('imagick') || extension_loaded('gd');
}

/** Upload PDF to media library */
function designhg_upload_pdf(array $file, string $title) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	// Use wp_handle_upload which enforces allowed types
	$overrides = ['test_form' => false];
	$upload_result = wp_handle_upload($file, $overrides);

	if (isset($upload_result['error'])) {
		return new WP_Error('upload_failed', $upload_result['error']);
	}

	$filename = $upload_result['file'];
	$file_type = wp_check_filetype(basename($filename), null);

	$attachment = [
		'post_mime_type' => $file_type['type'],
		'post_title' => $title . ' - PDF attachment',
		'post_content' => '',
		'post_status' => 'inherit',
	];

	$attach_id = wp_insert_attachment($attachment, $filename);
	if (is_wp_error($attach_id)) {
		return $attach_id;
	}

	$attach_data = wp_generate_attachment_metadata($attach_id, $filename);
	wp_update_attachment_metadata($attach_id, $attach_data);

	return $attach_id;
}

/** Create Real3D Flipbook */
function designhg_create_flipbook(string $title, string $pdf_url) {

	$real3dflipbooks_ids = get_option('real3dflipbooks_ids');
	if (!$real3dflipbooks_ids) {
		$real3dflipbooks_ids = array();
	}
	$flipbooks = array();
	foreach ($real3dflipbooks_ids as $id) {
		$book = get_option('real3dflipbook_'.$id);
		if ($book) {
			$flipbooks[$id] = $book;
		}
	}

	$highest_id = 0;
	foreach ($real3dflipbooks_ids as $id) {
		if((int)$id > $highest_id) {
			$highest_id = (int)$id;
		}
	}
	$new_id = $highest_id + 1;
	$flipbooks[$new_id] = $flipbooks[DESIGNHG_EASY_FLIP_TEMPLATE_ID];
	$flipbooks[$new_id]["id"] = $new_id;
	$flipbooks[$new_id]["name"] = $title;
	$flipbooks[$new_id]["date"] = current_time( 'mysql' );
	$flipbooks[$new_id]["type"] = 'pdf';
	$flipbooks[$new_id]["pdfUrl"] = $pdf_url;

	add_option('real3dflipbook_'.(string)$new_id, $flipbooks[$new_id]);

	array_push($real3dflipbooks_ids, $new_id);
	update_option('real3dflipbooks_ids', $real3dflipbooks_ids);

	return $new_id;
}

function designhg_build_shortcode(int $flipbook_id): string {
	return sprintf('[real3dflipbook id="%d"]', $flipbook_id);
}

function designhg_create_page(string $title, string $shortcode, int $thumbnail_id) {
	$args = [
		'post_title' => $title,
		'post_content' => $shortcode,
		'post_status' => 'publish',
		'post_type' => 'page',
	];

	$page_id = wp_insert_post($args, true);

	if (!is_wp_error($page_id) && $thumbnail_id) {
		set_post_thumbnail($page_id, $thumbnail_id);
	}

	return $page_id;
}

const CURRENT_THUMB_PATH = ABSPATH . '/titulka/aktualni.jpg';

function designhg_update_frontpage(int $thumbnail_id): void {
	$thumb_file = get_attached_file($thumbnail_id);

	error_log($thumb_file);

	if (file_exists(CURRENT_THUMB_PATH)) {
		$bckDir = PathHelper::getDirectory(CURRENT_THUMB_PATH);
		$bckName = PathHelper::getFileBase(CURRENT_THUMB_PATH) . '-backup-' . date('Y-m-d');
		$bckExt = PathHelper::getFileExt(CURRENT_THUMB_PATH);
		$i = 0;
		$bckPath = PathHelper::of($bckDir, $bckName . '.' . $bckExt);
		while (file_exists($bckPath)) {
			$i++;
			$bckPath = PathHelper::of($bckDir, $bckName . '-' . $i . '.' . $bckExt);
		}
		error_log($bckPath);
		copy(CURRENT_THUMB_PATH, $bckPath);
	}

	copy($thumb_file, CURRENT_THUMB_PATH);
}

/**
 Main method that will process PDF, create flipbook and update thumbnail
 */
function designhg_easyflip_run(array $file, string $title) {

	// 1. Upload PDF -------------------------------------------------------
	$attachment_id = designhg_upload_pdf($file, $title);
	if (is_wp_error($attachment_id)) {
		return $attachment_id;
	}

	$pdf_url = wp_get_attachment_url($attachment_id);

	// 2. Create flipbook
	$flipbook_id = designhg_create_flipbook($title, $pdf_url);
	if (is_wp_error($flipbook_id)) {
		return $flipbook_id;
	}

	// 3. Generate thumbnail -----------------------------------------------
	$thumbnail_id = designhg_thumbnail_generate($attachment_id, $title);
	$thumbnail_url = wp_get_attachment_url($thumbnail_id);

	if (is_wp_error($thumbnail_id)) return $thumbnail_id;

	// 4. Create page with shortcode ---------------------------------------
	$shortcode = designhg_build_shortcode($flipbook_id);
	$page_id = designhg_create_page($title, $shortcode, $thumbnail_id ?: 0);
	if (is_wp_error($page_id)) {
		return $page_id;
	}
	$page_url = get_permalink($page_id);

	// 5. Update front page ------------------------------------------------
	if ($thumbnail_id && !is_wp_error($thumbnail_id)) {
		designhg_update_frontpage($thumbnail_id);
	}

	return [
		'title' => $title,
		'flipbook_id' => $flipbook_id,
		'page_id' => $page_id,
		'page_url' => $page_url,
		'shortcode' => $shortcode,
		'thumbnail_url' => $thumbnail_url,
		'attachment_id' => $attachment_id,
	];
}
