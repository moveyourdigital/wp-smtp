jQuery(document).ready(function ($) {
	const __ = wp.i18n.__;

	const smtp_auth_selector = 'input[name=smtp_username], input[name=smtp_password], input[name=smtp_password] + button';

	function toggleDisable(disable, selector = '.smtp-input') {
		$(selector).attr('disabled', disable);
	}

	function resetToggle(show) {
		$('.smtpserver-pass-wrap .wp-hide-pw')
			.attr({
				'aria-label': show ? __('Show password') : __('Hide password')
			})
			.find('.text')
			.text(show ? __('Show') : __('Hide'))
			.end()
			.find('.dashicons')
			.removeClass(show ? 'dashicons-hidden' : 'dashicons-visibility')
			.addClass(show ? 'dashicons-visibility' : 'dashicons-hidden');
	}

	$('input[name=smtp_enabled]')
		.each(function (_, e) {
			if (e.checked) {
				toggleDisable(e.value === '0');
			}
		})
		.on('click', function (e) {
			toggleDisable(e.target.value === '0');
		});

	$('input[name=smtp_auth]')
		.each(function (_, e) {
			toggleDisable(e.checked === false, smtp_auth_selector);
		})
		.on('click', function (e) {
			toggleDisable(e.target.checked === false, smtp_auth_selector);
		});

	$('.smtpserver-pass-wrap .wp-hide-pw')
		.show().on('click', function () {
			const $pass1 = $('#smtp-password');

			if ('password' === $pass1.attr('type')) {
				$pass1.attr('type', 'text');
				resetToggle(false);
			} else {
				$pass1.attr('type', 'password');
				resetToggle(true);
			}
		});

});
