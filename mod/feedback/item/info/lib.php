<?php

defined('MOODLE_INTERNAL') OR die('not allowed');
require_once($CFG->dirroot.'/mod/feedback/item/feedback_item_class.php');

class feedback_item_info extends feedback_item_base {
    protected $type = "info";

    
    const MODE_RESPONSETIME = 1;
    
    const MODE_COURSE = 2;
    
    const MODE_CATEGORY = 3;

    
    const CURRENTTIMESTAMP = '__CURRENT__TIMESTAMP__';

    public function build_editform($item, $feedback, $cm) {
        global $DB, $CFG;
        require_once('info_form.php');

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

        $item->presentation = empty($item->presentation) ? self::MODE_COURSE : $item->presentation;
        $item->required = 0;

                $feedbackitems = feedback_get_depend_candidates_for_item($feedback, $item);
        $commonparams = array('cmid'=>$cm->id,
                             'id'=>isset($item->id) ? $item->id : null,
                             'typ'=>$item->typ,
                             'items'=>$feedbackitems,
                             'feedback'=>$feedback->id);

                $presentationoptions = array();
        if ($feedback->anonymous == FEEDBACK_ANONYMOUS_NO || $item->presentation == self::MODE_RESPONSETIME) {
                                    $presentationoptions[self::MODE_RESPONSETIME] = get_string('responsetime', 'feedback');
        }
        $presentationoptions[self::MODE_COURSE]  = get_string('course');
        $presentationoptions[self::MODE_CATEGORY]  = get_string('coursecategory');

                $this->item_form = new feedback_info_form('edit_item.php',
                                                  array('item'=>$item,
                                                  'common'=>$commonparams,
                                                  'positionlist'=>$positionlist,
                                                  'position' => $position,
                                                  'presentationoptions' => $presentationoptions));
    }

    public function save_item() {
        global $DB;

        if (!$item = $this->item_form->get_data()) {
            return false;
        }

        if (isset($item->clone_item) AND $item->clone_item) {
            $item->id = '';             $item->position++;
        }

        $item->hasvalue = $this->get_hasvalue();
        if (!$item->id) {
            $item->id = $DB->insert_record('feedback_item', $item);
        } else {
            $DB->update_record('feedback_item', $item);
        }

        return $DB->get_record('feedback_item', array('id'=>$item->id));
    }

    
    protected function get_analysed($item, $groupid = false, $courseid = false) {

        $presentation = $item->presentation;
        $analysed_val = new stdClass();
        $analysed_val->data = null;
        $analysed_val->name = $item->name;
        $values = feedback_get_group_values($item, $groupid, $courseid);
        if ($values) {
            $data = array();
            foreach ($values as $value) {
                $datavalue = new stdClass();

                switch($presentation) {
                    case self::MODE_RESPONSETIME:
                        $datavalue->value = $value->value;
                        $datavalue->show = $value->value ? userdate($datavalue->value) : '';
                        break;
                    case self::MODE_COURSE:
                        $datavalue->value = $value->value;
                        $datavalue->show = $datavalue->value;
                        break;
                    case self::MODE_CATEGORY:
                        $datavalue->value = $value->value;
                        $datavalue->show = $datavalue->value;
                        break;
                }

                $data[] = $datavalue;
            }
            $analysed_val->data = $data;
        }
        return $analysed_val;
    }

    public function get_printval($item, $value) {

        if (strval($value->value) === '') {
            return '';
        }
        return $item->presentation == self::MODE_RESPONSETIME ?
                userdate($value->value) : $value->value;
    }

    public function print_analysed($item, $itemnr = '', $groupid = false, $courseid = false) {
        $analysed_item = $this->get_analysed($item, $groupid, $courseid);
        $data = $analysed_item->data;
        if (is_array($data)) {
            echo '<tr><th colspan="2" align="left">';
            echo $itemnr . ' ';
            if (strval($item->label) !== '') {
                echo '('. format_string($item->label).') ';
            }
            echo format_text($item->name, FORMAT_HTML, array('noclean' => true, 'para' => false));
            echo '</th></tr>';
            $sizeofdata = count($data);
            for ($i = 0; $i < $sizeofdata; $i++) {
                $class = strlen(trim($data[$i]->show)) ? '' : ' class="isempty"';
                echo '<tr'.$class.'><td colspan="2" class="singlevalue">';
                echo str_replace("\n", '<br />', $data[$i]->show);
                echo '</td></tr>';
            }
        }
    }

    public function excelprint_item(&$worksheet, $row_offset,
                             $xls_formats, $item,
                             $groupid, $courseid = false) {
        $analysed_item = $this->get_analysed($item, $groupid, $courseid);

        $worksheet->write_string($row_offset, 0, $item->label, $xls_formats->head2);
        $worksheet->write_string($row_offset, 1, $item->name, $xls_formats->head2);
        $data = $analysed_item->data;
        if (is_array($data)) {
            $worksheet->write_string($row_offset, 2, $data[0]->show, $xls_formats->value_bold);
            $row_offset++;
            $sizeofdata = count($data);
            for ($i = 1; $i < $sizeofdata; $i++) {
                $worksheet->write_string($row_offset, 2, $data[$i]->show, $xls_formats->default);
                $row_offset++;
            }
        }
        $row_offset++;
        return $row_offset;
    }

    
    protected function get_current_value($item, $feedback, $courseid) {
        global $DB;
        switch ($item->presentation) {
            case self::MODE_RESPONSETIME:
                if ($feedback->anonymous != FEEDBACK_ANONYMOUS_YES) {
                                        return time();
                }
                break;
            case self::MODE_COURSE:
                $course = get_course($courseid);
                return format_string($course->shortname, true,
                        array('context' => context_course::instance($course->id)));
                break;
            case self::MODE_CATEGORY:
                if ($courseid !== SITEID) {
                    $coursecategory = $DB->get_record_sql('SELECT cc.id, cc.name FROM {course_categories} cc, {course} c '
                            . 'WHERE c.category = cc.id AND c.id = ?', array($courseid));
                    return format_string($coursecategory->name, true,
                            array('context' => context_coursecat::instance($coursecategory->id)));
                }
                break;
        }
        return '';
    }

    
    public function complete_form_element($item, $form) {
        if ($form->get_mode() == mod_feedback_complete_form::MODE_VIEW_RESPONSE) {
            $value = strval($form->get_item_value($item));
        } else {
            $value = $this->get_current_value($item,
                    $form->get_feedback(), $form->get_current_course_id());
        }
        $printval = $this->get_printval($item, (object)['value' => $value]);

        $class = '';
        switch ($item->presentation) {
            case self::MODE_RESPONSETIME:
                $class = 'info-responsetime';
                $value = $value ? self::CURRENTTIMESTAMP : '';
                break;
            case self::MODE_COURSE:
                $class = 'info-course';
                break;
            case self::MODE_CATEGORY:
                $class = 'info-category';
                break;
        }

        $name = $this->get_display_name($item);
        $inputname = $item->typ . '_' . $item->id;

        $element = $form->add_form_element($item,
                ['select', $inputname, $name,
                    array($value => $printval),
                    array('class' => $class)],
                false,
                false);
        $form->set_element_default($inputname, $value);
        $element->freeze();
        if ($form->get_mode() == mod_feedback_complete_form::MODE_COMPLETE) {
            $element->setPersistantFreeze(true);
        }
    }

    
    public function create_value($value) {
        if ($value === self::CURRENTTIMESTAMP) {
            return strval(time());
        }
        return parent::create_value($value);
    }

    public function can_switch_require() {
        return false;
    }
}
