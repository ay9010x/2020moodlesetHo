<?php


require_once($CFG->dirroot.'/lib/gradelib.php');
require_once($CFG->dirroot.'/grade/lib.php');
require_once($CFG->dirroot.'/grade/export/grade_export_form.php');


abstract class grade_export {

    public $plugin; 
    public $grade_items;     public $groupid;         public $course;          public $columns;     
    public $export_letters;      public $export_feedback;     public $userkey;         
    public $updatedgradesonly; 
    
    public $displaytype;
    public $decimalpoints;     public $onlyactive;     public $usercustomfields; 
    
    public $previewrows;

    
    public function __construct($course, $groupid, $formdata) {
        if (func_num_args() != 3 || ($formdata != null && get_class($formdata) != "stdClass")) {
            $args = func_get_args();
            return call_user_func_array(array($this, "deprecated_constructor"), $args);
        }
        $this->course = $course;
        $this->groupid = $groupid;

        $this->grade_items = grade_item::fetch_all(array('courseid'=>$this->course->id));

        $this->process_form($formdata);
    }

    
    protected function deprecated_constructor($course,
                                              $groupid=0,
                                              $itemlist='',
                                              $export_feedback=false,
                                              $updatedgradesonly = false,
                                              $displaytype = GRADE_DISPLAY_TYPE_REAL,
                                              $decimalpoints = 2,
                                              $onlyactive = false,
                                              $usercustomfields = false) {

        debugging('Many argument constructor for class "grade_export" is deprecated. Call the 3 argument version instead.', DEBUG_DEVELOPER);

        $this->course = $course;
        $this->groupid = $groupid;

        $this->grade_items = grade_item::fetch_all(array('courseid'=>$this->course->id));
                                $this->columns = array();
        if (!empty($itemlist)) {
            if ($itemlist=='-1') {
                            } else {
                $itemids = explode(',', $itemlist);
                                foreach ($itemids as $itemid) {
                    if (array_key_exists($itemid, $this->grade_items)) {
                        $this->columns[$itemid] =& $this->grade_items[$itemid];
                    }
                }
            }
        } else {
            foreach ($this->grade_items as $itemid=>$unused) {
                $this->columns[$itemid] =& $this->grade_items[$itemid];
            }
        }

        $this->export_feedback = $export_feedback;
        $this->userkey         = '';
        $this->previewrows     = false;
        $this->updatedgradesonly = $updatedgradesonly;

        $this->displaytype = $displaytype;
        $this->decimalpoints = $decimalpoints;
        $this->onlyactive = $onlyactive;
        $this->usercustomfields = $usercustomfields;
    }

    
    function process_form($formdata) {
        global $USER;

        $this->columns = array();
        if (!empty($formdata->itemids)) {
            if ($formdata->itemids=='-1') {
                            } else {
                foreach ($formdata->itemids as $itemid=>$selected) {
                    if ($selected and array_key_exists($itemid, $this->grade_items)) {
                        $this->columns[$itemid] =& $this->grade_items[$itemid];
                    }
                }
            }
        } else {
            foreach ($this->grade_items as $itemid=>$unused) {
                $this->columns[$itemid] =& $this->grade_items[$itemid];
            }
        }

        if (isset($formdata->key)) {
            if ($formdata->key == 1 && isset($formdata->iprestriction) && isset($formdata->validuntil)) {
                                $formdata->key = create_user_key('grade/export', $USER->id, $this->course->id, $formdata->iprestriction, $formdata->validuntil);
            }
            $this->userkey = $formdata->key;
        }

        if (isset($formdata->decimals)) {
            $this->decimalpoints = $formdata->decimals;
        }

        if (isset($formdata->export_letters)) {
            $this->export_letters = $formdata->export_letters;
        }

        if (isset($formdata->export_feedback)) {
            $this->export_feedback = $formdata->export_feedback;
        }

        if (isset($formdata->export_onlyactive)) {
            $this->onlyactive = $formdata->export_onlyactive;
        }

        if (isset($formdata->previewrows)) {
            $this->previewrows = $formdata->previewrows;
        }

        if (isset($formdata->display)) {
            $this->displaytype = $formdata->display;

                                    if (is_array($formdata->display)) {
                $this->displaytype = array_filter($formdata->display);
            }
        }

        if (isset($formdata->updatedgradesonly)) {
            $this->updatedgradesonly = $formdata->updatedgradesonly;
        }
    }

    
    public function track_exports() {
        global $CFG;

                if ($expplugins = explode(",", $CFG->gradeexport)) {
            if (in_array($this->plugin, $expplugins)) {
                return true;
            } else {
                return false;
          }
        } else {
            return false;
        }
    }

    
    public function format_grade($grade, $gradedisplayconst = null) {
        $displaytype = $this->displaytype;
        if (is_array($this->displaytype) && !is_null($gradedisplayconst)) {
            $displaytype = $gradedisplayconst;
        }

        $gradeitem = $this->grade_items[$grade->itemid];

                $grademax = $gradeitem->grademax;
        $grademin = $gradeitem->grademin;

                $gradeitem->grademax = $grade->get_grade_max();
        $gradeitem->grademin = $grade->get_grade_min();

        $formattedgrade = grade_format_gradevalue($grade->finalgrade, $gradeitem, false, $displaytype, $this->decimalpoints);

                $gradeitem->grademax = $grademax;
        $gradeitem->grademin = $grademin;

        return $formattedgrade;
    }

    
    public function format_column_name($grade_item, $feedback=false, $gradedisplayname = null) {
        $column = new stdClass();

        if ($grade_item->itemtype == 'mod') {
            $column->name = get_string('modulename', $grade_item->itemmodule).get_string('labelsep', 'langconfig').$grade_item->get_name();
        } else {
            $column->name = $grade_item->get_name();
        }

                $column->extra = ($feedback) ? get_string('feedback') : get_string($gradedisplayname, 'grades');

        return html_to_text(get_string('gradeexportcolumntype', 'grades', $column), 0, false);
    }

    
    public function format_feedback($feedback) {
        return strip_tags(format_text($feedback->feedback, $feedback->feedbackformat));
    }

    
    public abstract function print_grades();

    
    public function display_preview($require_user_idnumber=false) {
        global $OUTPUT;

        debugging('function grade_export::display_preview is deprecated.', DEBUG_DEVELOPER);

        $userprofilefields = grade_helper::get_user_profile_fields($this->course->id, $this->usercustomfields);
        $formatoptions = new stdClass();
        $formatoptions->para = false;

        echo $OUTPUT->heading(get_string('previewrows', 'grades'));

        echo '<table>';
        echo '<tr>';
        foreach ($userprofilefields as $field) {
            echo '<th>' . $field->fullname . '</th>';
        }
        if (!$this->onlyactive) {
            echo '<th>'.get_string("suspended")."</th>";
        }
        foreach ($this->columns as $grade_item) {
            echo '<th>'.$this->format_column_name($grade_item).'</th>';

                        if ($this->export_feedback) {
                echo '<th>'.$this->format_column_name($grade_item, true).'</th>';
            }
        }
        echo '</tr>';
                $i = 0;
        $gui = new graded_users_iterator($this->course, $this->columns, $this->groupid);
        $gui->require_active_enrolment($this->onlyactive);
        $gui->allow_user_custom_fields($this->usercustomfields);
        $gui->init();
        while ($userdata = $gui->next_user()) {
                        if ($this->previewrows and $this->previewrows <= $i) {
                break;
            }
            $user = $userdata->user;
            if ($require_user_idnumber and empty($user->idnumber)) {
                                continue;
            }

            $gradeupdated = false;             $rowstr = '';
            foreach ($this->columns as $itemid=>$unused) {
                $gradetxt = $this->format_grade($userdata->grades[$itemid]);

                                $g = new grade_export_update_buffer();
                $grade_grade = new grade_grade(array('itemid'=>$itemid, 'userid'=>$user->id));
                $status = $g->track($grade_grade);

                if ($this->updatedgradesonly && ($status == 'nochange' || $status == 'unknown')) {
                    $rowstr .= '<td>'.get_string('unchangedgrade', 'grades').'</td>';
                } else {
                    $rowstr .= "<td>$gradetxt</td>";
                    $gradeupdated = true;
                }

                if ($this->export_feedback) {
                    $rowstr .=  '<td>'.$this->format_feedback($userdata->feedbacks[$itemid]).'</td>';
                }
            }

                        if (!$gradeupdated && $this->updatedgradesonly) {
                continue;
            }

            echo '<tr>';
            foreach ($userprofilefields as $field) {
                $fieldvalue = grade_helper::get_user_field_value($user, $field);
                                echo '<td>' . format_text($fieldvalue, FORMAT_MOODLE, $formatoptions) . '</td>';
            }
            if (!$this->onlyactive) {
                $issuspended = ($user->suspendedenrolment) ? get_string('yes') : '';
                echo "<td>$issuspended</td>";
            }
            echo $rowstr;
            echo "</tr>";

            $i++;         }
        echo '</table>';
        $gui->close();
    }

    
    public function get_export_params() {
        $itemids = array_keys($this->columns);
        $itemidsparam = implode(',', $itemids);
        if (empty($itemidsparam)) {
            $itemidsparam = '-1';
        }

                if (!is_array($this->displaytype)) {
            $displaytypes = $this->displaytype;
        } else {
                        $displaytypes = implode(',', $this->displaytype);
        }

        if (!empty($this->updatedgradesonly)) {
            $updatedgradesonly = $this->updatedgradesonly;
        } else {
            $updatedgradesonly = 0;
        }
        $params = array('id'                => $this->course->id,
                        'groupid'           => $this->groupid,
                        'itemids'           => $itemidsparam,
                        'export_letters'    => $this->export_letters,
                        'export_feedback'   => $this->export_feedback,
                        'updatedgradesonly' => $updatedgradesonly,
                        'decimalpoints'     => $this->decimalpoints,
                        'export_onlyactive' => $this->onlyactive,
                        'usercustomfields'  => $this->usercustomfields,
                        'displaytype'       => $displaytypes,
                        'key'               => $this->userkey);

        return $params;
    }

    
    public function print_continue() {
        global $CFG, $OUTPUT;

        debugging('function grade_export::print_continue is deprecated.', DEBUG_DEVELOPER);
        $params = $this->get_export_params();

        echo $OUTPUT->heading(get_string('export', 'grades'));

        echo $OUTPUT->container_start('gradeexportlink');

        if (!$this->userkey) {
                        $url = new moodle_url('/grade/export/'.$this->plugin.'/export.php', $params);
            echo $OUTPUT->single_button($url, get_string('download', 'admin'));

        } else {
            $paramstr = '';
            $sep = '?';
            foreach($params as $name=>$value) {
                $paramstr .= $sep.$name.'='.$value;
                $sep = '&';
            }

            $link = $CFG->wwwroot.'/grade/export/'.$this->plugin.'/dump.php'.$paramstr.'&key='.$this->userkey;

            echo get_string('download', 'admin').': ' . html_writer::link($link, $link);
        }
        echo $OUTPUT->container_end();

        return;
    }

    
    public function get_export_url() {
        return new moodle_url('/grade/export/'.$this->plugin.'/dump.php', $this->get_export_params());
    }

    
    public static function convert_flat_displaytypes_to_array($displaytypes) {
        $types = array();

                if (is_int($displaytypes)) {
            $displaytype = clean_param($displaytypes, PARAM_INT);

                        $display[$displaytype] = 1;
        } else {
                        $display = array_flip(explode(',', $displaytypes));
        }

                foreach ($display as $type => $value) {
            $type = clean_param($type, PARAM_INT);
            if ($type == GRADE_DISPLAY_TYPE_LETTER) {
                $types['letter'] = GRADE_DISPLAY_TYPE_LETTER;
            } else if ($type == GRADE_DISPLAY_TYPE_PERCENTAGE) {
                $types['percentage'] = GRADE_DISPLAY_TYPE_PERCENTAGE;
            } else if ($type == GRADE_DISPLAY_TYPE_REAL) {
                $types['real'] = GRADE_DISPLAY_TYPE_REAL;
            } else if ($type == GRADE_DISPLAY_TYPE_REAL_PERCENTAGE) {
                $types['real'] = GRADE_DISPLAY_TYPE_REAL;
                $types['percentage'] = GRADE_DISPLAY_TYPE_PERCENTAGE;
            } else if ($type == GRADE_DISPLAY_TYPE_REAL_LETTER) {
                $types['real'] = GRADE_DISPLAY_TYPE_REAL;
                $types['letter'] = GRADE_DISPLAY_TYPE_LETTER;
            } else if ($type == GRADE_DISPLAY_TYPE_LETTER_REAL) {
                $types['letter'] = GRADE_DISPLAY_TYPE_LETTER;
                $types['real'] = GRADE_DISPLAY_TYPE_REAL;
            } else if ($type == GRADE_DISPLAY_TYPE_LETTER_PERCENTAGE) {
                $types['letter'] = GRADE_DISPLAY_TYPE_LETTER;
                $types['percentage'] = GRADE_DISPLAY_TYPE_PERCENTAGE;
            } else if ($type == GRADE_DISPLAY_TYPE_PERCENTAGE_LETTER) {
                $types['percentage'] = GRADE_DISPLAY_TYPE_PERCENTAGE;
                $types['letter'] = GRADE_DISPLAY_TYPE_LETTER;
            } else if ($type == GRADE_DISPLAY_TYPE_PERCENTAGE_REAL) {
                $types['percentage'] = GRADE_DISPLAY_TYPE_PERCENTAGE;
                $types['real'] = GRADE_DISPLAY_TYPE_REAL;
            }
        }
        return $types;
    }

    
    public static function convert_flat_itemids_to_array($itemids) {
        $items = array();

                if (is_int($itemids)) {
            $itemid = clean_param($itemids, PARAM_INT);
            $items[$itemid] = 1;
        } else {
                        $items = array_flip(explode(',', $itemids));
            foreach ($items as $itemid => $value) {
                $itemid = clean_param($itemid, PARAM_INT);
                $items[$itemid] = 1;
            }
        }
        return $items;
    }

    
    public function get_grade_publishing_url() {
        $url = $this->get_export_url();
        $output =  html_writer::start_div();
        $output .= html_writer::tag('p', get_string('gradepublishinglink', 'grades', html_writer::link($url, $url)));
        $output .=  html_writer::end_div();
        return $output;
    }

    
    public static function export_bulk_export_data($id, $itemids, $exportfeedback, $onlyactive, $displaytype,
                                                   $decimalpoints, $updatedgradesonly = null, $separator = null) {

        $formdata = new \stdClass();
        $formdata->id = $id;
        $formdata->itemids = self::convert_flat_itemids_to_array($itemids);
        $formdata->exportfeedback = $exportfeedback;
        $formdata->export_onlyactive = $onlyactive;
        $formdata->display = self::convert_flat_displaytypes_to_array($displaytype);
        $formdata->decimals = $decimalpoints;

        if (!empty($updatedgradesonly)) {
            $formdata->updatedgradesonly = $updatedgradesonly;
        }

        if (!empty($separator)) {
            $formdata->separator = $separator;
        }

        return $formdata;
    }
}


