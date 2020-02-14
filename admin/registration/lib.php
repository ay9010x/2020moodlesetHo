<?php







define('HUB_SITENOTPUBLISHED', 'notdisplayed');


define('HUB_SITENAMEPUBLISHED', 'named');


define('HUB_SITELINKPUBLISHED', 'linked');


class registration_manager {

    
    public function cron() {
        global $CFG;
        if (extension_loaded('xmlrpc')) {
            $function = 'hub_update_site_info';
            require_once($CFG->dirroot . "/webservice/xmlrpc/lib.php");

                        $hubs = $this->get_registered_on_hubs();
            if (empty($hubs)) {
                mtrace(get_string('registrationwarning', 'admin'));
            }
            foreach ($hubs as $hub) {
                                $siteinfo = $this->get_site_info($hub->huburl);
                $params = array('siteinfo' => $siteinfo);
                $serverurl = $hub->huburl . "/local/hub/webservice/webservices.php";
                $xmlrpcclient = new webservice_xmlrpc_client($serverurl, $hub->token);
                try {
                    $result = $xmlrpcclient->call($function, $params);
                    $this->update_registeredhub($hub);                     mtrace(get_string('siteupdatedcron', 'hub', $hub->hubname));
                } catch (Exception $e) {
                    $errorparam = new stdClass();
                    $errorparam->errormessage = $e->getMessage();
                    $errorparam->hubname = $hub->hubname;
                    mtrace(get_string('errorcron', 'hub', $errorparam));
                }
            }
        } else {
            mtrace(get_string('errorcronnoxmlrpc', 'hub'));
        }
    }

    
    public function get_site_secret_for_hub($huburl) {
        global $DB;

        $existingregistration = $DB->get_record('registration_hubs',
                    array('huburl' => $huburl));

        if (!empty($existingregistration)) {
            return $existingregistration->secret;
        }

        if ($huburl == HUB_MOODLEORGHUBURL) {
            $siteidentifier =  get_site_identifier();
        } else {
            $siteidentifier = random_string(32) . $_SERVER['HTTP_HOST'];
        }

        return $siteidentifier;

    }

    
    public function add_registeredhub($hub) {
        global $DB;
        $hub->timemodified = time();
        $id = $DB->insert_record('registration_hubs', $hub);
        return $id;
    }

    
    public function delete_registeredhub($huburl) {
        global $DB;
        $DB->delete_records('registration_hubs', array('huburl' => $huburl));
    }

    
    public function get_registeredhub($huburl = null, $token = null) {
        global $DB;

        $params = array();
        if (!empty($huburl)) {
            $params['huburl'] = $huburl;
        }
        if (!empty($token)) {
            $params['token'] = $token;
        }
        $params['confirmed'] = 1;
        $token = $DB->get_record('registration_hubs', $params);
        return $token;
    }

    
    public function get_unconfirmedhub($huburl) {
        global $DB;

        $params = array();
        $params['huburl'] = $huburl;
        $params['confirmed'] = 0;
        $token = $DB->get_record('registration_hubs', $params);
        return $token;
    }

    
    public function update_registeredhub($hub) {
        global $DB;
        $hub->timemodified = time();
        $DB->update_record('registration_hubs', $hub);
    }

    
    public function get_registered_on_hubs() {
        global $DB;
        $hubs = $DB->get_records('registration_hubs', array('confirmed' => 1));
        return $hubs;
    }

    
    public function get_site_info($huburl) {
        global $CFG, $DB;

        $siteinfo = array();
        $cleanhuburl = clean_param($huburl, PARAM_ALPHANUMEXT);
        $siteinfo['name'] = get_config('hub', 'site_name_' . $cleanhuburl);
        $siteinfo['description'] = get_config('hub', 'site_description_' . $cleanhuburl);
        $siteinfo['contactname'] = get_config('hub', 'site_contactname_' . $cleanhuburl);
        $siteinfo['contactemail'] = get_config('hub', 'site_contactemail_' . $cleanhuburl);
        $siteinfo['contactphone'] = get_config('hub', 'site_contactphone_' . $cleanhuburl);
        $siteinfo['imageurl'] = get_config('hub', 'site_imageurl_' . $cleanhuburl);
        $siteinfo['privacy'] = get_config('hub', 'site_privacy_' . $cleanhuburl);
        $siteinfo['street'] = get_config('hub', 'site_address_' . $cleanhuburl);
        $siteinfo['regioncode'] = get_config('hub', 'site_region_' . $cleanhuburl);
        $siteinfo['countrycode'] = get_config('hub', 'site_country_' . $cleanhuburl);
        $siteinfo['geolocation'] = get_config('hub', 'site_geolocation_' . $cleanhuburl);
        $siteinfo['contactable'] = get_config('hub', 'site_contactable_' . $cleanhuburl);
        $siteinfo['emailalert'] = get_config('hub', 'site_emailalert_' . $cleanhuburl);
        if (get_config('hub', 'site_coursesnumber_' . $cleanhuburl) == -1) {
            $coursecount = -1;
        } else {
            $coursecount = $DB->count_records('course') - 1;
        }
        $siteinfo['courses'] = $coursecount;
        if (get_config('hub', 'site_usersnumber_' . $cleanhuburl) == -1) {
            $usercount = -1;
        } else {
            $usercount = $DB->count_records('user', array('deleted' => 0));
        }
        $siteinfo['users'] = $usercount;

        if (get_config('hub', 'site_roleassignmentsnumber_' . $cleanhuburl) == -1) {
            $roleassigncount = -1;
        } else {
            $roleassigncount = $DB->count_records('role_assignments');
        }
        $siteinfo['enrolments'] = $roleassigncount;
        if (get_config('hub', 'site_postsnumber_' . $cleanhuburl) == -1) {
            $postcount = -1;
        } else {
            $postcount = $DB->count_records('forum_posts');
        }
        $siteinfo['posts'] = $postcount;
        if (get_config('hub', 'site_questionsnumber_' . $cleanhuburl) == -1) {
            $questioncount = -1;
        } else {
            $questioncount = $DB->count_records('question');
        }
        $siteinfo['questions'] = $questioncount;
        if (get_config('hub', 'site_resourcesnumber_' . $cleanhuburl) == -1) {
            $resourcecount = -1;
        } else {
            $resourcecount = $DB->count_records('resource');
        }
        $siteinfo['resources'] = $resourcecount;
                require_once($CFG->libdir . '/badgeslib.php');
        if (get_config('hub', 'site_badges_' . $cleanhuburl) == -1) {
            $badges = -1;
        } else {
            $badges = $DB->count_records_select('badge', 'status <> ' . BADGE_STATUS_ARCHIVED);
        }
        $siteinfo['badges'] = $badges;
        if (get_config('hub', 'site_issuedbadges_' . $cleanhuburl) == -1) {
            $issuedbadges = -1;
        } else {
            $issuedbadges = $DB->count_records('badge_issued');
        }
        $siteinfo['issuedbadges'] = $issuedbadges;
                require_once($CFG->dirroot . "/course/lib.php");
        if (get_config('hub', 'site_participantnumberaverage_' . $cleanhuburl) == -1) {
            $participantnumberaverage = -1;
        } else {
            $participantnumberaverage = average_number_of_participants();
        }
        $siteinfo['participantnumberaverage'] = $participantnumberaverage;
        if (get_config('hub', 'site_modulenumberaverage_' . $cleanhuburl) == -1) {
            $modulenumberaverage = -1;
        } else {
            $modulenumberaverage = average_number_of_courses_modules();
        }
        $siteinfo['modulenumberaverage'] = $modulenumberaverage;
        $siteinfo['language'] = get_config('hub', 'site_language_' . $cleanhuburl);
        $siteinfo['moodleversion'] = $CFG->version;
        $siteinfo['moodlerelease'] = $CFG->release;
        $siteinfo['url'] = $CFG->wwwroot;

        return $siteinfo;
    }

    
    public function get_site_privacy_string($privacy) {
        switch ($privacy) {
            case HUB_SITENOTPUBLISHED:
                $privacystring = get_string('siteprivacynotpublished', 'hub');
                break;
            case HUB_SITENAMEPUBLISHED:
                $privacystring = get_string('siteprivacypublished', 'hub');
                break;
            case HUB_SITELINKPUBLISHED:
                $privacystring = get_string('siteprivacylinked', 'hub');
                break;
        }
        if (empty($privacystring)) {
            throw new moodle_exception('unknownprivacy');
        }
        return $privacystring;
    }

}
?>
