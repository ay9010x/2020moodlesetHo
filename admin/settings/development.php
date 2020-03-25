<?php


if ($hassiteconfig) { 
        $ADMIN->add('development', new admin_category('experimental', new lang_string('experimental','admin')));

    $temp = new admin_settingpage('experimentalsettings', new lang_string('experimentalsettings', 'admin'));
            $temp->add(new admin_setting_configcheckbox('enablesafebrowserintegration', new lang_string('enablesafebrowserintegration', 'admin'), new lang_string('configenablesafebrowserintegration', 'admin'), 0));

    $temp->add(new admin_setting_configcheckbox('dndallowtextandlinks', new lang_string('dndallowtextandlinks', 'admin'), new lang_string('configdndallowtextandlinks', 'admin'), 0));
        $enablecssoptimiser = new admin_setting_configcheckbox('enablecssoptimiser', new lang_string('enablecssoptimiser','admin'), new lang_string('enablecssoptimiser_desc','admin'), 0);
    $enablecssoptimiser->set_updatedcallback('theme_reset_all_caches');
    $temp->add($enablecssoptimiser);

    $ADMIN->add('experimental', $temp);

        $temp = new admin_settingpage('debugging', new lang_string('debugging', 'admin'));
    $temp->add(new admin_setting_special_debug());
    $temp->add(new admin_setting_configcheckbox('debugdisplay', new lang_string('debugdisplay', 'admin'), new lang_string('configdebugdisplay', 'admin'), ini_get_bool('display_errors')));
    $temp->add(new admin_setting_configcheckbox('debugsmtp', new lang_string('debugsmtp', 'admin'), new lang_string('configdebugsmtp', 'admin'), 0));
    $temp->add(new admin_setting_configcheckbox('perfdebug', new lang_string('perfdebug', 'admin'), new lang_string('configperfdebug', 'admin'), '7', '15', '7'));
    $temp->add(new admin_setting_configcheckbox('debugstringids', new lang_string('debugstringids', 'admin'), new lang_string('debugstringids_desc', 'admin'), 0));
    $temp->add(new admin_setting_configcheckbox('debugvalidators', new lang_string('debugvalidators', 'admin'), new lang_string('configdebugvalidators', 'admin'), 0));
    $temp->add(new admin_setting_configcheckbox('debugpageinfo', new lang_string('debugpageinfo', 'admin'), new lang_string('configdebugpageinfo', 'admin'), 0));
    $ADMIN->add('development', $temp);

        $xhprofenabled = extension_loaded('xhprof') || extension_loaded('tideways');
    $temp = new admin_settingpage('profiling', new lang_string('profiling', 'admin'), 'moodle/site:config', !$xhprofenabled);
        $temp->add(new admin_setting_configcheckbox('profilingenabled', new lang_string('profilingenabled', 'admin'), new lang_string('profilingenabled_help', 'admin'), false));
        $temp->add(new admin_setting_configtextarea('profilingincluded', new lang_string('profilingincluded', 'admin'), new lang_string('profilingincluded_help', 'admin'), ''));
        $temp->add(new admin_setting_configtextarea('profilingexcluded', new lang_string('profilingexcluded', 'admin'), new lang_string('profilingexcluded_help', 'admin'), ''));
        $temp->add(new admin_setting_configtext('profilingautofrec', new lang_string('profilingautofrec', 'admin'), new lang_string('profilingautofrec_help', 'admin'), 0, PARAM_INT));
        $temp->add(new admin_setting_configcheckbox('profilingallowme', new lang_string('profilingallowme', 'admin'), new lang_string('profilingallowme_help', 'admin'), false));
        $temp->add(new admin_setting_configcheckbox('profilingallowall', new lang_string('profilingallowall', 'admin'), new lang_string('profilingallowall_help', 'admin'), false));
                $temp->add(new admin_setting_configselect('profilinglifetime', new lang_string('profilinglifetime', 'admin'), new lang_string('profilinglifetime_help', 'admin'), 24*60, array(
               0 => new lang_string('neverdeleteruns', 'admin'),
        30*24*60 => new lang_string('numdays', '', 30),
        15*24*60 => new lang_string('numdays', '', 15),
         7*24*60 => new lang_string('numdays', '', 7),
         4*24*60 => new lang_string('numdays', '', 4),
         2*24*60 => new lang_string('numdays', '', 2),
           24*60 => new lang_string('numhours', '', 24),
           16*80 => new lang_string('numhours', '', 16),
            8*60 => new lang_string('numhours', '', 8),
            4*60 => new lang_string('numhours', '', 4),
            2*60 => new lang_string('numhours', '', 2),
              60 => new lang_string('numminutes', '', 60),
              30 => new lang_string('numminutes', '', 30),
              15 => new lang_string('numminutes', '', 15))));
        $temp->add(new admin_setting_configtext('profilingimportprefix',
            new lang_string('profilingimportprefix', 'admin'),
            new lang_string('profilingimportprefix_desc', 'admin'), '(I)', PARAM_TAG, 10));

        $ADMIN->add('development', $temp);

         $ADMIN->add('development', new admin_externalpage('testclient', new lang_string('testclient', 'webservice'), "$CFG->wwwroot/$CFG->admin/webservice/testclient.php"));


    if ($CFG->mnet_dispatcher_mode !== 'off') {
        $ADMIN->add('development', new admin_externalpage('mnettestclient', new lang_string('testclient', 'mnet'), "$CFG->wwwroot/$CFG->admin/mnet/testclient.php"));
    }

    $ADMIN->add('development', new admin_externalpage('purgecaches', new lang_string('purgecaches','admin'), "$CFG->wwwroot/$CFG->admin/purgecaches.php"));

    $ADMIN->add('development', new admin_externalpage('thirdpartylibs', new lang_string('thirdpartylibs','admin'), "$CFG->wwwroot/$CFG->admin/thirdpartylibs.php"));
} 