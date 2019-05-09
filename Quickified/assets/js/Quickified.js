var lazyload = function() {
	function isElementInViewport(el) {
			var rect = el.getBoundingClientRect();
			return (
					rect.bottom >= 0 &&
					rect.right >= 0 &&
					rect.top <= (window.innerHeight || document.documentElement.clientHeight) &&
					rect.left <= (window.innerWidth || document.documentElement.clientWidth)
					);
	}

	function scrollcallback() {
			var elem = document.querySelectorAll('.quickified');
			elem.forEach(function(el) {
					var isvisible = isElementInViewport(el);
					if (isvisible) {
							var realsrc = el.getAttribute("data-src"),
									realsrcset = el.getAttribute("data-srcset"),
									realsizes = el.getAttribute("data-sizes");
							el.classList.remove('quickified');
							el.classList.add('loaded');
							el.setAttribute("src", realsrc);
							el.setAttribute("srcset", realsrcset);
							el.setAttribute("sizes", realsizes);
							el.removeAttribute("data-src");
							el.removeAttribute("data-srcset");
							el.removeAttribute("data-sizes");
					}
			});
	}
	scrollcallback();

	var scroll = function() {
			scrollcallback();
	};
	var raf = window.requestAnimationFrame ||
			window.webkitRequestAnimationFrame ||
			window.mozRequestAnimationFrame ||
			window.msRequestAnimationFrame ||
			window.oRequestAnimationFrame;

	var lastScrollTop = window.pageYOffset;

	if (raf) {
			loop();
	}

	function loop() {
			var scrollTop = window.pageYOffset;
			if (lastScrollTop === scrollTop) {
					raf(loop);
					return;
			} else {
					lastScrollTop = scrollTop;
					scroll();
					raf(loop);
			}
	}
};

if (window.addEventListener) {
	addEventListener('DOMContentLoaded', lazyload, false);
	addEventListener('load', lazyload, false);
	addEventListener('scroll', lazyload, false);
	addEventListener('resize', lazyload, false);
}