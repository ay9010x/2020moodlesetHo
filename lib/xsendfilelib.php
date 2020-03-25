<?php





function xsendfile($filepath) {
    global $CFG;

    if (empty($CFG->xsendfile)) {
        return false;
    }

    if (!file_exists($filepath)) {
        return false;
    }

    if (headers_sent()) {
        return false;
    }

    $filepath = realpath($filepath);

    $aliased = false;
    if (!empty($CFG->xsendfilealiases) and is_array($CFG->xsendfilealiases)) {
        foreach ($CFG->xsendfilealiases as $alias=>$dir) {
            $dir = realpath($dir);
            if ($dir === false) {
                continue;
            }
            if (substr($dir, -1) !== DIRECTORY_SEPARATOR) {
                                $dir .= DIRECTORY_SEPARATOR;
            }
            if (strpos($filepath, $dir) === 0) {
                $filepath = $alias.substr($filepath, strlen($dir));
                $aliased = true;
                break;
            }
        }
    }

    if ($CFG->xsendfile === 'X-LIGHTTPD-send-file') {
                header('Accept-Ranges: none');

    } else if ($CFG->xsendfile === 'X-Accel-Redirect') {
                        if (!$aliased) {
            return false;
        }
    }

    header("$CFG->xsendfile: $filepath");

    return true;
}
