<?php

defined('MOODLE_INTERNAL') OR die('not allowed');
require_once($CFG->dirroot.'/mod/feedback/item/feedback_item_class.php');

define('FEEDBACK_RADIORATED_ADJUST_SEP', '<<<<<');

define('FEEDBACK_MULTICHOICERATED_MAXCOUNT', 10); define('FEEDBACK_MULTICHOICERATED_VALUE_SEP', '####');
define('FEEDBACK_MULTICHOICERATED_VALUE_SEP2', '/');
define('FEEDBACK_MULTICHOICERATED_TYPE_SEP', '>>>>>');
define('FEEDBACK_MULTICHOICERATED_LINE_SEP', '|');
define('FEEDBACK_MULTICHOICERATED_ADJUST_SEP', '<<<<<');
define('FEEDBACK_MULTICHOICERATED_IGNOREEMPTY', 'i');
define('FEEDBACK_MULTICHOICERATED_HIDENOSELECT', 'h');

class feedback_item_multichoicerated extends feedback_item_base {
    protected $type = "multichoicerated";

    public function build_editform($item, $feedback, $cm) {
        global $DB, $CFG;
        require_once('multichoicerated_form.php');

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

        $this->item_form = new feedback_multichoicerated_form('edit_item.php', $customdata);
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
        $analysed_item = array();
        $analysed_item[] = $item->typ;
        $analysed_item[] = $item->name;

                $info = $this->get_info($item);
        $lines = null;
        $lines = explode (FEEDBACK_MULTICHOICERATED_LINE_SEP, $info->presentation);
        if (!is_array($lines)) {
            return null;
        }

                $values = feedback_get_group_values($item, $groupid, $courseid, $this->ignoreempty($item));
        if (!$values) {
            return null;
        }
        
        $analysed_answer = array();
        $sizeoflines = count($lines);
        for ($i = 1; $i <= $sizeoflines; $i++) {
            $item_values = explode(FEEDBACK_MULTICHOICERATED_VALUE_SEP, $lines[$i-1]);
            $ans = new stdClass();
            $ans->answertext = $item_values[1];
            $avg = 0.0;
            $anscount = 0;
            foreach ($values as $value) {
                                if ($value->value == $i) {
                    $avg += $item_values[0];                     $anscount++;
                }
            }
            $ans->answercount = $anscount;
            $ans->avg = doubleval($avg) / doubleval(count($values));
            $ans->value = $item_values[0];
            $ans->quotient = $ans->answercount / count($values);
            $analysed_answer[] = $ans;
        }
        $analysed_item[] = $analysed_answer;
        return $analysed_item;
    }

    public function get_printval($item, $value) {
        $printval = '';

        if (!isset($value->value)) {
            return $printval;
        }

        $info = $this->get_info($item);

        $presentation = explode (FEEDBACK_MULTICHOICERATED_LINE_SEP, $info->presentation);
        $index = 1;
        foreach ($presentation as $pres) {
            if ($value->value == $index) {
                $item_label = explode(FEEDBACK_MULTICHOICERATED_VALUE_SEP, $pres);
                $printval = format_string($item_label[1]);
                break;
            }
            $index++;
        }
        return $printval;
    }

