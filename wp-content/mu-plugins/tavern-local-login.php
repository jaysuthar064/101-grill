<?php
/**
 * Plugin Name: Tavern Local Auto Login
 * Description: Local development helper for one-click wp-admin access.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function tavern_local_auto_login_allowed(): bool {
	$host   = strtolower( trim( (string) ( $_SERVER['HTTP_HOST'] ?? '' ) ) );
	$remote = (string) ( $_SERVER['REMOTE_ADDR'] ?? '' );

	return in_array( $host, [ 'localhost:8886', '127.0.0.1:8886' ], true )
		&& in_array( $remote, [ '127.0.0.1', '::1' ], true );
}

/**
 * Resolve the local admin: the configured login if set, otherwise the
 * lowest-ID administrator. Survives database imports that replace users.
 */
function tavern_local_auto_login_user(): ?WP_User {
	$admins = get_users(
		[
			'role'    => 'administrator',
			'orderby' => 'ID',
			'order'   => 'ASC',
			'number'  => 1,
		]
	);

	$user = $admins[0] ?? null;

	return $user instanceof WP_User ? $user : null;
}

add_action(
	'init',
	static function (): void {
		if ( empty( $_GET['tavern_local_login'] ) ) {
			return;
		}

		if ( ! tavern_local_auto_login_allowed() ) {
			status_header( 403 );
			wp_die( 'Local auto-login is only available from localhost.' );
		}

		$expected_token = defined( 'TAVERN_AUTO_LOGIN_TOKEN' ) ? (string) TAVERN_AUTO_LOGIN_TOKEN : '';
		$given_token    = sanitize_text_field( wp_unslash( $_GET['token'] ?? '' ) );

		if ( '' === $expected_token || ! hash_equals( $expected_token, $given_token ) ) {
			status_header( 403 );
			wp_die( 'Invalid local auto-login token.' );
		}

		$user = tavern_local_auto_login_user();

		if ( ! $user instanceof WP_User ) {
			status_header( 403 );
			wp_die( 'No administrator account was found in this database.' );
		}

		wp_set_current_user( (int) $user->ID, $user->user_login );
		wp_set_auth_cookie( (int) $user->ID, true, is_ssl() );
		do_action( 'wp_login', $user->user_login, $user );

		wp_safe_redirect( admin_url() );
		exit;
	},
	1
);
