== Changelog ==
= 1.04 =
* Allow translation of Add/Remove buttons
* Replace some deprecated WPML function calls and constants
* Make sure wpml strings are updated when form is updated
* Do not translate default value for all field types
* Make sure incorrect field message is translated with ajax submit

= 1.03.03 =
* Send the language in the ajax url differently to prevent 404s during ajax calls
* Prevent a license key from being saved for another plugin

= 1.03.02 =
* Fix the issue with the default language in WPML being set different than the string language, including compatability with the Strings translation plugin v2.2.6+
* If values in the form settings page changed, update them when going to the translation settings
* Make sure the "previous" label is translatable when drafts are not enabled
* Pass the current language to the ajax calls 
* Get updates from FormidablePro.com

= 1.03.01 =
* Exclude categories for languages other than the default

= 1.03 =
* Automatically check as completed after a value is inserted on the translation page
* Added translations: update button, Previous button, Save Draft link, save draft message, delete entry message, reCaptcha language, global error message
* Added success message after translations are saved
* Filter dynamic default values in translations
* Strip slashes from translated strings
* Removed Add New form button on the translate page

= 1.02.01 =
* Fixed validation for not accepting the default value when translated
* Added translation for next button with multi-paged forms
* Fixed bug that was forcing separate values on the form builder page

= 1.02 =
* Replace FRM_VIEWS_PATH constant
* Replace globals
* Formidable v1.07.02 compatibility
* Truncate string names before sending them to WPML

= 1.01 =
* Update auto-updating for Formidable 1.07+
* Move all admin translating into the add-on
* Added unique message to translations
* Only check for an error message translation if there is an error message to show