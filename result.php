<?php

ini_set('display_errors', "On");
date_default_timezone_set('Asia/Tokyo');
require_once(__DIR__ . '/vendor/autoload.php');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment as Align;

$mysqli = new mysqli('localhost', 'root', 'password', 'sell_die');
$mysqli->set_charset("utf8");
$result = $mysqli->query("SHOW TABLES FROM sell_die");
while ($row = $result->fetch_row()) {
    $today = date("Y/m/d" ,strtotime("-1 day"));
    $date = explode("_", $row[0]);
    $mon_day = str_split($date[0], 2);
    $start = date("Y")."/$mon_day[0]/$mon_day[1]";
    $mon_day = str_split($date[1], 2);
    $end = date("Y")."/$mon_day[0]/$mon_day[1]";
    if (strtotime($today) >= strtotime($start) && strtotime($end) >= strtotime($today)) {
        $table = $row[0];
    }
}
if (isset($_GET["table"])) {
    $table = $_GET["table"];
}

$mon = "";
$wed = "";
$fri = "";
$all = "";
$week = "all";
if (isset($_GET["week"])) {
    switch ($_GET["week"]) {
        case "mon":
            $week = "mon";
            $mon = "selected";
            break;
        case "wed":
            $week = "wed";
            $wed = "selected";
            break;
        case "fri":
            $week = "fri";
            $fri = "selected";
            break;
        case "all":
            $all = "selected";
            break;
    }
}

$team_10 = "";
$team_20 = "";
$team_30 = "";
$team_40 = "";
$team_50 = "";
$team_60 = "";
$team_70 = "";
$team_00 = "";
$team = 00;
if (isset($_GET["team"])) {
    switch ($_GET["team"]) {
        case 10:
            $team = 10;
            $team_10 = "selected";
            break;
        case 20:
            $team = 20;
            $team_20 = "selected";
            break;
        case 30:
            $team = 30;
            $team_30 = "selected";
            break;
        case 40:
            $team = 40;
            $team_40 = "selected";
            break;
        case 50:
            $team = 50;
            $team_50 = "selected";
            break;
        case 60:
            $team = 60;
            $team_60 = "selected";
            break;
        case 70:
            $team = 70;
            $team_70 = "selected";
            break;
        case 00:
            $team = 00;
            $team_00 = "selected";
            break;
    }
}

