<?php



defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/mod/wiki/lib.php');
require_once($CFG->dirroot . '/mod/wiki/locallib.php');


class mod_wiki_external extends external_api {

    
    public static function get_wikis_by_courses_parameters() {
        return new external_function_parameters (
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'Course ID'), 'Array of course ids.', VALUE_DEFAULT, array()
                ),
            )
        );
    }

    
    public static function get_wikis_by_courses($courseids = array()) {

        $returnedwikis = array();
        $warnings = array();

        $params = self::validate_parameters(self::get_wikis_by_courses_parameters(), array('courseids' => $courseids));

        $mycourses = array();
        if (empty($params['courseids'])) {
            $mycourses = enrol_get_my_courses();
            $params['courseids'] = array_keys($mycourses);
        }

                if (!empty($params['courseids'])) {

            list($courses, $warnings) = external_util::validate_courses($params['courseids'], $mycourses);

                                    $wikis = get_all_instances_in_courses('wiki', $courses);

            foreach ($wikis as $wiki) {

                $context = context_module::instance($wiki->coursemodule);

                                $module = array();

                                $module['id'] = $wiki->id;
                $module['coursemodule'] = $wiki->coursemodule;
                $module['course'] = $wiki->course;
                $module['name']  = external_format_string($wiki->name, $context->id);

                $viewablefields = [];
                if (has_capability('mod/wiki:viewpage', $context)) {
                    list($module['intro'], $module['introformat']) =
                        external_format_text($wiki->intro, $wiki->introformat, $context->id, 'mod_wiki', 'intro', null);

                    $viewablefields = array('firstpagetitle', 'wikimode', 'defaultformat', 'forceformat', 'editbegin', 'editend',
                                            'section', 'visible', 'groupmode', 'groupingid');
                }

                                if (has_capability('moodle/course:manageactivities', $context)) {
                    $additionalfields = array('timecreated', 'timemodified');
                    $viewablefields = array_merge($viewablefields, $additionalfields);
                }

                foreach ($viewablefields as $field) {
                    $module[$field] = $wiki->{$field};
                }

                                $module['cancreatepages'] = wiki_can_create_pages($context);

                $returnedwikis[] = $module;
            }
        }

        $result = array();
        $result['wikis'] = $returnedwikis;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_wikis_by_courses_returns() {

        return new external_single_structure(
            array(
                'wikis' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Wiki ID.'),
                            'coursemodule' => new external_value(PARAM_INT, 'Course module ID.'),
                            'course' => new external_value(PARAM_INT, 'Course ID.'),
                            'name' => new external_value(PARAM_RAW, 'Wiki name.'),
                            'intro' => new external_value(PARAM_RAW, 'Wiki intro.', VALUE_OPTIONAL),
                            'introformat' => new external_format_value('Wiki intro format.', VALUE_OPTIONAL),
                            'timecreated' => new external_value(PARAM_INT, 'Time of creation.', VALUE_OPTIONAL),
                            'timemodified' => new external_value(PARAM_INT, 'Time of last modification.', VALUE_OPTIONAL),
                            'firstpagetitle' => new external_value(PARAM_RAW, 'First page title.', VALUE_OPTIONAL),
                            'wikimode' => new external_value(PARAM_TEXT, 'Wiki mode (individual, collaborative).', VALUE_OPTIONAL),
                            'defaultformat' => new external_value(PARAM_TEXT, 'Wiki\'s default format (html, creole, nwiki).',
                                                                            VALUE_OPTIONAL),
                            'forceformat' => new external_value(PARAM_INT, '1 if format is forced, 0 otherwise.',
                                                                            VALUE_OPTIONAL),
                            'editbegin' => new external_value(PARAM_INT, 'Edit begin.', VALUE_OPTIONAL),
                            'editend' => new external_value(PARAM_INT, 'Edit end.', VALUE_OPTIONAL),
                            'section' => new external_value(PARAM_INT, 'Course section ID.', VALUE_OPTIONAL),
                            'visible' => new external_value(PARAM_INT, '1 if visible, 0 otherwise.', VALUE_OPTIONAL),
                            'groupmode' => new external_value(PARAM_INT, 'Group mode.', VALUE_OPTIONAL),
                            'groupingid' => new external_value(PARAM_INT, 'Group ID.', VALUE_OPTIONAL),
                            'cancreatepages' => new external_value(PARAM_BOOL, 'True if user can create pages.'),
                        ), 'Wikis'
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

    
    public static function view_wiki_parameters() {
        return new external_function_parameters (
            array(
                'wikiid' => new external_value(PARAM_INT, 'Wiki instance ID.')
            )
        );
    }

    
    public static function view_wiki($wikiid) {

        $params = self::validate_parameters(self::view_wiki_parameters(),
                                            array(
                                                'wikiid' => $wikiid
                                            ));
        $warnings = array();

                if (!$wiki = wiki_get_wiki($params['wikiid'])) {
            throw new moodle_exception('incorrectwikiid', 'wiki');
        }

                list($course, $cm) = get_course_and_cm_from_instance($wiki, 'wiki');
        $context = context_module::instance($cm->id);
        self::validate_context($context);

                        if (!has_capability('mod/wiki:viewpage', $context)) {
            throw new moodle_exception('cannotviewpage', 'wiki');
        }

                wiki_view($wiki, $course, $cm, $context);

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function view_wiki_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'Status: true if success.'),
                'warnings' => new external_warnings()
            )
        );
    }

    
    public static function view_page_parameters() {
        return new external_function_parameters (
            array(
                'pageid' => new external_value(PARAM_INT, 'Wiki page ID.'),
            )
        );
    }

    
    public static function view_page($pageid) {

        $params = self::validate_parameters(self::view_page_parameters(),
                                            array(
                                                'pageid' => $pageid
                                            ));
        $warnings = array();

                if (!$page = wiki_get_page($params['pageid'])) {
            throw new moodle_exception('incorrectpageid', 'wiki');
        }

                if (!$wiki = wiki_get_wiki_from_pageid($params['pageid'])) {
            throw new moodle_exception('incorrectwikiid', 'wiki');
        }

                list($course, $cm) = get_course_and_cm_from_instance($wiki, 'wiki');
        $context = context_module::instance($cm->id);
        self::validate_context($context);

                if (!$subwiki = wiki_get_subwiki($page->subwikiid)) {
            throw new moodle_exception('incorrectsubwikiid', 'wiki');
        }
        if (!wiki_user_can_view($subwiki, $wiki)) {
            throw new moodle_exception('cannotviewpage', 'wiki');
        }

                wiki_page_view($wiki, $page, $course, $cm, $context);

        $result = array();
        $result['status'] = true;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function view_page_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'Status: true if success.'),
                'warnings' => new external_warnings()
            )
        );
    }

    
    public static function get_subwikis_parameters() {
        return new external_function_parameters (
            array(
                'wikiid' => new external_value(PARAM_INT, 'Wiki instance ID.')
            )
        );
    }

    
    public static function get_subwikis($wikiid) {
        global $USER;

        $warnings = array();

        $params = self::validate_parameters(self::get_subwikis_parameters(), array('wikiid' => $wikiid));

                if (!$wiki = wiki_get_wiki($params['wikiid'])) {
            throw new moodle_exception('incorrectwikiid', 'wiki');
        }

                list($course, $cm) = get_course_and_cm_from_instance($wiki, 'wiki');
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability('mod/wiki:viewpage', $context);

        $returnedsubwikis = wiki_get_visible_subwikis($wiki, $cm, $context);
        foreach ($returnedsubwikis as $subwiki) {
            $subwiki->canedit = wiki_user_can_edit($subwiki);
        }

        $result = array();
        $result['subwikis'] = $returnedsubwikis;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_subwikis_returns() {
        return new external_single_structure(
            array(
                'subwikis' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Subwiki ID.'),
                            'wikiid' => new external_value(PARAM_INT, 'Wiki ID.'),
                            'groupid' => new external_value(PARAM_RAW, 'Group ID.'),
                            'userid' => new external_value(PARAM_INT, 'User ID.'),
                            'canedit' => new external_value(PARAM_BOOL, 'True if user can edit the subwiki.'),
                        ), 'Subwikis'
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

    
    public static function get_subwiki_pages_parameters() {
        return new external_function_parameters (
            array(
                'wikiid' => new external_value(PARAM_INT, 'Wiki instance ID.'),
                'groupid' => new external_value(PARAM_INT, 'Subwiki\'s group ID, -1 means current group. It will be ignored'
                                        . ' if the wiki doesn\'t use groups.', VALUE_DEFAULT, -1),
                'userid' => new external_value(PARAM_INT, 'Subwiki\'s user ID, 0 means current user. It will be ignored'
                                        .' in collaborative wikis.', VALUE_DEFAULT, 0),
                'options' => new external_single_structure(
                            array(
                                    'sortby' => new external_value(PARAM_ALPHA,
                                            'Field to sort by (id, title, ...).', VALUE_DEFAULT, 'title'),
                                    'sortdirection' => new external_value(PARAM_ALPHA,
                                            'Sort direction: ASC or DESC.', VALUE_DEFAULT, 'ASC'),
                                    'includecontent' => new external_value(PARAM_INT,
                                            'Include each page contents or just the contents size.', VALUE_DEFAULT, 1),
                            ), 'Options', VALUE_DEFAULT, array()),
            )
        );
    }

    
    public static function get_subwiki_pages($wikiid, $groupid = -1, $userid = 0, $options = array()) {

        $returnedpages = array();
        $warnings = array();

        $params = self::validate_parameters(self::get_subwiki_pages_parameters(),
                                            array(
                                                'wikiid' => $wikiid,
                                                'groupid' => $groupid,
                                                'userid' => $userid,
                                                'options' => $options
                                                )
            );

                if (!$wiki = wiki_get_wiki($params['wikiid'])) {
            throw new moodle_exception('incorrectwikiid', 'wiki');
        }
        list($course, $cm) = get_course_and_cm_from_instance($wiki, 'wiki');
        $context = context_module::instance($cm->id);
        self::validate_context($context);

                list($groupid, $userid) = self::determine_group_and_user($cm, $wiki, $params['groupid'], $params['userid']);

                $subwiki = wiki_get_subwiki_by_group_and_user_with_validation($wiki, $groupid, $userid);

        if ($subwiki === false) {
            throw new moodle_exception('cannotviewpage', 'wiki');
        } else if ($subwiki->id != -1) {

                        $options = $params['options'];
            if (!empty($options['sortby'])) {
                if ($options['sortdirection'] != 'ASC' && $options['sortdirection'] != 'DESC') {
                                        $options['sortdirection'] = 'ASC';
                }
                $sort = $options['sortby'] . ' ' . $options['sortdirection'];
            }

            $pages = wiki_get_page_list($subwiki->id, $sort);
            $caneditpages = wiki_user_can_edit($subwiki);
            $firstpage = wiki_get_first_page($subwiki->id);

            foreach ($pages as $page) {
                $retpage = array(
                        'id' => $page->id,
                        'subwikiid' => $page->subwikiid,
                        'title' => external_format_string($page->title, $context->id),
                        'timecreated' => $page->timecreated,
                        'timemodified' => $page->timemodified,
                        'timerendered' => $page->timerendered,
                        'userid' => $page->userid,
                        'pageviews' => $page->pageviews,
                        'readonly' => $page->readonly,
                        'caneditpage' => $caneditpages,
                        'firstpage' => $page->id == $firstpage->id
                    );

                                if ($page->timerendered + WIKI_REFRESH_CACHE_TIME < time()) {
                    if ($content = wiki_refresh_cachedcontent($page)) {
                        $page = $content['page'];
                    }
                }
                list($cachedcontent, $contentformat) = external_format_text(
                            $page->cachedcontent, FORMAT_HTML, $context->id, 'mod_wiki', 'attachments', $subwiki->id);

                if ($options['includecontent']) {
                                        $retpage['cachedcontent'] = $cachedcontent;
                    $retpage['contentformat'] = $contentformat;
                } else {
                                        if (function_exists('mb_strlen') && ((int)ini_get('mbstring.func_overload') & 2)) {
                        $retpage['contentsize'] = mb_strlen($cachedcontent, '8bit');
                    } else {
                        $retpage['contentsize'] = strlen($cachedcontent);
                    }
                }

                $returnedpages[] = $retpage;
            }
        }

        $result = array();
        $result['pages'] = $returnedpages;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_subwiki_pages_returns() {

        return new external_single_structure(
            array(
                'pages' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Page ID.'),
                            'subwikiid' => new external_value(PARAM_INT, 'Page\'s subwiki ID.'),
                            'title' => new external_value(PARAM_RAW, 'Page title.'),
                            'timecreated' => new external_value(PARAM_INT, 'Time of creation.'),
                            'timemodified' => new external_value(PARAM_INT, 'Time of last modification.'),
                            'timerendered' => new external_value(PARAM_INT, 'Time of last renderization.'),
                            'userid' => new external_value(PARAM_INT, 'ID of the user that last modified the page.'),
                            'pageviews' => new external_value(PARAM_INT, 'Number of times the page has been viewed.'),
                            'readonly' => new external_value(PARAM_INT, '1 if readonly, 0 otherwise.'),
                            'caneditpage' => new external_value(PARAM_BOOL, 'True if user can edit the page.'),
                            'firstpage' => new external_value(PARAM_BOOL, 'True if it\'s the first page.'),
                            'cachedcontent' => new external_value(PARAM_RAW, 'Page contents.', VALUE_OPTIONAL),
                            'contentformat' => new external_format_value('cachedcontent', VALUE_OPTIONAL),
                            'contentsize' => new external_value(PARAM_INT, 'Size of page contents in bytes (doesn\'t include'.
                                                                            ' size of attached files).', VALUE_OPTIONAL),
                        ), 'Pages'
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

    
    public static function get_page_contents_parameters() {
        return new external_function_parameters (
            array(
                'pageid' => new external_value(PARAM_INT, 'Page ID.')
            )
        );
    }

    
    public static function get_page_contents($pageid) {

        $params = self::validate_parameters(self::get_page_contents_parameters(),
                                            array(
                                                'pageid' => $pageid
                                            )
            );
        $warnings = array();

                if (!$page = wiki_get_page($params['pageid'])) {
            throw new moodle_exception('incorrectpageid', 'wiki');
        }

                if (!$wiki = wiki_get_wiki_from_pageid($params['pageid'])) {
            throw new moodle_exception('incorrectwikiid', 'wiki');
        }

                $cm = get_coursemodule_from_instance('wiki', $wiki->id, $wiki->course);
        $context = context_module::instance($cm->id);
        self::validate_context($context);

                if (!$subwiki = wiki_get_subwiki($page->subwikiid)) {
            throw new moodle_exception('incorrectsubwikiid', 'wiki');
        }
        if (!wiki_user_can_view($subwiki, $wiki)) {
            throw new moodle_exception('cannotviewpage', 'wiki');
        }

        $returnedpage = array();
        $returnedpage['id'] = $page->id;
        $returnedpage['wikiid'] = $wiki->id;
        $returnedpage['subwikiid'] = $page->subwikiid;
        $returnedpage['groupid'] = $subwiki->groupid;
        $returnedpage['userid'] = $subwiki->userid;
        $returnedpage['title'] = $page->title;

                if ($page->timerendered + WIKI_REFRESH_CACHE_TIME < time()) {
            if ($content = wiki_refresh_cachedcontent($page)) {
                $page = $content['page'];
            }
        }

        list($returnedpage['cachedcontent'], $returnedpage['contentformat']) = external_format_text(
                            $page->cachedcontent, FORMAT_HTML, $context->id, 'mod_wiki', 'attachments', $subwiki->id);
        $returnedpage['caneditpage'] = wiki_user_can_edit($subwiki);

        $result = array();
        $result['page'] = $returnedpage;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_page_contents_returns() {
        return new external_single_structure(
            array(
                'page' => new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_INT, 'Page ID.'),
                        'wikiid' => new external_value(PARAM_INT, 'Page\'s wiki ID.'),
                        'subwikiid' => new external_value(PARAM_INT, 'Page\'s subwiki ID.'),
                        'groupid' => new external_value(PARAM_INT, 'Page\'s group ID.'),
                        'userid' => new external_value(PARAM_INT, 'Page\'s user ID.'),
                        'title' => new external_value(PARAM_RAW, 'Page title.'),
                        'cachedcontent' => new external_value(PARAM_RAW, 'Page contents.'),
                        'contentformat' => new external_format_value('cachedcontent', VALUE_OPTIONAL),
                        'caneditpage' => new external_value(PARAM_BOOL, 'True if user can edit the page.')
                    ), 'Page'
                ),
                'warnings' => new external_warnings()
            )
        );
    }

    
    public static function get_subwiki_files_parameters() {
        return new external_function_parameters (
            array(
                'wikiid' => new external_value(PARAM_INT, 'Wiki instance ID.'),
                'groupid' => new external_value(PARAM_INT, 'Subwiki\'s group ID, -1 means current group. It will be ignored'
                                        . ' if the wiki doesn\'t use groups.', VALUE_DEFAULT, -1),
                'userid' => new external_value(PARAM_INT, 'Subwiki\'s user ID, 0 means current user. It will be ignored'
                                        .' in collaborative wikis.', VALUE_DEFAULT, 0)
            )
        );
    }

    
    public static function get_subwiki_files($wikiid, $groupid = -1, $userid = 0) {

        $returnedfiles = array();
        $warnings = array();

        $params = self::validate_parameters(self::get_subwiki_files_parameters(),
                                            array(
                                                'wikiid' => $wikiid,
                                                'groupid' => $groupid,
                                                'userid' => $userid
                                                )
            );

                if (!$wiki = wiki_get_wiki($params['wikiid'])) {
            throw new moodle_exception('incorrectwikiid', 'wiki');
        }
        list($course, $cm) = get_course_and_cm_from_instance($wiki, 'wiki');
        $context = context_module::instance($cm->id);
        self::validate_context($context);

                list($groupid, $userid) = self::determine_group_and_user($cm, $wiki, $params['groupid'], $params['userid']);

                $subwiki = wiki_get_subwiki_by_group_and_user_with_validation($wiki, $groupid, $userid);

                if ($subwiki === false) {
            throw new moodle_exception('cannotviewfiles', 'wiki');
        } else if ($subwiki->id != -1) {
                        $fs = get_file_storage();
            if ($files = $fs->get_area_files($context->id, 'mod_wiki', 'attachments', $subwiki->id, 'filename', false)) {
                foreach ($files as $file) {
                    $filename = $file->get_filename();
                    $fileurl = moodle_url::make_webservice_pluginfile_url(
                                    $context->id, 'mod_wiki', 'attachments', $subwiki->id, '/', $filename);

                    $returnedfiles[] = array(
                        'filename' => $filename,
                        'mimetype' => $file->get_mimetype(),
                        'fileurl'  => $fileurl->out(false),
                        'filepath' => $file->get_filepath(),
                        'filesize' => $file->get_filesize(),
                        'timemodified' => $file->get_timemodified()
                    );
                }
            }
        }

        $result = array();
        $result['files'] = $returnedfiles;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_subwiki_files_returns() {

        return new external_single_structure(
            array(
                'files' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'filename' => new external_value(PARAM_FILE, 'File name.'),
                            'filepath' => new external_value(PARAM_PATH, 'File path.'),
                            'filesize' => new external_value(PARAM_INT, 'File size.'),
                            'fileurl' => new external_value(PARAM_URL, 'Downloadable file url.'),
                            'timemodified' => new external_value(PARAM_INT, 'Time modified.'),
                            'mimetype' => new external_value(PARAM_RAW, 'File mime type.'),
                        ), 'Files'
                    )
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

    
    protected static function determine_group_and_user($cm, $wiki, $groupid = -1, $userid = 0) {
        global $USER;

        $currentgroup = groups_get_activity_group($cm);
        if ($currentgroup === false) {
                        $groupid = 0;
        } else if ($groupid == -1) {
                        $groupid = !empty($currentgroup) ? $currentgroup : 0;
        }

                if ($wiki->wikimode == 'collaborative') {
                        $userid = 0;
        } else if (empty($userid)) {
                        $userid = $USER->id;
        }

        return array($groupid, $userid);
    }

    
    public static function get_page_for_editing_parameters() {
        return new external_function_parameters (
            array(
                'pageid' => new external_value(PARAM_INT, 'Page ID to edit.'),
                'section' => new external_value(PARAM_RAW, 'Section page title.', VALUE_DEFAULT, null)
            )
        );
    }

    
    public static function get_page_for_editing($pageid, $section = null) {
        global $USER;

        $params = self::validate_parameters(self::get_page_for_editing_parameters(),
                                            array(
                                                'pageid' => $pageid,
                                                'section' => $section
                                            )
            );

        $warnings = array();

                if (!$page = wiki_get_page($params['pageid'])) {
            throw new moodle_exception('incorrectpageid', 'wiki');
        }

                if (!$wiki = wiki_get_wiki_from_pageid($params['pageid'])) {
            throw new moodle_exception('incorrectwikiid', 'wiki');
        }

                if (!$subwiki = wiki_get_subwiki($page->subwikiid)) {
            throw new moodle_exception('incorrectsubwikiid', 'wiki');
        }

                $cm = get_coursemodule_from_instance('wiki', $wiki->id, $wiki->course);
        $context = context_module::instance($cm->id);
        self::validate_context($context);

        if (!wiki_user_can_edit($subwiki)) {
            throw new moodle_exception('cannoteditpage', 'wiki');
        }

        if (!wiki_set_lock($params['pageid'], $USER->id, $params['section'], true)) {
            throw new moodle_exception('pageislocked', 'wiki');
        }

        $version = wiki_get_current_version($page->id);
        if (empty($version)) {
            throw new moodle_exception('versionerror', 'wiki');
        }

        if (!is_null($params['section'])) {
            $content = wiki_parser_proxy::get_section($version->content, $version->contentformat, $params['section']);
        } else {
            $content = $version->content;
        }

        $pagesection = array();
        $pagesection['content'] = $content;
        $pagesection['contentformat'] = $version->contentformat;
        $pagesection['version'] = $version->version;

        $result = array();
        $result['pagesection'] = $pagesection;
        $result['warnings'] = $warnings;
        return $result;

    }

    
    public static function get_page_for_editing_returns() {
        return new external_single_structure(
            array(
                'pagesection' => new external_single_structure(
                    array(
                        'content' => new external_value(PARAM_RAW, 'The contents of the page-section to be edited.'),
                        'contentformat' => new external_value(PARAM_TEXT, 'Format of the original content of the page.'),
                        'version' => new external_value(PARAM_INT, 'Latest version of the page.'),
                        'warnings' => new external_warnings()
                    )
                )
            )
        );
    }

    
    public static function new_page_parameters() {
        return new external_function_parameters (
            array(
                'title' => new external_value(PARAM_TEXT, 'New page title.'),
                'content' => new external_value(PARAM_RAW, 'Page contents.'),
                'contentformat' => new external_value(PARAM_TEXT, 'Page contents format. If an invalid format is provided, default
                    wiki format is used.', VALUE_DEFAULT, null),
                'subwikiid' => new external_value(PARAM_INT, 'Page\'s subwiki ID.', VALUE_DEFAULT, null),
                'wikiid' => new external_value(PARAM_INT, 'Page\'s wiki ID. Used if subwiki does not exists.', VALUE_DEFAULT,
                    null),
                'userid' => new external_value(PARAM_INT, 'Subwiki\'s user ID. Used if subwiki does not exists.', VALUE_DEFAULT,
                    null),
                'groupid' => new external_value(PARAM_INT, 'Subwiki\'s group ID. Used if subwiki does not exists.', VALUE_DEFAULT,
                    null)
            )
        );
    }

    
    public static function new_page($title, $content, $contentformat = null, $subwikiid = null, $wikiid = null, $userid = null,
        $groupid = null) {
        global $USER;

        $params = self::validate_parameters(self::new_page_parameters(),
                                            array(
                                                'title' => $title,
                                                'content' => $content,
                                                'contentformat' => $contentformat,
                                                'subwikiid' => $subwikiid,
                                                'wikiid' => $wikiid,
                                                'userid' => $userid,
                                                'groupid' => $groupid
                                            )
            );

        $warnings = array();

                if (!empty($params['subwikiid'])) {
            if (!$subwiki = wiki_get_subwiki($params['subwikiid'])) {
                throw new moodle_exception('incorrectsubwikiid', 'wiki');
            }

            if (!$wiki = wiki_get_wiki($subwiki->wikiid)) {
                throw new moodle_exception('incorrectwikiid', 'wiki');
            }

                        $cm = get_coursemodule_from_instance('wiki', $wiki->id, $wiki->course);
            $context = context_module::instance($cm->id);
            self::validate_context($context);

        } else {
            if (!$wiki = wiki_get_wiki($params['wikiid'])) {
                throw new moodle_exception('incorrectwikiid', 'wiki');
            }

                        $cm = get_coursemodule_from_instance('wiki', $wiki->id, $wiki->course);
            $context = context_module::instance($cm->id);
            self::validate_context($context);

                        list($groupid, $userid) = self::determine_group_and_user($cm, $wiki, $params['groupid'], $params['userid']);

                        $subwiki = wiki_get_subwiki_by_group_and_user_with_validation($wiki, $groupid, $userid);

            if ($subwiki === false) {
                                throw new moodle_exception('cannoteditpage', 'wiki');
            } else if ($subwiki->id < 0) {
                                if (!wiki_user_can_edit($subwiki)) {
                    throw new moodle_exception('cannoteditpage', 'wiki');
                }

                                $swid = wiki_add_subwiki($wiki->id, $groupid, $userid);
                if (!$subwiki = wiki_get_subwiki($swid)) {
                    throw new moodle_exception('incorrectsubwikiid', 'wiki');
                }
            }
        }

                if (!wiki_user_can_edit($subwiki)) {
            throw new moodle_exception('cannoteditpage', 'wiki');
        }

        if ($page = wiki_get_page_by_title($subwiki->id, $params['title'])) {
            throw new moodle_exception('pageexists', 'wiki');
        }

                if (!$params['contentformat'] || $wiki->forceformat) {
            $params['contentformat'] = $wiki->defaultformat;
        } else {
            $formats = wiki_get_formats();
            if (!in_array($params['contentformat'], $formats)) {
                $params['contentformat'] = $wiki->defaultformat;
            }
        }

        $newpageid = wiki_create_page($subwiki->id, $params['title'], $params['contentformat'], $USER->id);

        if (!$page = wiki_get_page($newpageid)) {
            throw new moodle_exception('incorrectpageid', 'wiki');
        }

                $save = wiki_save_page($page, $params['content'], $USER->id);

        if (!$save) {
            throw new moodle_exception('savingerror', 'wiki');
        }

        $result = array();
        $result['pageid'] = $page->id;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function new_page_returns() {
        return new external_single_structure(
            array(
                'pageid' => new external_value(PARAM_INT, 'New page id.'),
                'warnings' => new external_warnings()
            )
        );
    }

    
    public static function edit_page_parameters() {
        return new external_function_parameters (
            array(
                'pageid' => new external_value(PARAM_INT, 'Page ID.'),
                'content' => new external_value(PARAM_RAW, 'Page contents.'),
                'section' => new external_value(PARAM_RAW, 'Section page title.', VALUE_DEFAULT, null)
            )
        );
    }

    
    public static function edit_page($pageid, $content, $section = null) {
        global $USER;

        $params = self::validate_parameters(self::edit_page_parameters(),
                                            array(
                                                'pageid' => $pageid,
                                                'content' => $content,
                                                'section' => $section
                                            )
            );
        $warnings = array();

                if (!$page = wiki_get_page($params['pageid'])) {
            throw new moodle_exception('incorrectpageid', 'wiki');
        }

                if (!$wiki = wiki_get_wiki_from_pageid($params['pageid'])) {
            throw new moodle_exception('incorrectwikiid', 'wiki');
        }

                if (!$subwiki = wiki_get_subwiki($page->subwikiid)) {
            throw new moodle_exception('incorrectsubwikiid', 'wiki');
        }

                $cm = get_coursemodule_from_instance('wiki', $wiki->id, $wiki->course);
        $context = context_module::instance($cm->id);
        self::validate_context($context);

        if (!wiki_user_can_edit($subwiki)) {
            throw new moodle_exception('cannoteditpage', 'wiki');
        }

        if (wiki_is_page_section_locked($page->id, $USER->id, $params['section'])) {
            throw new moodle_exception('pageislocked', 'wiki');
        }

                if (!is_null($params['section'])) {
            $version = wiki_get_current_version($page->id);
            $content = wiki_parser_proxy::get_section($version->content, $version->contentformat, $params['section'], false);
            if (!$content) {
                throw new moodle_exception('invalidsection', 'wiki');
            }

            $save = wiki_save_section($page, $params['section'], $params['content'], $USER->id);
        } else {
            $save = wiki_save_page($page, $params['content'], $USER->id);
        }

        wiki_delete_locks($page->id, $USER->id, $params['section']);

        if (!$save) {
            throw new moodle_exception('savingerror', 'wiki');
        }

        $result = array();
        $result['pageid'] = $page->id;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function edit_page_returns() {
        return new external_single_structure(
            array(
                'pageid' => new external_value(PARAM_INT, 'Edited page id.'),
                'warnings' => new external_warnings()
            )
        );
    }

}
