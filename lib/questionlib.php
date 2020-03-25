<?php




defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/question/engine/lib.php');
require_once($CFG->dirroot . '/question/type/questiontypebase.php');





define("QUESTION_NUMANS", 10);


define("QUESTION_NUMANS_START", 3);


define("QUESTION_NUMANS_ADD", 3);


function question_reorder_qtypes($sortedqtypes, $tomove, $direction) {
    $neworder = array_keys($sortedqtypes);
        $key = array_search($tomove, $neworder);
    if ($key === false) {
        return $neworder;
    }
        $otherkey = $key + $direction;
    if (!isset($neworder[$otherkey])) {
        return $neworder;
    }
        $swap = $neworder[$otherkey];
    $neworder[$otherkey] = $neworder[$key];
    $neworder[$key] = $swap;
    return $neworder;
}


function question_save_qtype_order($neworder, $config = null) {
    global $DB;

    if (is_null($config)) {
        $config = get_config('question');
    }

    foreach ($neworder as $index => $qtype) {
        $sortvar = $qtype . '_sortorder';
        if (!isset($config->$sortvar) || $config->$sortvar != $index + 1) {
            set_config($sortvar, $index + 1, 'question');
        }
    }
}



function questions_in_use($questionids) {
    global $CFG;

    if (question_engine::questions_in_use($questionids)) {
        return true;
    }

    foreach (core_component::get_plugin_list('mod') as $module => $path) {
        $lib = $path . '/lib.php';
        if (is_readable($lib)) {
            include_once($lib);

            $fn = $module . '_questions_in_use';
            if (function_exists($fn)) {
                if ($fn($questionids)) {
                    return true;
                }
            } else {

                                $fn = $module . '_question_list_instances';
                if (function_exists($fn)) {
                    foreach ($questionids as $questionid) {
                        $instances = $fn($questionid);
                        if (!empty($instances)) {
                            return true;
                        }
                    }
                }
            }
        }
    }

    return false;
}


