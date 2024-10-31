=== PRO Sitemaps Connect ===
Contributors: prositemaps
Stable tag: 1.3
Tested up to: 6.6
Tags: sitemap, xml, pro-sitemaps, seo, search engine
Requires at least: 5.0
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin is turning an XML Sitemap created by PRO Sitemaps service into a self-hosted sitemap by serving it directly using your website domain

== Description ==

XML sitemaps are designed to help search engines to crawl and index websites better. [PRO Sitemaps](https://pro-sitemaps.com) is an online service that creates such sitemaps, auto-updates and hosts them (among other features).

This plugin requires access to the 3rd party service: [PRO Sitemaps](https://pro-sitemaps.com/). Please read PRO Sitemaps
[Terms of Use](https://pro-sitemaps.com/terms-of-use/) and [Privacy Policy](https://pro-sitemaps.com/privacy-policy/) before installing the plugin. It uses PRO Sitemaps API, turning an XML Sitemap created by PRO Sitemaps into a self-hosted sitemap, serving it directly using your website domain. The API call to PRO Sitemaps will be made each time sitemap is requested on your website.

Plugin also updates your robots.txt file to notify search engines about your sitemap, and optionally initiates sitemap update each time a new post is created on your website.

== Installation ==

1. Upload the plugin package to the "wp-content/plugins/" folder on your website.
2. Activate the plugin in the wp-admin Plugins section.
3. Enter your PRO Sitemaps API key info on the Plugin Settings page (see FAQ for details).

== Screenshots ==

1. PRO Sitemaps plugin settings page
2. An example of self-hosted XML sitemap
3. An example of self-hosted Images sitemap
4. An example of self-hosted Video sitemap

== Frequently Asked Questions ==

= What is a sitemap? =

By placing a formatted xml file with site map on your website, you allow Search Engine crawlers (like Google) to find out what pages are present and which have recently changed, and to crawl your site accordingly. [Read more](https://pro-sitemaps.com/info/about-sitemaps.html)

= What is PRO Sitemaps? =

PRO Sitemaps is an online service that creates XML sitemaps for websites, including Images, Videos and News sitemaps, HTML sitemap and RSS feeds. The service keeps sitemaps up-to-date and allows to submit them to search engines directly from own servers. The service also provides Site History, Broken links, External links reports and other features. [Read more](https://pro-sitemaps.com/docs/overview/)

= What is this plugin for? =

Although PRO Sitemaps users can submit sitemaps from sitemap hosting domains, this plugin lets you turn those sitemaps into self-hosted versions, accessible via yourdomain.com/your-sitemap.xml

= How to obtain PRO Sitemaps API key =

1. Login to your PRO Sitemaps account on [pro-sitemaps.com](https://pro-sitemaps.com), or create a new free account if you don't have one.
2. Select your https://my-blogdomain.com/ site entry in PRO Sitemaps dashboard, or create a new site entry for it.
3. Select "More" -> "Use API" in the navigation menu. You will find your API Key and Site ID on that page.
4. Enter both on the Plugin Settings page and click "Save".

== Changelog ==

= 1.0 =
* Initial release.
