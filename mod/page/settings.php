<?php




defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once("$CFG->libdir/resourcelib.php");

    $displayoptions = resourcelib_get_displayoptions(array(RESOURCELIB_DISPLAY_OPEN, RESOURCELIB_DISPLAY_POPUP));
    $defaultdisplayoptions = array(RESOURCELIB_DISPLAY_OPEN);

        $settings->add(new admin_setting_configmultiselect('page/displayoptions',
        get_string('displayoptions', 'page'), get_string('configdisplayoptions', 'page'),
        $defaultdisplayoptions, $displayoptions));

        $settings->add(new admin_setting_heading('pagemodeditdefaults', get_string('modeditdefaults', 'admin'), get_string('condifmodeditdefaults', 'admin')));

    $settings->add(new admin_setting_configcheckbox('page/printheading',
        get_string('printheading', 'page'), get_string('printheadingexplain', 'page'), 1));
    $settings->add(new admin_setting_configcheckbox('page/printintro',
        get_string('printintro', 'page'), get_string('printintroexplain', 'page'), 0));
    $settings->add(new admin_setting_configselect('page/display',
        get_string('displayselect', 'page'), get_string('displayselectexplain', 'page'), RESOURCELIB_DISPLAY_OPEN, $displayoptions));
    $settings->add(new admin_setting_configtext('page/popupwidth',
        get_string('popupwidth', 'page'), get_string('popupwidthexplain', 'page'), 620, PARAM_INT, 7));
    $settings->add(new admin_setting_configtext('page/popupheight',
        get_string('popupheight', 'page'), get_string('popupheightexplain', 'page'), 450, PARAM_INT, 7));
}
