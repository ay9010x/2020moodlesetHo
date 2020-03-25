<?php



defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(__FILE__)) . '/message/lib.php');


function message_send($eventdata) {
    global $CFG, $DB;

        $messageid = false;

        $defaultpreferences = get_message_output_default_preferences();
    $preferencebase = $eventdata->component.'_'.$eventdata->name;
        if (!empty($defaultpreferences->{$preferencebase.'_disable'})) {
        return $messageid;
    }

        if (!isset($eventdata->notification)) {
        $eventdata->notification = 1;
    }

    if (!is_object($eventdata->userto)) {
        $eventdata->userto = core_user::get_user($eventdata->userto);
    }
    if (!is_object($eventdata->userfrom)) {
        $eventdata->userfrom = core_user::get_user($eventdata->userfrom);
    }
    if (!$eventdata->userto) {
        debugging('Attempt to send msg to unknown user', DEBUG_NORMAL);
        return false;
    }
    if (!$eventdata->userfrom) {
        debugging('Attempt to send msg from unknown user', DEBUG_NORMAL);
        return false;
    }

        if (!isset($eventdata->userto->auth) or !isset($eventdata->userto->suspended)
            or !isset($eventdata->userto->deleted) or !isset($eventdata->userto->emailstop)) {

        debugging('Necessary properties missing in userto object, fetching full record', DEBUG_DEVELOPER);
        $eventdata->userto = core_user::get_user($eventdata->userto->id);
    }

    $usertoisrealuser = (core_user::is_real_user($eventdata->userto->id) != false);
        if (!$usertoisrealuser && !empty($eventdata->userto->emailstop)) {
        debugging('Attempt to send msg to internal (noreply) user', DEBUG_NORMAL);
        return false;
    }

        if (isset($CFG->block_online_users_timetosee)) {
        $timetoshowusers = $CFG->block_online_users_timetosee * 60;
    } else {
        $timetoshowusers = 300;    }

        if (!empty($eventdata->userto->lastaccess) && (time()-$timetoshowusers) < $eventdata->userto->lastaccess) {
        $userstate = 'loggedin';
    } else {
        $userstate = 'loggedoff';
    }

        $savemessage = new stdClass();
    $savemessage->useridfrom        = $eventdata->userfrom->id;
    $savemessage->useridto          = $eventdata->userto->id;
    $savemessage->subject           = $eventdata->subject;
    $savemessage->fullmessage       = $eventdata->fullmessage;
    $savemessage->fullmessageformat = $eventdata->fullmessageformat;
    $savemessage->fullmessagehtml   = $eventdata->fullmessagehtml;
    $savemessage->smallmessage      = $eventdata->smallmessage;
    $savemessage->notification      = $eventdata->notification;

    if (!empty($eventdata->contexturl)) {
        $savemessage->contexturl = (string)$eventdata->contexturl;
    } else {
        $savemessage->contexturl = null;
    }

    if (!empty($eventdata->contexturlname)) {
        $savemessage->contexturlname = (string)$eventdata->contexturlname;
    } else {
        $savemessage->contexturlname = null;
    }

    $savemessage->timecreated = time();

    if (PHPUNIT_TEST and class_exists('phpunit_util')) {
                $componentdir = core_component::get_component_directory($eventdata->component);
        if (!$componentdir or !is_dir($componentdir)) {
            throw new coding_exception('Invalid component specified in message-send(): '.$eventdata->component);
        }
        if (!file_exists("$componentdir/db/messages.php")) {
            throw new coding_exception("$eventdata->component does not contain db/messages.php necessary for message_send()");
        }
        $messageproviders = null;
        include("$componentdir/db/messages.php");
        if (!isset($messageproviders[$eventdata->name])) {
            throw new coding_exception("Missing messaging defaults for event '$eventdata->name' in '$eventdata->component' messages.php file");
        }
        unset($componentdir);
        unset($messageproviders);
                if (phpunit_util::is_redirecting_messages()) {
            $savemessage->timeread = time();
            $messageid = $DB->insert_record('message_read', $savemessage);
            $message = $DB->get_record('message_read', array('id'=>$messageid));
            phpunit_util::message_sent($message);
            return $messageid;
        }
    }

        $processors = get_message_processors(true);

        $processorlist = array();
        foreach ($processors as $processor) {
                if (!$usertoisrealuser && !$processor->object->can_send_to_any_users()) {
            continue;
        }

                $defaultpreference = $processor->name.'_provider_'.$preferencebase.'_permitted';
        if (isset($defaultpreferences->{$defaultpreference})) {
            $permitted = $defaultpreferences->{$defaultpreference};
        } else {
                                    $preferrormsg = "Could not load preference $defaultpreference. Make sure the component and name you supplied
                    to message_send() are valid.";
            throw new coding_exception($preferrormsg);
        }

                        $userisconfigured = $processor->object->is_user_configured($eventdata->userto);

                if ($permitted == 'forced' && !$userisconfigured) {
            debugging('Attempt to force message delivery to user who has "'.$processor->name.'" output unconfigured', DEBUG_NORMAL);
        }

                if ($permitted == 'forced' && $userisconfigured) {
                        $processorlist[] = $processor->name;
        } else if ($permitted == 'permitted' && $userisconfigured && !$eventdata->userto->emailstop) {
                                    $userpreferencename = 'message_provider_'.$preferencebase.'_'.$userstate;
            if ($userpreference = get_user_preferences($userpreferencename, null, $eventdata->userto)) {
                if (in_array($processor->name, explode(',', $userpreference))) {
                    $processorlist[] = $processor->name;
                }
            } else if (isset($defaultpreferences->{$userpreferencename})) {
                if (in_array($processor->name, explode(',', $defaultpreferences->{$userpreferencename}))) {
                    $processorlist[] = $processor->name;
                }
            }
        }
    }

        $savemessage->id = $DB->insert_record('message', $savemessage);
    $eventdata->savedmessageid = $savemessage->id;

        return \core\message\manager::send_message($eventdata, $savemessage, $processorlist);
}



