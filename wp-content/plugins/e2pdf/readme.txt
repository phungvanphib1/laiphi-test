=== E2Pdf - Export To Pdf Tool for WordPress ===
Contributors: rasmarcus, oleksandrz
Donate link: https://e2pdf.com/
Tags: e2pdf, pro2pdf, pdf, create, edit, export, save, generation, pdftk, formidable, caldera, divi, forminator, forms, pdf viewer, create pdf, export pdf, save pdf, formidable pdf, caldera pdf, divi pdf, forminator pdf, forms pdf, wordpress pdf, pdf editor, export to pdf, export data, gravity, gravity pdf
Requires at least: 4.0
Tested up to: 6.1
Requires PHP: 5.3
Stable tag: 1.16.56
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Create and fill dynamic PDF documents with Formidable Forms, Gravity Forms, Caldera Forms, Forminator, Divi, Pages/Posts data, ACF and more...

== Description ==

= E2Pdf is the next generation PDF tool for Wordpress. =

This plugin includes:

* a PDF Document Viewer - Allow visitors to view static or dynamic PDF documents in Wordpress.
* a PDF Document Editor - Create/Edit new and existing PDF documents without leaving Wordpress.
* a PDF Forms Editor - Create/Edit new, existing, and auto-generated PDF Forms from the Dashboard.
* a PDF Data Injector - Merge data from Wordpress pages, posts, or web forms into PDF forms.
* a Generous Affiliate Program - 90-day cookies. 20% commission paid lifetime for all new payments.

= Learn all about E2Pdf =

