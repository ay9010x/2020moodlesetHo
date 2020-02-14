<?php


class HTMLPurifier_URIScheme_data extends HTMLPurifier_URIScheme
{
    
    public $browsable = true;

    
    public $allowed_types = array(
                        'image/jpeg' => true,
        'image/gif' => true,
        'image/png' => true,
    );
            
    public $may_omit_host = true;

    
    public function doValidate(&$uri, $config, $context)
    {
        $result = explode(',', $uri->path, 2);
        $is_base64 = false;
        $charset = null;
        $content_type = null;
        if (count($result) == 2) {
            list($metadata, $data) = $result;
                        $metas = explode(';', $metadata);
            while (!empty($metas)) {
                $cur = array_shift($metas);
                if ($cur == 'base64') {
                    $is_base64 = true;
                    break;
                }
                if (substr($cur, 0, 8) == 'charset=') {
                                                            if ($charset !== null) {
                        continue;
                    }                     $charset = substr($cur, 8);                 } else {
                    if ($content_type !== null) {
                        continue;
                    }                     $content_type = $cur;
                }
            }
        } else {
            $data = $result[0];
        }
        if ($content_type !== null && empty($this->allowed_types[$content_type])) {
            return false;
        }
        if ($charset !== null) {
                        $charset = null;
        }
        $data = rawurldecode($data);
        if ($is_base64) {
            $raw_data = base64_decode($data);
        } else {
            $raw_data = $data;
        }
                        $file = tempnam("/tmp", "");
        file_put_contents($file, $raw_data);
        if (function_exists('exif_imagetype')) {
            $image_code = exif_imagetype($file);
            unlink($file);
        } elseif (function_exists('getimagesize')) {
            set_error_handler(array($this, 'muteErrorHandler'));
            $info = getimagesize($file);
            restore_error_handler();
            unlink($file);
            if ($info == false) {
                return false;
            }
            $image_code = $info[2];
        } else {
            trigger_error("could not find exif_imagetype or getimagesize functions", E_USER_ERROR);
        }
        $real_content_type = image_type_to_mime_type($image_code);
        if ($real_content_type != $content_type) {
                                    if (empty($this->allowed_types[$real_content_type])) {
                return false;
            }
            $content_type = $real_content_type;
        }
                $uri->userinfo = null;
        $uri->host = null;
        $uri->port = null;
        $uri->fragment = null;
        $uri->query = null;
        $uri->path = "$content_type;base64," . base64_encode($raw_data);
        return true;
    }

    
    public function muteErrorHandler($errno, $errstr)
    {
    }
}
