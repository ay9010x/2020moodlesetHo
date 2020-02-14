<?php



require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('user_bulk_cohortadd_form.php');
require_once("$CFG->dirroot/cohort/lib.php");

$sort = optional_param('sort', 'fullname', PARAM_ALPHA);
$dir  = optional_param('dir', 'asc', PARAM_ALPHA);

admin_externalpage_setup('userbulk');
require_capability('moodle/cohort:assign', context_system::instance());

$users = $SESSION->bulk_users;

$strnever = get_string('never');

$cohorts = array(''=>get_string('choosedots'));
$allcohorts = $DB->get_records('cohort', null, 'name');

foreach ($allcohorts as $c) {
    if (!empty($c->component)) {
                continue;
    }
    $context = context::instance_by_id($c->contextid);
    if (!has_capability('moodle/cohort:assign', $context)) {
        continue;
    }

    if (empty($c->idnumber)) {
        $cohorts[$c->id] = format_string($c->name);
    } else {
        $cohorts[$c->id] = format_string($c->name) . ' [' . $c->idnumber . ']';
    }
}
unset($allcohorts);

if (count($cohorts) < 2) {
    redirect(new moodle_url('/admin/user/user_bulk.php'), get_string('bulknocohort', 'core_cohort'));
}

$countries = get_string_manager()->get_list_of_countries(true);
$namefields = get_all_user_name_fields(true);
foreach ($users as $key => $id) {
    $user = $DB->get_record('user', array('id'=>$id, 'deleted'=>0), 'id, ' . $namefields . ', username,
            email, country, lastaccess, city');
    $user->fullname = fullname($user, true);
    $user->country = @$countries[$user->country];
    unset($user->firstname);
    unset($user->lastname);
    $users[$key] = $user;
}
unset($countries);

$mform = new user_bulk_cohortadd_form(null, $cohorts);

if (empty($users) or $mform->is_cancelled()) {
    redirect(new moodle_url('/admin/user/user_bulk.php'));

} else if ($data = $mform->get_data()) {
        foreach ($users as $user) {
        if (!$DB->record_exists('cohort_members', array('cohortid'=>$data->cohort, 'userid'=>$user->id))) {
            cohort_add_member($data->cohort, $user->id);
        }
    }
    redirect(new moodle_url('/admin/user/user_bulk.php'));
}

function sort_compare($a, $b) {
    global $sort, $dir;
    if ($sort == 'lastaccess') {
        $rez = $b->lastaccess - $a->lastaccess;
    } else {
        $rez = strcasecmp(@$a->$sort, @$b->$sort);
    }
    return $dir == 'desc' ? -$rez : $rez;
}
usort($users, 'sort_compare');

$table = new html_table();
$table->width = "95%";
$columns = array('fullname', 'email', 'city', 'country', 'lastaccess');
foreach ($columns as $column) {
    $strtitle = get_string($column);
    if ($sort != $column) {
        $columnicon = '';
        $columndir = 'asc';
    } else {
        $columndir = ($dir == 'asc') ? 'desc' : 'asc';
        $columnicon = ' <img src="'.$OUTPUT->pix_url('t/'.($dir == 'asc' ? 'down' : 'up' )).'" alt="" />';
    }
    $table->head[] = '<a href="user_bulk_cohortadd.php?sort='.$column.'&amp;dir='.$columndir.'">'.$strtitle.'</a>'.$columnicon;
    $table->align[] = 'left';
}

foreach ($users as $user) {
    $table->data[] = array (
        '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.SITEID.'">'.$user->fullname.'</a>',
        $user->email,
        $user->city,
        $user->country,
        $user->lastaccess ? format_time(time() - $user->lastaccess) : $strnever
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('bulkadd', 'core_cohort'));

echo html_writer::table($table);

echo $OUTPUT->box_start();
$mform->display();
echo $OUTPUT->box_end();

echo $OUTPUT->footer();