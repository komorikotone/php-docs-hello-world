<?php
// DB接続
$host = "127.0.0.1";
$dbname = "salon";
$username = "root";
$password = "";
$port = "3306";

$pdo = new PDO("mysql:host=$host;dbname=$dbname;port=$port;charset=utf8", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


// フォームから受け取る
$name    = $_POST['nameFull'] ?? '';
$tel     = $_POST['telFull']  ?? '';
$date    = $_POST['dateFull'] ?? '';
$time    = $_POST['timeFull'] ?? '';
$note    = $_POST['note']     ?? '';

$menuId  = $_POST['menuid']   ?? 0;
$salonId = $_POST['salonid']  ?? 0;



// 予約テーブルに保存
$sql = "INSERT INTO reservations 
          (salonid, menuid, 名前, 電話, 予約日, 予約時間, メモ)
        VALUES 
          (:salonid, :menuid, :nameFull, :tel, :date, :time, :note)";

$stmt = $pdo->prepare($sql);
$stmt->execute([

    ':salonid' => $salonId,
    ':menuid'  => $menuId,
    ':nameFull'     => $name,
    ':tel'      => $tel,
    ':date'     => $date,
    ':time'     => $time,
    ':note'     => $note,
]);
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>予約完了</title>
    <link rel="stylesheet" href="./css/common.css">
    <link rel="stylesheet" href="./css/reserved.css">
</head>

<body>
    <header id="header">
        <div class="logo">milky <span class="logo-small">nailsalonsite</span></div>
    </header>
    <div class="search-bar">
        <a href="index.php">１.エリア検索</a>
        ▶
        <a href="#">２.サロン選択</a>
        ▶
        <a href="#">３.メニュー選択</a>
        ▶
        <a href="#">４.予約情報入力</a>
        ▶
        <a class="bar-select" href="#">５.予約完了</a>
    </div>

    <main id="main">
        <div class="reserve-comp fade-text">
            <p>以下の内容で予約を受け付けました。</p>


            <ul>
                <li>お名前&#127872;</li>
                <li class="reserved-info"><?= htmlspecialchars($name) ?></li>
                <li>電話番号 &#x1F4DE;</li>
                <li class="reserved-info"><?= htmlspecialchars($tel) ?></li>
                <li>希望日 &#129668;&#129498;&#8205;&#9792;&#65039;</li>
                <li class="reserved-info"><?= htmlspecialchars($date) ?></li>
                <li>希望時間&#128171;&#127775; </li>
                <li class="reserved-info"><?= htmlspecialchars($time) ?></li>
                <li>ご要望・相談など</li>
                <li class="reserved-info"><?= nl2br(htmlspecialchars($note)) ?></li>
            </ul>
        </div>
        <button class="btn" onclick="location.href='./index.php'">トップに戻る</button>
    </main>


    <footer id="footer">milky salon site</footer>
</body>

</html>