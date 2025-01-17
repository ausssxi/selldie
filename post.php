<?php

ini_set('display_errors', "On");
date_default_timezone_set('Asia/Tokyo');

$mysqli = new mysqli('localhost', 'root', 'password', 'sell_die');
$mysqli->set_charset("utf8");

$thank = "回答";

$result_show = $mysqli->query("SHOW TABLES FROM sell_die");
while ($row = $result_show->fetch_row()) {
    $today = date("Y/m/d" ,strtotime("-1 day"));
    $date = explode("_", $row[0]);
    $mon_day = str_split($date[0], 2);
    $start = date("Y")."/$mon_day[0]/$mon_day[1]";
    $mon_day = str_split($date[1], 2);
    $end = date("Y")."/$mon_day[0]/$mon_day[1]";
    if(strtotime($today) >= strtotime($start) && strtotime($end) >= strtotime($today)) {
        $table = $row[0];
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $team = (string) $_GET["team"];
    if (!empty($_POST['sell'])) {
        $team_sell = $team . "sell";
        foreach ($_POST['sell'] as $value) {
            $int = intval($value);
            $stmt = $mysqli->prepare("UPDATE ${table} SET $team_sell = $team_sell + 1 WHERE id = ?");
            $stmt->bind_param('i', $int);
            $stmt->execute();
        }
    }
    if (!empty($_POST['die'])) {
        $team_die = $team . "die";
        foreach ($_POST['die'] as $value) {
            $int = intval($value);
            $stmt = $mysqli->prepare("UPDATE ${table} SET $team_die = $team_die + 1 WHERE id = ?");
            $stmt->bind_param('i', $int);
            $stmt->execute();
        }
    }
    if (!empty($_POST['name']) && !empty($_POST['price'])) {
        $int = intval($_POST['price']);
        $stmt = $mysqli->prepare("INSERT INTO ${table} (name, price) VALUES (?, ?)");
        $stmt->bind_param('si', $_POST['name'], $int);
        $stmt->execute();
        $thank = "商品の追加";
    }
    echo "<html lang='ja'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no'>
            <link rel='shortcut icon' href='flower.ico'>
            <title>売れ筋死に筋</title>
        </head>
        <body>
            <p>${thank}、ありがとうございました。</p>
            <a href='vote.php'>戻る</a>
        </body>
    </html>";
}

?>
