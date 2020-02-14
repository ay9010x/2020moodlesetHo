<?php

defined('MOODLE_INTERNAL') OR die('not allowed');
require_once($CFG->dirroot.'/mod/feedback/item/feedback_item_class.php');

class feedback_item_numeric extends feedback_item_base {
    protected $type = "numeric";

    public function build_editform($item, $feedback, $cm) {
        global $DB, $CFG;
        require_once('numeric_form.php');

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

        $range_from_to = explode('|', $item->presentation);
        if (isset($range_from_to[0]) AND is_numeric($range_from_to[0])) {
            $range_from = $this->format_float($range_from_to[0]);
        } else {
            $range_from = '-';
        }

        if (isset($range_from_to[1]) AND is_numeric($range_from_to[1])) {
            $range_to = $this->format_float($range_from_to[1]);
        } else {
            $range_to = '-';
        }

        $item->rangefrom = $range_from;
        $item->rangeto = $range_to;

                $feedbackitems = feedback_get_depend_candidates_for_item($feedback, $item);
        $commonparams = array('cmid'=>$cm->id,
                             'id'=>isset($item->id) ? $item->id : null,
                             'typ'=>$item->typ,
                             'items'=>$feedbackitems,
                             'feedback'=>$feedback->id);

                $customdata = array('item' => $item,
                            'common' => $commonparams,
                            'positionlist' => $positionlist,
                            'position' => $position);

        $this->item_form = new feedback_numeric_form('edit_item.php', $customdata);
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
        global $DB;

        $analysed = new stdClass();
        $analysed->data = array();
        $analysed->name = $item->name;
        $values = feedback_get_group_values($item, $groupid, $courseid);

        $avg = 0.0;
        $counter = 0;
        if ($values) {
            $data = array();
            foreach ($values as $value) {
                if (is_numeric($value->value)) {
                    $data[] = $value->value;
                    $avg += $value->value;
                    $counter++;
                }
            }
            $avg = $counter > 0 ? $avg / $counter : null;
            $analysed->data = $data;
            $analysed->avg = $avg;
        }
        return $analysed;
    }

    public function get_printval($item, $value) {
        if (!isset($value->value)) {
            return '';
        }

        return $value->value;
    }

    public function print_analysed($item, $itemnr = '', $groupid = false, $courseid = false) {

        $values = $this->get_analysed($item, $groupid, $courseid);

        if (isset($values->data) AND is_array($values->data)) {
            echo '<tr><th colspan="2" align="left">';
            echo $itemnr . ' ';
            if (strval($item->label) !== '') {
                echo '('. format_string($item->label).') ';
            }
            echo format_text($item->name, FORMAT_HTML, array('noclean' => true, 'para' => false));
            echo '</th></tr>';

            foreach ($values->data as $value) {
                echo '<tr><td colspan="2" class="singlevalue">';
                echo $this->format_float($value);
                echo '</td></tr>';
            }

            if (isset($values->avg)) {
                $avg = format_float($values->avg, 2);
            } else {
                $avg = '-';
            }
            echo '<tr><td colspan="2"><b>';
            echo get_string('average', 'feedback').': '.$avg;
            echo '</b></td></tr>';
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

                        $worksheet->write_string($row_offset,
                                     2,
                                     get_string('average', 'feedback'),
                                     $xls_formats->value_bold);

            if (isset($analysed_item->avg)) {
                $worksheet->write_number($row_offset + 1,
                                         2,
                                         $analysed_item->avg,
                                         $xls_formats->value_bold);
            } else {
                $worksheet->write_string($row_offset + 1,
                                         2,
                                         '',
                                         $xls_formats->value_bold);
            }
            $row_offset++;
        }
        $row_offset++;
        return $row_offset;
    }

    
    protected function format_float($value) {
        if (!is_numeric($value)) {
            return null;
        }
        $decimal = is_int($value) ? 0 : strlen(substr(strrchr($value, '.'), 1));
        return format_float($value, $decimal);
    }

    
    protected function get_boundaries_for_display($item) {
        list($rangefrom, $rangeto) = explode('|', $item->presentation);
        if (!isset($rangefrom) || !is_numeric($rangefrom)) {
            $rangefrom = null;
        }
        if (!isset($rangeto) || !is_numeric($rangeto)) {
            $rangeto = null;
        }

        if (is_null($rangefrom) && is_numeric($rangeto)) {
            return ' (' . get_string('maximal', 'feedback') .
                        ': ' . $this->format_float($rangeto) . ')';
        }
        if (is_numeric($rangefrom) && is_null($rangeto)) {
            return ' (' . get_string('minimal', 'feedback') .
                        ': ' . $this->format_float($rangefrom) . ')';
        }
        if (is_null($rangefrom) && is_null($rangeto)) {
            return '';
        }
        return ' (' . $this->format_float($rangefrom) .
                ' - ' . $this->format_float($rangeto) . ')';
    }

    
    public function get_display_name_postfix($item) {
        return html_writer::span($this->get_boundaries_for_display($item), 'boundaries');
    }

    
    public function complete_form_element($item, $form) {
        $name = $this->get_display_name($item);
        $inputname = $item->typ . '_' . $item->id;
        $form->add_form_element($item,
                ['text', $inputname, $name],
                true,
                false
                );
        $form->set_element_type($inputname, PARAM_NOTAGS);
        $tmpvalue = $this->format_float($form->get_item_value($item));
        $form->set_element_default($inputname, $tmpvalue);

                $form->add_validation_rule(function($values, $files) use ($item) {
            $inputname = $item->typ . '_' . $item->id;
            list($rangefrom, $rangeto) = explode('|', $item->presentation);
            if (!isset($values[$inputname]) || trim($values[$inputname]) === '') {
                return $item->required ? array($inputname => get_string('required')) : true;
            }
            $value = unformat_float($values[$inputname], true);
            if ($value === false) {
                return array($inputname => get_string('invalidnum', 'error'));
            }
            if ((is_numeric($rangefrom) && $value < floatval($rangefrom)) ||
                    (is_numeric($rangeto) && $value > floatval($rangeto))) {
                return array($inputname => get_string('numberoutofrange', 'feedback'));
            }
            return true;
        });
    }

    public function create_value($data) {
        $data = unformat_float($data, true);

        if (is_numeric($data)) {
            $data = floatval($data);
        } else {
            $data = '';
        }
        return $data;
    }
}
