<?php



defined('MOODLE_INTERNAL') || die;


function book_get_numbering_types() {
    global $CFG; 
    require_once(dirname(__FILE__).'/locallib.php');

    return array (
        BOOK_NUM_NONE       => get_string('numbering0', 'mod_book'),
        BOOK_NUM_NUMBERS    => get_string('numbering1', 'mod_book'),
        BOOK_NUM_BULLETS    => get_string('numbering2', 'mod_book'),
        BOOK_NUM_INDENTED   => get_string('numbering3', 'mod_book')
    );
}


function book_get_nav_types() {
    require_once(dirname(__FILE__).'/locallib.php');

    return array (
        BOOK_LINK_TOCONLY   => get_string('navtoc', 'mod_book'),
        BOOK_LINK_IMAGE     => get_string('navimages', 'mod_book'),
        BOOK_LINK_TEXT      => get_string('navtext', 'mod_book'),
    );
}


function book_get_nav_classes() {
    return array ('navtoc', 'navimages', 'navtext');
}


function book_get_extra_capabilities() {
        return array('moodle/site:accessallgroups');
}


function book_add_instance($data, $mform) {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = $data->timecreated;
    if (!isset($data->customtitles)) {
        $data->customtitles = 0;
    }

    return $DB->insert_record('book', $data);
}


function book_update_instance($data, $mform) {
    global $DB;

    $data->timemodified = time();
    $data->id = $data->instance;
    if (!isset($data->customtitles)) {
        $data->customtitles = 0;
    }

    $DB->update_record('book', $data);

    $book = $DB->get_record('book', array('id'=>$data->id));
    $DB->set_field('book', 'revision', $book->revision+1, array('id'=>$book->id));

    return true;
}


function book_delete_instance($id) {
    global $DB;

    if (!$book = $DB->get_record('book', array('id'=>$id))) {
        return false;
    }

    $DB->delete_records('book_chapters', array('bookid'=>$book->id));
    $DB->delete_records('book', array('id'=>$book->id));

    return true;
}


function book_print_recent_activity($course, $viewfullnames, $timestart) {
    return false;  }


function book_reset_userdata($data) {
    return array();
}


function book_cron () {
    return true;
}


function book_grades($bookid) {
    return null;
}


function book_scale_used($bookid, $scaleid) {
    return false;
}


function book_scale_used_anywhere($scaleid) {
    return false;
}


function book_get_view_actions() {
    global $CFG; 
    $return = array('view', 'view all');

    $plugins = core_component::get_plugin_list('booktool');
    foreach ($plugins as $plugin => $dir) {
        if (file_exists("$dir/lib.php")) {
            require_once("$dir/lib.php");
        }
        $function = 'booktool_'.$plugin.'_get_view_actions';
        if (function_exists($function)) {
            if ($actions = $function()) {
                $return = array_merge($return, $actions);
            }
        }
    }

    return $return;
}


function book_get_post_actions() {
    global $CFG; 
    $return = array('update');

    $plugins = core_component::get_plugin_list('booktool');
    foreach ($plugins as $plugin => $dir) {
        if (file_exists("$dir/lib.php")) {
            require_once("$dir/lib.php");
        }
        $function = 'booktool_'.$plugin.'_get_post_actions';
        if (function_exists($function)) {
            if ($actions = $function()) {
                $return = array_merge($return, $actions);
            }
        }
    }

    return $return;
}


function book_supports($feature) {
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:           return MOD_ARCHETYPE_RESOURCE;
        case FEATURE_GROUPS:                  return false;
        case FEATURE_GROUPINGS:               return false;
        case FEATURE_MOD_INTRO:               return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS: return true;
        case FEATURE_GRADE_HAS_GRADE:         return false;
        case FEATURE_GRADE_OUTCOMES:          return false;
        case FEATURE_BACKUP_MOODLE2:          return true;
        case FEATURE_SHOW_DESCRIPTION:        return true;

        default: return null;
    }
}