function message_update_providers($component='moodle') {
    global $DB;

        $fileproviders = message_get_providers_from_file($component);

        $dbproviders = message_get_providers_from_db($component);

    foreach ($fileproviders as $messagename => $fileprovider) {

        if (!empty($dbproviders[$messagename])) {                           if ($dbproviders[$messagename]->capability == $fileprovider['capability']) {                                  unset($dbproviders[$messagename]);
                continue;

            } else {                                                $provider = new stdClass();
                $provider->id         = $dbproviders[$messagename]->id;
                $provider->capability = $fileprovider['capability'];
                $DB->update_record('message_providers', $provider);
                unset($dbproviders[$messagename]);
                continue;
            }

        } else {             
            $provider = new stdClass();
            $provider->name       = $messagename;
            $provider->component  = $component;
            $provider->capability = $fileprovider['capability'];

            $transaction = $DB->start_delegated_transaction();
            $DB->insert_record('message_providers', $provider);
            message_set_default_message_preference($component, $messagename, $fileprovider);
            $transaction->allow_commit();
        }
    }

    foreach ($dbproviders as $dbprovider) {          $DB->delete_records('message_providers', array('id' => $dbprovider->id));
        $DB->delete_records_select('config_plugins', "plugin = 'message' AND ".$DB->sql_like('name', '?', false), array("%_provider_{$component}_{$dbprovider->name}_%"));
        $DB->delete_records_select('user_preferences', $DB->sql_like('name', '?', false), array("message_provider_{$component}_{$dbprovider->name}_%"));
        cache_helper::invalidate_by_definition('core', 'config', array(), 'message');
    }

    return true;
}


function message_update_processors($processorname) {
    global $DB;

        $processor = $DB->get_records('message_processors', array('name' => $processorname));
    if (empty($processor)) {
        throw new invalid_parameter_exception();
    }

    $providers = $DB->get_records_sql('SELECT DISTINCT component FROM {message_providers}');

    $transaction = $DB->start_delegated_transaction();
    foreach ($providers as $provider) {
                $fileproviders = message_get_providers_from_file($provider->component);
        foreach ($fileproviders as $messagename => $fileprovider) {
            message_set_default_message_preference($provider->component, $messagename, $fileprovider, $processorname);
        }
    }
    $transaction->allow_commit();
}


function message_set_default_message_preference($component, $messagename, $fileprovider, $processorname='') {
    global $DB;

        $condition = null;
        if (!empty($processorname)) {
       $condition = array('name' => $processorname);
    }
    $processors = $DB->get_records('message_processors', $condition);

        $defaultpreferences = get_message_output_default_preferences();

        $componentproviderbase = $component.'_'.$messagename;
    $loggedinpref = array();
    $loggedoffpref = array();
        foreach ($processors as $processor) {
        $preferencename = $processor->name.'_provider_'.$componentproviderbase.'_permitted';
                if (!isset($defaultpreferences->{$preferencename})) {
                        $plugindefault = 0;
            if (isset($fileprovider['defaults'][$processor->name])) {
                $plugindefault = $fileprovider['defaults'][$processor->name];
            }
                        list($permitted, $loggedin, $loggedoff) = translate_message_default_setting($plugindefault, $processor->name);
                        set_config($preferencename, $permitted, 'message');
                        if ($loggedin) {
                $loggedinpref[] = $processor->name;
            }
            if ($loggedoff) {
                $loggedoffpref[] = $processor->name;
            }
        }
    }
        if (!empty($loggedinpref)) {
        $preferencename = 'message_provider_'.$componentproviderbase.'_loggedin';
        if (isset($defaultpreferences->{$preferencename})) {
                                                $loggedinpref = array_merge($loggedinpref, explode(',', $defaultpreferences->{$preferencename}));
        }
        set_config($preferencename, join(',', $loggedinpref), 'message');
    }
    if (!empty($loggedoffpref)) {
        $preferencename = 'message_provider_'.$componentproviderbase.'_loggedoff';
        if (isset($defaultpreferences->{$preferencename})) {
                                                $loggedoffpref = array_merge($loggedoffpref, explode(',', $defaultpreferences->{$preferencename}));
        }
        set_config($preferencename, join(',', $loggedoffpref), 'message');
    }
}


