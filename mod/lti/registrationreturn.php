<?php



require_once('../../config.php');
require_once($CFG->dirroot.'/mod/lti/locallib.php');

$top = optional_param('top', 0, PARAM_INT);
$msg = optional_param('lti_msg', '', PARAM_TEXT);
$err = optional_param('lti_errormsg', '', PARAM_TEXT);
$id = optional_param('id', 0, PARAM_INT);

require_sesskey();
require_login(0, false);

$systemcontext = context_system::instance();
require_capability('moodle/site:config', $systemcontext);

if (empty($top)) {

    $params = array();
    $params['sesskey'] = sesskey();
    $params['top'] = '1';
    if (!empty($msg)) {
        $params['lti_msg'] = $msg;
    }
    if (!empty($err)) {
        $params['lti_errormsg'] = $err;
    }
    if (!empty($id)) {
        $params['id'] = $id;
    }
    $redirect = new moodle_url('/mod/lti/registrationreturn.php', $params);
    $redirect = $redirect->out(false);

    $clickhere = get_string('click_to_continue', 'lti', (object)array('link' => $redirect));
    $html = <<< EOD
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script type="text/javascript">
//<![CDATA[
top.location.href = '{$redirect}';
//]]
</script>
</head>
<body>
<noscript>
{$clickhere}
</noscript>
</body>
</html>
EOD;

        send_headers('text/html; charset=utf-8', false);
    echo $html;

} else if (!empty($msg) && !empty($err)) {

    $params = array();
    $params['sesskey'] = sesskey();
    $params['top'] = '1';
    if (!empty($err)) {
        $params['lti_errormsg'] = $err;
    }
    if (!empty($id)) {
        $params['id'] = $id;
    }
    $redirect = new moodle_url('/mod/lti/registrationreturn.php', $params);
    $redirect = $redirect->out(false);
    redirect($redirect, $err);

} else {

    $redirect = new moodle_url('/mod/lti/toolproxies.php');
    if (!empty($id)) {
        $toolproxy = $DB->get_record('lti_tool_proxies', array('id' => $id));
        switch($toolproxy->state) {
            case LTI_TOOL_PROXY_STATE_ACCEPTED:
                $redirect->param('tab', 'tp_accepted');
                break;
            case LTI_TOOL_PROXY_STATE_REJECTED:
                $redirect->param('tab', 'tp_rejected');
                break;
            case LTI_TOOL_PROXY_STATE_PENDING:
                                $toolproxy->state = LTI_TOOL_PROXY_STATE_CONFIGURED;
                lti_update_tool_proxy($toolproxy);
        }
    }

    $redirect = $redirect->out();

    if (empty($msg)) {
        $msg = $err;
    }
    redirect($redirect, $msg);

}
