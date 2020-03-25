<?php

defined('MOODLE_INTERNAL') OR die('not allowed');
require_once($CFG->dirroot.'/mod/feedback/item/feedback_item_class.php');

define('FEEDBACK_MULTICHOICE_TYPE_SEP', '>>>>>');
define('FEEDBACK_MULTICHOICE_LINE_SEP', '|');
define('FEEDBACK_MULTICHOICE_ADJUST_SEP', '<<<<<');
define('FEEDBACK_MULTICHOICE_IGNOREEMPTY', 'i');
define('FEEDBACK_MULTICHOICE_HIDENOSELECT', 'h');

class feedback_item_multichoice extends feedback_item_base {
    protected $type = "multichoice";

    public function build_editform($item, $feedback, $cm) {
        global $DB, $CFG;
        require_once('multichoice_form.php');

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
        $info = $this->get_info($item);

        $item->ignoreempty = $this->ignoreempty($item);
        $item->hidenoselect = $this->hidenoselect($item);

                $feedbackitems = feedback_get_depend_candidates_for_item($feedback, $item);
        $commonparams = array('cmid'=>$cm->id,
                             'id'=>isset($item->id) ? $item->id : null,
                             'typ'=>$item->typ,
                             'items'=>$feedbackitems,
                             'feedback'=>$feedback->id);

                $customdata = array('item' => $item,
                            'common' => $commonparams,
                            'positionlist' => $positionlist,
                            'position' => $position,
                            'info' => $info);

        $this->item_form = new feedback_multichoice_form('edit_item.php', $customdata);
    }

    public function save_item() {
        global $DB;

        if (!$item = $this->item_form->get_data()) {
            return false;
        }

        if (isset($item->clone_item) AND $item->clone_item) {
            $item->id = '';             $item->position++;
        }

        $this->set_ignoreempty($item, $item->ignoreempty);
        $this->set_hidenoselect($item, $item->hidenoselect);

        $item->hasvalue = $this->get_hasvalue();
        if (!$item->id) {
            $item->id = $DB->insert_record('feedback_item', $item);
        } else {
            $DB->update_record('feedback_item', $item);
        }

        return $DB->get_record('feedback_item', array('id'=>$item->id));
    }


        
    
    protected function get_analysed($item, $groupid = false, $courseid = false) {
        $info = $this->get_info($item);

        $analysed_item = array();
        $analysed_item[] = $item->typ;
        $analysed_item[] = format_string($item->name);

                $answers = null;
        $answers = explode (FEEDBACK_MULTICHOICE_LINE_SEP, $info->presentation);
        if (!is_array($answers)) {
            return null;
        }

                $values = feedback_get_group_values($item, $groupid, $courseid, $this->ignoreempty($item));
        if (!$values) {
            return null;
        }

                $analysed_answer = array();
        if ($info->subtype == 'c') {
            $sizeofanswers = count($answers);
            for ($i = 1; $i <= $sizeofanswers; $i++) {
                $ans = new stdClass();
                $ans->answertext = $answers[$i-1];
                $ans->answercount = 0;
                foreach ($values as $value) {
                                        $vallist = explode(FEEDBACK_MULTICHOICE_LINE_SEP, $value->value);
                    foreach ($vallist as $val) {
                        if ($val == $i) {
                            $ans->answercount++;
                        }
                    }
                }
                $ans->quotient = $ans->answercount / count($values);
                $analysed_answer[] = $ans;
            }
        } else {
            $sizeofanswers = count($answers);
            for ($i = 1; $i <= $sizeofanswers; $i++) {
                $ans = new stdClass();
                $ans->answertext = $answers[$i-1];
                $ans->answercount = 0;
                foreach ($values as $value) {
                                        if ($value->value == $i) {
                        $ans->answercount++;
                    }
                }
                $ans->quotient = $ans->answercount / count($values);
                $analysed_answer[] = $ans;
            }
        }
        $analysed_item[] = $analysed_answer;
        return $analysed_item;
    }

    public function get_printval($item, $value) {
        $info = $this->get_info($item);

        $printval = '';

        if (!isset($value->value)) {
            return $printval;
        }

        $presentation = explode (FEEDBACK_MULTICHOICE_LINE_SEP, $info->presentation);

        if ($info->subtype == 'c') {
            $vallist = array_values(explode (FEEDBACK_MULTICHOICE_LINE_SEP, $value->value));
            $sizeofvallist = count($vallist);
            $sizeofpresentation = count($presentation);
            for ($i = 0; $i < $sizeofvallist; $i++) {
                for ($k = 0; $k < $sizeofpresentation; $k++) {
                    if ($vallist[$i] == ($k + 1)) {                        $printval .= trim(format_string($presentation[$k])) . chr(10);
                        break;
                    }
                }
            }
        } else {
            $index = 1;
            foreach ($presentation as $pres) {
                if ($value->value == $index) {
                    $printval = format_string($pres);
                    break;
                }
                $index++;
            }
        }
        return $printval;
    }

