<?php




defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/mod/forum/lib.php');

    $settings->add(new admin_setting_configselect('forum_displaymode', get_string('displaymode', 'forum'),
                       get_string('configdisplaymode', 'forum'), FORUM_MODE_NESTED, forum_get_layout_modes()));

    $settings->add(new admin_setting_configcheckbox('forum_replytouser', get_string('replytouser', 'forum'),
                       get_string('configreplytouser', 'forum'), 1));

        $settings->add(new admin_setting_configtext('forum_shortpost', get_string('shortpost', 'forum'),
                       get_string('configshortpost', 'forum'), 300, PARAM_INT));

        $settings->add(new admin_setting_configtext('forum_longpost', get_string('longpost', 'forum'),
                       get_string('configlongpost', 'forum'), 600, PARAM_INT));

        $settings->add(new admin_setting_configtext('forum_manydiscussions', get_string('manydiscussions', 'forum'),
                       get_string('configmanydiscussions', 'forum'), 100, PARAM_INT));

    if (isset($CFG->maxbytes)) {
        $maxbytes = 0;
        if (isset($CFG->forum_maxbytes)) {
            $maxbytes = $CFG->forum_maxbytes;
        }
        $settings->add(new admin_setting_configselect('forum_maxbytes', get_string('maxattachmentsize', 'forum'),
                           get_string('configmaxbytes', 'forum'), 512000, get_max_upload_sizes($CFG->maxbytes, 0, 0, $maxbytes)));
    }

        $settings->add(new admin_setting_configtext('forum_maxattachments', get_string('maxattachments', 'forum'),
                       get_string('configmaxattachments', 'forum'), 9, PARAM_INT));

        $options = array();
    $options[FORUM_TRACKING_OPTIONAL] = get_string('trackingoptional', 'forum');
    $options[FORUM_TRACKING_OFF] = get_string('trackingoff', 'forum');
    $options[FORUM_TRACKING_FORCED] = get_string('trackingon', 'forum');
    $settings->add(new admin_setting_configselect('forum_trackingtype', get_string('trackingtype', 'forum'),
                       get_string('configtrackingtype', 'forum'), FORUM_TRACKING_OPTIONAL, $options));

        $settings->add(new admin_setting_configcheckbox('forum_trackreadposts', get_string('trackforum', 'forum'),
                       get_string('configtrackreadposts', 'forum'), 1));

        $settings->add(new admin_setting_configcheckbox('forum_allowforcedreadtracking', get_string('forcedreadtracking', 'forum'),
                       get_string('forcedreadtracking_desc', 'forum'), 0));

        $settings->add(new admin_setting_configtext('forum_oldpostdays', get_string('oldpostdays', 'forum'),
                       get_string('configoldpostdays', 'forum'), 14, PARAM_INT));

        $settings->add(new admin_setting_configcheckbox('forum_usermarksread', get_string('usermarksread', 'forum'),
                       get_string('configusermarksread', 'forum'), 0));

    $options = array();
    for ($i = 0; $i < 24; $i++) {
        $options[$i] = sprintf("%02d",$i);
    }
        $settings->add(new admin_setting_configselect('forum_cleanreadtime', get_string('cleanreadtime', 'forum'),
                       get_string('configcleanreadtime', 'forum'), 2, $options));

        $settings->add(new admin_setting_configselect('digestmailtime', get_string('digestmailtime', 'forum'),
                       get_string('configdigestmailtime', 'forum'), 17, $options));

    if (empty($CFG->enablerssfeeds)) {
        $options = array(0 => get_string('rssglobaldisabled', 'admin'));
        $str = get_string('configenablerssfeeds', 'forum').'<br />'.get_string('configenablerssfeedsdisabled2', 'admin');

    } else {
        $options = array(0=>get_string('no'), 1=>get_string('yes'));
        $str = get_string('configenablerssfeeds', 'forum');
    }
    $settings->add(new admin_setting_configselect('forum_enablerssfeeds', get_string('enablerssfeeds', 'admin'),
                       $str, 0, $options));

    if (!empty($CFG->enablerssfeeds)) {
        $options = array(
            0 => get_string('none'),
            1 => get_string('discussions', 'forum'),
            2 => get_string('posts', 'forum')
        );
        $settings->add(new admin_setting_configselect('forum_rsstype', get_string('rsstypedefault', 'forum'),
                get_string('configrsstypedefault', 'forum'), 0, $options));

        $options = array(
            0  => '0',
            1  => '1',
            2  => '2',
            3  => '3',
            4  => '4',
            5  => '5',
            10 => '10',
            15 => '15',
            20 => '20',
            25 => '25',
            30 => '30',
            40 => '40',
            50 => '50'
        );
        $settings->add(new admin_setting_configselect('forum_rssarticles', get_string('rssarticles', 'forum'),
                get_string('configrssarticlesdefault', 'forum'), 0, $options));
    }

    $settings->add(new admin_setting_configcheckbox('forum_enabletimedposts', get_string('timedposts', 'forum'),
                       get_string('configenabletimedposts', 'forum'), 1));
}

