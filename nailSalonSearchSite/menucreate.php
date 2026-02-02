<?php
// menu_create.php（メニュー登録 = menuテーブル）

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

// サロン一覧
$shops = $pdo->query("SELECT id, サロン名 FROM shop ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
$preSalonId = (int)($_GET["salonid"] ?? 0);

$errors = [];
$success = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $salonid  = (int)($_POST["salonid"] ?? 0);
    $menuName = trim($_POST["menu"] ?? "");
    $category = trim($_POST["カテゴリ"] ?? "");
    $detail   = trim($_POST["メニュー説明詳細"] ?? "");
    $time     = trim($_POST["時間"] ?? "");
    $price    = trim($_POST["価格"] ?? "");

    // ===== 全部必須 =====
    if ($salonid <= 0) $errors[] = "サロンを選択してね。";
    if ($menuName === "") $errors[] = "メニュー名は必須です。";
    if ($category === "") $errors[] = "カテゴリは必須です。";
    if ($detail === "") $errors[] = "メニュー説明（詳細）は必須です。";

    if ($time === "") $errors[] = "時間（分）は必須です。";
    else if (!ctype_digit($time)) $errors[] = "時間（分）は数字で入力してね。";

    if ($price === "") $errors[] = "価格（円）は必須です。";
    else if (!ctype_digit($price)) $errors[] = "価格（円）は数字で入力してね。";

    // 画像も必須にする
    $imagePath = null;
    if (empty($_FILES["画像"]["name"])) {
        $errors[] = "画像は必須です。";
    } else {
        $file = $_FILES["画像"];
        if ($file["error"] !== UPLOAD_ERR_OK) {
            $errors[] = "画像アップロードに失敗しました。";
        } else {
            $ext = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
            $allowed = ["jpg", "jpeg", "png", "webp"];
            if (!in_array($ext, $allowed, true)) {
                $errors[] = "画像は jpg/jpeg/png/webp のみ対応です。";
            }
        }
    }

    // 画像保存（エラーがなければ）
    if (!$errors) {
        $dir = __DIR__ . "/uploads/menus";
        if (!is_dir($dir)) mkdir($dir, 0777, true);

        $ext = strtolower(pathinfo($_FILES["画像"]["name"], PATHINFO_EXTENSION));
        $newName = uniqid("menu_", true) . "." . $ext;
        $dest = $dir . "/" . $newName;

        if (!move_uploaded_file($_FILES["画像"]["tmp_name"], $dest)) {
            $errors[] = "画像保存に失敗しました。";
        } else {
            $imagePath = "uploads/menus/" . $newName;
        }
    }

    // INSERT
    if (!$errors) {
        $sql = "INSERT INTO menu
      (salonid, menu, カテゴリ, メニュー説明詳細, 時間, 価格, 画像)
      VALUES
      (:salonid, :menu, :cat, :detail, :time, :price, :img)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":salonid" => $salonid,
            ":menu"    => $menuName,
            ":cat"     => $category,
            ":detail"  => $detail,
            ":time"    => (int)$time,
            ":price"   => (int)$price,
            ":img"     => $imagePath,
        ]);

        $success = true;
        $preSalonId = $salonid;
        // 入力欄を空にしたいなら↓（好み）
        // $_POST = [];
    }
}
?>
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>メニュー登録</title>
    <link rel="stylesheet" href="./css/common.css">
    <link rel="stylesheet" href="./css/reserve.css">
    <link rel="stylesheet" href="./css/menucreate.css">

</head>

<body>
    <header id="header">
        <div class="logo">milky <span class="logo-small">nailsalonsite</span></div>
    </header>

    <div class="search-bar">
        <a href="index.php">トップ</a> ▶
        <a href="shop_create.php">管理：サロン登録</a> ▶
        <a class="bar-select" href="menu_create.php">管理：メニュー登録</a>
    </div>

    <main id="main">
        <h1 class="custom">メニュー登録（管理）</h1>

        <div class="fade-text fade-text-reserve">
            <section class="reserve-summary">
                <div class="reserve-box">

                    <?php if ($success): ?>
                        <div class="message ok">登録できました</div>
                    <?php endif; ?>

                    <?php if ($errors): ?>
                        <div class="message ng">
                            <strong>入力を確認してね</strong>
                            <ul><?php foreach ($errors as $e): ?><li><?= h($e) ?></li><?php endforeach; ?></ul>
                        </div>
                    <?php endif; ?>

                    <form id="menuForm" method="post" enctype="multipart/form-data" class="form-grid"
                        onsubmit="showErrors=true; return resetDo(true);">

                        <div class="message" id="msg"></div>

                        <div>
                            <div>サロン（必須）</div>
                            <select name="salonid" id="salonid" style="height: 1.8rem;">
                                <option value="">選択してください</option>
                                <?php foreach ($shops as $s): ?>
                                    <?php $selectedId = (int)($preSalonId ?: ($_POST["salonid"] ?? 0)); ?>
                                    <option value="<?= (int)$s["id"] ?>" <?= ($selectedId === (int)$s["id"] ? "selected" : "") ?>>
                                        <?= h($s["サロン名"]) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="errMsg" id="salonidError"></div>
                        </div>

                        <div>
                            <div>メニュー名（必須）</div>
                            <input name="menu" id="menu" value="<?= h($_POST["menu"] ?? "") ?>" placeholder="ワンカラー / マグネット など">
                            <div class="errMsg" id="menuError"></div>
                        </div>

                        <div class="two">
                            <div>
                                <div>カテゴリ（必須）</div>
                                <input name="カテゴリ" id="category" value="<?= h($_POST["カテゴリ"] ?? "") ?>" placeholder="おすすめ / 人気 など">
                                <div class="errMsg" id="categoryError"></div>
                            </div>

                            <div>
                                <div>画像（必須）</div>

                                <div class="file-ui" id="imageUI">
                                    <input type="file" name="画像" id="image" accept=".jpg,.jpeg,.png,.webp">
                                    <label for="image" class="file-btn">画像を選ぶ</label>
                                    <span class="file-name" id="imageName">未選択</span>
                                </div>

                                <div class="errMsg" id="imageError"></div>
                            </div>


                            <div>
                                <div>メニュー説明（詳細）（必須）</div>
                                <textarea name="メニュー説明詳細" id="detail" placeholder="内容・仕上がり・注意点など"
                                    style="width:30rem;height:5rem;"><?= h($_POST["メニュー説明詳細"] ?? "") ?></textarea>
                                <div class="errMsg" id="detailError"></div>
                            </div>

                            <div class="two">
                                <div>
                                    <div>時間（分）（必須）</div>
                                    <input name="時間" id="time" value="<?= h($_POST["時間"] ?? "") ?>" placeholder="60">
                                    <div class="errMsg" id="timeError"></div>
                                </div>
                                <div>
                                    <div>価格（円）（必須）</div>
                                    <input name="価格" id="price" value="<?= h($_POST["価格"] ?? "") ?>" placeholder="6500">
                                    <div class="errMsg" id="priceError"></div>
                                </div>
                            </div>

                            <button type="submit" class="btn">この内容で登録する</button>


                    </form>


            </section>

            <div class="btn-all">
                <button type="button" class="btn btn-a" onclick="location.href='menu.php?salonid=<?= (int)$preSalonId ?>'">メニュー一覧で確認</button>
                <button type="button" class="btn btn-a" onclick="location.href='shopcreate.php'">サロン登録へ戻る</button>
            </div>

        </div>
        </div>
    </main>

    <footer id="footer">milky salon site</footer>
    <script src="./js/menucreate.js"></script>
</body>

</html>