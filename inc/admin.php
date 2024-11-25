<?php
/**
 * WordPress Admin UI
 *
 * @package smtp
 */

namespace SMTP;

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		if ( 'options-general.php' === $hook ) {
			wp_register_style( 'admin-smtp', plugin_uri( '/css/admin-smtp.css' ), false, plugin_version() );
			wp_enqueue_script( 'admin-smtp', plugin_uri( '/js/admin-smtp.js' ), array( 'jquery' ), plugin_version(), true );
			wp_enqueue_style( 'admin-smtp' );
		}
	}
);

add_action(
	'admin_init',
	function () {
		add_settings_section(
			'smtp_settings_section',
			__( 'Email settings', 'smtp' ),
			function () {
				echo '<p>' . esc_html( __( 'By default, WordPress sends emails without authentication, which can increase the chance of them being marked as spam. To improve deliverability, it is recommended to use an SMTP server.', 'smtp' ) ) . '</p>';
			},
			'general',
			array(
				'section_class'  => 'smtp_settings',
				'before_section' => '<div style="margin-top: 40px;"></div>',
			),
		);

		$settings = array(
			'smtp_enabled'  => array(
				'type'          => 'boolean',
				'label'         => __( 'Send emails using', 'smtp' ),
				'description'   => __( 'SMTP delivery is enabled.', 'smtp' ),
				'show_in_rest'  => true,
				'html_callback' => function ( $args ) {
					?>
	<fieldset>
		<legend class="screen-reader-text">
		<span><?php echo esc_html( $args['label'] ); ?></span></legend>
		<p>
			<label>
				<input name="smtp_enabled" type="radio" value="0" class="tog" <?php echo esc_attr( checked( 0, get_option( 'smtp_enabled', '0' ), false ) ); ?> />
					<?php esc_attr_e( 'Default unauthenticated method', 'smtp' ); ?>
			</label>
		</p>
		<p>
			<label>
				<input name="smtp_enabled" type="radio" value="1" class="tog" <?php echo esc_attr( checked( 1, get_option( 'smtp_enabled' ), false ) ); ?> />
					<?php esc_attr_e( 'External SMTP server (recommended)', 'smtp' ); ?>
			</label>
		</p>
	</fieldset>
					<?php
				},
			),

			'smtp_host'     => array(
				'type'              => 'string',
				'label'             => __( 'Server address', 'smtp' ),
				'label_for'         => 'smtp-server',
				'html_callback'     => function ( $args ) {
					?>
	<input name="smtp_host" id="<?php echo esc_attr( $args['label_for'] ); ?>" type="text" placeholder="smtp.example.com" class="regular-text code smtp-input" value="<?php echo esc_attr( get_option( 'smtp_host' ) ); ?>" />
	<label for="smtpserver_port"><?php esc_html_e( 'Port', 'smtp' ); ?></label>
	<input name="smtp_port" type="text" id="smtpserver_port" placeholder="25" class="small-text smtp-input" value="<?php echo esc_attr( get_option( 'smtp_port' ) ); ?>" />
					<?php
				},
				'sanitize_callback' => 'sanitize_text_field',
			),

			'smtp_port'     => array(
				'type'              => 'integer',
				'sanitize_callback' => 'intval',
			),

			'smtp_auth'     => array(
				'type'          => 'boolean',
				'label'         => __( 'Authentication', 'smtp' ),
				'label_for'     => 'smtp-auth',
				'html_callback' => function ( $args ) {
					?>
	<input name="smtp_auth" id="<?php echo esc_attr( $args['label_for'] ); ?>" type="checkbox" value="1" class="tog smtp-input" <?php echo checked( 1, get_option( 'smtp_auth' ), false ); ?> />
	<label for="<?php echo esc_attr( $args['label_for'] ); ?>"><?php esc_html_e( 'Authenticate using username and password', 'smtp' ); ?></label>
					<?php
				},
			),

			'smtp_username' => array(
				'type'              => 'string',
				'label'             => __( 'Username', 'smtp' ),
				'sanitize_callback' => 'sanitize_text_field',
				'label_for'         => 'smtp-username',
				'html_callback'     => function ( $args ) {
					?>
	<input name="smtp_username" type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" placeholder="login@example.com" class="regular-text <?php echo is_rtl() ? 'rtl' : 'ltr'; ?> smtp-input" value="<?php echo esc_attr( get_option( 'smtp_username' ) ); ?>" />
					<?php
				},
			),

			'smtp_password' => array(
				'type'              => 'string',
				'label'             => __( 'Password', 'smtp' ),
				'sanitize_callback' => 'sanitize_text_field',
				'label_for'         => 'smtp-password',
				'class'             => 'smtpserver-pass-wrap',
				'html_callback'     => function ( $args ) {
					?>
	<input type="hidden" value=" "><!-- #24364 workaround -->
	<span class="wp-pwd">
		<input type="password" name="smtp_password" id="<?php echo esc_attr( $args['label_for'] ); ?>" class="regular-text <?php echo is_rtl() ? 'rtl' : 'ltr'; ?> smtp-input" autocomplete="off" value="<?php echo esc_attr( get_option( 'smtp_password' ) ); ?>">
		<button type="button" class="button wp-hide-pw hide-if-no-js smtp-input" data-toggle="0" data-start-masked="1" aria-label="<?php esc_attr_e( 'Show password' ); ?>">
			<span class="dashicons dashicons-visibility" aria-hidden="true"></span>
		</button>
	</span>
					<?php
				},
			),

			'smtp_secure'   => array(
				'type'              => 'string',
				'label'             => __( 'Encryption', 'smtp' ),
				'label_for'         => 'smtp-secure',
				'sanitize_callback' => 'sanitize_text_field',
				'html_callback'     => function ( $args ) {
					?>
	<fieldset>
		<legend class="screen-reader-text">
			<span><?php echo esc_html( $args['label'] ); ?></span>
		</legend>
		<p>
			<label>
				<input name="<?php echo esc_attr( 'smtp_secure' ); ?>" type="radio" value="" class="tog smtp-input" <?php echo esc_attr( checked( 1, get_option( 'smtp_secure' ) === '', false ) ); ?> />
					<?php esc_attr_e( 'No encryption layer (insecure)', 'smtp' ); ?>
			</label>
		</p>
		<p>
			<label>
				<input name="<?php echo esc_attr( 'smtp_secure' ); ?>" type="radio" value="tls" class="tog smtp-input" <?php echo esc_attr( checked( 1, get_option( 'smtp_secure' ) === 'tls', false ) ); ?> />
					<?php esc_attr_e( 'Use STARTTLS', 'smtp' ); ?>
			</label>
		</p>
		<p>
			<label>
				<input name="<?php echo esc_attr( 'smtp_secure' ); ?>" type="radio" value="ssl" class="tog smtp-input" <?php echo esc_attr( checked( 1, get_option( 'smtp_secure' ) === 'ssl', false ) ); ?> />
					<?php esc_attr_e( 'Implicit SSL/TLS (recommended)', 'smtp' ); ?>
			</label>
		</p>
		<p class="description">
					<?php esc_html_e( 'The recommended method for securing modern SMTP servers is SSL/TLS, typically using port 465, while explicit STARTTLS commonly uses port 587.', 'smtp' ); ?>
		</p>
	</fieldset>
					<?php
				},
			),

			'smtp_from'     => array(
				'type'              => 'string',
				'label'             => __( 'From email address', 'smtp' ),
				'sanitize_callback' => 'sanitize_text_field',
				'html_callback'     => function ( $args ) {
					// borrowed from: https://github.com/WordPress/WordPress/blob/98170817289e5ccbfe47cce46bc1069f7d79f710/wp-includes/pluggable.php#L369.
					$sitename   = wp_parse_url( network_home_url(), PHP_URL_HOST );
					$default_from_email = 'wordpress@';

					if ( null !== $sitename ) {
						if ( str_starts_with( $sitename, 'www.' ) ) {
							$sitename = substr( $sitename, 4 );
						}

						$default_from_email .= $sitename;
					}
					?>
	<fieldset>
		<legend class="screen-reader-text">
			<span><?php echo esc_html( $args['label'] ); ?></span>
		</legend>
		<input name="smtp_from" type="text" id="<?php echo esc_attr( $args['label_for'] ); ?>" placeholder="<?php echo esc_attr( $default_from_email ); ?>" class="regular-text <?php echo is_rtl() ? 'rtl' : 'ltr'; ?> smtp-input" value="<?php echo esc_attr( get_option( 'smtp_from' ) ); ?>" />
		<p class="description">
					<?php
					/* translators: default WordPress email adddress */
					echo ( sprintf( esc_html__( 'If left empty, uses the default WordPress email %s which, depending on the server configurations, can decrease the chances of email deliverability.', 'smtp' ), '<code>' . esc_html( $default_from_email ) . '</code>' ) );
					?>
		</p>
	</fieldset>
					<?php
				},
			),
		);

		foreach ( $settings as $field => $args ) {
			register_setting(
				'general',
				$field,
				array_intersect_key(
					$args,
					array_flip(
						array(
							'type',
							'description',
							'sanitize_callback',
							'show_in_rest',
						)
					)
				)
			);

			if ( isset( $args['html_callback'] ) ) {
				add_settings_field(
					$field,
					$args['label'],
					$args['html_callback'],
					'general',
					'smtp_settings_section',
					array_diff_key( $args, array_flip( array( 'html_callback' ) ) ),
				);
			}
		}
	}
);
