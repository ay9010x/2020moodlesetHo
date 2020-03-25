<?php


defined('MOODLE_INTERNAL') || die();


class restore_fix_missing_questions extends restore_execution_step
{
    
    protected function define_execution()
    {
        global $DB;

        $restoreid = $this->get_restoreid();
        $courseid = $this->get_courseid();
        $userid = $this->task->get_userid();

        $workaround_qtypes = explode(',', get_config('block_sharing_cart', 'workaround_qtypes'));

                $contexts = restore_dbops::restore_get_question_banks($restoreid);
        foreach ($contexts as $contextid => $contextlevel) {
            $categories = restore_dbops::restore_get_question_categories($restoreid, $contextid);
            $canadd = false;
            if ($targetcontext = restore_dbops::restore_find_best_target_context($categories, $courseid, $contextlevel)) {
                $canadd = has_capability('moodle/question:add', $targetcontext, $userid);
            }
            foreach ($categories as $category) {
                $questions = restore_dbops::restore_get_questions($restoreid, $category->id);
                foreach ($questions as $question) {
                    if (!in_array($question->qtype, $workaround_qtypes))
                        continue;
                    $mapping = restore_dbops::get_backup_ids_record($restoreid, 'question', $question->id);
                    if ($mapping && $mapping->newitemid &&
                        !self::is_question_valid($question->qtype, $mapping->newitemid))
                    {
                        if (!$canadd)
                            throw new moodle_exception('questioncannotberestored', 'backup', '', $question);
                        $catmapping = restore_dbops::get_backup_ids_record($restoreid, 'question_category', $category->id);
                        $matchquestions = $DB->get_records('question', array(
                            'category' => $catmapping->newitemid,
                            'qtype'    => $question->qtype,
                            'stamp'    => $question->stamp,
                            'version'  => $question->version
                            ));
                        $newitemid = 0;                         foreach ($matchquestions as $q) {
                            if ($q->id == $mapping->newitemid)
                                continue;
                            if (self::is_question_valid($question->qtype, $q->id)) {
                                $newitemid = $q->id;                                 break;
                            }
                        }
                        $this->update_mapping($mapping, $newitemid);
                    }
                }
            }
        }
    }

    
    private function update_mapping($record, $newitemid)
    {
        $restoreid = $this->get_restoreid();
        $key = "{$record->itemid} {$record->itemname} {$restoreid}";
        $extrarecord = array('newitemid' => $newitemid);

                $reflector = new ReflectionMethod('restore_dbops', 'update_backup_cached_record');
        $reflector->setAccessible(true);
        $reflector->invoke(null, $record, $extrarecord, $key, $record);
    }

    
    private static function is_question_valid($qtypename, $questionid)
    {
        global $DB;

                $question = (object)array('id' => $questionid);
        try {
                        $question->options = new stdClass;
            $oldhandler = set_error_handler(function ($n, $s, $f, $l) { return true; });
            question_bank::get_qtype($qtypename)->get_question_options($question);
            isset($oldhandler) and set_error_handler($oldhandler);
            if (count(get_object_vars($question->options)) == 0) {
                if ($qtypename === 'random') {
                                    } else {
                    return false;
                }
            }
        } catch (moodle_exception $ex) {
            isset($oldhandler) and set_error_handler($oldhandler);
            return false;
        }
                        if (property_exists($question->options, 'subquestions')) {
            if (empty($question->options->subquestions))
                return false;
                        $dbman = $DB->get_manager();
            if ($dbman->table_exists("question_{$qtypename}") &&
                $dbman->field_exists("question_{$qtypename}", 'question') &&
                $dbman->table_exists("question_{$qtypename}_sub") &&
                $dbman->field_exists("question_{$qtypename}_sub", 'question'))
            {
                                $q = $DB->get_record("question_{$qtypename}", array('question' => $question->id));
                if (!$q || empty($q->subquestions))
                    return false;
                $subquestionids = explode(',', $q->subquestions);
                list ($sql, $params) = $DB->get_in_or_equal($subquestionids);
                $sql .= ' AND question = ?';
                $params[] = $question->id;
                $count = $DB->get_field_select("question_{$qtypename}_sub", 'COUNT(*)', "id $sql", $params);
                if ($count != count($subquestionids))
                    return false;
            }
        }
        return true;
    }
}
