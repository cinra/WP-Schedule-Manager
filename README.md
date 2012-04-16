# WP-Schedule-Manager

* version 0.1

Wordpressで記事毎にスケジュールを管理するプラグイン。

---

## Usage

インスタンス$wpsmをglobalにして使用します。以下のメソッドが利用可能。

### Methods

#### get

	$wpsm->get({array})

* post_id - 該当記事（デフォルト：false）
* date - 開始日。'2012-04-15'等の記述や、'yesterday'、'1 month ago'等の文章指定も使用できます。（デフォルト：today）
* term_by - 期間の単位。'year''month''week''day'が使えます。（デフォルト：day）
* term - 期間の長さ。'term'に合わせて、期間を指定できます。（デフォルト：1）
* order_by - ソート対象（デフォルト：date）
* order - ソート順。'ASC'|'DESC'（デフォルト：ASC）

例：

	$wpsm->get(array(
		'post_id'	=> 1 //記事ID
		'date'		=> '1 year ago', //一ヶ月前から
		'term'		=> 1 //一日
	));

