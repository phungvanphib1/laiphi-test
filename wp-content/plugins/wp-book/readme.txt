=== WP Book ===
Contributors: rajanit2000  
Tags: pdf, mpdf, post, pages, download  
Requires at least: 3.8  
Tested up to: 4.9.8  
Stable tag: 1.0   

Download your posts, pages and custom post as a PDF Book in few clicks

== Description ==

Wp Book is a WordPress Plugin that allows you to create PDF book for your posts, pages custom posts on different conditions and filters.

Check out the [WPBook Overview Video](https://www.youtube.com/watch?v=nBIG8jT8aNY) below.

### Features

The following conditions and filters are supported.

#### Post Filters options

- Post type
- Number of posts
- Post order
- Post order by

#### PDF Layout options

- Post type
- Number of posts
- Post order
- Post order by

#### Sorting

- Support manual sorting drag and drop

== Installation ==

The simplest way to install the plugin is to use the built-in automatic plugin installer. Go to plugins -> Add New and then enter `wp-book` to automatically install it.

If for some reason the above method doesn't work then you can download the plugin as a zip file, extract it and then use your favorite FTP client and then upload the contents of the zip file to the wp-content/plugins/ directory of your WordPress installation and then activate the Plugin from Plugins page.

Goto `Settings > WP Book`

== Credits ==

Thanks to [mPDF](https://mpdf.github.io/) PHP library, create pdf using PHP.
Thanks to [jQueryUI](https://jqueryui.com/) JavaScript library, which the Plugin uses.
Thanks to [select2](https://select2.github.io/) JavaScript library, which the Plugin uses.

== Frequently Asked Questions ==

= After installing the Plugin, I just see a blank page. =

This can happen if you have huge number of posts and your server is very underpowered. Check your PHP error log to see if there are any errors and correct them. The most common problems are script timeout or running out of memory. Change your PHP.ini file and increase the script timeout and/or amount of memory used by PHP process. 

In particular try to change the following settings

*   `max_execution_time = 600` - Maximum execution time of each script, in seconds
*   `max_input_time = 30` - Maximum amount of time each script may spend parsing request data
*   `memory_limit = 256M` - Maximum amount of memory a script may consume

= Temporary files directory "/wp-book/vendor/mpdf/mpdf/src/Config/../../tmp" is not writable issue. =

This can happen when the plugin's tmp directory does not have write permission. You need to setup 777 permission for tmp directory. 

Its located `/wp-content/plugins/wp-book/vendor/mpdf/mpdf/tmp`

== Screenshots ==

1. Home page for WP Book.

2. File created successfully.

3. PDF sample

4. Post type filters

5. Page format options

6. Header and Footer

7. Predefined templates

== Changelog ==

= v1.0 =

- Initial release



