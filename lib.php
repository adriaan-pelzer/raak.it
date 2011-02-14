<?php

define ('DB_USER', 'raakit_tikaar');
define ('DB_PASS', 't1k44r');
define ('DB_HOST', 'localhost');
define ('DB_BASE', 'raakit_tikaar');

function get_ip () {
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
      $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}

function hash_from_shorturl ($shorturl) {
    return str_replace ("http://", "", str_replace (str_replace ("www.", "", $_SERVER['HTTP_HOST'])."/", "", $shorturl));
}

function int_to_hash ($int) {
    $chars = array ('0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
    $d6 = (int) $int/(62*62*62*62*62*62);
    $d5 = (int) ($int%(62*62*62*62*62*62))/(62*62*62*62*62);
    $d4 = (int) (($int%(62*62*62*62*62*62))%(62*62*62*62*62))/(62*62*62*62);
    $d3 = (int) ((($int%(62*62*62*62*62*62))%(62*62*62*62*62))%(62*62*62*62))/(62*62*62);
    $d2 = (int) (((($int%(62*62*62*62*62*62))%(62*62*62*62*62))%(62*62*62*62))%(62*62*62))/(62*62);
    $d1 = (int) ((((($int%(62*62*62*62*62*62))%(62*62*62*62*62))%(62*62*62*62))%(62*62*62))%(62*62))/62;
    $d0 = (int) ((((($int%(62*62*62*62*62*62))%(62*62*62*62*62))%(62*62*62*62))%(62*62*62))%(62*62))%62;

    $rc = $chars[$d6].$chars[$d5].$chars[$d4].$chars[$d3].$chars[$d2].$chars[$d1].$chars[$d0];
    $rc = preg_replace ("/^0*/", "", $rc);
    return $rc;
}

function hash_to_int ($hash) {
    $chars = array ('0'=>0,'1'=>1,'2'=>2,'3'=>3,'4'=>4,'5'=>5,'6'=>6,'7'=>7,'8'=>8,'9'=>9,'a'=>10,'b'=>11,'c'=>12,'d'=>13,'e'=>14,'f'=>15,'g'=>16,'h'=>17,'i'=>18,'j'=>19,'k'=>20,'l'=>21,'m'=>22,'n'=>23,'o'=>24,'p'=>25,'q'=>26,'r'=>27,'s'=>28,'t'=>29,'u'=>30,'v'=>31,'w'=>32,'x'=>33,'y'=>34,'z'=>35,'A'=>36,'B'=>37,'C'=>38,'D'=>39,'E'=>40,'F'=>41,'G'=>42,'H'=>43,'I'=>44,'J'=>45,'K'=>46,'L'=>47,'M'=>48,'N'=>49,'O'=>50,'P'=>51,'Q'=>52,'R'=>53,'S'=>54,'T'=>55,'U'=>56,'V'=>57,'W'=>58,'X'=>59,'Y'=>60,'Z'=>61);

    $ret = 0;

    //echo "hash: ".$hash."<br />";
    for ($i = 0; $i < strlen ($hash); $i++) {
        //echo "i: ".$i."<br />";
        $sub = 1;
        for ($j = 0; $j < $i; $j++) {
            //echo "j: ".$j."<br />";
            $sub = $sub * 62;
            //echo "sub: ".$sub."<br />";
        }
        $ret += $chars[$hash[strlen($hash)-$i-1]] * $sub;
        //echo "ret: ".$ret."<br />";
    }

    return $ret;
}

function validateurl($url, $testscheme=true) {
    // SCHEME
    $urlregex = "^(https?|ftp)\:\/\/";

    if (!(eregi($urlregex, $url))) {
        if ($testscheme) {
            return array ("code"=>-1, "title"=>NULL, "error"=>"This is not a valid URL<br />The <em>http://</em>, <em>https://</em> OR <em>ftp://</em> bit is missing");
        } else {
            $url = $url;
        }
    }

    // USER AND PASS (optional)
    $urlregex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?";

    // HOSTNAME OR IP
    $urlregex .= "[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)*"; // http://x = allowed (ex. http://localhost, http://routerlogin)
    //$urlregex .= "[a-z0-9+\$_-]+(\.[a-z0-9+\$_-]+)+"; // http://x.x = minimum
    //$urlregex .= "([a-z0-9+\$_-]+\.)*[a-z0-9+\$_-]{2,3}"; // http://x.xx(x) = minimum
    //use only one of the above

    if (!(eregi($urlregex, $url))) {
        return array ("code"=>-2, "title"=>NULL, "error"=>"This is not a valid URL<br />There are illegal characters<br />(or no characters at all)<br />after the <em>http://</em>, <em>https://</em> OR <em>ftp://</em> bit");
    }

    // PORT (optional)
    $urlregex .= "(\:[0-9]{2,5})?";
    // PATH (optional)
    $urlregex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?";
    // GET Query (optional)
    $urlregex .= "(\?[a-z+&\$_.-][a-z0-9;:@/&%=+\$_.-]*)?";
    // ANCHOR (optional)
    $urlregex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?";

    if (!(eregi($urlregex, $url))) {
        return array ("code"=>-3, "title"=>NULL, "error"=>"This is not a valid URL<br />There are illegal characters after the <em>http://</em>, <em>https://</em> OR <em>ftp://</em> bit");
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, true);
    //curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    /* WARNING! This is site-specific code - remove */
    if (!($data) || ($data == "") || (preg_match ("/URL=\/cgi-sys\/defaultwebpage\.cgi/", $data))) {
        return array ("code"=>-4, "title"=>NULL, "error"=>"There's nothing at that URL");
    }
    /* -------------------------------------------- */
    curl_close($ch);
    preg_match("/HTTP\/1\.[1|0]\s(\d{3})/",$data,$matches);
    $code = $matches[1];
    if (!(preg_match("/<[Tt][Ii][Tt][Ll][Ee]>(.*)<\/[Tt][Ii][Tt][Ll][Ee]>/", $data, $matches))) {
        $title = NULL;
    } else {
        $title = $matches[1];
    }
    if (($code >= 300) && ($code < 400)) {
        if (preg_match("/Location: ([^ ]+) /", $data,$matches)) {
            $location = $matches[1];
            if (!(eregi ("^(https?|ftp)\:\/\/", $location))) {
                preg_match ("/^((https?|ftp)\:\/\/[^\/]+)/", $url, $urlmatches);
                $location = $urlmatches[0]."/".$location;
            }
            return validateurl($location, false);
        } else {
            return array("code"=>$code, "title"=>$title);
        }
    } else {
        return array("code"=>$code, "title"=>$title);
    }
}

?>
