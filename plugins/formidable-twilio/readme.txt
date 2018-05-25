= 1.08 =
* Improved: Removed deprecated instructions and screenshot.

= 1.07 =
* Improved: When a text is received, run validation and send a text with any error messages
* Improved: When a text is received, respond with the form success message
* Improved: Allow for the "other" fields to be filled from a text submission
* Improved; Check if license number is for correct plugin
* Improved: Don't try to send to a blank number

= 1.06 =
* New: Add frmtwlo_format_number hook for formatting the phone number before it's sent to Twilio.
* Fix: License activation issues resolved.

= 1.05 =
* Add frmtwlo_sms_response hook for changing the message in the text
* Allow for 8-digit numbers. +65 phone numbers were having trouble because we assumed they should be US numbers. Now if the number starts with a +, don't add the +1
* Get updates from FormidablePro.com

= 1.04 =
* Fix error with accepting votes
* Send a response when a text vote is received

= 1.03 =
* Add deprecated notices for users running < 2.0
* Allow messages over 160 characters
* Remove the Twlio API helpers and use wp_remote_post instead
* Increased security with escaping all input and output

= 1.02 =
* Add auto migration from < 2.0 so options will not need to be set again
* Allow for multiple recipients separated by , or ;

= 1.01 =
* Update for Formidable v2.0 compatibility
* IMPORTANT: Please update your form settings after updating to v2.0. You will need to add your text again.

= 1.0 =
* Allow texts to be sent to and from numbers without 1 for the international code

= 1.0rc1 =
* Added SMS notifications
* Added auto-updating

= 1.0b2 =
* Fix error when updating settings
* Update for Formidable v1.6