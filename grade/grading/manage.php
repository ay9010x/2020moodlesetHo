<?php



require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->dirroot.'/grade/grading/lib.php');

$areaid     = optional_param('areaid', null, PARAM_INT);
$contextid  = optional_param('contextid', null, PARAM_INT);
$component  = optional_param('component', null, PARAM_COMPONENT);
$area       = optional_param('area', null, PARAM_AREA);
$returnurl  = optional_param('returnurl', null, PARAM_LOCALURL);
$setmethod  = optional_param('setmethod', null, PARAM_PLUGIN);
$shareform  = optional_param('shareform', null, PARAM_INT);
$deleteform = optional_param('deleteform', null, PARAM_INT);
$confirmed  = optional_param('confirmed', false, PARAM_BOOL);
$message    = optional_param('message', null, PARAM_NOTAGS);

if (!is_null($areaid)) {
        $manager = get_grading_manager($areaid);
} else {
        if (is_null($contextid) or is_null($component) or is_null($area)) {
        throw new coding_exception('The caller script must identify the gradable area.');
    }
    $context = context::instance_by_id($contextid, MUST_EXIST);
    $manager = get_grading_manager($context, $component, $area);
}

if ($manager->get_context()->contextlevel < CONTEXT_COURSE) {
    throw new coding_exception('Unsupported gradable area context level');
}

$method = $manager->get_active_method();

list($context, $course, $cm) = get_context_info_array($manager->get_context()->id);

require_login($course, true, $cm);
require_capability('moodle/grade:managegradingforms', $context);

if (!empty($returnurl)) {
    $returnurl = new moodle_url($returnurl);
} else {
    $returnurl = null;
}

$PAGE->set_url($manager->get_management_url($returnurl));
navigation_node::override_active_url($manager->get_management_url());
$PAGE->set_title(get_string('gradingmanagement', 'core_grading'));
$PAGE->set_heading(get_string('gradingmanagement', 'core_grading'));
$output = $PAGE->get_renderer('core_grading');

if (!empty($setmethod)) {
    require_sesskey();
    if ($setmethod == 'none') {
                $setmethod = null;
    }
    $manager->set_active_method($setmethod);
    redirect($PAGE->url);
}

if (!empty($shareform)) {
    require_capability('moodle/grade:sharegradingforms', context_system::instance());
    $controller = $manager->get_controller($method);
    $definition = $controller->get_definition();
    if (!$confirmed) {
                echo $output->header();
        echo $output->confirm(get_string('manageactionshareconfirm', 'core_grading', s($definition->name)),
            new moodle_url($PAGE->url, array('shareform' => $shareform, 'confirmed' => 1)),
            $PAGE->url);
        echo $output->footer();
        die();
    } else {
        require_sesskey();
        $newareaid = $manager->create_shared_area($method);
        $targetarea = get_grading_manager($newareaid);
        $targetcontroller = $targetarea->get_controller($method);
        $targetcontroller->update_definition($controller->get_definition_copy($targetcontroller));
        $DB->set_field('grading_definitions', 'timecopied', time(), array('id' => $definition->id));
        redirect(new moodle_url($PAGE->url, array('message' => get_string('manageactionsharedone', 'core_grading'))));
    }
}

if (!empty($deleteform)) {
    $controller = $manager->get_controller($method);
    $definition = $controller->get_definition();
    if (!$confirmed) {
                echo $output->header();
        echo $output->confirm(markdown_to_html(get_string('manageactiondeleteconfirm', 'core_grading', array(
            'formname'  => s($definition->name),
            'component' => $manager->get_component_title(),
            'area'      => $manager->get_area_title()))),
            new moodle_url($PAGE->url, array('deleteform' => $deleteform, 'confirmed' => 1)), $PAGE->url);
        echo $output->footer();
        die();
    } else {
        require_sesskey();
        $controller->delete_definition();
        redirect(new moodle_url($PAGE->url, array('message' => get_string('manageactiondeletedone', 'core_grading'))));
    }
}

echo $output->header();

if (!empty($message)) {
    echo $output->management_message($message);
}

echo $output->heading(get_string('gradingmanagementtitle', 'core_grading', array(
    'component' => $manager->get_component_title(), 'area' => $manager->get_area_title())));

echo $output->management_method_selector($manager, $PAGE->url);

if (!empty($method)) {
    $controller = $manager->get_controller($method);
        echo $output->container_start('actions');
    if ($controller->is_form_defined()) {
        $definition = $controller->get_definition();
                echo $output->management_action_icon($controller->get_editor_url($returnurl),
            get_string('manageactionedit', 'core_grading'), 'b/document-edit');
                echo $output->management_action_icon(new moodle_url($PAGE->url, array('deleteform' => $definition->id)),
            get_string('manageactiondelete', 'core_grading'), 'b/edit-delete');
                if (has_capability('moodle/grade:sharegradingforms', context_system::instance())) {
            if (empty($definition->copiedfromid)) {
                $hasoriginal = false;
            } else {
                $hasoriginal = $DB->record_exists('grading_definitions', array('id' => $definition->copiedfromid));
            }
            if (!$controller->is_form_available()) {
                                $allowshare = false;
            } else if (!$hasoriginal) {
                                if (empty($definition->timecopied)) {
                                        $allowshare = true;
                } else if ($definition->timemodified > $definition->timecopied) {
                                        $allowshare = true;
                } else {
                                        $allowshare = false;
                }
            } else {
                                if ($definition->timecreated == $definition->timemodified) {
                                        $allowshare = false;
                } else if (empty($definition->timecopied)) {
                                        $allowshare = true;
                } else if ($definition->timemodified > $definition->timecopied) {
                                        $allowshare = true;
                } else {
                                        $allowshare = false;
                }
            }
            if ($allowshare) {
                echo $output->management_action_icon(new moodle_url($PAGE->url, array('shareform' => $definition->id)),
                    get_string('manageactionshare', 'core_grading'), 'b/bookmark-new');
            }
        }
    } else {
        echo $output->management_action_icon($controller->get_editor_url($returnurl),
            get_string('manageactionnew', 'core_grading'), 'b/document-new');
        $pickurl = new moodle_url('/grade/grading/pick.php', array('targetid' => $controller->get_areaid()));
        if (!is_null($returnurl)) {
            $pickurl->param('returnurl', $returnurl->out(false));
        }
        echo $output->management_action_icon($pickurl,
            get_string('manageactionclone', 'core_grading'), 'b/edit-copy');
    }
    echo $output->container_end();

        if ($message = $controller->form_unavailable_notification()) {
        echo $output->notification($message);
    }
        if ($controller->is_form_defined()) {
        if ($definition->status == gradingform_controller::DEFINITION_STATUS_READY) {
            $tag = html_writer::tag('span', get_string('statusready', 'core_grading'), array('class' => 'status ready'));
        } else {
            $tag = html_writer::tag('span', get_string('statusdraft', 'core_grading'), array('class' => 'status draft'));
        }
        echo $output->heading(s($definition->name) . ' ' . $tag, 3, 'definition-name');
        echo $output->box($controller->render_preview($PAGE), 'definition-preview');
    }
}


echo $output->footer();
