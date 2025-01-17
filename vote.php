<?php

ini_set('display_errors', "On");
date_default_timezone_set('Asia/Tokyo');
session_start();

$mysqli = new mysqli('localhost', 'root', 'password', 'sell_die');
$mysqli->set_charset("utf8");

?>
<html lang="ja">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <link rel="shortcut icon" href="flower.ico">
        <title>売れ筋死に筋</title>
        <script type="text/javascript" src="/jquery-3.3.1.min.js"></script>
    </head>
    <style>
        input {
            font-size: 100%;
        }

        input[type=checkbox] {
            transform: scale(1.5);
        }

        .flex {
            display: flex;
        }

        .padding-top-16 {
            padding-top: 16px;
        }

        .padding-bottom-16 {
            padding-bottom: 16px;
        }

        .text-align-center {
            text-align: center;
        }

        @media screen and (max-width: 767px) {
            .none-767 {
                display: none;
            }
        }

    </style>
    <body>
        <?php
        if (isset($_GET["team"])) {
            $result_show = $mysqli->query("SHOW TABLES FROM sell_die");
            while ($row = $result_show->fetch_row()) {
                $today = date("Y/m/d" ,strtotime("-1 day"));
                $date = explode("_", $row[0]);
                $mon_day = str_split($date[0], 2);
                $start = date("Y")."/$mon_day[0]/$mon_day[1]";
                $mon_day = str_split($date[1], 2);
                $end = date("Y")."/$mon_day[0]/$mon_day[1]";
                if (strtotime($today) >= strtotime($start) && strtotime($end) >= strtotime($today)) {
                    $table = $row[0];
                    echo "<form method='post' action='post.php?team=$_GET[team]'>";
                        $result = $mysqli->query("SELECT id, name, price FROM $row[0] ORDER BY name;");
                        while ($row = $result->fetch_assoc()) {
                            echo "<p>$row[name]&nbsp;$row[price]円<br><input type='checkbox' name='sell[]' value='$row[id]'>&nbsp;&nbsp;&nbsp;売れ&nbsp;&nbsp;&nbsp;<input type='checkbox' name='die[]' value='$row[id]'>&nbsp;&nbsp;&nbsp;死に&nbsp;&nbsp;&nbsp;</p>";
                        }
                        echo "<div class='flex'>
                            <div>
                                <input type='submit' value='送信'>
                            </div>
                            <div class='none-767'>
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            </div>
                            <div class='none-767'>
                                <input type='button' value='結果' onclick='location.href=\"result.php?table=${table}\"'>
                            </div>
                        </div>
                    </form>
                    <br>
                    <p>アイテム追加</p>
                    <form action='post.php?team=$_GET[team]' method='post'>
                        <div>
                            <p>
                                <label for='name'>商品名：</label>
                                <input type='text' name='name' required>
                            </p>
                        </div>
                        <div>
                            <p>
                                <label for='price'>価格　：</label>
                                <input type='number' name='price' required>
                            </p>
                        </div>
                        <input type='submit' value='送信する'>
                    </form>";
                }
            }
        } else {
            echo "<div class='text-align-center'>
                <div class='padding-top-16 padding-bottom-16'>
                    <select>
                        <option hidden>選択してください</option>";
                        $team = [10, 20, 30, 40, 50, 60, 70];
                        foreach ($team as $value) {
                            echo "<option value='${value}'>${value}</option>";
                        }
                    echo "</select>
                </div>
            </div>";
        }
        ?>
        <script>
            (function($) {

                $(function() {
                    $('select').change(function() {
                        var val = $('option:selected').val();
                        location.href = 'http://34.193.237.19/vote.php?team=' + val;
                    });
                });

            })(jQuery);
        </script>
    </body>
</html>
