<?php

// ----------------DB接続 ----------------------
$host = "127.0.0.1";
$dbname = "salon";
$username = "root";
$password = "";
$port = "3306";

$pdo = new PDO(
    "mysql:host=$host;dbname=$dbname;port=$port;charset=utf8",
    $username,
    $password
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ---------------- GET受け取り ----------------------
$prefecture = $_GET['都道府県'] ?? '';
$city       = $_GET['市区町村'] ?? '';
$station    = $_GET['最寄り駅'] ?? '';

// ---------------- areaData用：全サロンから取得 ----------------------
$areaData = [];

$sqlArea = "SELECT 都道府県, 市区町村, 最寄り駅 FROM shop";
$stmtArea = $pdo->query($sqlArea);
$allShops = $stmtArea->fetchAll(PDO::FETCH_ASSOC);


foreach ($allShops as $shop) {
    $pref     = trim($shop['都道府県']);
    $shopCity = trim($shop['市区町村'] ?? '');
    $st       = trim($shop['最寄り駅'] ?? '');

    if (!isset($areaData[$pref])) {
        $areaData[$pref] = [
            'cities' => [],
            'stationsByCity' => []
        ];
    }

    // cities
    if ($shopCity !== '' && !in_array($shopCity, $areaData[$pref]['cities'], true)) {
        $areaData[$pref]['cities'][] = $shopCity;
    }

    // stationsByCity
    if ($shopCity !== '' && $st !== '') {
        if (!isset($areaData[$pref]['stationsByCity'][$shopCity])) {
            $areaData[$pref]['stationsByCity'][$shopCity] = [];
        }
        if (!in_array($st, $areaData[$pref]['stationsByCity'][$shopCity], true)) {
            $areaData[$pref]['stationsByCity'][$shopCity][] = $st;
        }
    }
}


$cityItems = [];
if ($prefecture !== '' && isset($areaData[$prefecture]['cities'])) {
    $cityItems = $areaData[$prefecture]['cities'];
}

$stationItems = [];
if ($prefecture !== '' && $city !== '' && isset($areaData[$prefecture]['stationsByCity'][$city])) {
    $stationItems = $areaData[$prefecture]['stationsByCity'][$city];
}

// ---------------- サロン検索SQL（条件つき） ----------------------
$sql = "SELECT * FROM shop WHERE 1=1";
$params = [];

if ($prefecture !== '') {
    $sql .= " AND 都道府県 = :prefecture";
    $params[':prefecture'] = $prefecture;
}
if ($city !== '') {
    $sql .= " AND 市区町村 = :city";
    $params[':city'] = $city;
}
if ($station !== '') {
    $sql .= " AND 最寄り駅 = :station";
    $params[':station'] = $station;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$shops = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ---------------- おすすめメニューTOP２ ----------------------
$sqlmenu = "SELECT * FROM menu WHERE salonid = :id AND menuid <= 2";
$stmtmenu = $pdo->prepare($sqlmenu);

?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>検索結果</title>

    <!-- 共通CSS -->
    <link rel="stylesheet" href="./css/common.css">
    <!-- 検索結果ページ専用CSS -->
    <link rel="stylesheet" href="./css/search.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Dela+Gothic+One&family=Kaisei+Decol&family=Kapakana:wght@300..400&family=PT+Serif&family=Yusei+Magic&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
</head>

<body>

    <!-- ヘッダー-->
    <header id="header">
        <div class="header-logo">
            <div class="logo">
                milky <span class="logo-small">nailsalonsite</span>
            </div>
        </div>
    </header>

    <div class="search-bar">
        <a href="index.php">１.エリア検索</a>
        ▶
        <a class="bar-select" href="search.php">２.サロン選択</a>
        ▶
        <a href="#">３.メニュー選択</a>
        ▶
        <a href="#">４.予約情報入力</a>
        ▶
        <a href="#">５.予約完了</a>
    </div>

    <main id="main">

        <form method="get" id="searchForm" action="search.php">
            <div class="search-condition">


                <h3>検索条件</h3>

                <div class="condition-tags">

                    <!-- 都道府県 -->
                    <span class="condition-tag">
                        都道府県：<span id="prefLabel" onclick="prefChange()">
                            <?= $prefecture === '' ? '指定なし' : htmlspecialchars($prefecture) ?><small> ▼</small>
                        </span>

                        <select id="prefSelect" name="都道府県" style="display:none">
                            <option value="愛知県" <?= ($prefecture == "愛知県" ?  "selected" : "") ?>>愛知県</option>
                            <option value="岐阜県" <?= ($prefecture == "岐阜県" ?  "selected" : "") ?>>岐阜県</option>
                            <option value="三重県" <?= ($prefecture == "三重県" ?  "selected" : "") ?>>三重県</option>
                        </select>
                    </span>

                    <!-- 市区町村 -->
                    <span class="condition-tag">
                        市区町村：<span id="cityLabel" onclick="cityChange()">
                            <?= $city === '' ? '指定なし' : htmlspecialchars($city) ?><small> ▼</small>
                        </span>

                        <select id="citySelect" name="市区町村" style="display:none">
                            <option value="">市区町村を選択</option>
                            <?php foreach ($cityItems as $cityItem): ?>
                                <option value="<?= htmlspecialchars($cityItem) ?>" <?= ($city === $cityItem ? "selected" : "") ?>>
                                    <?= htmlspecialchars($cityItem) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </span>

                    <!-- ✅ 駅名 -->
                    <span class="condition-tag">
                        駅名：<span id="stationLabel" onclick="stationChange()">
                            <?= $station === '' ? '指定なし' : htmlspecialchars($station) ?> <small>▼</small>
                        </span>

                        <select id="stationSelect" name="最寄り駅" style="display:none">
                            <option value="">駅名を選択</option>
                            <?php foreach ($stationItems as $st): ?>
                                <option value="<?= htmlspecialchars($st) ?>" <?= ($station === $st ? "selected" : "") ?>>
                                    <?= htmlspecialchars($st) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </span> <br>


                </div>

            </div>
        </form>


        <!-- サロンが見つかった場合 -->
        <?php if (!empty($shops)): ?>

            <div class="salon-list">

                <?php foreach ($shops as $shop): ?>

                    <?php
                    $stmtmenu->execute([':id' => $shop['id']]);
                    $menus = $stmtmenu->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <a class="salon-card" href="menu.php?salonid=<?= urlencode($shop['id']) ?>">

                        <!-- サロン名 -->
                        <div class="salon-top">
                            <h2 class="salon-name">
                                <?= htmlspecialchars($shop['サロン名']) ?>
                            </h2>
                        </div>

                        <div class="salon-info">

                            <p class="salon-location">
                                <?= htmlspecialchars($shop['サロン紹介']) ?>
                            </p>

                            <div>おすすめメニュー</div>

                            <div class="salon-menus">
                                <?php if (!empty($menus)): ?>
                                    <div class="menu-list">

                                        <?php foreach ($menus as $menu): ?>
                                            <div class="menu-item">

                                                <?php if (!empty($menu['画像'])): ?>
                                                    <div class="menu-thumb">
                                                        <img src="<?= htmlspecialchars($menu['画像']) ?>" alt="">
                                                    </div>
                                                <?php endif; ?>

                                                <div class="menu-card">
                                                    <div class="seach-menu-title">
                                                        <?= htmlspecialchars($menu['menu']) ?>
                                                        <small class="menu-smalltitle"><?= htmlspecialchars($menu['カテゴリ']) ?></small>
                                                    </div>

                                                    <div class="menu-setumei">
                                                        <small><?= htmlspecialchars($menu['メニュー説明詳細']) ?></small>
                                                    </div>

                                                    <div class="menu-time">
                                                        <?= htmlspecialchars($menu['時間']) ?>分
                                                        ￥<?= htmlspecialchars($menu['価格']) ?>
                                                    </div>

                                                    <div>
                                                        <?php if (!empty($menu['ハッシュタグ1'])): ?>
                                                            <small>#<?= htmlspecialchars($menu['ハッシュタグ1']) ?></small>
                                                        <?php endif; ?>
                                                        <?php if (!empty($menu['ハッシュタグ2'])): ?>
                                                            <small>#<?= htmlspecialchars($menu['ハッシュタグ2']) ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                            </div>
                                        <?php endforeach; ?>

                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <p class="salon-distance">
                            <?= htmlspecialchars($shop['最寄り駅']) ?>駅から
                            <?= htmlspecialchars($shop['駅からの距離']) ?>
                        </p>

                    </a>

                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <p>条件に合うサロンが見つかりませんでした。</p>
        <?php endif; ?>


        <button onclick="location.href='./index.php'" class="btn">
            検索画面に戻る
        </button>


        <script>
            window.areaData = <?= json_encode($areaData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
            window.currentCity = <?= json_encode($city, JSON_UNESCAPED_UNICODE); ?>;
            window.currentStation = <?= json_encode($station, JSON_UNESCAPED_UNICODE); ?>;
        </script>

    </main>

    <script src="./js/search.js?v=2"></script>

    <footer id="footer">
        milky salon site
    </footer>


</body>

</html>