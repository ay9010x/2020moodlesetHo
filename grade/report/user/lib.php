<?php



require_once($CFG->dirroot . '/grade/report/lib.php');
require_once($CFG->libdir.'/tablelib.php');

define("GRADE_REPORT_USER_HIDE_HIDDEN", 0);
define("GRADE_REPORT_USER_HIDE_UNTIL", 1);
define("GRADE_REPORT_USER_SHOW_HIDDEN", 2);


class grade_report_user extends grade_report {

    
    public $user;

    
    public $table;

    
    public $tableheaders = array();

    
    public $tablecolumns = array();

    
    public $tabledata = array();

    
    public $gtree;

    
    public $gseq;

    
    public $showrank;

    
    public $showpercentage;

    
    public $showrange = true;

    
    public $showgrade = true;

    
    public $decimals = 2;

    
    public $rangedecimals = 0;

    
    public $showfeedback = true;

    
    public $showweight = true;

    
    public $showlettergrade = false;

    
    public $showcontributiontocoursetotal = true;

    
    public $showaverage = false;

    public $maxdepth;
    public $evenodd;

    public $canviewhidden;

    public $switch;

    
    public $showhiddenitems;
    public $showtotalsifcontainhidden;

    public $baseurl;
    public $pbarurl;

    
    protected $modinfo = null;

    
    protected $viewasuser = false;

    
    protected $aggregationhints = array();

    
    public function __construct($courseid, $gpr, $context, $userid, $viewasuser = null) {
        global $DB, $CFG;
        parent::__construct($courseid, $gpr, $context);

        $this->showrank        = grade_get_setting($this->courseid, 'report_user_showrank', $CFG->grade_report_user_showrank);
        $this->showpercentage  = grade_get_setting($this->courseid, 'report_user_showpercentage', $CFG->grade_report_user_showpercentage);
        $this->showhiddenitems = grade_get_setting($this->courseid, 'report_user_showhiddenitems', $CFG->grade_report_user_showhiddenitems);
        $this->showtotalsifcontainhidden = array($this->courseid => grade_get_setting($this->courseid, 'report_user_showtotalsifcontainhidden', $CFG->grade_report_user_showtotalsifcontainhidden));

        $this->showgrade       = grade_get_setting($this->courseid, 'report_user_showgrade',       !empty($CFG->grade_report_user_showgrade));
        $this->showrange       = grade_get_setting($this->courseid, 'report_user_showrange',       !empty($CFG->grade_report_user_showrange));
        $this->showfeedback    = grade_get_setting($this->courseid, 'report_user_showfeedback',    !empty($CFG->grade_report_user_showfeedback));

        $this->showweight = grade_get_setting($this->courseid, 'report_user_showweight',
            !empty($CFG->grade_report_user_showweight));

        $this->showcontributiontocoursetotal = grade_get_setting($this->courseid, 'report_user_showcontributiontocoursetotal',
            !empty($CFG->grade_report_user_showcontributiontocoursetotal));

        $this->showlettergrade = grade_get_setting($this->courseid, 'report_user_showlettergrade', !empty($CFG->grade_report_user_showlettergrade));
        $this->showaverage     = grade_get_setting($this->courseid, 'report_user_showaverage',     !empty($CFG->grade_report_user_showaverage));

        $this->viewasuser = $viewasuser;

                $defaultdecimals = 2;
        if (property_exists($CFG, 'grade_decimalpoints')) {
            $defaultdecimals = $CFG->grade_decimalpoints;
        }
        $this->decimals = grade_get_setting($this->courseid, 'decimalpoints', $defaultdecimals);

                $defaultrangedecimals = 0;
        if (property_exists($CFG, 'grade_report_user_rangedecimals')) {
            $defaultrangedecimals = $CFG->grade_report_user_rangedecimals;
        }
        $this->rangedecimals = grade_get_setting($this->courseid, 'report_user_rangedecimals', $defaultrangedecimals);

        $this->switch = grade_get_setting($this->courseid, 'aggregationposition', $CFG->grade_aggregationposition);

                $this->gtree = new grade_tree($this->courseid, false, $this->switch, null, !$CFG->enableoutcomes);

                $this->user = $DB->get_record('user', array('id' => $userid));

                $coursecontext = context_course::instance($this->courseid);
        if ($viewasuser) {
            $this->modinfo = new course_modinfo($this->course, $this->user->id);
            $this->canviewhidden = has_capability('moodle/grade:viewhidden', $coursecontext, $this->user->id);
        } else {
            $this->modinfo = $this->gtree->modinfo;
            $this->canviewhidden = has_capability('moodle/grade:viewhidden', $coursecontext);
        }

                $this->maxdepth = 1;
        $this->inject_rowspans($this->gtree->top_element);
        $this->maxdepth++;         for ($i = 1; $i <= $this->maxdepth; $i++) {
            $this->evenodd[$i] = 0;
        }

        $this->tabledata = array();

                $this->baseurl = $CFG->wwwroot.'/grade/report?id='.$courseid.'&amp;userid='.$userid;
        $this->pbarurl = $this->baseurl;

                $this->setup_table();

                $this->calculate_averages();
    }

    
    function inject_rowspans(&$element) {

        if ($element['depth'] > $this->maxdepth) {
            $this->maxdepth = $element['depth'];
        }
        if (empty($element['children'])) {
            return 1;
        }
        $count = 1;

        foreach ($element['children'] as $key=>$child) {
                        if ($child['type'] == 'category' && $child['object']->is_hidden() && !$this->canviewhidden
                    && ($this->showhiddenitems == GRADE_REPORT_USER_HIDE_HIDDEN
                    || ($this->showhiddenitems == GRADE_REPORT_USER_HIDE_UNTIL && !$child['object']->is_hiddenuntil()))) {
                                $this->inject_rowspans($element['children'][$key]);
            } else {
                $count += $this->inject_rowspans($element['children'][$key]);
            }
        }

        $element['rowspan'] = $count;
        return $count;
    }


    
    public function setup_table() {
        

        
        $this->tablecolumns = array('itemname');
        $this->tableheaders = array($this->get_lang_string('gradeitem', 'grades'));

        if ($this->showweight) {
            $this->tablecolumns[] = 'weight';
            $this->tableheaders[] = $this->get_lang_string('weightuc', 'grades');
        }

        if ($this->showgrade) {
            $this->tablecolumns[] = 'grade';
            $this->tableheaders[] = $this->get_lang_string('grade', 'grades');
        }

        if ($this->showrange) {
            $this->tablecolumns[] = 'range';
            $this->tableheaders[] = $this->get_lang_string('range', 'grades');
        }

        if ($this->showpercentage) {
            $this->tablecolumns[] = 'percentage';
            $this->tableheaders[] = $this->get_lang_string('percentage', 'grades');
        }

        if ($this->showlettergrade) {
            $this->tablecolumns[] = 'lettergrade';
            $this->tableheaders[] = $this->get_lang_string('lettergrade', 'grades');
        }

        if ($this->showrank) {
            $this->tablecolumns[] = 'rank';
            $this->tableheaders[] = $this->get_lang_string('rank', 'grades');
        }

        if ($this->showaverage) {
            $this->tablecolumns[] = 'average';
            $this->tableheaders[] = $this->get_lang_string('average', 'grades');
        }

        if ($this->showfeedback) {
            $this->tablecolumns[] = 'feedback';
            $this->tableheaders[] = $this->get_lang_string('feedback', 'grades');
        }

        if ($this->showcontributiontocoursetotal) {
            $this->tablecolumns[] = 'contributiontocoursetotal';
            $this->tableheaders[] = $this->get_lang_string('contributiontocoursetotal', 'grades');
        }
    }