function book_extend_settings_navigation(settings_navigation $settingsnav, navigation_node $booknode) {
    global $USER, $PAGE, $OUTPUT;

    $plugins = core_component::get_plugin_list('booktool');
    foreach ($plugins as $plugin => $dir) {
        if (file_exists("$dir/lib.php")) {
            require_once("$dir/lib.php");
        }
        $function = 'booktool_'.$plugin.'_extend_settings_navigation';
        if (function_exists($function)) {
            $function($settingsnav, $booknode);
        }
    }

    $params = $PAGE->url->params();

    if ($PAGE->cm->modname === 'book' and !empty($params['id']) and !empty($params['chapterid'])
            and has_capability('mod/book:edit', $PAGE->cm->context)) {
        if (!empty($USER->editing)) {
            $string = get_string("turneditingoff");
            $edit = '0';
        } else {
            $string = get_string("turneditingon");
            $edit = '1';
        }
        $url = new moodle_url('/mod/book/view.php', array('id'=>$params['id'], 'chapterid'=>$params['chapterid'], 'edit'=>$edit, 'sesskey'=>sesskey()));
        $booknode->add($string, $url, navigation_node::TYPE_SETTING);
        $PAGE->set_button($OUTPUT->single_button($url, $string));
    }
}



function book_get_file_areas($course, $cm, $context) {
    $areas = array();
    $areas['chapter'] = get_string('chapters', 'mod_book');
    return $areas;
}


function book_get_file_info($browser, $areas, $course, $cm, $context, $filearea, $itemid, $filepath, $filename) {
    global $CFG, $DB;

    
    if (!has_capability('mod/book:read', $context)) {
        return null;
    }

    if ($filearea !== 'chapter') {
        return null;
    }

    require_once(dirname(__FILE__).'/locallib.php');

    if (is_null($itemid)) {
        return new book_file_info($browser, $course, $cm, $context, $areas, $filearea);
    }

    $fs = get_file_storage();
    $filepath = is_null($filepath) ? '/' : $filepath;
    $filename = is_null($filename) ? '.' : $filename;
    if (!$storedfile = $fs->get_file($context->id, 'mod_book', $filearea, $itemid, $filepath, $filename)) {
        return null;
    }

        $canwrite = has_capability('mod/book:edit', $context);

    $chaptername = $DB->get_field('book_chapters', 'title', array('bookid'=>$cm->instance, 'id'=>$itemid));
    $chaptername = format_string($chaptername, true, array('context'=>$context));

    $urlbase = $CFG->wwwroot.'/pluginfile.php';
    return new file_info_stored($browser, $context, $storedfile, $urlbase, $chaptername, true, true, $canwrite, false);
}


function book_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_course_login($course, true, $cm);

    if ($filearea !== 'chapter') {
        return false;
    }

    if (!has_capability('mod/book:read', $context)) {
        return false;
    }

    $chid = (int)array_shift($args);

    if (!$book = $DB->get_record('book', array('id'=>$cm->instance))) {
        return false;
    }

    if (!$chapter = $DB->get_record('book_chapters', array('id'=>$chid, 'bookid'=>$book->id))) {
        return false;
    }

    if ($chapter->hidden and !has_capability('mod/book:viewhiddenchapters', $context)) {
        return false;
    }

        if ($args[0] == 'index.html') {
        $filename = "index.html";

                $content = file_rewrite_pluginfile_urls($chapter->content, 'webservice/pluginfile.php', $context->id, 'mod_book', 'chapter',
                                                $chapter->id);
        $formatoptions = new stdClass;
        $formatoptions->noclean = true;
        $formatoptions->overflowdiv = true;
        $formatoptions->context = $context;

        $content = format_text($content, $chapter->contentformat, $formatoptions);

                $options = array('reverse' => true);
        $content = file_rewrite_pluginfile_urls($content, 'webservice/pluginfile.php', $context->id, 'mod_book', 'chapter',
                                                $chapter->id, $options);
        $content = str_replace('@@PLUGINFILE@@/', '', $content);

        $titles = "";
                if (!$book->customtitles) {
            require_once(dirname(__FILE__).'/locallib.php');
            $chapters = book_preload_chapters($book);

            if (!$chapter->subchapter) {
                $currtitle = book_get_chapter_title($chapter->id, $chapters, $book, $context);
                                $titles = "<h3>$currtitle</h3>";
            } else {
                $currtitle = book_get_chapter_title($chapters[$chapter->id]->parent, $chapters, $book, $context);
                $currsubtitle = book_get_chapter_title($chapter->id, $chapters, $book, $context);
                                $titles = "<h3>$currtitle</h3>";
                $titles .= "<h4>$currsubtitle</h4>";
            }
        }

        $content = $titles . $content;

        send_file($content, $filename, 0, 0, true, true);
    } else {
        $fs = get_file_storage();
        $relativepath = implode('/', $args);
        $fullpath = "/$context->id/mod_book/chapter/$chid/$relativepath";
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            return false;
        }

                $lifetime = $CFG->filelifetime;
        if ($lifetime > 60 * 10) {
            $lifetime = 60 * 10;
        }

                send_stored_file($file, $lifetime, 0, $forcedownload, $options);
    }
}