class grade_export_update_buffer {
    public $update_list;
    public $export_time;

    
    public function __construct() {
        $this->update_list = array();
        $this->export_time = time();
    }

    
    public function grade_export_update_buffer() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    public function flush($buffersize) {
        global $CFG, $DB;

        if (count($this->update_list) > $buffersize) {
            list($usql, $params) = $DB->get_in_or_equal($this->update_list);
            $params = array_merge(array($this->export_time), $params);

            $sql = "UPDATE {grade_grades} SET exported = ? WHERE id $usql";
            $DB->execute($sql, $params);
            $this->update_list = array();
        }
    }

    
    public function track($grade_grade) {

        if (empty($grade_grade->exported) or empty($grade_grade->timemodified)) {
            if (is_null($grade_grade->finalgrade)) {
                                $status = 'unknown';
            } else {
                $status = 'new';
                $this->update_list[] = $grade_grade->id;
            }

        } else if ($grade_grade->exported < $grade_grade->timemodified) {
            $status = 'regrade';
            $this->update_list[] = $grade_grade->id;

        } else if ($grade_grade->exported >= $grade_grade->timemodified) {
            $status = 'nochange';

        } else {
                        $status = 'unknown';
        }

        $this->flush(100);

        return $status;
    }

    
    public function close() {
        $this->flush(0);
    }
}


function export_verify_grades($courseid) {
    if (grade_needs_regrade_final_grades($courseid)) {
        throw new moodle_exception('gradesneedregrading', 'grades');
    }
}
