=== SiteOrigin Premium ===
Requires at least: 4.4
Tested up to: 4.9
Stable tag: 1.3.2
Build time: 2018-05-22T14:52:50+02:00
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.html

Addons for all SiteOrigin themes and plugins.

== Description ==

SiteOrigin Premium is a collection of addons that enhance SiteOrigin themes and plugins. For one low price, you get all the addons.

== Installation ==

Read our [installation instructions](https://siteorigin.com/premium-documentation/install-siteorigin-premium/) for a full guide on installing SiteOrigin Premium.

== Documentation ==

[Documentation](https://siteorigin.com/premium-documentation/) is available on SiteOrigin.

== Changelog ==

 = 1.3.2 - 22 May 2018 =
* Fixed copy issues in a few places.
* Added Hero documentation link and video.
* Changed Social Widgets description.
* Social Widgets: Add button to description.
* Added new admin addon icons.
* Added videos for tabs and testimonials.
* CPTB: Replaced individual permissions with single option to allow editing of layout in post type instances.
* CPTB: Use widgets' content from post type instances and update widgets' content in instances unless edited.
* CPTB: Warn when changing non-editable layouts and there are existing instances of the post type.

 = 1.3.1 - 9 April 2018 =
* CPT Builder: Prevent filtering out widgets added to custom post type instances.
* Animations: Only perform animations once.

 = 1.3.0 - 2 April 2018 =
* Contact: Auto responder!
* Hero: Addon to allow animation of Hero frames content!
* Contact: DateTime field has option to use 24h format for times.
* Web Font Selector: IE 11 Compat. Don't use `Array.from`.
* Added missing documentation links for Web Font Selector, Call-to-action and Testimonials addons.
* AJAX Comments: Disable comments form submit button when comment submitted.
* Moved animate JS and CSS to common folders and register in main file.
* Contact: Renamed addon for consistency with other widget addon names.
* Accordion: Option to scroll to a specific panel on load.

= 1.2.1 - 31 January 2018 =
* CPT Builder: Allow customization of available Page Builder features for the custom post type.
* Accordion: Moved presets field to above title field.
* Accordion: updated presets to use 16px for all panels and white font for Rounded preset.
* Fix PHP compatibility error.
* Lightbox: Added documentation link.
* Lightbox: Ensure instance specific settings are applied.
* CPT Builder: Prevent custom post types from showing in Page Builder settings list.
* CPT Builder: Use `widgets_init` action to register custom post types.
* Lightbox: Added global settings for overlay color and opacity.
* Testimonials: Font family and size options.
* CTA: Font family and size options.
* Accordion: Allow item specific title icons.
* CPT Builder: Add option of excluding custom post type from search.
* CPT Builder: Add description to Hierarchical to explain what it does.
* CPT Builder: Taxonomy items use label name in editor.

= 1.2.0 - 7 November 2017 =
* New Tabs Widget addon!
* Accordion: Use new presets field.
* Accordion: Allow for setting panels font family and size.
* Accordion: Allow for setting headings text transform.
* Accordion: Deep linking to single/multiple panels.
* Add rel="noopener noreferrer" for all 3rd party/unknown links.

= 1.1.2 - 20 October 2017 =
* Fix lightbox in slider and layout slider widgets.

= 1.1.1 - 12 October 2017 =
* Fix missing js lib.

= 1.1.0 - 11 October 2017 =
* New Accordion widget addon!
* Contact: Apply field label styles to DateTime field labels too.
* Pass post name through `sanitize_reserved_post_types` before using as post type slug.
* Spacing between addon item buttons.

= 1.0.7 - 19 September 2017 =
* Update to latest EDD updater
* Removed submodules and adding addon files back into main repo.
* Animate hiding/showing Lightbox fields.
* Added global and instance lightbox settings to disable captions.
* Prevent JS error when style attribute is empty string.
* Prevent error in Hero widget when lightbox is active.

= 1.0.6 - 9 September 2017 =
* Removed accidentally included addon folder.


= 1.0.5 - 6 September 2017 =
* Lightbox: Fix image widgets using 'image_set_slug'.
* Lightbox: Removed 'disable_scrolling' option which doesn't appear to work.
* Lightbox: Use `_sow_form_id` as slug for images already in group.
* Lightbox: Image widget fallback to using `_sow_form_id`.
* Lightbox: Use 'full' image sizes for lightbox.
* Lightbox: Use album name instead of image set slug.
* Lightbox: Conditional display of album name input when lightbox enabled.

= 1.0.4 - 7 August 2017 =
* Contact form fields: Don't apply disabled date ranges if parsing fails.
* Contact form fields: Google maps widget and contact form location field working together.
* Web font selector: Allow font family without quotes.
* Web font selector: Correct import URLs.
* Web font selector: Select first variant if no 'regular' variant exists.
* AJAX comments: Account for error handler.
* AJAX comments: Check for existing error before error.
* AJAX comments: Account for encoded text.
* AJAX comments: Correct spacing.
* AJAX comments: Move timer.
* Animations: Add hover event.
* Changed to an autoloader system.
* Move addons to submodules.
* Global settings for addons.
* Lightbox: New lightbox addon!

= 1.0.3 - 28 September 2016 =
* Added Google Font Field addon for SiteOrigin CSS.
* Disable Ajax Comments on WooCommerce to avoid conflict.
* Fixed Contact Form addon Date Picker

= 1.0.2 - 25 August 2016 =
* Various date picket contact form field improvements.
* Fix build script to remove node modules.
* JS fix to get menu working properly on multisite.

= 1.0.1 - 16 August 2016 =
* Fixed license checking and plugin updating.

= 1.0 - 12 August 2016 =
* Initial release.
