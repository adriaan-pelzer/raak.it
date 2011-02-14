<?php
require_once 'lib.php';

$ret = array();

if (!(isset ($_GET['url']))) {
    $ret["code"] = -1;
    $ret["error"] = "No URL entered";
} else {
    $result = validateurl (urldecode ($_GET['url']));
    if ($result["code"] != 200) {
        if ($result["code"] < 0) {
            $ret["code"] = -2;
            $ret["error"] = $result["error"];
        } else {
            $ret["code"] = -3;
            $ret["error"] = "A URL was entered, but it returned HTTP error ".$result["code"];
            $ret["http_code"] = $result["code"];
        }
    } else {
        if (!($mysql = mysql_connect (DB_HOST, DB_USER, DB_PASS))) {
            $ret["code"] = -4;
            $ret["error"] = "Cannot connect to database";
            $ret["mysql_error"] = mysql_error();
        } else {
            if (!(mysql_select_db (DB_BASE, $mysql))) {
                $ret["code"] = -5;
                $ret["error"] = "Cannot select database";
                $ret["mysql_error"] = mysql_error();
            } else {
                if ($res = mysql_query ("SELECT * FROM `urls` WHERE `url`='".$_GET['url']."'", $mysql)) {
                    if (mysql_num_rows ($res) > 0) {
                        if ($row = mysql_fetch_array ($res)) {
                            if ($row['title'] != $result["title"]) {
                                if (!(mysql_query ("UPDATE `urls` SET `title`='".$result["title"]."' WHERE `index`='".$row['index']."'"))) {
                                    $ret["code"] = -9;
                                    $ret["error"] = "Cannot update url title";
                                    $ret["mysql_error"] = mysql_error();
                                }
                            }
                            $string = int_to_hash ($row['index']);
                            $ret["code"] = 0;
                            $ret["result"] = "http://".$_SERVER["HTTP_HOST"]."/".$string;
                        } else {
                            $ret["code"] = -7;
                            $ret["error"] = "Cannot fetch previously stored URL";
                            $ret["mysql_error"] = mysql_error();
                        }
                    } else {
                        if (mysql_query ("INSERT INTO `urls` (`url`, `title`) VALUES ('".$_GET['url']."', '".addslashes ($result["title"])."')", $mysql)) {
                            $string = int_to_hash (mysql_insert_id ($mysql));
                            $ret["code"] = 0;
                            $ret["result"] = "http://".$_SERVER["HTTP_HOST"]."/".$string;
                        } else {
                            $ret["code"] = -8;
                            $ret["error"] = "Cannot insert URL into database";
                            $ret["mysql_error"] = mysql_error();
                        }
                    }
                } else {
                    $ret["code"] = -6;
                    $ret["error"] = "Cannot query the database for a previously stored URL";
                    $ret["mysql_error"] = mysql_error();
                }

            }
        }
    }
}

if ($_GET['format'] == 'txt') {
    echo $ret['result'];
} else {
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Content-type: application/json');
    echo json_encode ($ret);
}
?>
