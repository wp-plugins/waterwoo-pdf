=== WaterWoo PDF Plugin ===
Contributors: Caroline Paquette
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=PB2CFX8H4V49L
Tags: woocommerce, ecommerce, pdf, file, ebook, watermark, watermarking, copyright, protection, security, plugin
Requires at least: 3.7
Tested up to: 4.1
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Protect your intellectual property! WaterWoo PDF allows WooCommerce site administrators to apply custom watermarks to PDFs upon sale.

== Description ==
WaterWoo PDF is a plugin that adds a watermark to every page of your PDF file(s). The watermark is customizable with font face, font color, font size, placement, and text. Not only that, but since the watermark is added when the download button is clicked (either on the customer's order confirmation page or email), the watermark can include customer-specific data such as the customer's first name, last name, and email. Your watermark is highly customizable and manipulatable.

**Features:**

* Watermark only designated PDF downloads (as specified by you), or *all* PDF downloads from your site
* Files do not need to be in a specific directory or even the same domain as your Woocommerce installation
* Watermark can be moved on the page, allowing for different paper sizes (such as letter, A4, legal, etc)
* Watermark is applied to **all** pages of **every** PDF purchased
* Watermarks upon click of either the customer's order confirmation page link or email order confirmation link
* Dynamic customer data inputs (customer first name, last name, and email)
* Choice of font face, color, size and placement (horizontal line of text anywhere on the page).

**Premium version (coming soon):**

[WaterWoo Premium](http://cap.little-package.com/shop/waterwoo-pdf-premium "WaterWoo PDF Premium Version") offers these helpful extra features for $45:

* Rotatable transparent page overlay watermark, apart from footer watermark (two watermark locations!)
* Additional font faces and text formatting options, such as font color and style (bold, italics)
* Additional dynamic customer data input (telephone number)
* Begin watermark on selected page of PDF document (to avoid watermarking a cover page, for example)
* Password protect and/or encrypt PDF files
* Test watermark and/or manually watermark a file on the fly

**Planned Features**

* Begin watermark on selected page of PDF document (to avoid watermarking a cover page, for example)
* Additional dynamic customer data input (customer address)
* Watermark with images (transparency)
* (Got more ideas? Tell me!)

If you have suggestions for a new add-on, please get in touch.

**Translations in your language:**

Get in touch to get a premium license in exchange for your translation. Needed:

* French
* Spanish
* German

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
First make sure you have checked the box at the top of your settings page (Woocommerce -> Settings -> Watermark) so that watermarking is enabled! Secondly, make sure you have entered your PDF file names correctly in the second field if you've entered any at all. Lastly, WaterWoo may not be able to watermark PDF files version 1.5 and newer. Consider the Premium version of the plugin. 

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
* Initial release.

== Upgrade Notice ==
= 1.0 =
* Initial release.