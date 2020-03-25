<?php





defined('MOODLE_INTERNAL') || die();


    function data_rss_get_feed($context, $args) {
        global $CFG, $DB;

                if (empty($CFG->data_enablerssfeeds)) {
            debugging("DISABLED (module configuration)");
            return null;
        }

        $dataid = clean_param($args[3], PARAM_INT);
        $cm = get_coursemodule_from_instance('data', $dataid, 0, false, MUST_EXIST);
        if ($cm) {
            $modcontext = context_module::instance($cm->id);

                        if ($context->id != $modcontext->id || !has_capability('mod/data:viewentry', $modcontext)) {
                return null;
            }
        }

        $data = $DB->get_record('data', array('id' => $dataid), '*', MUST_EXIST);
        if (!rss_enabled_for_mod('data', $data, false, true)) {
            return null;
        }

        $sql = data_rss_get_sql($data);

                $filename = rss_get_file_name($data, $sql);
        $cachedfilepath = rss_get_file_full_name('mod_data', $filename);

                $cachedfilelastmodified = 0;
        if (file_exists($cachedfilepath)) {
            $cachedfilelastmodified = filemtime($cachedfilepath);
        }
                $dontrecheckcutoff = time()-60;
        if ( $dontrecheckcutoff > $cachedfilelastmodified && data_rss_newstuff($data, $cachedfilelastmodified)) {
            require_once($CFG->dirroot . '/mod/data/lib.php');

                        if (!$firstfield = $DB->get_record_sql('SELECT id,name FROM {data_fields} WHERE dataid = ? ORDER by id', array($data->id), true)) {
                return null;
            }

            if (!$records = $DB->get_records_sql($sql, array(), 0, $data->rssarticles)) {
                return null;
            }

            $firstrecord = array_shift($records);              array_unshift($records, $firstrecord);

                        $items = array();
            foreach ($records as $record) {
                $recordarray = array();
                array_push($recordarray, $record);

                $item = null;

                                if (!empty($data->rsstitletemplate)) {
                    $item->title = data_print_template('rsstitletemplate', $recordarray, $data, '', 0, true);
                } else {                     $item->title   = strip_tags($DB->get_field('data_content', 'content',
                                                      array('fieldid'=>$firstfield->id, 'recordid'=>$record->id)));
                }
                $item->description = data_print_template('rsstemplate', $recordarray, $data, '', 0, true);
                $item->pubdate = $record->timecreated;
                $item->link = $CFG->wwwroot.'/mod/data/view.php?d='.$data->id.'&rid='.$record->id;

                array_push($items, $item);
            }
            $course = $DB->get_record('course', array('id'=>$data->course));
            $coursecontext = context_course::instance($course->id);
            $courseshortname = format_string($course->shortname, true, array('context' => $coursecontext));

                        $header = rss_standard_header($courseshortname . ': ' . format_string($data->name, true, array('context' => $context)),
                                          $CFG->wwwroot."/mod/data/view.php?d=".$data->id,
                                          format_text($data->intro, $data->introformat, array('context' => $context)));

            if (!empty($header)) {
                $articles = rss_add_items($items);
            }

                        if (!empty($header) && !empty($articles)) {
                $footer = rss_standard_footer();
            }
                        if (!empty($header) && !empty($articles) && !empty($footer)) {
                $rss = $header.$articles.$footer;

                                $status = rss_save_file('mod_data', $filename, $rss);
            }
        }

        return $cachedfilepath;
    }

    function data_rss_get_sql($data, $time=0) {
                if ($time) {
            $time = " AND dr.timemodified > '$time'";
        } else {
            $time = '';
        }

        $approved = ($data->approval) ? ' AND dr.approved = 1 ' : ' ';

        $sql = "SELECT dr.*, u.firstname, u.lastname
                  FROM {data_records} dr, {user} u
                 WHERE dr.dataid = {$data->id} $approved
                       AND dr.userid = u.id $time
              ORDER BY dr.timecreated DESC";

        return $sql;
    }

    
    function data_rss_newstuff($data, $time) {
        global $DB;

        $sql = data_rss_get_sql($data, $time);

        $recs = $DB->get_records_sql($sql, null, 0, 1);        return ($recs && !empty($recs));
    }

    
    function data_rss_delete_file($data) {
        global $CFG;
        require_once("$CFG->libdir/rsslib.php");

        rss_delete_file('mod_data', $data);
    }

