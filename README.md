# 🗽StaticPress2019-S3🗿

[![Build Status](https://travis-ci.com/yukihiko-shinoda/staticpress2019-s3.svg?branch=master)](https://travis-ci.com/yukihiko-shinoda/staticpress2019-s3)
[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/staticpress2019-s3)](https://wordpress.org/plugins/staticpress2019-s3/)
[![WordPress Plugin: Tested WP Version](https://img.shields.io/wordpress/plugin/tested/staticpress2019-s3)](https://travis-ci.com/yukihiko-shinoda/staticpress2019-s3)
[![PHP from Travis config](https://img.shields.io/travis/php-v/yukihiko-shinoda/staticpress2019-s3/master)](https://travis-ci.com/yukihiko-shinoda/staticpress2019-s3)
[![WordPress Plugin: Required WP Version](https://img.shields.io/wordpress/plugin/wp-version/staticpress2019-s3)](https://travis-ci.com/yukihiko-shinoda/staticpress2019-s3)
[![WordPress Plugin Active Installs](https://img.shields.io/wordpress/plugin/installs/staticpress2019-s3)](https://wordpress.org/plugins/staticpress2019-s3/advanced/)
[![WordPress Plugin Downloads](https://img.shields.io/wordpress/plugin/dm/staticpress2019-s3)](https://wordpress.org/plugins/staticpress2019-s3/advanced/)

Uploads dumped static site by StaticPress into S3.

## Description

[StaticPress2019-S3](https://wordpress.org/plugins/staticpress2019-s3/) transforms your WordPress into static websites and blogs.

This plugin is a revival of [StaticPress-S3](https://github.com/megumiteam/staticpress-s3) by CI / CD pipeline and TDD, and maintained by volunteers instead of the original no longer maintained.

## Installation

1. Sign into your WordPress admin dashboard.
2. Click [Plugins] -> [Add New].
3. Search by keyword: `staticpress2019`.
4. Click [Install Now] button for `StaticPress2019` and `StaticPress2019-S3`.
5. Click [Activate] button for `StaticPress2019` and `StaticPress2019-S3`.

## AWS settings

### IAM User

Create IAM User for deploy and issue its access key.
Then, attach following IAM policy:

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "s3:ListAllMyBuckets"
            ],
            "Resource": "*"
        },
        {
            "Effect": "Allow",
            "Action": [
                "s3:ListBucket"
            ],
            "Resource": "arn:aws:s3:::<your-bucket-name>"
        },
        {
            "Effect": "Allow",
            "Action": [
                "s3:PutObject"
            ],
            "Resource": "arn:aws:s3:::<your-bucket-name>/*"
        }
    ]
}
```

### S3 bucket policy

Create S3 bucket for hosting, set bucket object ownership control as `BucketOwnerEnforced`, and set bucket policy as following:

```json
{
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Principal": {
                "AWS": "arn:aws:iam::<your-aws-account-id>:user/<your-iam-user-name>"
            },
            "Action": "s3:ListBucket",
            "Resource": "arn:aws:s3:::<your-bucket-name>"
        },
        {
            "Effect": "Allow",
            "Principal": {
                "AWS": "arn:aws:iam::<your-aws-account-id>:user/<your-iam-user-name>"
            },
            "Action": "s3:PutObject",
            "Resource": "arn:aws:s3:::<your-bucket-name>/*"
        }
    ]
}
```

Note: Blocking all public access doesn't reject upload.

## How to use

1. Sign into your WordPress admin dashboard.
2. Click [StaticPress2019] -> [StaticPress2019 Options].
3. Set [Static URL] as URL to publish in S3.
4. Set [Save DIR (Document root)] as appropriate directory to dump static files.
5. Click [Save Changes].
6. Set [AWS Access Key], [AWS Secret Key], [AWS Region] in [StaticPress S3 Option].
7. Click [Save Changes].
8. Choose [S3 Bucket].
9. Click [Save Changes].
10. Click [StaticPress2019] -> [StaticPress2019].
11. Click [Rebuild].

## Frequently Asked Questions

<!-- markdownlint-disable no-trailing-punctuation -->
### Why mime type of file in S3 is not correct?
<!-- markdownlint-enable no-trailing-punctuation -->

This plugin uses [magic file](https://unix.stackexchange.com/questions/393288/explain-please-what-is-a-magic-file-in-unix) to detect mime type.
If mime type is not correct, you can specify different magic file from default by using environment variable `MAGIC`.

### What is [Put Object ACL] in StaticPress2019 Options form for?

It's just for backward compatibility. New user shouldn't use it:

[Disabling ACLs for all new buckets and enforcing Object Ownership - Amazon Simple Storage Service](https://docs.aws.amazon.com/AmazonS3/latest/userguide/ensure-object-ownership.html)

Updated user may need to use it. According to the old specifications, StaticPress2019-S3 put object ACL when upload static file to S3.
