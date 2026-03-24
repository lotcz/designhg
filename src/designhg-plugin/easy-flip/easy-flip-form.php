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
	</div>
	<?php
}