function book_page_type_list($pagetype, $parentcontext, $currentcontext) {
    $module_pagetype = array('mod-book-*'=>get_string('page-mod-book-x', 'mod_book'));
    return $module_pagetype;
}


function book_export_contents($cm, $baseurl) {
    global $DB;

    $contents = array();
    $context = context_module::instance($cm->id);

    $book = $DB->get_record('book', array('id' => $cm->instance), '*', MUST_EXIST);

    $fs = get_file_storage();

    $chapters = $DB->get_records('book_chapters', array('bookid' => $book->id), 'pagenum');

    $structure = array();
    $currentchapter = 0;

    foreach ($chapters as $chapter) {
        if ($chapter->hidden) {
            continue;
        }

                $thischapter = array(
            "title"     => format_string($chapter->title, true, array('context' => $context)),
            "href"      => $chapter->id . "/index.html",
            "level"     => 0,
            "subitems"  => array()
        );

                if (!$chapter->subchapter) {
            $currentchapter = $chapter->pagenum;
            $structure[$currentchapter] = $thischapter;
        } else {
                        $thischapter['level'] = 1;
            $structure[$currentchapter]["subitems"][] = $thischapter;
        }

        
                $filename = 'index.html';
        $chapterindexfile = array();
        $chapterindexfile['type']         = 'file';
        $chapterindexfile['filename']     = $filename;
                $chapterindexfile['filepath']     = "/{$chapter->id}/";
        $chapterindexfile['filesize']     = 0;
        $chapterindexfile['fileurl']      = moodle_url::make_webservice_pluginfile_url(
                    $context->id, 'mod_book', 'chapter', $chapter->id, '/', 'index.html')->out(false);
        $chapterindexfile['timecreated']  = $book->timecreated;
        $chapterindexfile['timemodified'] = $book->timemodified;
        $chapterindexfile['content']      = format_string($chapter->title, true, array('context' => $context));
        $chapterindexfile['sortorder']    = 0;
        $chapterindexfile['userid']       = null;
        $chapterindexfile['author']       = null;
        $chapterindexfile['license']      = null;
        $contents[] = $chapterindexfile;

                $files = $fs->get_area_files($context->id, 'mod_book', 'chapter', $chapter->id, 'sortorder DESC, id ASC', false);
        foreach ($files as $fileinfo) {
            $file = array();
            $file['type']         = 'file';
            $file['filename']     = $fileinfo->get_filename();
            $file['filepath']     = "/{$chapter->id}" . $fileinfo->get_filepath();
            $file['filesize']     = $fileinfo->get_filesize();
            $file['fileurl']      = moodle_url::make_webservice_pluginfile_url(
                                        $context->id, 'mod_book', 'chapter', $chapter->id,
                                        $fileinfo->get_filepath(), $fileinfo->get_filename())->out(false);
            $file['timecreated']  = $fileinfo->get_timecreated();
            $file['timemodified'] = $fileinfo->get_timemodified();
            $file['sortorder']    = $fileinfo->get_sortorder();
            $file['userid']       = $fileinfo->get_userid();
            $file['author']       = $fileinfo->get_author();
            $file['license']      = $fileinfo->get_license();
            $contents[] = $file;
        }
    }

        $structurefile = array();
    $structurefile['type']         = 'content';
    $structurefile['filename']     = 'structure';
    $structurefile['filepath']     = "/";
    $structurefile['filesize']     = 0;
    $structurefile['fileurl']      = null;
    $structurefile['timecreated']  = $book->timecreated;
    $structurefile['timemodified'] = $book->timemodified;
    $structurefile['content']      = json_encode(array_values($structure));
    $structurefile['sortorder']    = 0;
    $structurefile['userid']       = null;
    $structurefile['author']       = null;
    $structurefile['license']      = null;

        array_unshift($contents, $structurefile);

    return $contents;
}


function book_view($book, $chapter, $islastchapter, $course, $cm, $context) {

        if (empty($chapter)) {
        \mod_book\event\course_module_viewed::create_from_book($book, $context)->trigger();

    } else {
        \mod_book\event\chapter_viewed::create_from_chapter($book, $context, $chapter)->trigger();

        if ($islastchapter) {
                        $completion = new completion_info($course);
            $completion->set_module_viewed($cm);
        }
    }
}