if (isset($_POST["upload"])) {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    if ($week == "all" and $team == 00) {
        $result = $mysqli->query("SELECT name, price, (10sell + 20sell + 30sell + 40sell + 50sell + 60sell + 70sell) AS total FROM ${table} WHERE 10sell != 0 OR 20sell != 0 OR 30sell != 0 OR 40sell != 0 OR 50sell != 0 OR 60sell != 0 OR 70sell != 0 ORDER BY total DESC, name ASC");
    } else if($week != "all" and $team != 00) {
        $team_sell = $team . "sell";
        $result = $mysqli->query("SELECT name, price, ${team_sell} FROM ${table} WHERE ${team_sell} > 0 and ${week} > 0 ORDER BY ${team_sell} DESC, name ASC");
    } else if($week != "all") {
        $result = $mysqli->query("SELECT name, price, (10sell + 20sell + 30sell + 40sell + 50sell + 60sell + 70sell) AS total FROM ${table} WHERE (10sell != 0 OR 20sell != 0 OR 30sell != 0 OR 40sell != 0 OR 50sell != 0 OR 60sell != 0 OR 70sell != 0) and ${week} > 0 ORDER BY total DESC, name");
    } else {
        $team_sell = $team . "sell";
        $result = $mysqli->query("SELECT name, price, ${team_sell} FROM ${table} WHERE ${team_sell} > 0 ORDER BY ${team_sell} DESC, name ASC");
    }
    $count = $result->num_rows;
    $count = $count + 4;
    $sheet->getRowDimension(2)->setRowHeight(26.0);
    for ($i = 3; $i <= $count; $i++) {
        $sheet->getRowDimension($i)->setRowHeight(22.0);
    }
    for ($i = 1; $i <= $count; $i++) {
        $sheet -> getStyle("B${i}")->getAlignment()->setVertical(Align::VERTICAL_CENTER);
        $sheet -> getStyle("C${i}")->getAlignment()->setVertical(Align::VERTICAL_CENTER);
        $sheet -> getStyle("D${i}")->getAlignment()->setVertical(Align::VERTICAL_CENTER);
    }
    $i = 4;
    while ($row = $result->fetch_assoc()) {
        if (isset($row["total"])) {
            $total = $row["total"];
        }
        if (isset($team_sell)) {
            if (isset($row[$team_sell])) {
                $total = $row[$team_sell];
            }
        }
        $borders = $sheet->getStyle("C${i}")->getBorders();
        $borders->getLeft()->setBorderStyle('thin');
        $borders = $sheet->getStyle("D${i}")->getBorders();
        $borders->getLeft()->setBorderStyle('thin');
        $i++;
        $sheet->setCellValue("B${i}", $row["name"]);
        $sheet->setCellValue("C${i}", $row["price"]);
        $sheet->setCellValue("D${i}", $total);
        $borders = $sheet->getStyle("B${i}:D${i}")->getBorders();
        $borders->getBottom()->setBorderStyle('thin');
    }
    $borders = $sheet->getStyle("C${i}")->getBorders();
    $borders->getLeft()->setBorderStyle('thin');
    $borders = $sheet->getStyle("D${i}")->getBorders();
    $borders->getLeft()->setBorderStyle('thin');
    $dim = $sheet->getColumnDimension("B");
    $dim->setAutoSize(true);
    $sheet->calculateColumnWidths();
    $dim->setAutoSize(false);
    $col_width = $dim->getWidth();
    $dim -> setWidth($col_width * 1.7);
    $borders = $sheet->getStyle("B4:D${count}")->getBorders();
    $borders->getTop()->setBorderStyle('double');
    $borders->getBottom()->setBorderStyle('double');
    $borders->getleft()->setBorderStyle('double');
    $borders->getRight()->setBorderStyle('double');
    $borders = $sheet->getStyle("B4:D4")->getBorders();
    $borders->getBottom()->setBorderStyle('medium');
    $sheet->mergeCells('B2:D2');
    $sheet->getStyle('B2')->getFont()->setBold(true);
    $sheet->getStyle('B2')->getFont()->setSize(13);
    $sheet->setCellValue('B2', "売れ筋");
    $sheet->setCellValue('B4', "商品名");
    $sheet->setCellValue('C4', "価格");
    $sheet->setCellValue('D4', "評価数");
    $sheet->getStyle("B2")->getAlignment()->setHorizontal(Align::HORIZONTAL_CENTER);
    $sheet->getStyle("B4")->getAlignment()->setHorizontal(Align::HORIZONTAL_CENTER);
    $sheet->getStyle("C4")->getAlignment()->setHorizontal(Align::HORIZONTAL_CENTER);
    $sheet->getStyle("D4")->getAlignment()->setHorizontal(Align::HORIZONTAL_CENTER);

    if ($week == "all" and $team == 00) {
        $result = $mysqli->query("SELECT name, price, (10die + 20die + 30die + 40die + 50die + 60die + 70die) AS total FROM ${table} WHERE 10die != 0 OR 20die != 0 OR 30die != 0 OR 40die != 0 OR 50die != 0 OR 60die != 0  OR 70die != 0 ORDER BY total DESC, name ASC");
    } else if($week != "all" and $team != 00) {
        $team_die = $team . "die";
        $result = $mysqli->query("SELECT name, price, ${team_die} FROM ${table} WHERE ${team_die} > 0 and ${week} > 0 ORDER BY ${team_die} DESC, name ASC");
    } else if($week != "all") {
        $result = $mysqli->query("SELECT name, price, (10die + 20die + 30die + 40die + 50die + 60die + 70die) AS total FROM ${table} WHERE (10die != 0 OR 20die != 0 OR 30die != 0 OR 40die != 0 OR 50die != 0 OR 60die != 0 OR 70die != 0) and ${week} > 0 ORDER BY total DESC, name");
    } else {
        $team_die = $team . "die";
        $result = $mysqli->query("SELECT name, price, ${team_die} FROM ${table} WHERE ${team_die} > 0 ORDER BY ${team_die} DESC, name ASC");
    }
    $count = $result->num_rows;
    $count = $count + 4;
    $sheet->getRowDimension(2)->setRowHeight(26.0);
    for ($i = 3; $i <= $count; $i++) {
        $sheet->getRowDimension($i)->setRowHeight(22.0);
    }
    for ($i = 1; $i <= $count; $i++) {
        $sheet -> getStyle("F${i}")->getAlignment()->setVertical(Align::VERTICAL_CENTER);
        $sheet -> getStyle("G${i}")->getAlignment()->setVertical(Align::VERTICAL_CENTER);
        $sheet -> getStyle("H${i}")->getAlignment()->setVertical(Align::VERTICAL_CENTER);
    }
    $i = 4;
    while ($row = $result->fetch_assoc()) {
        if (isset($row["total"])) {
            $total = $row["total"];
        }
        if (isset($team_die)) {
            if (isset($row[$team_die])) {
                $total = $row[$team_die];
            }
        }
        $borders = $sheet->getStyle("G${i}")->getBorders();
        $borders->getLeft()->setBorderStyle('thin');
        $borders = $sheet->getStyle("H${i}")->getBorders();
        $borders->getLeft()->setBorderStyle('thin');
        $i++;
        $sheet->setCellValue("F${i}", $row["name"]);
        $sheet->setCellValue("G${i}", $row["price"]);
        $sheet->setCellValue("H${i}", $total);
        $borders = $sheet->getStyle("F${i}:H${i}")->getBorders();
        $borders->getBottom()->setBorderStyle('thin');
    }
    $borders = $sheet->getStyle("G${i}")->getBorders();
    $borders->getLeft()->setBorderStyle('thin');
    $borders = $sheet->getStyle("H${i}")->getBorders();
    $borders->getLeft()->setBorderStyle('thin');
    $dim = $sheet->getColumnDimension("F");
    $dim->setAutoSize(true);
    $sheet->calculateColumnWidths();
    $dim->setAutoSize(false);
    $col_width = $dim->getWidth();
    $dim -> setWidth($col_width * 1.7);
    $borders = $sheet->getStyle("F4:H${count}")->getBorders();
    $borders->getTop()->setBorderStyle('double');
    $borders->getBottom()->setBorderStyle('double');
    $borders->getleft()->setBorderStyle('double');
    $borders->getRight()->setBorderStyle('double');
    $borders = $sheet->getStyle("F4:H4")->getBorders();
    $borders->getBottom()->setBorderStyle('medium');
    $sheet->mergeCells('F2:H2');
    $sheet->getStyle('F2')->getFont()->setBold(true);
    $sheet->getStyle('F2')->getFont()->setSize(13);
    $sheet->setCellValue('F2', "死に筋");
    $sheet->setCellValue('F4', "商品名");
    $sheet->setCellValue('G4', "価格");
    $sheet->setCellValue('H4', "評価数");
    $sheet->getStyle("F2")->getAlignment()->setHorizontal(Align::HORIZONTAL_CENTER);
    $sheet->getStyle("F4")->getAlignment()->setHorizontal(Align::HORIZONTAL_CENTER);
    $sheet->getStyle("G4")->getAlignment()->setHorizontal(Align::HORIZONTAL_CENTER);
    $sheet->getStyle("H4")->getAlignment()->setHorizontal(Align::HORIZONTAL_CENTER);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="売れ死に' . $table . '.xlsx"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    die;
}

