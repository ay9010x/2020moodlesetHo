<?php




define('TESTING_EXITCODE_COMPOSER', 255);


function testing_cli_argument_path($moodlepath) {
    global $CFG;

    if (isset($CFG->admin) and $CFG->admin !== 'admin') {
        $moodlepath = preg_replace('|^/admin/|', "/$CFG->admin/", $moodlepath);
    }

    if (isset($_SERVER['REMOTE_ADDR'])) {
                $cwd = dirname(dirname(__DIR__));
    } else {
                $cwd = getcwd();
    }
    if (substr($cwd, -1) !== DIRECTORY_SEPARATOR) {
        $cwd .= DIRECTORY_SEPARATOR;
    }
    $path = realpath($CFG->dirroot.$moodlepath);

    if (strpos($path, $cwd) === 0) {
        $path = substr($path, strlen($cwd));
    }

    if (testing_is_cygwin()) {
        $path = str_replace('\\', '/', $path);
    }

    return $path;
}


function testing_fix_file_permissions($file) {
    global $CFG;

    $permissions = fileperms($file);
    if ($permissions & $CFG->filepermissions != $CFG->filepermissions) {
        $permissions = $permissions | $CFG->filepermissions;
        return chmod($file, $permissions);
    }

    return true;
}


function testing_is_cygwin() {
    if (empty($_SERVER['OS']) or $_SERVER['OS'] !== 'Windows_NT') {
        return false;

    } else if (!empty($_SERVER['SHELL']) and $_SERVER['SHELL'] === '/bin/bash') {
        return true;

    } else if (!empty($_SERVER['TERM']) and $_SERVER['TERM'] === 'cygwin') {
        return true;

    } else {
        return false;
    }
}


function testing_is_mingw() {

    if (!testing_is_cygwin()) {
        return false;
    }

    if (!empty($_SERVER['MSYSTEM'])) {
        return true;
    }

    return false;
}


function testing_initdataroot($dataroot, $framework) {
    global $CFG;

    $filename = $dataroot . '/' . $framework . 'testdir.txt';

    umask(0);
    if (!file_exists($filename)) {
        file_put_contents($filename, 'Contents of this directory are used during tests only, do not delete this file!');
    }
    testing_fix_file_permissions($filename);

    $varname = $framework . '_dataroot';
    $datarootdir = $CFG->{$varname} . '/' . $framework;
    if (!file_exists($datarootdir)) {
        mkdir($datarootdir, $CFG->directorypermissions);
    }
}


function testing_error($errorcode, $text = '') {

        echo($text."\n");
    if (isset($_SERVER['REMOTE_ADDR'])) {
        header('HTTP/1.1 500 Internal Server Error');
    }
    exit($errorcode);
}


function testing_update_composer_dependencies() {
        $cwd = getcwd();

        $dirroot = dirname(dirname(__DIR__));
    $composerpath = $dirroot . DIRECTORY_SEPARATOR . 'composer.phar';
    $composerurl = 'https://getcomposer.org/composer.phar';

        chdir($dirroot);

            if (!file_exists($composerpath)) {
        $file = @fopen($composerpath, 'w');
        if ($file === false) {
            $errordetails = error_get_last();
            $error = sprintf("Unable to create composer.phar\nPHP error: %s",
                             $errordetails['message']);
            testing_error(TESTING_EXITCODE_COMPOSER, $error);
        }
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL,  $composerurl);
        curl_setopt($curl, CURLOPT_FILE, $file);
        $result = curl_exec($curl);

        $curlerrno = curl_errno($curl);
        $curlerror = curl_error($curl);
        $curlinfo = curl_getinfo($curl);

        curl_close($curl);
        fclose($file);

        if (!$result) {
            $error = sprintf("Unable to download composer.phar\ncURL error (%d): %s",
                             $curlerrno, $curlerror);
            testing_error(TESTING_EXITCODE_COMPOSER, $error);
        } else if ($curlinfo['http_code'] === 404) {
            if (file_exists($composerpath)) {
                                unlink($composerpath);
            }
            $error = sprintf("Unable to download composer.phar\n" .
                                "404 http status code fetching $composerurl");
            testing_error(TESTING_EXITCODE_COMPOSER, $error);
        }
    } else {
        passthru("php composer.phar self-update", $code);
        if ($code != 0) {
            exit($code);
        }
    }

        passthru("php composer.phar install", $code);
    if ($code != 0) {
        exit($code);
    }

        chdir($cwd);
}
