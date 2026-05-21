# efpic-pro

**Professional photo proofing features for photographers.**

* * *

## Requirements

To use efpic Pro without issues, your server has to run at least PHP 7.4. WordPress should be at version 6.0 or greater.

* * *

## Installation Instructions

1. In your WordPress Admin go to “Plugins > Add New”
2. Click on “Upload Plugin” (next to the “Add Plugins” headline)
3. Choose the .zip file you downloaded from our website on your hard drive
4. Click on “Install Now” – The plugin will be uploaded and installed
5. Click on “Activate Plugin”

* * *

## License Activation

To activate a license you need to install and activate the respective Pro module first (see above). You can find your Licenses on your account page.

1. In your WordPress Admin go to “efpic > efpic Pro”
2. Enter the license into the field and click on “Activate License”

* * *

## Support

* Please have a look at our documentation first: https://efpic.io/docs/
* You can reach out to our support team here: https://efpic.io/support/

* * *

## Changelog

### 1.4.7
Release Date: 2024-08-21

* **Added:** Individual [image comments now have a date](https://log.efpic.dev/individual-image-comments-now-have-a-date/).

* **Added:** Implemented [an alert](https://log.efpic.dev/selection-goal-alert/), which will be displayed if the selection goal is not met.

* **Bugfix:** Fixed an issue where, depending on the fonts used, the collection title could be cut off.

* **Bugfix:** Fixed text domain for a couple of strings.

### 1.4.6
Release Date: 2024-08-02

* **Added:** The approval form (in most cases this is just the approval message textarea, although [you can add more fields](https://efpic.io/docs/developers/#custom-approval-form)) is now prefilled with previous value(s), if the client has already sent their selection before.

* **Changed:** Started disabling auto loading of efpic Pro options.

* **Bugfix:** Fix the text domain for a couple of strings.

### 1.4.5
Release Date: 2024-07-11

* **Added:** Compatibility with efpic 2.3.4

* **Bugfix:** Fixed a bug where the View link in the admin bar wouldn't work correctly for Delivery collections.

### 1.4.4
Release Date: June 28th, 2024

Bugfixes
- Fix an issue, where a single image comment would not have a client name assigned to it
- Fixed an issue, where the approved info would not be shown below delivery on the collection edit screen

### 1.4.3
Release Date: June 25th, 2024

* Bugfix
  * Fix text domain on a couple of strings

### 1.4.2
Release Date: June 25th, 2024

* Enhancements
  * Made the email required setting a lot clearer and added a link to a docs page

* Bugfix
  * Fix an issue where the registration email would display an escape sequence instead of making a new line
  * Fix an issue where a user could create a comment without being registered first
  * Fix an issue where certain keys could not be used when typing into the registration form in some instances
  * Make missing strings translatable

### 1.4.1
Release Date: June 10th, 2024

* Bugfix
  * Make missing strings translatable

### 1.4.0
Release Date: June 3rd, 2024

* Enhancements
  * Clients can now self-identify/register before making a selection
  * New setting to require clients to enter an email address when self-registering for a collection
  * Allow clients to switch between grid sizes

### 1.3.0
Release Date: March 13th, 2024

* Enhancements
  * Additional clients can be added to a collection even after the collection has been sent
  * Individual clients can now be removed from a collection

### 1.2.4
Release Date: February 21st, 2024

* Bugfixes
  * Fix a translation bug which would display the selection option "The client needs to select a maximum of x images" incorrectly in languages other than English

### 1.2.3
Release Date: December 21st, 2023

* Bugfixes
  * Fix a bug where the lightbox would show the select button, even though the collection has already been approved

* Enhancements
  * Photographer logo is now a bit bigger in efpic emails
  * Comments are now collapsed by default on smaller screens
  * The comment box is now more distinct on smaller screens

### 1.2.2
Release Date: December 8th, 2023

* Bugfixes
  * Fix a bug where translation updates would not go away after running the update

### 1.2.1
Release Date: November 29th, 2023

* Bugfixes
  * Fix a bug where the after approval redirect would not work correctly

### 1.2.0
Release Date: November 21st, 2023

* Enhancements
  * It is now possible to set the default expiration time in the settings
  * The exact expiration date & time can now be set individually for each collection

* Bugfixes
  * Fix a bug where the custom primary color would not be used in efpic emails

### 1.1.0
Release Date: October 23rd, 2023

* Compatibility with efpic 2.0.0

### 1.0.2
Release Date: September 18th, 2023

* Fix a bug where the button text color would not be caclulated correctly, when setting a light primary color
* Display a more meaningful update message in the plugin overview, if the Pro license has expired or is invalid

### 1.0.1
Release Date: July 18th, 2023

* Enhancements
  * Compatibility with efpic 1.9.1: The "send selection…" button text can now be filtered.
  * Button text color now automatically switches between black and white, depending on the background color.

* Bugfixes
  * Fix a PHP warning when using PHP 8

### 1.0.0
Release Date: June 19th, 2023

* Initial release
* Combining all of our previous Pro modules (Brand & Customize, Delivery, Download, Import, Mark & Comment, Selection Options and Theft Protection) into one.