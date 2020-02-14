<?php




defined('MOODLE_INTERNAL') || die();

global $DB;
if (empty($lesson)) {
    print_error('cannotcallscript');
}
if (!isset($currenttab)) {
    $currenttab = '';
}
if (!isset($cm)) {
    $cm = get_coursemodule_from_instance('lesson', $lesson->id);
    $context = context_module::instance($cm->id);
}
if (!isset($course)) {
    $course = $DB->get_record('course', array('id' => $lesson->course));
}

$tabs = $row = $inactive = $activated = array();

$attemptscount = $DB->count_records('lesson_grades', array('lessonid'=>$lesson->id));

$row[] = new tabobject('view', "$CFG->wwwroot/mod/lesson/view.php?id=$cm->id", get_string('preview', 'lesson'), get_string('previewlesson', 'lesson', format_string($lesson->name)));
$row[] = new tabobject('edit', "$CFG->wwwroot/mod/lesson/edit.php?id=$cm->id", get_string('edit', 'lesson'), get_string('edita', 'moodle', format_string($lesson->name)));
if (has_capability('mod/lesson:viewreports', $context)) {
    $row[] = new tabobject('reports', "$CFG->wwwroot/mod/lesson/report.php?id=$cm->id", get_string('reports', 'lesson'),
            get_string('viewreports2', 'lesson', $attemptscount));
}
if (has_capability('mod/lesson:grade', $context)) {
    $row[] = new tabobject('essay', "$CFG->wwwroot/mod/lesson/essay.php?id=$cm->id", get_string('manualgrading', 'lesson'));
}

$tabs[] = $row;


switch ($currenttab) {
    case 'reportoverview':
    case 'reportdetail':
            $inactive[] = 'reports';
        $activated[] = 'reports';

        $row    = array();
        $row[]  = new tabobject('reportoverview', "$CFG->wwwroot/mod/lesson/report.php?id=$cm->id&amp;action=reportoverview", get_string('overview', 'lesson'));
        $row[]  = new tabobject('reportdetail', "$CFG->wwwroot/mod/lesson/report.php?id=$cm->id&amp;action=reportdetail", get_string('detailedstats', 'lesson'));
        $tabs[] = $row;
        break;
    case 'collapsed':
    case 'full':
    case 'single':
            $inactive[] = 'edit';
        $activated[] = 'edit';

        $row    = array();
        $row[]  = new tabobject('collapsed', "$CFG->wwwroot/mod/lesson/edit.php?id=$cm->id&amp;mode=collapsed", get_string('collapsed', 'lesson'));
        $row[]  = new tabobject('full', "$CFG->wwwroot/mod/lesson/edit.php?id=$cm->id&amp;mode=full", get_string('full', 'lesson'));
        $tabs[] = $row;
        break;
}

print_tabs($tabs, $currenttab, $inactive, $activated);
