<?php



defined('MOODLE_INTERNAL') || die('Direct access to this script is forbidden.');

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');


class mod_assign_grading_batch_operations_form extends moodleform {
    
    public function definition() {
        $mform = $this->_form;
        $instance = $this->_customdata;

                $options = array();
        $options['lock'] = get_string('locksubmissions', 'assign');
        $options['unlock'] = get_string('unlocksubmissions', 'assign');
        $options['downloadselected'] = get_string('downloadselectedsubmissions', 'assign');
        if ($instance['submissiondrafts']) {
            $options['reverttodraft'] = get_string('reverttodraft', 'assign');
        }
        if ($instance['duedate'] && has_capability('mod/assign:grantextension', $instance['context'])) {
            $options['grantextension'] = get_string('grantextension', 'assign');
        }
        if ($instance['attemptreopenmethod'] == ASSIGN_ATTEMPT_REOPEN_METHOD_MANUAL) {
            $options['addattempt'] = get_string('addattempt', 'assign');
        }

        foreach ($instance['feedbackplugins'] as $plugin) {
            if ($plugin->is_visible() && $plugin->is_enabled()) {
                foreach ($plugin->get_grading_batch_operations() as $action => $description) {
                    $operationkey = 'plugingradingbatchoperation_' . $plugin->get_type() . '_' . $action;
                    $options[$operationkey] = $description;
                }
            }
        }
        if ($instance['markingworkflow']) {
            $options['setmarkingworkflowstate'] = get_string('setmarkingworkflowstate', 'assign');
        }
        if ($instance['markingallocation']) {
            $options['setmarkingallocation'] = get_string('setmarkingallocation', 'assign');
        }
        
        global $context, $course, $cm, $DB;
        $hasfile = false;
        $assignment = new assign($context, $cm, $course);
        foreach ($assignment->get_submission_plugins() as $plugin) {
            if ($plugin->is_enabled() && $plugin->is_visible() && $plugin->get_type() =='file') {
                $hasfile = true;
            }
        }
        
        if($hasfile){
            $options['setpattern'] = get_string('setpattern', 'assign');
            
            if($DB->record_exists('folder',array('assign'=>$cm->instance))){
                $options['cancelpattern'] = get_string('cancelpattern', 'assign');
            }
        }
        
        $mform->addElement('hidden', 'action', 'gradingbatchoperation');
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'id', $instance['cm']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'selectedusers', '', array('class'=>'selectedusers'));
        $mform->setType('selectedusers', PARAM_SEQUENCE);
        $mform->addElement('hidden', 'returnaction', 'grading');
        $mform->setType('returnaction', PARAM_ALPHA);

        $objs = array();
        $objs[] =& $mform->createElement('select', 'operation', get_string('chooseoperation', 'assign'), $options);
        $objs[] =& $mform->createElement('submit', 'submit', get_string('go'));
        $batchdescription = get_string('batchoperationsdescription', 'assign');
        $mform->addElement('group', 'actionsgrp', $batchdescription, $objs, ' ', false);
    }
}

