<?php






    function glossary_rss_get_feed($context, $args) {
        global $CFG, $DB, $COURSE, $USER;

        $status = true;

        if (empty($CFG->glossary_enablerssfeeds)) {
            debugging("DISABLED (module configuration)");
            return null;
        }

        $glossaryid  = clean_param($args[3], PARAM_INT);
        $cm = get_coursemodule_from_instance('glossary', $glossaryid, 0, false, MUST_EXIST);
        $modcontext = context_module::instance($cm->id);

        if ($COURSE->id == $cm->course) {
            $course = $COURSE;
        } else {
            $course = $DB->get_record('course', array('id'=>$cm->course), '*', MUST_EXIST);
        }
                if ($context->id != $modcontext->id || !has_capability('mod/glossary:view', $modcontext)) {
            return null;
        }

        $glossary = $DB->get_record('glossary', array('id' => $glossaryid), '*', MUST_EXIST);
        if (!rss_enabled_for_mod('glossary', $glossary)) {
            return null;
        }

        $sql = glossary_rss_get_sql($glossary);

                $filename = rss_get_file_name($glossary, $sql);
        $cachedfilepath = rss_get_file_full_name('mod_glossary', $filename);

                $cachedfilelastmodified = 0;
        if (file_exists($cachedfilepath)) {
            $cachedfilelastmodified = filemtime($cachedfilepath);
        }
                $dontrecheckcutoff = time()-60;
        if ( $dontrecheckcutoff > $cachedfilelastmodified && glossary_rss_newstuff($glossary, $cachedfilelastmodified)) {
            if (!$recs = $DB->get_records_sql($sql, array(), 0, $glossary->rssarticles)) {
                return null;
            }

            $items = array();

            $formatoptions = new stdClass();
            $formatoptions->trusttext = true;

            foreach ($recs as $rec) {
                $item = new stdClass();
                $item->title = $rec->entryconcept;

                if ($glossary->rsstype == 1) {                    $item->author = fullname($rec);
                }

                $item->pubdate = $rec->entrytimecreated;
                $item->link = $CFG->wwwroot."/mod/glossary/showentry.php?courseid=".$glossary->course."&eid=".$rec->entryid;

                $definition = file_rewrite_pluginfile_urls($rec->entrydefinition, 'pluginfile.php',
                    $modcontext->id, 'mod_glossary', 'entry', $rec->entryid);
                $item->description = format_text($definition, $rec->entryformat, $formatoptions, $glossary->course);
                $items[] = $item;
            }

                        $header = rss_standard_header(format_string($glossary->name,true),
                                          $CFG->wwwroot."/mod/glossary/view.php?g=".$glossary->id,
                                          format_string($glossary->intro,true));
                        if (!empty($header)) {
                $articles = rss_add_items($items);
            }
                        if (!empty($header) && !empty($articles)) {
                $footer = rss_standard_footer();
            }
                        if (!empty($header) && !empty($articles) && !empty($footer)) {
                $rss = $header.$articles.$footer;

                                $status = rss_save_file('mod_glossary', $filename, $rss);
            }
        }

        if (!$status) {
            $cachedfilepath = null;
        }

        return $cachedfilepath;
    }

    
    function glossary_rss_get_sql($glossary, $time=0) {
                if ($time) {
            $time = "AND e.timecreated > $time";
        } else {
            $time = "";
        }

        if ($glossary->rsstype == 1) {            $allnamefields = get_all_user_name_fields(true,'u');
            $sql = "SELECT e.id AS entryid,
                      e.concept AS entryconcept,
                      e.definition AS entrydefinition,
                      e.definitionformat AS entryformat,
                      e.definitiontrust AS entrytrust,
                      e.timecreated AS entrytimecreated,
                      u.id AS userid,
                      $allnamefields
                 FROM {glossary_entries} e,
                      {user} u
                WHERE e.glossaryid = {$glossary->id} AND
                      u.id = e.userid AND
                      e.approved = 1 $time
             ORDER BY e.timecreated desc";
        } else {            $sql = "SELECT e.id AS entryid,
                      e.concept AS entryconcept,
                      e.definition AS entrydefinition,
                      e.definitionformat AS entryformat,
                      e.definitiontrust AS entrytrust,
                      e.timecreated AS entrytimecreated,
                      u.id AS userid
                 FROM {glossary_entries} e,
                      {user} u
                WHERE e.glossaryid = {$glossary->id} AND
                      u.id = e.userid AND
                      e.approved = 1 $time
             ORDER BY e.timecreated desc";
        }

        return $sql;
    }

    
    function glossary_rss_newstuff($glossary, $time) {
        global $DB;

        $sql = glossary_rss_get_sql($glossary, $time);

        $recs = $DB->get_records_sql($sql, null, 0, 1);        return ($recs && !empty($recs));
    }

    
    function glossary_rss_delete_file($glossary) {
        global $CFG;
        require_once("$CFG->libdir/rsslib.php");

        rss_delete_file('mod_glossary', $glossary);
    }
