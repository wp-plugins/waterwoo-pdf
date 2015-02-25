=== WaterWoo PDF Plugin ===
Contributors: littlepackage
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=PB2CFX8H4V49L
Tags: book, copyright, digital, ebook, ecommerce, e-commerce, file, marca de agua, pdf, plugin, property, protection, publishing, security, signature, watermark, watermarking, woocommerce
Requires at least: 3.7
Tested up to: 4.1
Stable tag: 1.0.11
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Protect your intellectual property! WaterWoo PDF allows WooCommerce site administrators to apply custom watermarks to PDFs upon sale.

== Description ==
WaterWoo PDF is a plugin that adds a watermark to every page of your sold PDF file(s). The watermark is customizable with font face, font color, font size, placement, and text. Not only that, but since the watermark is added when the download button is clicked (either on the customer's order confirmation page or email), the watermark can include customer-specific data such as the customer's first name, last name, and email. Your watermark is highly customizable and manipulatable, practically magic!

**Features:**

* Watermark only designated PDF downloads (as specified by you), or *all* PDF downloads from your site
* Files do not need to be in a specific directory
* Super customizable placement: watermark can be moved all over the page, allowing for different paper sizes (such as letter, A4, legal, etc)
* Watermark is applied to **all** pages of **every** PDF purchased
* Watermarks upon click of either the customer's order confirmation page link or email order confirmation link
* Dynamic customer data inputs (customer first name, last name, email, order paid date, and phone)
* Choice of font face, color, size and placement (horizontal line of text anywhere on the page).

**Premium version:**

[WaterWoo Premium](http://cap.little-package.com/shop/waterwoo-pdf-premium "WaterWoo PDF Premium Version") offers these helpful extra features:

* Watermark all PDF files with same settings OR set individual watermarks per product or even per product variation!
* Supports all versions of Adobe PDF (through 1.7)
* Rotatable transparent page overlay watermark, apart from footer watermark (two watermark locations!)
* Additional text formatting options, such as font color and style (bold, italics)
* Additional font (Deja Vu) adding more international character support
* Additional dynamic customer data input (business name, order paid date)
* Begin watermark on selected page of PDF document (to avoid watermarking a cover page, for example)
* Optionally password protect and/or encrypt PDF files
* Optionally prevent copying, annotating, or modifying of your PDF files
* Test watermark and/or manually watermark a file on the fly, from the admin panel

== Installation ==

= To install plugin =
1. Upload the entire "waterwoo-pdf" folder to the "/wp-content/plugins/" directory.
2. Activate the plugin through the "Plugins" menu in WordPress.
3. Visit WooCommerce->Settings->Watermark tab to set your plugin preferences.
4. **Please test your watermarking** by making mock purchases before going live to make sure it works and looks great!
5.  Note: for this to work you need to have pretty URLs enabled from the WP settings. Otherwise a 404 error will be thrown.

= To remove plugin: =

1. Deactivate plugin through the 'Plugins' menu in WordPress

2. Delete plugin through the 'Plugins' menu in WordPress


== Frequently Asked Questions ==
= Where do I change watermark settings? =
You can find the WaterWoo settings page by clicking on the "settings" link under the WaterWoo PDF plugin title on your Wordpress plugins panel, or by navigating to the WooCommerce->Settings->Watermark tab.

= It doesn't work =
First make sure you have checked the box at the top of your settings page (Woocommerce -> Settings -> Watermark) so that watermarking is enabled! Secondly, make sure you have entered your PDF file names correctly in the second field if you've entered any at all. Lastly, WaterWoo may not be able to watermark PDF files version 1.5 and newer. Consider the Premium version of the plugin, which includes the PDF parser necessary for newer files (1.5+).

= How do I test my watermark? =
I recommend creating a coupon in your Woocommerce shop to allow 100% free purchases. Don't share this coupon code with anyone! Test your watermark by purchasing PDFs from your shop using the coupon. It's a bit more tedious. If you want an easier go of it (on-the-fly testing), purchase the Premium version of this plugin.

= Why does the watermark go off the page, create new pages? =
Your watermark text string is too big or long for the page! Try decreasing font size, adjusting rotation, or using the X and Y fine tuners to move the watermark back onto the page. The built-in adjustments on the settings page ultimately allow for watermarking on all document sizes. You may need to edit your watermark if it is too verbose.

= Where do the watermarked files go? =
They are generated with a unique name and stored in the same folder as your original Wordpress/Woo product media upload (usually wp-content/uploads/year/month/file). The unique name includes the order number and a time stamp. If your end user complains of not being able to access their custom PDF for some reason (most often after their max number of downloads is exceeded), you can find it in that folder, right alongside your original.

= Will WaterWoo PDF watermark images? =
WaterWoo PDF is intended to watermark PDF (.pdf) files. If you are specifically looking to watermark image files (.jpg, .jpeg, .gif, .png, .etc), you may want to look into a plugin such as [Image Watermark](http://wordpress.org/plugins/image-watermark/ "Image Watermark Plugin"). WaterWoo PDF will create an watermark on a PDF page but the watermark will NOT sit over image files embedded in the PDF. 

= I get an FPDF error =
If you get the "FPDF error: This document (../../yourfile.pdf) probably uses a compression technique which is not supported by the free parser shipped with FPDI" it is because the PDF you are trying to watermark uses a compression technique not supported by the bundled PDF generator, FPDI. FPDI parses PDFs through version 1.4, and occasionally has troubles with 1.5, 1.6 and 1.7. 

1. Try this [solution using Acrobat](http://stackoverflow.com/a/7155711 "Stack Overflow"), if possible. Alternatively, you can go to Edit->Preflight->Standards and Save As PDF/A.
2. If that doesn't work, test and perhaps purchase the [add-on from SetaSign](http://www.setasign.com/products/fpdi/demos/fpdi-pdf-parser/ "PDF Parser Add-On") and add it into this plugin. That will take some programming chops. Or consider buying the Premium version of this plugin, as it will solve this problem.

= Does this work for ePub/Mobi files =
No. At this time I am unaware of a Woocommerce watermarking plugin for these file types.


== Screenshots ==

1. Screenshot of the settings page, as a Woocommerce settings tab.

== Changelog ==

= 1.0 =
* Initial release

= 1.0.2 =
* Support for landscape orientation

= 1.0.3 = 
* Fixed 4 PHP warnings

= 1.0.4 = 
* Support for odd-sized PDFs

= 1.0.5 = 
* Clean up code in waterwoo-pdf.php class_wwpdf_system_check.php and class_wwpdf_download_product.php
* UTF font encoding
* Support for redirect downloads (as long as file is in wp-content folder)
* Better watermark centering on page

= 1.0.6 =
* Readme updates
* Implemented woo-includes to determine if Woo is active
* Fixed link to settings from plugin page
* Tidy "inc/class_wwpdf_watermark.php"

= 1.0.7 =
* Missing folder replaced

= 1.0.8 =
* Fix default option variable names

= 1.0.9 =
* WC 2.3 ready
* added phone number shortcode
* tidied folder structure

= 1.0.10 = 
* WC 2.3.4 update
* added order paid date shortcode: [DATE]

== Upgrade Notice ==

= 1.0 =
* Initial release

= 1.0.1 =
* Minor changes

= 1.0.2 =
* Support for landscape orientation

= 1.0.3 =
* Fixed 4 PHP warnings

= 1.0.4 = 
* Support for odd-sized PDFs
* Added warning to free users that there may be a delay preparing the free version to work with the upcoming Woocommerce 2.3 release.

= 1.0.5 = 
* UTF font encoding
* Support for redirect downloads (as long as file is in wp-content folder)
* Better watermark centering on page

= 1.0.9 = 
* WC 2.3 ready
* added phone number shortcode

= 1.0.10 = 
* added order paid date shortcode: [DATE]

= 1.0.11 = 
* fix to woo-includes / Woo Dependencies