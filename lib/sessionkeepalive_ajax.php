<?php



define('AJAX_SCRIPT', true);
require_once(dirname(__DIR__) . '/config.php');

if (!confirm_sesskey()) {
    header('HTTP/1.1 403 Forbidden');
    print_error('invalidsesskey');
}

\core\session\manager::touch_session(session_id());
