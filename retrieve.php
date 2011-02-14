<?php
require_once 'lib.php';

$ret = array();

if (!(isset ($_GET['shorturl']))) {
    $ret["code"] = -1;
    $ret["error"] = "No short URL entered";
} else {
    if ($mysql = mysql_connect (DB_HOST, DB_USER, DB_PASS)) {
        if (mysql_select_db (DB_BASE, $mysql)) {
            if ($result = mysql_query ("SELECT * FROM `urls` WHERE `index`='".hash_to_int (hash_from_shorturl ($_GET['shorturl']))."'")) {
                if ($row =  mysql_fetch_array ($result)) {
                    if (mysql_query ("INSERT INTO `stats` (`urlid`, `ip`) VALUES ('".$row['index']."', '".get_ip()."')", $mysql)) {
                        $ret["code"] = 0;
                        $ret["result"] = $row["url"];
                    } else {
                        $ret["code"] = -8;
                        $ret["error"] = "Cannot insert stat into database";
                        $ret["mysql_error"] = mysql_error();
                        $ret["result"] = $row["url"];
                    }
                } else {
                    $ret["code"] = -9;
                    $ret["error"] = "The URL cannot be found";
                }
            } else {
                $ret["code"] = -6;
                $ret["error"] = "Cannot query the database for a stored URL";
                $ret["mysql_error"] = mysql_error();
            }
        } else {
            $ret["code"] = -5;
            $ret["error"] = "Cannot select database";
            $ret["mysql_error"] = mysql_error();
        }
    } else {
        $ret["code"] = -4;
        $ret["error"] = "Cannot connect to database";
        $ret["mysql_error"] = mysql_error();
    }
}

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo json_encode ($ret);
?>
