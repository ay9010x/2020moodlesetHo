<?php




define("RECAPTCHA_API_SERVER", "http://www.google.com/recaptcha/api");
define("RECAPTCHA_API_SECURE_SERVER", "https://www.google.com/recaptcha/api");
define("RECAPTCHA_VERIFY_SERVER", "www.google.com");


function _recaptcha_qsencode ($data) {
        $req = "";
        foreach ( $data as $key => $value )
                $req .= $key . '=' . urlencode( $value ) . '&';

                $req=substr($req,0,strlen($req)-1);
        return $req;
}




function _recaptcha_http_post($host, $path, $data, $port = 80, $https=false) {
        global $CFG;
        $protocol = 'http';
        if ($https) {
            $protocol = 'https';
        }

        require_once $CFG->libdir . '/filelib.php';

        $req = _recaptcha_qsencode ($data);

        $headers = array();
        $headers['Host'] = $host;
        $headers['Content-Type'] = 'application/x-www-form-urlencoded';
        $headers['Content-Length'] = strlen($req);
        $headers['User-Agent'] = 'reCAPTCHA/PHP';

        $results = download_file_content("$protocol://" . $host . $path, $headers, $data, false, 300, 20, true);

        if ($results) {
            return array(1 => $results);
        } else {
            return false;
        }
}




function recaptcha_get_html ($pubkey, $error = null, $use_ssl = false) {
    global $CFG, $PAGE;

    $recaptchatype = optional_param('recaptcha', 'image', PARAM_TEXT);

    if ($pubkey == null || $pubkey == '') {
		die ("To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>");
    }

    if ($use_ssl) {
        $server = RECAPTCHA_API_SECURE_SERVER;
    } else {
        $server = RECAPTCHA_API_SERVER;
    }

    $errorpart = "";
    if ($error) {
       $errorpart = "&amp;error=" . $error;
    }

    require_once $CFG->libdir . '/filelib.php';
    $html = download_file_content($server . '/noscript?k=' . $pubkey . $errorpart, null, null, false, 300, 20, true);
    preg_match('/image\?c\=([A-Za-z0-9\-\_]*)\"/', $html, $matches);
    $challenge_hash = $matches[1];
    $image_url = $server . '/image?c=' . $challenge_hash;

    $strincorrectpleasetryagain = get_string('incorrectpleasetryagain', 'auth');
    $strenterthewordsabove = get_string('enterthewordsabove', 'auth');
    $strenterthenumbersyouhear = get_string('enterthenumbersyouhear', 'auth');
    $strgetanothercaptcha = get_string('getanothercaptcha', 'auth');
    $strgetanaudiocaptcha = get_string('getanaudiocaptcha', 'auth');
    $strgetanimagecaptcha = get_string('getanimagecaptcha', 'auth');

    $return = html_writer::script('', $server . '/challenge?k=' . $pubkey . $errorpart);
    $return .= '<noscript>
        <div id="recaptcha_widget_noscript">
        <div id="recaptcha_image_noscript"><img src="' . $image_url . '" alt="reCAPTCHA"/></div>';

    if ($error == 'incorrect-captcha-sol') {
        $return .= '<div class="recaptcha_only_if_incorrect_sol" style="color:red">' . $strincorrectpleasetryagain . '</div>';
    }

    if ($recaptchatype == 'image') {
        $return .= '<span class="recaptcha_only_if_image">' . $strenterthewordsabove . '</span>';
    } elseif ($recaptchatype == 'audio') {
        $return .= '<span class="recaptcha_only_if_audio">' . $strenterthenumbersyouhear . '</span>'; 
    }
    
    $return .= '<input type="text" id="recaptcha_response_field_noscript" name="recaptcha_response_field" />';
    $return .= '<input type="hidden" id="recaptcha_challenge_field_noscript" name="recaptcha_challenge_field" value="' . $challenge_hash . '" />';
    $return .= '<div><a href="signup.php">' . $strgetanothercaptcha . '</a></div>';
    
        

    $return .= '
        </div>
    </noscript>';

    return $return;
}





class ReCaptchaResponse {
        var $is_valid;
        var $error;
}



