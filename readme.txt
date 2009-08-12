=== EG-Archives ===
Contributors: Emmanuel GEORJON
Donate link: http://www.emmanuelgeorjon.com/
Tags: archive, widget
Requires at least: 2.6
Tested up to: 2.8.3
Stable tag: 1.1.0

EG-Archives provides a widget (and a template tag) to display archives in yearly mode, AND monthly, according a specified pivot date.

== Description ==

The standard Archives widget doesn't accept a lot of parameters. You can just specify 

* a title, 
* if you want to display posts count or not.

With the **EG-Archives** widget, you can choose 

* the title,
* the format of the list (simple or dropdown list),
* the type: Yearly, Monthly, ... 

A specific type is added: the "mixed Yearly/monthly" type.
With this type, you can display list of archives in yearly mode before a specified year, and in monthly mode after this specified date. This type allows to shorten list, and is very useful if you don't publish too many posts per year.

= Translations =

The plugin comes with English, French and Russian. Thanks to the following people for their contributions:

* Russian (ru) - [Fatcow](http://www.fatcow.com)

If you want to help to translate the plugin to your language, please have a look at the eg_archives.pot file which contains all definitions and may be used with a [gettext](http://www.gnu.org/software/gettext/) editor like [Poedit](http://www.poedit.net/) (Windows).

If you have created your own language pack, or have an update of an existing one, you can send [gettext .po and .mo files](http://codex.wordpress.org/Translating_WordPress) to me so that I can bundle it into the plugin.

== Installation ==

1. The plugin is available for download on the WordPress repository,
1. Download the file `eg-archives.zip`, and uncompress it,
1. Upload the resulting files to a folder in `../wp-content/plugins/`,
1. Activate EG-Archives through the 'Plugins' menu in WordPress,
1. The plugin is ready to be used,

The plugin is now ready to be used.
You can also use the WordPress 2.7 features, and install the plugin directly from the WordPress interface.

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

`eg_get_archives` accept an additional argument:

* **pivot** (integer) The "pivot" year. Before the pivot, archives are listed i yearly mode, after archives are listed in monthly mode. Default: current year (currently 2009)

== Screenshots ==

1. Widget configuration page
2. Archives list

== Frequently Asked Questions == 

None.

== Changelog ==

= Version 1.1.0 - Aug 11th, 2009 =

* New: Translation into Russian

= Version 1.0.1 - June 10th, 2009 =

* Bugfix: Wrong count of posts,
* Bugfix: Limit parameter doesn't work,

= Version 1.0.0 - June 7th, 2009 =

* Initial release

== Licence ==

This plugin is released under the GPL, you can use it free of charge on your personal or commercial blog.


