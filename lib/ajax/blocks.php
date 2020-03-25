<?php



define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__) . '/../../config.php');

$courseid = required_param('courseid', PARAM_INT);
$pagelayout = required_param('pagelayout', PARAM_ALPHAEXT);
$pagetype = required_param('pagetype', PARAM_ALPHANUMEXT);
$contextid = required_param('contextid', PARAM_INT);
$subpage = optional_param('subpage', '', PARAM_ALPHANUMEXT);
$cmid = optional_param('cmid', null, PARAM_INT);
$action = optional_param('action', '', PARAM_ALPHA);
$buimoveid = optional_param('bui_moveid', 0, PARAM_INT);
$buinewregion = optional_param('bui_newregion', '', PARAM_ALPHAEXT);
$buibeforeid = optional_param('bui_beforeid', 0, PARAM_INT);

$PAGE->set_pagetype($pagetype);
$PAGE->set_url('/lib/ajax/blocks.php', array('courseid' => $courseid, 'pagelayout' => $pagelayout, 'pagetype' => $pagetype));

$cm = null;
if (!is_null($cmid)) {
    $cm = get_coursemodule_from_id(null, $cmid, $courseid, false, MUST_EXIST);
}
require_login($courseid, false, $cm);
require_sesskey();

$PAGE->set_context(context::instance_by_id($contextid));

$PAGE->set_pagelayout($pagelayout);
$PAGE->set_subpage($subpage);
$PAGE->blocks->add_custom_regions_for_pagetype($pagetype);
$pagetype = explode('-', $pagetype);
switch ($pagetype[0]) {
    case 'my':
        $PAGE->set_blocks_editing_capability('moodle/my:manageblocks');
        break;
    case 'user':
        if ($pagetype[1] === 'profile' && $PAGE->context->contextlevel == CONTEXT_USER
                && $PAGE->context->instanceid == $USER->id) {
                        $PAGE->set_blocks_editing_capability('moodle/user:manageownblocks');
        } else {
            $PAGE->set_blocks_editing_capability('moodle/user:manageblocks');
        }
        break;
}

echo $OUTPUT->header();

switch ($action) {
    case 'move':
                $PAGE->blocks->load_blocks();
        $instances = $PAGE->blocks->get_blocks_for_region($buinewregion);

        $buinewweight = null;
        if ($buibeforeid == 0) {
            if (count($instances) === 0) {
                                $buinewweight = 0;
            } else {
                                $last = end($instances);
                $buinewweight = $last->instance->weight + 1;
            }
        } else {
                        $lastweight = 0;
            $lastblock = 0;
            $first = reset($instances);
            if ($first) {
                $lastweight = $first->instance->weight - 2;
            }

            foreach ($instances as $instance) {
                if ($instance->instance->id == $buibeforeid) {
                                        if ($lastblock == $buimoveid) {
                                                break;
                    }
                    $buinewweight = ($lastweight + $instance->instance->weight) / 2;
                    break;
                }
                $lastweight = $instance->instance->weight;
                $lastblock = $instance->instance->id;
            }
        }

                if (isset($buinewweight)) {
                        $_POST['bui_newweight'] = $buinewweight;
            $PAGE->blocks->process_url_move();
        }
        break;
}
