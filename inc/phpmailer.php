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
 * Strip credential values and common auth-exchange patterns from an SMTP debug line.
 *
 * @param string $str       Raw debug line from PHPMailer.
 * @param array  $sensitive Known sensitive values to scrub (username, password).
 * @return string
 */
function redact_smtp_debug( $str, $sensitive ) {
	// Redact any known credential value verbatim, wherever it appears.
	foreach ( $sensitive as $value ) {
		if ( $value !== '' ) {
			$str = str_replace( $value, '[redacted]', $str );
		}
	}

	// Redact base64 payloads sent immediately after AUTH LOGIN/PLAIN username/password prompts,
	// in case PHPMailer's own internal masking doesn't cover a given debug level/path.
	if ( preg_match( '/^(CLIENT -> SERVER:\s*)[A-Za-z0-9+\/=]{8,}$/', trim( $str ) ) ) {
		return '[redacted: auth payload]';
	}

	return $str;
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

		$debug_enabled = defined( 'WP_DEBUG' ) && WP_DEBUG;
		$debug_display = defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY;
		$debug_log     = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;

		if ( $debug_enabled ) {
			$phpmailer->SMTPDebug = 2;
			$phpmailer->Debugoutput = function ( $str, $level ) use ( $debug_display, $debug_log, $phpmailer ) {
				// phpcs:ignore -- reading configured credentials to redact them from debug output
				$sensitive = array_filter( [ $phpmailer->Username, $phpmailer->Password ] );
				$redacted  = redact_smtp_debug( $str, $sensitive );

				if ( $debug_log ) {
					error_log( 'PHPMailer SMTP: ' . $redacted );
				}
				
				if ( $debug_display && ! ( ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) ) {
					echo esc_html( 'PHPMailer SMTP: ' . $redacted ) . "\n";
				}
			};
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
		$smtp_email_from = get_field( 'smtp_from' );
		if ( ! $smtp_email_from ) {
			$smtp_email_from = get_field( 'smtp_from_email' );
		}

		if ( $smtp_email_from ) {
			return $smtp_email_from;
		}

		return $from_email;
	}
);
