<!-- やること
 デザインこだわる
 駅名や住所などをリアルにする
 データベースの表示順とかちゃんと考える -->

<?php
// DB接続
$host = "127.0.0.1";
$dbname = "salon";
$username = "root";
$password = "";
$port = "3306";

$pdo = new PDO("mysql:host=$host;dbname=$dbname;port=$port;charset=utf8", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo = new PDO(
  "mysql:host=localhost;dbname=salon;charset=utf8mb4",
  "root",
  ""
);


// URLからサロンIDを受け取る
// 値があればそれを使ってなくてもそのまま受け取ってくれる
$shopId = $_GET['salonid'] ?? '';
//$shopId = $_GET['salonid'] "";これだとsalonIdがなかったことになる
//$shopId = $_GET['salonid'];これだと値が入ってない時に受け取ってもらえない
// 下の省略形
// if (isset($_GET['salonid'])) {
//     $shopId = $_GET['salonid'];
// } else {
//     $shopId = '';
// }

if ($shopId === "") {
  exit('サロンが指定されていません');
}

// サロン情報
$sqlSalon = "SELECT * FROM shop WHERE id = :id";
$stmtSalon = $pdo->prepare($sqlSalon);
$stmtSalon->execute([':id' => $shopId]);
$shop = $stmtSalon->fetch(PDO::FETCH_ASSOC);

if (!$shop) {
  exit('サロンが見つかりません');
}

$sqlmenu = "SELECT * FROM menu WHERE salonid = :id";
$stmtmenu = $pdo->prepare($sqlmenu);
$stmtmenu->execute([':id' => $shopId]);
$menus = $stmtmenu->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">

  <!-- ページタイトル：サロン名を動的に表示 -->
  <title><?= htmlspecialchars($shop['サロン名']) ?>のメニュー一覧</title>

  <!-- 共通CSS -->
  <link rel="stylesheet" href="./css/common.css">
  <!-- メニュー一覧ページ専用CSS -->
  <link rel="stylesheet" href="./css/menu.css">
</head>

<body>

  <!--  ヘッダー -->
  <header id="header">
    <div class="logo">
      milky <span class="logo-small">nailsalonsite</span>
    </div>
  </header>

  <div class="search-bar">
    <a href="index.php">１.エリア検索</a>
    ▶
    <a href="search.php">２.サロン選択</a>
    ▶
    <a class="bar-select" href="menu.php">３.メニュー選択</a>
    ▶
    <a href="#">４.予約情報入力</a>
    ▶
    <a href="#">５.予約完了</a>
  </div>

  <main id="main">

    <!-- サロン基本情報表示 -->
    <div class="fade-text">

      <!-- サロン名 -->
      <h1>
        <span class="salon-name">
          <?= htmlspecialchars($shop['サロン名']) ?>
        </span>
      </h1>

      <!-- 住所・最寄り駅 -->
      <p>
        <?= htmlspecialchars($shop['都道府県']) ?>
        <?= htmlspecialchars($shop['市区町村']) ?>
        <?= htmlspecialchars($shop['住所']) ?> /
        <?= htmlspecialchars($shop['最寄り駅']) ?>駅
      </p>
    </div>

    <!--   メニュー一覧カード-->
    <div class="salon-card">

      <!-- メニューが存在する場合のみ表示 -->
      <?php if (!empty($menus)): ?>

        <div class="menu-list">

          <!-- メニューを1件ずつ表示 -->
          <?php foreach ($menus as $menu): ?>

            <!-- メニューカード全体をリンクにする -->
            <a class="menu-item" href="reserve.php?menu=<?= $menu['id'] ?>">

              <!-- メニュー画像 -->
              <?php if (!empty($menu['画像'])): ?>
                <div class="menu-thumb">
                  <img src="<?= htmlspecialchars($menu['画像'], ENT_QUOTES, 'UTF-8') ?>" alt="">
                </div>
              <?php endif; ?>

              <!-- メニュー情報 -->
              <div class="menu-card">

                <!-- メニュー名＋カテゴリ -->
                <div class="menu-kategori">
                  <span class="menu-title">
                    <?= htmlspecialchars($menu['menu']) ?>
                  </span>
                  <small class="menu-smalltitle"><?= htmlspecialchars($menu['カテゴリ']) ?></small>
                </div>


                <!-- 詳細説明 -->
                <small><?= htmlspecialchars($menu['メニュー説明詳細']) ?></small>
                <!-- 時間・価格 -->
                <div class="menu-time">
                  <?= htmlspecialchars($menu['時間']) ?>分
                  ￥<?= htmlspecialchars($menu['価格']) ?>
                </div>

                <!-- ハッシュタグ -->
                <div>
                  <small>#<?= htmlspecialchars($menu['ハッシュタグ1']) ?></small>
                  <small>#<?= htmlspecialchars($menu['ハッシュタグ2']) ?></small>
                </div>

              </div>
            </a><!-- /.menu-item -->

          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>


 

    <!-- 検索画面に戻る -->
    <button class="btn" onclick="location.href='./search.php'">
      検索画面に戻る
    </button>

  </main>

  <footer id="footer">
    milky salon site
  </footer>

</body>

</html>