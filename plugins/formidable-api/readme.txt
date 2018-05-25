== Description ==
Routes
* frm/v2/entries READ (tested)
* frm/v2/entries CREATE (tested)
* frm/v2/entries EDIT (tested)
* frm/v2/entries DELETE
* frm/v2/entries/# READ (tested)
* frm/v2/entries/# EDIT (tested)
* frm/v2/entries/# DELETE

* frm/v2/forms/#/fields READ (tested)
* frm/v2/forms/#/fields CREATE
* frm/v2/forms/#/fields/# READ (tested)
* frm/v2/forms/#/fields/# DELETE

* frm/v2/forms/#?return=html READ (tested)
* frm/v2/forms READ (tested)
* frm/v2/forms CREATE
* frm/v2/forms/# READ (tested)
* frm/v2/forms/# DELETE

* frm/v2/views/# READ

== Changelog ==
= 1.03 =
- Fix: Fix unique field validation on edit.
- Fix: Double escape /r/n/ in json.
- Fix: Prevent cookie from being created during api call
- Fix: Update deprecated validation call.
- Fix: Allow quotes in shortcodes within API action.
- Fix: Include custom headers in logging.
- Fix: Include post values when updating an entry.

= 1.02 =
* Enhancement: Add headers to API call hook to allow headers to be modified
* Fix: Correct the dynamic URL check
* Fix: Load JS on Settings page load so + button works in Safari

= 1.0b11 =
* Fix entry creation and editing
* Use Y-m-d H:i:s for default creation date format

= 1.0b11 =
* Better results when using multiple frm-api shortcodes on a page
* Allow any parameters in frm-api shortcode to pass to the API
* Fix the form action not showing up
* Adjustments so API works with Formidable < 2.0.15

= 1.0b10 =
* Add v2.0 routes. These can be accessed with wp-json/frm/v2/...
* Add frm-api shortcode for inserting a form or view from another site. [frm-api id=50 url="http://othersite.com" type=form]
* Allow for REDIRECT_HTTP_AUTHORIZATION
* Don't require a license key for open items

= 1.0b9 =
* Get auto-updates from FormidablePros.com

= 1.0b8 =
* Return more than 20 entries when requested
* Update for deprecated localize_script function
* Allow shortcodes like [/foreach] and [/if 25] in the json content
* Sanitize & for POST

= 1.0b7 =
* Show the webhooks menu
* Updates for 2.0 compatability (will be moving into the Form actions later)

= 1.0b6 =
* Fix PUT/PATCH methods instead of assuming POST
* Let the JSON API plugin handle the data fetching and decoding
* Fill in entry values with those from the existing entry when editing

= 1.0b5 =
* Allow field keys to work for sending data for creating entries
* Format data for specific fields as needed before an entry is created

= 1.0b4 =
* Updated authentication for v1.0 of the JSON Rest API plugin
* Fixed editing entries
* Bug fixes

= 1.0b3 =
* Added functionality to edit entries
* Save authorization errors to global so they can later be returned
* Added 'test' parameter to the create entry function for testing without creating an entry
* Added json_encode JSON_PRETTY_PRINT fallback for PHP < 5.4

= 1.0b2 =
* Fixed permission checking