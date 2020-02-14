<?php

defined('MOODLE_INTERNAL') OR die('not allowed');
require_once($CFG->dirroot.'/mod/feedback/item/feedback_item_class.php');

class feedback_item_textfield extends feedback_item_base {
    protected $type = "textfield";

    public function build_editform($item, $feedback, $cm) {
        global $DB, $CFG;
        require_once('textfield_form.php');

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

        $item->presentation = empty($item->presentation) ? '' : $item->presentation;

        $size_and_length = explode('|', $item->presentation);

        if (isset($size_and_length[0]) AND $size_and_length[0] >= 5) {
            $itemsize = $size_and_length[0];
        } else {
            $itemsize = 30;
        }

        $itemlength = isset($size_and_length[1]) ? $size_and_length[1] : 255;

        $item->itemsize = $itemsize;
        $item->itemmaxlength = $itemlength;

                $feedbackitems = feedback_get_depend_candidates_for_item($feedback, $item);
        $commonparams = array('cmid' => $cm->id,
                             'id' => isset($item->id) ? $item->id : null,
                             'typ' => $item->typ,
                             'items' => $feedbackitems,
                             'feedback' => $feedback->id);

                $customdata = array('item' => $item,
                            'common' => $commonparams,
                            'positionlist' => $positionlist,
                            'position' => $position);

        $this->item_form = new feedback_textfield_form('edit_item.php', $customdata);
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

        $analysed_val = new stdClass();
        $analysed_val->data = null;
        $analysed_val->name = $item->name;

        $values = feedback_get_group_values($item, $groupid, $courseid);
        if ($values) {
            $data = array();
            foreach ($values as $value) {
                $data[] = str_replace("\n", '<br />', $value->value);
            }
            $analysed_val->data = $data;
        }
        return $analysed_val;
    }

    public function get_printval($item, $value) {

        if (!isset($value->value)) {
            return '';
        }
        return $value->value;
    }

    public function print_analysed($item, $itemnr = '', $groupid = false, $courseid = false) {
        $values = feedback_get_group_values($item, $groupid, $courseid);
        if ($values) {
            echo '<tr><th colspan="2" align="left">';
            echo $itemnr . ' ';
            if (strval($item->label) !== '') {
                echo '('. format_string($item->label).') ';
            }
            echo $this->get_display_name($item);
            echo '</th></tr>';
            foreach ($values as $value) {
                $class = strlen(trim($value->value)) ? '' : ' class="isempty"';
                echo '<tr'.$class.'><td colspan="2" class="singlevalue">';
                echo str_replace("\n", '<br />', $value->value);
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
            $worksheet->write_string($row_offset, 2, htmlspecialchars_decode($data[0], ENT_QUOTES), $xls_formats->value_bold);
            $row_offset++;
            $sizeofdata = count($data);
            for ($i = 1; $i < $sizeofdata; $i++) {
                $worksheet->write_string($row_offset, 2, htmlspecialchars_decode($data[$i], ENT_QUOTES), $xls_formats->default);
                $row_offset++;
            }
        }
        $row_offset++;
        return $row_offset;
    }

    
    public function complete_form_element($item, $form) {
        $name = $this->get_display_name($item);
        $inputname = $item->typ . '_' . $item->id;
        list($size, $maxlength) = explode ("|", $item->presentation);
        $form->add_form_element($item,
                ['text', $inputname, $name, ['maxlength' => $maxlength, 'size' => $size]]);
        $form->set_element_type($inputname, PARAM_NOTAGS);

        $form->add_element_rule($inputname, get_string('maximumchars', '', $maxlength), 'maxlength', $maxlength, 'client');
    }

    
    public function create_value($value) {
        return s($value);
    }
}
