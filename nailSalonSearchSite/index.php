<!-- 
① データベースからお店情報を全部取ってくる
shop テーブルから
都道府県・市区町村・最寄り駅 を取得する
取得した結果を$shops（複数件の配列） として受け取る

② JavaScript に渡すための箱 $areaData を空で用意する
まだ何も入っていない空の配列を作る

③ お店データを1件ずつ順番に処理する（foreach）
$shops に入っているお店を1件ずつ $shop として取り出す

④ 1件分のお店データから必要な情報を取り出す
都道府県 $pref
市区町村 $city（なければ空文字）
最寄り駅 $station（なければ空文字）
それぞれの前後の空白を trim() で削除する

⑤ その都道府県がまだ $areaData に無ければ、入れ物を作る
まだ登場していない都道府県だった場合
その都道府県用に市区町村を入れる cities の空配列駅を入れる stations の空配列
を用意する

⑥ 市区町村を条件付きで追加する
市区町村が空じゃない
すでにその都道府県の cities に入っていない
この2つを満たした場合だけcities 配列に追加する

⑦ 最寄り駅も同じルールで追加する
駅名が空じゃない
すでに stations に入っていない
条件を満たした場合だけ
stations 配列に追加する

⑧ すべてのお店データを処理し終えるまで繰り返す
結果として都道府県ごとに、市区町村と駅が整理された配列 が完成する -->

<?php
//------DB接続 & データ取得 -----------------
$host = "127.0.0.1";
$dbname = "salon";
$username = "root";
$password = "";
$port = "3306";

$pdo = new PDO("mysql:host=$host;dbname=$dbname;port=$port;charset=utf8", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$sqlReco = "
  SELECT 
    m.id,
    m.menu,
    m.カテゴリ,
    m.メニュー説明詳細,
    m.価格,
    m.時間,
    m.画像,
    m.ハッシュタグ1,
    m.ハッシュタグ2,
    s.サロン名,
    s.住所,
    s.最寄り駅
  FROM menu m
  JOIN shop s ON m.salonid = s.id
  ORDER BY m.id DESC
  LIMIT 2
";
$stmtReco = $pdo->query($sqlReco);
$recoMenus = $stmtReco->fetchAll(PDO::FETCH_ASSOC);



$sql = "SELECT 都道府県, 市区町村, 最寄り駅 FROM shop";
$stmt = $pdo->query($sql);
$shops = $stmt->fetchAll(PDO::FETCH_ASSOC);

//javascriptに送る中身は空
$areaData = [];


foreach ($shops as $shop) {
  //配列の中身を1個ずつ順番に取り出して処理するための命令
  //   $shops の中身を 1件ずつ取り出して、
  // その1件分を $shop で使う
  $pref    = trim($shop['都道府県']);
  $city    = trim($shop['市区町村'] ?? '');
  $station = trim($shop['最寄り駅'] ?? '');
  // trim→文字列の前後にくっついてる余計な空白とか改行をけす


  //「この都道府県、まだ登録されてないから
  // あとで市区町村とか駅を入れられる空の入れ物を先に作っとこ」

  if (!isset($areaData[$pref])) {
    // 都道府県がまだなければ

    $areaData[$pref] = [
      'cities'   => [], //都道府県に属している市区町村のリスト
      //'cities'：フォルダ名「市区町村」$city：その中のファイル名「本巣市」
      'stations' => [],
    ];
  }

  if ($city !== '' && !in_array($city, $areaData[$pref]['cities'], true)) {
    //  $city が空じゃない＆ まだ city の配列に同じ市区町村が入っていない
    //  true は 厳密比較 型も含めてチェックする 見つかった → true  見つからない → false
    $areaData[$pref]['cities'][] = $city;
    // $areaData['愛知県']['cities'] = ['名古屋市'];
  }

  if ($station !== '' && !in_array($station, $areaData[$pref]['stations'], true)) {
    $areaData[$pref]['stations'][] = $station;
  }
}
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ネイル</title>
  <link rel="stylesheet" href="./css/common.css">

  <link rel="stylesheet" href="./css/search.css">
  <link rel="stylesheet" href="./css/index.css">
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

</head>

<body>

  <section class="hero">
    <!-- 背景グリッド（CSSで出す） -->

    <div class="hero-container">
      <!-- 左：ブランド & 検索 -->
      <div class="hero-left">
        <div class="logo milk">
          milky <br>
          <span class="logo-small">nailsalon site</span>
        </div>

        <div class="hero-line">あなたにぴったりの<br>
          <div class="hero-line2">ネイルサロンが きっと見つかる。</div>
        </div>
        <a href="./index.php#hero-card" class="btn btn-a">エリア検索</a>
        <a href="./shopcreate.php" class="btn btn-b " style="background-color: #FEE8EB;">サロン登録</a>




      </div>
      <div class="hero-right">
        <div class="slider">
          <div class="slides">
            <img src="images/hero.jpg" class="slide active" alt="ネイルデザイン">
            <img src="images/salon4-dezain.jpg" class="slide" alt="ネイルデザイン2">
            <img src="images/salon3-teigaku.jpg" class="slide" alt="ネイルデザイン3">
          </div>


        </div>
      </div>
    </div>


    <!-- 右：写真スライダー -->

    </div>


    <div id="hero-card" class="hero-card">
      <form method="get" action="search.php">
        <div class="search-block">
          <div class="search-head">
            <h3>エリアから探す</h3>
          </div>

          <!-- 都道府県ボタン -->
          <div class="pref-div">
            <div class="pref pref1 active">
              <input type="radio" id="pref-aichi" name="都道府県" value="愛知県" hidden checked>
              <label for="pref-aichi" class="pref-text">愛知県</label>
            </div>

            <div class="pref pref2">
              <input type="radio" id="pref-gifu" name="都道府県" value="岐阜県" hidden>
              <label for="pref-gifu" class="pref-text">岐阜県</label>
            </div>

            <div class="pref pref3">
              <input type="radio" id="pref-mie" name="都道府県" value="三重県" hidden>
              <label for="pref-mie" class="pref-text">三重県</label>
            </div>
          </div>


          <!-- タブ＋タグ -->
          <div class="tab-wrapper">
            <div class="tab-buttons">
              <button type="button" class="tab-btn active" data-target="tab-city">
                市区町村で探す
              </button>
              <button type="button" class="tab-btn" data-target="tab-station">
                駅名で探す
              </button>
            </div>

            <div class="tab-contents">
              <div id="tab-city" class="tab-content active">
                <div class="tag-list" id="city-list"></div>
                <input type="hidden" name="市区町村" id="city-input">
              </div>

              <div id="tab-station" class="tab-content">
                <div class="tag-list" id="station-list"></div>
                <input type="hidden" name="最寄り駅" id="station-input">
              </div>
            </div>
          </div>

          <button type="submit" class="btn">この条件で検索</button>
        </div>
      </form>
    </div>

  </section>



  <script>
    window.areaData = <?= json_encode($areaData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
  </script>


  <script src="./js/index.js?v=1"></script>
  <footer id="footer">milky salon site</footer>
</body>


</html>
