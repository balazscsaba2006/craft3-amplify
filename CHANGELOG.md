# Changelog

## 1.0.0 - 2018-06-11
### Added
- Initial release

## 1.0.1 - 2018-06-13
### Readme
- Updated readme

## 1.0.2 - 2018-07-06
### Added
- Added minimum PHP version 7.1

## 1.0.3 - 2018-07-06
### Fixed
- Downgraded to PHP 7.0

## 1.0.5 - 2019-03-26
### Fixed
- Ensure version number matches Git tags
- Refactored Twig extension logic to work on older versions of PHP 7

## 1.0.6 - 2019-04-18
### Fixed
- Remove return type on Twig Extension because it caused issues in some PHP environments

## 1.0.7 - 2019-05-02
### Fixed
- Fixed Iframe https

## 1.0.8 - 2019-05-03
### Fixed
- Filter unsuported tags [align, hspace, vspace, allowfullscreen, allowtransparency]

## 1.0.9 - 2019-12-06
### Fixed
- Fixed blank image tags. Add check for blank src=""

## 1.0.10 - 2020-05-27
### Updated
- Updated lib/simple_html_dom.php with regex pattern that works with PCRE2 strict mode (php7.3)

## 1.0.11 - 2022-03-07
### Fixed
- Call to a member function find() on bool on \twig\TwigExtensions::amplifyImages

## 2.0.0 - 2022-09-09
### Added
- Craft 4 support

## 2.1.0 - 2026-09-07
### Added
- Craft 5 support. `craftcms/cms` widened to `^4.0.0|^5.0.0`; the plugin uses only
  `craft\base\Plugin` and a Twig `AbstractExtension`, neither of which changed in Craft 5.
### Changed
- Minimum PHP raised to 8.2, matching Craft 5.
### Fixed
- `lib/simple_html_dom.php` passed null to `strtolower()` for selectors with no tag or no
  attribute, which PHP 8.1+ deprecates. Upstream is version 1.5 from 2012 and unmaintained,
  so this is fixed in the vendored copy.

## 2.1.1 - 2026-09-08
### Fixed
- Reverted the PHP floor to `^8.0.2|^9.0`. 2.1.0 raised it to `^8.2` to match Craft 5, but the
  plugin's own code does not need 8.2 and `craftcms/cms ^5.0` already enforces it. The higher
  floor made the plugin uninstallable on a Craft 4 project pinned below 8.2.

## 2.1.2 - 2026-09-10
### Fixed
- The plugin built the Twig environment while it was still loading. `init()` read
  `view->twig` to add the Twig extension, and plugins load during application bootstrap, so
  Twig was created before Craft finished initialising. Craft logs
  "Twig instantiated before Craft is fully initialized" every time that happens, on every web
  request and every console command; on one site that was about 3,400 warnings a day, which
  buried real errors. Switched to `view->registerTwigExtension()`, which stores the extension
  and hands it to Twig when Twig is actually created. Same behaviour, nothing built early, and
  no change to the `craftcms/cms` constraint since the method exists in Craft 3, 4 and 5.

## 2.1.3 - 2026-09-10
### Fixed
- Iframes with a non-numeric width or height produced invalid AMP. `amp-iframe` requires
  numeric dimensions, and because the filter sets `layout="responsive"` those numbers are an
  aspect ratio rather than a size, so `width="90%"` carries nothing usable and AMP rejects the
  document. The old check only added dimensions when they were **missing**, so a percentage was
  found and left in place. Any non-numeric value is now replaced with the default, and each
  `amp-iframe` on the page is handled separately instead of the first one's state deciding for
  all of them. Reported in #9.
- An image whose size could not be determined broke the page. FasterImage reports failure by
  setting `size` to the string `'failed'` rather than returning null, and that string reached the
  caller: older versions read `[0]` and `[1]` off it and emitted `width="f" height="a"`, and
  since the return type was declared `?array` it throws a `TypeError` instead, so a single
  unreadable image takes down the whole AMP page. It now returns null, which the caller already
  handles by dropping the image from the document. Also reported in #9.

## 2.1.4 - 2026-09-10
### Fixed
- 2.1.3 replaced one crash with another. Making `readImageSize()` return null for an unreadable
  image finally reached the line that caches that fact, `cacheImageSize($src, null, null)`, and
  that method declared `int $width, int $height`, so the page died on a `TypeError` instead of a
  `?array` one. The line had been there for years and had never run, because the old code
  returned the truthy string `'failed'` and never took the failure branch. The parameters are now
  nullable, which is what `[null, null]` in the cache has always meant and what the cache-hit
  branch already tests for. Measured on a production site: 127 of 173 AMP pages answered 500
  under 2.1.3 and answer 200 with this change.

