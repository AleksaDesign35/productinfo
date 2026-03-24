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
		if (!toggle || !nav) return;
		toggle.addEventListener('click', function () {
			var open = nav.classList.toggle('is-open');
			toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
			document.body.classList.toggle('pi-nav-open', open);
			runLucide();
		});
	}
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})();
