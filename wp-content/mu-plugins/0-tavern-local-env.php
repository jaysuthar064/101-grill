<?php
/**
 * Plugin Name: Tavern Local Environment Guard
 * Description: Neutralises host-specific plugins when the site runs on localhost.
 *
 * Why this exists
 * ---------------
 * The Hostinger plugins fatal outside Hostinger's servers:
 *
 *   Typed static property Hostinger\WpHelper\Utils::$apiTokenFile
 *   must not be accessed before initialization
 *
 * The obvious fix is to deactivate them locally, but that state then travels
 * with any database backup and silently disables them on the real host, where
 * they are needed. So instead they stay ACTIVE in the database - correct for
 * production - and this file removes them from the load order only while the
 * request is coming from localhost.
 *
 * The filename starts with 0- so it loads before the Hostinger must-use
 * plugins, which are sorted alphabetically.
 *
 * On the live site every check below is false and this file does nothing at
 * all, so it is safe to ship in a backup.
 *
 * @package BabaYagaChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Is this request being served from a local development host?
 *
 * @return bool
 */
function tavern_is_local_env() {
	static $local = null;

	if ( null !== $local ) {
		return $local;
	}

	$host = strtolower( (string) ( $_SERVER['HTTP_HOST'] ?? '' ) );
	$host = explode( ':', $host )[0];

	$local = in_array( $host, array( 'localhost', '127.0.0.1', '::1' ), true )
		|| ( '' !== $host && str_ends_with( $host, '.test' ) )
		|| ( '' !== $host && str_ends_with( $host, '.local' ) );

	// WP-CLI has no HTTP_HOST unless one is exported; treat it as local too.
	if ( ! $local && defined( 'WP_CLI' ) && WP_CLI && '' === $host ) {
		$local = true;
	}

	return $local;
}

if ( ! tavern_is_local_env() ) {
	return;
}

/**
 * Plugins that only work on their own hosting platform.
 *
 * @return array
 */
function tavern_host_only_plugins() {
	return array(
		'hostinger/hostinger.php',
		'hostinger-ai-assistant/hostinger-ai-assistant.php',
		'hostinger-easy-onboarding/hostinger-easy-onboarding.php',
		'hostinger-reach/hostinger-reach.php',
		'litespeed-cache/litespeed-cache.php',
	);
}

/**
 * Drop those plugins from the active list for this request only.
 *
 * The stored option is untouched, so the database still reports them as
 * active and a backup restored to the live host brings them back enabled.
 *
 * @param mixed $plugins Active plugin list.
 * @return mixed
 */
function tavern_filter_active_plugins( $plugins ) {
	if ( ! is_array( $plugins ) ) {
		return $plugins;
	}

	$blocked = tavern_host_only_plugins();
	$kept    = array();

	foreach ( $plugins as $plugin ) {
		$match = false;

		foreach ( $blocked as $needle ) {
			if ( $plugin === $needle || 0 === strpos( (string) $plugin, dirname( $needle ) . '/' ) ) {
				$match = true;
				break;
			}
		}

		if ( ! $match ) {
			$kept[] = $plugin;
		}
	}

	return $kept;
}
add_filter( 'option_active_plugins', 'tavern_filter_active_plugins', 1 );
add_filter( 'site_option_active_sitewide_plugins', 'tavern_filter_active_plugins', 1 );

/**
 * Keep the host-only plugins in the STORED option.
 *
 * The filter above hides them from this request. If anything then saves the
 * option back - visiting the Plugins screen and toggling anything is enough -
 * WordPress would write the filtered list and permanently drop them from the
 * database, which would then travel to live in the next backup and disable
 * them on the real host.
 *
 * This re-merges them on the way in, so storage always reflects what
 * production needs no matter what happens locally.
 *
 * @param mixed $value New option value.
 * @return mixed
 */
function tavern_keep_host_plugins_stored( $value ) {
	if ( ! is_array( $value ) ) {
		return $value;
	}

	foreach ( tavern_host_only_plugins() as $plugin ) {
		if ( ! in_array( $plugin, $value, true ) ) {
			$value[] = $plugin;
		}
	}

	sort( $value );

	return array_values( $value );
}
add_filter( 'pre_update_option_active_plugins', 'tavern_keep_host_plugins_stored', 99 );
