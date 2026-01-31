<?php
$host = "127.0.0.1";
$dbname = "salon";
$username = "root";
$password = "";
$port = "3306";

$pdo = new PDO("mysql:host=$host;dbname=$dbname;port=$port;charset=utf8", $username, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function h($s)
{
  return htmlspecialchars($s ?? "", ENT_QUOTES, "UTF-8");
}

$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $salonName = trim($_POST["サロン名"] ?? "");
  $pref      = trim($_POST["都道府県"] ?? "");
  $city      = trim($_POST["市区町村"] ?? "");
  $address   = trim($_POST["住所"] ?? "");
  $station   = trim($_POST["最寄り駅"] ?? "");
  $distance  = trim($_POST["駅からの距離"] ?? "");
  $phone     = trim($_POST["電話"] ?? "");
  $hours     = trim($_POST["営業時間"] ?? "");
  $intro     = trim($_POST["サロン紹介"] ?? "");

  // ===== 全部必須 =====
  if ($salonName === "") $errors[] = "サロン名は必須です。";
  if ($pref === "")      $errors[] = "都道府県は必須です。";
  if ($city === "")      $errors[] = "市区町村は必須です。";
  if ($address === "")   $errors[] = "住所は必須です。";
  if ($station === "")   $errors[] = "最寄り駅は必須です。";
  if ($distance === "")  $errors[] = "駅からの距離は必須です。";
  if ($phone === "")     $errors[] = "電話番号は必須です。";
  if ($hours === "")     $errors[] = "営業時間は必須です。";
  if ($intro === "")     $errors[] = "サロン紹介は必須です。";

  // 電話形式チェック（必須なので空チェックの後でOK）
  if ($phone !== "" && !preg_match('/^[0-9\-]+$/', $phone)) {
    $errors[] = "電話番号は数字とハイフンのみで入力してください。";
  }


  if (!$errors) {
    $sql = "INSERT INTO shop
      (サロン名, 都道府県, 最寄り駅, 駅からの距離, 市区町村, 住所, 電話, 営業時間, サロン紹介)
      VALUES
      (:salonName, :pref, :station, :distance, :city, :address, :phone, :hours, :intro)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
      ":salonName" => $salonName,
      ":pref"      => $pref,
      ":station"   => $station,
      ":distance"  => $distance,
      ":city"      => $city,
      ":address"   => $address,
      ":phone"     => $phone,
      ":hours"     => $hours,
      ":intro"     => $intro,
    ]);
    $success = true;
  }
}
?>


<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <title>サロン登録</title>
  <link rel="stylesheet" href="./css/common.css">
  <link rel="stylesheet" href="./css/reserve.css">
  <link rel="stylesheet" href="./css/shopcreate.css">
</head>

<body>
  <header id="header">
    <div class="logo">milky <span class="logo-small">nailsalonsite</span></div>
  </header>

  <div class="search-bar">
    <a class="bar-select" href="shop_create.php">管理：サロン登録</a> ▶
    <a href="menu_create.php">管理：メニュー登録</a>
  </div>

  <main id="main">
    <h1 class="custom">サロン登録</h1>

    <div class="fade-text fade-text-reserve">
      <section class="reserve-summary">
        <div class="reserve-box">

          <?php if ($success): ?>
            <div class="message" style="color: red;">登録できました！</div>
          <?php endif; ?>

          <form id="menuForm" method="post" enctype="multipart/form-data" class="form-grid"
         
         
         
          onsubmit="showErrors=true; return resetDo(true);">


            <div class="message" id="msg"></div>

            <div>
              <label>サロン名</label><br>
              <input type="text" name="サロン名" id="salonName" style="width: 33rem; height:1.8rem"
                value="<?= h($_POST["サロン名"] ?? "") ?>" placeholder="Cherie Nail など">
              <div class="errMsg" id="salonNameError"></div>
            </div><br>

            <div>
              <label>都道府県</label><br>
              <input type="text" name="都道府県" id="pref" style="width: 10rem; height:1.8rem"
                value="<?= h($_POST["都道府県"] ?? "") ?>" placeholder="岐阜県 など">
              <div class="errMsg" id="prefError"></div>
            </div><br>


            <div>
              <label>市区町村</label><br>
              <input type="text" name="市区町村" id="city" style="width: 10rem; height:1.8rem"
                value="<?= h($_POST["市区町村"] ?? "") ?>" placeholder="岐阜市 など">
              <div class="errMsg" id="cityError"></div>
            </div><br>

            <div>
              <label>住所</label><br>
              <input type="text" name="住所" id="address" style="width: 33rem; height:1.8rem"
                value="<?= h($_POST["住所"] ?? "") ?>" placeholder="岐阜県岐阜市…">
              <div class="errMsg" id="addressError"></div>
            </div><br>

            <div>
              <label>最寄り駅</label><br>
              <input type="text" name="最寄り駅" id="station" style="width: 10rem; height:1.8rem"
                value="<?= h($_POST["最寄り駅"] ?? "") ?>" placeholder="岐阜 など">
              <div class="errMsg" id="stationError"></div>
            </div><br>

            <div>
              <label>駅からの距離</label><br>
              <input type="text" name="駅からの距離" id="distance" style="width: 10rem; height:1.8rem"
                value="<?= h($_POST["駅からの距離"] ?? "") ?>" placeholder="徒歩7分 など">
              <div class="errMsg" id="distanceError"></div>
            </div><br>

            <div>
              <label>電話</label><br>
              <input type="text" name="電話" id="phone" style="width: 10rem; height:1.8rem"
                value="<?= h($_POST["電話"] ?? "") ?>" placeholder="000-0000-0000">
              <div class="errMsg" id="phoneError"></div>
            </div><br>

            <div>
              <label>営業時間</label><br>
              <input type="text" name="営業時間" id="hours" style="width: 10rem; height:1.8rem"
                value="<?= h($_POST["営業時間"] ?? "") ?>" placeholder="10:00〜19:00">
              <div class="errMsg" id="hoursError"></div>
            </div><br>

            <div>
              <label>サロン紹介</label><br>
              <textarea name="サロン紹介" id="intro" rows="5" style="width: 33rem;"><?= h($_POST["サロン紹介"] ?? "") ?></textarea>
              <div class="errMsg" id="introError"></div>
            </div>

            <button type="submit" class="btn">この内容で登録する</button>
          </form>




        </div>
      </section>
    </div>
    <div class="btn-all">
      <button type="button" class="btn btn-a" onclick="location.href='search.php'">検索で確認</button>
      <button type="button" class="btn btn-a" onclick="location.href='menucreate.php'">メニュー登録へ</button>
    </div>
  </main>

  <footer id="footer">milky salon site</footer>
  <script src="./js/shopcreate.js"></script>
</body>

</html>