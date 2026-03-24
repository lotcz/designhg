<?php

if (!defined('ABSPATH')) exit;

const DHG_ADBOARD_META_IMAGE_URL = '_adboard_image_url';

function dhg_adboard_get_image_url(int $post_id): ?string {
	return get_post_meta($post_id, DHG_ADBOARD_META_IMAGE_URL, true);
}

function dhg_adboard_set_image_url(int $post_id, string $url): ?string {
	return update_post_meta($post_id, DHG_ADBOARD_META_IMAGE_URL, $url);
}

const DHG_ADBOARD_META_IMAGE_ID = '_adboard_image_id';

function dhg_adboard_get_image_id(int $post_id): ?int {
	return (int) get_post_meta($post_id, DHG_ADBOARD_META_IMAGE_ID, true);
}

function dhg_adboard_set_image_id(int $post_id, int $id): ?string {
	return update_post_meta($post_id, DHG_ADBOARD_META_IMAGE_ID, $id);
}

const DHG_ADBOARD_META_LINK_URL = '_adboard_link_url';

function dhg_adboard_get_link_url(int $post_id): ?string {
	return get_post_meta($post_id, DHG_ADBOARD_META_LINK_URL, true);
}

function dhg_adboard_set_link_url(int $post_id, string $url): ?string {
	return update_post_meta($post_id, DHG_ADBOARD_META_LINK_URL, $url);
}

const DHG_ADBOARD_META_DATE_START = '_adboard_date_start';

function dhg_adboard_get_date_start(int $post_id): ?string {
	return get_post_meta($post_id, DHG_ADBOARD_META_DATE_START, true);
}

function dhg_adboard_set_date_start(int $post_id, string $date): ?string {
	return update_post_meta($post_id, DHG_ADBOARD_META_DATE_START, $date);
}

const DHG_ADBOARD_META_DATE_END = '_adboard_date_end';

function dhg_adboard_get_date_end(int $post_id): ?string {
	return get_post_meta($post_id, DHG_ADBOARD_META_DATE_END, true);
}

function dhg_adboard_set_date_end(int $post_id, string $date): ?string {
	return update_post_meta($post_id, DHG_ADBOARD_META_DATE_END, $date);
}

const DHG_ADBOARD_META_POSITION = '_adboard_position';

function dhg_adboard_get_position(int $post_id): ?string {
	return get_post_meta($post_id, DHG_ADBOARD_META_POSITION, true);
}

function dhg_adboard_set_position(int $post_id, string $position): ?string {
	return update_post_meta($post_id, DHG_ADBOARD_META_POSITION, $position);
}