    public function print_analysed($item, $itemnr = '', $groupid = false, $courseid = false) {
        global $OUTPUT;

        $analysed_item = $this->get_analysed($item, $groupid, $courseid);
        if ($analysed_item) {
            $itemname = $analysed_item[1];
            echo '<tr><th colspan="2" align="left">';
            echo $itemnr . ' ';
            if (strval($item->label) !== '') {
                echo '('. format_string($item->label).') ';
            }
            echo format_string($itemname);
            echo '</th></tr>';

            $analysed_vals = $analysed_item[2];
            $pixnr = 0;
            foreach ($analysed_vals as $val) {
                $intvalue = $pixnr % 10;
                $pix = $OUTPUT->pix_url('multichoice/' . $intvalue, 'feedback');
                $pixspacer = $OUTPUT->pix_url('spacer');
                $pixnr++;
                $pixwidth = max(2, intval($val->quotient * FEEDBACK_MAX_PIX_LENGTH));
                $pixwidthspacer = FEEDBACK_MAX_PIX_LENGTH + 1 - $pixwidth;
                $quotient = format_float($val->quotient * 100, 2);
                $str_quotient = '';
                if ($val->quotient > 0) {
                    $str_quotient = ' ('. $quotient . ' %)';
                }
                echo '<tr>';
                echo '<td class="optionname">' .
                            format_text(trim($val->answertext), FORMAT_HTML, array('noclean' => true, 'para' => false)).':
                      </td>
                      <td class="optioncount" style="width:'.FEEDBACK_MAX_PIX_LENGTH.';">
                        <img class="feedback_bar_image" alt="'.$intvalue.'" src="'.$pix.'" width="'.$pixwidth.'" />'.
                        '<img class="feedback_bar_image" alt="" src="'.$pixspacer.'" width="'.$pixwidthspacer.'" />
                        '.$val->answercount.$str_quotient.'
                      </td>';
                echo '</tr>';
            }
        }
    }

