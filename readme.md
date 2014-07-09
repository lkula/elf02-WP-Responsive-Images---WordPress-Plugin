# elf02 WP Responsive Images - WordPress Plugin

Responsive image solution using picturefill.js v2 by [Scott Jehl][1]. Original idea by [Tim Evko][2].

## New in version 1.3.3
* Option to load picturefill.js asynchronously.

## New in version 1.3.2
* __Important regex bugfix.__
* Option to use native implementation instead of picturefill.js.

## New in version 1.3.1
* Optional full size image fallback.

## New in version 1.3.0
* Simplified srcset syntax.
* Automatically added 2x image sizes.
* Top Notch backend styling.

## New in version 1.2.2
* Better security approaches.
* Improved regex and a list off all in WordPress registered breakpoints
* Update to [Picturefill 2.0.0 Stable][6]

## New in version 1.2.1
* Add enhanced code for Retina Images by [Martin Wolf][5]
* New Options Page for easy control over five breakpoints.

## How It Works
The plugin extends each posted image with a data-responsive attribute. A "the_content" filter replaced such an image with the picturefill.js markup. After deactivating the plugin or removing the data-responsive attribute, all images should appear as normal.

## Additional
To regenerate older images after setting your breakpoints, you can use the plugin [Regenerate Thumbnails][4]. Be careful and make first a backup of your images. For me, it works fine.

Plugin Page: [elf02 WP Responsive Images - WordPress Plugin][3]

  [1]: http://scottjehl.github.io/picturefill/
  [2]: https://github.com/tevko/wp-tevko-responsive-images
  [3]: http://elf02.de/2014/04/22/elf02-wp-responsive-images-wordpress-plugin/
  [4]: http://wordpress.org/plugins/regenerate-thumbnails/
  [5]: http://visuellegedanken.de/
  [6]: https://github.com/scottjehl/picturefill/releases/tag/2.0.0
