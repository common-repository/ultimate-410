=== Ultimate 410 Gone Status Code ===

Contributors: tinyweb, 7iebenschlaefer, alpipego
Tags: 410, http-status
Requires at least: 5.1
Tested up to: 6.6
Requires PHP: 7.0
Stable tag: 1.1.8
License: MIT
License URI: https://opensource.org/licenses/MIT

Easy “410 – gone” status code plugin for WordPress: CSV bulk upload, manual & regex entry, 410 option when deleting pages, posts, categories & tags.

== Description ==

In accordance with the [HTTP Specification](https://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html#sec10.4.11), the 410 status code in the response header indicates that the requested resource is gone (permanently removed).

Features:

* When deleting pages, posts, categories or tags from WordPress, the ultimate 410 status code will ask if a 410 response code should be set for the deleted URL.
* Add 410 URLs manually
* Add 410 URL schemes via regex (regular expressions)
* Bulk upload 410 URLs via csv-file.
    * works even if the csv-file contains a BOM

If you come across any bugs or have suggestions, please use the plugin support forum.

== Frequently Asked Questions ==

= Can I customize the 410 response message? =

The default message is a simple plain text message that reads "410 – gone". This is because many people want to minimize the bandwidth that is used by error responses.

If you want to customize the message, just place a template file with the name `410.php` in your theme folder, and the plugin will automatically use that instead. Take a look at your themes' `404.php` file to see how the template needs to be structured.

= Will this plugin work if a caching/performance plugin is active? =

The plugin has been tested with the following caching plugins, and should work even if they are active:

* W3 Total Cache
* WP Super Cache
* Cache Enabler
* WP Rocket

We have not tested it with other caching plugins, and there is a high chance that it will not work with many of them. Most of them will cache the response as if it is a 404 (page not found) response, and issue a 404 response header instead of a 410 response header.

= How do you handle trailing slashes? =

The "Add URL" and the "Upload" functions consider the exact URL `/page-to-be-deleted/` to be different from `/page-to-be-deleted`

Specify the exact URL that your site uses (with or without the trailing slash). If you want both URLs (with and without the / at the end) to return 410 gone status code, you can use regex like this `page-to-be-deleted/?` or add both URLs as simple strings.

== Screenshots ==

1. CSV Upload and table of already added 410 URLs.
2. Manually add URL.
3. Admin notice after page trashed.
4. Admin notice page URL added as 410.

== Changelog ==

= 1.1.8  =

* * Remove deprecated ini_set() calls with `auto_detect_line_endings`.

= 1.1.7 =

* Prevent handling an empty request (e.g. the root or only query parameters on the root)
* Revert a change from 1.1.5 that prevented WP Bakery from correctly rendering contents on the 410 page.

= 1.1.5 =

* URL sanitization in PHP scripts has been enhanced to mitigate potential security risks associated with authenticated stored Cross-site Scripting (XSS).

= 1.1.4 =

* exit after custom 410-template got included

= 1.1.3 =

* fix issue with adding URLs after post got deleted

= 1.1.2 =

* fix version quirks

= 1.1.1 =

* remove undefined variable

= 1.1.0 =

* add bulk delete options
* account for ASCII characters in URLs
* fix PHP notices for passing variables by reference needlessly

= 1.0.5 =

* fix pagination issue affecting other WP_List_Tables
* fix issue with deletion of regex that included backslashes

= 1.0.4 =

* fix WordPress database error: [Specified key was too long; max key length is 767 bytes]
