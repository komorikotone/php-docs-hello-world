<?php
// ----------------DB接続 ----------------------
$host = "127.0.0.1";
$dbname = "salon";
$username = "root";
$password = "";
$port = "3306";

$pdo = new PDO("mysql:host=$host;dbname=$dbname;port=$port;charset=utf8", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


$menureserve = "";

// URLに menu〇〇が付いてたら、ifの中を実行
if (isset($_GET['menu'])) {
    $menureserve = (int)$_GET['menu'];
} else if (isset($_GET['menuid'])) {
    $menureserve = (int)$_GET['menuid'];
}


if ($menureserve <= 0) {
    exit('メニューIDが指定されていません');
}


//=====メニュー + サロン情報を取得=====// ・menuテーブル: id, salonid, menuid, メニュー説明, 価格, 時間, カテゴリ, ...
// ・salonテーブル: id, サロン名, 住所, 最寄り駅, ...
$sql = "SELECT 
            m.*, 
            s.サロン名, 
            s.住所, 
            s.最寄り駅
        FROM menu AS m
        JOIN shop AS s ON m.salonid = s.id
        WHERE m.id = :id";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':id', $menureserve, PDO::PARAM_INT);
$stmt->execute();

$menu = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$menu) {
    exit('メニュー情報が見つかりません (id=' . htmlspecialchars($menureserve, ENT_QUOTES, 'UTF-8') . ')');
}



?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($menu['サロン名'] ?? 'サロン', ENT_QUOTES, 'UTF-8') ?>｜予約フォーム</title>

    <link rel="stylesheet" href="./css/common.css">
    <link rel="stylesheet" href="./css/reserve.css">
</head>

<body onload="resetDo()">
    <header id="header">
        <div class="logo">milky <span class="logo-small">nailsalonsite</span></div>
    </header>

    <div class="search-bar">
        <a href="index.php">１.エリア検索</a>
        ▶
        <a href="search.php">２.サロン選択</a>
        ▶
        <a href="./menu.php?salonid=<?= urlencode($menu['salonid']) ?>">３.メニュー選択</a>
        ▶
        <a class="bar-select" href="reserve.php">４.予約情報入力</a>
        ▶
        <a href="#">５.予約完了</a>
    </div>

    <main id="main">
        <h1 class="custom">お客様情報入力</h1>

        <div class="fade-text fade-text-reserve">
            <!-- サロン＆メニュー情報 -->
            <section class="reserve-summary">
                <div class="reserve-box">
                    <h3>選択中のメニュー</h3>
                    <h2 class="salon-name"><?= htmlspecialchars($menu['サロン名'], ENT_QUOTES, 'UTF-8') ?></h2>
                    <p><?= htmlspecialchars($menu['住所'], ENT_QUOTES, 'UTF-8') ?> / <?= htmlspecialchars($menu['最寄り駅'], ENT_QUOTES, 'UTF-8') ?>駅</p>




                    <!-- 予約フォーム -->
                    <section class="reserve-form">
                        <form id="reserveForm" action="reserved.php" method="post" onsubmit="return resetDo()">
                            <div class="menu-item">

                                <!-- メニュー画像 -->
                                <?php if (!empty($menu['画像'])): ?>
                                    <div class="menu-thumb">
                                        <img src="<?= htmlspecialchars($menu['画像']) ?>" alt="">
                                    </div>
                                <?php endif; ?>

                                <!-- メニュー内容 -->
                                <div class="menu-card">

                                    <!-- メニュー名＋カテゴリ -->
                                    <div class="seach-menu-title">
                                        <?= htmlspecialchars($menu['menu']) ?>
                                        <small class="menu-smalltitle"><?= htmlspecialchars($menu['カテゴリ']) ?></small>
                                    </div>


                                    <!-- 詳細説明 -->
                                    <div class="menu-setumei">
                                        <small><?= htmlspecialchars($menu['メニュー説明詳細']) ?></small>
                                    </div>

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
                            </div><!-- /.menu-item -->
                    </section>

                    <input type="hidden" name="menuid" value="<?= htmlspecialchars($menu['id'], ENT_QUOTES, 'UTF-8') ?>">
                    <input type="hidden" name="salonid" value="<?= htmlspecialchars($menu['salonid'], ENT_QUOTES, 'UTF-8') ?>">
                    <div class="message" id="msg"></div>
                    <div>
                        <label>お名前&#127872;</label><br>
                        <input type="text" name="nameFull" style="width: 10rem; height:1.5rem" id="nameFull" class="hissu" oninput="resetDo()" placeholder="お名前入力">
                        <div class="errMsg" id="nameError"></div>
                    </div>

                    <div>
                        <label>電話番号 &#x1F4DE;</label><br>
                        <input type="text" name="telFull" style="width: 10rem; height:1.5rem" id="telFull" class="hissu" oninput="resetDo()" placeholder="ハイフンなし">
                        <div class="errMsg" id="telError"></div>
                    </div>

                    <div>
                        <label>希望日 &#129668;&#129498;&#8205;&#9792;&#65039;</label><br>
                        <input type="date" name="dateFull" style="height: 1.5rem;" id="dateFull" class="hissu"
                            value="<?= htmlspecialchars($selectedDate, ENT_QUOTES, 'UTF-8') ?>" oninput="resetDo()">
                        <div class="errMsg" id="dateError"></div>
                    </div>

                    <div>
                        希望時間&#128171;&#127775; <br>
                        <input type="time" name="timeFull" style="height: 1.5rem;" id="timeFull" class="hissu" oninput="resetDo()">
                        <div class="errMsg" id="timeError"></div>
                    </div>

                    <div>
                        <label>ご要望・相談など</label><br>
                        <textarea name="note" rows="4"></textarea>
                    </div>

                    <button type="submit" class="btn">この内容で予約する</button>
                </div>
                <div class="btn-all">
                    <button type="button" class="btn btn-a" onclick="resetBtn()">リセット</button>
                    <button type="button" class="btn btn-a" onclick="location.href='./menu.php?salonid=<?= urlencode($menu['salonid']) ?>'">メニューへ戻る</button>
                </div>
                </form>
            </section>
        </div>
    </main>
    <footer id="footer">milky salon site</footer>
    <script src="./javascript/reserve.js"></script>
</body>