    function fill_table() {
                        $this->fill_table_recursive($this->gtree->top_element);
                        return true;
    }

    
    private function fill_table_recursive(&$element) {
        global $DB, $CFG;

        $type = $element['type'];
        $depth = $element['depth'];
        $grade_object = $element['object'];
        $eid = $grade_object->id;
        $element['userid'] = $this->user->id;
        $fullname = $this->gtree->get_element_header($element, true, true, true, true, true);
        $data = array();
        $hidden = '';
        $excluded = '';
        $itemlevel = ($type == 'categoryitem' || $type == 'category' || $type == 'courseitem') ? $depth : ($depth + 1);
        $class = 'level' . $itemlevel . ' level' . ($itemlevel % 2 ? 'odd' : 'even');
        $classfeedback = '';

                if ($type == 'category' && $grade_object->is_hidden() && !$this->canviewhidden && (
                $this->showhiddenitems == GRADE_REPORT_USER_HIDE_HIDDEN ||
                ($this->showhiddenitems == GRADE_REPORT_USER_HIDE_UNTIL && !$grade_object->is_hiddenuntil()))) {
            return false;
        }

        if ($type == 'category') {
            $this->evenodd[$depth] = (($this->evenodd[$depth] + 1) % 2);
        }
        $alter = ($this->evenodd[$depth] == 0) ? 'even' : 'odd';

                if ($type == 'item' or $type == 'categoryitem' or $type == 'courseitem') {
            $header_row = "row_{$eid}_{$this->user->id}";
            $header_cat = "cat_{$grade_object->categoryid}_{$this->user->id}";

            if (! $grade_grade = grade_grade::fetch(array('itemid'=>$grade_object->id,'userid'=>$this->user->id))) {
                $grade_grade = new grade_grade();
                $grade_grade->userid = $this->user->id;
                $grade_grade->itemid = $grade_object->id;
            }

            $grade_grade->load_grade_item();

                        if ($grade_grade->grade_item->is_hidden()) {
                $hidden = ' dimmed_text';
            }

            $hide = false;
                        if ($grade_grade->is_hidden() && !$this->canviewhidden && (
                    $this->showhiddenitems == GRADE_REPORT_USER_HIDE_HIDDEN ||
                    ($this->showhiddenitems == GRADE_REPORT_USER_HIDE_UNTIL && !$grade_grade->is_hiddenuntil()))) {
                $hide = true;
            } else if (!empty($grade_object->itemmodule) && !empty($grade_object->iteminstance)) {
                                                                $instances = $this->modinfo->get_instances_of($grade_object->itemmodule);
                if (!empty($instances[$grade_object->iteminstance])) {
                    $cm = $instances[$grade_object->iteminstance];
                    if (!$cm->uservisible) {
                                                                        if (!$cm->availableinfo) {
                            $hide = true;
                        }
                    }
                }
            }

                        $gradeval = $grade_grade->finalgrade;
            $hint = $grade_grade->get_aggregation_hint();
            if (!$this->canviewhidden) {
                                $adjustedgrade = $this->blank_hidden_total_and_adjust_bounds($this->courseid,
                                                                             $grade_grade->grade_item,
                                                                             $gradeval);

                $gradeval = $adjustedgrade['grade'];

                                                $grade_grade->grade_item->grademax = $adjustedgrade['grademax'];
                $grade_grade->grade_item->grademin = $adjustedgrade['grademin'];
                $hint['status'] = $adjustedgrade['aggregationstatus'];
                $hint['weight'] = $adjustedgrade['aggregationweight'];
            } else {
                                if (!is_null($gradeval)) {
                    $grade_grade->grade_item->grademax = $grade_grade->get_grade_max();
                    $grade_grade->grade_item->grademin = $grade_grade->get_grade_min();
                }
            }


            if (!$hide) {
                                

                                $class .= $hidden . $excluded;
                if ($this->switch) {                    $class .= ($type == 'categoryitem' or $type == 'courseitem') ? " ".$alter."d$depth baggt b2b" : " item b1b";
                } else {
                   $class .= ($type == 'categoryitem' or $type == 'courseitem') ? " ".$alter."d$depth baggb" : " item b1b";
                }
                if ($type == 'categoryitem' or $type == 'courseitem') {
                    $header_cat = "cat_{$grade_object->iteminstance}_{$this->user->id}";
                }

                                $data['itemname']['content'] = $fullname;
                $data['itemname']['class'] = $class;
                $data['itemname']['colspan'] = ($this->maxdepth - $depth);
                $data['itemname']['celltype'] = 'th';
                $data['itemname']['id'] = $header_row;

                if ($this->showfeedback) {
                                        $classfeedback = $class;
                }
                $class .= " itemcenter ";
                if ($this->showweight) {
                    $data['weight']['class'] = $class;
                    $data['weight']['content'] = '-';
                    $data['weight']['headers'] = "$header_cat $header_row weight";
                    
                                        if (is_numeric($hint['weight'])) {
                        $data['weight']['content'] = format_float($hint['weight'] * 100.0, 2) . ' %';
                    }
                    if ($hint['status'] != 'used' && $hint['status'] != 'unknown') {
                        $data['weight']['content'] .= '<br>' . get_string('aggregationhint' . $hint['status'], 'grades');
                    }
                }

                if ($this->showgrade) {
                    if ($grade_grade->grade_item->needsupdate) {
                        $data['grade']['class'] = $class.' gradingerror';
                        $data['grade']['content'] = get_string('error');
                    } else if (!empty($CFG->grade_hiddenasdate) and $grade_grade->get_datesubmitted() and !$this->canviewhidden and $grade_grade->is_hidden()
                           and !$grade_grade->grade_item->is_category_item() and !$grade_grade->grade_item->is_course_item()) {
                                                $class .= ' datesubmitted';
                        $data['grade']['class'] = $class;
                        $data['grade']['content'] = get_string('submittedon', 'grades', userdate($grade_grade->get_datesubmitted(), get_string('strftimedatetimeshort')));

                    } else if ($grade_grade->is_hidden()) {
                        $data['grade']['class'] = $class.' dimmed_text';
                        $data['grade']['content'] = '-';
                        if ($this->canviewhidden) {
                            $data['grade']['content'] = grade_format_gradevalue($gradeval,
                                                                                $grade_grade->grade_item,
                                                                                true);
                        }
                    } else {
                        $data['grade']['class'] = $class;
                        $data['grade']['content'] = grade_format_gradevalue($gradeval,
                                                                            $grade_grade->grade_item,
                                                                            true);
                    }
                    $data['grade']['headers'] = "$header_cat $header_row grade";
                }

                                if ($this->showrange) {
                    $data['range']['class'] = $class;
                    $data['range']['content'] = $grade_grade->grade_item->get_formatted_range(GRADE_DISPLAY_TYPE_REAL, $this->rangedecimals);
                    $data['range']['headers'] = "$header_cat $header_row range";
                }

                                if ($this->showpercentage) {
                    if ($grade_grade->grade_item->needsupdate) {
                        $data['percentage']['class'] = $class.' gradingerror';
                        $data['percentage']['content'] = get_string('error');
                    } else if ($grade_grade->is_hidden()) {
                        $data['percentage']['class'] = $class.' dimmed_text';
                        $data['percentage']['content'] = '-';
                        if ($this->canviewhidden) {
                            $data['percentage']['content'] = grade_format_gradevalue($gradeval, $grade_grade->grade_item, true, GRADE_DISPLAY_TYPE_PERCENTAGE);
                        }
                    } else {
                        $data['percentage']['class'] = $class;
                        $data['percentage']['content'] = grade_format_gradevalue($gradeval, $grade_grade->grade_item, true, GRADE_DISPLAY_TYPE_PERCENTAGE);
                    }
                    $data['percentage']['headers'] = "$header_cat $header_row percentage";
                }

                                if ($this->showlettergrade) {
                    if ($grade_grade->grade_item->needsupdate) {
                        $data['lettergrade']['class'] = $class.' gradingerror';
                        $data['lettergrade']['content'] = get_string('error');
                    } else if ($grade_grade->is_hidden()) {
                        $data['lettergrade']['class'] = $class.' dimmed_text';
                        if (!$this->canviewhidden) {
                            $data['lettergrade']['content'] = '-';
                        } else {
                            $data['lettergrade']['content'] = grade_format_gradevalue($gradeval, $grade_grade->grade_item, true, GRADE_DISPLAY_TYPE_LETTER);
                        }
                    } else {
                        $data['lettergrade']['class'] = $class;
                        $data['lettergrade']['content'] = grade_format_gradevalue($gradeval, $grade_grade->grade_item, true, GRADE_DISPLAY_TYPE_LETTER);
                    }
                    $data['lettergrade']['headers'] = "$header_cat $header_row lettergrade";
                }

                                if ($this->showrank) {
                    if ($grade_grade->grade_item->needsupdate) {
                        $data['rank']['class'] = $class.' gradingerror';
                        $data['rank']['content'] = get_string('error');
                        } elseif ($grade_grade->is_hidden()) {
                            $data['rank']['class'] = $class.' dimmed_text';
                            $data['rank']['content'] = '-';
                    } else if (is_null($gradeval)) {
                                                $data['rank']['class'] = $class;
                        $data['rank']['content'] = '-';

                    } else {
                                                $sql = "SELECT COUNT(DISTINCT(userid))
                                  FROM {grade_grades}
                                 WHERE finalgrade > ?
                                       AND itemid = ?
                                       AND hidden = 0";
                        $rank = $DB->count_records_sql($sql, array($grade_grade->finalgrade, $grade_grade->grade_item->id)) + 1;

                        $data['rank']['class'] = $class;
                        $data['rank']['content'] = "$rank/".$this->get_numusers(false);                     }
                    $data['rank']['headers'] = "$header_cat $header_row rank";
                }

                                if ($this->showaverage) {
                    $data['average']['class'] = $class;
                    if (!empty($this->gtree->items[$eid]->avg)) {
                        $data['average']['content'] = $this->gtree->items[$eid]->avg;
                    } else {
                        $data['average']['content'] = '-';
                    }
                    $data['average']['headers'] = "$header_cat $header_row average";
                }

                                if ($this->showfeedback) {
                    if ($grade_grade->overridden > 0 AND ($type == 'categoryitem' OR $type == 'courseitem')) {
                    $data['feedback']['class'] = $classfeedback.' feedbacktext';
                        $data['feedback']['content'] = get_string('overridden', 'grades').': ' . format_text($grade_grade->feedback, $grade_grade->feedbackformat);
                    } else if (empty($grade_grade->feedback) or (!$this->canviewhidden and $grade_grade->is_hidden())) {
                        $data['feedback']['class'] = $classfeedback.' feedbacktext';
                        $data['feedback']['content'] = '&nbsp;';
                    } else {
                        $data['feedback']['class'] = $classfeedback.' feedbacktext';
                        $data['feedback']['content'] = format_text($grade_grade->feedback, $grade_grade->feedbackformat);
                    }
                    $data['feedback']['headers'] = "$header_cat $header_row feedback";
                }
                                if ($this->showcontributiontocoursetotal) {
                    $data['contributiontocoursetotal']['class'] = $class;
                    $data['contributiontocoursetotal']['content'] = '-';
                    $data['contributiontocoursetotal']['headers'] = "$header_cat $header_row contributiontocoursetotal";

                }
            }
                        if ($this->showcontributiontocoursetotal) {
                $hint['grademax'] = $grade_grade->grade_item->grademax;
                $hint['grademin'] = $grade_grade->grade_item->grademin;
                $hint['grade'] = $gradeval;
                $parent = $grade_object->load_parent_category();
                if ($grade_object->is_category_item()) {
                    $parent = $parent->load_parent_category();
                }
                $hint['parent'] = $parent->load_grade_item()->id;
                $this->aggregationhints[$grade_grade->itemid] = $hint;
            }
        }

                if ($type == 'category') {
            $data['leader']['class'] = $class.' '.$alter."d$depth b1t b2b b1l";
            $data['leader']['rowspan'] = $element['rowspan'];

            if ($this->switch) {                $data['itemname']['class'] = $class.' '.$alter."d$depth b1b b1t";
            } else {
               $data['itemname']['class'] = $class.' '.$alter."d$depth b2t";
            }
            $data['itemname']['colspan'] = ($this->maxdepth - $depth + count($this->tablecolumns) - 1);
            $data['itemname']['content'] = $fullname;
            $data['itemname']['celltype'] = 'th';
            $data['itemname']['id'] = "cat_{$grade_object->id}_{$this->user->id}";
        }

                foreach ($data as $key => $celldata) {
            $data[$key]['class'] .= ' column-' . $key;
        }
        $this->tabledata[] = $data;

                if (isset($element['children'])) {
            foreach ($element['children'] as $key=>$child) {
                $this->fill_table_recursive($element['children'][$key]);
            }
        }

                        if ($this->showcontributiontocoursetotal && ($type == 'category' && $depth == 1)) {
            
            $this->fill_contributions_column($element);
        }
    }

    
    public function fill_contributions_column($element) {

                if (isset($element['children'])) {
            foreach ($element['children'] as $key=>$child) {
                $this->fill_contributions_column($element['children'][$key]);
            }
        } else if ($element['type'] == 'item') {
                        $grade_object = $element['object'];
            $itemid = $grade_object->id;

                        if (isset($this->aggregationhints[$itemid])) {

                                $gradecat = $grade_object->load_parent_category();
                if ($gradecat->aggregation == GRADE_AGGREGATE_SUM) {
                                        $graderange = $this->aggregationhints[$itemid]['grademax'];

                    if ($graderange != 0) {
                        $gradeval = $this->aggregationhints[$itemid]['grade'] / $graderange;
                    } else {
                        $gradeval = 0;
                    }
                } else {
                    $gradeval = grade_grade::standardise_score($this->aggregationhints[$itemid]['grade'],
                        $this->aggregationhints[$itemid]['grademin'], $this->aggregationhints[$itemid]['grademax'], 0, 1);
                }

                                                $parent = null;
                do {
                    if (!is_null($this->aggregationhints[$itemid]['weight'])) {
                        $gradeval *= $this->aggregationhints[$itemid]['weight'];
                    } else if (empty($parent)) {
                                                $gradeval = null;
                        break;
                    }

                                                            if (isset($this->aggregationhints[$itemid]['parent']) &&
                            $this->aggregationhints[$itemid]['parent'] != $itemid) {
                        $parent = $this->aggregationhints[$itemid]['parent'];
                        $itemid = $parent;
                    } else {
                                                $parent = false;
                    }
                } while ($parent);

                                if (!is_null($gradeval)) {
                                        $gradeval *= 100;
                }

                                                $header_row = "row_{$grade_object->id}_{$this->user->id}";
                foreach ($this->tabledata as $key => $row) {
                    if (isset($row['itemname']) && ($row['itemname']['id'] == $header_row)) {
                                                $content = '-';
                        if (!is_null($gradeval)) {
                            $decimals = $grade_object->get_decimals();
                            $content = format_float($gradeval, $decimals, true) . ' %';
                        }
                        $this->tabledata[$key]['contributiontocoursetotal']['content'] = $content;
                        break;
                    }
                }
            }
        }
    }

    
    public function print_table($return=false) {
         $maxspan = $this->maxdepth;

                $html = "
            <table cellspacing='0'
                   cellpadding='0'
                   summary='" . s($this->get_lang_string('tablesummary', 'gradereport_user')) . "'
                   class='boxaligncenter generaltable user-grade'>
            <thead>
                <tr>
                    <th id='".$this->tablecolumns[0]."' class=\"header column-{$this->tablecolumns[0]}\" colspan='$maxspan'>".$this->tableheaders[0]."</th>\n";

        for ($i = 1; $i < count($this->tableheaders); $i++) {
            $html .= "<th id='".$this->tablecolumns[$i]."' class=\"header column-{$this->tablecolumns[$i]}\">".$this->tableheaders[$i]."</th>\n";
        }

        $html .= "
                </tr>
            </thead>
            <tbody>\n";

                for ($i = 0; $i < count($this->tabledata); $i++) {
            $html .= "<tr>\n";
            if (isset($this->tabledata[$i]['leader'])) {
                $rowspan = $this->tabledata[$i]['leader']['rowspan'];
                $class = $this->tabledata[$i]['leader']['class'];
                $html .= "<td class='$class' rowspan='$rowspan'></td>\n";
            }
            for ($j = 0; $j < count($this->tablecolumns); $j++) {
                $name = $this->tablecolumns[$j];
                $class = (isset($this->tabledata[$i][$name]['class'])) ? $this->tabledata[$i][$name]['class'] : '';
                $colspan = (isset($this->tabledata[$i][$name]['colspan'])) ? "colspan='".$this->tabledata[$i][$name]['colspan']."'" : '';
                $content = (isset($this->tabledata[$i][$name]['content'])) ? $this->tabledata[$i][$name]['content'] : null;
                $celltype = (isset($this->tabledata[$i][$name]['celltype'])) ? $this->tabledata[$i][$name]['celltype'] : 'td';
                $id = (isset($this->tabledata[$i][$name]['id'])) ? "id='{$this->tabledata[$i][$name]['id']}'" : '';
                $headers = (isset($this->tabledata[$i][$name]['headers'])) ? "headers='{$this->tabledata[$i][$name]['headers']}'" : '';
                if (isset($content)) {
                    $html .= "<$celltype $id $headers class='$class' $colspan>$content</$celltype>\n";
                }
            }
            $html .= "</tr>\n";
        }

        $html .= "</tbody></table>";

        if ($return) {
            return $html;
        } else {
            echo $html;
        }
    }

    
    function process_data($data) {
    }
    function process_action($target, $action) {
    }

    
    function calculate_averages() {
        global $USER, $DB, $CFG;

        if ($this->showaverage) {
                                                $averagesdisplaytype   = $this->get_pref('averagesdisplaytype');
            $averagesdecimalpoints = $this->get_pref('averagesdecimalpoints');
            $meanselection         = $this->get_pref('meanselection');
            $shownumberofgrades    = $this->get_pref('shownumberofgrades');

            $avghtml = '';
            $groupsql = $this->groupsql;
            $groupwheresql = $this->groupwheresql;
            $totalcount = $this->get_numusers(false);

                        list($relatedctxsql, $relatedctxparams) = $DB->get_in_or_equal($this->context->get_parent_context_ids(true), SQL_PARAMS_NAMED, 'relatedctx');

                        list($gradebookrolessql, $gradebookrolesparams) = $DB->get_in_or_equal(explode(',', $this->gradebookroles), SQL_PARAMS_NAMED, 'grbr0');

                        $coursecontext = $this->context->get_course_context(true);
            $defaultgradeshowactiveenrol = !empty($CFG->grade_report_showonlyactiveenrol);
            $showonlyactiveenrol = get_user_preferences('grade_report_showonlyactiveenrol', $defaultgradeshowactiveenrol);
            $showonlyactiveenrol = $showonlyactiveenrol || !has_capability('moodle/course:viewsuspendedusers', $coursecontext);
            list($enrolledsql, $enrolledparams) = get_enrolled_sql($this->context, '', 0, $showonlyactiveenrol);

            $params = array_merge($this->groupwheresql_params, $gradebookrolesparams, $enrolledparams, $relatedctxparams);
            $params['courseid'] = $this->courseid;

                        $sql = "SELECT gg.itemid, SUM(gg.finalgrade) AS sum
                      FROM {grade_items} gi
                      JOIN {grade_grades} gg ON gg.itemid = gi.id
                      JOIN {user} u ON u.id = gg.userid
                      JOIN ($enrolledsql) je ON je.id = gg.userid
                      JOIN (
                                   SELECT DISTINCT ra.userid
                                     FROM {role_assignments} ra
                                    WHERE ra.roleid $gradebookrolessql
                                      AND ra.contextid $relatedctxsql
                           ) rainner ON rainner.userid = u.id
                      $groupsql
                     WHERE gi.courseid = :courseid
                       AND u.deleted = 0
                       AND gg.finalgrade IS NOT NULL
                       AND gg.hidden = 0
                       $groupwheresql
                  GROUP BY gg.itemid";

            $sum_array = array();
            $sums = $DB->get_recordset_sql($sql, $params);
            foreach ($sums as $itemid => $csum) {
                $sum_array[$itemid] = $csum->sum;
            }
            $sums->close();

            $columncount=0;

                                                            $sql = "SELECT gi.id, COUNT(u.id) AS count
                      FROM {grade_items} gi
                      JOIN {user} u ON u.deleted = 0
                      JOIN ($enrolledsql) je ON je.id = u.id
                      JOIN (
                               SELECT DISTINCT ra.userid
                                 FROM {role_assignments} ra
                                WHERE ra.roleid $gradebookrolessql
                                  AND ra.contextid $relatedctxsql
                           ) rainner ON rainner.userid = u.id
                      LEFT JOIN {grade_grades} gg
                             ON (gg.itemid = gi.id AND gg.userid = u.id AND gg.finalgrade IS NOT NULL AND gg.hidden = 0)
                      $groupsql
                     WHERE gi.courseid = :courseid
                           AND gg.finalgrade IS NULL
                           $groupwheresql
                  GROUP BY gi.id";

            $ungraded_counts = $DB->get_records_sql($sql, $params);

            foreach ($this->gtree->items as $itemid=>$unused) {
                if (!empty($this->gtree->items[$itemid]->avg)) {
                    continue;
                }
                $item = $this->gtree->items[$itemid];

                if ($item->needsupdate) {
                    $avghtml .= '<td class="cell c' . $columncount++.'"><span class="gradingerror">'.get_string('error').'</span></td>';
                    continue;
                }

                if (empty($sum_array[$item->id])) {
                    $sum_array[$item->id] = 0;
                }

                if (empty($ungraded_counts[$itemid])) {
                    $ungraded_count = 0;
                } else {
                    $ungraded_count = $ungraded_counts[$itemid]->count;
                }

                                if ($meanselection == GRADE_REPORT_MEAN_GRADED) {
                    $mean_count = $totalcount - $ungraded_count;
                } else {                     $sum_array[$item->id] += ($ungraded_count * $item->grademin);
                    $mean_count = $totalcount;
                }

                                if (!empty($USER->gradeediting) && $USER->gradeediting[$this->courseid]) {
                    $displaytype = GRADE_DISPLAY_TYPE_REAL;

                } else if ($averagesdisplaytype == GRADE_REPORT_PREFERENCE_INHERIT) {                     $displaytype = $item->get_displaytype();

                } else {
                    $displaytype = $averagesdisplaytype;
                }

                                if ($averagesdecimalpoints == GRADE_REPORT_PREFERENCE_INHERIT) {
                    $decimalpoints = $item->get_decimals();
                } else {
                    $decimalpoints = $averagesdecimalpoints;
                }

                if (empty($sum_array[$item->id]) || $mean_count == 0) {
                    $this->gtree->items[$itemid]->avg = '-';
                } else {
                    $sum = $sum_array[$item->id];
                    $avgradeval = $sum/$mean_count;
                    $gradehtml = grade_format_gradevalue($avgradeval, $item, true, $displaytype, $decimalpoints);

                    $numberofgrades = '';
                    if ($shownumberofgrades) {
                        $numberofgrades = " ($mean_count)";
                    }

                    $this->gtree->items[$itemid]->avg = $gradehtml.$numberofgrades;
                }
            }
        }
    }

    
    public function viewed() {
        $event = \gradereport_user\event\grade_report_viewed::create(
            array(
                'context' => $this->context,
                'courseid' => $this->courseid,
                'relateduserid' => $this->user->id,
            )
        );
        $event->trigger();
    }
}

