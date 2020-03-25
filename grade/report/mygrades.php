<?php



require_once('../../config.php');
require_once($CFG->dirroot . '/user/lib.php');

require_login(null, false);
if (isguestuser()) {
    throw new require_login_exception('Guests are not allowed here.');
}
$url = user_mygrades_url();
redirect($url);
