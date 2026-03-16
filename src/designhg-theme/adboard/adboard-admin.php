<?php

if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/adboard-common.php';

add_action(
	'admin_enqueue_scripts',
	function($hook) {
		wp_enqueue_style(
			'designhg-adboard-style-admin',
			get_stylesheet_directory_uri() . '/adboard/adboard-style-admin.css',
			null,
			filemtime(get_stylesheet_directory() . '/adboard/adboard-style-admin.css')
		);

		/* Enqueue media uploader on CPT edit screen */
		global $post_type;
		if (in_array($hook, ['post.php', 'post-new.php']) && $post_type === 'adboard') {
			wp_enqueue_media();
		}

	}
);

const DHG_ADBOARD_POSITIONS = [
	'sides' => 'Po stranách',
	'full' => 'Celá obrazovka'
];

function dhg_adboard_get_position_label(string $position): string {
	return DHG_ADBOARD_POSITIONS[$position] ?? 'Neznámý';
}

const DHG_ADBOARD_STATES = [
	'active' => 'Aktivní',
	'scheduled' => 'Naplánován',
	'expired' => 'Vypršel',
	'inactive' => 'Není nastaveno datum',
	'no-image' => 'Bez obrázku',
];

function dhg_adboard_get_status_label(string $status): string {
	return DHG_ADBOARD_STATES[$status] ?? 'Neznámý';
}

/**
 * Returns post's adboard state ('active' | 'scheduled' | 'expired' | 'inactive')
 */
function dhg_adboard_get_status(int $post_id): string {
	$image_url = dhg_adboard_get_image_url($post_id);
	if (empty($image_url)) return 'no-image';

	$start = dhg_adboard_get_date_start($post_id);
	$end = dhg_adboard_get_date_end($post_id);

	if (!$start || !$end) return 'inactive';

	$today = current_time('Y-m-d');
	if ($today < $start) return 'scheduled';
	if ($today > $end) return 'expired';

	return 'active';
}

function dhg_adboard_render_status(string $status) {
	?>
	<span class="dhg-status status-<?php echo $status ?>"><?php echo dhg_adboard_get_status_label($status) ?></span>
	<?php
}

/* REGISTER "adboard" CUSTOM POST TYPE */
add_action('init', function () {
	$labels = [
		'name' => __('AdBoardy', 'adboard'),
		'singular_name' => __('AdBoard', 'adboard'),
		'menu_name' => __('AdBoardy', 'adboard'),
		'add_new' => __('Přidat AdBoard', 'adboard'),
		'add_new_item' => __('Přidat AdBoard', 'adboard'),
		'edit_item' => __('Upravit AdBoard', 'adboard'),
		'new_item' => __('Přidat AdBoard', 'adboard'),
		'view_item' => __('Zobrazit AdBoard', 'adboard'),
		'search_items' => __('Hledat AdBoard', 'adboard'),
		'not_found' => __('Žádné výsledky nebyly nalezeny', 'adboard'),
		'not_found_in_trash' => __('Žádné AdBoardy nejsou v Koši', 'adboard'),
	];

	register_post_type('adboard', [
		'labels' => $labels,
		'public' => false,
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_icon' => 'dashicons-megaphone',
		'menu_position' => 25,
		'supports' => ['title'],
		'has_archive' => false,
		'rewrite' => false,
		'capability_type' => 'post',
		'show_in_rest' => false,
	]);
});

/* META BOX — Ad Fields */
add_action('add_meta_boxes', function () {
	add_meta_box(
		'adboard_details',
		__('Vlastnosti AdBoardu', 'adboard'),
		'adboard_render_meta_box',
		'adboard',
		'normal',
		'high'
	);
});