function grade_report_user_settings_definition(&$mform) {
    global $CFG;

    $options = array(-1 => get_string('default', 'grades'),
                      0 => get_string('hide'),
                      1 => get_string('show'));

    if (empty($CFG->grade_report_user_showrank)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_user_showrank', get_string('showrank', 'grades'), $options);
    $mform->addHelpButton('report_user_showrank', 'showrank', 'grades');

    if (empty($CFG->grade_report_user_showpercentage)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_user_showpercentage', get_string('showpercentage', 'grades'), $options);
    $mform->addHelpButton('report_user_showpercentage', 'showpercentage', 'grades');

    if (empty($CFG->grade_report_user_showgrade)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_user_showgrade', get_string('showgrade', 'grades'), $options);

    if (empty($CFG->grade_report_user_showfeedback)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_user_showfeedback', get_string('showfeedback', 'grades'), $options);

    if (empty($CFG->grade_report_user_showweight)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_user_showweight', get_string('showweight', 'grades'), $options);

    if (empty($CFG->grade_report_user_showaverage)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_user_showaverage', get_string('showaverage', 'grades'), $options);
    $mform->addHelpButton('report_user_showaverage', 'showaverage', 'grades');

    if (empty($CFG->grade_report_user_showlettergrade)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_user_showlettergrade', get_string('showlettergrade', 'grades'), $options);
    if (empty($CFG->grade_report_user_showcontributiontocoursetotal)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[$CFG->grade_report_user_showcontributiontocoursetotal]);
    }

    $mform->addElement('select', 'report_user_showcontributiontocoursetotal', get_string('showcontributiontocoursetotal', 'grades'), $options);
    $mform->addHelpButton('report_user_showcontributiontocoursetotal', 'showcontributiontocoursetotal', 'grades');

    if (empty($CFG->grade_report_user_showrange)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[1]);
    }

    $mform->addElement('select', 'report_user_showrange', get_string('showrange', 'grades'), $options);

    $options = array(0=>0, 1=>1, 2=>2, 3=>3, 4=>4, 5=>5);
    if (! empty($CFG->grade_report_user_rangedecimals)) {
        $options[-1] = $options[$CFG->grade_report_user_rangedecimals];
    }
    $mform->addElement('select', 'report_user_rangedecimals', get_string('rangedecimals', 'grades'), $options);

    $options = array(-1 => get_string('default', 'grades'),
                      0 => get_string('shownohidden', 'grades'),
                      1 => get_string('showhiddenuntilonly', 'grades'),
                      2 => get_string('showallhidden', 'grades'));

    if (empty($CFG->grade_report_user_showhiddenitems)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[$CFG->grade_report_user_showhiddenitems]);
    }

    $mform->addElement('select', 'report_user_showhiddenitems', get_string('showhiddenitems', 'grades'), $options);
    $mform->addHelpButton('report_user_showhiddenitems', 'showhiddenitems', 'grades');

        $options = array(-1 => get_string('default', 'grades'),
                      GRADE_REPORT_HIDE_TOTAL_IF_CONTAINS_HIDDEN => get_string('hide'),
                      GRADE_REPORT_SHOW_TOTAL_IF_CONTAINS_HIDDEN => get_string('hidetotalshowexhiddenitems', 'grades'),
                      GRADE_REPORT_SHOW_REAL_TOTAL_IF_CONTAINS_HIDDEN => get_string('hidetotalshowinchiddenitems', 'grades') );

    if (empty($CFG->grade_report_user_showtotalsifcontainhidden)) {
        $options[-1] = get_string('defaultprev', 'grades', $options[0]);
    } else {
        $options[-1] = get_string('defaultprev', 'grades', $options[$CFG->grade_report_user_showtotalsifcontainhidden]);
    }

    $mform->addElement('select', 'report_user_showtotalsifcontainhidden', get_string('hidetotalifhiddenitems', 'grades'), $options);
    $mform->addHelpButton('report_user_showtotalsifcontainhidden', 'hidetotalifhiddenitems', 'grades');

}


function grade_report_user_profilereport($course, $user, $viewasuser = false) {
    global $OUTPUT;
    if (!empty($course->showgrades)) {

        $context = context_course::instance($course->id);

                $gpr = new grade_plugin_return(array('type'=>'report', 'plugin'=>'user', 'courseid'=>$course->id, 'userid'=>$user->id));
                $report = new grade_report_user($course->id, $gpr, $context, $user->id, $viewasuser);

                echo '<div class="grade-report-user">';         if ($report->fill_table()) {
            echo $report->print_table(true);
        }
        echo '</div>';
    }
}


function gradereport_user_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $CFG, $USER;
    if (empty($course)) {
                $course = get_fast_modinfo(SITEID)->get_course();
    }
    $usercontext = context_user::instance($user->id);
    $anyreport = has_capability('moodle/user:viewuseractivitiesreport', $usercontext);

        if ($anyreport || ($course->showreports && $user->id == $USER->id)) {
                $gradeaccess = false;
        $coursecontext = context_course::instance($course->id);
        if (has_capability('moodle/grade:viewall', $coursecontext)) {
                        $gradeaccess = true;
        } else if ($course->showgrades) {
            if ($iscurrentuser && has_capability('moodle/grade:view', $coursecontext)) {
                                $gradeaccess = true;
            } else if (has_capability('moodle/grade:viewall', $usercontext)) {
                                $gradeaccess = true;
            } else if ($anyreport) {
                                $gradeaccess = true;
            }
        }
        if ($gradeaccess) {
            $url = new moodle_url('/course/user.php', array('mode' => 'grade', 'id' => $course->id, 'user' => $user->id));
            $node = new core_user\output\myprofile\node('reports', 'grade', get_string('grade'), null, $url);
            $tree->add_node($node);
        }
    }
}
