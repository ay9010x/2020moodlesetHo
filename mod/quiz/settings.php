<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/quiz/lib.php');

$reports = core_component::get_plugin_list_with_file('quiz', 'settings.php', false);
$reportsbyname = array();
foreach ($reports as $report => $reportdir) {
    $strreportname = get_string($report . 'report', 'quiz_'.$report);
    $reportsbyname[$strreportname] = $report;
}
core_collator::ksort($reportsbyname);

$rules = core_component::get_plugin_list_with_file('quizaccess', 'settings.php', false);
$rulesbyname = array();
foreach ($rules as $rule => $ruledir) {
    $strrulename = get_string('pluginname', 'quizaccess_' . $rule);
    $rulesbyname[$strrulename] = $rule;
}
core_collator::ksort($rulesbyname);

if (empty($reportsbyname) && empty($rulesbyname)) {
    $pagetitle = get_string('modulename', 'quiz');
} else {
    $pagetitle = get_string('generalsettings', 'admin');
}
$quizsettings = new admin_settingpage('modsettingquiz', $pagetitle, 'moodle/site:config');

if ($ADMIN->fulltree) {
        $quizsettings->add(new admin_setting_heading('quizintro', '', get_string('configintro', 'quiz')));

        $quizsettings->add(new admin_setting_configduration_with_advanced('quiz/timelimit',
            get_string('timelimit', 'quiz'), get_string('configtimelimitsec', 'quiz'),
            array('value' => '0', 'adv' => false), 60));

        $quizsettings->add(new mod_quiz_admin_setting_overduehandling('quiz/overduehandling',
            get_string('overduehandling', 'quiz'), get_string('overduehandling_desc', 'quiz'),
            array('value' => 'autosubmit', 'adv' => false), null));

        $quizsettings->add(new admin_setting_configduration_with_advanced('quiz/graceperiod',
            get_string('graceperiod', 'quiz'), get_string('graceperiod_desc', 'quiz'),
            array('value' => '86400', 'adv' => false)));

        $quizsettings->add(new admin_setting_configduration('quiz/graceperiodmin',
            get_string('graceperiodmin', 'quiz'), get_string('graceperiodmin_desc', 'quiz'),
            60, 1));

        $options = array(get_string('unlimited'));
    for ($i = 1; $i <= QUIZ_MAX_ATTEMPT_OPTION; $i++) {
        $options[$i] = $i;
    }
    $quizsettings->add(new admin_setting_configselect_with_advanced('quiz/attempts',
            get_string('attemptsallowed', 'quiz'), get_string('configattemptsallowed', 'quiz'),
            array('value' => 0, 'adv' => false), $options));

        $quizsettings->add(new mod_quiz_admin_setting_grademethod('quiz/grademethod',
            get_string('grademethod', 'quiz'), get_string('configgrademethod', 'quiz'),
            array('value' => QUIZ_GRADEHIGHEST, 'adv' => false), null));

        $quizsettings->add(new admin_setting_configtext('quiz/maximumgrade',
            get_string('maximumgrade'), get_string('configmaximumgrade', 'quiz'), 10, PARAM_INT));

        $perpage = array();
    $perpage[0] = get_string('never');
    $perpage[1] = get_string('aftereachquestion', 'quiz');
    for ($i = 2; $i <= QUIZ_MAX_QPP_OPTION; ++$i) {
        $perpage[$i] = get_string('afternquestions', 'quiz', $i);
    }
    $quizsettings->add(new admin_setting_configselect_with_advanced('quiz/questionsperpage',
            get_string('newpageevery', 'quiz'), get_string('confignewpageevery', 'quiz'),
            array('value' => 1, 'adv' => false), $perpage));

        $quizsettings->add(new admin_setting_configselect_with_advanced('quiz/navmethod',
            get_string('navmethod', 'quiz'), get_string('confignavmethod', 'quiz'),
            array('value' => QUIZ_NAVMETHOD_FREE, 'adv' => true), quiz_get_navigation_options()));

        $quizsettings->add(new admin_setting_configcheckbox_with_advanced('quiz/shuffleanswers',
            get_string('shufflewithin', 'quiz'), get_string('configshufflewithin', 'quiz'),
            array('value' => 1, 'adv' => false)));

        $quizsettings->add(new admin_setting_question_behaviour('quiz/preferredbehaviour',
            get_string('howquestionsbehave', 'question'), get_string('howquestionsbehave_desc', 'quiz'),
            'deferredfeedback'));

        $quizsettings->add(new admin_setting_configselect_with_advanced('quiz/canredoquestions',
            get_string('canredoquestions', 'quiz'), get_string('canredoquestions_desc', 'quiz'),
            array('value' => 0, 'adv' => true),
            array(0 => get_string('no'), 1 => get_string('canredoquestionsyes', 'quiz'))));

        $quizsettings->add(new admin_setting_configcheckbox_with_advanced('quiz/attemptonlast',
            get_string('eachattemptbuildsonthelast', 'quiz'),
            get_string('configeachattemptbuildsonthelast', 'quiz'),
            array('value' => 0, 'adv' => true)));

        $quizsettings->add(new admin_setting_heading('reviewheading',
            get_string('reviewoptionsheading', 'quiz'), ''));
    foreach (mod_quiz_admin_review_setting::fields() as $field => $name) {
        $default = mod_quiz_admin_review_setting::all_on();
        $forceduring = null;
        if ($field == 'attempt') {
            $forceduring = true;
        } else if ($field == 'overallfeedback') {
            $default = $default ^ mod_quiz_admin_review_setting::DURING;
            $forceduring = false;
        }
        $quizsettings->add(new mod_quiz_admin_review_setting('quiz/review' . $field,
                $name, '', $default, $forceduring));
    }

        $quizsettings->add(new mod_quiz_admin_setting_user_image('quiz/showuserpicture',
            get_string('showuserpicture', 'quiz'), get_string('configshowuserpicture', 'quiz'),
            array('value' => 0, 'adv' => false), null));

        $options = array();
    for ($i = 0; $i <= QUIZ_MAX_DECIMAL_OPTION; $i++) {
        $options[$i] = $i;
    }
    $quizsettings->add(new admin_setting_configselect_with_advanced('quiz/decimalpoints',
            get_string('decimalplaces', 'quiz'), get_string('configdecimalplaces', 'quiz'),
            array('value' => 2, 'adv' => false), $options));

        $options = array(-1 => get_string('sameasoverall', 'quiz'));
    for ($i = 0; $i <= QUIZ_MAX_Q_DECIMAL_OPTION; $i++) {
        $options[$i] = $i;
    }
    $quizsettings->add(new admin_setting_configselect_with_advanced('quiz/questiondecimalpoints',
            get_string('decimalplacesquestion', 'quiz'),
            get_string('configdecimalplacesquestion', 'quiz'),
            array('value' => -1, 'adv' => true), $options));

        $quizsettings->add(new admin_setting_configcheckbox_with_advanced('quiz/showblocks',
            get_string('showblocks', 'quiz'), get_string('configshowblocks', 'quiz'),
            array('value' => 0, 'adv' => true)));

        $quizsettings->add(new admin_setting_configtext_with_advanced('quiz/password',
            get_string('requirepassword', 'quiz'), get_string('configrequirepassword', 'quiz'),
            array('value' => '', 'adv' => false), PARAM_TEXT));

        $quizsettings->add(new admin_setting_configtext_with_advanced('quiz/subnet',
            get_string('requiresubnet', 'quiz'), get_string('configrequiresubnet', 'quiz'),
            array('value' => '', 'adv' => true), PARAM_TEXT));

        $quizsettings->add(new admin_setting_configduration_with_advanced('quiz/delay1',
            get_string('delay1st2nd', 'quiz'), get_string('configdelay1st2nd', 'quiz'),
            array('value' => 0, 'adv' => true), 60));
    $quizsettings->add(new admin_setting_configduration_with_advanced('quiz/delay2',
            get_string('delaylater', 'quiz'), get_string('configdelaylater', 'quiz'),
            array('value' => 0, 'adv' => true), 60));

        $quizsettings->add(new mod_quiz_admin_setting_browsersecurity('quiz/browsersecurity',
            get_string('showinsecurepopup', 'quiz'), get_string('configpopup', 'quiz'),
            array('value' => '-', 'adv' => true), null));

    $quizsettings->add(new admin_setting_configtext('quiz/initialnumfeedbacks',
            get_string('initialnumfeedbacks', 'quiz'), get_string('initialnumfeedbacks_desc', 'quiz'),
            2, PARAM_INT, 5));

        if (!empty($CFG->enableoutcomes)) {
        $quizsettings->add(new admin_setting_configcheckbox('quiz/outcomes_adv',
            get_string('outcomesadvanced', 'quiz'), get_string('configoutcomesadvanced', 'quiz'),
            '0'));
    }

        $quizsettings->add(new admin_setting_configduration('quiz/autosaveperiod',
            get_string('autosaveperiod', 'quiz'), get_string('autosaveperiod_desc', 'quiz'), 60, 1));
}

