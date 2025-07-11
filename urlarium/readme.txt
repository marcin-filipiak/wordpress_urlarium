=== URLarium ===
Contributors: marcin-filipiak
Tags: directory, websites, categories, links, listing
Requires at least: 5.8
Tested up to: 6.8
Requires PHP: 7.2
Stable tag: 1.0
License: GPLv2 or later
License URI: [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

Simple website directory plugin to categorize and list websites with name, description, and link.

== Description ==

**URLarium** allows you to create a directory of websites with categorized listings. Features include:

* 📁 Custom post type for websites with title, URL, and description
* 🏷️ Custom taxonomy for organizing websites into categories
* 🔗 Shortcode to display all categories or a specific category’s website list
* 🖥️ Admin metabox for entering website details
* 🎨 External CSS for styling the lists

Use shortcode `[urlarium]` to show all categories or `[urlarium category="category-slug"]` to show websites from one category.

== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory or install it via WordPress Plugin Directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Add new websites under **URLarium** menu in admin. Assign categories and fill URL, description.
4. Insert shortcode `[urlarium]` or `[urlarium category="slug"]` into pages or posts to display directories.

== Frequently Asked Questions ==

= How to display all website categories? =
Use shortcode `[urlarium]` anywhere in posts or pages.

= How to display websites from a specific category? =
Use `[urlarium category="category-slug"]` replacing `"category-slug"` with the category slug.

= Can I add website URL and description? =
Yes, these are entered via the Website Details metabox when editing or adding a new URLarium link.

= Is there styling included? =
Yes, the plugin enqueues a simple CSS file (`style.css`) for the directory lists.

== Screenshots ==

1. Admin screen for adding a new website with URL and description fields.
2. Frontend list of website categories (links).
3. Frontend list of websites under a selected category with clickable titles.

== Changelog ==

= 1.0 =

* Initial release with custom post type, taxonomy, metabox, shortcode, and styling.

== License ==

This plugin is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License v2 or later.

