<?php

defined('MOODLE_INTERNAL') OR die('not allowed');
require_once($CFG->dirroot.'/mod/feedback/item/feedback_item_class.php');
require_once($CFG->libdir.'/formslib.php');

class feedback_item_label extends feedback_item_base {
    protected $type = "label";
    private $presentationoptions = null;
    private $context;

    
    public function __construct() {
        $this->presentationoptions = array('maxfiles' => EDITOR_UNLIMITED_FILES,
                                           'trusttext'=>true);

    }

    public function build_editform($item, $feedback, $cm) {
        global $DB, $CFG;
        require_once('label_form.php');

                $position = $item->position;
        $lastposition = $DB->count_records('feedback_item', array('feedback'=>$feedback->id));
        if ($position == -1) {
            $i_formselect_last = $lastposition + 1;
            $i_formselect_value = $lastposition + 1;
            $item->position = $lastposition + 1;
        } else {
            $i_formselect_last = $lastposition;
            $i_formselect_value = $item->position;
        }
                $positionlist = array_slice(range(0, $i_formselect_last), 1, $i_formselect_last, true);

                $feedbackitems = feedback_get_depend_candidates_for_item($feedback, $item);
        $commonparams = array('cmid'=>$cm->id,
                             'id'=>isset($item->id) ? $item->id : null,
                             'typ'=>$item->typ,
                             'items'=>$feedbackitems,
                             'feedback'=>$feedback->id);

        $this->context = context_module::instance($cm->id);

                $item->presentationformat = FORMAT_HTML;
        $item->presentationtrust = 1;

                $this->presentationoptions = array_merge(array('context' => $this->context),
                                                 $this->presentationoptions);

        $item = file_prepare_standard_editor($item,
                                            'presentation',                                             $this->presentationoptions,
                                            $this->context,
                                            'mod_feedback',
                                            'item',                                             $item->id);

                $customdata = array('item' => $item,
                            'common' => $commonparams,
                            'positionlist' => $positionlist,
                            'position' => $position,
                            'presentationoptions' => $this->presentationoptions);

        $this->item_form = new feedback_label_form('edit_item.php', $customdata);
    }

    public function save_item() {
        global $DB;

        if (!$item = $this->item_form->get_data()) {
            return false;
        }

        if (isset($item->clone_item) AND $item->clone_item) {
            $item->id = '';             $item->position++;
        }

        $item->presentation = '';

        $item->hasvalue = $this->get_hasvalue();
        if (!$item->id) {
            $item->id = $DB->insert_record('feedback_item', $item);
        } else {
            $DB->update_record('feedback_item', $item);
        }

        $item = file_postupdate_standard_editor($item,
                                                'presentation',
                                                $this->presentationoptions,
                                                $this->context,
                                                'mod_feedback',
                                                'item',
                                                $item->id);

        $DB->update_record('feedback_item', $item);

        return $DB->get_record('feedback_item', array('id'=>$item->id));
    }

    
    private function print_item($item) {
        global $DB, $CFG;

        require_once($CFG->libdir . '/filelib.php');

                if (!$item->feedback AND $item->template) {
            $template = $DB->get_record('feedback_template', array('id'=>$item->template));
            if ($template->ispublic) {
                $context = context_system::instance();
            } else {
                $context = context_course::instance($template->course);
            }
            $filearea = 'template';
        } else {
            $cm = get_coursemodule_from_instance('feedback', $item->feedback);
            $context = context_module::instance($cm->id);
            $filearea = 'item';
        }

        $item->presentationformat = FORMAT_HTML;
        $item->presentationtrust = 1;

        $output = file_rewrite_pluginfile_urls($item->presentation,
                                               'pluginfile.php',
                                               $context->id,
                                               'mod_feedback',
                                               $filearea,
                                               $item->id);

        $formatoptions = array('overflowdiv'=>true, 'trusted'=>$CFG->enabletrusttext);
        echo format_text($output, FORMAT_HTML, $formatoptions);
    }

    
    public function get_display_name($item, $withpostfix = true) {
        return '';
    }

    
    public function complete_form_element($item, $form) {
        global $DB;
        if (!$item->feedback AND $item->template) {
                        $template = $DB->get_record('feedback_template', array('id' => $item->template));
            if ($template->ispublic) {
                $context = context_system::instance();
            } else {
                $context = context_course::instance($template->course);
            }
            $filearea = 'template';
        } else {
                        $context = $form->get_cm()->context;
            $filearea = 'item';
        }
        $output = file_rewrite_pluginfile_urls($item->presentation, 'pluginfile.php',
                $context->id, 'mod_feedback', $filearea, $item->id);
        $formatoptions = array('overflowdiv' => true, 'noclean' => true);
        $output = format_text($output, FORMAT_HTML, $formatoptions);

        $inputname = $item->typ . '_' . $item->id;

        $name = $this->get_display_name($item);
        $form->add_form_element($item, ['static', $inputname, $name, $output], false, false);
    }

    public function compare_value($item, $dbvalue, $dependvalue) {
        return false;
    }

    public function postupdate($item) {
        global $DB;

        $context = context_module::instance($item->cmid);
        $item = file_postupdate_standard_editor($item,
                                                'presentation',
                                                $this->presentationoptions,
                                                $context,
                                                'mod_feedback',
                                                'item',
                                                $item->id);

        $DB->update_record('feedback_item', $item);
        return $item->id;
    }

    public function get_hasvalue() {
        return 0;
    }

    public function can_switch_require() {
        return false;
    }

    public function excelprint_item(&$worksheet,
                             $row_offset,
                             $xls_formats,
                             $item,
                             $groupid,
                             $courseid = false) {
    }

    public function print_analysed($item, $itemnr = '', $groupid = false, $courseid = false) {
    }
    public function get_printval($item, $value) {
    }
}
