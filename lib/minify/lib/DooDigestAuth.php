<?php



class DooDigestAuth{

    
    public static function http_auth($realm, $users, $fail_msg=NULL, $fail_url=NULL){
        $realm = "Restricted area - $realm";

                        if(!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) && strpos($_SERVER['REDIRECT_HTTP_AUTHORIZATION'], 'Digest')===0){
            $_SERVER['PHP_AUTH_DIGEST'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        if (empty($_SERVER['PHP_AUTH_DIGEST'])) {
            header('WWW-Authenticate: Digest realm="'.$realm.
                   '",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');
            header('HTTP/1.1 401 Unauthorized');
            if($fail_msg!=NULL)
                die($fail_msg);
            if($fail_url!=NULL)
                die("<script>window.location.href = '$fail_url'</script>");
            exit;
        }

                if (!($data = self::http_digest_parse($_SERVER['PHP_AUTH_DIGEST'])) || !isset($users[$data['username']])){
            header('WWW-Authenticate: Digest realm="'.$realm.
                   '",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');
            header('HTTP/1.1 401 Unauthorized');
            if($fail_msg!=NULL)
                die($fail_msg);
            if($fail_url!=NULL)
                die("<script>window.location.href = '$fail_url'</script>");
            exit;
        }

                $A1 = md5($data['username'] . ':' . $realm . ':' . $users[$data['username']]);
        $A2 = md5($_SERVER['REQUEST_METHOD'].':'.$data['uri']);
        $valid_response = md5($A1.':'.$data['nonce'].':'.$data['nc'].':'.$data['cnonce'].':'.$data['qop'].':'.$A2);

        if ($data['response'] != $valid_response){
            header('HTTP/1.1 401 Unauthorized');
            header('WWW-Authenticate: Digest realm="'.$realm.
                   '",qop="auth",nonce="'.uniqid().'",opaque="'.md5($realm).'"');
            if($fail_msg!=NULL)
                die($fail_msg);
            if($fail_url!=NULL)
                die("<script>window.location.href = '$fail_url'</script>");
            exit;
        }

                return $data['username'];
    }

    
    private static function http_digest_parse($txt)
    {
        $res = preg_match("/username=\"([^\"]+)\"/i", $txt, $match);
        $data['username'] = (isset($match[1]))?$match[1]:null;
        $res = preg_match('/nonce=\"([^\"]+)\"/i', $txt, $match);
        $data['nonce'] = $match[1];
        $res = preg_match('/nc=([0-9]+)/i', $txt, $match);
        $data['nc'] = $match[1];
        $res = preg_match('/cnonce=\"([^\"]+)\"/i', $txt, $match);
        $data['cnonce'] = $match[1];
        $res = preg_match('/qop=([^,]+)/i', $txt, $match);
        $data['qop'] = str_replace('"','',$match[1]);
        $res = preg_match('/uri=\"([^\"]+)\"/i', $txt, $match);
        $data['uri'] = $match[1];
        $res = preg_match('/response=\"([^\"]+)\"/i', $txt, $match);
        $data['response'] = $match[1];
        return $data;
    }


}
