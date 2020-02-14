<?php



namespace enrol_lti\task;


class sync_members extends \core\task\scheduled_task {

    
    const LTI_MESSAGE_TYPE = 'basic-lis-readmembershipsforcontext';

    
    const LTI_VERSION = 'LTI-1p0';

    
    public function get_name() {
        return get_string('tasksyncmembers', 'enrol_lti');
    }

    
    public function execute() {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/enrol/lti/ims-blti/OAuth.php');
        require_once($CFG->dirroot . '/enrol/lti/ims-blti/OAuthBody.php');

                if (!is_enabled_auth('lti')) {
            mtrace('Skipping task - ' . get_string('pluginnotenabled', 'auth', get_string('pluginname', 'auth_lti')));
            return true;
        }

                        if (!enrol_is_enabled('lti')) {
            mtrace('Skipping task - ' . get_string('enrolisdisabled', 'enrol_lti'));
            return true;
        }

                if ($tools = \enrol_lti\helper::get_lti_tools(array('status' => ENROL_INSTANCE_ENABLED, 'membersync' => 1))) {
            $ltiplugin = enrol_get_plugin('lti');
            $consumers = array();
            $currentusers = array();
            $userphotos = array();
            foreach ($tools as $tool) {
                mtrace("Starting - Member sync for shared tool '$tool->id' for the course '$tool->courseid'.");

                                $usercount = 0;
                $enrolcount = 0;
                $unenrolcount = 0;

                                if ($ltiusers = $DB->get_records('enrol_lti_users', array('toolid' => $tool->id), 'lastaccess DESC')) {
                    foreach ($ltiusers as $ltiuser) {
                        $mtracecontent = "for the user '$ltiuser->userid' in the tool '$tool->id' for the course " .
                            "'$tool->courseid'";
                        $usercount++;

                                                if (!$ltiuser->membershipsurl) {
                            mtrace("Skipping - Empty membershipsurl $mtracecontent.");
                            continue;
                        }

                                                if (!$ltiuser->membershipsid) {
                            mtrace("Skipping - Empty membershipsid $mtracecontent.");
                            continue;
                        }

                        $consumer = sha1($ltiuser->membershipsurl . ':' . $ltiuser->membershipsid . ':' .
                            $ltiuser->consumerkey . ':' . $ltiuser->consumersecret);
                        if (in_array($consumer, $consumers)) {
                                                        continue;
                        }

                        $consumers[] = $consumer;

                        $params = array(
                            'lti_message_type' => self::LTI_MESSAGE_TYPE,
                            'id' => $ltiuser->membershipsid,
                            'lti_version' => self::LTI_VERSION
                        );

                        mtrace("Calling memberships url '$ltiuser->membershipsurl' with body '" .
                            json_encode($params) . "'");

                        try {
                            $response = sendOAuthParamsPOST('POST', $ltiuser->membershipsurl, $ltiuser->consumerkey,
                                $ltiuser->consumersecret, 'application/x-www-form-urlencoded', $params);
                        } catch (\Exception $e) {
                            mtrace("Skipping - No response received $mtracecontent from '$ltiuser->membershipsurl'");
                            mtrace($e->getMessage());
                            continue;
                        }

                                                $data = new \SimpleXMLElement($response);

                                                if (empty($data->statusinfo)) {
                            mtrace("Skipping - Bad response received $mtracecontent from '$ltiuser->membershipsurl'");
                            mtrace('Skipping - Error parsing the XML received \'' . substr($response, 0, 125) .
                                '\' ... (Displaying only 125 chars)');
                            continue;
                        }

                                                if (strpos(strtolower($data->statusinfo->codemajor), 'success') === false) {
                            mtrace('Skipping - Error received from the remote system: ' . $data->statusinfo->codemajor
                                . ' ' . $data->statusinfo->severity . ' ' . $data->statusinfo->codeminor);
                            continue;
                        }

                        $members = $data->memberships->member;
                        mtrace(count($members) . ' members received.');
                        foreach ($members as $member) {
                                                        $user = new \stdClass();
                            $user->username = \enrol_lti\helper::create_username($ltiuser->consumerkey, $member->user_id);
                            $user->firstname = \core_user::clean_field($member->person_name_given, 'firstname');
                            $user->lastname = \core_user::clean_field($member->person_name_family, 'lastname');
                            $user->email = \core_user::clean_field($member->person_contact_email_primary, 'email');

                                                        $user = \enrol_lti\helper::assign_user_tool_data($tool, $user);

                            if (!$dbuser = $DB->get_record('user', array('username' => $user->username, 'deleted' => 0))) {
                                if ($tool->membersyncmode == \enrol_lti\helper::MEMBER_SYNC_ENROL_AND_UNENROL ||
                                    $tool->membersyncmode == \enrol_lti\helper::MEMBER_SYNC_ENROL_NEW) {
                                                                                                            if (empty($user->email)) {
                                        $user->email = $user->username .  "@example.com";
                                    }

                                    $user->auth = 'lti';
                                    $user->id = user_create_user($user);

                                                                        $currentusers[] = $user->id;
                                    $userphotos[$user->id] = $member->user_image;
                                }
                            } else {
                                                                if (empty($user->email)) {
                                    unset($user->email);
                                }

                                $user->id = $dbuser->id;
                                user_update_user($user);

                                                                $currentusers[] = $user->id;
                                $userphotos[$user->id] = $member->user_image;
                            }
                            if ($tool->membersyncmode == \enrol_lti\helper::MEMBER_SYNC_ENROL_AND_UNENROL ||
                                $tool->membersyncmode == \enrol_lti\helper::MEMBER_SYNC_ENROL_NEW) {
                                                                \enrol_lti\helper::enrol_user($tool, $user->id);
                            }
                        }
                    }
                                        if ($tool->membersyncmode == \enrol_lti\helper::MEMBER_SYNC_ENROL_AND_UNENROL ||
                        $tool->membersyncmode == \enrol_lti\helper::MEMBER_SYNC_UNENROL_MISSING) {
                                                foreach ($ltiusers as $ltiuser) {
                            if (!in_array($ltiuser->userid, $currentusers)) {
                                $instance = new \stdClass();
                                $instance->id = $tool->enrolid;
                                $instance->courseid = $tool->courseid;
                                $instance->enrol = 'lti';
                                $ltiplugin->unenrol_user($instance, $ltiuser->id);
                            }
                        }
                    }
                }
                mtrace("Completed - Synced members for tool '$tool->id' in the course '$tool->courseid'. " .
                     "Processed $usercount users; enrolled $enrolcount members; unenrolled $unenrolcount members.");
                mtrace("");
            }

                        mtrace("Started - Syncing user profile images.");
            $counter = 0;
            if (!empty($userphotos)) {
                foreach ($userphotos as $userid => $url) {
                    if ($url) {
                        $result = \enrol_lti\helper::update_user_profile_image($userid, $url);
                        if ($result === \enrol_lti\helper::PROFILE_IMAGE_UPDATE_SUCCESSFUL) {
                            $counter++;
                            mtrace("Profile image succesfully downloaded and created for user '$userid' from $url.");
                        } else {
                            mtrace($result);
                        }
                    }
                }
            }
            mtrace("Completed - Synced $counter profile images.");
        }
    }
}
