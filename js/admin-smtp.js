jQuery(document).ready(function ($) {
	const __ = wp.i18n.__;

	function toggleDisable(disable) {
		$('.smtp-input').attr('disabled', disable);
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