function recaptcha_check_answer ($privkey, $remoteip, $challenge, $response, $https=false)
{
    if ($privkey == null || $privkey == '') {
		die ("To use reCAPTCHA you must get an API key from <a href='https://www.google.com/recaptcha/admin/create'>https://www.google.com/recaptcha/admin/create</a>");
    }

    if ($remoteip == null || $remoteip == '') {
        die ("For security reasons, you must pass the remote ip to reCAPTCHA");
    }

                if ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0) {
                $recaptcha_response = new ReCaptchaResponse();
                $recaptcha_response->is_valid = false;
                $recaptcha_response->error = 'incorrect-captcha-sol';
                return $recaptcha_response;
        }

        $response = _recaptcha_http_post(RECAPTCHA_VERIFY_SERVER, "/recaptcha/api/verify",
                                         array (
                                                'privatekey' => $privkey,
                                                'remoteip' => $remoteip,
                                                'challenge' => $challenge,
                                                'response' => $response
                                                ),
                                         $https        
                                        );

        $answers = explode ("\n", $response [1]);
        $recaptcha_response = new ReCaptchaResponse();

        if (trim ($answers [0]) == 'true') {
                $recaptcha_response->is_valid = true;
        }
        else {
                $recaptcha_response->is_valid = false;
                $recaptcha_response->error = $answers [1];
        }
        return $recaptcha_response;

}


function recaptcha_get_signup_url ($domain = null, $appname = null) {
	return "https://www.google.com/recaptcha/admin/create?" .  _recaptcha_qsencode (array ('domains' => $domain, 'app' => $appname));
}

function _recaptcha_aes_pad($val) {
    $block_size = 16;
    $numpad = $block_size - (strlen ($val) % $block_size);
    return str_pad($val, strlen ($val) + $numpad, chr($numpad));
}



function _recaptcha_aes_encrypt($val,$ky) {
    if (! function_exists ("mcrypt_encrypt")) {
        die ("To use reCAPTCHA Mailhide, you need to have the mcrypt php module installed.");
    }
    $mode=MCRYPT_MODE_CBC;
    $enc=MCRYPT_RIJNDAEL_128;
    $val=_recaptcha_aes_pad($val);
    return mcrypt_encrypt($enc, $ky, $val, $mode, "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0");
}


function _recaptcha_mailhide_urlbase64 ($x) {
    return strtr(base64_encode ($x), '+/', '-_');
}


function recaptcha_mailhide_url($pubkey, $privkey, $email) {
    if ($pubkey == '' || $pubkey == null || $privkey == "" || $privkey == null) {
        die ("To use reCAPTCHA Mailhide, you have to sign up for a public and private key, " .
		     "you can do so at <a href='http://www.google.com/recaptcha/mailhide/apikey'>http://www.google.com/recaptcha/mailhide/apikey</a>");
    }


    $ky = pack('H*', $privkey);
    $cryptmail = _recaptcha_aes_encrypt ($email, $ky);

	return "http://www.google.com/recaptcha/mailhide/d?k=" . $pubkey . "&c=" . _recaptcha_mailhide_urlbase64 ($cryptmail);
}


function _recaptcha_mailhide_email_parts ($email) {
    $arr = preg_split("/@/", $email );

    if (strlen ($arr[0]) <= 4) {
        $arr[0] = substr ($arr[0], 0, 1);
    } else if (strlen ($arr[0]) <= 6) {
        $arr[0] = substr ($arr[0], 0, 3);
    } else {
        $arr[0] = substr ($arr[0], 0, 4);
    }
    return $arr;
}


function recaptcha_mailhide_html($pubkey, $privkey, $email) {
    $emailparts = _recaptcha_mailhide_email_parts ($email);
    $url = recaptcha_mailhide_url ($pubkey, $privkey, $email);

    return htmlentities($emailparts[0]) . "<a href='" . htmlentities ($url) .
        "' onclick=\"window.open('" . htmlentities ($url) . "', '', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=500,height=300'); return false;\" title=\"Reveal this e-mail address\">...</a>@" . htmlentities ($emailparts [1]);

}
