<?php


require_once('../config.php');
require_once($CFG->libdir.'/adminlib.php');

$return = optional_param('return', '', PARAM_ALPHA);

require_login(0, false);
if (isguestuser()) {
        $SESSION->wantsurl = (string)new moodle_url('/admin/upgradesettings.php', array('return'=>$return));
    redirect(get_login_url());
}

admin_externalpage_setup('upgradesettings'); $PAGE->set_pagelayout('maintenance'); $PAGE->blocks->show_only_fake_blocks();
$adminroot = admin_get_root(); 
if ($data = data_submitted() and confirm_sesskey()) {
    $count = admin_write_settings($data);
}

$newsettings = admin_output_new_settings_by_page($adminroot);
if (isset($newsettings['frontpagesettings'])) {
    $frontpage = $newsettings['frontpagesettings'];
    unset($newsettings['frontpagesettings']);
    array_unshift($newsettings, $frontpage);
}
$newsettingshtml = implode($newsettings);
unset($newsettings);

$focus = '';

if (empty($adminroot->errors) and $newsettingshtml === '') {
        if ($return == 'site') {
        redirect("$CFG->wwwroot/");
    } else {
        redirect("$CFG->wwwroot/$CFG->admin/index.php");
    }
}

if (!empty($adminroot->errors)) {
    $firsterror = reset($adminroot->errors);
    $focus = $firsterror->id;
}

echo $OUTPUT->header($focus);

if (!empty($SITE->fullname) and !empty($SITE->shortname)) {
    echo $OUTPUT->box(get_string('upgradesettingsintro','admin'), 'generalbox');
}

echo '<form action="upgradesettings.php" method="post" id="adminsettings">';
echo '<div>';
echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
echo '<input type="hidden" name="return" value="'.$return.'" />';
echo prevent_form_autofill_password();
echo '<fieldset>';
echo '<div class="clearer"><!-- --></div>';
echo $newsettingshtml;
echo '</fieldset>';
echo '<div class="form-buttons"><input class="form-submit" type="submit" value="'.get_string('savechanges','admin').'" /></div>';
echo '</div>';
echo '</form>';

echo $OUTPUT->footer();