    public function excelprint_item(&$worksheet, $row_offset,
                             $xls_formats, $item,
                             $groupid, $courseid = false) {

        $analysed_item = $this->get_analysed($item, $groupid, $courseid);

        $data = $analysed_item[2];

                $worksheet->write_string($row_offset, 0, $item->label, $xls_formats->head2);
        $worksheet->write_string($row_offset, 1, $analysed_item[1], $xls_formats->head2);
        if (is_array($data)) {
            $sizeofdata = count($data);
            for ($i = 0; $i < $sizeofdata; $i++) {
                $analysed_data = $data[$i];

                $worksheet->write_string($row_offset,
                                         $i + 2,
                                         trim($analysed_data->answertext),
                                         $xls_formats->head2);

                $worksheet->write_number($row_offset + 1,
                                         $i + 2,
                                         $analysed_data->answercount,
                                         $xls_formats->default);

                $worksheet->write_number($row_offset + 2,
                                         $i + 2,
                                         $analysed_data->quotient,
                                         $xls_formats->procent);
            }
        }
        $row_offset += 3;
        return $row_offset;
    }

    
    protected function get_options($item) {
        $info = $this->get_info($item);
        $presentation = explode (FEEDBACK_MULTICHOICE_LINE_SEP, $info->presentation);
        $options = array();
        foreach ($presentation as $idx => $optiontext) {
            $options[$idx + 1] = format_text($optiontext, FORMAT_HTML, array('noclean' => true, 'para' => false));
        }
        if ($info->subtype === 'r' && !$this->hidenoselect($item)) {
            $options = array(0 => get_string('not_selected', 'feedback')) + $options;
        }

        return $options;
    }

    
    public function complete_form_element($item, $form) {
        $info = $this->get_info($item);
        $name = $this->get_display_name($item);
        $class = 'multichoice-' . $info->subtype;
        $inputname = $item->typ . '_' . $item->id;
        $options = $this->get_options($item);
        $separator = !empty($info->horizontal) ? ' ' : '<br>';
        $tmpvalue = $form->get_item_value($item);

        if ($info->subtype === 'd' || ($info->subtype === 'r' && $form->is_frozen())) {
                        $element = $form->add_form_element($item,
                    ['select', $inputname.'[0]', $name, array(0 => '') + $options, array('class' => $class)],
                    false, false);
            $form->set_element_default($inputname.'[0]', $tmpvalue);
        } else if ($info->subtype === 'c' && $form->is_frozen()) {
                        $objs = [];
            foreach (explode(FEEDBACK_MULTICHOICE_LINE_SEP, $form->get_item_value($item)) as $v) {
                $objs[] = ['static', $inputname."[$v]", '', isset($options[$v]) ? $options[$v] : ''];
            }
            $element = $form->add_form_group_element($item, 'group_'.$inputname, $name, $objs, $separator, $class);
        } else {
                        $class .= ' multichoice-' . ($info->horizontal ? 'horizontal' : 'vertical');
            $objs = [];
            if ($info->subtype === 'c') {
                                $objs[] = ['hidden', $inputname.'[0]', 0];
                $form->set_element_type($inputname.'[0]', PARAM_INT);
                foreach ($options as $idx => $label) {
                    $objs[] = ['advcheckbox', $inputname.'['.$idx.']', '', $label, null, array(0, $idx)];
                    $form->set_element_type($inputname.'['.$idx.']', PARAM_INT);
                }
                $element = $form->add_form_group_element($item, 'group_'.$inputname, $name, $objs, $separator, $class);
                if ($tmpvalue) {
                    foreach (explode(FEEDBACK_MULTICHOICE_LINE_SEP, $tmpvalue) as $v) {
                        $form->set_element_default($inputname.'['.$v.']', $v);
                    }
                }
            } else {
                                if (!array_key_exists(0, $options)) {
                                        $objs[] = ['hidden', $inputname.'[0]'];
                }
                foreach ($options as $idx => $label) {
                    $objs[] = ['radio', $inputname.'[0]', '', $label, $idx];
                }
                $element = $form->add_form_group_element($item, 'group_'.$inputname, $name, $objs, $separator, $class);
                $form->set_element_default($inputname.'[0]', $tmpvalue);
                $form->set_element_type($inputname.'[0]', PARAM_INT);
            }
        }

                if ($item->required) {
            $elementname = $element->getName();
            $form->add_validation_rule(function($values, $files) use ($elementname, $item) {
                $inputname = $item->typ . '_' . $item->id;
                return empty($values[$inputname]) || !array_filter($values[$inputname]) ?
                    array($elementname => get_string('required')) : true;
            });
        }
    }

    
    public function create_value($value) {
        $value = array_unique(array_filter($value));
        return join(FEEDBACK_MULTICHOICE_LINE_SEP, $value);
    }

    
    public function compare_value($item, $dbvalue, $dependvalue) {

        if (is_array($dbvalue)) {
            $dbvalues = $dbvalue;
        } else {
            $dbvalues = explode(FEEDBACK_MULTICHOICE_LINE_SEP, $dbvalue);
        }

        $info = $this->get_info($item);
        $presentation = explode (FEEDBACK_MULTICHOICE_LINE_SEP, $info->presentation);
        $index = 1;
        foreach ($presentation as $pres) {
            foreach ($dbvalues as $dbval) {
                if ($dbval == $index AND trim($pres) == $dependvalue) {
                    return true;
                }
            }
            $index++;
        }
        return false;
    }

    public function get_info($item) {
        $presentation = empty($item->presentation) ? '' : $item->presentation;

        $info = new stdClass();
                        $info->subtype = '';
        $info->presentation = '';
        $info->horizontal = false;

        $parts = explode(FEEDBACK_MULTICHOICE_TYPE_SEP, $item->presentation);
        @list($info->subtype, $info->presentation) = $parts;
        if (!isset($info->subtype)) {
            $info->subtype = 'r';
        }

        if ($info->subtype != 'd') {
            $parts = explode(FEEDBACK_MULTICHOICE_ADJUST_SEP, $info->presentation);
            @list($info->presentation, $info->horizontal) = $parts;
            if (isset($info->horizontal) AND $info->horizontal == 1) {
                $info->horizontal = true;
            } else {
                $info->horizontal = false;
            }
        }
        return $info;
    }

    public function set_ignoreempty($item, $ignoreempty=true) {
        $item->options = str_replace(FEEDBACK_MULTICHOICE_IGNOREEMPTY, '', $item->options);
        if ($ignoreempty) {
            $item->options .= FEEDBACK_MULTICHOICE_IGNOREEMPTY;
        }
    }

    public function ignoreempty($item) {
        if (strstr($item->options, FEEDBACK_MULTICHOICE_IGNOREEMPTY)) {
            return true;
        }
        return false;
    }

    public function set_hidenoselect($item, $hidenoselect=true) {
        $item->options = str_replace(FEEDBACK_MULTICHOICE_HIDENOSELECT, '', $item->options);
        if ($hidenoselect) {
            $item->options .= FEEDBACK_MULTICHOICE_HIDENOSELECT;
        }
    }

    public function hidenoselect($item) {
        if (strstr($item->options, FEEDBACK_MULTICHOICE_HIDENOSELECT)) {
            return true;
        }
        return false;
    }
}
