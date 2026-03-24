<?php

if (!defined('ABSPATH')) {
	exit;
}

require_once __DIR__ . '/../common/designhg-common.php';
require_once __DIR__ . '/easy-flip-processor.php';

function designhg_easy_flip_render_page() {
	if (!designhg_can_manage()) {
		wp_die('Unauthorized');
	}
	$last     = designhg_easy_flip_get_last();
	?>
	<div class="wrap r3dh-wrap">
		<h1 class="r3dh-title">
			<span class="dashicons dashicons-book-alt"></span>
			Vložit nové číslo
		</h1>

		<?php if (!designhg_flipbook_exists()) : ?>
		<div class="notice notice-error">
			<p>Je vyžadován plugin Real3D Flipbook!</p>
		</div>
		<?php endif; ?>

		<?php if (!designhg_imageext_exists()) : ?>
		<div class="notice notice-warning">
			<p>Je vyžadován jeden z modulů Imagick nebo GD</p>
		</div>
		<?php endif; ?>


		<div class="r3dh-card">
			<label for="r3dhFileInput">
				<div class="r3dh-upload-area" id="r3dhDropZone">
					<div class="r3dh-upload-icon">
						<span class="dashicons dashicons-upload"></span>
					</div>
					<p class="r3dh-upload-label">Zde vložte soubor PDF s novým číslem</p>
					<p class="r3dh-upload-hint">Pouze soubory PDF</p>
					<input type="file" id="r3dhFileInput" accept=".pdf,application/pdf" hidden>
				</div>
			</label>

			<div class="r3dh-form" id="r3dhForm" style="display:none">
				<div class="r3dh-file-info" id="r3dhFileInfo"></div>

				<div class="r3dh-fields">
					<div class="r3dh-field">
						<label for="r3dhTitle">Titulek</label>
						<input type="text" id="r3dhTitle" placeholder="Název čísla...">
					</div>

					<div class="r3dh-field r3dh-field--page-slug">
						<label for="r3dhSlug"><?php esc_html_e( 'Page slug (optional)', 'r3d-helper' ); ?></label>
						<input type="text" id="r3dhSlug" placeholder="<?php esc_attr_e( 'auto-generated', 'r3d-helper' ); ?>">
					</div>

				</div>

				<button class="button button-primary r3dh-btn-create" id="r3dhCreate" <?php disabled(!designhg_flipbook_exists()); ?>>
					Vytvořit Flipbook
				</button>
			</div>

			<div class="r3dh-progress" id="r3dhProgress" style="display:none">
				<div class="r3dh-spinner"></div>
				<p id="r3dhProgressMsg">Zpracovávám...</p>
			</div>

			<div class="r3dh-result" id="r3dhResult" style="display:none"></div>
		</div>

		<?php if ( ! empty( $last ) ) : ?>
		<div class="r3dh-card r3dh-card--history">
			<h2><?php esc_html_e( 'Last Created Flipbook', 'r3d-helper' ); ?></h2>
			<table class="widefat striped">
				<tr>
					<th><?php esc_html_e( 'Title', 'r3d-helper' ); ?></th>
					<td><?php echo esc_html( $last['title'] ?? '—' ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Flipbook ID', 'r3d-helper' ); ?></th>
					<td><?php echo esc_html( $last['flipbook_id'] ?? '—' ); ?></td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Page URL', 'r3d-helper' ); ?></th>
					<td>
						<?php if ( ! empty( $last['page_url'] ) ) : ?>
							<a href="<?php echo esc_url( $last['page_url'] ); ?>" target="_blank">
								<?php echo esc_url( $last['page_url'] ); ?>
							</a>
						<?php else : ?>—<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th><?php esc_html_e( 'Shortcode', 'r3d-helper' ); ?></th>
					<td><code><?php echo esc_html( $last['shortcode'] ?? '—' ); ?></code></td>
				</tr>
				<?php if ( ! empty( $last['thumbnail_url'] ) ) : ?>
				<tr>
					<th><?php esc_html_e( 'Thumbnail', 'r3d-helper' ); ?></th>
					<td><img src="<?php echo esc_url( $last['thumbnail_url'] ); ?>" style="max-width:160px;height:auto;border:1px solid #ddd;"></td>
				</tr>
				<?php endif; ?>
			</table>
		</div>
		<?php endif; ?>
	</div>
	<?php
}
