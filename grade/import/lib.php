<?php


require_once($CFG->libdir.'/gradelib.php');


function get_new_importcode() {
    global $USER, $DB;

    $importcode = time();
    while ($DB->get_record('grade_import_values', array('importcode' => $importcode, 'importer' => $USER->id))) {
        $importcode--;
    }

    return $importcode;
}


function grade_import_commit($courseid, $importcode, $importfeedback=true, $verbose=true) {
    global $CFG, $USER, $DB, $OUTPUT;

    $failed = false;
    $executionerrors = false;
    $commitstart = time();     $newitemids = array(); 
        $params = array($importcode, $USER->id);
    if ($newitems = $DB->get_records_sql("SELECT *
                                           FROM {grade_import_newitem}
                                          WHERE importcode = ? AND importer=?", $params)) {

                        $instances = array();
        foreach ($newitems as $newitem) {
            
            $gradeimportparams = array('newgradeitem' => $newitem->id, 'importcode' => $importcode, 'importer' => $USER->id);
            if ($grades = $DB->get_records('grade_import_values', $gradeimportparams)) {
                                                $gradeitem = new grade_item(array('courseid'=>$courseid, 'itemtype'=>'manual', 'itemname'=>$newitem->itemname), false);
                $gradeitem->insert('import');
                $instances[] = $gradeitem;

                                foreach ($grades as $grade) {
                    if (!$gradeitem->update_final_grade($grade->userid, $grade->finalgrade, 'import', $grade->feedback, FORMAT_MOODLE)) {
                        $failed = true;
                        break 2;
                    }
                }
            }
        }

        if ($failed) {
            foreach ($instances as $instance) {
                $gradeitem->delete('import');
            }
            import_cleanup($importcode);
            return false;
        }
    }

    
    if ($gradeitems = $DB->get_records_sql("SELECT DISTINCT (itemid)
                                             FROM {grade_import_values}
                                            WHERE importcode = ? AND importer=? AND itemid > 0",
                                            array($importcode, $USER->id))) {

        $modifieditems = array();

        foreach ($gradeitems as $itemid=>$notused) {

            if (!$gradeitem = new grade_item(array('id'=>$itemid))) {
                                import_cleanup($importcode);
                return false;
            }
                        $gradeimportparams = array('itemid' => $itemid, 'importcode' => $importcode, 'importer' => $USER->id);
            if ($grades = $DB->get_records('grade_import_values', $gradeimportparams)) {

                                foreach ($grades as $grade) {
                    if (!$importfeedback) {
                        $grade->feedback = false;                     }
                    if ($grade->importonlyfeedback) {
                                                $grade->finalgrade = false;
                    }
                    if (!$gradeitem->update_final_grade($grade->userid, $grade->finalgrade, 'import', $grade->feedback)) {
                        $errordata = new stdClass();
                        $errordata->itemname = $gradeitem->itemname;
                        $errordata->userid = $grade->userid;
                        $executionerrors[] = get_string('errorsettinggrade', 'grades', $errordata);
                        $failed = true;
                        break 2;
                    }
                }
                                $modifieditems[] = $itemid;

            }
        }

        if ($failed) {
            if ($executionerrors && $verbose) {
                echo $OUTPUT->notification(get_string('gradeimportfailed', 'grades'));
                foreach ($executionerrors as $errorstr) {
                    echo $OUTPUT->notification($errorstr);
                }
            }
            import_cleanup($importcode);
            return false;
        }
    }

    if ($verbose) {
        echo $OUTPUT->notification(get_string('importsuccess', 'grades'), 'notifysuccess');
        $unenrolledusers = get_unenrolled_users_in_import($importcode, $courseid);
        if ($unenrolledusers) {
            $list = array();
            foreach ($unenrolledusers as $u) {
                $u->fullname = fullname($u);
                $list[] = get_string('usergrade', 'grades', $u);
            }
            echo $OUTPUT->notification(get_string('unenrolledusersinimport', 'grades', html_writer::alist($list)), 'notifysuccess');
        }
        echo $OUTPUT->continue_button($CFG->wwwroot.'/grade/index.php?id='.$courseid);
    }
        import_cleanup($importcode);

    return true;
}


function get_unenrolled_users_in_import($importcode, $courseid) {
    global $CFG, $DB;

    $coursecontext = context_course::instance($courseid);

        list($relatedctxsql, $relatedctxparams) = $DB->get_in_or_equal($coursecontext->get_parent_context_ids(true), SQL_PARAMS_NAMED, 'relatedctx');

        list($gradebookrolessql, $gradebookrolesparams) = $DB->get_in_or_equal(explode(',', $CFG->gradebookroles), SQL_PARAMS_NAMED, 'grbr');

        $context = context_course::instance($courseid);
    list($enrolledsql, $enrolledparams) = get_enrolled_sql($context);
    list($sort, $sortparams) = users_order_by_sql('u');

    $sql = "SELECT giv.id, u.firstname, u.lastname, u.idnumber AS useridnumber,
                   COALESCE(gi.idnumber, gin.itemname) AS gradeidnumber
              FROM {grade_import_values} giv
              JOIN {user} u
                   ON giv.userid = u.id
              LEFT JOIN {grade_items} gi
                        ON gi.id = giv.itemid
              LEFT JOIN {grade_import_newitem} gin
                        ON gin.id = giv.newgradeitem
              LEFT JOIN ($enrolledsql) je
                        ON je.id = u.id
              LEFT JOIN {role_assignments} ra
                        ON (giv.userid = ra.userid AND ra.roleid $gradebookrolessql AND ra.contextid $relatedctxsql)
             WHERE giv.importcode = :importcode
                   AND (ra.id IS NULL OR je.id IS NULL)
          ORDER BY gradeidnumber, $sort";
    $params = array_merge($gradebookrolesparams, $enrolledparams, $sortparams, $relatedctxparams);
    $params['importcode'] = $importcode;

    return $DB->get_records_sql($sql, $params);
}


function import_cleanup($importcode) {
    global $USER, $DB;

        $DB->delete_records('grade_import_values', array('importcode' => $importcode, 'importer' => $USER->id));
    $DB->delete_records('grade_import_newitem', array('importcode' => $importcode, 'importer' => $USER->id));
}


