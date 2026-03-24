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
			<?php esc_html_e( 'Flipbook Creator', 'r3d-helper' ); ?>
		</h1>

		<?php if (!designhg_flipbook_exists()) : ?>
		<div class="notice notice-error">
			<p><?php esc_html_e( 'Real3D Flipbook plugin is not active. Please install and activate it first.', 'r3d-helper' ); ?></p>
		</div>
		<?php endif; ?>

		<?php if (!designhg_imageext_exists()) : ?>
		<div class="notice notice-warning">
			<p><?php esc_html_e( 'Imagick or GD extension not found. Thumbnail generation from PDF may not work.', 'r3d-helper' ); ?></p>
		</div>
		<?php endif; ?>

		<div class="r3dh-card">
			<div class="r3dh-upload-area" id="r3dhDropZone">
				<div class="r3dh-upload-icon">
					<span class="dashicons dashicons-upload"></span>
				</div>
				<p class="r3dh-upload-label"><?php esc_html_e( 'Drop a PDF here or click to upload', 'r3d-helper' ); ?></p>
				<p class="r3dh-upload-hint"><?php esc_html_e( 'PDF files only', 'r3d-helper' ); ?></p>
				<input type="file" id="r3dhFileInput" accept=".pdf,application/pdf" hidden>
			</div>

			<div class="r3dh-form" id="r3dhForm" style="display:none">
				<div class="r3dh-file-info" id="r3dhFileInfo"></div>

				<div class="r3dh-fields">
					<div class="r3dh-field">
						<label for="r3dhTitle"><?php esc_html_e( 'Flipbook title', 'r3d-helper' ); ?></label>
						<input type="text" id="r3dhTitle" placeholder="<?php esc_attr_e( 'My Flipbook', 'r3d-helper' ); ?>">
					</div>

					<div class="r3dh-field r3dh-field--page-slug">
						<label for="r3dhSlug"><?php esc_html_e( 'Page slug (optional)', 'r3d-helper' ); ?></label>
						<input type="text" id="r3dhSlug" placeholder="<?php esc_attr_e( 'auto-generated', 'r3d-helper' ); ?>">
					</div>

					<div class="r3dh-field r3dh-checkbox">
						<label>
							<input type="checkbox" id="r3dhUpdateFrontpage" checked>
							<?php esc_html_e( 'Update front-page preview thumbnail', 'r3d-helper' ); ?>
						</label>
					</div>
				</div>

				<button class="button button-primary r3dh-btn-create" id="r3dhCreate" <?php disabled(!designhg_flipbook_exists()); ?>>
					<?php esc_html_e( 'Create Flipbook', 'r3d-helper' ); ?>
				</button>
			</div>

			<div class="r3dh-progress" id="r3dhProgress" style="display:none">
				<div class="r3dh-spinner"></div>
				<p id="r3dhProgressMsg"><?php esc_html_e( 'Processing…', 'r3d-helper' ); ?></p>
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