function message_get_providers_for_user($userid) {
    global $DB, $CFG;

    $providers = get_message_providers();

        if (!$CFG->messaging) {
        foreach ($providers as $providerid => $provider) {
            if ($provider->name == 'instantmessage') {
                unset($providers[$providerid]);
                break;
            }
        }
    }

        foreach ($providers as $providerid => $provider) {
        list($type, $name) = core_component::normalize_component($provider->component);
        if ($type == 'enrol' && !enrol_is_enabled($name)) {
            unset($providers[$providerid]);
        }
    }

                            $unsureproviders = array();
    $unsurecapabilities = array();
    $systemcontext = context_system::instance();
    foreach ($providers as $providerid => $provider) {
        if (empty($provider->capability) || has_capability($provider->capability, $systemcontext, $userid)) {
                        continue;
        }

        $unsureproviders[$providerid] = $provider;
        $unsurecapabilities[$provider->capability] = 1;
        unset($providers[$providerid]);
    }

    if (empty($unsureproviders)) {
                return $providers;
    }

        list($capcondition, $params) = $DB->get_in_or_equal(
            array_keys($unsurecapabilities), SQL_PARAMS_NAMED);
    $params['userid'] = $userid;

    $sql = "SELECT DISTINCT rc.capability, 1

              FROM {role_assignments} ra
              JOIN {context} actx ON actx.id = ra.contextid
              JOIN {role_capabilities} rc ON rc.roleid = ra.roleid
              JOIN {context} cctx ON cctx.id = rc.contextid

             WHERE ra.userid = :userid
               AND rc.capability $capcondition
               AND rc.permission > 0
               AND (".$DB->sql_concat('actx.path', "'/'")." LIKE ".$DB->sql_concat('cctx.path', "'/%'").
               " OR ".$DB->sql_concat('cctx.path', "'/'")." LIKE ".$DB->sql_concat('actx.path', "'/%'").")";

    if (!empty($CFG->defaultfrontpageroleid)) {
        $frontpagecontext = context_course::instance(SITEID);

        list($capcondition2, $params2) = $DB->get_in_or_equal(
                array_keys($unsurecapabilities), SQL_PARAMS_NAMED);
        $params = array_merge($params, $params2);
        $params['frontpageroleid'] = $CFG->defaultfrontpageroleid;
        $params['frontpagepathpattern'] = $frontpagecontext->path . '/';

        $sql .= "
             UNION

            SELECT DISTINCT rc.capability, 1

              FROM {role_capabilities} rc
              JOIN {context} cctx ON cctx.id = rc.contextid

             WHERE rc.roleid = :frontpageroleid
               AND rc.capability $capcondition2
               AND rc.permission > 0
               AND ".$DB->sql_concat('cctx.path', "'/'")." LIKE :frontpagepathpattern";
    }

    $relevantcapabilities = $DB->get_records_sql_menu($sql, $params);

        foreach ($unsureproviders as $providerid => $provider) {
        if (array_key_exists($provider->capability, $relevantcapabilities)) {
            $providers[$providerid] = $provider;
        }
    }

    return $providers;
}


function message_get_providers_from_db($component) {
    global $DB;

    return $DB->get_records('message_providers', array('component'=>$component), '', 'name, id, component, capability');  }


function message_get_providers_from_file($component) {
    $defpath = core_component::get_component_directory($component).'/db/messages.php';

    $messageproviders = array();

    if (file_exists($defpath)) {
        require($defpath);
    }

    foreach ($messageproviders as $name => $messageprovider) {           if (empty($messageprovider['capability'])) {
            $messageproviders[$name]['capability'] = NULL;
        }
        if (empty($messageprovider['defaults'])) {
            $messageproviders[$name]['defaults'] = array();
        }
    }

    return $messageproviders;
}


function message_provider_uninstall($component) {
    global $DB;

    $transaction = $DB->start_delegated_transaction();
    $DB->delete_records('message_providers', array('component' => $component));
    $DB->delete_records_select('config_plugins', "plugin = 'message' AND ".$DB->sql_like('name', '?', false), array("%_provider_{$component}_%"));
    $DB->delete_records_select('user_preferences', $DB->sql_like('name', '?', false), array("message_provider_{$component}_%"));
    $transaction->allow_commit();
        cache_helper::invalidate_by_definition('core', 'config', array(), 'message');
}


function message_processor_uninstall($name) {
    global $DB;

    $transaction = $DB->start_delegated_transaction();
    $DB->delete_records('message_processors', array('name' => $name));
    $DB->delete_records_select('config_plugins', "plugin = ?", array("message_{$name}"));
            $DB->delete_records_select('config_plugins', "plugin = 'message' AND ".$DB->sql_like('name', '?', false), array("{$name}_provider_%"));
    $transaction->allow_commit();
        cache_helper::invalidate_by_definition('core', 'config', array(), array('message', "message_{$name}"));
}
