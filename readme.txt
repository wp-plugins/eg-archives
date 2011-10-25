=== EG-Archives ===
Contributors: Emmanuel GEORJON
Donate link: http://www.emmanuelgeorjon.com/
Tags: archive, widget
Requires at least: 3.0.0
Tested up to: 3.2.1
Stable tag: 2.0.1

EG-Archives provides a widget (and a template tag) to display archives in yearly mode, AND monthly, according a specified pivot date.

== Description ==

The standard Archives widget doesn't accept a lot of parameters. You can just specify 

* a title, 
* if you want to display posts count or not.

With the **EG-Archives** widget, you can choose 

* the title,
* the format of the list (simple or dropdown list),
* the type: Yearly, Monthly, ... 
* category, to display archives for only specific categories,
* Number of columns (1 or 2)

A specific type is added: the "mixed Yearly/monthly" type.
With this type, you can display list of archives in yearly mode before a specified year, and in monthly mode after this specified date. This type allows to shorten list, and is very useful if you don't publish too many posts per year. Example:

* August 2011
* April 2011
* February 2011
* January 2011
* 2010
* 2009

= Translations =

The plugin comes up with 4 translations. Thanks to the following people for their contributions:

* French (FR)
* German (DE) - [Frank W. Hempel](http://www.frank-hempel.de/)
* Bulgarian (BG) - [Web Geek, Dimitar Kolevski](http://webhostinggeeks.com/)
* Russian (RU) - FatCow

== Installation ==

1. The plugin is available for download on the WordPress repository,
1. Download the file `eg-archives.zip`, and uncompress it,
1. Upload the resulting files to a folder in `../wp-content/plugins/`,
1. Activate EG-Archives through the 'Plugins' menu in WordPress,
1. The plugin is ready to be used,

You can also install the plugin directly from the WordPress interface.

One the installation is done, you can go to the menu **Options / EG-Archives**, to setup the options.

== Upgrade Notice ==

The version 1.1 of the plugin used the old Widget API. The new version 2.0 is using the API published with WordPress 2.8. The widgets options are different between the two API. So, options defined with EG-Archives 1.1.x will be lost after the upgrade.

The recommendation for a clean upgrade is

1. Remove the widget from your sidebar,
1. Upgrade the plugin,
1. Install the new widget in your sidebar,
1. Setup the options with the menu **Options / EG-Archives**


== Usage ==

**EG-Archives** adds a widget, and a template tag.

= Widget =

* Go to the **Appearence / Widgets** menu, 
* Active the widget named: EG-Archives,
* Configure it, through the widget menu.

= Template tag =

You can display the archives list, anywhere in your templates, with the function: `<?php eg_get_archives('arguments'); ?>`
Arguments are the same than the standard WordPress function `wp_get_archives`.

* **type** (string) The type of archive list to display. Valid values: yearly, monthly, daily, weekly, postbypost. Default is monthly,
* **limit** (integer) Number of archives to get. Default is no limit,
* **format** (string) Format for the archive list. Valid values:
    * html - In HTML list (<li>) tags and before and after strings. This is the default,
	* option - In select (<select>) or dropdown option (<option>) tags,
	* link - Within link (<link>) tags,
	* custom - Custom list using the before and after strings. 
* **before** (string) Text to place before the link when using the html or custom for format option. There is no default,
* **after** (string) Text to place after the link when using the html or custom for format option. There is no default, 
* **show_post_count** (boolean) Display number of posts in an archive (1 - true) or do not (0 - false). For use with all type except 'postbypost'. Defaults to 0,
* **echo** (boolean) Display the output (1 - true) or return it (0 - false). Defaults to 1. 

`eg_get_archives` accept an additional arguments:

* **pivot** (integer) The "pivot" year. Before the pivot, archives are listed i yearly mode, after archives are listed in monthly mode. Default: current year (currently 2009)
* **columns** (integer) Number of columns to display list. Possible values are 1 or 2. Default is 1.

== Screenshots ==

1. Widget configuration page
2. Archives list

== Frequently Asked Questions == 

None.

== Changelog ==

= Version 2.0.1 - Oct25th , 2011 =

* New: Bulgarian translation
* Change: updated German translation (thanks to Franck)
* Change: internal libraries

= Version 2.0.0 - Sept 16th, 2011 =

* Update the plugin for full WordPress 3.2.x compatibility,
* New: display list in 1 or 2 columns,
* New: ability to load plugin's stylesheet, or to use your own stylesheet,
* New: ability to disable the default WordPress' Archive widget,
* Change: widget library is using WordPress 2.8 API now.

= Version 1.1.1 - Sept 21st, 2009 =

* New: Deutsch translation (thanks to Franck Hempel)

= Version 1.1.0 - Aug 11th, 2009 =

* New: Allow multi-widget
* New: WP 2.8 compliant
* New: Translation into Russian (thanks to Fatcow)

= Version 1.0.1 - June 10th, 2009 =

* Bug fix: 
	* Wrong count of posts,
	* Limit parameter doesn't work,

= Version 1.0.0 - June 7th, 2009 =

* Initial release

== Licence ==

This plugin is released under the GPL, you can use it free of charge on your personal or commercial blog.

== Translations ==

The plugin comes with French and English translations, please refer to the [WordPress Codex](http://codex.wordpress.org/Installing_WordPress_in_Your_Language "Installing WordPress in Your Language") for more information about activating the translation. If you want to help to translate the plugin to your language, please have a look at the eg_series.pot file which contains all defintions and may be used with a [gettext](http://www.gnu.org/software/gettext/) editor like [Poedit](http://www.poedit.net/) (Windows).

