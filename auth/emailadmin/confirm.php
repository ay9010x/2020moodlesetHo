<?php



require('../../config.php');
require_once($CFG->libdir.'/authlib.php');

function send_confirmation_email_user($user) {
    global $CFG;

    $site = get_site();
    $supportuser = core_user::get_support_user();

    $data = new stdClass();
    $data->firstname = fullname($user);
    $data->sitename  = format_string($site->fullname);
    $data->admin     = generate_email_signoff();

        $subject = get_string_manager()->get_string('auth_emailadminconfirmationsubject', 'auth_emailadmin', format_string($site->fullname), 'zh_tw');

    $username = urlencode($user->username);
    $username = str_replace('.', '%2E', $username);     $data->link  = $CFG->wwwroot;
    $data->username = $username;

        $messagetext = get_string_manager()->get_string('auth_emailadminuserconfirmation', 'auth_emailadmin', $data, 'zh_tw');
    $messagehtml = text_to_html($messagetext, false, false, true);

    $user->mailformat = 1;  
    
    return email_to_user($user, $supportuser, $subject, $messagetext, $messagehtml);
}

$data = optional_param('data', '', PARAM_RAW);  
$p = optional_param('p', '', PARAM_ALPHANUM);   $s = optional_param('s', '', PARAM_RAW);        
$PAGE->set_url('/auth/emailadmin/confirm.php');
$PAGE->set_context(context_system::instance());

if (empty($CFG->registerauth)) {
    print_error('cannotusepage2');
}
$authplugin = get_auth_plugin($CFG->registerauth);

if (!$authplugin->can_confirm()) {
    print_error('cannotusepage2');
}

if (!empty($data) || (!empty($p) && !empty($s))) {

    if (!empty($data)) {
        $dataelements = explode('/', $data, 2);         $usersecret = $dataelements[0];
        $username   = $dataelements[1];
    } else {
        $usersecret = $p;
        $username   = $s;
    }

    $confirmed = $authplugin->user_confirm($username, $usersecret);

    if ($confirmed == AUTH_CONFIRM_ALREADY) {
        $user = get_complete_user_data('username', $username);
        $PAGE->navbar->add(get_string("alreadyconfirmed"));
        $PAGE->set_title(get_string("alreadyconfirmed"));
        $PAGE->set_heading($COURSE->fullname);
        echo $OUTPUT->header();
        echo $OUTPUT->box_start('generalbox centerpara boxwidthnormal boxaligncenter');
        echo "<h3>".get_string("thanks").", ". fullname($user) . "</h3>\n";
        echo "<p>".get_string("alreadyconfirmed")."</p>\n";
        echo $OUTPUT->single_button("$CFG->wwwroot/course/", get_string('courses'));
        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();
        exit;

    } else if ($confirmed == AUTH_CONFIRM_OK) {

        
        if (!$user = get_complete_user_data('username', $username)) {
            print_error('cannotfinduser', '', '', s($username));
        }

        send_confirmation_email_user($user);
        $PAGE->navbar->add(get_string("confirmed"));
        $PAGE->set_title(get_string("confirmed"));
        $PAGE->set_heading($COURSE->fullname);
        echo $OUTPUT->header();
        echo $OUTPUT->box_start('generalbox centerpara boxwidthnormal boxaligncenter');
        echo "<h3>".get_string("thanks").", ". fullname($USER) . "</h3>\n";
        echo "<p>".get_string("confirmed")."</p>\n";
        echo $OUTPUT->single_button("$CFG->wwwroot/course/", get_string('courses'));
        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();
        exit;
    } else {
        mtrace("Confirm returned: ". $confirmed);
        print_error('invalidconfirmdata');
    }
} else {
    print_error("errorwhenconfirming");
}

redirect("$CFG->wwwroot/");
