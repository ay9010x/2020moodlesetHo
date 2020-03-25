<?php



namespace assignfeedback_editpdf;


class page_editor {

    
    public static function get_comments($gradeid, $pageno, $draft) {
        global $DB;

        $comments = array();
        $params = array('gradeid'=>$gradeid, 'pageno'=>$pageno, 'draft'=>1);
        if (!$draft) {
            $params['draft'] = 0;
        }
        $records = $DB->get_records('assignfeedback_editpdf_cmnt', $params);
        foreach ($records as $record) {
            array_push($comments, new comment($record));
        }

        return $comments;
    }

    
    public static function set_comments($gradeid, $pageno, $comments) {
        global $DB;

        $DB->delete_records('assignfeedback_editpdf_cmnt', array('gradeid'=>$gradeid, 'pageno'=>$pageno, 'draft'=>1));

        $added = 0;
        foreach ($comments as $record) {
                        if (!($record instanceof comment)) {
                $comment = new comment($record);
            } else {
                $comment = $record;
            }
            if (trim($comment->rawtext) === '') {
                continue;
            }
            $comment->gradeid = $gradeid;
            $comment->pageno = $pageno;
            $comment->draft = 1;
            if (self::add_comment($comment)) {
                $added++;
            }
        }

        return $added;
    }

    
    public static function get_comment($commentid) {
        $record = $DB->get_record('assignfeedback_editpdf_cmnt', array('id'=>$commentid), '*', IGNORE_MISSING);
        if ($record) {
            return new comment($record);
        }
        return false;
    }

    
    public static function add_comment(comment $comment) {
        global $DB;
        $comment->id = null;
        return $DB->insert_record('assignfeedback_editpdf_cmnt', $comment);
    }

    
    public static function remove_comment($commentid) {
        global $DB;
        return $DB->delete_records('assignfeedback_editpdf_cmnt', array('id'=>$commentid));
    }

    
    public static function get_annotations($gradeid, $pageno, $draft) {
        global $DB;

        $params = array('gradeid'=>$gradeid, 'pageno'=>$pageno, 'draft'=>1);
        if (!$draft) {
            $params['draft'] = 0;
        }
        $annotations = array();
        $records = $DB->get_records('assignfeedback_editpdf_annot', $params);
        foreach ($records as $record) {
            array_push($annotations, new annotation($record));
        }

        return $annotations;
    }

    
    public static function set_annotations($gradeid, $pageno, $annotations) {
        global $DB;

        $DB->delete_records('assignfeedback_editpdf_annot', array('gradeid' => $gradeid, 'pageno' => $pageno, 'draft' => 1));
        $added = 0;
        foreach ($annotations as $record) {
                        if (!($record instanceof annotation)) {
                $annotation = new annotation($record);
            } else {
                $annotation = $record;
            }
            $annotation->gradeid = $gradeid;
            $annotation->pageno = $pageno;
            $annotation->draft = 1;
            if (self::add_annotation($annotation)) {
                $added++;
            }
        }

        return $added;
    }

    
    public static function get_annotation($annotationid) {
        global $DB;

        $record = $DB->get_record('assignfeedback_editpdf_annot', array('id'=>$annotationid), '*', IGNORE_MISSING);
        if ($record) {
            return new annotation($record);
        }
        return false;
    }

    
    public static function unrelease_drafts($gradeid) {
        global $DB;

                $result = $DB->delete_records('assignfeedback_editpdf_cmnt', array('gradeid'=>$gradeid, 'draft'=>0));
        $result = $DB->delete_records('assignfeedback_editpdf_annot', array('gradeid'=>$gradeid, 'draft'=>0)) && $result;
        return $result;
    }

    
    public static function release_drafts($gradeid) {
        global $DB;

                $DB->delete_records('assignfeedback_editpdf_cmnt', array('gradeid'=>$gradeid, 'draft'=>0));
        $DB->delete_records('assignfeedback_editpdf_annot', array('gradeid'=>$gradeid, 'draft'=>0));

                $records = $DB->get_records('assignfeedback_editpdf_annot', array('gradeid'=>$gradeid, 'draft'=>1));
        foreach ($records as $record) {
            unset($record->id);
            $record->draft = 0;
            $DB->insert_record('assignfeedback_editpdf_annot', $record);
        }
        $records = $DB->get_records('assignfeedback_editpdf_cmnt', array('gradeid'=>$gradeid, 'draft'=>1));
        foreach ($records as $record) {
            unset($record->id);
            $record->draft = 0;
            $DB->insert_record('assignfeedback_editpdf_cmnt', $record);
        }

        return true;
    }

    
    public static function has_annotations_or_comments($gradeid, $includedraft) {
        global $DB;
        $params = array('gradeid'=>$gradeid);
        if (!$includedraft) {
            $params['draft'] = 0;
        }
        if ($DB->count_records('assignfeedback_editpdf_cmnt', $params)) {
            return true;
        }
        if ($DB->count_records('assignfeedback_editpdf_annot', $params)) {
            return true;
        }
        return false;
    }

    
    public static function revert_drafts($gradeid) {
        global $DB;

                $DB->delete_records('assignfeedback_editpdf_cmnt', array('gradeid'=>$gradeid, 'draft'=>1));
        $DB->delete_records('assignfeedback_editpdf_annot', array('gradeid'=>$gradeid, 'draft'=>1));

                $records = $DB->get_records('assignfeedback_editpdf_annot', array('gradeid'=>$gradeid, 'draft'=>0));
        foreach ($records as $record) {
            unset($record->id);
            $record->draft = 0;
            $DB->insert_record('assignfeedback_editpdf_annot', $record);
        }
        $records = $DB->get_records('assignfeedback_editpdf_cmnt', array('gradeid'=>$gradeid, 'draft'=>0));
        foreach ($records as $record) {
            unset($record->id);
            $record->draft = 0;
            $DB->insert_record('assignfeedback_editpdf_annot', $record);
        }

        return true;
    }

    
    public static function add_annotation(annotation $annotation) {
        global $DB;

        $annotation->id = null;
        return $DB->insert_record('assignfeedback_editpdf_annot', $annotation);
    }

    
    public static function remove_annotation($annotationid) {
        global $DB;

        return $DB->delete_records('assignfeedback_editpdf_annot', array('id'=>$annotationid));
    }

    
    public static function copy_drafts_from_to($assignment, $grade, $sourceuserid) {
        global $DB;

                $DB->delete_records('assignfeedback_editpdf_annot', array('gradeid' => $grade->id));
        $DB->delete_records('assignfeedback_editpdf_cmnt', array('gradeid' => $grade->id));
                $sourceusergrade = $assignment->get_user_grade($sourceuserid, true, $grade->attemptnumber);
        $annotations = $DB->get_records('assignfeedback_editpdf_annot', array('gradeid' => $sourceusergrade->id, 'draft' => 1));
        $comments = $DB->get_records('assignfeedback_editpdf_cmnt', array('gradeid' => $sourceusergrade->id, 'draft' => 1));
        $contextid = $assignment->get_context()->id;
        $sourceitemid = $sourceusergrade->id;

                foreach ($annotations as $annotation) {
            $annotation->gradeid = $grade->id;
            $DB->insert_record('assignfeedback_editpdf_annot', $annotation);
        }
        foreach ($comments as $comment) {
            $comment->gradeid = $grade->id;
            $DB->insert_record('assignfeedback_editpdf_cmnt', $comment);
        }

        $fs = get_file_storage();

                self::replace_files_from_to($fs, $contextid, $sourceitemid, $grade->id, document_services::STAMPS_FILEAREA, true);

                self::replace_files_from_to($fs, $contextid, $sourceitemid, $grade->id, document_services::PAGE_IMAGE_FILEAREA);

        return true;
    }

    
    public static function replace_files_from_to($fs, $contextid, $sourceitemid, $itemid, $area, $includesubdirs = false) {
        $component = 'assignfeedback_editpdf';
                $fs->delete_area_files($contextid, $component, $area, $itemid);

                if ($files = $fs->get_area_files($contextid, $component, $area, $sourceitemid,
                                         "filename", $includesubdirs)) {
            foreach ($files as $file) {
                $newrecord = new \stdClass();
                $newrecord->contextid = $contextid;
                $newrecord->itemid = $itemid;
                $fs->create_file_from_storedfile($newrecord, $file);
            }
        }
    }

    
    public static function delete_draft_content($gradeid) {
        global $DB;
        $conditions = array('gradeid' => $gradeid, 'draft' => 1);
        $result = $DB->delete_records('assignfeedback_editpdf_annot', $conditions);
        $result = $result && $DB->delete_records('assignfeedback_editpdf_cmnt', $conditions);
        return $result;
    }
}
