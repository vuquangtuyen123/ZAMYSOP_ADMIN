<?php

// Simple RBAC helper based on roles in users.ma_role and roles_rows.sql
// Roles: 1 Administrator, 2 Moderator, 3 User (customer)

function current_user() {
	if (session_status() === PHP_SESSION_NONE) session_start();
	return [
		'id' => $_SESSION['user_id'] ?? null,
		'email' => $_SESSION['user_email'] ?? null,
		'name' => $_SESSION['user_name'] ?? null,
		'role_id' => isset($_SESSION['role_id']) ? (int)$_SESSION['role_id'] : null,
		'role' => $_SESSION['role_name'] ?? null,
	];
}

function require_login() {
	$u = current_user();
	if (!$u['id']) {
		header('Location: index.php?c=login&a=login');
		exit();
	}
}

function role_capabilities() {
	return [
		1 => [ // Administrator
			'dashboard.view_all' => true,
			'product.crud' => true,
			'order.manage_all' => true,
			'user.manage_staff_and_customers' => true,
			'discount.crud' => true,
			'inventory.upload' => true,
			'news_banner.crud' => true,
			'comment.moderate' => true,
			'message.manage_all' => true,
			'roles.manage' => true,
			'logout' => true,
		],
		2 => [ // Moderator (Quản trị viên)
			'dashboard.view_assigned' => true,
			'product.edit' => true,
			'order.process_assigned' => true,
			'user.view_customers' => true,
			'discount.create_edit' => true,
			'inventory.upload' => true,
			'news_banner.create_edit' => true,
			'comment.reply' => true,
			'message.reply' => true,
			'logout' => true,
		],
		3 => [ // Customer - no admin access
		]
	];
}

function can($capability) {
	$u = current_user();
	$roleId = $u['role_id'] ?? 0;
	$caps = role_capabilities();
	return !empty($caps[$roleId][$capability]);
}

function require_capability($capability) {
	if (!can($capability)) {
		http_response_code(403);
		echo 'Bạn không có quyền thực hiện chức năng này.';
		exit;
	}
}

?>

