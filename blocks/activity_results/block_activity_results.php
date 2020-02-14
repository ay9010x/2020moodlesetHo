<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/grade/constants.php');

define('B_ACTIVITYRESULTS_NAME_FORMAT_FULL', 1);
define('B_ACTIVITYRESULTS_NAME_FORMAT_ID',   2);
define('B_ACTIVITYRESULTS_NAME_FORMAT_ANON', 3);
define('B_ACTIVITYRESULTS_GRADE_FORMAT_PCT', 1);
define('B_ACTIVITYRESULTS_GRADE_FORMAT_FRA', 2);
define('B_ACTIVITYRESULTS_GRADE_FORMAT_ABS', 3);
define('B_ACTIVITYRESULTS_GRADE_FORMAT_SCALE', 4);


class block_activity_results extends block_base {

    
    public function init() {
        $this->title = get_string('pluginname', 'block_activity_results');
    }

    
    public function applicable_formats() {
        return array('course-view' => true, 'mod' => true);
    }

    
    public function get_owning_activity() {
        global $DB;

                $result = new stdClass();
        $result->id = 0;

        if (empty($this->instance->parentcontextid)) {
            return $result;
        }
        $parentcontext = context::instance_by_id($this->instance->parentcontextid);
        if ($parentcontext->contextlevel != CONTEXT_MODULE) {
            return $result;
        }
        $cm = get_coursemodule_from_id($this->page->cm->modname, $parentcontext->instanceid);
        if (!$cm) {
            return $result;
        }
                $rec = $DB->get_record('grade_items', array('iteminstance' => $cm->instance, 'itemmodule' => $this->page->cm->modname));
        if (!$rec) {
            return $result;
        }
                if (($rec->gradetype != GRADE_TYPE_VALUE) && ($rec->gradetype != GRADE_TYPE_SCALE)) {
            return $result;
        }
        return $rec;
    }

    
    public function instance_config_save($data, $nolongerused = false) {
        global $DB;
        if (empty($data->activitygradeitemid)) {
                        $info = $this->get_owning_activity();
            $data->activitygradeitemid = $info->id;
            if ($info->id < 1) {
                                $info->itemmodule = '';
                $info->iteminstance = '';
            } else {
                $data->activityparent = $info->itemmodule;
                $data->activityparentid = $info->iteminstance;
            }
        } else {
                        $info = $DB->get_record('grade_items', array('id' => $data->activitygradeitemid));
            $data->activityparent = $info->itemmodule;
            $data->activityparentid = $info->iteminstance;
        }
        parent::instance_config_save($data);
    }

    
    public function get_content() {
        global $USER, $CFG, $DB;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        if (empty($this->instance)) {
            return $this->content;
        }

                if (!empty($this->config->activitygradeitemid)) {
                        $activitygradeitemid = $this->config->activitygradeitemid;

                        $activity = $DB->get_record('grade_items', array('id' => $activitygradeitemid));
            if (empty($activity)) {
                                $this->content->text = get_string('error_emptyactivityrecord', 'block_activity_results');
                return $this->content;
            }
            $courseid = $activity->courseid;
            $inactivity = false;
        } else {
                        $activitygradeitemid = 0;
        }

                if (!empty($this->config->activitygradeitemid)) {
            if ($this->get_owning_activity()->id == $this->config->activitygradeitemid) {
                $inactivity = true;
            } else {
                $inactivity = false;
            }
        }

                if (empty($activitygradeitemid)) {
            $this->content->text = get_string('error_emptyactivityid', 'block_activity_results');
            return $this->content;
        }

                if (empty($this->config->showbest) && empty($this->config->showworst)) {
            $this->content->text = get_string('configuredtoshownothing', 'block_activity_results');
            return $this->content;
        }

                if (empty($activity->gradetype) || ($activity->gradetype != GRADE_TYPE_VALUE && $activity->gradetype != GRADE_TYPE_SCALE)) {
            $this->content->text = get_string('error_unsupportedgradetype', 'block_activity_results');
            return $this->content;
        }

                $sql = 'SELECT * FROM {grade_grades}
                 WHERE itemid = ? AND finalgrade is not NULL
                 ORDER BY finalgrade, timemodified DESC';

        $grades = $DB->get_records_sql($sql, array( $activitygradeitemid));

        if (empty($grades) || $activity->hidden) {
                        return $this->content;
        }

                $groupmode = NOGROUPS;
        $best      = array();
        $worst     = array();

        if (!empty($this->config->nameformat)) {
            $nameformat = $this->config->nameformat;
        } else {
            $nameformat = B_ACTIVITYRESULTS_NAME_FORMAT_FULL;
        }

                if ($inactivity) {
            $cm = $this->page->cm;
            $context = $this->page->context;
        } else {
            $cm = get_coursemodule_from_instance($activity->itemmodule, $activity->iteminstance, $courseid);
            $context = context_module::instance($cm->id);
        }

        if (!empty($this->config->usegroups)) {
            $groupmode = groups_get_activity_groupmode($cm);

            if ($groupmode == SEPARATEGROUPS && has_capability('moodle/site:accessallgroups', $context)) {
                                $groupmode = VISIBLEGROUPS;
            }
        }

        switch ($groupmode) {
            case VISIBLEGROUPS:
                                $groups = groups_get_all_groups($courseid);

                if (empty($groups)) {
                                        $this->content->text = get_string('error_nogroupsexist', 'block_activity_results');
                    return $this->content;
                }

                                $userids = array();
                $gradeforuser = array();
                foreach ($grades as $grade) {
                    $userids[] = $grade->userid;
                    $gradeforuser[$grade->userid] = (float)$grade->finalgrade;
                }

                                list($usertest, $params) = $DB->get_in_or_equal($userids);
                $params[] = $courseid;
                $usergroups = $DB->get_records_sql('
                        SELECT gm.id, gm.userid, gm.groupid, g.name
                        FROM {groups} g
                        LEFT JOIN {groups_members} gm ON g.id = gm.groupid
                        WHERE gm.userid ' . $usertest . ' AND g.courseid = ?', $params);

                                $groupgrades = array();
                foreach ($usergroups as $usergroup) {
                    if (!isset($groupgrades[$usergroup->groupid])) {
                        $groupgrades[$usergroup->groupid] = array(
                                'sum' => (float)$gradeforuser[$usergroup->userid],
                                'number' => 1,
                                'group' => $usergroup->name);
                    } else {
                        $groupgrades[$usergroup->groupid]['sum'] += $gradeforuser[$usergroup->userid];
                        $groupgrades[$usergroup->groupid]['number'] += 1;
                    }
                }

                foreach ($groupgrades as $groupid => $groupgrade) {
                    $groupgrades[$groupid]['average'] = $groupgrades[$groupid]['sum'] / $groupgrades[$groupid]['number'];
                }

                                uasort($groupgrades, create_function('$a, $b',
                        'if($a["average"] == $b["average"]) return 0; return ($a["average"] > $b["average"] ? 1 : -1);'));

                                $numbest  = empty($this->config->showbest) ? 0 : min($this->config->showbest, count($groupgrades));
                $numworst = empty($this->config->showworst) ? 0 : min($this->config->showworst, count($groupgrades) - $numbest);

                                $remaining = $numbest;
                $groupgrade = end($groupgrades);
                while ($remaining--) {
                    $best[key($groupgrades)] = $groupgrade['average'];
                    $groupgrade = prev($groupgrades);
                }

                $remaining = $numworst;
                $groupgrade = reset($groupgrades);
                while ($remaining--) {
                    $worst[key($groupgrades)] = $groupgrade['average'];
                    $groupgrade = next($groupgrades);
                }

                                if ($activity->gradetype == GRADE_TYPE_SCALE) {
                                        $gradeformat = B_ACTIVITYRESULTS_GRADE_FORMAT_SCALE;
                                        $scale = $this->get_scale($activity->scaleid);
                } else if (intval(empty($this->config->gradeformat))) {
                    $gradeformat = B_ACTIVITYRESULTS_GRADE_FORMAT_PCT;
                } else {
                    $gradeformat = $this->config->gradeformat;
                }

                                $this->content->text .= $this->activity_link($activity, $cm);

                if ($nameformat == B_ACTIVITYRESULTS_NAME_FORMAT_FULL) {
                    if (has_capability('moodle/course:managegroups', $context)) {
                        $grouplink = $CFG->wwwroot.'/group/overview.php?id='.$courseid.'&amp;group=';
                    } else if (has_capability('moodle/course:viewparticipants', $context)) {
                        $grouplink = $CFG->wwwroot.'/user/index.php?id='.$courseid.'&amp;group=';
                    } else {
                        $grouplink = '';
                    }
                }

                $rank = 0;
                if (!empty($best)) {
                    $this->content->text .= '<table class="grades"><caption>';
                    if ($numbest == 1) {
                        $this->content->text .= get_string('bestgroupgrade', 'block_activity_results');
                    } else {
                        $this->content->text .= get_string('bestgroupgrades', 'block_activity_results', $numbest);
                    }
                    $this->content->text .= '</caption><colgroup class="number" />';
                    $this->content->text .= '<colgroup class="name" /><colgroup class="grade" /><tbody>';
                    foreach ($best as $groupid => $averagegrade) {
                        switch ($nameformat) {
                            case B_ACTIVITYRESULTS_NAME_FORMAT_ANON:
                            case B_ACTIVITYRESULTS_NAME_FORMAT_ID:
                                $thisname = get_string('group');
                            break;
                            default:
                            case B_ACTIVITYRESULTS_NAME_FORMAT_FULL:
                                if ($grouplink) {
                                    $thisname = '<a href="'.$grouplink.$groupid.'">'.$groupgrades[$groupid]['group'].'</a>';
                                } else {
                                    $thisname = $groupgrades[$groupid]['group'];
                                }
                            break;
                        }
                        $this->content->text .= '<tr><td>'.(++$rank).'.</td><td>'.$thisname.'</td><td>';
                        switch ($gradeformat) {
                            case B_ACTIVITYRESULTS_GRADE_FORMAT_SCALE:
                                                                $answer = (round($averagegrade, 0, PHP_ROUND_HALF_UP) - 1);
                                if (isset($scale[$answer])) {
                                    $this->content->text .= $scale[$answer];
                                } else {
                                                                        $this->content->text .= get_string('unknown', 'block_activity_results');
                                }
                            break;
                            case B_ACTIVITYRESULTS_GRADE_FORMAT_FRA:
                                $this->content->text .= $this->activity_format_grade($averagegrade)
                                    . '/' . $this->activity_format_grade($activity->grademax);
                            break;
                            case B_ACTIVITYRESULTS_GRADE_FORMAT_ABS:
                                $this->content->text .= $this->activity_format_grade($averagegrade);
                            break;
                            default:
                            case B_ACTIVITYRESULTS_GRADE_FORMAT_PCT:
                                $this->content->text .= $this->activity_format_grade((float)$averagegrade /
                                        (float)$activity->grademax * 100).'%';
                            break;
                        }
                        $this->content->text .= '</td></tr>';
                    }
                    $this->content->text .= '</tbody></table>';
                }

                $rank = 0;
                if (!empty($worst)) {
                    $worst = array_reverse($worst, true);
                    $this->content->text .= '<table class="grades"><caption>';
                    if ($numworst == 1) {
                        $this->content->text .= get_string('worstgroupgrade', 'block_activity_results');
                    } else {
                        $this->content->text .= get_string('worstgroupgrades', 'block_activity_results', $numworst);
                    }
                    $this->content->text .= '</caption><colgroup class="number" />';
                    $this->content->text .= '<colgroup class="name" /><colgroup class="grade" /><tbody>';
                    foreach ($worst as $groupid => $averagegrade) {
                        switch ($nameformat) {
                            case B_ACTIVITYRESULTS_NAME_FORMAT_ANON:
                            case B_ACTIVITYRESULTS_NAME_FORMAT_ID:
                                $thisname = get_string('group');
                            break;
                            default:
                            case B_ACTIVITYRESULTS_NAME_FORMAT_FULL:
                                if ($grouplink) {
                                    $thisname = '<a href="'.$grouplink.$groupid.'">'.$groupgrades[$groupid]['group'].'</a>';
                                } else {
                                    $thisname = $groupgrades[$groupid]['group'];
                                }
                            break;
                        }
                        $this->content->text .= '<tr><td>'.(++$rank).'.</td><td>'.$thisname.'</td><td>';
                        switch ($gradeformat) {
                            case B_ACTIVITYRESULTS_GRADE_FORMAT_SCALE:
                                                                $answer = (round($averagegrade, 0, PHP_ROUND_HALF_UP) - 1);
                                if (isset($scale[$answer])) {
                                    $this->content->text .= $scale[$answer];
                                } else {
                                                                        $this->content->text .= get_string('unknown', 'block_activity_results');
                                }
                            break;
                            case B_ACTIVITYRESULTS_GRADE_FORMAT_FRA:
                                $this->content->text .= $this->activity_format_grade($averagegrade)
                                    . '/' . $this->activity_format_grade($activity->grademax);
                            break;
                            case B_ACTIVITYRESULTS_GRADE_FORMAT_ABS:
                                $this->content->text .= $this->activity_format_grade($averagegrade);
                            break;
                            default:
                            case B_ACTIVITYRESULTS_GRADE_FORMAT_PCT:
                                $this->content->text .= $this->activity_format_grade((float)$averagegrade /
                                        (float)$activity->grademax * 100).'%';
                            break;
                        }
                        $this->content->text .= '</td></tr>';
                    }
                    $this->content->text .= '</tbody></table>';
                }
            break;

            case SEPARATEGROUPS:
                                                if (!isloggedin()) {
                                        return $this->content;
                }

                $mygroups = groups_get_all_groups($courseid, $USER->id);
                if (empty($mygroups)) {
                                        return $this->content;
                }

                                list($grouptest, $params) = $DB->get_in_or_equal(array_keys($mygroups));
                $mygroupsusers = $DB->get_records_sql_menu(
                        'SELECT DISTINCT userid, 1 FROM {groups_members} WHERE groupid ' . $grouptest,
                        $params);

                                foreach ($grades as $key => $grade) {
                    if (!isset($mygroupsusers[$grade->userid])) {
                        unset($grades[$key]);
                    }
                }

                            default:
            case NOGROUPS:
                                $numbest  = empty($this->config->showbest) ? 0 : min($this->config->showbest, count($grades));
                $numworst = empty($this->config->showworst) ? 0 : min($this->config->showworst, count($grades) - $numbest);

                                $remaining = $numbest;
                $grade = end($grades);
                while ($remaining--) {
                    $best[$grade->userid] = $grade->id;
                    $grade = prev($grades);
                }

                $remaining = $numworst;
                $grade = reset($grades);
                while ($remaining--) {
                    $worst[$grade->userid] = $grade->id;
                    $grade = next($grades);
                }

                if (empty($best) && empty($worst)) {
                                        return $this->content;
                }

                                $userids = array_merge(array_keys($best), array_keys($worst));
                $fields = array_merge(array('id', 'idnumber'), get_all_user_name_fields());
                $fields = implode(',', $fields);
                $users = $DB->get_records_list('user', 'id', $userids, '', $fields);

                                if ($activity->gradetype == GRADE_TYPE_SCALE) {
                                        $gradeformat = B_ACTIVITYRESULTS_GRADE_FORMAT_SCALE;
                                        $scale = $this->get_scale($activity->scaleid);
                } else if (intval(empty($this->config->gradeformat))) {
                    $gradeformat = B_ACTIVITYRESULTS_GRADE_FORMAT_PCT;
                } else {
                    $gradeformat = $this->config->gradeformat;
                }

                                $this->content->text .= $this->activity_link($activity, $cm);

                $rank = 0;
                if (!empty($best)) {
                    $this->content->text .= '<table class="grades"><caption>';
                    if ($numbest == 1) {
                        $this->content->text .= get_string('bestgrade', 'block_activity_results');
                    } else {
                        $this->content->text .= get_string('bestgrades', 'block_activity_results', $numbest);
                    }
                    $this->content->text .= '</caption><colgroup class="number" />';
                    $this->content->text .= '<colgroup class="name" /><colgroup class="grade" /><tbody>';
                    foreach ($best as $userid => $gradeid) {
                        switch ($nameformat) {
                            case B_ACTIVITYRESULTS_NAME_FORMAT_ID:
                                $thisname = get_string('user').' '.$users[$userid]->idnumber;
                            break;
                            case B_ACTIVITYRESULTS_NAME_FORMAT_ANON:
                                $thisname = get_string('user');
                            break;
                            default:
                            case B_ACTIVITYRESULTS_NAME_FORMAT_FULL:
                                if (has_capability('moodle/user:viewdetails', $context)) {
                                    $thisname = html_writer::link(new moodle_url('/user/view.php',
                                        array('id' => $userid, 'course' => $courseid)), fullname($users[$userid]));
                                } else {
                                    $thisname = fullname($users[$userid]);
                                }
                            break;
                        }
                        $this->content->text .= '<tr><td>'.(++$rank).'.</td><td>'.$thisname.'</td><td>';
                        switch ($gradeformat) {
                            case B_ACTIVITYRESULTS_GRADE_FORMAT_SCALE:
                                                                $answer = (round($grades[$gradeid]->finalgrade, 0, PHP_ROUND_HALF_UP) - 1);
                                if (isset($scale[$answer])) {
                                    $this->content->text .= $scale[$answer];
                                } else {
                                                                        $this->content->text .= get_string('unknown', 'block_activity_results');
                                }
                            break;
                            case B_ACTIVITYRESULTS_GRADE_FORMAT_FRA:
                                $this->content->text .= $this->activity_format_grade($grades[$gradeid]->finalgrade);
                                $this->content->text .= '/'.$this->activity_format_grade($activity->grademax);
                            break;
                            case B_ACTIVITYRESULTS_GRADE_FORMAT_ABS:
                                $this->content->text .= $this->activity_format_grade($grades[$gradeid]->finalgrade);
                            break;
                            default:
                            case B_ACTIVITYRESULTS_GRADE_FORMAT_PCT:
                                if ($activity->grademax) {
                                    $this->content->text .= $this->activity_format_grade((float)$grades[$gradeid]->finalgrade /
                                            (float)$activity->grademax * 100).'%';
                                } else {
                                    $this->content->text .= '--%';
                                }
                            break;
                        }
                        $this->content->text .= '</td></tr>';
                    }
                    $this->content->text .= '</tbody></table>';
                }

                $rank = 0;
                if (!empty($worst)) {
                    $worst = array_reverse($worst, true);
                    $this->content->text .= '<table class="grades"><caption>';
                    if ($numbest == 1) {
                        $this->content->text .= get_string('worstgrade', 'block_activity_results');
                    } else {
                        $this->content->text .= get_string('worstgrades', 'block_activity_results', $numworst);
                    }
                    $this->content->text .= '</caption><colgroup class="number" />';
                    $this->content->text .= '<colgroup class="name" /><colgroup class="grade" /><tbody>';
                    foreach ($worst as $userid => $gradeid) {
                        switch ($nameformat) {
                            case B_ACTIVITYRESULTS_NAME_FORMAT_ID:
                                $thisname = get_string('user').' '.$users[$userid]->idnumber;
                            break;
                            case B_ACTIVITYRESULTS_NAME_FORMAT_ANON:
                                $thisname = get_string('user');
                            break;
                            default:
                            case B_ACTIVITYRESULTS_NAME_FORMAT_FULL:
                                if (has_capability('moodle/user:viewdetails', $context)) {
                                    $thisname = html_writer::link(new moodle_url('/user/view.php',
                                        array('id' => $userid, 'course' => $courseid)), fullname($users[$userid]));
                                } else {
                                    $thisname = fullname($users[$userid]);
                                }
                            break;
                        }
                        $this->content->text .= '<tr><td>'.(++$rank).'.</td><td>'.$thisname.'</td><td>';
                        switch ($gradeformat) {
                            case B_ACTIVITYRESULTS_GRADE_FORMAT_SCALE:
                                                                $answer = (round($grades[$gradeid]->finalgrade, 0, PHP_ROUND_HALF_UP) - 1);
                                if (isset($scale[$answer])) {
                                    $this->content->text .= $scale[$answer];
                                } else {
                                                                        $this->content->text .= get_string('unknown', 'block_activity_results');
                                }
                            break;
                            case B_ACTIVITYRESULTS_GRADE_FORMAT_FRA:
                                $this->content->text .= $this->activity_format_grade($grades[$gradeid]->finalgrade);
                                $this->content->text .= '/'.$this->activity_format_grade($activity->grademax);
                            break;
                            case B_ACTIVITYRESULTS_GRADE_FORMAT_ABS:
                                $this->content->text .= $this->activity_format_grade($grades[$gradeid]->finalgrade);
                            break;
                            default:
                            case B_ACTIVITYRESULTS_GRADE_FORMAT_PCT:
                                if ($activity->grademax) {
                                    $this->content->text .= $this->activity_format_grade((float)$grades[$gradeid]->finalgrade /
                                            (float)$activity->grademax * 100).'%';
                                } else {
                                    $this->content->text .= '--%';
                                }
                            break;
                        }
                        $this->content->text .= '</td></tr>';
                    }
                    $this->content->text .= '</tbody></table>';
                }
            break;
        }

        return $this->content;
    }

    
    public function instance_allow_multiple() {
        return true;
    }

    
    private function activity_format_grade($grade) {
        if (is_null($grade)) {
            return get_string('notyetgraded', 'block_activity_results');
        }
        return format_float($grade, $this->config->decimalpoints);
    }

    
    private function activity_link($activity, $cm) {

        $o = html_writer::start_tag('h3');
        $o .= html_writer::link(new moodle_url('/mod/'.$activity->itemmodule.'/view.php',
        array('id' => $cm->id)), $activity->itemname);
        $o .= html_writer::end_tag('h3');
        return $o;
    }

    
    private function get_scale($scaleid) {
        global $DB;
        $scaletext = $DB->get_field('scale', 'scale', array('id' => $scaleid), IGNORE_MISSING);
        $scale = explode ( ',', $scaletext);
        return $scale;

    }
}