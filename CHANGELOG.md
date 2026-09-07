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
