<?php



defined('MOODLE_INTERNAL') || die();


function upgrade_mysql_get_supported_tables() {
    global $OUTPUT, $DB;

    $tables = array();
    $patprefix = str_replace('_', '\\_', $DB->get_prefix());
    $pregprefix = preg_quote($DB->get_prefix(), '/');

    $sql = "SHOW FULL TABLES LIKE '$patprefix%'";
    $rs = $DB->get_recordset_sql($sql);
    foreach ($rs as $record) {
        $record = array_change_key_case((array)$record, CASE_LOWER);
        $type = $record['table_type'];
        unset($record['table_type']);
        $fullname = array_shift($record);

        if ($pregprefix === '') {
            $name = $fullname;
        } else {
            $count = null;
            $name = preg_replace("/^$pregprefix/", '', $fullname, -1, $count);
            if ($count !== 1) {
                continue;
            }
        }

        if (!preg_match("/^[a-z][a-z0-9_]*$/", $name)) {
            echo $OUTPUT->notification("Database table with invalid name '$fullname' detected, skipping.", 'notifyproblem');
            continue;
        }
        if ($type === 'VIEW') {
            echo $OUTPUT->notification("Unsupported database table view '$fullname' detected, skipping.", 'notifyproblem');
            continue;
        }
        $tables[$name] = $name;
    }
    $rs->close();

    return $tables;
}


function upgrade_group_members_only($groupingid, $availability) {
        if ($groupingid) {
                $condition = (object)array('type' => 'grouping', 'id' => (int)$groupingid);
    } else {
                $condition = (object)array('type' => 'group');
    }

    if (is_null($availability)) {
                $tree = (object)array('op' => '&', 'c' => array($condition), 'showc' => array(false));
    } else {
                $tree = json_decode($availability);
        switch ($tree->op) {
            case '&' :
                                $tree->c[] = $condition;
                $tree->showc[] = false;
                break;
            case '!|' :
                                                $tree->c[] = (object)array('op' => '!&', 'c' => array($condition));
                $tree->showc[] = false;
                break;
            default:
                                                $tree = (object)array('op' => '&', 'c' => array($tree, $condition),
                        'showc' => array($tree->show, false));
                                unset($tree->c[0]->show);
                break;
        }
    }

    return json_encode($tree);
}


function upgrade_mimetypes($filetypes) {
    global $DB;
    $select = $DB->sql_like('filename', '?', false);
    foreach ($filetypes as $extension=>$mimetype) {
        $DB->set_field_select(
            'files',
            'mimetype',
            $mimetype,
            $select,
            array($extension)
        );
    }
}


function upgrade_extra_credit_weightoverride($onlycourseid = 0) {
    global $DB;

            $courses = $DB->get_fieldset_sql(
        "SELECT DISTINCT gc.courseid
          FROM {grade_categories} gc
          INNER JOIN {grade_items} gi ON gc.id = gi.categoryid AND gi.weightoverride = :weightoverriden
          INNER JOIN {grade_items} gie ON gc.id = gie.categoryid AND gie.aggregationcoef = :extracredit
          WHERE gc.aggregation = :naturalaggmethod" . ($onlycourseid ? " AND gc.courseid = :onlycourseid" : ''),
        array('naturalaggmethod' => 13,
            'weightoverriden' => 1,
            'extracredit' => 1,
            'onlycourseid' => $onlycourseid,
        )
    );
    foreach ($courses as $courseid) {
        $gradebookfreeze = get_config('core', 'gradebook_calculations_freeze_' . $courseid);
        if (!$gradebookfreeze) {
            set_config('gradebook_calculations_freeze_' . $courseid, 20150619);
        }
    }
}


