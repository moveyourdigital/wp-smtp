<?php
/**
 * Filters for PHPMailer
 *
 * @package smtp
 */

namespace SMTP;

/**
 * In every visit to the site settings network, if the options
 * don't exist, create them so we can change in a per site basis.
 *
 * @since 0.2.1
 */
add_action(
	'admin_head',
	function () {
		if ( get_current_screen()->id !== 'site-settings-network' ) {
			return;
		}

		// phpcs:ignore
		$id = isset( $_REQUEST['id'] ) ? (int) $_REQUEST['id'] : 0;

		switch_to_blog( $id );

		if ( null === get_option( 'smtp_host', null ) ) {
			$options = array(
				'smtp_host',
				'smtp_auth',
				'smtp_port',
				'smtp_username',
				'smtp_password',
				'smtp_secure',
				'smtp_from_email',
				'smtp_from_name',
			);
			foreach ( $options as $option ) {
				add_option( $option, '' );
			}
		}

		restore_current_blog();
	}
);
