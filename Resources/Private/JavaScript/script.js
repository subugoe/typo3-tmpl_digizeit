jQuery(function() {

	$ = jQuery;

	// Responsive off-canvas navigation and login

	$('[data-toggle="login"]').click( function() {
		$target = $('.' + $(this).data('toggle'))
		isActive = $target.hasClass('active');
		if ( ! isActive ) $target.addClass('active');
		$('.wrapper')
			.css({'right': 'auto'})
			.animate({ 'left': ( isActive ? 0 : '-' + $target.css('width') ) }, null, function() {
				if ( isActive ) $target.removeClass('active')
			});
		return false;
	})

	$('[data-toggle="navigation"]').click( function() {
		$target = $('.' + $(this).data('toggle'))
		isActive = $target.hasClass('active');
		if ( ! isActive ) $target.addClass('active');
		$('.wrapper')
			.css({'left': 'auto'})
			.animate({ 'right': ( isActive ? 0 : '-' + $target.css('width') ) }, null, function() {
				if ( isActive ) $target.removeClass('active')
			});
		return false;
	})

	$('body').click( function() {
		if ( $('.login').hasClass('active') ) {
			$('[data-toggle="login"]').click();
		} else if ( $('.navigation').hasClass('active') ) {
			$('[data-toggle="navigation"]').click();
		}
	})

	$('.login, .navigation').click( function(e) {
		e.stopPropagation()
	})

	$(window).resize( function() {
		$('body').click()
	})

	// FAQ handling

	$('.irfaq__toggle--show-all').click( function() {
		$('.irfaq__answer').slideDown()
	})

	$('.irfaq__toggle--hide-all').click( function() {
		$('.irfaq__answer').slideUp()
	})

	$('.irfaq__question').click( function() {
		$(this)
			.toggleClass('irfaq__question--minus')
			.siblings('.irfaq__answer')
				.slideToggle()
	})

	// Scroll top link

	$(window).scroll( function() {
		if ( $(window).scrollTop() > 250 ) {
			$('.to-top').addClass('to-top--visible')
		} else {
			$('.to-top').removeClass('to-top--visible')
		}
	})

	$('.to-top').click( function() {
		$('html, body').animate({scrollTop: 0})
	})

	// Filter for address list

	$('.ttaddress__filter').keyup( function() {
		var val = $(this).val().toLowerCase()
		var $addressItems = $(this).closest('.ttaddress').find('.ttaddress__item')
		if ( val > '' ) {
			$addressItems.each( function() {
				if ( $(this).text().toLowerCase().indexOf(val) > -1 ) {
					$(this).slideDown()
				} else {
					$(this).slideUp()
				}
			})
		} else {
			$addressItems.slideDown()
		}
	})

	// SVG -> PNG fallback for older browsers

	if ( ! document.implementation.hasFeature('http://www.w3.org/TR/SVG11/feature#Image', '1.1') ) {
		$('img[src$="svg"]').attr("src", function() {
			return $(this).attr("src").replace(".svg", ".png")
		})
	}

})
