<?php


if (!during_initial_install()) {     $frontpagecontext = context_course::instance(SITEID);

    if ($hassiteconfig or has_any_capability(array(
            'moodle/course:update',
            'moodle/role:assign',
            'moodle/restore:restorecourse',
            'moodle/backup:backupcourse',
            'moodle/course:managefiles',
            'moodle/question:add',
            'moodle/question:editmine',
            'moodle/question:editall',
            'moodle/question:viewmine',
            'moodle/question:viewall',
            'moodle/question:movemine',
            'moodle/question:moveall'), $frontpagecontext)) {

                $temp = new admin_settingpage('frontpagesettings', new lang_string('frontpagesettings','admin'), 'moodle/course:update', false, $frontpagecontext);
        $temp->add(new admin_setting_sitesettext('fullname', new lang_string('fullsitename'), '', NULL));         $temp->add(new admin_setting_sitesettext('shortname', new lang_string('shortsitename'), '', NULL));         $temp->add(new admin_setting_special_frontpagedesc());
        $temp->add(new admin_setting_courselist_frontpage(false));         $temp->add(new admin_setting_courselist_frontpage(true)); 
        $options = array();
        $options[] = new lang_string('unlimited');
        for ($i=1; $i<100; $i++) {
            $options[$i] = $i;
        }
        $temp->add(new admin_setting_configselect('maxcategorydepth', new lang_string('configsitemaxcategorydepth','admin'), new lang_string('configsitemaxcategorydepthhelp','admin'), 2, $options));

        $temp->add(new admin_setting_configtext('frontpagecourselimit', new lang_string('configfrontpagecourselimit','admin'), new lang_string('configfrontpagecourselimithelp','admin'), 200, PARAM_INT));

        $temp->add(new admin_setting_sitesetcheckbox('numsections', new lang_string('sitesection'), new lang_string('sitesectionhelp','admin'), 1));
        $temp->add(new admin_setting_sitesetselect('newsitems', new lang_string('newsitemsnumber'), '', 3,
             array('0' => '0',
                   '1' => '1',
                   '2' => '2',
                   '3' => '3',
                   '4' => '4',
                   '5' => '5',
                   '6' => '6',
                   '7' => '7',
                   '8' => '8',
                   '9' => '9',
                   '10' => '10')));
        $temp->add(new admin_setting_configtext('commentsperpage', new lang_string('commentsperpage', 'admin'), '', 15, PARAM_INT));

                $options = array(0=>new lang_string('none'));         $defaultfrontpageroleid = 0;
        $roles = role_fix_names(get_all_roles(), null, ROLENAME_ORIGINALANDSHORT);
        foreach ($roles as $role) {
            if (empty($role->archetype) or $role->archetype === 'guest' or $role->archetype === 'frontpage' or $role->archetype === 'student') {
                $options[$role->id] = $role->localname;
                if ($role->archetype === 'frontpage') {
                    $defaultfrontpageroleid = $role->id;
                }
            }
        }
        if ($defaultfrontpageroleid and (!isset($CFG->defaultfrontpageroleid) or $CFG->defaultfrontpageroleid)) {
                        unset($options[0]);
        }
        $temp->add(new admin_setting_configselect('defaultfrontpageroleid', new lang_string('frontpagedefaultrole', 'admin'), '', $defaultfrontpageroleid, $options));

        $ADMIN->add('frontpage', $temp);
    }
}
