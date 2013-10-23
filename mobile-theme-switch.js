(function() {
	// Please note: this whole API assumes the default site you are running is a Desktop version.

	var IS_MOBILE, IS_TABLET, PLATFORM_DETECTED = false,
		CURRENT_SITE_TYPE,
		CHECKED_COOKIE_NAME = 'jsmts_checked',
		FLAG_MOBILE = 'm',
		FLAG_TABLET = 't',
		FLAG_DESKTOP = 'd';

	// Before doing anything, let's create some API other code can use to make decisions based on platform :D
	function detectPlatform()
	{
		if (PLATFORM_DETECTED) {
			return;
		}

		var flags = (function(a){
			return [
				// mobile regexes from http://detectmobilebrowsers.com/
					(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i.test(a)
					|| /1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))),
				// tablet regex from https://github.com/codefuze/js-mobile-tablet-redirect
					/ipad|android 3|sch-i800|playbook|tablet|kindle|gt-p1000|sgh-t849|shw-m180s|a510|a511|a100|dell streak|silk/i.test(a.toLowerCase())];
		})(navigator.userAgent || navigator.vendor || window.opera);

		IS_MOBILE = flags[0];
		IS_TABLET = flags[1];
		PLATFORM_DETECTED = true;
	}

	function detectLocation()	// :NOTE: this is re-run on each request, for compatibility with History API location changes
	{
		switch (JSMTS.method) {
			case 'qs':
				var qs = getQuery(window.location.href),
					unset = typeof qs[JSMTS.key] == 'undefined';

				if (unset) {
					CURRENT_SITE_TYPE = FLAG_DESKTOP;
				} else {
					CURRENT_SITE_TYPE = qs[JSMTS.key];
					if (CURRENT_SITE_TYPE != FLAG_MOBILE && CURRENT_SITE_TYPE != FLAG_TABLET) {
						CURRENT_SITE_TYPE = FLAG_DESKTOP;
					}
				}
				break;
			case 'r':
				var topLevelUrl = window.location.protocol + '//' + window.location.hostname;

				if (topLevelUrl == JSMTS.key) {
					CURRENT_SITE_TYPE = FLAG_MOBILE;
				} else if (topLevelUrl == JSMTS.key2) {
					CURRENT_SITE_TYPE = FLAG_TABLET;
				} else {
					CURRENT_SITE_TYPE = FLAG_DESKTOP;
				}
				break;
			case 'c':
				// :TODO:
				break;
		}
	}

	JSMTS.isMobile = function()
	{
		detectPlatform();
		return IS_MOBILE;
	};

	JSMTS.isTablet = function()
	{
		detectPlatform();
		return IS_TABLET;
	};

	JSMTS.isDesktop = function()
	{
		detectPlatform();
		return !(IS_TABLET || IS_MOBILE);
	};

	JSMTS.viewingMobile = function()
	{
		detectLocation();
		return CURRENT_SITE_TYPE == FLAG_MOBILE;
	};

	JSMTS.viewingTablet = function()
	{
		detectLocation();
		return CURRENT_SITE_TYPE == FLAG_TABLET;
	};

	JSMTS.viewingDesktop = function()
	{
		detectLocation();
		return CURRENT_SITE_TYPE == FLAG_DESKTOP;
	};

	// HELPER METHODS

	function getQuery(url)
	{
		var query = url.indexOf('?');

		if (query == -1) {
			return {};
		}

		var qs = url.substring(query + 1).split('&'),
			i = 0, l = qs.length,
			result = {};

		for (; i < l; ++i) {
			qs[i] = qs[i].split('=');
			result[qs[i][0]] = qs[i].length > 1 ? decodeURIComponent(qs[i][1]) : true;
		}

		return result;
	}

	function appendQuery(str, key, val)
	{
		var bits = str.split('#', 2);
		return bits[0] + (bits[0].indexOf('?') == -1 ? '?' : '&') + key + '=' + val + (typeof bits[1] != 'undefined' ? '#' + bits[1] : '');
	}

	function removeQuery(str, key)
	{
		var query = str.indexOf('?');

		if (query == -1) {
			return str;
		}

		var params = getQuery(str),
			k, paramStr = [];

		for (k in params) {
			if (!params.hasOwnProperty(k) || key == k) {
				continue;
			}
			paramStr.push(k + '=' + params[k]);
		}

		return str.substring(0, query) + (paramStr.length ? '?' + paramStr.join('&') : '');
	}

	// cookie manipulation methods taken from http://www.quirksmode.org/js/cookies.html

	function createCookie(name, value, days)
	{
		if (days) {
			var date = new Date();
			date.setTime(date.getTime()+(days*24*60*60*1000));
			var expires = "; expires="+date.toGMTString();
		}
		else var expires = "";
		document.cookie = name+"="+value+expires+"; path=/";
	}

	function readCookie(name)
	{
		var nameEQ = name + "=";
		var ca = document.cookie.split(';');
		for(var i=0;i < ca.length;i++) {
			var c = ca[i];
			while (c.charAt(0)==' ') c = c.substring(1,c.length);
			if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
		}
		return null;
	}

	function eraseCookie(name)
	{
		createCookie(name,"",-1);
	}

	// store these methods into the global API for reuse as well
	JSMTS.util = {
		'getQuery' : getQuery,
		'appendQuery' : appendQuery,
		'removeQuery' : removeQuery,
		'createCookie' : createCookie,
		'readCookie' : readCookie,
		'eraseCookie' : eraseCookie
	};

	// BEGIN MAIN LOGIC

	// if no themes are configured, do nothing
	if (!JSMTS.check_mobile && !JSMTS.check_tablet) {
		return;
	}

	if (JSMTS.set_state) {
		// stop processing if we've already checked for a browser this session
		if (readCookie(CHECKED_COOKIE_NAME)) {
			return;
		}

		// flag a cookie if we have now checked
		createCookie(CHECKED_COOKIE_NAME, 1, JSMTS.recheck_timeout);
	}

	// sniff out the platform and active site

	detectPlatform();
	detectLocation();

	// check whether we are in the correct state, and set it if not

	if (JSMTS.check_mobile && IS_MOBILE && CURRENT_SITE_TYPE != FLAG_MOBILE) {
		switch (JSMTS.method) {
			case 'qs':
				window.location.replace(appendQuery(removeQuery(window.location.href, JSMTS.key), JSMTS.key, FLAG_MOBILE));
				break;
			case 'r':
				window.location.replace(JSMTS.key + window.location.pathname);
				break;
			case 'c':
				// :TODO:
				break;
		}
	} else if (JSMTS.check_tablet && IS_TABLET && CURRENT_SITE_TYPE != FLAG_TABLET) {
		switch (JSMTS.method) {
			case 'qs':
				window.location.replace(appendQuery(removeQuery(window.location.href, JSMTS.key), JSMTS.key, FLAG_TABLET));
				break;
			case 'r':
				window.location.replace(JSMTS.key2 + window.location.pathname);
				break;
			case 'c':
				// :TODO:
				break;
		}
	} else if (!(JSMTS.check_mobile && IS_MOBILE) && !(JSMTS.check_tablet && IS_TABLET)) {
		switch (JSMTS.method) {
			case 'qs':
				var qs = getQuery(window.location.href),
					unset = typeof qs[JSMTS.key] == 'undefined';
				if (!unset) {
					window.location.replace(removeQuery(window.location.href, JSMTS.key));
				}
				break;
			case 'r':
				var topLevelUrl = window.location.protocol + '//' + window.location.hostname;
				if (topLevelUrl != JSMTS.base) {
					window.location.replace(JSMTS.base + window.location.pathname);
				}
				break;
			case 'c':
				// :TODO:
				break;
		}
	}
})();