function question_context_has_any_questions($context) {
    global $DB;
    if (is_object($context)) {
        $contextid = $context->id;
    } else if (is_numeric($context)) {
        $contextid = $context;
    } else {
        print_error('invalidcontextinhasanyquestions', 'question');
    }
    return $DB->record_exists_sql("SELECT *
                                     FROM {question} q
                                     JOIN {question_categories} qc ON qc.id = q.category
                                    WHERE qc.contextid = ? AND q.parent = 0", array($contextid));
}


function match_grade_options($gradeoptionsfull, $grade, $matchgrades = 'error') {

    if ($matchgrades == 'error') {
                foreach ($gradeoptionsfull as $value => $option) {
                        if (abs($grade - $value) < 0.00001) {
                return $value;             }
        }
                return false;

    } else if ($matchgrades == 'nearest') {
                $best = false;
        $bestmismatch = 2;
        foreach ($gradeoptionsfull as $value => $option) {
            $newmismatch = abs($grade - $value);
            if ($newmismatch < $bestmismatch) {
                $best = $value;
                $bestmismatch = $newmismatch;
            }
        }
        return $best;

    } else {
                throw new coding_exception('Unknown $matchgrades ' . $matchgrades .
                ' passed to match_grade_options');
    }
}


function question_remove_stale_questions_from_category($categoryid) {
    global $DB;

    $select = 'category = :categoryid AND (qtype = :qtype OR hidden = :hidden)';
    $params = ['categoryid' => $categoryid, 'qtype' => 'random', 'hidden' => 1];
    $questions = $DB->get_recordset_select("question", $select, $params, '', 'id');
    foreach ($questions as $question) {
                question_delete_question($question->id);
    }
    $questions->close();
}


function question_category_delete_safe($category) {
    global $DB;
    $criteria = array('category' => $category->id);
    $context = context::instance_by_id($category->contextid, IGNORE_MISSING);
    $rescue = null; 
        if ($questions = $DB->get_records('question', $criteria, '', 'id,qtype')) {

                foreach ($questions as $question) {
            question_delete_question($question->id);
        }

                                                $questionids = $DB->get_records_menu('question', $criteria, '', 'id, 1');
        if (!empty($questionids)) {
            $parentcontextid = SYSCONTEXTID;
            $name = get_string('unknown', 'question');
            if ($context !== false) {
                $name = $context->get_context_name();
                $parentcontext = $context->get_parent_context();
                if ($parentcontext) {
                    $parentcontextid = $parentcontext->id;
                }
            }
            question_save_from_deletion(array_keys($questionids), $parentcontextid, $name, $rescue);
        }
    }

        $DB->delete_records('question_categories', array('id' => $category->id));
}


function question_category_in_use($categoryid, $recursive = false) {
    global $DB;

        if ($questions = $DB->get_records_menu('question',
            array('category' => $categoryid), '', 'id, 1')) {
        if (questions_in_use(array_keys($questions))) {
            return true;
        }
    }
    if (!$recursive) {
        return false;
    }

        if ($children = $DB->get_records('question_categories',
            array('parent' => $categoryid), '', 'id, 1')) {
        foreach ($children as $child) {
            if (question_category_in_use($child->id, $recursive)) {
                return true;
            }
        }
    }

    return false;
}


function question_delete_question($questionid) {
    global $DB;

    $question = $DB->get_record_sql('
            SELECT q.*, qc.contextid
            FROM {question} q
            JOIN {question_categories} qc ON qc.id = q.category
            WHERE q.id = ?', array($questionid));
    if (!$question) {
                                return;
    }

        if (questions_in_use(array($questionid))) {
        return;
    }

    $dm = new question_engine_data_mapper();
    $dm->delete_previews($questionid);

        question_bank::get_qtype($question->qtype, false)->delete_question(
            $questionid, $question->contextid);

        core_tag_tag::remove_all_item_tags('core_question', 'question', $question->id);

        if ($children = $DB->get_records('question',
            array('parent' => $questionid), '', 'id, qtype')) {
        foreach ($children as $child) {
            if ($child->id != $questionid) {
                question_delete_question($child->id);
            }
        }
    }

        $DB->delete_records('question', array('id' => $questionid));
    question_bank::notify_question_edited($questionid);
}


function question_delete_context($contextid) {
    global $DB;

        $feedbackdata   = array();

        $strcatdeleted = get_string('unusedcategorydeleted', 'question');
    $fields = 'id, parent, name, contextid';
    if ($categories = $DB->get_records('question_categories', array('contextid' => $contextid), 'parent', $fields)) {
                        $categories = sort_categories_by_tree($categories);

        foreach ($categories as $category) {
            question_category_delete_safe($category);

                        $feedbackdata[] = array($category->name, $strcatdeleted);
        }
    }
    return $feedbackdata;
}


function question_delete_course($course, $feedback=true) {
    $coursecontext = context_course::instance($course->id);
    $feedbackdata = question_delete_context($coursecontext->id, $feedback);

        if ($feedback && $feedbackdata) {
        $table = new html_table();
        $table->head = array(get_string('category', 'question'), get_string('action'));
        $table->data = $feedbackdata;
        echo html_writer::table($table);
    }
    return true;
}


function question_delete_course_category($category, $newcategory, $feedback=true) {
    global $DB, $OUTPUT;

    $context = context_coursecat::instance($category->id);
    if (empty($newcategory)) {
        $feedbackdata = question_delete_context($context->id, $feedback);

                if ($feedback && $feedbackdata) {
            $table = new html_table();
            $table->head = array(get_string('questioncategory', 'question'), get_string('action'));
            $table->data = $feedbackdata;
            echo html_writer::table($table);
        }

    } else {
                if (!$newcontext = context_coursecat::instance($newcategory->id)) {
            return false;
        }

                core_tag_tag::move_context('core_question', 'question', $context, $newcontext);

        $DB->set_field('question_categories', 'contextid', $newcontext->id, array('contextid' => $context->id));

        if ($feedback) {
            $a = new stdClass();
            $a->oldplace = $context->get_context_name();
            $a->newplace = $newcontext->get_context_name();
            echo $OUTPUT->notification(
                    get_string('movedquestionsandcategories', 'question', $a), 'notifysuccess');
        }
    }

    return true;
}


function question_save_from_deletion($questionids, $newcontextid, $oldplace,
        $newcategory = null) {
    global $DB;

        if (is_null($newcategory)) {
        $newcategory = new stdClass();
        $newcategory->parent = 0;
        $newcategory->contextid = $newcontextid;
        $newcategory->name = get_string('questionsrescuedfrom', 'question', $oldplace);
        $newcategory->info = get_string('questionsrescuedfrominfo', 'question', $oldplace);
        $newcategory->sortorder = 999;
        $newcategory->stamp = make_unique_id_code();
        $newcategory->id = $DB->insert_record('question_categories', $newcategory);
    }

        if (!question_move_questions_to_category($questionids, $newcategory->id)) {
        return false;
    }
    return $newcategory;
}


function question_delete_activity($cm, $feedback=true) {
    global $DB;

    $modcontext = context_module::instance($cm->id);
    $feedbackdata = question_delete_context($modcontext->id, $feedback);
        if ($feedback && $feedbackdata) {
        $table = new html_table();
        $table->head = array(get_string('category', 'question'), get_string('action'));
        $table->data = $feedbackdata;
        echo html_writer::table($table);
    }
    return true;
}


function question_move_questions_to_category($questionids, $newcategoryid) {
    global $DB;

    $newcontextid = $DB->get_field('question_categories', 'contextid',
            array('id' => $newcategoryid));
    list($questionidcondition, $params) = $DB->get_in_or_equal($questionids);
    $questions = $DB->get_records_sql("
            SELECT q.id, q.qtype, qc.contextid
              FROM {question} q
              JOIN {question_categories} qc ON q.category = qc.id
             WHERE  q.id $questionidcondition", $params);
    foreach ($questions as $question) {
        if ($newcontextid != $question->contextid) {
            question_bank::get_qtype($question->qtype)->move_files(
                    $question->id, $question->contextid, $newcontextid);
        }
    }

        $DB->set_field_select('question', 'category', $newcategoryid,
            "id $questionidcondition", $params);

        $DB->set_field_select('question', 'category', $newcategoryid,
            "parent $questionidcondition", $params);

        core_tag_tag::change_items_context('core_question', 'question', $questionids, $newcontextid);

    
        foreach ($questions as $question) {
        question_bank::notify_question_edited($question->id);
    }

    return true;
}


function question_move_category_to_context($categoryid, $oldcontextid, $newcontextid) {
    global $DB;

    $questionids = $DB->get_records_menu('question',
            array('category' => $categoryid), '', 'id,qtype');
    foreach ($questionids as $questionid => $qtype) {
        question_bank::get_qtype($qtype)->move_files(
                $questionid, $oldcontextid, $newcontextid);
                question_bank::notify_question_edited($questionid);
    }

    core_tag_tag::change_items_context('core_question', 'question',
            array_keys($questionids), $newcontextid);

    $subcatids = $DB->get_records_menu('question_categories',
            array('parent' => $categoryid), '', 'id,1');
    foreach ($subcatids as $subcatid => $notused) {
        $DB->set_field('question_categories', 'contextid', $newcontextid,
                array('id' => $subcatid));
        question_move_category_to_context($subcatid, $oldcontextid, $newcontextid);
    }
}


function question_preview_url($questionid, $preferredbehaviour = null,
        $maxmark = null, $displayoptions = null, $variant = null, $context = null) {

    $params = array('id' => $questionid);

    if (is_null($context)) {
        global $PAGE;
        $context = $PAGE->context;
    }
    if ($context->contextlevel == CONTEXT_MODULE) {
        $params['cmid'] = $context->instanceid;
    } else if ($context->contextlevel == CONTEXT_COURSE) {
        $params['courseid'] = $context->instanceid;
    }

    if (!is_null($preferredbehaviour)) {
        $params['behaviour'] = $preferredbehaviour;
    }

    if (!is_null($maxmark)) {
        $params['maxmark'] = $maxmark;
    }

    if (!is_null($displayoptions)) {
        $params['correctness']     = $displayoptions->correctness;
        $params['marks']           = $displayoptions->marks;
        $params['markdp']          = $displayoptions->markdp;
        $params['feedback']        = (bool) $displayoptions->feedback;
        $params['generalfeedback'] = (bool) $displayoptions->generalfeedback;
        $params['rightanswer']     = (bool) $displayoptions->rightanswer;
        $params['history']         = (bool) $displayoptions->history;
    }

    if ($variant) {
        $params['variant'] = $variant;
    }

    return new moodle_url('/question/preview.php', $params);
}


function question_preview_popup_params() {
    return array(
        'height' => 600,
        'width' => 800,
    );
}


function question_preload_questions($questionids = null, $extrafields = '', $join = '',
        $extraparams = array(), $orderby = '') {
    global $DB;

    if ($questionids === null) {
        $where = '';
        $params = array();
    } else {
        if (empty($questionids)) {
            return array();
        }

        list($questionidcondition, $params) = $DB->get_in_or_equal(
                $questionids, SQL_PARAMS_NAMED, 'qid0000');
        $where = 'WHERE q.id ' . $questionidcondition;
    }

    if ($join) {
        $join = 'JOIN ' . $join;
    }

    if ($extrafields) {
        $extrafields = ', ' . $extrafields;
    }

    if ($orderby) {
        $orderby = 'ORDER BY ' . $orderby;
    }

    $sql = "SELECT q.*, qc.contextid{$extrafields}
              FROM {question} q
              JOIN {question_categories} qc ON q.category = qc.id
              {$join}
             {$where}
          {$orderby}";

        $questions = $DB->get_records_sql($sql, $extraparams + $params);
    foreach ($questions as $question) {
        $question->_partiallyloaded = true;
    }

    return $questions;
}


function question_load_questions($questionids, $extrafields = '', $join = '') {
    $questions = question_preload_questions($questionids, $extrafields, $join);

        if (!get_question_options($questions)) {
        return 'Could not load the question options';
    }

    return $questions;
}


function _tidy_question($question, $loadtags = false) {
    global $CFG;

        if (!question_bank::is_qtype_installed($question->qtype)) {
        $question->questiontext = html_writer::tag('p', get_string('warningmissingtype',
                'qtype_missingtype')) . $question->questiontext;
    }
    question_bank::get_qtype($question->qtype)->get_question_options($question);

        $question->defaultmark += 0;
    $question->penalty += 0;

    if (isset($question->_partiallyloaded)) {
        unset($question->_partiallyloaded);
    }

    if ($loadtags && core_tag_tag::is_enabled('core_question', 'question')) {
        $question->tags = core_tag_tag::get_item_tags_array('core_question', 'question', $question->id);
    }
}


function get_question_options(&$questions, $loadtags = false) {
    if (is_array($questions)) {         foreach ($questions as $i => $notused) {
            _tidy_question($questions[$i], $loadtags);
        }
    } else {         _tidy_question($questions, $loadtags);
    }
    return true;
}


function print_question_icon($question) {
    global $PAGE;
    return $PAGE->get_renderer('question', 'bank')->qtype_icon($question->qtype);
}


function question_hash($question) {
    return make_unique_id_code();
}



function sort_categories_by_tree(&$categories, $id = 0, $level = 1) {
    global $DB;

    $children = array();
    $keys = array_keys($categories);

    foreach ($keys as $key) {
        if (!isset($categories[$key]->processed) && $categories[$key]->parent == $id) {
            $children[$key] = $categories[$key];
            $categories[$key]->processed = true;
            $children = $children + sort_categories_by_tree(
                    $categories, $children[$key]->id, $level+1);
        }
    }
            if ($level == 1) {
        foreach ($keys as $key) {
                                    if (!isset($categories[$key]->processed) && !$DB->record_exists('question_categories',
                    array('contextid' => $categories[$key]->contextid,
                            'id' => $categories[$key]->parent))) {
                $children[$key] = $categories[$key];
                $categories[$key]->processed = true;
                $children = $children + sort_categories_by_tree(
                        $categories, $children[$key]->id, $level + 1);
            }
        }
    }
    return $children;
}


function flatten_category_tree(&$categories, $id, $depth = 0, $nochildrenof = -1) {

        $newcategories = array();
    $newcategories[$id] = $categories[$id];
    $newcategories[$id]->indentedname = str_repeat('&nbsp;&nbsp;&nbsp;', $depth) .
            $categories[$id]->name;

        foreach ($categories[$id]->childids as $childid) {
        if ($childid != $nochildrenof) {
            $newcategories = $newcategories + flatten_category_tree(
                    $categories, $childid, $depth + 1, $nochildrenof);
        }
    }

        unset($newcategories[$id]->childids);

    return $newcategories;
}


function add_indented_names($categories, $nochildrenof = -1) {

                foreach (array_keys($categories) as $id) {
        $categories[$id]->childids = array();
    }

                $toplevelcategoryids = array();
    foreach (array_keys($categories) as $id) {
        if (!empty($categories[$id]->parent) &&
                array_key_exists($categories[$id]->parent, $categories)) {
            $categories[$categories[$id]->parent]->childids[] = $id;
        } else {
            $toplevelcategoryids[] = $id;
        }
    }

        $newcategories = array();
    foreach ($toplevelcategoryids as $id) {
        $newcategories = $newcategories + flatten_category_tree(
                $categories, $id, 0, $nochildrenof);
    }

    return $newcategories;
}


function question_category_select_menu($contexts, $top = false, $currentcat = 0,
        $selected = "", $nochildrenof = -1) {
    global $OUTPUT;
    $categoriesarray = question_category_options($contexts, $top, $currentcat,
            false, $nochildrenof);
    if ($selected) {
        $choose = '';
    } else {
        $choose = 'choosedots';
    }
    $options = array();
    foreach ($categoriesarray as $group => $opts) {
        $options[] = array($group => $opts);
    }
    echo html_writer::label(get_string('questioncategory', 'core_question'), 'id_movetocategory', false, array('class' => 'accesshide'));
    echo html_writer::select($options, 'category', $selected, $choose, array('id' => 'id_movetocategory'));
}


function question_get_default_category($contextid) {
    global $DB;
    $category = $DB->get_records('question_categories',
            array('contextid' => $contextid), 'id', '*', 0, 1);
    if (!empty($category)) {
        return reset($category);
    } else {
        return false;
    }
}


function question_make_default_categories($contexts) {
    global $DB;
    static $preferredlevels = array(
        CONTEXT_COURSE => 4,
        CONTEXT_MODULE => 3,
        CONTEXT_COURSECAT => 2,
        CONTEXT_SYSTEM => 1,
    );

    $toreturn = null;
    $preferredness = 0;
        foreach ($contexts as $key => $context) {
        if (!$exists = $DB->record_exists("question_categories",
                array('contextid' => $context->id))) {
                        $category = new stdClass();
            $contextname = $context->get_context_name(false, true);
            $category->name = get_string('defaultfor', 'question', $contextname);
            $category->info = get_string('defaultinfofor', 'question', $contextname);
            $category->contextid = $context->id;
            $category->parent = 0;
                        $category->sortorder = 999;
            $category->stamp = make_unique_id_code();
            $category->id = $DB->insert_record('question_categories', $category);
        } else {
            $category = question_get_default_category($context->id);
        }
        $thispreferredness = $preferredlevels[$context->contextlevel];
        if (has_any_capability(array('moodle/question:usemine', 'moodle/question:useall'), $context)) {
            $thispreferredness += 10;
        }
        if ($thispreferredness > $preferredness) {
            $toreturn = $category;
            $preferredness = $thispreferredness;
        }
    }

    if (!is_null($toreturn)) {
        $toreturn = clone($toreturn);
    }
    return $toreturn;
}


function get_categories_for_contexts($contexts, $sortorder = 'parent, sortorder, name ASC') {
    global $DB;
    return $DB->get_records_sql("
            SELECT c.*, (SELECT count(1) FROM {question} q
                        WHERE c.id = q.category AND q.hidden='0' AND q.parent='0') AS questioncount
              FROM {question_categories} c
             WHERE c.contextid IN ($contexts)
          ORDER BY $sortorder");
}


function question_category_options($contexts, $top = false, $currentcat = 0,
        $popupform = false, $nochildrenof = -1) {
    global $CFG;
    $pcontexts = array();
    foreach ($contexts as $context) {
        $pcontexts[] = $context->id;
    }
    $contextslist = join($pcontexts, ', ');

    $categories = get_categories_for_contexts($contextslist);

    $categories = question_add_context_in_key($categories);

    if ($top) {
        $categories = question_add_tops($categories, $pcontexts);
    }
    $categories = add_indented_names($categories, $nochildrenof);

        $categoriesarray = array();
    foreach ($pcontexts as $contextid) {
        $context = context::instance_by_id($contextid);
        $contextstring = $context->get_context_name(true, true);
        foreach ($categories as $category) {
            if ($category->contextid == $contextid) {
                $cid = $category->id;
                if ($currentcat != $cid || $currentcat == 0) {
                    $countstring = !empty($category->questioncount) ?
                            " ($category->questioncount)" : '';
                    $categoriesarray[$contextstring][$cid] =
                            format_string($category->indentedname, true,
                                array('context' => $context)) . $countstring;
                }
            }
        }
    }
    if ($popupform) {
        $popupcats = array();
        foreach ($categoriesarray as $contextstring => $optgroup) {
            $group = array();
            foreach ($optgroup as $key => $value) {
                $key = str_replace($CFG->wwwroot, '', $key);
                $group[$key] = $value;
            }
            $popupcats[] = array($contextstring => $group);
        }
        return $popupcats;
    } else {
        return $categoriesarray;
    }
}

function question_add_context_in_key($categories) {
    $newcatarray = array();
    foreach ($categories as $id => $category) {
        $category->parent = "$category->parent,$category->contextid";
        $category->id = "$category->id,$category->contextid";
        $newcatarray["$id,$category->contextid"] = $category;
    }
    return $newcatarray;
}

function question_add_tops($categories, $pcontexts) {
    $topcats = array();
    foreach ($pcontexts as $context) {
        $newcat = new stdClass();
        $newcat->id = "0,$context";
        $newcat->name = get_string('top');
        $newcat->parent = -1;
        $newcat->contextid = $context;
        $topcats["0,$context"] = $newcat;
    }
        return array_merge($topcats, $categories);
}


function question_categorylist($categoryid) {
    global $DB;

        $categorylist = array();

        $subcategories = array($categoryid);

    while ($subcategories) {
        foreach ($subcategories as $subcategory) {
                        if (isset($categorylist[$subcategory])) {
                throw new coding_exception("Category id=$subcategory is already on the list - loop of categories detected.");
            }
            $categorylist[$subcategory] = $subcategory;
        }

        list ($in, $params) = $DB->get_in_or_equal($subcategories);

        $subcategories = $DB->get_records_select_menu('question_categories',
                "parent $in", $params, NULL, 'id,id AS id2');
    }

    return $categorylist;
}



function get_import_export_formats($type) {
    global $CFG;
    require_once($CFG->dirroot . '/question/format.php');

    $formatclasses = core_component::get_plugin_list_with_class('qformat', '', 'format.php');

    $fileformatname = array();
    foreach ($formatclasses as $component => $formatclass) {

        $format = new $formatclass();
        if ($type == 'import') {
            $provided = $format->provide_import();
        } else {
            $provided = $format->provide_export();
        }

        if ($provided) {
            list($notused, $fileformat) = explode('_', $component, 2);
            $fileformatnames[$fileformat] = get_string('pluginname', $component);
        }
    }

    core_collator::asort($fileformatnames);
    return $fileformatnames;
}



function question_default_export_filename($course, $category) {
        
    $base = clean_filename(get_string('exportfilename', 'question'));

    $dateformat = str_replace(' ', '_', get_string('exportnameformat', 'question'));
    $timestamp = clean_filename(userdate(time(), $dateformat, 99, false));

    $shortname = clean_filename($course->shortname);
    if ($shortname == '' || $shortname == '_' ) {
        $shortname = $course->id;
    }

    $categoryname = clean_filename(format_string($category->name));

    return "{$base}-{$shortname}-{$categoryname}-{$timestamp}";

    return $export_name;
}


class context_to_string_translator{
    
    protected $contexttostringarray = array();

    public function __construct($contexts) {
        $this->generate_context_to_string_array($contexts);
    }

    public function context_to_string($contextid) {
        return $this->contexttostringarray[$contextid];
    }

    public function string_to_context($contextname) {
        $contextid = array_search($contextname, $this->contexttostringarray);
        return $contextid;
    }

    protected function generate_context_to_string_array($contexts) {
        if (!$this->contexttostringarray) {
            $catno = 1;
            foreach ($contexts as $context) {
                switch ($context->contextlevel) {
                    case CONTEXT_MODULE :
                        $contextstring = 'module';
                        break;
                    case CONTEXT_COURSE :
                        $contextstring = 'course';
                        break;
                    case CONTEXT_COURSECAT :
                        $contextstring = "cat$catno";
                        $catno++;
                        break;
                    case CONTEXT_SYSTEM :
                        $contextstring = 'system';
                        break;
                }
                $this->contexttostringarray[$context->id] = $contextstring;
            }
        }
    }

}


function question_has_capability_on($question, $cap, $cachecat = -1) {
    global $USER, $DB;

            $question_questioncaps = array('edit', 'view', 'use', 'move');
    static $questions = array();
    static $categories = array();
    static $cachedcat = array();
    if ($cachecat != -1 && array_search($cachecat, $cachedcat) === false) {
        $questions += $DB->get_records('question', array('category' => $cachecat), '', 'id,category,createdby');
        $cachedcat[] = $cachecat;
    }
    if (!is_object($question)) {
        if (!isset($questions[$question])) {
            if (!$questions[$question] = $DB->get_record('question',
                    array('id' => $question), 'id,category,createdby')) {
                print_error('questiondoesnotexist', 'question');
            }
        }
        $question = $questions[$question];
    }
    if (empty($question->category)) {
                        return false;
    }
    if (!isset($categories[$question->category])) {
        if (!$categories[$question->category] = $DB->get_record('question_categories',
                array('id'=>$question->category))) {
            print_error('invalidcategory', 'question');
        }
    }
    $category = $categories[$question->category];
    $context = context::instance_by_id($category->contextid);

    if (array_search($cap, $question_questioncaps)!== false) {
        if (!has_capability('moodle/question:' . $cap . 'all', $context)) {
            if ($question->createdby == $USER->id) {
                return has_capability('moodle/question:' . $cap . 'mine', $context);
            } else {
                return false;
            }
        } else {
            return true;
        }
    } else {
        return has_capability('moodle/question:' . $cap, $context);
    }

}


function question_require_capability_on($question, $cap) {
    if (!question_has_capability_on($question, $cap)) {
        print_error('nopermissions', '', '', $cap);
    }
    return true;
}


function question_edit_url($context) {
    global $CFG, $SITE;
    if (!has_any_capability(question_get_question_capabilities(), $context)) {
        return false;
    }
    $baseurl = $CFG->wwwroot . '/question/edit.php?';
    $defaultcategory = question_get_default_category($context->id);
    if ($defaultcategory) {
        $baseurl .= 'cat=' . $defaultcategory->id . ',' . $context->id . '&amp;';
    }
    switch ($context->contextlevel) {
        case CONTEXT_SYSTEM:
            return $baseurl . 'courseid=' . $SITE->id;
        case CONTEXT_COURSECAT:
                                    return false;
        case CONTEXT_COURSE:
            return $baseurl . 'courseid=' . $context->instanceid;
        case CONTEXT_MODULE:
            return $baseurl . 'cmid=' . $context->instanceid;
    }

}


function question_extend_settings_navigation(navigation_node $navigationnode, $context) {
    global $PAGE;

    if ($context->contextlevel == CONTEXT_COURSE) {
        $params = array('courseid'=>$context->instanceid);
    } else if ($context->contextlevel == CONTEXT_MODULE) {
        $params = array('cmid'=>$context->instanceid);
    } else {
        return;
    }

    if (($cat = $PAGE->url->param('cat')) && preg_match('~\d+,\d+~', $cat)) {
        $params['cat'] = $cat;
    }

    $questionnode = $navigationnode->add(get_string('questionbank', 'question'),
            new moodle_url('/question/edit.php', $params), navigation_node::TYPE_CONTAINER, null, 'questionbank');

    $contexts = new question_edit_contexts($context);
    if ($contexts->have_one_edit_tab_cap('questions')) {
        $questionnode->add(get_string('questions', 'question'), new moodle_url(
                '/question/edit.php', $params), navigation_node::TYPE_SETTING, null, 'questions');
    }
    if ($contexts->have_one_edit_tab_cap('categories')) {
        $questionnode->add(get_string('categories', 'question'), new moodle_url(
                '/question/category.php', $params), navigation_node::TYPE_SETTING, null, 'categories');
    }
    if ($contexts->have_one_edit_tab_cap('import')) {
        $questionnode->add(get_string('import', 'question'), new moodle_url(
                '/question/import.php', $params), navigation_node::TYPE_SETTING, null, 'import');
    }
    if ($contexts->have_one_edit_tab_cap('export')) {
        $questionnode->add(get_string('export', 'question'), new moodle_url(
                '/question/export.php', $params), navigation_node::TYPE_SETTING, null, 'export');
    }

    return $questionnode;
}


function question_get_question_capabilities() {
    return array(
        'moodle/question:add',
        'moodle/question:editmine',
        'moodle/question:editall',
        'moodle/question:viewmine',
        'moodle/question:viewall',
        'moodle/question:usemine',
        'moodle/question:useall',
        'moodle/question:movemine',
        'moodle/question:moveall',
    );
}


function question_get_all_capabilities() {
    $caps = question_get_question_capabilities();
    $caps[] = 'moodle/question:managecategory';
    $caps[] = 'moodle/question:flag';
    return $caps;
}



class question_edit_contexts {

    public static $caps = array(
        'editq' => array('moodle/question:add',
            'moodle/question:editmine',
            'moodle/question:editall',
            'moodle/question:viewmine',
            'moodle/question:viewall',
            'moodle/question:usemine',
            'moodle/question:useall',
            'moodle/question:movemine',
            'moodle/question:moveall'),
        'questions'=>array('moodle/question:add',
            'moodle/question:editmine',
            'moodle/question:editall',
            'moodle/question:viewmine',
            'moodle/question:viewall',
            'moodle/question:movemine',
            'moodle/question:moveall'),
        'categories'=>array('moodle/question:managecategory'),
        'import'=>array('moodle/question:add'),
        'export'=>array('moodle/question:viewall', 'moodle/question:viewmine'));

    protected $allcontexts;

    
    public function __construct(context $thiscontext) {
        $this->allcontexts = array_values($thiscontext->get_parent_contexts(true));
    }

    
    public function all() {
        return $this->allcontexts;
    }

    
    public function lowest() {
        return $this->allcontexts[0];
    }

    
    public function having_cap($cap) {
        $contextswithcap = array();
        foreach ($this->allcontexts as $context) {
            if (has_capability($cap, $context)) {
                $contextswithcap[] = $context;
            }
        }
        return $contextswithcap;
    }

    
    public function having_one_cap($caps) {
        $contextswithacap = array();
        foreach ($this->allcontexts as $context) {
            foreach ($caps as $cap) {
                if (has_capability($cap, $context)) {
                    $contextswithacap[] = $context;
                    break;                 }
            }
        }
        return $contextswithacap;
    }

    
    public function having_one_edit_tab_cap($tabname) {
        return $this->having_one_cap(self::$caps[$tabname]);
    }

    
    public function having_add_and_use() {
        $contextswithcap = array();
        foreach ($this->allcontexts as $context) {
            if (!has_capability('moodle/question:add', $context)) {
                continue;
            }
            if (!has_any_capability(array('moodle/question:useall', 'moodle/question:usemine'), $context)) {
                            continue;
            }
            $contextswithcap[] = $context;
        }
        return $contextswithcap;
    }

    
    public function have_cap($cap) {
        return (count($this->having_cap($cap)));
    }

    
    public function have_one_cap($caps) {
        foreach ($caps as $cap) {
            if ($this->have_cap($cap)) {
                return true;
            }
        }
        return false;
    }

    
    public function have_one_edit_tab_cap($tabname) {
        return $this->have_one_cap(self::$caps[$tabname]);
    }

    
    public function require_cap($cap) {
        if (!$this->have_cap($cap)) {
            print_error('nopermissions', '', '', $cap);
        }
    }

    
    public function require_one_cap($caps) {
        if (!$this->have_one_cap($caps)) {
            $capsstring = join($caps, ', ');
            print_error('nopermissions', '', '', $capsstring);
        }
    }

    
    public function require_one_edit_tab_cap($tabname) {
        if (!$this->have_one_edit_tab_cap($tabname)) {
            print_error('nopermissions', '', '', 'access question edit tab '.$tabname);
        }
    }
}



function question_rewrite_question_urls($text, $file, $contextid, $component,
        $filearea, array $ids, $itemid, array $options=null) {

    $idsstr = '';
    if (!empty($ids)) {
        $idsstr .= implode('/', $ids);
    }
    if ($itemid !== null) {
        $idsstr .= '/' . $itemid;
    }
    return file_rewrite_pluginfile_urls($text, $file, $contextid, $component,
            $filearea, $idsstr, $options);
}


function question_rewrite_question_preview_urls($text, $questionid,
        $filecontextid, $filecomponent, $filearea, $itemid,
        $previewcontextid, $previewcomponent, $options = null) {

    $path = "preview/$previewcontextid/$previewcomponent/$questionid";
    if ($itemid) {
        $path .= '/' . $itemid;
    }

    return file_rewrite_pluginfile_urls($text, 'pluginfile.php', $filecontextid,
            $filecomponent, $filearea, $path, $options);
}


function question_pluginfile($course, $context, $component, $filearea, $args, $forcedownload, array $options=array()) {
    global $DB, $CFG;

        if ($filearea === 'export') {
        list($context, $course, $cm) = get_context_info_array($context->id);
        require_login($course, false, $cm);

        require_once($CFG->dirroot . '/question/editlib.php');
        $contexts = new question_edit_contexts($context);
                $contexts->require_one_edit_tab_cap('export');
        $category_id = (int)array_shift($args);
        $format      = array_shift($args);
        $cattofile   = array_shift($args);
        $contexttofile = array_shift($args);
        $filename    = array_shift($args);

                require_once($CFG->dirroot . '/question/format.php');
        require_once($CFG->dirroot . '/question/editlib.php');
        require_once($CFG->dirroot . '/question/format/' . $format . '/format.php');

        $classname = 'qformat_' . $format;
        if (!class_exists($classname)) {
            send_file_not_found();
        }

        $qformat = new $classname();

        if (!$category = $DB->get_record('question_categories', array('id' => $category_id))) {
            send_file_not_found();
        }

        $qformat->setCategory($category);
        $qformat->setContexts($contexts->having_one_edit_tab_cap('export'));
        $qformat->setCourse($course);

        if ($cattofile == 'withcategories') {
            $qformat->setCattofile(true);
        } else {
            $qformat->setCattofile(false);
        }

        if ($contexttofile == 'withcontexts') {
            $qformat->setContexttofile(true);
        } else {
            $qformat->setContexttofile(false);
        }

        if (!$qformat->exportpreprocess()) {
            send_file_not_found();
            print_error('exporterror', 'question', $thispageurl->out());
        }

                if (!$content = $qformat->exportprocess(true)) {
            send_file_not_found();
        }

        send_file($content, $filename, 0, 0, true, true, $qformat->mime_type());
    }

        $qubaidorpreview = array_shift($args);

        if ($qubaidorpreview === 'preview') {
        $previewcontextid = (int)array_shift($args);
        $previewcomponent = array_shift($args);
        $questionid = (int) array_shift($args);
        $previewcontext = context_helper::instance_by_id($previewcontextid);

        $result = component_callback($previewcomponent, 'question_preview_pluginfile', array(
                $previewcontext, $questionid,
                $context, $component, $filearea, $args,
                $forcedownload, $options), 'callbackmissing');

        if ($result === 'callbackmissing') {
            throw new coding_exception("Component {$previewcomponent} does not define the callback " .
                    "{$previewcomponent}_question_preview_pluginfile callback. " .
                    "Which is required if you are using question_rewrite_question_preview_urls.", DEBUG_DEVELOPER);
        }

        send_file_not_found();
    }

        $qubaid = (int)$qubaidorpreview;
    $slot = (int)array_shift($args);

    $module = $DB->get_field('question_usages', 'component',
            array('id' => $qubaid));
    if (!$module) {
        send_file_not_found();
    }

    if ($module === 'core_question_preview') {
        require_once($CFG->dirroot . '/question/previewlib.php');
        return question_preview_question_pluginfile($course, $context,
                $component, $filearea, $qubaid, $slot, $args, $forcedownload, $options);

    } else {
        $dir = core_component::get_component_directory($module);
        if (!file_exists("$dir/lib.php")) {
            send_file_not_found();
        }
        include_once("$dir/lib.php");

        $filefunction = $module . '_question_pluginfile';
        if (function_exists($filefunction)) {
            $filefunction($course, $context, $component, $filearea, $qubaid, $slot,
                $args, $forcedownload, $options);
        }

                if (strpos($module, 'mod_') === 0) {
            $filefunctionold  = substr($module, 4) . '_question_pluginfile';
            if (function_exists($filefunctionold)) {
                $filefunctionold($course, $context, $component, $filearea, $qubaid, $slot,
                    $args, $forcedownload, $options);
            }
        }

        send_file_not_found();
    }
}


function core_question_question_preview_pluginfile($previewcontext, $questionid,
        $filecontext, $filecomponent, $filearea, $args, $forcedownload, $options = array()) {
    global $DB;

        $question = $DB->get_record_sql('
            SELECT q.*, qc.contextid
              FROM {question} q
              JOIN {question_categories} qc ON qc.id = q.category
             WHERE q.id = :id AND qc.contextid = :contextid',
            array('id' => $questionid, 'contextid' => $filecontext->id), MUST_EXIST);

        list($context, $course, $cm) = get_context_info_array($previewcontext->id);
    require_login($course, false, $cm);

    question_require_capability_on($question, 'use');

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/{$filecontext->id}/{$filecomponent}/{$filearea}/{$relativepath}";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        send_file_not_found();
    }

    send_stored_file($file, 0, 0, $forcedownload, $options);
}


function question_make_export_url($contextid, $categoryid, $format, $withcategories,
        $withcontexts, $filename) {
    global $CFG;
    $urlbase = "$CFG->httpswwwroot/pluginfile.php";
    return moodle_url::make_file_url($urlbase,
            "/$contextid/question/export/{$categoryid}/{$format}/{$withcategories}" .
            "/{$withcontexts}/{$filename}", true);
}


function question_page_type_list($pagetype, $parentcontext, $currentcontext) {
    global $CFG;
    $types = array(
        'question-*'=>get_string('page-question-x', 'question'),
        'question-edit'=>get_string('page-question-edit', 'question'),
        'question-category'=>get_string('page-question-category', 'question'),
        'question-export'=>get_string('page-question-export', 'question'),
        'question-import'=>get_string('page-question-import', 'question')
    );
    if ($currentcontext->contextlevel == CONTEXT_COURSE) {
        require_once($CFG->dirroot . '/course/lib.php');
        return array_merge(course_page_type_list($pagetype, $parentcontext, $currentcontext), $types);
    } else {
        return $types;
    }
}


function question_module_uses_questions($modname) {
    if (plugin_supports('mod', $modname, FEATURE_USES_QUESTIONS)) {
        return true;
    }

    $component = 'mod_'.$modname;
    if (component_callback_exists($component, 'question_pluginfile')) {
        debugging("{$component} uses questions but doesn't declare FEATURE_USES_QUESTIONS", DEBUG_DEVELOPER);
        return true;
    }

    return false;
}
