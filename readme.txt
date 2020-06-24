=== StaticPress2019-S3 ===
Contributors: yshinoda, wokamoto, mogmet
Donate link: https://www.amazon.co.jp/hz/wishlist/ls/7XDWZD7KHD56?ref_=wl_share
Tags: static aws s3
Requires at least: 4.3
Tested up to: 5.4
Requires PHP: 5.6
Stable tag: 0.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Uploads dumped static site by StaticPress into S3.

== Description ==

[StaticPress2019-S3](https://wordpress.org/plugins/staticpress2019-s3/) uploads dumped static site by StaticPress into S3.

This plugin is a revival of [StaticPress-S3](https://github.com/megumiteam/staticpress-s3) by CI / CD pipeline and TDD, and maintained by volunteers instead of the original no longer maintained.

== Installation ==

1. Go to Admin page on your WordPress.
2. Click [Plugins] -> [Add New].
3. Search by keyword: "staticpress2019".
4. Click [Install Now] button for "StaticPress2019" and "StaticPress2019-S3".
5. Click [Activate] button for "StaticPress2019" and "StaticPress2019-S3".

== How to use ==

1. Click [StaticPress2019] -> [StaticPress2019 Options]
2. Set [Static URL] as URL to publish in S3
3. Set [Save DIR (Document root)] as appropriate directory to dump static files
4. Click [Save Changes]
5. Set [AWS Access Key], [AWS Secret Key], [AWS Region] in [StaticPress S3 Option]
6. Click [Save Changes]
7. Choose [S3 Bucket]
8. Click [Save Changes]
9. Click [StaticPress2019] -> [StaticPress2019]
10. Click [Rebuild]

== Frequently Asked Questions ==

= Why mime type of file in S3 is not correct? =

This plugin uses [magic file](https://unix.stackexchange.com/questions/393288/explain-please-what-is-a-magic-file-in-unix) to detect mime type.
If mime type is not correct, you can specify different magic file from default by using environment variable "MAGIC".

== Changelog ==

**0.1.1 - October 11, 2013**  
Some fix.

== Upgrade Notice ==

= 0.1.1 =
This version fixes some bug.
