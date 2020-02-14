<?php



define('NO_DEBUG_DISPLAY', true);

require("../../config.php");
require_once("lib.php");
require_once($CFG->libdir.'/eventslib.php');
require_once($CFG->libdir.'/enrollib.php');
require_once($CFG->libdir . '/filelib.php');

set_exception_handler('enrol_paypal_ipn_exception_handler');

if (empty($_POST) or !empty($_GET)) {
    print_error("Sorry, you can not use the script that way.");
}


$req = 'cmd=_notify-validate';

$data = new stdClass();

foreach ($_POST as $key => $value) {
    $req .= "&$key=".urlencode($value);
    $data->$key = fix_utf8($value);
}

$custom = explode('-', $data->custom);
$data->userid           = (int)$custom[0];
$data->courseid         = (int)$custom[1];
$data->instanceid       = (int)$custom[2];
$data->payment_gross    = $data->mc_gross;
$data->payment_currency = $data->mc_currency;
$data->timeupdated      = time();



if (! $user = $DB->get_record("user", array("id"=>$data->userid))) {
    message_paypal_error_to_admin("Not a valid user id", $data);
    die;
}

if (! $course = $DB->get_record("course", array("id"=>$data->courseid))) {
    message_paypal_error_to_admin("Not a valid course id", $data);
    die;
}

if (! $context = context_course::instance($course->id, IGNORE_MISSING)) {
    message_paypal_error_to_admin("Not a valid context id", $data);
    die;
}

if (! $plugin_instance = $DB->get_record("enrol", array("id"=>$data->instanceid, "status"=>0))) {
    message_paypal_error_to_admin("Not a valid instance id", $data);
    die;
}

$plugin = enrol_get_plugin('paypal');

$paypaladdr = empty($CFG->usepaypalsandbox) ? 'www.paypal.com' : 'www.sandbox.paypal.com';
$c = new curl();
$options = array(
    'returntransfer' => true,
    'httpheader' => array('application/x-www-form-urlencoded', "Host: $paypaladdr"),
    'timeout' => 30,
    'CURLOPT_HTTP_VERSION' => CURL_HTTP_VERSION_1_1,
);
$location = "https://$paypaladdr/cgi-bin/webscr";
$result = $c->post($location, $req, $options);

if (!$result) {      echo "<p>Error: could not access paypal.com</p>";
    message_paypal_error_to_admin("Could not access paypal.com to verify payment", $data);
    die;
}



