# 🗽StaticPress2019-S3🗿

[![Build Status](https://travis-ci.org/yukihiko-shinoda/staticpress-s3.svg?branch=master)](https://travis-ci.org/yukihiko-shinoda/staticpress-s3)
[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/staticpress2019-s3)](https://wordpress.org/plugins/staticpress2019-s3/)
[![WordPress Plugin: Tested WP Version](https://img.shields.io/wordpress/plugin/tested/staticpress2019-s3)](https://travis-ci.org/yukihiko-shinoda/staticpress-s3)
[![PHP from Travis config](https://img.shields.io/travis/php-v/yukihiko-shinoda/staticpress-s3/master)](https://travis-ci.org/yukihiko-shinoda/staticpress-s3)
[![WordPress Plugin: Required WP Version](https://img.shields.io/wordpress/plugin/wp-version/staticpress2019-s3)](https://travis-ci.org/yukihiko-shinoda/staticpress-s3)
[![WordPress Plugin Active Installs](https://img.shields.io/wordpress/plugin/installs/staticpress2019-s3)](https://wordpress.org/plugins/staticpress2019-s3/advanced/)
[![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dm/staticpress2019-s3)](https://wordpress.org/plugins/staticpress2019-s3/advanced/)

Uploads dumped static site by StaticPress into S3.

## Description

[StaticPress2019-S3](https://wordpress.org/plugins/staticpress2019-s3/) transforms your WordPress into static websites and blogs.

This plugin is a revival of [StaticPress-S3](https://github.com/megumiteam/staticpress-s3) by CI / CD pipeline and TDD, and maintained by volunteers instead of the original no longer maintained.

## Installation

1. Go to Admin page on your WordPress.
2. Click [Plugins] -> [Add New].
3. Search by keyword: `staticpress2019`.
4. Click [Install Now] button for `StaticPress2019` and `StaticPress2019-S3`.
5. Click [Activate] button for `StaticPress2019` and `StaticPress2019-S3`.

## How to use

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

## Frequently Asked Questions

<!-- markdownlint-disable no-trailing-punctuation -->
### Why mime type of file in S3 is not correct?
<!-- markdownlint-enable no-trailing-punctuation -->

This plugin uses [magic file](https://unix.stackexchange.com/questions/393288/explain-please-what-is-a-magic-file-in-unix) to detect mime type.
If mime type is not correct, you can specify different magic file from default by using environment variable `MAGIC`.
