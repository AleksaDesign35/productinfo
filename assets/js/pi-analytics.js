(function () {
	if (!window.piAnalyticsConfig || !window.piAnalyticsConfig.ajaxUrl || !window.localStorage) {
		return;
	}

	var config = window.piAnalyticsConfig;
	var tokenKey = 'piAnalyticsVisitToken';
	var lastSeenKey = 'piAnalyticsLastSeen';
	var visitWindowMs = (parseInt(config.visitWindow, 10) || 1800) * 1000;
	var exitSent = false;

	function read(key) {
		try {
			return window.localStorage.getItem(key) || '';
		} catch (error) {
			return '';
		}
	}

	function write(key, value) {
		try {
			window.localStorage.setItem(key, value);
		} catch (error) {}
	}

	function now() {
		return Date.now();
	}

	function makeToken() {
		if (window.crypto && typeof window.crypto.randomUUID === 'function') {
			return window.crypto.randomUUID();
		}

		return 'v' + now().toString(36) + Math.random().toString(36).slice(2, 14);
	}

	function buildData(action) {
		var data = new FormData();

		data.append('action', action);
		data.append('visitToken', visitToken);
		data.append('currentUrl', window.location.href);
		data.append('referrerUrl', document.referrer || '');

		return data;
	}

	function send(action, keepalive) {
		var data = buildData(action);

		if (keepalive && navigator.sendBeacon) {
			navigator.sendBeacon(config.ajaxUrl, data);
			return;
		}

		if (window.fetch) {
			window.fetch(config.ajaxUrl, {
				method: 'POST',
				body: data,
				credentials: 'same-origin',
				keepalive: !!keepalive
			});
			return;
		}

		var request = new XMLHttpRequest();
		request.open('POST', config.ajaxUrl, true);
		request.send(data);
	}

	var visitToken = read(tokenKey);
	var lastSeen = parseInt(read(lastSeenKey), 10);

	if (!visitToken || !lastSeen || now() - lastSeen > visitWindowMs) {
		visitToken = makeToken();
	}

	write(tokenKey, visitToken);
	write(lastSeenKey, String(now()));
	send(config.trackAction, false);

	function sendExit() {
		if (exitSent) {
			return;
		}

		exitSent = true;
		write(lastSeenKey, String(now()));
		send(config.exitAction, true);
	}

	window.addEventListener('pagehide', sendExit);
	document.addEventListener('visibilitychange', function () {
		if (document.visibilityState === 'hidden') {
			sendExit();
		}
	});
})();
