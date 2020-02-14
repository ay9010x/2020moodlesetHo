<?php

defined('MOODLE_INTERNAL') OR die('not allowed');
require_once($CFG->dirroot.'/mod/feedback/item/feedback_item_class.php');

class feedback_item_captcha extends feedback_item_base {
    protected $type = "captcha";

    public function build_editform($item, $feedback, $cm) {
        global $DB;

        $editurl = new moodle_url('/mod/feedback/edit.php', array('id'=>$cm->id));

                if (isset($item->id) AND $item->id > 0) {
            notice(get_string('there_are_no_settings_for_recaptcha', 'feedback'), $editurl->out());
            exit;
        }

                $params = array('feedback' => $feedback->id, 'typ' => $this->type);
        if ($DB->record_exists('feedback_item', $params)) {
            notice(get_string('only_one_captcha_allowed', 'feedback'), $editurl->out());
            exit;
        }

        $this->item = $item;
        $this->item_form = true; 
        $lastposition = $DB->count_records('feedback_item', array('feedback'=>$feedback->id));

        $this->item->feedback = $feedback->id;
        $this->item->template = 0;
        $this->item->name = get_string('captcha', 'feedback');
        $this->item->label = '';
        $this->item->presentation = '';
        $this->item->typ = $this->type;
        $this->item->hasvalue = $this->get_hasvalue();
        $this->item->position = $lastposition + 1;
        $this->item->required = 1;
        $this->item->dependitem = 0;
        $this->item->dependvalue = '';
        $this->item->options = '';
    }

    public function show_editform() {
    }

    public function is_cancelled() {
        return false;
    }

    public function get_data() {
        return true;
    }

    public function save_item() {
        global $DB;

        if (!$this->item) {
            return false;
        }

        if (empty($this->item->id)) {
            $this->item->id = $DB->insert_record('feedback_item', $this->item);
        } else {
            $DB->update_record('feedback_item', $this->item);
        }

        return $DB->get_record('feedback_item', array('id'=>$this->item->id));
    }

    public function get_printval($item, $value) {
        return '';
    }

    public function print_analysed($item, $itemnr = '', $groupid = false, $courseid = false) {
        return $itemnr;
    }

    public function excelprint_item(&$worksheet, $row_offset,
                             $xls_formats, $item,
                             $groupid, $courseid = false) {
        return $row_offset;
    }

    
    public function get_display_name($item, $withpostfix = true) {
        return get_string('captcha', 'feedback');
    }

    
    public function complete_form_element($item, $form) {
        $name = $this->get_display_name($item);
        $inputname = $item->typ . '_' . $item->id;

        if ($form->get_mode() != mod_feedback_complete_form::MODE_COMPLETE) {
            $form->add_form_element($item,
                    ['static', $inputname, $name],
                    false,
                    false);
        } else {
            $form->add_form_element($item,
                    ['recaptcha', $inputname, $name],
                    false,
                    false);
        }

                $form->add_validation_rule(function($values, $files) use ($item, $form) {
            $elementname = $item->typ . '_' . $item->id;
            $recaptchaelement = $form->get_form_element($elementname);
            if (empty($values['recaptcha_response_field'])) {
                return array($elementname => get_string('required'));
            } else if (!empty($values['recaptcha_challenge_field'])) {
                $challengefield = $values['recaptcha_challenge_field'];
                $responsefield = $values['recaptcha_response_field'];
                if (true !== ($result = $recaptchaelement->verify($challengefield, $responsefield))) {
                    return array($elementname => $result);
                }
            } else {
                return array($elementname => get_string('missingrecaptchachallengefield'));
            }
            return true;
        });

    }

    public function create_value($data) {
        global $USER;
        return $USER->sesskey;
    }

    public function get_hasvalue() {
        global $CFG;

                if (empty($CFG->recaptchaprivatekey) OR empty($CFG->recaptchapublickey)) {
            return 0;
        }
        return 1;
    }

    public function can_switch_require() {
        return false;
    }

    
    public function edit_actions($item, $feedback, $cm) {
        $actions = parent::edit_actions($item, $feedback, $cm);
        unset($actions['update']);
        return $actions;
    }
}