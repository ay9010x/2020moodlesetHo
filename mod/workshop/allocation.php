<?php




require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/allocation/lib.php');

$cmid       = required_param('cmid', PARAM_INT);                    $method     = optional_param('method', 'manual', PARAM_ALPHA);      
$cm         = get_coursemodule_from_id('workshop', $cmid, 0, false, MUST_EXIST);
$course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$workshop   = $DB->get_record('workshop', array('id' => $cm->instance), '*', MUST_EXIST);
$workshop   = new workshop($workshop, $cm, $course);

$PAGE->set_url($workshop->allocation_url($method));

require_login($course, false, $cm);
$context = $PAGE->context;
require_capability('mod/workshop:allocate', $context);

$PAGE->set_title($workshop->name);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(get_string('allocation', 'workshop'));

$allocator  = $workshop->allocator_instance($method);
$initresult = $allocator->init();

$output = $PAGE->get_renderer('mod_workshop');
echo $output->header();
echo $OUTPUT->heading(format_string($workshop->name));

$allocators = workshop::installed_allocators();
if (!empty($allocators)) {
    $tabs       = array();
    $row        = array();
    $inactive   = array();
    $activated  = array();
    foreach ($allocators as $methodid => $methodname) {
        $row[] = new tabobject($methodid, $workshop->allocation_url($methodid)->out(), $methodname);
        if ($methodid == $method) {
            $currenttab = $methodid;
        }
    }
}
$tabs[] = $row;
print_tabs($tabs, $currenttab, $inactive, $activated);

if (is_null($initresult->get_status()) or $initresult->get_status() == workshop_allocation_result::STATUS_VOID) {
    echo $output->container_start('allocator-ui');
    echo $allocator->ui();
    echo $output->container_end();
} else {
    echo $output->container_start('allocator-init-results');
    echo $output->render($initresult);
    echo $output->continue_button($workshop->allocation_url($method));
    echo $output->container_end();
}
echo $output->footer();
