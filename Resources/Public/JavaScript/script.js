jQuery(function(){$=jQuery,$('[data-toggle="login"]').click(function(){return $target=$("."+$(this).data("toggle")),isActive=$target.hasClass("active"),isActive||$target.addClass("active"),$(".wrapper").css({right:"auto"}).animate({left:isActive?0:"-"+$target.css("width")},null,function(){isActive&&$target.removeClass("active")}),!1}),$('[data-toggle="navigation"]').click(function(){return $target=$("."+$(this).data("toggle")),isActive=$target.hasClass("active"),isActive||$target.addClass("active"),$(".wrapper").css({left:"auto"}).animate({right:isActive?0:"-"+$target.css("width")},null,function(){isActive&&$target.removeClass("active")}),!1}),$("body").click(function(){$(".login").hasClass("active")?$('[data-toggle="login"]').click():$(".navigation").hasClass("active")&&$('[data-toggle="navigation"]').click()}),$(".login, .navigation").click(function(t){t.stopPropagation()}),$(window).resize(function(){$("body").click()}),$(".irfaq__toggle--show-all").click(function(){$(".irfaq__answer").slideDown()}),$(".irfaq__toggle--hide-all").click(function(){$(".irfaq__answer").slideUp()}),$(".irfaq__question").click(function(){$(this).toggleClass("irfaq__question--minus").siblings(".irfaq__answer").slideToggle()}),$(window).scroll(function(){$(window).scrollTop()>250?$(".to-top").addClass("to-top--visible"):$(".to-top").removeClass("to-top--visible")}),$(".to-top").click(function(){$("html, body").animate({scrollTop:0})}),$(".ttaddress__filter").keyup(function(){var t=$(this).val().toLowerCase().split(" "),i=$(this).closest(".ttaddress").find(".ttaddress__item");t!==[""]?i.each(function(i,e){var a=!0;$.each(t,function(t,i){return-1===$(e).text().toLowerCase().indexOf(i)?(a=!1,!1):void 0}),a?$(e).slideDown("slow"):$(e).slideUp("slow")}):$addressItems.slideDown(),$(this).next(".ttaddress__clear-filter").toggle(t>[""])}),$(".ttaddress__clear-filter").click(function(){$(this).prev(".ttaddress__filter").val("").keyup()}),document.implementation.hasFeature("http://www.w3.org/TR/SVG11/feature#Image","1.1")||$('img[src$="svg"]').attr("src",function(){return $(this).attr("src").replace(".svg",".png")})});