* [FAQ](https://e2pdf.com/support/faq)
* [Help Desk](https://e2pdf.com/support/desk)
* [Documenation](https://e2pdf.com/support/docs)
* [YouTube](https://www.youtube.com/e2pdf)

= PDF DOCUMENT VIEWER: [e2pdf-view] =

* Allows users to view and print PDF documents without leaving your site.
* Preview dynamically created PDF documents prior to downloading, emailing, or purchasing.

= PDF DOCUMENT EDITOR: Built-in =

* Create a PDF from a blank document.
* Upload and edit existing PDF documents.
* Add/Edit text and images.
* Auto-generate PDF documents based on a Wordpress page or post.

= PDF FORMS EDITOR: Built-in =

* Create PDF forms from a blank document.
* Upload and edit existing PDF documents or forms, no need for third-party software.
* Auto-generate PDF forms based on a Wordpress page, post, or web form.
* Use actions and conditions to create dynamic PDF documents.

= PDF DATA INJECTOR: Remotely Generated¹ =

* Map Wordpress pages or post to PDF fields.
* Map web forms to PDF form fields.
* Map signature² fields to PDF form fields.
* Map images² to PDF form fields.

= EMAIL PDF OPTIONS =

* Send as email attachment.
* Send a link in email body to download PDF documents and forms. 

= SAVE DYNAMIC PDF TO SERVER =

* Save form filled PDF documents to static or dynamic folders on your server.

== EXTENSIONS: 3rd Party Integrations ==

* [Wordpress](https://e2pdf.com/extensions/wordpress)
* [Forminator](https://e2pdf.com/extensions/forminator)
* [Formidable Forms](https://e2pdf.com/extensions/formidable)
* [Gravity Forms](https://e2pdf.com/extensions/gravity)
* [Caldera Forms](https://e2pdf.com/extensions/caldera)
* [Divi Contact Forms](https://e2pdf.com/extensions/divi)
* [WooCommerce](https://e2pdf.com/extensions/woocommerce)

== APIs: 3rd Party Integrations ==

* Adobe Sign REST API

= IN DEVELOPMENT³ =

* Contact Form 7
* Ninja Forms

== Terms of Service ==

By continuing to use our plugin you are agreeing to our [Terms of Service](https://e2pdf.com/legal/terms).

== Additional Information, Definition and Explaination ==

¹ Remotely Generated: Due to the complex nature of the PDF file format, dynamic PDF documents are generating remotely with the E2Pdf API at E2Pdf.com. 
PRIVACY POLICY: We do not collect or store any web form submitted user private data that is sent to the API.

² Selected extension must include the signature field or image field.

³ In Development: Extensions to be added to E2Pdf. [Click here](https://e2pdf.com/support/desk) to request an integration, or the status of an integration.

== HISTORY ==

E2Pdf is the new and highly improved iteration of the [Formidable PRO2PDF plugin](https://wordpress.org/plugins/formidablepro-2-pdf/). Originally designed and coded in 2013 out of a need to print dynamic PDF documents from WordPress forms, PRO2PDF provided the automation necessary for a small insurance broker to produce far more business with the same number of employees.

Today, the E2Pdf plugin and Wordpress extension provide the entire WordPress community with a cost free method of creating dynamic PDF documents – without programming or coding – with one simple shortcode. More information can be found at [E2Pdf.com](https://e2pdf.com)

[youtube https://www.youtube.com/watch?v=BFu78n9-tcM]

== Installation ==

1. Go to your "Plugins" -> "Add New" page in your WordPress admin dashboard
2. Search for "E2Pdf"
3. Click the "Install Now" button
4. Activate the plugin through the "Plugins" menu
5. Create a new Template, activate and use one of the shortcodes available to add PDF to needed page/form and you're done!

== Frequently Asked Questions ==
= Support for Multisite installation =

Yes, plugin supports Network Activation.

= How to add new Fonts? =

To upload new fonts go to E2Pdf -> Settings -> Fonts. After successfull upload font will appear in the "Font Dropdown" in templates.

= Font inside fields for "Uploaded" PDF different =

At this moment, fields are fully recreated and controlled by E2Pdf. To use the original font, you must upload the font to E2Pdf and assign it to the PDF form field(s), or any other created objects from the PDF Builder.

= How to make PDF read-only? =

Set "Inline" checkbox "On" under "Settings" metabox -> Template while Template edit

= How to resize fields/html objects? =

Resize can be completed via "Double" Click on needed field/html object and resized or via "Right Mouse Click" -> "Properties"

== Screenshots ==

1. Export data to PDF from Admin Panel.
2. Templates list Page.
3. Creating new PDF Template.
4. Editing PDF Template.
5. PDF Template Object properties.
6. Settings Page.

== Changelog ==

= 1.16.56 =
*Release Date - 23 January 2023*

* Add: "Forminator" :html filter
* Add: "Forminator" group and repeater fields support
* Add: "Forminator" global {address} / {time} field slugs support
* Add: [e2pdf-format-output] "strip_shortcodes" and "do_shortcode" filters
* Add: "Gravity Forms Survey Add-On" "Visual Mapper" and "Auto PDF" support
* Add: "Auto PDF" "consent" field type support for "Forminator"
* Fix: "Auto PDF" fails in some cases for "Forminator"
* Fix: "Divi Contact Form Helper" plugin compatibility
* Fix: "remove_tags" filter fails in some cases
* Fix: Current Post for Toolset View
* Fix: Fails to create SQL tables in some cases
* Fix: Compatibility with UltimateMember Homepage Redirect
* Improvement: Additional filter hooks

= 1.16.55 =
*Release Date - 15 November 2022*

* Fix: PHP8 error in some cases
* Fix: Formidable Forms Toggle Buttons inside "Visual Mapper"

= 1.16.54 =
*Release Date - 27 September 2022*

* Fix: Incorrect checkbox value with "Visual Mapper" for "Formidable Forms"
* Fix: Compatibility with WooCommerce Enforcing Rules enabled

= 1.16.53 =
*Release Date - 03 September 2022*

* Fix: Formidable Forms Total Formatted Fields Visual Mapper support
* Fix: "PDF name" does not rendered correctly at "E2Pdf" -> "Export"
* Fix: Multiple "File URL" for WooCommerce

= 1.16.52 =
*Release Date - 26 June 2022*

* Fix: Forminator 1.17.2 compatibility fix
* Fix: "WordPress" connected [e2pdf-exclude] support inside Formidable Forms View beforeContent and afterContent

= 1.16.51 =
*Release Date - 23 June 2022*

* Fix: Forminator 1.16.x conditions fails in some cases

= 1.16.50 =
*Release Date - 03 June 2022*

* Fix: "Visual Mapper" / "Auto PDF" incorrect "checkbox" & "radio" options in some cases

= 1.16.49 =
*Release Date - 31 May 2022*

* Fix: "Calculation" field slug not rendered with Forminator 1.16.x

= 1.16.48 =
*Release Date - 24 May 2022*

* Fix: Compatibility with Forminator 1.16.x
* Add: Define 3rd party plugins activation

= 1.16.46 =
*Release Date - 13 April 2022*

* Fix: Prevent "pre-upload" PDF to be truncated
* Fix: MacOS "Command" support

= 1.16.45 =
*Release Date - 08 February 2022*

* Improvement: Minor Enhancements

= 1.16.44 =
*Release Date - 02 November 2021*

* Add: Minor improvements

= 1.16.43 =
*Release Date - 29 October 2021*

* Add: "convert" attribute support for [e2pdf-wp] shortcode
* Add: [e2pdf-wp-term] shortcode

= 1.16.41 =
*Release Date - 19 October 2021*

* Add: Order Product Item Meta Keys support for WooCommerce "Visual Mapper"
* Add: Custom "Uploads" folder support
* Add: "args" support request for [e2pdf-zapier] shortcode
* Add: "Protected" images support for Formidable Forms
* Add: WooCommerce Order Template [e2pdf-download] shortcode support as File URL
* Fix: Error on "Divi" page builder content
* Fix: WooCommerce [e2pdf-wc-product key="get_image"] "size" attribute
* Fix: GravityWiz Nested Forms product field
* Fix: "Gravity Forms" 2.5 "Visual Mapper"
* Fix: Forminator "Calculation" field for "Auto PDF"
* Fix: Forminator "Multi File Upload" field for "Visual Mapper"
* Fix: Forminator "Calculation" field for "Visual Mapper"
* Fix: WooCommerce products with same IDs and different meta
* Fix: WordPress 5.8.0 "widget" dynamic shortcode support
* Fix: [e2pdf-save] shortcode fails in some cases

= 1.16.28 =
*Release Date - 21 September 2021*

* Add: E2Pdf Templates re-activation process
* Add: Additional attributes for [e2pdf-wp-posts] shortcode
* Add: "path" attribute for [e2pdf-foreach-value] shortcode
* Add: "Conditional Shortcode" support for Gravity Forms
* Add: "list-style-image" CSS support
* Add: "list-style-image-size" CSS support
* Add: "list-style-image-width", "list-style-image-height" CSS support
* Add: [e2pdf-attachment] shortcode support for MemberPress
* Add: Additional filters
* Fix: PDF Generation fails in some cases
* Fix: WPML PDF URL compatibility
* Fix: Clear cache for SG Optimizer (Memcached) and WPML
* Fix: Shortcode wrappers with QRCode and BarCode
* Fix: Incorrect attribute label value for WooCommerce Variation
* Fix: [e2pdf-url] shortcode support for backend
* Fix: "Visual Mapper" Formidable Forms for "radio", "checkbox" as images
* Fix: Incorrect PDF objects/fields position with some pre-uploaded PDFs
* Fix: Minor GUI bug-fixes
* Improvement: id="current" for [e2pdf-user] shortcode removed for "Visual Mapper"

= 1.16.16 =
*Release Date - 09 August 2021*

* Add: PDF download URL Rewrite
* Add: "get_variation_attributes" key support for WooCommerce Variation
* Add: "get_variation_attribute" key support for WooCommerce Variation
* Add: Dynamic dataset support for WooCommerce Variation Description
* Add: WooCommerce Checkout PDF download button setting
* Add: WooCommerce Cart PDF button order setting
* Add: Additional hooks
* Fix: HappyAddons for Elementor compatibility
* Fix: "Backward compatibility" for WooCommerce
* Fix: "get_attribute" fails in some cases for WooCommerce Variation
* Fix: Minor bug fixes
* Improvement: "get_attributes" shortcode for WooCommerce Variation will get merged attributes

= 1.16.09 =
*Release Date - 06 July 2021*

* Add: Additional hooks
* Add: Simplified Arabic fonts support
* Add: [e2pdf-wp-posts] shortcode
* Add: [e2pdf-foreach] shortcode support for WordPress
* Add: "dynamic" IDs for [e2pdf-wp] and [e2pdf-user] shortcodes
* Add: "Flatsome" global tab content dynamic shortcodes support
* Add: "Quiet Zone Size" property for QR Code and Barcode
* Fix: "Transparent" background support for QR Code and Barcode
* Fix: SG Optimizer HTML Minify compatibility
* Fix: Minor bug fixes
* Improvement: Visual Mapper for WordPress

= 1.16.02 =
*Release Date - 15 June 2021*

* Fix: WooCommerce incorrect render of non-existing value

= 1.16.01 =
*Release Date - 10 June 2021*

* Fix: Some PDFs can't be uploaded

= 1.16.00 =
*Release Date - 10 June 2021*

* Add: WooCommerce "Auto PDF" for Products
* Add: WooCommerce "Auto PDF" for Variations
* Add: WooCommerce "Auto PDF" for Orders
* Add: WooCommerce "Auto PDF" for Cart
* Add: [e2pdf-foreach] shortcode support for WooCommerce
* Add: "pre", "after" attributes for [e2pdf-format-output] shortcode
* Add: "mixed_tags", "closed_tags" attributes for [e2pdf-format-output] shortcode along with "remove_tags" filter
* Fix: WooCommerce download product PDF name
* Fix: WooCommerce invoice button title
* Improvement: Shortcodes for WooCommerce
* Improvement: Visual Mapper for WooCommerce

= 1.15.14 =
*Release Date - 09 June 2021*

* Add: WordPress "taxonomy" "Visual Mapper" support
* Add: "permalink" and "post_permalink" support for WooCommerce extension
* Add: [e2pdf-url] shortcode support
* Add: [e2pdf-zapier] shortcode support for WooCommerce
* Add: "strip_tags_allowed" attribute for [e2pdf-format-output] shortcode
* Add: Filter "WooCommerce" Orders Templates by "Product and Variation IDs"
* Add: "permalink" and "post_permalink" support for WordPress extension
* Add: "explode_filter" for [e2pdf-format-output]
* Add: WPBakery Page Builder Image Object Link Support
* Add: CSS "auto" height property for "td"
* Add: "CSS Section" priority option
* Add: QR Code support
* Add: Barcode support
* Add: Export to "JPG" format support
* Add: "locale" attribute support for [e2pdf-format-date] shortcode
* Add: "Merged Items" support for Formidable Forms
* Add: "Z-index" negative position support
* Add: "Fonts Cache" support
* Add: Fixed height support for HTML table columns
* Add: Rowspan support for HTML table columns
* Add: GravityWiz Nested Forms "Auto PDF" support
* Add: GravityWiz Nested Forms "Visual Mapper" support
* Add: WooCommerce additional product item meta keys
* Add: [default-message] support for Formidable Forms
* Fix: Formidable Forms "Auto Map" duplicated brackets
* Fix: Missing fields with same name on pre-made PDFs usage
* Fix: Do not show "sub-forms" under items list for Formidable Forms
* Fix: Incorrect "color" prevent PDF from generation
* Fix: "Cache" not enabled by default
* Fix: "Multiple Themes" compatibility fix
* Fix: "Beaver Builder" Templates shortcodes fix
* Fix: Forminator "Auto PDF" fails in some cases
* Fix: Shortcodes render for Caldera Forms with "SendGrid" enabled
* Fix: [e2pdf-format-output] "explode" fails in some cases
* Fix: "Nonce" check prevents some pages to load
* Fix: "Divi" shortcodes not rendered inside post content
* Improvement: "path" attribute return empty value instead empty array if not found
* Improvement: "hr" width 100% by default
* Improvement: pdf.js update (v2.6.347)

= 1.13.40 =
*Release Date - 02 May 2021*

* Add: "[id]" as shortcode attribute for "WooCommerce", "WordPress"
* Add: List of available "Image" sizes
* Add: Formidable Forms "Field Keys" support for "Auto Map"
* Add: [e2pdf-download] shortcode support as WooCommerce attribute value
* Add: "Visual Mapper" new shortcodes support for WooCommerce
* Add: Product and Order Items meta data output support for WooCommerce
* Add: Product attributes output for WooCommerce
* Add: Conditions to be used with attributes for WooCommerce email attachments
* Add: EU API servers for PDF generation
* Add: Dynamic dataset support for "Divi"
* Add: "table" float CSS support
* Add: Zapier support
* Add: [e2pdf-zapier] shortcode
* Add: A4 (LANDSCAPE) size preset
* Add: Additional filters to hook "shortcode" attributes
* Add: "wpautop" filter to [e2pdf-format-output]
* Add: Remove "unsupported" inline html tags automatically
* Add: "data:image" image support
* Add: Multiple "Other" checkbox support for Formidable Forms
* Add: "Tab Order" setting
* Add: GravityWiz Nested Forms custom E2Pdf templates
* Add: WooCommerce Invoice Status Options
* Add: "html_entity_decode" filter to [e2pdf-format-output]
* Add: Support "Other" value for Gravity Forms "Auto PDF"
* Add: WooCommerce support
* Add: Embedded Formidable Forms for "Auto" PDF
* Add: Global "RTL" option
* Add: Global "Text Align" option
* Add: Forminator built-in additional slugs render
* Add: Forminator eSignature "Auto PDF" support
* Add: Forminator eSignature Visual Mapper support
* Fix: Incorrect page detection in some cases
* Fix: Forminator 1.14.11 compatibility fix
* Fix: Minor style fixes
* Fix: Forminator 1.14.10 "Visual Mapper" "Auto PDF"
* Fix: PHP8 deprecation notices
* Fix: PHP8 compatibility fix
* Fix: Relative "dir" attribute for [e2pdf-save] shortcode incorrect path in some cases
* Fix: "Number" fields not working in some cases
* Fix: Forminator "Auto PDF" radio fields
* Fix: Incorrect "Order ID" for WooCommerce in some cases
* Fix: Incorrect "options" render of "lookup" and "dynamic" Formidable Form fields
* Fix: Compatiability fix with "Dynamic Conditions" for "Elementor"
* Fix: Forminator do not show all forms list
* Fix: Forminator incorrect form names in some cases
* Fix: "e2pdf_model_shortcode_e2pdf_zapier_extension_options" filter
* Fix: WooCommerce incorrect product data in some cases
* Fix: Gravity Forms "Conditional Shortcode" fix for [e2pdf-attachment] shortcode
* Fix: Compatibility with "All 404 Redirect to Homepage" plugin
* Fix: Incorrectly fired "auto" download in some cases
* Fix: "Rulers Grid" not showing in some cases
* Fix: PHP Fatal Error on Gravity Forms in some cases
* Fix: PHP warning on "E2Pdf" -> "Export" in some cases
* Fix: "Z-index" option PDF standards
* Fix: Incorrect position of "Radio" buttons in case of "Hide" page action
* Fix: Caldera Forms incorrect slugs truncate in some cases
* Fix: Emails failed on WooCommerce in some cases
* Fix: Incorrect font render in some cases
* Fix: "0" position flush elements by default while PDF reupload
* Fix: Compatibility with "Minify HTML" plugin
* Fix: "Other" value for Gravity Forms Visual Mapper
* Fix: jQuery compatibility fix
* Fix: Visual mapper failed for Gravity Forms in some cases
* Fix: Duplicated attachments for Forminator
* Fix: Forminator Visual Mapper
* Fix: Forminator 1.13.5 compatibility fix
* Fix: Rendered value incorrect in some cases
* Fix: Visual Mapper doesn't render styles on template load
* Fix: Caldera Forms incorrect "Image" value render
* Fix: Caldera Forms PHP warning
* Fix: Incorrect images inside "Cart" with 3rd party WooCommerce plugins
* Fix: Empty "attributes" not fired correctly in some cases
* Fix: Minor bug fixes
* Improvement: Forminator "Auto PDF" fields description support
* Improvement: Better support of "lookup" and "dynamic" Formidable Form fields
* Improvement: "Visual Mapper" for Formidable Forms
* Improvement: "Add Action"/"Add Condition" actions
* Improvement: "Visual Mapper" for WooCommerce
* Improvement: [e2pdf-format-date] extended shortcode attributes and values
* Improvement: Do not unselect elements while "Ctrl" pressed
* Improvement: Max font-size increased to 512
* Improvement: WordPress "Visual Mapper" forced to use standard shortcodes
* Improvement: "Other" radio option better support for Formidable Forms
* Improvement: WordPress shortcodes render
* Improvement: WooCommerce support
* Improvement: "flatten" option enabled by default
* Improvement: Visual Mapper for Forminator
* Improvement: HTML/CSS render

= 1.11.08 =
*Release Date - 24 August 2020*

* Add: "remove_tags" attribute and filter for [e2pdf-format-output] shortcode
* Add: [post_content] "output" shortcode attribute
* Add: [post_author] "subkey" shortcode attribute
* Add: [e2pdf-user] extended shortcode attributes
* Add: "user_avatar" key support for [e2pdf-user] shortcode
* Add: "substr" filter for [e2pdf-format-output] shortcode
* Add: "View" and "Download" action on E2Pdf -> Export
* Add: "responsive" attribute for [e2pdf-view]
* Add: Custom CSS for PDF viewer
* Add: Loading text for PDF viewer
* Add: Additional debug information
* Add: "offset" attribute for [e2pdf-format-date] shortcode
* Add: Auto restore License Key process
* Add: "create_dir", "create_index", "create_htaccess" attributes for [e2pdf-save] shortcode
* Add: Formidable Forms "Sections" for Auto PDF
* Add: "e2pdf_model_e2pdf_shortcode_attachment_path" filter
* Add: "e2pdf_model_e2pdf_shortcode_save_path" filter
* Add: "e2pdf_controller_frontend_e2pdf_download" action
* Add: "e2pdf_controller_frontend_e2pdf_download_response" action
* Add: "target" attribute for "e2pdf-download"
* Add: Text Rotation option
* Add: Vertical align for "HTML" Object
* Add: CSS margin support for p tags
* Add: "site_url" for "e2pdf-view" shortcode
* Add: "e2pdf_extension_render_shortcodes_pre_do_shortcode" filter
* Add: "e2pdf_extension_render_shortcodes_after_do_shortcode" filter
* Add: "e2pdf_model_shortcode_site_url" filter
* Add: "e2pdf_model_shortcode_e2pdf_download_site_url" filter
* Add: "e2pdf_model_shortcode_e2pdf_view_site_url" filter
* Fix: [id] shortcode fix for WordPress extension
* Fix: Incorrect "ireplace" function in some cases
* Fix: Compatibility Fix WordPress 5.5
* Fix: PDFs failed to load in some cases
* Fix: PHP warning on Gravity Forms in some cases
* Fix: Incorrect order of Formidable Forms Repeater fields in some cases
* Fix: Gravity Forms entries limit fix
* Fix: "headers" PHP notice
* Fix: Viewer failed to load correctly in some cases
* Fix: Divi Contact Forms undefined variable notice
* Fix: Divi Contact Forms shortcodes do not fire with spam protection on
* Fix: Incorrect output of meta key if value is set to 0
* Fix: "e2pdf-view" shortcode for non ajax submissions Caldera Forms
* Fix: "dir" attribute not rendered dynamic values in some cases
* Fix: Gravity Forms "Visual Mapper" fails in some cases 
* Fix: Download "file name" incorrect for Microsoft Edge, Safari
* Fix: "inline" attribute ignored in some cases
* Fix: "W3 Total Cache" compatibility fix
* Fix: Incorrect element position after element resize in some cases
* Fix: Minor bug fixes
* Improvement: Visual Mapper for Formidable Forms
* Improvement: Properties settings
* Improvement: UI

= 1.10.11 =
*Release Date - 01 May 2020*

* Add: "e2pdf_controller_templates_max_font_size" filter
* Add: "e2pdf_controller_templates_max_line_height" filter
* Add: "Release Candidate" update option
* Add: "Only Image" for Image/Signature object
* Add: Value "pre-render" filter
* Add: Extended [meta] shortcode for WordPress
* Add: "Caldera Forms" Auto Responder support
* Add: "Preg Match All" filter
* Add: Maintenance Section
* Fix: Divi Contact Forms shortcodes not rendered in some cases
* Fix: "attachment_url" and "attachment_image_url" meta key attributes not fired in some cases
* Fix: Minor style fixes
* Fix: Caldera Forms multipage
* Fix: "Elementor" shortcode widget failed to render
* Fix: Fonts conflict in some cases
* Fix: Divi Contact Forms 4.3.3 Visual Mapper
* Fix: Incorrect "compression" setting in some cases
* Fix: PDF can't be parsed in some cases
* Fix: Firefox mass elements select
* Fix: HTML images not rendered in some cases
* Fix: Shortcode wrappers incorrectly fired on checkbox value
* Fix: Caldera Forms checkbox not rendered in some cases
* Fix: DB columns missed after update in some cases
* Improvement: WordPress Extension
* Improvement: HTML Support
* Improvement: Better image detection
* Improvement: Visual Builder
* Improvement: Image render

= 1.09.10 =
*Release Date - 15 January 2020*

* Add: "Letter Spacing" setting
* Add: "Merge" option for "Actions" and "Conditions"
* Add: Custom "CSS" Html Object support
* Add: Url Generation Link Format option
* Add: .otf fonts support
* Add: "E2Pdf Connected Account" setting
* Add: Templates Bulk Actions: "Activation" and "Deactivation"
* Add: "Global Actions"
* Add: "single_page_mode", "hide", "background", "border" attributes for [e2pdf-view]
* Add: %%_wp_http_referer%%, %%e2pdf_entry_id%% shortcodes support for "Divi"
* Add: Database Debug information
* Add: id="current" for [e2pdf-user] shortcode
* Add: "responsive", "viewer" attributes for [e2pdf-view]
* Add: Support of shortcodes which contains [id] for "Formidable Forms"
* Add: Option to disable WYSIWYG Editor for HTML Object
* Add: Option to disable Local Images Load
* Add: Option to change Images Load Timeout
* Add: "justify" option for HTML Object
* Add: Caldera Forms Conditional Fields support
* Add: "Visual Mapper" show hidden fields option
* Add: Template Revisions
* Fix: Caldera Forms minor bug-fixes
* Fix: Caldera Forms Connected Forms 1.2.2 compatibility
* Fix: Missed font characters in some cases
* Fix: Missed signature images on view action
* Fix: Incorrect fields render for "Actions" and "Conditions"
* Fix: "New Lines to BR" filter not fired on view/preview action
* Fix: "UC Browser" compatibility
* Fix: "Debug" page doesn't appear on "Turn on Debug mode" setting
* Fix: [frm-math] shortcode for "Formidable Forms" usage inside Template directly
* Fix: WordPress Page Builder – Beaver Builder Compatibility
* Fix: RTL Support
* Fix: Hidden pages missed on "Preview" and "View" Template action
* Fix: "Divi Contact Forms" auto download
* Fix: WordPress 5.3 Styles
* Fix: Missed fields for "Gravity Forms" inside "Visual Mapper"
* Fix: "Hide" action for pages fired incorrectly in some cases
* Fix: Incorrect render of values in some cases
* Fix: "Visual Mapper" fails for "Caldera Forms" in some cases
* Fix: UI minor bug fixes
* Fix: Compatibility with Divi 3.27
* Fix: Incorrect render of values in some cases
* Fix: Fields data were not saving in some cases
* Improvement: Previous versions compatibility
* Improvement: Extended "Debug" information
* Improvement: pdf.js update (v2.3.200)
* Improvement: Templates load
* Improvement: Translation
* Improvement: Errors and Message Notifications
* Improvement: Optimization

= 1.08.09 =
*Release Date - 07 August 2019*

* Add: "Nl2br" option for "e2pdf-html"
* Add: Unlink paid License Key option
* Add: "attachment_image_url" attribute for [e2pdf-meta] shortcode support
* Add: "Events Manager" render shortcodes support
* Add: "Divi Builder" support
* Add: Extended 3rd party plugins support for WordPress extension
* Add: "Visual Mapper" meta keys support for WordPress extension
* Add: "nl2br" filter for [e2pdf-format-output] shortcode
* Add: Permission settings
* Add: Dynamic shortcode support for "WordPress" extension inside Widgets
* Add: "Hide (If Empty)" and "Hide Page (if Empty)" properties for HTML object
* Add: "Preg Replace" option for fields and objects
* Add: "Replace Value" and "Auto-Close" options for "Visual Mapper"
* Fix: "Visual Mapper" styles for Forminator
* Fix: Conflict with Elementor
* Fix: Conflict with SiteOrigin
* Fix: "Caldera Forms" incorrect support for checkbox with "Show Values" option
* Fix: "preg_replace" error
* Improvement: "Auto PDF"
* Improvement: UI
* Improvement: Optimization

= 1.07.11 =
*Release Date - 24 June 2019*

* Add: Serialized post meta fields support
* Add: "attachment_url", "path" attributes for WordPress [meta key="x"] shortcode
* Add: Post terms support
* Add: [terms key="x"] shortcode for WordPress posts/pages support
* Add: "pdf" attribute for [e2pdf-download] shortcode
* Add: "Popup Maker" support
* Add: "overwrite" attribute for [e2pdf-save] shortcode
* Add: "Entries" cache support
* Add: Cache support
* Add: "Auto Form" Gravity Forms support
* Add: "Close" button while creating new template
* Add: Gravity Forms support
* Add: "attachment" attribute for [e2pdf-save] shortcode
* Add: [e2pdf-arg] shortcode suppport
* Add: [post_thumbnail] shortcode for WordPress extension
* Fix: "Gravity Forms" does not render values inside mail notification in some cases
* Fix: Missing "Actions" and "Conditions" for pages while re-upload PDF
* Fix: "Visual Mapper" fails for "Caldera Forms" in some cases
* Fix: "Visual Mapper" not rendered correctly for "Gravity Forms" in some cases
* Fix: "Auto Form" Caldera Forms dropdown
* Fix: PHP warnings on signature field in some cases
* Fix: "Auto PDF" radio group names
* Fix: Some shortcodes not fired correctly
* Fix: Backward compatibility with WordPress 4.0
* Fix: "Auto PDF" option visible on extension change
* Fix: New pages do not respect global E2Pdf Template size option
* Fix: Incorrect file name while download in some cases
* Improvement: Better support for 3rd party WordPress plugins
* Improvement: Optimization
* Improvement: UI
* Improvement: Translation
* Improvement: Filters

= 1.06.02 =
*Release Date - 10 April 2019*

* Add: Custom post types support
* Add: [e2pdf-user] shortcode support
* Add: [e2pdf-wp] shortcode support
* Add: [e2pdf-content] shortcode support
* Add: Custom field names
* Add: "Auto Form from PDF" additional options
* Add: "Meta" Title, Subject, Author and Keywords PDF options
* Add: "e2pdf_extension_render_shortcodes_tags" filter
* Add: [e2pdf-view] shortcode additional attributes: page, zoom, nameddest, pagemode
* Fix: "Error" show if failed while creating "Template"
* Fix: WordPress Pages/Posts not showing all items
* Fix: Notice on "e2pdf-image" and "e2pdf-signature" render
* Fix: [e2pdf-exclude] not process shortcodes inside
* Fix: [e2pdf-download] incorrect button title in some cases
* Fix: "Attachments" missing in some cases due incorrect "PDF Name"
* Fix: "Auto Form from PDF" pre-built template radio/checkbox empty
* Fix: "Import" item replace shortcodes inside "Email" body and "Success Messages"
* Improvement: WordPress extension
* Improvement: pdf.js update (v2.0.943)
* Improvement: "Deactivate" template while moving to "Trash"
* Improvement: Translation

= 1.05.03 =
*Release Date - 23 February 2019*

* Add: "Auto Form" from pre-built E2Pdf Template
* Add: "Formidable Forms" Item import options
* Add: "Pagination" and "Screen" options for Templates list
* Fix: "Forminator" incorrect field IDs while "Auto Form"
* Fix: "Replace PDF" failed in some cases (Chrome 72.0.3626.109)
* Fix: "Replace PDF" css
* Fix: Caldera Forms "Auto Form" empty field values
* Fix: Backup failed in some cases
* Fix: "E2Pdf" css style affected other pages
* Improvement: Templates activation/deactivation action
* Improvement: Optimization
* Improvement: Translation

= 1.04.07 =
*Release Date - 11 February 2019*

* Add: Possibility to disable extensions
* Add: "Forminator" "Disable store submissions in my database" support
* Add: "Caldera Forms" Connected Forms support
* Add: "Download" and "View" links generation based on saved PDFs
* Add: Auto Form from PDF for "Formidable Forms", "Caldera Forms", "Forminator"
* Add: "highlight" property for e2pdf-link
* Add: "search", "replace", "ireplace" attributes for [e2pdf-format-output] shortcode
* Add: Changing "Page ID" for elements with "Actions" and "Conditions"
* Fix: "PHP" Warning on empty "LIKE" "NOT_LIKE" condition value
* Fix: "Forminator" textarea field type "Auto PDF"
* Fix: "Visual Mapper" for "Forminator PRO" 1.6.1
* Fix: "Formidable Forms" Signature field (2.0.1) compatibility
* Fix: Image render failed in some cases
* Fix: "Visual Mapper" not rendered correctly for "Forminator" in some cases
* Fix: "Incorrect" element position with "Auto PDF" and "Free" License Type in some cases
* Fix: "Filename" for [e2pdf-view] shortcode
* Fix: "Pages" and "Elements" possible overload issue
* Fix: "e2pdf-signature" failed on load in some cases
* Improvement: Translation
* Improvement: Templates list load time

= 1.03.07 =
*Release Date - 24 December 2018*

* Add: Additional checks for "Visual Mapper"
* Add: Formidable Forms "Repeatable" fields support for e2pdf-" shortcodes
* Add: Display element "Type" inside properties window
* Fix: "Formidable Forms" Visual Mapper error with address field in some cases
* Fix: Z-index issue in some cases
* Fix: "Mozilla Firefox" PDF re-upload new tab
* Fix: Incorrect page size after E2Pdf Template load in some cases
* Fix: "Divi" extension compatibility fix with 3.18.7
* Fix: "e2pdf-format-output" shortcode warning fix
* Fix: "e2pdf-" shortcodes incorrect render for image and signature field types
* Fix: PHP warnings on settings page
* Fix: "auto" and "inline" options failed on "false" state
* Fix: "frontend.js" missed for "admin" users
* Fix: Default value for border color
* Fix: "Border" on fields after editing PDF
* Fix: Incorrect "HTML" element text position
* Fix: Incorrect "HTML" size
* Fix: "Divi" delete item error in some cases
* Fix: "Actions" and "Conditions" replace shortcodes among import action
* Fix: "Upload PDF" item and extension not updated in some cases
* Fix: "Rectangle" minimum width
* Fix: "Visual Mapper" encoding issue in some cases
* Fix: "Visual Mapper" for "Formidable Forms" showed draft entry in some cases
* Fix: "Visual Mapper" memory leak
* Fix: "Visual Mapper" failed in some cases
* Improvement: "Visual Mapper" checks
* Improvement: "Image" load optimization
* Improvement: Text positions inside inputs
* Improvement: Auto PDF
* Improvement: UI
* Improvement: Translation

= 1.02.02 =
*Release Date - 02 December 2018*

* Add: "Adobe Sign" REST API support
* Fix: "Actions" not fired in some cases
* Fix: "Divi" duplicate replacement of value in some cases
* Fix: "Forminator" empty forms list while creating Template
* Fix: Notifications not showed in some cases
* Fix: Shortcode attributes not rendered correctly in some cases
* Improvement: Optimization

= 1.01.01 =
*Release Date - 26 October 2018*

* Add: "Forminator" support
* Add: Extensions unlock
* Fix: Minor bug fixes

= 1.00.13 =
*Release Date - 15 October 2018*

* Add: "Line Height" option for "textarea" field type
* Add: "Signature" field
* Add: "E-signature" field type
* Add: Typed "Signature" support for all extensions
* Add: "length" property for "input", "textarea" fields
* Add: "comb" (Combination of Characters) property for "input", "textarea" fields
* Add: Notification on failed PDF re-upload
* Add: Notification on failed PDF upload
* Add: "class" attribute for "e2pdf-download" and "e2pdf-view" shortcodes
* Add: Privacy Policy
* Fix: "Divi" item not found in some cases
* Fix: Typed "Signature" color fix
* Fix: Minor style fixes
* Fix: Checkbox value contains comma
* Fix: Signature text generation Formidable Forms
* Fix: Multiple repeat sections Formidable Forms
* Fix: Mozilla Firefox compatibility
* Improvement: "Visual Mapper"
* Improvement: Signature quality, options
* Improvement: Update Process

= 1.00.00 =
*Release Date - 20 August 2018*

* Initial Release

== Upgrade Notice ==

= 1.00.00 =

Initial Release
