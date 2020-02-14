<?php




defined('MOODLE_INTERNAL') || die();


function flowplayer_send_flash_content($filename) {
    global $CFG;
    
        if (!empty($_GET) or !empty($_POST)) {
        header("HTTP/1.1 404 Not Found");
        die;
    }

        if (!empty($_SERVER['HTTP_REFERER'])) {
        $refhost = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
        $host = parse_url($CFG->wwwroot . '/', PHP_URL_HOST);
        if ($refhost and $host and strtolower($refhost) !== strtolower($host)) {
            header("HTTP/1.1 404 Not Found");
            die;
        }
    }

        $content = file_get_contents($CFG->dirroot . '/lib/flowplayer/' . $filename . '.bin');
    if (!$content) {
        header("HTTP/1.1 404 Not Found");
        die;
    }
    $content = base64_decode($content);

        if (strpos($CFG->wwwroot, 'https://') === 0) {
                header('Cache-Control: private, max-age=10, no-transform');
        header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
        header('Pragma: ');
    } else {
        header('Cache-Control: private, must-revalidate, pre-check=0, post-check=0, max-age=0, no-transform');
        header('Expires: '. gmdate('D, d M Y H:i:s', 0) .' GMT');
        header('Pragma: no-cache');
    }

        header('Content-Type: application/x-shockwave-flash');
    echo $content;
    die;
}