if (strlen($result) > 0) {
    if (strcmp($result, "VERIFIED") == 0) {          

        
                
        if ($data->payment_status != "Completed" and $data->payment_status != "Pending") {
            $plugin->unenrol_user($plugin_instance, $data->userid);
            message_paypal_error_to_admin("Status not completed or pending. User unenrolled from course", $data);
            die;
        }

        
        if ($data->mc_currency != $plugin_instance->currency) {
            message_paypal_error_to_admin("Currency does not match course settings, received: ".$data->mc_currency, $data);
            die;
        }

                
        if ($data->payment_status == "Pending" and $data->pending_reason != "echeck") {
            $eventdata = new stdClass();
            $eventdata->modulename        = 'moodle';
            $eventdata->component         = 'enrol_paypal';
            $eventdata->name              = 'paypal_enrolment';
            $eventdata->userfrom          = get_admin();
            $eventdata->userto            = $user;
            $eventdata->subject           = "Moodle: PayPal payment";
            $eventdata->fullmessage       = "Your PayPal payment is pending.";
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml   = '';
            $eventdata->smallmessage      = '';
            message_send($eventdata);

            message_paypal_error_to_admin("Payment pending", $data);
            die;
        }

                
        if (! ( $data->payment_status == "Completed" or
               ($data->payment_status == "Pending" and $data->pending_reason == "echeck") ) ) {
            die;
        }

        


        if ($existing = $DB->get_record("enrol_paypal", array("txn_id"=>$data->txn_id))) {               message_paypal_error_to_admin("Transaction $data->txn_id is being repeated!", $data);
            die;

        }

        if (core_text::strtolower($data->business) !== core_text::strtolower($plugin->get_config('paypalbusiness'))) {               message_paypal_error_to_admin("Business email is {$data->business} (not ".
                    $plugin->get_config('paypalbusiness').")", $data);
            die;

        }

        if (!$user = $DB->get_record('user', array('id'=>$data->userid))) {               message_paypal_error_to_admin("User $data->userid doesn't exist", $data);
            die;
        }

        if (!$course = $DB->get_record('course', array('id'=>$data->courseid))) {             message_paypal_error_to_admin("Course $data->courseid doesn't exist", $data);
            die;
        }

        $coursecontext = context_course::instance($course->id, IGNORE_MISSING);

                if ( (float) $plugin_instance->cost <= 0 ) {
            $cost = (float) $plugin->get_config('cost');
        } else {
            $cost = (float) $plugin_instance->cost;
        }

                $cost = format_float($cost, 2, false);

        if ($data->payment_gross < $cost) {
            message_paypal_error_to_admin("Amount paid is not enough ($data->payment_gross < $cost))", $data);
            die;

        }
                $data->item_name = $course->fullname;

        
        $DB->insert_record("enrol_paypal", $data);

        if ($plugin_instance->enrolperiod) {
            $timestart = time();
            $timeend   = $timestart + $plugin_instance->enrolperiod;
        } else {
            $timestart = 0;
            $timeend   = 0;
        }

                $plugin->enrol_user($plugin_instance, $user->id, $plugin_instance->roleid, $timestart, $timeend);

                if ($users = get_users_by_capability($context, 'moodle/course:update', 'u.*', 'u.id ASC',
                                             '', '', '', '', false, true)) {
            $users = sort_by_roleassignment_authority($users, $context);
            $teacher = array_shift($users);
        } else {
            $teacher = false;
        }

        $mailstudents = $plugin->get_config('mailstudents');
        $mailteachers = $plugin->get_config('mailteachers');
        $mailadmins   = $plugin->get_config('mailadmins');
        $shortname = format_string($course->shortname, true, array('context' => $context));


        if (!empty($mailstudents)) {
            $a = new stdClass();
            $a->coursename = format_string($course->fullname, true, array('context' => $coursecontext));
            $a->profileurl = "$CFG->wwwroot/user/view.php?id=$user->id";

            $eventdata = new stdClass();
            $eventdata->modulename        = 'moodle';
            $eventdata->component         = 'enrol_paypal';
            $eventdata->name              = 'paypal_enrolment';
            $eventdata->userfrom          = empty($teacher) ? core_user::get_support_user() : $teacher;
            $eventdata->userto            = $user;
            $eventdata->subject           = get_string("enrolmentnew", 'enrol', $shortname);
            $eventdata->fullmessage       = get_string('welcometocoursetext', '', $a);
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml   = '';
            $eventdata->smallmessage      = '';
            message_send($eventdata);

        }

        if (!empty($mailteachers) && !empty($teacher)) {
            $a->course = format_string($course->fullname, true, array('context' => $coursecontext));
            $a->user = fullname($user);

            $eventdata = new stdClass();
            $eventdata->modulename        = 'moodle';
            $eventdata->component         = 'enrol_paypal';
            $eventdata->name              = 'paypal_enrolment';
            $eventdata->userfrom          = $user;
            $eventdata->userto            = $teacher;
            $eventdata->subject           = get_string("enrolmentnew", 'enrol', $shortname);
            $eventdata->fullmessage       = get_string('enrolmentnewuser', 'enrol', $a);
            $eventdata->fullmessageformat = FORMAT_PLAIN;
            $eventdata->fullmessagehtml   = '';
            $eventdata->smallmessage      = '';
            message_send($eventdata);
        }

        if (!empty($mailadmins)) {
            $a->course = format_string($course->fullname, true, array('context' => $coursecontext));
            $a->user = fullname($user);
            $admins = get_admins();
            foreach ($admins as $admin) {
                $eventdata = new stdClass();
                $eventdata->modulename        = 'moodle';
                $eventdata->component         = 'enrol_paypal';
                $eventdata->name              = 'paypal_enrolment';
                $eventdata->userfrom          = $user;
                $eventdata->userto            = $admin;
                $eventdata->subject           = get_string("enrolmentnew", 'enrol', $shortname);
                $eventdata->fullmessage       = get_string('enrolmentnewuser', 'enrol', $a);
                $eventdata->fullmessageformat = FORMAT_PLAIN;
                $eventdata->fullmessagehtml   = '';
                $eventdata->smallmessage      = '';
                message_send($eventdata);
            }
        }

    } else if (strcmp ($result, "INVALID") == 0) {         $DB->insert_record("enrol_paypal", $data, false);
        message_paypal_error_to_admin("Received an invalid payment notification!! (Fake payment?)", $data);
    }
}

exit;




function message_paypal_error_to_admin($subject, $data) {
    echo $subject;
    $admin = get_admin();
    $site = get_site();

    $message = "$site->fullname:  Transaction failed.\n\n$subject\n\n";

    foreach ($data as $key => $value) {
        $message .= "$key => $value\n";
    }

    $eventdata = new stdClass();
    $eventdata->modulename        = 'moodle';
    $eventdata->component         = 'enrol_paypal';
    $eventdata->name              = 'paypal_enrolment';
    $eventdata->userfrom          = $admin;
    $eventdata->userto            = $admin;
    $eventdata->subject           = "PAYPAL ERROR: ".$subject;
    $eventdata->fullmessage       = $message;
    $eventdata->fullmessageformat = FORMAT_PLAIN;
    $eventdata->fullmessagehtml   = '';
    $eventdata->smallmessage      = '';
    message_send($eventdata);
}


function enrol_paypal_ipn_exception_handler($ex) {
    $info = get_exception_info($ex);

    $logerrmsg = "enrol_paypal IPN exception handler: ".$info->message;
    if (debugging('', DEBUG_NORMAL)) {
        $logerrmsg .= ' Debug: '.$info->debuginfo."\n".format_backtrace($info->backtrace, true);
    }
    error_log($logerrmsg);

    exit(0);
}
