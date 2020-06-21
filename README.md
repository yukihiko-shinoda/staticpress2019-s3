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

## 使い方

1. git clone https://github.com/yukihiko-shinoda/staticpress-s3
2. staticpress-s3をzipにする
3. Wordpressのページにログイン
4. [プラグイン] -> [新規追加] -> [プラグインのアップロード]で上で作成したzipをアップロード
5. プラグインでStaticPressをインストール
6. プラグインを有効にする
7. [StaticPress] -> [StaticPress設定]を選択
8. 静的サイトにs3で公開するURLを設定
9. 出力先ディレクトリを適当なディレクトリに設定
10. [変更を保持]をクリック
11. StaticPress S3 OptionにAWSのアクセスキーとシークレットキーとリージョンを設定
12. [変更を保持]をクリック
13. S3のバケットを選択
14. [StaticPress] -> [StaticPress]を選択
15. [再構築]をクリック

## 問題
- Dockerで作成した場合など実際のポート番号とサーバー内のポート番号が違う場合にStaticPress側でクローリングが正しく動作しない

