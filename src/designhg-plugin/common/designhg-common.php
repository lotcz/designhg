<?php

if (!defined('ABSPATH')) {
	exit;
}

const DESIGNHG_ADMIN_CAPABILITY = 'manage_options';

function designhg_can_manage(): bool {
	return current_user_can(DESIGNHG_ADMIN_CAPABILITY);
}
