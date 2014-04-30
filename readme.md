# elf02 WP Responsive Images - WordPress Plugin

Responsive image solution using picturefill.js v2 by [Scott Jehl][1]. Original idea by [Tim Evko][2].

## New version 1.2.1
* Add enhanced code for Retina Images by [Martin Wolf][5]
* New Options Page for easy control over five breakpoints.

### Bugfix in version 1.2.1
**Smallest breakpoint must be set. This is also the initial image and must be called "small-img".**

[Scott Jehl][1] describes the problem as follows:

> Temporary extra HTTP Requests for Picture usage in some browsers: In browsers that natively support srcset but do not yet support the picture element, users may experience a wasted HTTP request for each picture element on a page. This is because the browser's preparser will fetch one of the URLs listed in the picture element's child img's srcset attribute as soon as possible during page load, before the JavaScript can evaluate the potential picture source elements for a better match. This problem will only affect browsers that have implemented srcset but not picture, which will hopefully be short-lived.

## How It Works
The plugin extends each posted image with a data-responsive attribute. A "the_content" filter replaced such an image with the picturefill.js markup. After deactivating the plugin or removing the data-responsive attribute, all images should appear as normal.

## Additional
To regenerate older images after setting your breakpoints, you can use the plugin [Regenerate Thumbnails][4]. Be careful and make first a backup of your images. For me, it works fine.

Plugin Page: [elf02 WP Responsive Images - WordPress Plugin][3]

  [1]: http://scottjehl.github.io/picturefill/
  [2]: https://github.com/tevko/wp-tevko-responsive-images
  [3]: http://elf02.de/elf02-wp-responsive-images-wordpress-plugin/
  [4]: http://wordpress.org/plugins/regenerate-thumbnails/
  [5]: http://visuellegedanken.de/
