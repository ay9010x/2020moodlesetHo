<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/mod/glossary/lib.php');


class mod_glossary_external extends external_api {

    
    protected static function get_browse_modes_from_display_format($format) {
        global $DB;

        $formats = array();
        $dp = $DB->get_record('glossary_formats', array('name' => $format), '*', IGNORE_MISSING);
        if ($dp) {
            $formats = glossary_get_visible_tabs($dp);
        }

                $modes = array('letter');

        if (in_array('category', $formats)) {
            $modes[] = 'cat';
        }
        if (in_array('date', $formats)) {
            $modes[] = 'date';
        }
        if (in_array('author', $formats)) {
            $modes[] = 'author';
        }

        return $modes;
    }

    
    protected static function get_entry_return_structure($includecat = false) {
        $params = array(
            'id' => new external_value(PARAM_INT, 'The entry ID'),
            'glossaryid' => new external_value(PARAM_INT, 'The glossary ID'),
            'userid' => new external_value(PARAM_INT, 'Author ID'),
            'userfullname' => new external_value(PARAM_NOTAGS, 'Author full name'),
            'userpictureurl' => new external_value(PARAM_URL, 'Author picture'),
            'concept' => new external_value(PARAM_RAW, 'The concept'),
            'definition' => new external_value(PARAM_RAW, 'The definition'),
            'definitionformat' => new external_format_value('definition'),
            'definitiontrust' => new external_value(PARAM_BOOL, 'The definition trust flag'),
            'attachment' => new external_value(PARAM_BOOL, 'Whether or not the entry has attachments'),
            'attachments' => new external_multiple_structure(
                new external_single_structure(array(
                    'filename' => new external_value(PARAM_FILE, 'File name'),
                    'mimetype' => new external_value(PARAM_RAW, 'Mime type'),
                    'fileurl'  => new external_value(PARAM_URL, 'File download URL')
                )), 'attachments', VALUE_OPTIONAL
            ),
            'timecreated' => new external_value(PARAM_INT, 'Time created'),
            'timemodified' => new external_value(PARAM_INT, 'Time modified'),
            'teacherentry' => new external_value(PARAM_BOOL, 'The entry was created by a teacher, or equivalent.'),
            'sourceglossaryid' => new external_value(PARAM_INT, 'The source glossary ID'),
            'usedynalink' => new external_value(PARAM_BOOL, 'Whether the concept should be automatically linked'),
            'casesensitive' => new external_value(PARAM_BOOL, 'When true, the matching is case sensitive'),
            'fullmatch' => new external_value(PARAM_BOOL, 'When true, the matching is done on full words only'),
            'approved' => new external_value(PARAM_BOOL, 'Whether the entry was approved'),
        );

        if ($includecat) {
            $params['categoryid'] = new external_value(PARAM_INT, 'The category ID. This may be' .
                ' \''. GLOSSARY_SHOW_NOT_CATEGORISED . '\' when the entry is not categorised', VALUE_DEFAULT,
                GLOSSARY_SHOW_NOT_CATEGORISED);
            $params['categoryname'] = new external_value(PARAM_RAW, 'The category name. May be empty when the entry is' .
                ' not categorised, or the request was limited to one category.', VALUE_DEFAULT, '');
        }

        return new external_single_structure($params);
    }

    
    protected static function fill_entry_details($entry, $context) {
        global $PAGE;
        $canviewfullnames = has_capability('moodle/site:viewfullnames', $context);

                $entry->concept = external_format_string($entry->concept, $context->id);
        list($entry->definition, $entry->definitionformat) = external_format_text($entry->definition, $entry->definitionformat,
            $context->id, 'mod_glossary', 'entry', $entry->id);

                $user = mod_glossary_entry_query_builder::get_user_from_record($entry);
        $userpicture = new user_picture($user);
        $userpicture->size = 1;
        $entry->userfullname = fullname($user, $canviewfullnames);
        $entry->userpictureurl = $userpicture->get_url($PAGE)->out(false);

                $entry->attachment = !empty($entry->attachment) ? 1 : 0;
        $entry->attachments = array();
        if ($entry->attachment) {
            $fs = get_file_storage();
            if ($files = $fs->get_area_files($context->id, 'mod_glossary', 'attachment', $entry->id, 'filename', false)) {
                foreach ($files as $file) {
                    $filename = $file->get_filename();
                    $fileurl = moodle_url::make_webservice_pluginfile_url($context->id, 'mod_glossary', 'attachment',
                        $entry->id, '/', $filename);
                    $entry->attachments[] = array(
                        'filename' => $filename,
                        'mimetype' => $file->get_mimetype(),
                        'fileurl'  => $fileurl->out(false)
                    );
                }
            }
        }
    }

    
    protected static function validate_glossary($id) {
        global $DB;
        $glossary = $DB->get_record('glossary', array('id' => $id), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($glossary, 'glossary');
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        return array($glossary, $context, $course, $cm);
    }

    
    public static function get_glossaries_by_courses_parameters() {
        return new external_function_parameters (
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'course id'),
                    'Array of course IDs', VALUE_DEFAULT, array()
                ),
            )
        );
    }

    
    public static function get_glossaries_by_courses($courseids = array()) {
        $params = self::validate_parameters(self::get_glossaries_by_courses_parameters(), array('courseids' => $courseids));

        $warnings = array();
        $courses = array();
        $courseids = $params['courseids'];

        if (empty($courseids)) {
            $courses = enrol_get_my_courses();
            $courseids = array_keys($courses);
        }

                $glossaries = array();
        $modes = array();

                if (!empty($courseids)) {
            list($courses, $warnings) = external_util::validate_courses($courseids, $courses);

                        $glossaries = get_all_instances_in_courses('glossary', $courses);
            foreach ($glossaries as $glossary) {
                $context = context_module::instance($glossary->coursemodule);
                $glossary->name = external_format_string($glossary->name, $context->id);
                list($glossary->intro, $glossary->introformat) = external_format_text($glossary->intro, $glossary->introformat,
                    $context->id, 'mod_glossary', 'intro', null);

                                if (!$glossary->entbypage) {
                    $glossary->entbypage = $CFG->glossary_entbypage;
                }

                                if (!isset($modes[$glossary->displayformat])) {
                    $modes[$glossary->displayformat] = self::get_browse_modes_from_display_format($glossary->displayformat);
                }
                $glossary->browsemodes = $modes[$glossary->displayformat];
            }
        }

        $result = array();
        $result['glossaries'] = $glossaries;
        $result['warnings'] = $warnings;
        return $result;
    }

    
    public static function get_glossaries_by_courses_returns() {
        return new external_single_structure(array(
            'glossaries' => new external_multiple_structure(
                new external_single_structure(array(
                    'id' => new external_value(PARAM_INT, 'Glossary id'),
                    'coursemodule' => new external_value(PARAM_INT, 'Course module id'),
                    'course' => new external_value(PARAM_INT, 'Course id'),
                    'name' => new external_value(PARAM_RAW, 'Glossary name'),
                    'intro' => new external_value(PARAM_RAW, 'The Glossary intro'),
                    'introformat' => new external_format_value('intro'),
                    'allowduplicatedentries' => new external_value(PARAM_INT, 'If enabled, multiple entries can have the' .
                        ' same concept name'),
                    'displayformat' => new external_value(PARAM_TEXT, 'Display format type'),
                    'mainglossary' => new external_value(PARAM_INT, 'If enabled this glossary is a main glossary.'),
                    'showspecial' => new external_value(PARAM_INT, 'If enabled, participants can browse the glossary by' .
                        ' special characters, such as @ and #'),
                    'showalphabet' => new external_value(PARAM_INT, 'If enabled, participants can browse the glossary by' .
                        ' letters of the alphabet'),
                    'showall' => new external_value(PARAM_INT, 'If enabled, participants can browse all entries at once'),
                    'allowcomments' => new external_value(PARAM_INT, 'If enabled, all participants with permission to' .
                        ' create comments will be able to add comments to glossary entries'),
                    'allowprintview' => new external_value(PARAM_INT, 'If enabled, students are provided with a link to a' .
                        ' printer-friendly version of the glossary. The link is always available to teachers'),
                    'usedynalink' => new external_value(PARAM_INT, 'If site-wide glossary auto-linking has been enabled' .
                        ' by an administrator and this checkbox is ticked, the entry will be automatically linked' .
                        ' wherever the concept words and phrases appear throughout the rest of the course.'),
                    'defaultapproval' => new external_value(PARAM_INT, 'If set to no, entries require approving by a' .
                        ' teacher before they are viewable by everyone.'),
                    'approvaldisplayformat' => new external_value(PARAM_TEXT, 'When approving glossary items you may wish' .
                        ' to use a different display format'),
                    'globalglossary' => new external_value(PARAM_INT, ''),
                    'entbypage' => new external_value(PARAM_INT, 'Entries shown per page'),
                    'editalways' => new external_value(PARAM_INT, 'Always allow editing'),
                    'rsstype' => new external_value(PARAM_INT, 'To enable the RSS feed for this activity, select either' .
                        ' concepts with author or concepts without author to be included in the feed'),
                    'rssarticles' => new external_value(PARAM_INT, 'This setting specifies the number of glossary entry' .
                        ' concepts to include in the RSS feed. Between 5 and 20 generally acceptable'),
                    'assessed' => new external_value(PARAM_INT, 'Aggregate type'),
                    'assesstimestart' => new external_value(PARAM_INT, 'Restrict rating to items created after this'),
                    'assesstimefinish' => new external_value(PARAM_INT, 'Restrict rating to items created before this'),
                    'scale' => new external_value(PARAM_INT, 'Scale ID'),
                    'timecreated' => new external_value(PARAM_INT, 'Time created'),
                    'timemodified' => new external_value(PARAM_INT, 'Time modified'),
                    'completionentries' => new external_value(PARAM_INT, 'Number of entries to complete'),
                    'section' => new external_value(PARAM_INT, 'Section'),
                    'visible' => new external_value(PARAM_INT, 'Visible'),
                    'groupmode' => new external_value(PARAM_INT, 'Group mode'),
                    'groupingid' => new external_value(PARAM_INT, 'Grouping ID'),
                    'browsemodes' => new external_multiple_structure(
                        new external_value(PARAM_ALPHA, 'Modes of browsing allowed')
                    )
                ), 'Glossaries')
            ),
            'warnings' => new external_warnings())
        );
    }

    
    public static function view_glossary_parameters() {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT, 'Glossary instance ID'),
            'mode' => new external_value(PARAM_ALPHA, 'The mode in which the glossary is viewed'),
        ));
    }

    
    public static function view_glossary($id, $mode) {
        $params = self::validate_parameters(self::view_glossary_parameters(), array(
            'id' => $id,
            'mode' => $mode
        ));
        $id = $params['id'];
        $mode = $params['mode'];
        $warnings = array();

                list($glossary, $context, $course, $cm) = self::validate_glossary($id);

                glossary_view($glossary, $course, $cm, $context, $mode);

        return array(
            'status' => true,
            'warnings' => $warnings
        );
    }

    
    public static function view_glossary_returns() {
        return new external_single_structure(array(
            'status' => new external_value(PARAM_BOOL, 'True on success'),
            'warnings' => new external_warnings()
        ));
    }

    
    public static function view_entry_parameters() {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT, 'Glossary entry ID'),
        ));
    }

    
    public static function view_entry($id) {
        global $DB, $USER;

        $params = self::validate_parameters(self::view_entry_parameters(), array('id' => $id));
        $id = $params['id'];
        $warnings = array();

                $entry = $DB->get_record('glossary_entries', array('id' => $id), '*', MUST_EXIST);
        list($glossary, $context, $course, $cm) = self::validate_glossary($entry->glossaryid);

        if (!glossary_can_view_entry($entry, $cm)) {
            throw new invalid_parameter_exception('invalidentry');
        }

                glossary_entry_view($entry, $context);

        return array(
            'status' => true,
            'warnings' => $warnings
        );
    }

    
    public static function view_entry_returns() {
        return new external_single_structure(array(
            'status' => new external_value(PARAM_BOOL, 'True on success'),
            'warnings' => new external_warnings()
        ));
    }

    
    public static function get_entries_by_letter_parameters() {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT, 'Glossary entry ID'),
            'letter' => new external_value(PARAM_ALPHA, 'A letter, or either keywords: \'ALL\' or \'SPECIAL\'.'),
            'from' => new external_value(PARAM_INT, 'Start returning records from here', VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Number of records to return', VALUE_DEFAULT, 20),
            'options' => new external_single_structure(array(
                'includenotapproved' => new external_value(PARAM_BOOL, 'When false, includes the non-approved entries created by' .
                    ' the user. When true, also includes the ones that the user has the permission to approve.', VALUE_DEFAULT, 0)
            ), 'An array of options', VALUE_DEFAULT, array())
        ));
    }

    
    public static function get_entries_by_letter($id, $letter, $from, $limit, $options) {
        $params = self::validate_parameters(self::get_entries_by_letter_parameters(), array(
            'id' => $id,
            'letter' => $letter,
            'from' => $from,
            'limit' => $limit,
            'options' => $options,
        ));
        $id = $params['id'];
        $letter = $params['letter'];
        $from = $params['from'];
        $limit = $params['limit'];
        $options = $params['options'];
        $warnings = array();

                list($glossary, $context) = self::validate_glossary($id);

                $modes = self::get_browse_modes_from_display_format($glossary->displayformat);
        if (!in_array('letter', $modes)) {
            throw new invalid_parameter_exception('invalidbrowsemode');
        }

        $entries = array();
        list($records, $count) = glossary_get_entries_by_letter($glossary, $context, $letter, $from, $limit, $options);
        foreach ($records as $key => $record) {
            self::fill_entry_details($record, $context);
            $entries[] = $record;
        }

        return array(
            'count' => $count,
            'entries' => $entries,
            'warnings' => $warnings
        );
    }

    
    public static function get_entries_by_letter_returns() {
        return new external_single_structure(array(
            'count' => new external_value(PARAM_INT, 'The total number of records matching the request.'),
            'entries' => new external_multiple_structure(
                self::get_entry_return_structure()
            ),
            'warnings' => new external_warnings()
        ));
    }

    
    public static function get_entries_by_date_parameters() {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT, 'Glossary entry ID'),
            'order' => new external_value(PARAM_ALPHA, 'Order the records by: \'CREATION\' or \'UPDATE\'.',
                VALUE_DEFAULT, 'UPDATE'),
            'sort' => new external_value(PARAM_ALPHA, 'The direction of the order: \'ASC\' or \'DESC\'', VALUE_DEFAULT, 'DESC'),
            'from' => new external_value(PARAM_INT, 'Start returning records from here', VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Number of records to return', VALUE_DEFAULT, 20),
            'options' => new external_single_structure(array(
                'includenotapproved' => new external_value(PARAM_BOOL, 'When false, includes the non-approved entries created by' .
                    ' the user. When true, also includes the ones that the user has the permission to approve.', VALUE_DEFAULT, 0)
            ), 'An array of options', VALUE_DEFAULT, array())
        ));
    }

    
    public static function get_entries_by_date($id, $order, $sort, $from, $limit, $options) {
        $params = self::validate_parameters(self::get_entries_by_date_parameters(), array(
            'id' => $id,
            'order' => core_text::strtoupper($order),
            'sort' => core_text::strtoupper($sort),
            'from' => $from,
            'limit' => $limit,
            'options' => $options,
        ));
        $id = $params['id'];
        $order = $params['order'];
        $sort = $params['sort'];
        $from = $params['from'];
        $limit = $params['limit'];
        $options = $params['options'];
        $warnings = array();

        if (!in_array($order, array('CREATION', 'UPDATE'))) {
            throw new invalid_parameter_exception('invalidorder');
        } else if (!in_array($sort, array('ASC', 'DESC'))) {
            throw new invalid_parameter_exception('invalidsort');
        }

                list($glossary, $context) = self::validate_glossary($id);

                $modes = self::get_browse_modes_from_display_format($glossary->displayformat);
        if (!in_array('date', $modes)) {
            throw new invalid_parameter_exception('invalidbrowsemode');
        }

        $entries = array();
        list($records, $count) = glossary_get_entries_by_date($glossary, $context, $order, $sort, $from, $limit, $options);
        foreach ($records as $key => $record) {
            self::fill_entry_details($record, $context);
            $entries[] = $record;
        }

        return array(
            'count' => $count,
            'entries' => $entries,
            'warnings' => $warnings
        );
    }

    
    public static function get_entries_by_date_returns() {
        return new external_single_structure(array(
            'count' => new external_value(PARAM_INT, 'The total number of records matching the request.'),
            'entries' => new external_multiple_structure(
                self::get_entry_return_structure()
            ),
            'warnings' => new external_warnings()
        ));
    }

    
    public static function get_categories_parameters() {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT, 'The glossary ID'),
            'from' => new external_value(PARAM_INT, 'Start returning records from here', VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Number of records to return', VALUE_DEFAULT, 20)
        ));
    }

    
    public static function get_categories($id, $from, $limit) {
        $params = self::validate_parameters(self::get_categories_parameters(), array(
            'id' => $id,
            'from' => $from,
            'limit' => $limit
        ));
        $id = $params['id'];
        $from = $params['from'];
        $limit = $params['limit'];
        $warnings = array();

                list($glossary, $context) = self::validate_glossary($id);

                $categories = array();
        list($records, $count) = glossary_get_categories($glossary, $from, $limit);
        foreach ($records as $category) {
            $category->name = external_format_string($category->name, $context->id);
            $categories[] = $category;
        }

        return array(
            'count' => $count,
            'categories' => $categories,
            'warnings' => array(),
        );
    }

    
    public static function get_categories_returns() {
        return new external_single_structure(array(
            'count' => new external_value(PARAM_INT, 'The total number of records.'),
            'categories' => new external_multiple_structure(
                new external_single_structure(array(
                    'id' => new external_value(PARAM_INT, 'The category ID'),
                    'glossaryid' => new external_value(PARAM_INT, 'The glossary ID'),
                    'name' => new external_value(PARAM_RAW, 'The name of the category'),
                    'usedynalink' => new external_value(PARAM_BOOL, 'Whether the category is automatically linked'),
                ))
            ),
            'warnings' => new external_warnings()
        ));
    }

    
    public static function get_entries_by_category_parameters() {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT, 'The glossary ID.'),
            'categoryid' => new external_value(PARAM_INT, 'The category ID. Use \'' . GLOSSARY_SHOW_ALL_CATEGORIES . '\' for all' .
                ' categories, or \'' . GLOSSARY_SHOW_NOT_CATEGORISED . '\' for uncategorised entries.'),
            'from' => new external_value(PARAM_INT, 'Start returning records from here', VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Number of records to return', VALUE_DEFAULT, 20),
            'options' => new external_single_structure(array(
                'includenotapproved' => new external_value(PARAM_BOOL, 'When false, includes the non-approved entries created by' .
                    ' the user. When true, also includes the ones that the user has the permission to approve.', VALUE_DEFAULT, 0)
            ), 'An array of options', VALUE_DEFAULT, array())
        ));
    }

    
    public static function get_entries_by_category($id, $categoryid, $from, $limit, $options) {
        global $DB;

        $params = self::validate_parameters(self::get_entries_by_category_parameters(), array(
            'id' => $id,
            'categoryid' => $categoryid,
            'from' => $from,
            'limit' => $limit,
            'options' => $options,
        ));
        $id = $params['id'];
        $categoryid = $params['categoryid'];
        $from = $params['from'];
        $limit = $params['limit'];
        $options = $params['options'];
        $warnings = array();

                list($glossary, $context) = self::validate_glossary($id);

                $modes = self::get_browse_modes_from_display_format($glossary->displayformat);
        if (!in_array('cat', $modes)) {
            throw new invalid_parameter_exception('invalidbrowsemode');
        }

                if (in_array($categoryid, array(GLOSSARY_SHOW_ALL_CATEGORIES, GLOSSARY_SHOW_NOT_CATEGORISED))) {
                    } else if (!$DB->record_exists('glossary_categories', array('id' => $categoryid, 'glossaryid' => $id))) {
            throw new invalid_parameter_exception('invalidcategory');
        }

                $entries = array();
        list($records, $count) = glossary_get_entries_by_category($glossary, $context, $categoryid, $from, $limit, $options);
        foreach ($records as $key => $record) {
            self::fill_entry_details($record, $context);
            if ($record->categoryid === null) {
                $record->categoryid = GLOSSARY_SHOW_NOT_CATEGORISED;
            }
            if (isset($record->categoryname)) {
                $record->categoryname = external_format_string($record->categoryname, $context->id);
            }
            $entries[] = $record;
        }

        return array(
            'count' => $count,
            'entries' => $entries,
            'warnings' => $warnings
        );
    }

    
    public static function get_entries_by_category_returns() {
        return new external_single_structure(array(
            'count' => new external_value(PARAM_INT, 'The total number of records matching the request.'),
            'entries' => new external_multiple_structure(
                self::get_entry_return_structure(true)
            ),
            'warnings' => new external_warnings()
        ));
    }

    
    public static function get_authors_parameters() {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT, 'Glossary entry ID'),
            'from' => new external_value(PARAM_INT, 'Start returning records from here', VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Number of records to return', VALUE_DEFAULT, 20),
            'options' => new external_single_structure(array(
                'includenotapproved' => new external_value(PARAM_BOOL, 'When false, includes self even if all of their entries' .
                    ' require approval. When true, also includes authors only having entries pending approval.', VALUE_DEFAULT, 0)
            ), 'An array of options', VALUE_DEFAULT, array())
        ));
    }

    
    public static function get_authors($id, $from, $limit, $options) {
        global $PAGE;

        $params = self::validate_parameters(self::get_authors_parameters(), array(
            'id' => $id,
            'from' => $from,
            'limit' => $limit,
            'options' => $options,
        ));
        $id = $params['id'];
        $from = $params['from'];
        $limit = $params['limit'];
        $options = $params['options'];
        $warnings = array();

                list($glossary, $context) = self::validate_glossary($id);

                list($users, $count) = glossary_get_authors($glossary, $context, $limit, $from, $options);

        $canviewfullnames = has_capability('moodle/site:viewfullnames', $context);
        foreach ($users as $user) {
            $userpicture = new user_picture($user);
            $userpicture->size = 1;

            $author = new stdClass();
            $author->id = $user->id;
            $author->fullname = fullname($user, $canviewfullnames);
            $author->pictureurl = $userpicture->get_url($PAGE)->out(false);
            $authors[] = $author;
        }
        $users->close();

        return array(
            'count' => $count,
            'authors' => $authors,
            'warnings' => array(),
        );
    }

    
    public static function get_authors_returns() {
        return new external_single_structure(array(
            'count' => new external_value(PARAM_INT, 'The total number of records.'),
            'authors' => new external_multiple_structure(
                new external_single_structure(array(
                    'id' => new external_value(PARAM_INT, 'The user ID'),
                    'fullname' => new external_value(PARAM_NOTAGS, 'The fullname'),
                    'pictureurl' => new external_value(PARAM_URL, 'The picture URL'),
                ))
            ),
            'warnings' => new external_warnings()
        ));
    }

    
    public static function get_entries_by_author_parameters() {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT, 'Glossary entry ID'),
            'letter' => new external_value(PARAM_ALPHA, 'First letter of firstname or lastname, or either keywords:'
                . ' \'ALL\' or \'SPECIAL\'.'),
            'field' => new external_value(PARAM_ALPHA, 'Search and order using: \'FIRSTNAME\' or \'LASTNAME\'', VALUE_DEFAULT,
                'LASTNAME'),
            'sort' => new external_value(PARAM_ALPHA, 'The direction of the order: \'ASC\' or \'DESC\'', VALUE_DEFAULT, 'ASC'),
            'from' => new external_value(PARAM_INT, 'Start returning records from here', VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Number of records to return', VALUE_DEFAULT, 20),
            'options' => new external_single_structure(array(
                'includenotapproved' => new external_value(PARAM_BOOL, 'When false, includes the non-approved entries created by' .
                    ' the user. When true, also includes the ones that the user has the permission to approve.', VALUE_DEFAULT, 0)
            ), 'An array of options', VALUE_DEFAULT, array())
        ));
    }

    
    public static function get_entries_by_author($id, $letter, $field, $sort, $from, $limit, $options) {
        $params = self::validate_parameters(self::get_entries_by_author_parameters(), array(
            'id' => $id,
            'letter' => $letter,
            'field' => core_text::strtoupper($field),
            'sort' => core_text::strtoupper($sort),
            'from' => $from,
            'limit' => $limit,
            'options' => $options,
        ));
        $id = $params['id'];
        $letter = $params['letter'];
        $field = $params['field'];
        $sort = $params['sort'];
        $from = $params['from'];
        $limit = $params['limit'];
        $options = $params['options'];
        $warnings = array();

        if (!in_array($field, array('FIRSTNAME', 'LASTNAME'))) {
            throw new invalid_parameter_exception('invalidfield');
        } else if (!in_array($sort, array('ASC', 'DESC'))) {
            throw new invalid_parameter_exception('invalidsort');
        }

                list($glossary, $context) = self::validate_glossary($id);

                $modes = self::get_browse_modes_from_display_format($glossary->displayformat);
        if (!in_array('author', $modes)) {
            throw new invalid_parameter_exception('invalidbrowsemode');
        }

                $entries = array();
        list($records, $count) = glossary_get_entries_by_author($glossary, $context, $letter, $field, $sort, $from, $limit,
            $options);
        foreach ($records as $key => $record) {
            self::fill_entry_details($record, $context);
            $entries[] = $record;
        }

        return array(
            'count' => $count,
            'entries' => $entries,
            'warnings' => $warnings
        );
    }

    
    public static function get_entries_by_author_returns() {
        return new external_single_structure(array(
            'count' => new external_value(PARAM_INT, 'The total number of records matching the request.'),
            'entries' => new external_multiple_structure(
                self::get_entry_return_structure()
            ),
            'warnings' => new external_warnings()
        ));
    }

    
    public static function get_entries_by_author_id_parameters() {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT, 'Glossary entry ID'),
            'authorid' => new external_value(PARAM_INT, 'The author ID'),
            'order' => new external_value(PARAM_ALPHA, 'Order by: \'CONCEPT\', \'CREATION\' or \'UPDATE\'', VALUE_DEFAULT,
                'CONCEPT'),
            'sort' => new external_value(PARAM_ALPHA, 'The direction of the order: \'ASC\' or \'DESC\'', VALUE_DEFAULT, 'ASC'),
            'from' => new external_value(PARAM_INT, 'Start returning records from here', VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Number of records to return', VALUE_DEFAULT, 20),
            'options' => new external_single_structure(array(
                'includenotapproved' => new external_value(PARAM_BOOL, 'When false, includes the non-approved entries created by' .
                    ' the user. When true, also includes the ones that the user has the permission to approve.', VALUE_DEFAULT, 0)
            ), 'An array of options', VALUE_DEFAULT, array())
        ));
    }

    
    public static function get_entries_by_author_id($id, $authorid, $order, $sort, $from, $limit, $options) {
        $params = self::validate_parameters(self::get_entries_by_author_id_parameters(), array(
            'id' => $id,
            'authorid' => $authorid,
            'order' => core_text::strtoupper($order),
            'sort' => core_text::strtoupper($sort),
            'from' => $from,
            'limit' => $limit,
            'options' => $options,
        ));
        $id = $params['id'];
        $authorid = $params['authorid'];
        $order = $params['order'];
        $sort = $params['sort'];
        $from = $params['from'];
        $limit = $params['limit'];
        $options = $params['options'];
        $warnings = array();

        if (!in_array($order, array('CONCEPT', 'CREATION', 'UPDATE'))) {
            throw new invalid_parameter_exception('invalidorder');
        } else if (!in_array($sort, array('ASC', 'DESC'))) {
            throw new invalid_parameter_exception('invalidsort');
        }

                list($glossary, $context) = self::validate_glossary($id);

                $modes = self::get_browse_modes_from_display_format($glossary->displayformat);
        if (!in_array('author', $modes)) {
            throw new invalid_parameter_exception('invalidbrowsemode');
        }

                $entries = array();
        list($records, $count) = glossary_get_entries_by_author_id($glossary, $context, $authorid, $order, $sort, $from,
            $limit, $options);
        foreach ($records as $key => $record) {
            self::fill_entry_details($record, $context);
            $entries[] = $record;
        }

        return array(
            'count' => $count,
            'entries' => $entries,
            'warnings' => $warnings
        );
    }

    
    public static function get_entries_by_author_id_returns() {
        return new external_single_structure(array(
            'count' => new external_value(PARAM_INT, 'The total number of records matching the request.'),
            'entries' => new external_multiple_structure(
                self::get_entry_return_structure()
            ),
            'warnings' => new external_warnings()
        ));
    }

    
    public static function get_entries_by_search_parameters() {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT, 'Glossary entry ID'),
            'query' => new external_value(PARAM_NOTAGS, 'The query string'),
            'fullsearch' => new external_value(PARAM_BOOL, 'The query', VALUE_DEFAULT, 1),
            'order' => new external_value(PARAM_ALPHA, 'Order by: \'CONCEPT\', \'CREATION\' or \'UPDATE\'', VALUE_DEFAULT,
                'CONCEPT'),
            'sort' => new external_value(PARAM_ALPHA, 'The direction of the order: \'ASC\' or \'DESC\'', VALUE_DEFAULT, 'ASC'),
            'from' => new external_value(PARAM_INT, 'Start returning records from here', VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Number of records to return', VALUE_DEFAULT, 20),
            'options' => new external_single_structure(array(
                'includenotapproved' => new external_value(PARAM_BOOL, 'When false, includes the non-approved entries created by' .
                    ' the user. When true, also includes the ones that the user has the permission to approve.', VALUE_DEFAULT, 0)
            ), 'An array of options', VALUE_DEFAULT, array())
        ));
    }

    
    public static function get_entries_by_search($id, $query, $fullsearch, $order, $sort, $from, $limit, $options) {
        $params = self::validate_parameters(self::get_entries_by_search_parameters(), array(
            'id' => $id,
            'query' => $query,
            'fullsearch' => $fullsearch,
            'order' => core_text::strtoupper($order),
            'sort' => core_text::strtoupper($sort),
            'from' => $from,
            'limit' => $limit,
            'options' => $options,
        ));
        $id = $params['id'];
        $query = $params['query'];
        $fullsearch = $params['fullsearch'];
        $order = $params['order'];
        $sort = $params['sort'];
        $from = $params['from'];
        $limit = $params['limit'];
        $options = $params['options'];
        $warnings = array();

        if (!in_array($order, array('CONCEPT', 'CREATION', 'UPDATE'))) {
            throw new invalid_parameter_exception('invalidorder');
        } else if (!in_array($sort, array('ASC', 'DESC'))) {
            throw new invalid_parameter_exception('invalidsort');
        }

                list($glossary, $context) = self::validate_glossary($id);

                $entries = array();
        list($records, $count) = glossary_get_entries_by_search($glossary, $context, $query, $fullsearch, $order, $sort, $from,
            $limit, $options);
        foreach ($records as $key => $record) {
            self::fill_entry_details($record, $context);
            $entries[] = $record;
        }

        return array(
            'count' => $count,
            'entries' => $entries,
            'warnings' => $warnings
        );
    }

    
    public static function get_entries_by_search_returns() {
        return new external_single_structure(array(
            'count' => new external_value(PARAM_INT, 'The total number of records matching the request.'),
            'entries' => new external_multiple_structure(
                self::get_entry_return_structure()
            ),
            'warnings' => new external_warnings()
        ));
    }

    
    public static function get_entries_by_term_parameters() {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT, 'Glossary entry ID'),
            'term' => new external_value(PARAM_NOTAGS, 'The entry concept, or alias'),
            'from' => new external_value(PARAM_INT, 'Start returning records from here', VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Number of records to return', VALUE_DEFAULT, 20),
            'options' => new external_single_structure(array(
                'includenotapproved' => new external_value(PARAM_BOOL, 'When false, includes the non-approved entries created by' .
                    ' the user. When true, also includes the ones that the user has the permission to approve.', VALUE_DEFAULT, 0)
            ), 'An array of options', VALUE_DEFAULT, array())
        ));
    }

    
    public static function get_entries_by_term($id, $term, $from, $limit, $options) {
        $params = self::validate_parameters(self::get_entries_by_term_parameters(), array(
            'id' => $id,
            'term' => $term,
            'from' => $from,
            'limit' => $limit,
            'options' => $options,
        ));
        $id = $params['id'];
        $term = $params['term'];
        $from = $params['from'];
        $limit = $params['limit'];
        $options = $params['options'];
        $warnings = array();

                list($glossary, $context) = self::validate_glossary($id);

                $entries = array();
        list($records, $count) = glossary_get_entries_by_term($glossary, $context, $term, $from, $limit, $options);
        foreach ($records as $key => $record) {
            self::fill_entry_details($record, $context);
            $entries[] = $record;
        }

        return array(
            'count' => $count,
            'entries' => $entries,
            'warnings' => $warnings
        );
    }

    
    public static function get_entries_by_term_returns() {
        return new external_single_structure(array(
            'count' => new external_value(PARAM_INT, 'The total number of records matching the request.'),
            'entries' => new external_multiple_structure(
                self::get_entry_return_structure()
            ),
            'warnings' => new external_warnings()
        ));
    }

    
    public static function get_entries_to_approve_parameters() {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT, 'Glossary entry ID'),
            'letter' => new external_value(PARAM_ALPHA, 'A letter, or either keywords: \'ALL\' or \'SPECIAL\'.'),
            'order' => new external_value(PARAM_ALPHA, 'Order by: \'CONCEPT\', \'CREATION\' or \'UPDATE\'', VALUE_DEFAULT,
                'CONCEPT'),
            'sort' => new external_value(PARAM_ALPHA, 'The direction of the order: \'ASC\' or \'DESC\'', VALUE_DEFAULT, 'ASC'),
            'from' => new external_value(PARAM_INT, 'Start returning records from here', VALUE_DEFAULT, 0),
            'limit' => new external_value(PARAM_INT, 'Number of records to return', VALUE_DEFAULT, 20),
            'options' => new external_single_structure(array(), 'An array of options', VALUE_DEFAULT, array())
        ));
    }

    
    public static function get_entries_to_approve($id, $letter, $order, $sort, $from, $limit) {
        $params = self::validate_parameters(self::get_entries_to_approve_parameters(), array(
            'id' => $id,
            'letter' => $letter,
            'order' => $order,
            'sort' => $sort,
            'from' => $from,
            'limit' => $limit
        ));
        $id = $params['id'];
        $letter = $params['letter'];
        $order = $params['order'];
        $sort = $params['sort'];
        $from = $params['from'];
        $limit = $params['limit'];
        $warnings = array();

                list($glossary, $context) = self::validate_glossary($id);

                require_capability('mod/glossary:approve', $context);

                $entries = array();
        list($records, $count) = glossary_get_entries_to_approve($glossary, $context, $letter, $order, $sort, $from, $limit);
        foreach ($records as $key => $record) {
            self::fill_entry_details($record, $context);
            $entries[] = $record;
        }

        return array(
            'count' => $count,
            'entries' => $entries,
            'warnings' => $warnings
        );
    }

    
    public static function get_entries_to_approve_returns() {
        return new external_single_structure(array(
            'count' => new external_value(PARAM_INT, 'The total number of records matching the request.'),
            'entries' => new external_multiple_structure(
                self::get_entry_return_structure()
            ),
            'warnings' => new external_warnings()
        ));
    }

    
    public static function get_entry_by_id_parameters() {
        return new external_function_parameters(array(
            'id' => new external_value(PARAM_INT, 'Glossary entry ID'),
        ));
    }

    
    public static function get_entry_by_id($id) {
        global $DB, $USER;

        $params = self::validate_parameters(self::get_entry_by_id_parameters(), array('id' => $id));
        $id = $params['id'];
        $warnings = array();

                $entry = $DB->get_record('glossary_entries', array('id' => $id), '*', MUST_EXIST);
        list($glossary, $context) = self::validate_glossary($entry->glossaryid);

        if (empty($entry->approved) && $entry->userid != $USER->id && !has_capability('mod/glossary:approve', $context)) {
            throw new invalid_parameter_exception('invalidentry');
        }

        $entry = glossary_get_entry_by_id($id);
        self::fill_entry_details($entry, $context);

        return array(
            'entry' => $entry,
            'warnings' => $warnings
        );
    }

    
    public static function get_entry_by_id_returns() {
        return new external_single_structure(array(
            'entry' => self::get_entry_return_structure(),
            'warnings' => new external_warnings()
        ));
    }

}
