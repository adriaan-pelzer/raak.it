<?php
require_once 'lib.php';

$ret = array();

if ($mysql = mysql_connect (DB_HOST, DB_USER, DB_PASS)) {
    if (!(mysql_select_db (DB_BASE, $mysql))) {
        $ret["code"] = -5;
        $ret["error"] = "Cannot select database";
        $ret["mysql_error"] = mysql_error();
    }
} else {
    $ret["code"] = -4;
    $ret["error"] = "Cannot connect to database";
    $ret["mysql_error"] = mysql_error();
}

if (!(isset ($_GET['shorturl'])) && !(isset ($_GET['url']))) {
    $ret["code"] = 0;
    $ret["urls"] = array();

    if ($result = mysql_query ("SELECT * FROM `stats` ORDER BY `urlid`", $mysql)) {
        while ($row = mysql_fetch_array ($result)) {
            $url_hash = int_to_hash ($row["urlid"]);
            //echo "Adding initial: ".$url_hash."<br />\n";

            if (isset ($ret["urls"][$url_hash])) {
                $ret["urls"][$url_hash]["count"]++;
            } else {
                $ret["urls"][$url_hash] = array();
                $ret["urls"][$url_hash]["count"] = 1;
            }
        }
    }

    if ($result = mysql_query ("SELECT * FROM `urls`", $mysql)) {
        while ($row = mysql_fetch_array ($result)) {
            $url_hash = int_to_hash ($row["index"]);
            //echo "Adding extras: ".$url_hash."<br />\n";

            $ret["urls"][$url_hash]["title"] = $row["title"];
            $ret["urls"][$url_hash]["url"] = $row["url"];
        }
    }
} else {
    if (isset ($_GET['shorturl'])) {
        if ($result = mysql_query ("SELECT * FROM `urls` WHERE `index`='".hash_to_int( hash_from_shorturl ($_GET['shorturl']))."'", $mysql)) {
            if ($row =  mysql_fetch_array ($result)) {
                $urlid = $row["index"];

                $ret["code"] = 0;
                $ret["urls"] = array();
                $ret["urls"][int_to_hash ($row["urlid"])]["title"] = $row["title"];
                $ret["urls"][int_to_hash ($row["urlid"])]["url"] = $row["url"];

                if ($result = mysql_query ("SELECT * FROM `stats` WHERE `urlid`='".$urlid."'", $mysql)) {
                    while ($row = mysql_fetch_array ($result)) {
                        if (isset ($ret["urls"][int_to_hash ($row["urlid"])])) {
                            $ret["urls"][int_to_hash ($row["urlid"])]["count"]++;
                            array_push ($ret["urls"][int_to_hash ($row["urlid"])]["ips"], $row["ip"]);
                        } else {
                            $ret["urls"][int_to_hash ($row["urlid"])] = array();
                            $ret["urls"][int_to_hash ($row["urlid"])]["ips"] = array();
                            $ret["urls"][int_to_hash ($row["urlid"])]["count"] = 1;
                            array_push ($ret["urls"][int_to_hash ($row["urlid"])]["ips"], $row["ip"]);
                        }
                    }
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
        if ($result = mysql_query ("SELECT * FROM `urls` WHERE `url`='".$_GET['url']."'", $mysql)) {
            if ($row =  mysql_fetch_array ($result)) {
                $urlid = $row['index'];

                $ret["code"] = 0;
                $ret["urls"] = array();
                $ret["urls"][int_to_hash ($row["urlid"])]["title"] = $row["title"];
                $ret["urls"][int_to_hash ($row["urlid"])]["url"] = $row["url"];

                if ($result = mysql_query ("SELECT * FROM `stats` WHERE `urlid`='".$urlid."'", $mysql)) {
                    while ($row = mysql_fetch_array ($result)) {
                        if (isset ($ret["urls"][int_to_hash ($row["urlid"])])) {
                            $ret["urls"][int_to_hash ($row["urlid"])]["count"]++;
                            array_push ($ret["urls"][int_to_hash ($row["urlid"])]["ips"], $row["ip"]);
                        } else {
                            $ret["urls"][int_to_hash ($row["urlid"])] = array();
                            $ret["urls"][int_to_hash ($row["urlid"])]["ips"] = array();
                            $ret["urls"][int_to_hash ($row["urlid"])]["count"] = 1;
                            array_push ($ret["urls"][int_to_hash ($row["urlid"])]["ips"], $row["ip"]);
                        }
                    }
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
    }
}

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
echo json_encode ($ret);
?>