?>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <link rel="shortcut icon" href="flower.ico">
        <title>売れ筋死に筋</title>
    </head>
    <style>
        input {
            font-size: 100%;
        }
        select {
            font-size: 100%;
        }
        table {
            border-collapse: collapse;
        }
        td {
            border: solid;
        }
        .padding-8 {
            padding: 8px;
        }
        .flex {
            display: flex;
        }
        .space-between {
            justify-content: space-between;
        }
        .right {
            text-align: right
        }
    </style>
    <body>
    </style>
    <body>
        <div class='flex space-between padding-8'>
            <div>
                <a href='vote.php'>戻る</a>
            </div>
            <div>
                <form action="result.php" method = "GET">
                    <select name="week">
                        <option value = "all" <?php echo $all ?>>全て</option>
                        <option value = "mon" <?php echo $mon ?>>月</option>
                        <option value = "wed" <?php echo $wed ?>>水</option>
                        <option value = "fri" <?php echo $fri ?>>金</option>
                   </select>
                   <select name="team">
                       <option value = "00" <?php echo $team_00 ?>>全て</option>
                       <option value = "10" <?php echo $team_10 ?>>10</option>
                       <option value = "20" <?php echo $team_20 ?>>20</option>
                       <option value = "30" <?php echo $team_30 ?>>30</option>
                       <option value = "40" <?php echo $team_40 ?>>40</option>
                       <option value = "50" <?php echo $team_50 ?>>50</option>
                       <option value = "60" <?php echo $team_60 ?>>60</option>
                       <option value = "70" <?php echo $team_70 ?>>70</option>
                  </select>
                  <input type="submit" value="送信"/>
                </form>
            </div>
        </div>
        <div class='flex'>
            <div class='padding-8'>
                <p>売れ筋</p>
                <table>
                    <tr>
                        <td class='padding-8'>商品名</td>
                        <td class='padding-8'>価格</td>
                        <td class='padding-8'>評価数</td>
                        <td class='padding-8'>月</td>
                        <td class='padding-8'>水</td>
                        <td class='padding-8'>金</td>
                    </tr>
                    <?php
                    if (isset($table)) {
                        if ($week == "all" and $team == 00) {
                            $result = $mysqli->query("SELECT name, price, (10sell + 20sell + 30sell + 40sell + 50sell + 60sell + 70sell) AS total, mon, wed, fri FROM ${table} WHERE 10sell != 0 OR 20sell != 0 OR 30sell != 0 OR 40sell != 0 OR 50sell != 0 OR 60sell != 0 OR 70sell != 0 ORDER BY total DESC, name ASC");
                        } else if($week != "all" and $team != 00) {
                            $team_sell = $team . "sell";
                            $result = $mysqli->query("SELECT name, price, ${team_sell}, mon, wed, fri FROM ${table} WHERE ${team_sell} > 0 and ${week} > 0 ORDER BY ${team_sell} DESC, name ASC");
                        } else if($week != "all") {
                            $result = $mysqli->query("SELECT name, price, (10sell + 20sell + 30sell + 40sell + 50sell + 60sell + 70sell) AS total, mon, wed, fri FROM ${table} WHERE (10sell != 0 OR 20sell != 0 OR 30sell != 0 OR 40sell != 0 OR 50sell != 0 OR 60sell != 0 OR 70sell != 0) and ${week} > 0 ORDER BY total DESC, name ASC");
                        } else {
                            $team_sell = $team . "sell";
                            $result = $mysqli->query("SELECT name, price, ${team_sell}, mon, wed, fri FROM ${table} WHERE ${team_sell} > 0 ORDER BY ${team_sell} DESC, name ASC");
                        }
                        while ($row = $result->fetch_assoc()) {
                            if ($row["mon"] == 1) {
                                $mon = "○";
                            } else {
                                $mon = "";
                            }
                            if ($row["wed"] == 1) {
                                $wed = "○";
                            } else {
                                $wed = "";
                            }
                            if ($row["fri"] == 1) {
                                $fri = "○";
                            } else {
                                $fri = "";
                            }
                            if (isset($row["total"])) {
                                $total = $row["total"];
                            }
                            if (isset($team_sell)) {
                                if (isset($row[$team_sell])) {
                                    $total = $row[$team_sell];
                                }
                            }
                            echo "<tr>
                                <td class='padding-8'>$row[name]</td>
                                <td class='padding-8'>$row[price]</td>
                                <td class='padding-8'>${total}</td>
                                <td class='padding-8'>${mon}</td>
                                <td class='padding-8'>${wed}</td>
                                <td class='padding-8'>${fri}</td>
                            </tr>";
                        }
                    }
                    ?>
                </table>
            </div>
            <div class='padding-8'>
                <p>死に筋</p>
                <table>
                    <tr>
                        <td class='padding-8'>商品名</td>
                        <td class='padding-8'>価格</td>
                        <td class='padding-8'>評価数</td>
                        <td class='padding-8'>月</td>
                        <td class='padding-8'>水</td>
                        <td class='padding-8'>金</td>
                    </tr>
                    <?php
                    if (isset($table)) {
                        if ($week == "all" and $team == 00) {
                            $result = $mysqli->query("SELECT name, price, (10die + 20die + 30die + 40die + 50die + 60die + 70die) AS total, mon, wed, fri FROM ${table} WHERE 10die != 0 OR 20die != 0 OR 30die != 0 OR 40die != 0 OR 50die != 0 OR 60die != 0 OR 70die != 0 ORDER BY total DESC, name ASC");
                        } else if($week != "all" and $team != 00) {
                            $team_die = $team . "die";
                            $result = $mysqli->query("SELECT name, price, ${team_die}, mon, wed, fri FROM ${table} WHERE ${team_die} > 0 and ${week} > 0 ORDER BY ${team_die} DESC, name ASC");
                        } else if($week != "all") {
                            $result = $mysqli->query("SELECT name, price, (10die + 20die + 30die + 40die + 50die + 60die + 70die) AS total, mon, wed, fri FROM ${table} WHERE (10die != 0 OR 20die != 0 OR 30die != 0 OR 40die != 0 OR 50die != 0 OR 60die != 0 OR 70die != 0) and ${week} > 0 ORDER BY total DESC, name ASC");
                        } else {
                            $team_die = $team . "die";
                            $result = $mysqli->query("SELECT name, price, ${team_die}, mon, wed, fri FROM ${table} WHERE ${team_die} > 0 ORDER BY ${team_die} DESC, name ASC");
                        }
                        while ($row = $result->fetch_assoc()) {
                            if ($row["mon"] == 1) {
                                $mon = "○";
                            } else {
                                $mon = "";
                            }
                            if ($row["wed"] == 1) {
                                $wed = "○";
                            } else {
                                $wed = "";
                            }
                            if ($row["fri"] == 1) {
                                $fri = "○";
                            } else {
                                $fri = "";
                            }
                            if (isset($row["total"])) {
                                $total = $row["total"];
                            }
                            if (isset($team_die)) {
                                if (isset($row[$team_die])) {
                                    $total = $row[$team_die];
                                }
                            }
                            echo "<tr>
                                <td class='padding-8'>$row[name]</td>
                                <td class='padding-8'>$row[price]</td>
                                <td class='padding-8'>${total}</td>
                                <td class='padding-8'>${mon}</td>
                                <td class='padding-8'>${wed}</td>
                                <td class='padding-8'>${fri}</td>
                            </tr>";
                        }
                    }
                    ?>
                </table>
            </div>
        </div>
        <div>
            <?php
            echo "<form action='result.php?table=${table}&week=${week}&team=${team}' method='post'>";
            ?>
                <input type='submit' value='出力' name='upload'>
            </form>
        </div>
        <br>
        <p>過去の売れ死に</p>
        <?php
            $result = $mysqli->query("SHOW TABLES FROM sell_die");
            while ($row = $result->fetch_row()) {
                echo "<p><a href='result.php?table=$row[0]'>$row[0]</a></p>";
            }
        ?>
        <br>
        <p>総合計を追加</p>
        <form action="database.php" method="post" enctype="multipart/form-data">
            <div>
                <p>
                    <input type="file" name="file" />
                </p>
            </div>
            <div>
                <p>
                    <input type="submit" value="アップロード">
                </p>
            </div>
        </form>
    </body>
</html>
