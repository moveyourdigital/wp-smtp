<?php
/**
 * Filters for PHPMailer
 *
 * @package smtp
 */

namespace SMTP;

/**
 * Get option and fall back to constant
 *
 * @param string $field lowercase name of the field.
 * @return string|null
 */
function get_field( $field ) {
	$value = get_option( $field );

	if ( $value ) {
		return $value;
	}

	$field = strtoupper( $field );

	if ( defined( $field ) ) {
		return constant( $field );
	}
}

/**
 * Fires after PHPMailer is initialized.
 *
 * @since 0.2.0
 *
 * @param PHPMailer $phpmailer The PHPMailer instance (passed by reference).
 */
add_action(
	'phpmailer_init',
	function ( $phpmailer ) {
		if ( ! is_object( $phpmailer ) ) {
			$phpmailer = (object) $phpmailer;
		}

		$smtp_enabled = get_field( 'smtp_enabled' );

		if ( $smtp_enabled ) {
			$phpmailer->IsSMTP();
		} else {
			return $phpmailer;
		}

		$smtp_host = get_field( 'smtp_host' );

		if ( $smtp_host ) {
			// phpcs:ignore
			$phpmailer->Host = $smtp_host;
		}

		$smtp_auth = get_field( 'smtp_auth' );

		if ( $smtp_auth ) {
			// phpcs:ignore
			$phpmailer->SMTPAuth = (bool) $smtp_auth;
		}

		$smtp_port = get_field( 'smtp_port' );

		if ( $smtp_port ) {
			// phpcs:ignore
			$phpmailer->Port = (int) $smtp_port;
		}

		$smtp_username = get_field( 'smtp_username' );

		if ( $smtp_username ) {
			// phpcs:ignore
			$phpmailer->Username = $smtp_username;
		}

		$smtp_password = get_field( 'smtp_password' );

		if ( $smtp_password ) {
			// phpcs:ignore
			$phpmailer->Password = $smtp_password;
		}

		$smtp_secure = get_field( 'smtp_secure' );

		if ( $smtp_secure ) {
			// phpcs:ignore
			$phpmailer->SMTPSecure = $smtp_secure;
		}

		$smtp_email_from = get_field( 'smtp_from' );

		if ( $smtp_email_from ) {
			// phpcs:ignore
			$phpmailer->From = $smtp_email_from;
		}

		return $phpmailer;
	},
	10,
	1,
);

/**
 * Filters the email address to send from.
 *
 * @since 0.1.0
 *
 * @param string $from_email Email address to send from.
 *
 * @uses wp_mail_from
 */
add_filter(
	'wp_mail_from',
	function ( $from_email ) {
		$smtp_email_from = get_field( 'smtp_from_email' );

		if ( $smtp_email_from ) {
			return $smtp_email_from;
		}

		return $from_email;
	}
);
