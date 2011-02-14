<?php
$ch = curl_init ("http://".$_SERVER['HTTP_HOST']."/retrieve.php?shorturl=".$_SERVER['HTTP_REFERER']);
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, TRUE);
$result = json_decode (curl_exec ($ch));
curl_close ($ch);
if ($result->code == 0) {
    header ("Location: ".$result->result);
    die();
} else {
    echo $result->error;
}
?>
