(function () {
	function runLucide() {
		if (typeof lucide !== 'undefined' && lucide.createIcons) {
			lucide.createIcons({ attrs: { 'stroke-width': 2 } });
		}
	}
	function init() {
		runLucide();
		var toggle = document.querySelector('.pi-site-header__toggle');
		var nav = document.querySelector('#pi-site-main-nav');
		var closeBtn = document.querySelector('.pi-site-header__drawer-close');
		var backdrop = document.querySelector('.pi-site-header__backdrop');
		if (!toggle || !nav) return;
		function setMenu(open) {
			nav.classList.toggle('is-open', open);
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			document.body.classList.toggle('pi-nav-open', open);
			if (backdrop) backdrop.classList.toggle('is-open', open);
			runLucide();
		}
		toggle.addEventListener('click', function () {
			setMenu(!nav.classList.contains('is-open'));
		});
		if (closeBtn) {
			closeBtn.addEventListener('click', function () {
				setMenu(false);
			});
		}
		if (backdrop) {
			backdrop.addEventListener('click', function () {
				setMenu(false);
			});
		}
		document.addEventListener('keydown', function (e) {
			if (e.key === 'Escape') setMenu(false);
		});
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
