<?php

ini_set('display_errors', "On");
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

$mysqli = new mysqli('localhost', 'root', 'password', 'sell_die');
$mysqli->set_charset("utf8");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_FILES['file']['name'] == "総合計.xls") {
        $storeDir = '/var/www/html/';
        $filename = "総合計.xls";
        move_uploaded_file($_FILES['file']['tmp_name'], $storeDir.$filename);
        $spreadsheet = IOFactory::load($filename);
        $sheet = $spreadsheet->getSheetByName('総合計');
        $before_month = str_pad($sheet->getCell('H2')->getOldCalculatedValue(), 2, '0', STR_PAD_LEFT);
        $before_day = str_pad($sheet->getCell('J2')->getOldCalculatedValue(), 2, '0', STR_PAD_LEFT);
        $after_month = str_pad($sheet->getCell('M2')->getOldCalculatedValue(), 2, '0', STR_PAD_LEFT);
        $after_day = str_pad($sheet->getCell('O2')->getOldCalculatedValue(), 2, '0', STR_PAD_LEFT);
        $period = $before_month.$before_day."_".$after_month.$after_day;
        $result = $mysqli->query("SHOW TABLES FROM sell_die");
        $row = $result->fetch_row();
        if (!in_array($period, $row)) {
            $stmt = $mysqli->prepare("CREATE TABLE ${period} (id smallint(5) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT, name varchar(300) UNIQUE, price smallint(5) UNSIGNED, 10sell tinyint(3) UNSIGNED default 0, 10die tinyint(3) UNSIGNED default 0, 20sell tinyint(3) UNSIGNED default 0, 20die tinyint(3) UNSIGNED default 0, 30sell tinyint(3) UNSIGNED default 0, 30die tinyint(3) UNSIGNED default 0, 40sell tinyint(3) UNSIGNED default 0, 40die tinyint(3) UNSIGNED default 0, 50sell tinyint(3) UNSIGNED default 0, 50die tinyint(3) UNSIGNED default 0, 60sell tinyint(3) UNSIGNED default 0, 60die tinyint(3) UNSIGNED default 0, 70sell tinyint(3) UNSIGNED default 0, 70die tinyint(3) UNSIGNED default 0, mon BOOLEAN, wed BOOLEAN, fri BOOLEAN) engine = innodb default charset = utf8");
            $stmt->execute();
        }
        for ($i = 9; $i < 110; $i++) {
            if ($sheet->getCell("B${i}")->getCalculatedValue() == '#REF!' || $sheet->getCell("B${i}")->getCalculatedValue() == '#VALUE!') {
                $item = $sheet->getCell("B${i}")->getOldCalculatedValue();
            } else {
                $item = $sheet->getCell("B${i}")->getCalculatedValue();
            }
            if ($sheet->getCell("M${i}")->getCalculatedValue() == null || $sheet->getCell("M${i}")->getCalculatedValue() == '#VALUE!') {
                $tue = $sheet->getCell("M${i}")->getOldCalculatedValue();
            } else {
                $tue = $sheet->getCell("M${i}")->getCalculatedValue();
            }
            if ($sheet->getCell("N${i}")->getCalculatedValue() == null || $sheet->getCell("N${i}")->getCalculatedValue() == '#VALUE!') {
                $wed = $sheet->getCell("N${i}")->getOldCalculatedValue();
            } else {
                $wed = $sheet->getCell("N${i}")->getCalculatedValue();
            }
            if ($sheet->getCell("O${i}")->getCalculatedValue() == null || $sheet->getCell("O${i}")->getCalculatedValue() == '#VALUE!') {
                $thu = $sheet->getCell("O${i}")->getOldCalculatedValue();
            } else {
                $thu = $sheet->getCell("O${i}")->getCalculatedValue();
            }
            if ($sheet->getCell("P${i}")->getCalculatedValue() == null || $sheet->getCell("P${i}")->getCalculatedValue() == '#VALUE!') {
                $fri = $sheet->getCell("P${i}")->getOldCalculatedValue();
            } else {
                $fri = $sheet->getCell("P${i}")->getCalculatedValue();
            }
            if ($sheet->getCell("Q${i}")->getCalculatedValue() == null || $sheet->getCell("Q${i}")->getCalculatedValue() == '#VALUE!') {
                $sat = $sheet->getCell("Q${i}")->getOldCalculatedValue();
            } else {
                $sat = $sheet->getCell("Q${i}")->getCalculatedValue();
            }
            if ($sheet->getCell("R${i}")->getCalculatedValue() == null || $sheet->getCell("R${i}")->getCalculatedValue() == '#VALUE!') {
                $sun = $sheet->getCell("R${i}")->getOldCalculatedValue();
            } else {
                $sun = $sheet->getCell("R${i}")->getCalculatedValue();
            }
            if ($sheet->getCell("S${i}")->getCalculatedValue() == null || $sheet->getCell("S${i}")->getCalculatedValue() == '#VALUE!') {
                $mon = $sheet->getCell("S${i}")->getOldCalculatedValue();
            } else {
                $mon = $sheet->getCell("S${i}")->getCalculatedValue();
            }
            if (strpos($item,'単品・シンプル束') !== false && ($tue >= 1 || $wed >= 1 || $thu >= 1 || $fri >= 1 || $sat >= 1 || $sun >= 1 || $mon >= 1)) {
                $name = explode("・資材", $sheet->getCell("C${i}")->getOldCalculatedValue());
                $name_replace = preg_replace( "/（.+?）/", "", $name[0]);
                $name_replace = preg_replace( "/\(.+?\)/", "", $name_replace);
                $name_replace = preg_replace( "/[0-9]{2}㎝/", "", $name_replace);
                $name_replace = preg_replace( "/[0-9]{2}cm/", "", $name_replace);
                $name_replace = preg_replace( "/[0-9]{2}ｃｍ/", "", $name_replace);
                $name_replace = preg_replace( "/[0-9]{2}入/", "", $name_replace);
                $name_replace = preg_replace( "/[0-9]{1}-[0-9]{1}F/", "", $name_replace);
                $name_replace = preg_replace( "/[0-9]{1}\/[0-9]{1}F/", "", $name_replace);
                $name_replace = preg_replace( "/S[0-9]{2}/", "", $name_replace);
                $name_replace = preg_replace( "/M[0-9]{2}/", "", $name_replace);
                $name_replace = preg_replace( "/L[0-9]{2}/", "", $name_replace);
                $name_replace = preg_replace( "/優[0-9]{2}/", "", $name_replace);
                $name_replace = preg_replace( "/秀[0-9]{2}/", "", $name_replace);
                $name_replace = preg_replace( "/(・)+$/", "", $name_replace);
                $name_replace = str_replace(" ", "", $name_replace);
                $name_replace = str_replace("　", "", $name_replace);
                $name_replace = str_replace("～", "", $name_replace);
                $name_replace = str_replace("×1", "", $name_replace);
                $name_replace = str_replace("× 1", "", $name_replace);
                $name_replace = str_replace("以上", "", $name_replace);
                $name_replace = str_replace("L/2L", "", $name_replace);
                $name_replace = str_replace("L/２L", "", $name_replace);
                $name_replace = str_replace("Ｌ/２Ｌ", "", $name_replace);
                $name_replace = str_replace("L2L", "", $name_replace);
                $name_replace = str_replace("MIX", "", $name_replace);
                $name_replace = str_replace("ＭＩＸ", "", $name_replace);
                $name_replace = str_replace("Mix", "", $name_replace);
                $name_replace = str_replace("mix", "", $name_replace);
                $name_replace = str_replace("FLC級", "", $name_replace);
                $name_replace = str_replace("ＦＬＣ級", "", $name_replace);
                $name_replace = str_replace("定期", "", $name_replace);
                $name_replace = str_replace("中国", "", $name_replace);
                $name_replace = str_replace("前倒し", "", $name_replace);
                $name_replace = str_replace("在庫", "", $name_replace);
                $name_replace = str_replace("お任せ", "", $name_replace);
                $name_replace = str_replace("代品", "", $name_replace);
                $name_replace = str_replace("不足", "", $name_replace);
                $name_replace = str_replace("洋花用", "", $name_replace);
                $name_replace = str_replace("国産/輸入", "", $name_replace);
                $price = $sheet->getCell("I${i}")->getOldCalculatedValue();
                if ($tue >= 1 || $wed >= 1) {
                    $monday = 1;
                } else {
                    $monday = 0;
                }
                if ($thu >= 1 || $fri >= 1) {
                    $wednesday = 1;
                } else {
                    $wednesday = 0;
                }
                if ($sat >= 1 || $sun >= 1 || $mon >= 1) {
                    $friday = 1;
                } else {
                    $friday = 0;
                }
                $stmt = $mysqli->prepare("INSERT INTO ${period} (name, price, mon, wed, fri) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param('siiii', $name_replace, $price, $monday, $wednesday, $friday);
                $stmt->execute();
            } elseif (strpos($item,'通常MIX') !== false && ($tue >= 1 || $wed >= 1 || $thu >= 1 || $fri >= 1 || $sat >= 1 || $sun >= 1 || $mon >= 1)) {
                $name = explode("・資材", $sheet->getCell("C${i}")->getOldCalculatedValue());
                $name_replace = preg_replace( "/（.+?）/", "", $name[0]);
                $name_replace = preg_replace( "/\(.+?\)/", "", $name_replace);
                $name_replace = preg_replace( "/[0-9]{2}㎝/", "", $name_replace);
                $name_replace = preg_replace( "/[0-9]{2}cm/", "", $name_replace);
                $name_replace = preg_replace( "/[0-9]{2}ｃｍ/", "", $name_replace);
                $name_replace = preg_replace( "/[0-9]{2}入/", "", $name_replace);
                $name_replace = preg_replace( "/[0-9]{1}-[0-9]{1}F/", "", $name_replace);
                $name_replace = preg_replace( "/[0-9]{1}\/[0-9]{1}F/", "", $name_replace);
                $name_replace = preg_replace( "/S[0-9]{2}/", "", $name_replace);
                $name_replace = preg_replace( "/M[0-9]{2}/", "", $name_replace);
                $name_replace = preg_replace( "/L[0-9]{2}/", "", $name_replace);
                $name_replace = preg_replace( "/優[0-9]{2}/", "", $name_replace);
                $name_replace = preg_replace( "/秀[0-9]{2}/", "", $name_replace);
                $name_replace = preg_replace( "/(・)+$/", "", $name_replace);
                $name_replace = str_replace(" ", "", $name_replace);
                $name_replace = str_replace("　", "", $name_replace);
                $name_replace = str_replace("～", "", $name_replace);
                $name_replace = str_replace("×1", "", $name_replace);
                $name_replace = str_replace("× 1", "", $name_replace);
                $name_replace = str_replace("以上", "", $name_replace);
                $name_replace = str_replace("L/2L", "", $name_replace);
                $name_replace = str_replace("L/２L", "", $name_replace);
                $name_replace = str_replace("Ｌ/２Ｌ", "", $name_replace);
                $name_replace = str_replace("L2L", "", $name_replace);
                $name_replace = str_replace("MIX", "", $name_replace);
                $name_replace = str_replace("ＭＩＸ", "", $name_replace);
                $name_replace = str_replace("Mix", "", $name_replace);
                $name_replace = str_replace("mix", "", $name_replace);
                $name_replace = str_replace("FLC級", "", $name_replace);
                $name_replace = str_replace("ＦＬＣ級", "", $name_replace);
                $name_replace = str_replace("定期", "", $name_replace);
                $name_replace = str_replace("中国", "", $name_replace);
                $name_replace = str_replace("前倒し", "", $name_replace);
                $name_replace = str_replace("在庫", "", $name_replace);
                $name_replace = str_replace("お任せ", "", $name_replace);
                $name_replace = str_replace("代品", "", $name_replace);
                $name_replace = str_replace("不足", "", $name_replace);
                $price = $sheet->getCell("I${i}")->getOldCalculatedValue();
                if ($tue >= 1 || $wed >= 1) {
                    $monday = 1;
                } else {
                    $monday = 0;
                }
                if ($thu >= 1 || $fri >= 1) {
                    $wednesday = 1;
                } else {
                    $wednesday = 0;
                }
                if ($sat >= 1 || $sun >= 1 || $mon >= 1) {
                    $friday = 1;
                } else {
                    $friday = 0;
                }
                $stmt = $mysqli->prepare("INSERT INTO ${period} (name, price, mon, wed, fri) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param('siiii', $name_replace, $price, $monday, $wednesday, $friday);
                $stmt->execute();
            }
        }
        echo "<p>総合計.xlsのファイルのアップロードが成功しました。</p>
        <a href='result.php'>戻る</a>";
    } else {
        echo "<p>総合計.xlsのファイルしかアップロードできません。</p>
        <a href='result.php'>戻る</a>";
    }
}
?>
