A JavaScript-based mobile theme switcher plugin for Wordpress, brought to you by [Map Creative](http://mapcreative.com.au/).

The chief purpose of this plugin is to allow functionality similar to [Device Theme Switcher](http://wordpress.org/plugins/device-theme-switcher/), but without requiring the use of any serverside logic. This allows the plugin to function where others would fail, i.e. in server setups with complex caching mechanisms sitting in front of Wordpress like WPEngine's [EverCache](http://wpengine.com/scale-to-millions-of-hits-a-day-or-hour/).

A selectable state persistence mechanism also features, to allow for mobile theme state to be passed by URL or other methods for greater compatibility with complicated caching layers. Appending GET parameters to a URL results in a separate HTML cache for mobile versions of pages, and prevents caching layers becoming confused between desktop and mobile versions of the same page.