    public function print_analysed($item, $itemnr = '', $groupid = false, $courseid = false) {
        global $OUTPUT;
        $analysed_item = $this->get_analysed($item, $groupid, $courseid);
        if ($analysed_item) {
            echo '<tr><th colspan="2" align="left">';
            echo $itemnr . ' ';
            if (strval($item->label) !== '') {
                echo '('. format_string($item->label).') ';
            }
            echo format_string($analysed_item[1]);
            echo '</th></tr>';
            $analysed_vals = $analysed_item[2];
            $pixnr = 0;
            $avg = 0.0;
            foreach ($analysed_vals as $val) {
                $intvalue = $pixnr % 10;
                $pix = $OUTPUT->pix_url('multichoice/' . $intvalue, 'feedback');
                $pixspacer = $OUTPUT->pix_url('spacer');
                $pixnr++;
                $pixwidth = intval($val->quotient * FEEDBACK_MAX_PIX_LENGTH);
                $pixwidthspacer = FEEDBACK_MAX_PIX_LENGTH + 1 - $pixwidth;

                $avg += $val->avg;
                $quotient = format_float($val->quotient * 100, 2);
                echo '<tr>';
                echo '<td class="optionname">';
                echo '<span class="weight">('.$val->value.') </span>'.
                        format_text(trim($val->answertext), FORMAT_HTML, array('noclean' => true, 'para' => false)).':</td>';
                echo '<td class="optionvalue" style="width: '.FEEDBACK_MAX_PIX_LENGTH.'">';
                echo '<img class="feedback_bar_image" alt="'.$intvalue.'" src="'.$pix.'" width="'.$pixwidth.'" />';
                echo '<img class="feedback_bar_image" alt="" src="'.$pixspacer.'" width="'.$pixwidthspacer.'" /> ';
                echo $val->answercount;
                if ($val->quotient > 0) {
                    echo ' ('.$quotient.' %)';
                } else {
                    echo '';
                }
                echo '</td></tr>';
            }
            $avg = format_float($avg, 2);
            echo '<tr><td align="left" colspan="2"><b>';
            echo get_string('average', 'feedback').': '.$avg.'</b>';
            echo '</td></tr>';
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
            $avg = 0.0;
            $sizeofdata = count($data);
            for ($i = 0; $i < $sizeofdata; $i++) {
                $analysed_data = $data[$i];

                $worksheet->write_string($row_offset,
                                $i + 2,
                                trim($analysed_data->answertext).' ('.$analysed_data->value.')',
                                $xls_formats->value_bold);

                $worksheet->write_number($row_offset + 1,
                                $i + 2,
                                $analysed_data->answercount,
                                $xls_formats->default);

                $avg += $analysed_data->avg;
            }
                        $worksheet->write_string($row_offset,
                                count($data) + 2,
                                get_string('average', 'feedback'),
                                $xls_formats->value_bold);

            $worksheet->write_number($row_offset + 1,
                                count($data) + 2,
                                $avg,
                                $xls_formats->value_bold);
        }
        $row_offset +=2;
        return $row_offset;
    }

    
    protected function get_options($item) {
        $info = $this->get_info($item);
        $lines = explode(FEEDBACK_MULTICHOICERATED_LINE_SEP, $info->presentation);
        $options = array();
        foreach ($lines as $idx => $line) {
            list($weight, $optiontext) = explode(FEEDBACK_MULTICHOICERATED_VALUE_SEP, $line);
            $options[$idx + 1] = format_text("<span class=\"weight\">($weight) </span>".$optiontext,
                    FORMAT_HTML, array('noclean' => true, 'para' => false));
        }
        if ($info->subtype === 'r' && !$this->hidenoselect($item)) {
            $options = array(0 => get_string('not_selected', 'feedback')) + $options;
        }

        return $options;
    }

    
    public function complete_form_element($item, $form) {
        $info = $this->get_info($item);
        $name = $this->get_display_name($item);
        $class = 'multichoicerated-' . $info->subtype;
        $inputname = $item->typ . '_' . $item->id;
        $options = $this->get_options($item);
        if ($info->subtype === 'd' || $form->is_frozen()) {
            $el = $form->add_form_element($item,
                    ['select', $inputname, $name, array('' => '') + $options, array('class' => $class)]);
        } else {
            $objs = array();
            if (!array_key_exists(0, $options)) {
                                $objs[] = ['hidden', $inputname];
            }
            foreach ($options as $idx => $label) {
                $objs[] = ['radio', $inputname, '', $label, $idx];
            }
            $separator = $info->horizontal ? ' ' : '<br>';
            $class .= ' multichoicerated-' . ($info->horizontal ? 'horizontal' : 'vertical');
            $el = $form->add_form_group_element($item, 'group_'.$inputname, $name, $objs, $separator, $class);
            $form->set_element_type($inputname, PARAM_INT);

                        $form->set_element_default($inputname, $form->get_item_value($item));

                        if ($item->required) {
                $form->add_validation_rule(function($values, $files) use ($item) {
                    $inputname = $item->typ . '_' . $item->id;
                    return empty($values[$inputname]) ? array('group_' . $inputname => get_string('required')) : true;
                });
            }
        }
    }

    
    public function compare_value($item, $dbvalue, $dependvalue) {

        if (is_array($dbvalue)) {
            $dbvalues = $dbvalue;
        } else {
            $dbvalues = explode(FEEDBACK_MULTICHOICERATED_LINE_SEP, $dbvalue);
        }

        $info = $this->get_info($item);
        $presentation = explode (FEEDBACK_MULTICHOICERATED_LINE_SEP, $info->presentation);
        $index = 1;
        foreach ($presentation as $pres) {
            $presvalues = explode(FEEDBACK_MULTICHOICERATED_VALUE_SEP, $pres);

            foreach ($dbvalues as $dbval) {
                if ($dbval == $index AND trim($presvalues[1]) == $dependvalue) {
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

        $parts = explode(FEEDBACK_MULTICHOICERATED_TYPE_SEP, $item->presentation);
        @list($info->subtype, $info->presentation) = $parts;

        if (!isset($info->subtype)) {
            $info->subtype = 'r';
        }

        if ($info->subtype != 'd') {
            $parts = explode(FEEDBACK_MULTICHOICERATED_ADJUST_SEP, $info->presentation);
            @list($info->presentation, $info->horizontal) = $parts;

            if (isset($info->horizontal) AND $info->horizontal == 1) {
                $info->horizontal = true;
            } else {
                $info->horizontal = false;
            }
        }

        $info->values = $this->prepare_presentation_values_print($info->presentation,
                                                    FEEDBACK_MULTICHOICERATED_VALUE_SEP,
                                                    FEEDBACK_MULTICHOICERATED_VALUE_SEP2);
        return $info;
    }

    public function prepare_presentation_values($linesep1,
                                         $linesep2,
                                         $valuestring,
                                         $valuesep1,
                                         $valuesep2) {

        $lines = explode($linesep1, $valuestring);
        $newlines = array();
        foreach ($lines as $line) {
            $value = '';
            $text = '';
            if (strpos($line, $valuesep1) === false) {
                $value = 0;
                $text = $line;
            } else {
                @list($value, $text) = explode($valuesep1, $line, 2);
            }

            $value = intval($value);
            $newlines[] = $value.$valuesep2.$text;
        }
        $newlines = implode($linesep2, $newlines);
        return $newlines;
    }

    public function prepare_presentation_values_print($valuestring, $valuesep1, $valuesep2) {
        $valuestring = str_replace(array("\n","\r"), "", $valuestring);
        return $this->prepare_presentation_values(FEEDBACK_MULTICHOICERATED_LINE_SEP,
                                                  "\n",
                                                  $valuestring,
                                                  $valuesep1,
                                                  $valuesep2);
    }

    public function prepare_presentation_values_save($valuestring, $valuesep1, $valuesep2) {
        $valuestring = str_replace("\r", "\n", $valuestring);
        $valuestring = str_replace("\n\n", "\n", $valuestring);
        return $this->prepare_presentation_values("\n",
                        FEEDBACK_MULTICHOICERATED_LINE_SEP,
                        $valuestring,
                        $valuesep1,
                        $valuesep2);
    }

    public function set_ignoreempty($item, $ignoreempty=true) {
        $item->options = str_replace(FEEDBACK_MULTICHOICERATED_IGNOREEMPTY, '', $item->options);
        if ($ignoreempty) {
            $item->options .= FEEDBACK_MULTICHOICERATED_IGNOREEMPTY;
        }
    }

    public function ignoreempty($item) {
        if (strstr($item->options, FEEDBACK_MULTICHOICERATED_IGNOREEMPTY)) {
            return true;
        }
        return false;
    }

    public function set_hidenoselect($item, $hidenoselect=true) {
        $item->options = str_replace(FEEDBACK_MULTICHOICERATED_HIDENOSELECT, '', $item->options);
        if ($hidenoselect) {
            $item->options .= FEEDBACK_MULTICHOICERATED_HIDENOSELECT;
        }
    }

    public function hidenoselect($item) {
        if (strstr($item->options, FEEDBACK_MULTICHOICERATED_HIDENOSELECT)) {
            return true;
        }
        return false;
    }
}
