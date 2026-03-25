<?php

use Zavadil\Common\Client\Imagez\ImagezHttpClient;

defined('ABSPATH') || exit;

const THUMB_WIDTH = 150;
const THUMB_HEIGHT = 200;

function designhg_register_as_attachment(string $thumb_path, string $title) {
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$attachment = [
		'post_mime_type' => 'image/jpeg',
		'post_title' => $title . ' — Flipbook Thumbnail',
		'post_content' => '',
		'post_status' => 'inherit',
	];

	$attach_id = wp_insert_attachment($attachment, $thumb_path);
	if (is_wp_error($attach_id)) {
		return $attach_id;
	}

	$meta = wp_generate_attachment_metadata($attach_id, $thumb_path);
	wp_update_attachment_metadata($attach_id, $meta);

	return $attach_id;
}

function designhg_thumbnail_generate_imagick(string $pdf_path, string $thumb_path): bool {
	try {
		$img = new Imagick();
		$img->setResolution(150, 150);
		$img->readImage($pdf_path . '[0]'); // first page only
		$img->setImageFormat('jpeg');
		$img->setImageCompressionQuality(85);
		$img->thumbnailImage(THUMB_WIDTH, THUMB_HEIGHT, true);
		$img->flattenImages();
		$img->writeImage($thumb_path);
		$img->clear();
		$img->destroy();
		return true;
	} catch (Exception $e) {
		// Log but continue to next method
		error_log('[R3DH] Imagick error: ' . $e->getMessage());
		return false;
	}
}

function designhg_thumbnail_generate_imagez(string $pdf_path, string $thumb_path): bool {
	require_once __DIR__ . '/../vendor/autoload.php';

	try {
		$client = new ImagezHttpClient("https://imagez.zavadil.eu", "25imagez2025secret09");
		$health = $client->uploadFile($pdf_path);
		$url = $client->getResizedImageUrl($health->name, THUMB_WIDTH, THUMB_HEIGHT, "crop", "jpg");
		file_put_contents($thumb_path, file_get_contents($url));
		return true;
	} catch (Exception $e) {
		// Log but continue to next method
		error_log('[R3DH] Imagez error: ' . $e->getMessage() . $e->getTraceAsString());
		return false;
	}
}

function designhg_thumbnail_generate(int $attachment_id, string $title) {
	$pdf_path = get_attached_file($attachment_id);

	if (!$pdf_path || !file_exists($pdf_path)) {
		return new WP_Error('file_not_found', __('PDF file not found on disk.', 'r3d-helper'));
	}

	$upload_dir = wp_upload_dir();
	$thumb_name = 'r3dh-thumb-' . $attachment_id . '-' . time() . '.jpg';
	$thumb_path = trailingslashit($upload_dir['path']) . $thumb_name;

	$generated = false;

	if (extension_loaded('imagick')) {
		$generated = designhg_thumbnail_generate_imagick($pdf_path, $thumb_path);
	}

	if (!$generated) {
		$generated = designhg_thumbnail_generate_imagez($pdf_path, $thumb_path);
	}

	if (!$generated || !file_exists($thumb_path)) {
		return new WP_Error('thumb_failed', __('Could not generate thumbnail from PDF.', 'r3d-helper'));
	}

	return designhg_register_as_attachment($thumb_path, $title);
}


