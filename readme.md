# elf02 WP Responsive Images - WordPress Plugin

Responsive image solution using picturefill.js v2 by [Scott Jehl][1]. Original idea by [Tim Evko][2].

## New version 1.2.1
* Add enhanced code for Retina Images by [Martin Wolf][5]
* New Options Page for easy control over five Breakpoints.

## How It Works
The plugin extends each posted image with a data-responsive attribute. A "the_content" filter replaced such an image with the picturefill.js markup. After deactivating the plugin or removing the data-responsive attribute, all images should appear as normal.

## Additional
To regenerate older images after setting your Breakpoints, you can use the Plugin [Regenerate Thumbnails][4]. Be careful: Not yet tested by me!

Plugin Page: [elf02 WP Responsive Images - WordPress Plugin][3]

  [1]: http://scottjehl.github.io/picturefill/
  [2]: https://github.com/tevko/wp-tevko-responsive-images
  [3]: http://elf02.de/elf02-wp-respo…rdpress-plugin/
  [4]: http://wordpress.org/plugins/regenerate-thumbnails/
  [5]: http://visuellegedanken.de/