if (empty($reportsbyname) && empty($rulesbyname)) {
    $ADMIN->add('modsettings', $quizsettings);
} else {
    $ADMIN->add('modsettings', new admin_category('modsettingsquizcat',
            get_string('modulename', 'quiz'), $module->is_enabled() === false));
    $ADMIN->add('modsettingsquizcat', $quizsettings);

        foreach ($reportsbyname as $strreportname => $report) {
        $reportname = $report;

        $settings = new admin_settingpage('modsettingsquizcat'.$reportname,
                $strreportname, 'moodle/site:config', $module->is_enabled() === false);
        if ($ADMIN->fulltree) {
            include($CFG->dirroot . "/mod/quiz/report/$reportname/settings.php");
        }
        if (!empty($settings)) {
            $ADMIN->add('modsettingsquizcat', $settings);
        }
    }

        foreach ($rulesbyname as $strrulename => $rule) {
        $settings = new admin_settingpage('modsettingsquizcat' . $rule,
                $strrulename, 'moodle/site:config', $module->is_enabled() === false);
        if ($ADMIN->fulltree) {
            include($CFG->dirroot . "/mod/quiz/accessrule/$rule/settings.php");
        }
        if (!empty($settings)) {
            $ADMIN->add('modsettingsquizcat', $settings);
        }
    }
}

$settings = null; 