function upgrade_calculated_grade_items($courseid = null) {
    global $DB, $CFG;

    $affectedcourses = array();
    $possiblecourseids = array();
    $params = array();
    $singlecoursesql = '';
    if (isset($courseid)) {
        $singlecoursesql = "AND ns.id = :courseid";
        $params['courseid'] = $courseid;
    }
    $siteminmaxtouse = 1;
    if (isset($CFG->grade_minmaxtouse)) {
        $siteminmaxtouse = $CFG->grade_minmaxtouse;
    }
    $courseidsql = "SELECT ns.id
                      FROM (
                        SELECT c.id, coalesce(" . $DB->sql_compare_text('gs.value') . ", :siteminmax) AS gradevalue
                          FROM {course} c
                          LEFT JOIN {grade_settings} gs
                            ON c.id = gs.courseid
                           AND ((gs.name = 'minmaxtouse' AND " . $DB->sql_compare_text('gs.value') . " = '2'))
                        ) ns
                    WHERE " . $DB->sql_compare_text('ns.gradevalue') . " = '2' $singlecoursesql";
    $params['siteminmax'] = $siteminmaxtouse;
    $courses = $DB->get_records_sql($courseidsql, $params);
    foreach ($courses as $course) {
        $possiblecourseids[$course->id] = $course->id;
    }

    if (!empty($possiblecourseids)) {
        list($sql, $params) = $DB->get_in_or_equal($possiblecourseids);
                        $coursesql = "SELECT DISTINCT courseid
                        FROM {grade_items}
                       WHERE calculation IS NOT NULL
                         AND itemtype = 'manual'
                         AND (grademax <> 100 OR grademin <> 0)
                         AND courseid $sql";
        $affectedcourses = $DB->get_records_sql($coursesql, $params);
    }

            if (!isset($courseid) || !in_array($courseid, $affectedcourses)) {
        $singlecoursesql = '';
        $params = array();
        if (isset($courseid)) {
            $singlecoursesql = "AND courseid = :courseid";
            $params['courseid'] = $courseid;
        }
        $nestedsql = "SELECT id
                        FROM {grade_items}
                       WHERE itemtype = 'category'
                         AND calculation IS NOT NULL $singlecoursesql";
        $calculatedgradecategories = $DB->get_records_sql($nestedsql, $params);
        $categoryids = array();
        foreach ($calculatedgradecategories as $key => $gradecategory) {
            $categoryids[$key] = $gradecategory->id;
        }

        if (!empty($categoryids)) {
            list($sql, $params) = $DB->get_in_or_equal($categoryids);
                                    $coursesql = "SELECT DISTINCT gi.courseid
                            FROM {grade_grades} gg, {grade_items} gi
                           WHERE gi.id = gg.itemid
                             AND (gg.rawgrademax <> gi.grademax OR gg.rawgrademin <> gi.grademin)
                             AND gi.id $sql";
            $additionalcourses = $DB->get_records_sql($coursesql, $params);
            foreach ($additionalcourses as $key => $additionalcourse) {
                if (!array_key_exists($key, $affectedcourses)) {
                    $affectedcourses[$key] = $additionalcourse;
                }
            }
        }
    }

    foreach ($affectedcourses as $affectedcourseid) {
        if (isset($CFG->upgrade_calculatedgradeitemsonlyregrade) && !($courseid)) {
            $DB->set_field('grade_items', 'needsupdate', 1, array('courseid' => $affectedcourseid->courseid));
        } else {
                        $gradebookfreeze = get_config('core', 'gradebook_calculations_freeze_' . $affectedcourseid->courseid);
            if (!$gradebookfreeze) {
                set_config('gradebook_calculations_freeze_' . $affectedcourseid->courseid, 20150627);
            }
        }
    }
}


function upgrade_course_tags() {
    global $DB;
    $sql = "SELECT min(ti.id)
        FROM {tag_instance} ti
        LEFT JOIN {tag_instance} tii on tii.itemtype = ? and tii.itemid = ti.itemid and tii.tiuserid = 0 and tii.tagid = ti.tagid
        where ti.itemtype = ? and ti.tiuserid <> 0 AND tii.id is null
        group by ti.tagid, ti.itemid";
    $ids = $DB->get_fieldset_sql($sql, array('course', 'course'));
    if ($ids) {
        list($idsql, $idparams) = $DB->get_in_or_equal($ids);
        $DB->execute('UPDATE {tag_instance} SET tiuserid = 0 WHERE id ' . $idsql, $idparams);
    }
    $DB->execute("DELETE FROM {tag_instance} WHERE itemtype = ? AND tiuserid <> 0", array('course'));
}


function make_default_scale() {
    global $DB;

    $defaultscale = new stdClass();
    $defaultscale->courseid = 0;
    $defaultscale->userid = 0;
    $defaultscale->name  = get_string('separateandconnected');
    $defaultscale->description = get_string('separateandconnectedinfo');
    $defaultscale->scale = get_string('postrating1', 'forum').','.
                           get_string('postrating2', 'forum').','.
                           get_string('postrating3', 'forum');
    $defaultscale->timemodified = time();

    $defaultscale->id = $DB->insert_record('scale', $defaultscale);
    return $defaultscale;
}



function make_competence_scale() {
    global $DB;

    $defaultscale = new stdClass();
    $defaultscale->courseid = 0;
    $defaultscale->userid = 0;
    $defaultscale->name  = get_string('defaultcompetencescale');
    $defaultscale->description = get_string('defaultcompetencescaledesc');
    $defaultscale->scale = get_string('defaultcompetencescalenotproficient').','.
                           get_string('defaultcompetencescaleproficient');
    $defaultscale->timemodified = time();

    $defaultscale->id = $DB->insert_record('scale', $defaultscale);
    return $defaultscale;
}


