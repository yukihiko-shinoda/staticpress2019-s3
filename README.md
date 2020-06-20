# staticpress-s3
staticpressで作成した静的サイトをS3に反映する

## 使い方
1. git clone https://github.com/yuiwasaki/staticpress-s3
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

