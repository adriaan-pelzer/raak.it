<?php
include "header.php";

function cmp ($a, $b) {
    if ($a["count"] == $b["count"]) {
        return 0;
    }
    return ($a["count"] > $b["count"]) ? -1 : 1;
}

if (isset ($_POST['shorten'])) {
    if (!(isset ($_POST['url_in']))) {
        $error = "Please enter a URL to shorten";
    } else {
        $ch = curl_init ('http://raak.it/register.php?url='.urlencode ($_POST['url_in']));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $result_array = curl_exec ($ch);
        curl_close ($ch);
        $result = json_decode ($result_array);
        if ($result->code != 0) {
            $error = $result->error;
        } else {
            $short_url = $result->result;
        }
    }

    if (isset ($error)) {
?>
        <section id="content">
            <p>
                <div id="label">Cannot shorten your url:</div>
                <div id="error"><?php echo $error; ?></div>
            </p>
            <p>
                <span><a href="index.php">Try Again</a></span>
            </p>
        </section>
<?php
    } else {
?>
        <section id="content">
            <p>
                <span id="label">Your shortened url:</span>
                <span id="url" class="error"><mark><a href="<?php echo $short_url; ?>"><?php echo $short_url; ?></a></mark></span>
            </p>
            <p>
                <span><a href="index.php">Shorten Another</a></span>
            </p>
        </section>
<?php
    }
} else {
?>
        <section id="content">
            <form action="index.php" method="post" name="shorten">
                <label for="url_in">Please Enter URL to shorten</label>
                <input type="text" name="url_in" placeholder="url to be shortened"  required id="text" />
                <span id="button_container">
                    Shorten
                    <input name="shorten" type="submit" value="Shorten" id ="button" />
                </span>
            </form>
        </section>
<?php
}

$stats = array();

$ch = curl_init ("http://raak.it/stats.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$result_json = curl_exec ($ch);
$result = json_decode ($result_json, TRUE);
if ($result["code"] != 0) {
    $stats_error = $result["error"];
} else {
    $stats = $result["urls"];
}
uasort ($stats, 'cmp');
?>
        <section id="top5">
        <h2>Top 5 links</h2>
<?php
$i = 0;

foreach ($stats as $key=>$stat) {
?>
    <div class="stats"><a href="http://raak.it/<?php echo $key; ?>"><?php echo $stat["title"]; ?></a></div>
<?php
    $i++;

    if ($i > 4) {
        break;
    } else {
?>
<?php
    }
}
?>
        </section>
<?php
include "footer.php";
?>
