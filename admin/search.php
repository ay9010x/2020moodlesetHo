<?php


require_once('../config.php');
require_once($CFG->libdir.'/adminlib.php');

$query = trim(optional_param('query', '', PARAM_NOTAGS));  
$PAGE->set_context(context_system::instance());

admin_externalpage_setup('search', '', array('query' => $query)); 
$adminroot = admin_get_root(); $adminroot->search = $query; $statusmsg = '';
$errormsg  = '';
$focus = '';

if ($data = data_submitted() and confirm_sesskey()) {
    if (admin_write_settings($data)) {
        redirect($PAGE->url, get_string('changessaved'), null, \core\output\notification::NOTIFY_SUCCESS);
    }

    if (!empty($adminroot->errors)) {
        $errormsg = get_string('errorwithsettings', 'admin');
        $firsterror = reset($adminroot->errors);
        $focus = $firsterror->id;
    } else {
        redirect($PAGE->url);
    }
}

echo $OUTPUT->header($focus);

if ($errormsg !== '') {
    echo $OUTPUT->notification($errormsg);

} else if ($statusmsg !== '') {
    echo $OUTPUT->notification($statusmsg, 'notifysuccess');
}

$resultshtml = admin_search_settings_html($query); 
echo '<form action="' . $PAGE->url->out(true) . '" method="post" id="adminsettings">';
echo '<div>';
echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
echo prevent_form_autofill_password();
echo '</div>';
echo '<fieldset>';
echo '<div class="clearer"><!-- --></div>';
if ($resultshtml != '') {
    echo $resultshtml;
} else {
    echo get_string('noresults','admin');
}
echo '</fieldset>';
echo '</form>';

echo $OUTPUT->footer();