function upgrade_course_letter_boundary($courseid = null) {
    global $DB, $CFG;

    $coursesql = '';
    $params = array('contextlevel' => CONTEXT_COURSE);
    if (!empty($courseid)) {
        $coursesql = 'AND c.id = :courseid';
        $params['courseid'] = $courseid;
    }

        $systemcontext = context_system::instance();
    $systemneedsfreeze = upgrade_letter_boundary_needs_freeze($systemcontext);

        $usergradelettercolumnsetting = 0;
    if (isset($CFG->grade_report_user_showlettergrade)) {
        $usergradelettercolumnsetting = (int)$CFG->grade_report_user_showlettergrade;
    }
    $lettercolumnsql = '';
    if ($usergradelettercolumnsetting) {
                $lettercolumnsql = '(gss.value is NULL OR ' . $DB->sql_compare_text('gss.value') .  ' <> \'0\')';
    } else {
                $lettercolumnsql = $DB->sql_compare_text('gss.value') .  ' = \'1\'';
    }

        $systemusesletters = (int) (isset($CFG->grade_displaytype) && in_array($CFG->grade_displaytype, array(3, 13, 23, 31, 32)));
    $systemletters = $systemusesletters || $usergradelettercolumnsetting;

    $contextselect = context_helper::get_preload_record_columns_sql('ctx');

    if ($systemletters && $systemneedsfreeze) {
                
        $sql = "SELECT DISTINCT c.id AS courseid
                  FROM {course} c
                  JOIN {grade_items} gi ON c.id = gi.courseid
                  JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel
             LEFT JOIN {grade_settings} gs ON gs.courseid = c.id AND gs.name = 'displaytype'
             LEFT JOIN {grade_settings} gss ON gss.courseid = c.id AND gss.name = 'report_user_showlettergrade'
             LEFT JOIN {grade_letters} gl ON gl.contextid = ctx.id
                 WHERE gi.display = 0
                 AND ((gs.value is NULL)
                      AND ($lettercolumnsql))
                 AND gl.id is NULL $coursesql";
        $affectedcourseids = $DB->get_recordset_sql($sql, $params);
        foreach ($affectedcourseids as $courseid) {
            set_config('gradebook_calculations_freeze_' . $courseid->courseid, 20160518);
        }
        $affectedcourseids->close();
    }

        $sql = "SELECT DISTINCT c.id AS courseid, $contextselect
              FROM {course} c
              JOIN {context} ctx ON ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel
              JOIN {grade_items} gi ON c.id = gi.courseid
         LEFT JOIN {grade_settings} gs ON c.id = gs.courseid AND gs.name = 'displaytype'
         LEFT JOIN {grade_settings} gss ON gss.courseid = c.id AND gss.name = 'report_user_showlettergrade'
             WHERE
                (
                    -- A grade item is using letters
                    (gi.display IN (3, 13, 23, 31, 32))
                    -- OR the course is using letters
                    OR (" . $DB->sql_compare_text('gs.value') . " IN ('3', '13', '23', '31', '32')
                        -- OR the course using the system default which is letters
                        OR (gs.value IS NULL AND $systemusesletters = 1)
                    )
                    OR ($lettercolumnsql)
                )
                -- AND the course matches
                $coursesql";

    $potentialcourses = $DB->get_recordset_sql($sql, $params);

    foreach ($potentialcourses as $value) {
        $gradebookfreeze = 'gradebook_calculations_freeze_' . $value->courseid;

                        if (!property_exists($CFG, $gradebookfreeze)) {
                        context_helper::preload_from_record($value);
            $coursecontext = context_course::instance($value->courseid);
            if (upgrade_letter_boundary_needs_freeze($coursecontext)) {
                                                set_config('gradebook_calculations_freeze_' . $value->courseid, 20160518);
            }
        }
    }
    $potentialcourses->close();
}


function upgrade_letter_boundary_needs_freeze($context) {
    global $DB;

    $contexts = $context->get_parent_context_ids();
    array_unshift($contexts, $context->id);

    foreach ($contexts as $ctxid) {

        $letters = $DB->get_records_menu('grade_letters', array('contextid' => $ctxid), 'lowerboundary DESC',
                'lowerboundary, letter');

        if (!empty($letters)) {
            foreach ($letters as $boundary => $notused) {
                $standardisedboundary = upgrade_standardise_score($boundary, 0, 100, 0, 100);
                if ($standardisedboundary < $boundary) {
                    return true;
                }
            }
                        return false;
        }
    }
    return false;
}


function upgrade_standardise_score($rawgrade, $sourcemin, $sourcemax, $targetmin, $targetmax) {
    if (is_null($rawgrade)) {
        return null;
    }

    if ($sourcemax == $sourcemin or $targetmin == $targetmax) {
                return $targetmax;
    }

    $factor = ($rawgrade - $sourcemin) / ($sourcemax - $sourcemin);
    $diff = $targetmax - $targetmin;
    $standardisedvalue = $factor * $diff + $targetmin;
    return $standardisedvalue;
}