function adboard_render_meta_box($post) {
	wp_nonce_field('adboard_save_meta', 'adboard_nonce');

	$link_url = dhg_adboard_get_link_url($post->ID);
	$image_url = dhg_adboard_get_image_url($post->ID);
	$image_id = dhg_adboard_get_image_id($post->ID);
	$date_start = dhg_adboard_get_date_start($post->ID);
	$date_end = dhg_adboard_get_date_end($post->ID);
	$position = dhg_adboard_get_position($post->ID);
	$status = dhg_adboard_get_status($post->ID);

	?>
	<div class="adboard-meta-box">
		<table>
			<tr>
				<th><label>Stav:</label></th>
				<td>
					<?php echo dhg_adboard_render_status($status); ?>
				</td>
			</tr>
			<tr>
				<th><label for="adboard_link_url"><?php _e('Odkaz', 'adboard'); ?>:</label></th>
				<td>
					<input type="url" id="adboard_link_url" name="adboard_link_url"
						value="<?php echo esc_url($link_url); ?>"
						placeholder="https://inzerent.cz/odkaz"/>
					<p class="description"><?php _e('Kam odkaz reklamy povede', 'adboard'); ?></p>
				</td>
			</tr>
			<tr>
				<th><label><?php _e('Obrázek', 'adboard'); ?>:</label></th>
				<td>
					<input type="hidden" id="adboard_image_id" name="adboard_image_id" value="<?php echo esc_attr($image_id); ?>"/>
					<input type="hidden" id="adboard_image_url" name="adboard_image_url" value="<?php echo esc_url($image_url); ?>"/>

					<div>
						<button type="button" id="adboard_upload_btn" class="button button-secondary">
							<?php _e('Vybrat / nahrát obrázek', 'adboard'); ?>
						</button>
						<button type="button" id="adboard_remove_btn" class="button"
							style="<?php echo $image_url ? '' : 'display:none;'; ?>margin-left:8px;">
							<?php _e('Odstranit', 'adboard'); ?>
						</button>
					</div>

					<div>
						<p class="description">Doporučená velikost: 1920×1080, viz <a href="<?php echo get_stylesheet_directory_uri() ?>/adboard/adboard-manual.pdf">manuál</a>.</p>
					</div>

					<div>
						<?php if ($image_url) : ?>
							<img id="adboard_preview" src="<?php echo esc_url($image_url); ?>" class="preview-thumb"/>
						<?php else : ?>
							<img id="adboard_preview" src="" class="preview-thumb" style="display:none;"/>
						<?php endif; ?>
					</div>

				</td>
			</tr>
			<tr>
				<th><label for="adboard_date_start"><?php _e('Od', 'adboard'); ?>:</label></th>
				<td>
					<input type="date" id="adboard_date_start" name="adboard_date_start"
						value="<?php echo esc_attr($date_start); ?>"/>
				</td>
			</tr>
			<tr>
				<th><label for="adboard_date_end"><?php _e('Do', 'adboard'); ?>:</label></th>
				<td>
					<input type="date" id="adboard_date_end" name="adboard_date_end"
						value="<?php echo esc_attr($date_end); ?>"/>
					<p class="description"><?php _e('AdBoard bude viditelný mezi těmito daty', 'adboard'); ?></p>
				</td>
			</tr>
			<tr>
				<th><label for="adboard_position"><?php _e('Typ', 'adboard'); ?>:</label></th>
				<td>
					<select id="adboard_position" name="adboard_position">
						<option value="sides" <?php selected($position, 'sides'); ?>><?php echo dhg_adboard_get_position_label('sides') ?></option>
						<option value="full" <?php selected($position, 'full'); ?>><?php echo dhg_adboard_get_position_label('full') ?></option>
					</select>
				</td>
			</tr>
		</table>
	</div>

	<script>
		jQuery(function ($) {
			var frame;
			$('#adboard_upload_btn').on('click', function (e) {
				e.preventDefault();
				if (frame) {
					frame.open();
					return;
				}
				frame = wp.media({title: 'Zvolte obrázek pro AdBoard', button: {text: 'Zvolit'}, multiple: false});
				frame.on('select', function () {
					var att = frame.state().get('selection').first().toJSON();
					$('#adboard_image_id').val(att.id);
					$('#adboard_image_url').val(att.url);
					$('#adboard_preview').attr('src', att.url).show();
					$('#adboard_remove_btn').show();
				});
				frame.open();
			});
			$('#adboard_remove_btn').on('click', function () {
				$('#adboard_image_id').val('');
				$('#adboard_image_url').val('');
				$('#adboard_preview').attr('src', '').hide();
				$(this).hide();
			});
		});
	</script>
	<?php
}

/* SAVE META */
add_action(
	'save_post_adboard',
	function ($post_id) {
		if (!isset($_POST['adboard_nonce'])) return;
		if (!wp_verify_nonce($_POST['adboard_nonce'], 'adboard_save_meta')) return;
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
		if (!current_user_can('edit_post', $post_id)) return;

		dhg_adboard_set_link_url($post_id, esc_url_raw($_POST['adboard_link_url']));
		dhg_adboard_set_image_url($post_id, esc_url_raw($_POST['adboard_image_url']));
		dhg_adboard_set_image_id($post_id, absint($_POST['adboard_image_id']));
		dhg_adboard_set_date_start($post_id, sanitize_text_field($_POST['adboard_date_start']));
		dhg_adboard_set_date_end($post_id, sanitize_text_field($_POST['adboard_date_end']));
		dhg_adboard_set_position($post_id, sanitize_text_field($_POST['adboard_position']));
	}
);

/* ADMIN COLUMNS */
add_filter(
	'manage_adboard_posts_columns',
	function ($cols) {
		return [
			'cb' => $cols['cb'],
			'title' => __('Název', 'adboard'),
			'adboard_image' => __('Obrázek', 'adboard'),
			'adboard_link' => __('Odkaz', 'adboard'),
			'adboard_start' => __('Od', 'adboard'),
			'adboard_end' => __('Do', 'adboard'),
			'adboard_pos' => __('Typ', 'adboard'),
			'adboard_status' => __('Stav', 'adboard'),
		];
	}
);

add_action(
	'manage_adboard_posts_custom_column',
	function ($col, $post_id) {
		switch ($col) {
			case 'adboard_image':
				$img = dhg_adboard_get_image_url($post_id);
				echo $img ? '<img src="' . esc_url($img) . '" style="width:80px;height:50px;object-fit:cover;border-radius:4px;">' : '—';
				break;
			case 'adboard_link':
				$url = dhg_adboard_get_link_url($post_id);
				echo $url ? '<a href="' . esc_url($url) . '" target="_blank" rel="noopener" style="max-width:180px;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' . esc_url($url) . '</a>' : '—';
				break;
			case 'adboard_start':
				echo esc_html(dhg_adboard_get_date_start($post_id) ?: '—');
				break;
			case 'adboard_end':
				echo esc_html(dhg_adboard_get_date_end($post_id) ?: '—');
				break;
			case 'adboard_pos':
				$position = dhg_adboard_get_position($post_id);
				echo dhg_adboard_get_position_label($position);
				break;
			case 'adboard_status':
				$status = dhg_adboard_get_status($post_id);
				dhg_adboard_render_status($status);
				break;
		}
	},
	10,
	2
);

