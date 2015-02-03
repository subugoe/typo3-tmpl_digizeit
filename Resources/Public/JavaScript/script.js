jQuery(function() {

	$ = jQuery;

	$('[data-toggle="login"]').click( function() {
		$target = $('#' + $(this).data('toggle'))
		isActive = $target.hasClass('active');
		if ( ! isActive ) $target.addClass('active');
		$('#wrapper')
			.css({'right': 'auto'})
			.animate({ 'left': ( isActive ? 0 : '-' + $target.css('width') ) }, null, function() {
				if ( isActive ) $target.removeClass('active')
			});
		return false;
	})

	$('[data-toggle="navigation"]').click( function() {
		$target = $('#' + $(this).data('toggle'))
		isActive = $target.hasClass('active');
		if ( ! isActive ) $target.addClass('active');
		$('#wrapper')
			.css({'left': 'auto'})
			.animate({ 'right': ( isActive ? 0 : '-' + $target.css('width') ) }, null, function() {
				if ( isActive ) $target.removeClass('active')
			});
		return false;
	})

	$('body').click( function() {
		if ( $('#login').hasClass('active') ) {
			$('[data-toggle="login"]').click();
		} else if ( $('#navigation').hasClass('active') ) {
			$('[data-toggle="navigation"]').click();
		}
	})
	$('#login, #navigation').click( function(e) {
		e.stopPropagation()
	})
	$(window).resize( function() {
		$('body').click()
	})

	$(window).scroll( function() {
		if ( $(window).scrollTop() > 250 ) {
			$('#totop:hidden').fadeIn()
		} else {
			$('#totop:visible').fadeOut()
		}
	})

	$('#totop').click( function() {
		$('html, body').animate({scrollTop: 0})
	})

})
