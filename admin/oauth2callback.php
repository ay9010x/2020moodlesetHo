<?php




require_once(dirname(dirname(__FILE__)).'/config.php');

$code = required_param('code', PARAM_RAW);
$state = required_param('state', PARAM_LOCALURL);

$redirecturl = new moodle_url($state);
$params = $redirecturl->params();

if (isset($params['sesskey']) and confirm_sesskey($params['sesskey'])) {
    $redirecturl->param('oauth2code', $code);
    redirect($redirecturl);
} else {
    print_error('invalidsesskey');
}
