<?php



require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/enrol/lti/ims-blti/blti.php');

$toolid = required_param('id', PARAM_INT);

$tool = \enrol_lti\helper::get_lti_tool($toolid);

$ltirequest = new BLTI($tool->secret, false, false);

if ($ltirequest->valid) {
        if (!is_enabled_auth('lti')) {
        print_error('pluginnotenabled', 'auth', '', get_string('pluginname', 'auth_lti'));
        exit();
    }

        if (!enrol_is_enabled('lti')) {
        print_error('enrolisdisabled', 'enrol_lti');
        exit();
    }

        if ($tool->status != ENROL_INSTANCE_ENABLED) {
        print_error('enrolisdisabled', 'enrol_lti');
        exit();
    }

        $context = context::instance_by_id($tool->contextid);

        $user = new stdClass();
    $user->username = \enrol_lti\helper::create_username($ltirequest->info['oauth_consumer_key'], $ltirequest->info['user_id']);
    if (!empty($ltirequest->info['lis_person_name_given'])) {
        $user->firstname = $ltirequest->info['lis_person_name_given'];
    } else {
        $user->firstname = $ltirequest->info['user_id'];
    }
    if (!empty($ltirequest->info['lis_person_name_family'])) {
        $user->lastname = $ltirequest->info['lis_person_name_family'];
    } else {
        $user->lastname = $ltirequest->info['context_id'];
    }

    $user->email = \core_user::clean_field($ltirequest->getUserEmail(), 'email');

        $user = \enrol_lti\helper::assign_user_tool_data($tool, $user);

        if (!$dbuser = $DB->get_record('user', array('username' => $user->username, 'deleted' => 0))) {
                        if (empty($user->email)) {
            $user->email = $user->username .  "@example.com";
        }

        $user->auth = 'lti';
        $user->id = user_create_user($user);

                $user = $DB->get_record('user', array('id' => $user->id));
    } else {
        if (\enrol_lti\helper::user_match($user, $dbuser)) {
            $user = $dbuser;
        } else {
                        if (empty($user->email)) {
                unset($user->email);
            }

            $user->id = $dbuser->id;
            user_update_user($user);

                        $user = $DB->get_record('user', array('id' => $user->id));
        }
    }

        $image = false;
    if (!empty($ltirequest->info['user_image'])) {
        $image = $ltirequest->info['user_image'];
    } else if (!empty($ltirequest->info['custom_user_image'])) {
        $image = $ltirequest->info['custom_user_image'];
    }

        if ($image) {
        \enrol_lti\helper::update_user_profile_image($user->id, $image);
    }

        $isinstructor = $ltirequest->isInstructor();

    if ($context->contextlevel == CONTEXT_COURSE) {
        $courseid = $context->instanceid;
        $urltogo = new moodle_url('/course/view.php', array('id' => $courseid));

                unset($SESSION->forcepagelayout);
    } else if ($context->contextlevel == CONTEXT_MODULE) {
        $cmid = $context->instanceid;
        $cm = get_coursemodule_from_id(false, $context->instanceid, 0, false, MUST_EXIST);
        $urltogo = new moodle_url('/mod/' . $cm->modname . '/view.php', array('id' => $cm->id));

                if (!$isinstructor) {
                        $SESSION->forcepagelayout = 'embedded';
        } else {
                        unset($SESSION->forcepagelayout);
        }
    } else {
        print_error('invalidcontext');
        exit();
    }

        $result = \enrol_lti\helper::enrol_user($tool, $user->id);

        if ($result !== \enrol_lti\helper::ENROLMENT_SUCCESSFUL) {
        print_error($result, 'enrol_lti');
        exit();
    }

        $roleid = $isinstructor ? $tool->roleinstructor : $tool->rolelearner;
    role_assign($roleid, $user->id, $tool->contextid);

        $sourceid = (!empty($ltirequest->info['lis_result_sourcedid'])) ? $ltirequest->info['lis_result_sourcedid'] : '';
    $serviceurl = (!empty($ltirequest->info['lis_outcome_service_url'])) ? $ltirequest->info['lis_outcome_service_url'] : '';

        if ($userlog = $DB->get_record('enrol_lti_users', array('toolid' => $tool->id, 'userid' => $user->id))) {
        if ($userlog->sourceid != $sourceid) {
            $userlog->sourceid = $sourceid;
        }
        if ($userlog->serviceurl != $serviceurl) {
            $userlog->serviceurl = $serviceurl;
        }
        $userlog->lastaccess = time();
        $DB->update_record('enrol_lti_users', $userlog);
    } else {
                $userlog = new stdClass();
        $userlog->userid = $user->id;
        $userlog->toolid = $tool->id;
        $userlog->serviceurl = $serviceurl;
        $userlog->sourceid = $sourceid;
        $userlog->consumerkey = $ltirequest->info['oauth_consumer_key'];
        $userlog->consumersecret = $tool->secret;
        $userlog->lastgrade = 0;
        $userlog->lastaccess = time();
        $userlog->timecreated = time();

        if (!empty($ltirequest->info['ext_ims_lis_memberships_url'])) {
            $userlog->membershipsurl = $ltirequest->info['ext_ims_lis_memberships_url'];
        } else {
            $userlog->membershipsurl = '';
        }

        if (!empty($ltirequest->info['ext_ims_lis_memberships_id'])) {
            $userlog->membershipsid = $ltirequest->info['ext_ims_lis_memberships_id'];
        } else {
            $userlog->membershipsid = '';
        }
        $DB->insert_record('enrol_lti_users', $userlog);
    }

        complete_user_login($user);

    if (empty($CFG->allowframembedding)) {
                $stropentool = get_string('opentool', 'enrol_lti');
        echo html_writer::tag('p', get_string('frameembeddingnotenabled', 'enrol_lti'));
        echo html_writer::link($urltogo, $stropentool, array('target' => '_blank'));
    } else {
                redirect($urltogo);
    }
} else {
    echo $ltirequest->message;
}
