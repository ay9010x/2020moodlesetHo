<?php



defined('MOODLE_INTERNAL') || die();


define('TEXTFILTER_ON', 1);

define('TEXTFILTER_INHERIT', 0);

define('TEXTFILTER_OFF', -1);

define('TEXTFILTER_DISABLED', -9999);


define('TEXTFILTER_EXCL_SEPARATOR', '-%-');



class filter_manager {
    
    protected $textfilters = array();

    
    protected $stringfilters = array();

    
    protected $stringfilternames = array();

    
    protected static $singletoninstance;

    
    protected function __construct() {
        $this->stringfilternames = filter_get_string_filters();
    }

    
    public static function instance() {
        global $CFG;
        if (is_null(self::$singletoninstance)) {
            if (!empty($CFG->perfdebug) and $CFG->perfdebug > 7) {
                self::$singletoninstance = new performance_measuring_filter_manager();
            } else {
                self::$singletoninstance = new self();
            }
        }
        return self::$singletoninstance;
    }

    
    public static function reset_caches() {
        if (self::$singletoninstance) {
            self::$singletoninstance->unload_all_filters();
        }
        self::$singletoninstance = null;
    }

    
    protected function unload_all_filters() {
        $this->textfilters = array();
        $this->stringfilters = array();
        $this->stringfilternames = array();
    }

    
    protected function load_filters($context) {
        $filters = filter_get_active_in_context($context);
        $this->textfilters[$context->id] = array();
        $this->stringfilters[$context->id] = array();
        foreach ($filters as $filtername => $localconfig) {
            $filter = $this->make_filter_object($filtername, $context, $localconfig);
            if (is_null($filter)) {
                continue;
            }
            $this->textfilters[$context->id][$filtername] = $filter;
            if (in_array($filtername, $this->stringfilternames)) {
                $this->stringfilters[$context->id][$filtername] = $filter;
            }
        }
    }

    
    protected function make_filter_object($filtername, $context, $localconfig) {
        global $CFG;
        $path = $CFG->dirroot .'/filter/'. $filtername .'/filter.php';
        if (!is_readable($path)) {
            return null;
        }
        include_once($path);

        $filterclassname = 'filter_' . $filtername;
        if (class_exists($filterclassname)) {
            return new $filterclassname($context, $localconfig);
        }

        return null;
    }

    
    protected function apply_filter_chain($text, $filterchain, array $options = array(),
            array $skipfilters = null) {
        foreach ($filterchain as $filtername => $filter) {
            if ($skipfilters !== null && in_array($filtername, $skipfilters)) {
                continue;
            }
            $text = $filter->filter($text, $options);
        }
        return $text;
    }

    
    protected function get_text_filters($context) {
        if (!isset($this->textfilters[$context->id])) {
            $this->load_filters($context);
        }
        return $this->textfilters[$context->id];
    }

    
    protected function get_string_filters($context) {
        if (!isset($this->stringfilters[$context->id])) {
            $this->load_filters($context);
        }
        return $this->stringfilters[$context->id];
    }

    
    public function filter_text($text, $context, array $options = array(),
            array $skipfilters = null) {
        $text = $this->apply_filter_chain($text, $this->get_text_filters($context), $options, $skipfilters);
                $text = str_replace(array('<nolink>', '</nolink>'), '', $text);
        return $text;
    }

    
    public function filter_string($string, $context) {
        return $this->apply_filter_chain($string, $this->get_string_filters($context));
    }

    
    public function text_filtering_hash($context) {
        debugging('filter_manager::text_filtering_hash() is deprecated. ' .
                'It was an internal part of the old format_text caching, ' .
                'and should not have been called from other code.', DEBUG_DEVELOPER);
        $filters = $this->get_text_filters($context);
        $hashes = array();
        foreach ($filters as $filter) {
            $hashes[] = $filter->hash();
        }
        return implode('-', $hashes);
    }

    
    public function setup_page_for_filters($page, $context) {
        $filters = $this->get_text_filters($context);
        foreach ($filters as $filter) {
            $filter->setup($page, $context);
        }
    }
}



class null_filter_manager {
    public function filter_text($text, $context, array $options = array(),
            array $skipfilters = null) {
        return $text;
    }

    public function filter_string($string, $context) {
        return $string;
    }

    public function text_filtering_hash() {
        debugging('filter_manager::text_filtering_hash() is deprecated. ' .
                'It was an internal part of the old format_text caching, ' .
                'and should not have been called from other code.', DEBUG_DEVELOPER);
        return '';
    }
}



class performance_measuring_filter_manager extends filter_manager {
    
    protected $filterscreated = 0;

    
    protected $textsfiltered = 0;

    
    protected $stringsfiltered = 0;

    protected function unload_all_filters() {
        parent::unload_all_filters();
        $this->filterscreated = 0;
        $this->textsfiltered = 0;
        $this->stringsfiltered = 0;
    }

    protected function make_filter_object($filtername, $context, $localconfig) {
        $this->filterscreated++;
        return parent::make_filter_object($filtername, $context, $localconfig);
    }

    public function filter_text($text, $context, array $options = array(),
            array $skipfilters = null) {
        $this->textsfiltered++;
        return parent::filter_text($text, $context, $options, $skipfilters);
    }

    public function filter_string($string, $context) {
        $this->stringsfiltered++;
        return parent::filter_string($string, $context);
    }

    
    public function get_performance_summary() {
        return array(array(
            'contextswithfilters' => count($this->textfilters),
            'filterscreated' => $this->filterscreated,
            'textsfiltered' => $this->textsfiltered,
            'stringsfiltered' => $this->stringsfiltered,
        ), array(
            'contextswithfilters' => 'Contexts for which filters were loaded',
            'filterscreated' => 'Filters created',
            'textsfiltered' => 'Pieces of content filtered',
            'stringsfiltered' => 'Strings filtered',
        ));
    }
}



abstract class moodle_text_filter {
    
    protected $context;

    
    protected $localconfig;

    
    public function __construct($context, array $localconfig) {
        $this->context = $context;
        $this->localconfig = $localconfig;
    }

    
    public function hash() {
        debugging('moodle_text_filter::hash() is deprecated. ' .
                'It was an internal part of the old format_text caching, ' .
                'and should not have been called from other code.', DEBUG_DEVELOPER);
        return __CLASS__;
    }

    
    public function setup($page, $context) {
            }

    
    public abstract function filter($text, array $options = array());
}



class filterobject {
    
    var $phrase;
    var $hreftagbegin;
    var $hreftagend;
    
    var $casesensitive;
    var $fullmatch;
    
    var $replacementphrase;
    var $work_phrase;
    var $work_hreftagbegin;
    var $work_hreftagend;
    var $work_casesensitive;
    var $work_fullmatch;
    var $work_replacementphrase;
    
    var $work_calculated;

    
    public function __construct($phrase, $hreftagbegin = '<span class="highlight">',
                                   $hreftagend = '</span>',
                                   $casesensitive = false,
                                   $fullmatch = false,
                                   $replacementphrase = NULL) {

        $this->phrase           = $phrase;
        $this->hreftagbegin     = $hreftagbegin;
        $this->hreftagend       = $hreftagend;
        $this->casesensitive    = $casesensitive;
        $this->fullmatch        = $fullmatch;
        $this->replacementphrase= $replacementphrase;
        $this->work_calculated  = false;

    }
}


function filter_get_name($filter) {
    if (strpos($filter, 'filter/') === 0) {
        debugging("Old '$filter'' parameter used in filter_get_name()");
        $filter = substr($filter, 7);
    } else if (strpos($filter, '/') !== false) {
        throw new coding_exception('Unknown filter type ' . $filter);
    }

    if (get_string_manager()->string_exists('filtername', 'filter_' . $filter)) {
        return get_string('filtername', 'filter_' . $filter);
    } else {
        return $filter;
    }
}


function filter_get_all_installed() {
    global $CFG;

    $filternames = array();
    foreach (core_component::get_plugin_list('filter') as $filter => $fulldir) {
        if (is_readable("$fulldir/filter.php")) {
            $filternames[$filter] = filter_get_name($filter);
        }
    }
    core_collator::asort($filternames);
    return $filternames;
}


function filter_set_global_state($filtername, $state, $move = 0) {
    global $DB;

        if (!in_array($state, array(TEXTFILTER_ON, TEXTFILTER_OFF, TEXTFILTER_DISABLED))) {
        throw new coding_exception("Illegal option '$state' passed to filter_set_global_state. " .
                "Must be one of TEXTFILTER_ON, TEXTFILTER_OFF or TEXTFILTER_DISABLED.");
    }

    if ($move > 0) {
        $move = 1;
    } else if ($move < 0) {
        $move = -1;
    }

    if (strpos($filtername, 'filter/') === 0) {
                $filtername = substr($filtername, 7);
    } else if (strpos($filtername, '/') !== false) {
        throw new coding_exception("Invalid filter name '$filtername' used in filter_set_global_state()");
    }

    $transaction = $DB->start_delegated_transaction();

    $syscontext = context_system::instance();
    $filters = $DB->get_records('filter_active', array('contextid' => $syscontext->id), 'sortorder ASC');

    $on = array();
    $off = array();

    foreach($filters as $f) {
        if ($f->active == TEXTFILTER_DISABLED) {
            $off[$f->filter] = $f;
        } else {
            $on[$f->filter] = $f;
        }
    }

        if (isset($on[$filtername])) {
        $filter = $on[$filtername];
        if ($filter->active != $state) {
            add_to_config_log('filter_active', $filter->active, $state, $filtername);

            $filter->active = $state;
            $DB->update_record('filter_active', $filter);
            if ($filter->active == TEXTFILTER_DISABLED) {
                unset($on[$filtername]);
                $off = array($filter->filter => $filter) + $off;
            }

        }

    } else if (isset($off[$filtername])) {
        $filter = $off[$filtername];
        if ($filter->active != $state) {
            add_to_config_log('filter_active', $filter->active, $state, $filtername);

            $filter->active = $state;
            $DB->update_record('filter_active', $filter);
            if ($filter->active != TEXTFILTER_DISABLED) {
                unset($off[$filtername]);
                $on[$filter->filter] = $filter;
            }
        }

    } else {
        add_to_config_log('filter_active', '', $state, $filtername);

        $filter = new stdClass();
        $filter->filter    = $filtername;
        $filter->contextid = $syscontext->id;
        $filter->active    = $state;
        $filter->sortorder = 99999;
        $filter->id = $DB->insert_record('filter_active', $filter);

        $filters[$filter->id] = $filter;
        if ($state == TEXTFILTER_DISABLED) {
            $off[$filter->filter] = $filter;
        } else {
            $on[$filter->filter] = $filter;
        }
    }

        if ($move != 0 and isset($on[$filter->filter])) {
        $i = 1;
        foreach ($on as $f) {
            $f->newsortorder = $i;
            $i++;
        }

        $filter->newsortorder = $filter->newsortorder + $move;

        foreach ($on as $f) {
            if ($f->id == $filter->id) {
                continue;
            }
            if ($f->newsortorder == $filter->newsortorder) {
                if ($move == 1) {
                    $f->newsortorder = $f->newsortorder - 1;
                } else {
                    $f->newsortorder = $f->newsortorder + 1;
                }
            }
        }

        core_collator::asort_objects_by_property($on, 'newsortorder', core_collator::SORT_NUMERIC);
    }

        core_collator::asort_objects_by_property($off, 'filter', core_collator::SORT_NATURAL);

        $i = 1;
    foreach ($on as $f) {
        if ($f->sortorder != $i) {
            $DB->set_field('filter_active', 'sortorder', $i, array('id'=>$f->id));
        }
        $i++;
    }
    foreach ($off as $f) {
        if ($f->sortorder != $i) {
            $DB->set_field('filter_active', 'sortorder', $i, array('id'=>$f->id));
        }
        $i++;
    }

    $transaction->allow_commit();
}


function filter_is_enabled($filtername) {
    if (strpos($filtername, 'filter/') === 0) {
                $filtername = substr($filtername, 7);
    } else if (strpos($filtername, '/') !== false) {
        throw new coding_exception("Invalid filter name '$filtername' used in filter_is_enabled()");
    }
    return array_key_exists($filtername, filter_get_globally_enabled());
}


function filter_get_globally_enabled() {
    static $enabledfilters = null;
    if (is_null($enabledfilters)) {
        $filters = filter_get_global_states();
        $enabledfilters = array();
        foreach ($filters as $filter => $filerinfo) {
            if ($filerinfo->active != TEXTFILTER_DISABLED) {
                $enabledfilters[$filter] = $filter;
            }
        }
    }
    return $enabledfilters;
}


function filter_get_string_filters() {
    global $CFG;
    $stringfilters = array();
    if (!empty($CFG->filterall) && !empty($CFG->stringfilters)) {
        $stringfilters = explode(',', $CFG->stringfilters);
        $stringfilters = array_combine($stringfilters, $stringfilters);
    }
    return $stringfilters;
}


function filter_set_applies_to_strings($filter, $applytostrings) {
    $stringfilters = filter_get_string_filters();
    $prevfilters = $stringfilters;
    $allfilters = core_component::get_plugin_list('filter');

    if ($applytostrings) {
        $stringfilters[$filter] = $filter;
    } else {
        unset($stringfilters[$filter]);
    }

        foreach ($stringfilters as $filter) {
        if (!isset($allfilters[$filter])) {
            unset($stringfilters[$filter]);
        }
    }

    if ($prevfilters != $stringfilters) {
        set_config('stringfilters', implode(',', $stringfilters));
        set_config('filterall', !empty($stringfilters));
    }
}


function filter_set_local_state($filter, $contextid, $state) {
    global $DB;

        if (!in_array($state, array(TEXTFILTER_ON, TEXTFILTER_OFF, TEXTFILTER_INHERIT))) {
        throw new coding_exception("Illegal option '$state' passed to filter_set_local_state. " .
                "Must be one of TEXTFILTER_ON, TEXTFILTER_OFF or TEXTFILTER_INHERIT.");
    }

    if ($contextid == context_system::instance()->id) {
        throw new coding_exception('You cannot use filter_set_local_state ' .
                'with $contextid equal to the system context id.');
    }

    if ($state == TEXTFILTER_INHERIT) {
        $DB->delete_records('filter_active', array('filter' => $filter, 'contextid' => $contextid));
        return;
    }

    $rec = $DB->get_record('filter_active', array('filter' => $filter, 'contextid' => $contextid));
    $insert = false;
    if (empty($rec)) {
        $insert = true;
        $rec = new stdClass;
        $rec->filter = $filter;
        $rec->contextid = $contextid;
    }

    $rec->active = $state;

    if ($insert) {
        $DB->insert_record('filter_active', $rec);
    } else {
        $DB->update_record('filter_active', $rec);
    }
}


function filter_set_local_config($filter, $contextid, $name, $value) {
    global $DB;
    $rec = $DB->get_record('filter_config', array('filter' => $filter, 'contextid' => $contextid, 'name' => $name));
    $insert = false;
    if (empty($rec)) {
        $insert = true;
        $rec = new stdClass;
        $rec->filter = $filter;
        $rec->contextid = $contextid;
        $rec->name = $name;
    }

    $rec->value = $value;

    if ($insert) {
        $DB->insert_record('filter_config', $rec);
    } else {
        $DB->update_record('filter_config', $rec);
    }
}


function filter_unset_local_config($filter, $contextid, $name) {
    global $DB;
    $DB->delete_records('filter_config', array('filter' => $filter, 'contextid' => $contextid, 'name' => $name));
}


function filter_get_local_config($filter, $contextid) {
    global $DB;
    return $DB->get_records_menu('filter_config', array('filter' => $filter, 'contextid' => $contextid), '', 'name,value');
}


function filter_get_all_local_settings($contextid) {
    global $DB;
    return array(
        $DB->get_records('filter_active', array('contextid' => $contextid), 'filter', 'filter,active'),
        $DB->get_records('filter_config', array('contextid' => $contextid), 'filter,name', 'filter,name,value'),
    );
}


function filter_get_active_in_context($context) {
    global $DB, $FILTERLIB_PRIVATE;

    if (!isset($FILTERLIB_PRIVATE)) {
        $FILTERLIB_PRIVATE = new stdClass();
    }

            if (isset($FILTERLIB_PRIVATE->active) &&
            array_key_exists($context->id, $FILTERLIB_PRIVATE->active)) {
        return $FILTERLIB_PRIVATE->active[$context->id];
    }

    $contextids = str_replace('/', ',', trim($context->path, '/'));

            $sql = "SELECT active.filter, fc.name, fc.value
         FROM (SELECT f.filter, MAX(f.sortorder) AS sortorder
             FROM {filter_active} f
             JOIN {context} ctx ON f.contextid = ctx.id
             WHERE ctx.id IN ($contextids)
             GROUP BY filter
             HAVING MAX(f.active * ctx.depth) > -MIN(f.active * ctx.depth)
         ) active
         LEFT JOIN {filter_config} fc ON fc.filter = active.filter AND fc.contextid = $context->id
         ORDER BY active.sortorder";
    $rs = $DB->get_recordset_sql($sql);

        $filters = array();
    foreach ($rs as $row) {
        if (!isset($filters[$row->filter])) {
            $filters[$row->filter] = array();
        }
        if (!is_null($row->name)) {
            $filters[$row->filter][$row->name] = $row->value;
        }
    }

    $rs->close();

    return $filters;
}


function filter_preload_activities(course_modinfo $modinfo) {
    global $DB, $FILTERLIB_PRIVATE;

    if (!isset($FILTERLIB_PRIVATE)) {
        $FILTERLIB_PRIVATE = new stdClass();
    }

        if (!isset($FILTERLIB_PRIVATE->preloaded)) {
        $FILTERLIB_PRIVATE->preloaded = array();
    }
    if (!empty($FILTERLIB_PRIVATE->preloaded[$modinfo->get_course_id()])) {
        return;
    }
    $FILTERLIB_PRIVATE->preloaded[$modinfo->get_course_id()] = true;

        $cmcontexts = array();
    $cmcontextids = array();
    foreach ($modinfo->get_cms() as $cm) {
        $modulecontext = context_module::instance($cm->id);
        $cmcontextids[] = $modulecontext->id;
        $cmcontexts[] = $modulecontext;
    }

        $coursecontext = context_course::instance($modinfo->get_course_id());
    $parentcontextids = explode('/', substr($coursecontext->path, 1));
    $allcontextids = array_merge($cmcontextids, $parentcontextids);

        list ($sql, $params) = $DB->get_in_or_equal($allcontextids);
    $filteractives = $DB->get_records_select('filter_active', "contextid $sql", $params);

        list ($sql, $params) = $DB->get_in_or_equal($cmcontextids);
    $filterconfigs = $DB->get_records_select('filter_config', "contextid $sql", $params);

                
            $courseactive = array();

        $remainingactives = array();

        $banned = array();

        foreach ($filteractives as $row) {
        $depth = array_search($row->contextid, $parentcontextids);
        if ($depth !== false) {
                        if (!array_key_exists($row->filter, $courseactive)) {
                $courseactive[$row->filter] = 0;
            }
                                                            $courseactive[$row->filter] +=
                ($depth + 1) * ($depth + 1) * $row->active;

            if ($row->active == TEXTFILTER_DISABLED) {
                $banned[$row->filter] = true;
            }
        } else {
                        if (!array_key_exists($row->contextid, $remainingactives)) {
                $remainingactives[$row->contextid] = array();
            }
            $remainingactives[$row->contextid][] = $row;
        }
    }

        foreach ($courseactive as $filter=>$score) {
        if ($score <= 0) {
            unset($courseactive[$filter]);
        } else {
            $courseactive[$filter] = array();
        }
    }

            if (!isset($FILTERLIB_PRIVATE->active)) {
        $FILTERLIB_PRIVATE->active = array();
    }
    foreach ($cmcontextids as $contextid) {
                $FILTERLIB_PRIVATE->active[$contextid] = $courseactive;

                if (array_key_exists($contextid, $remainingactives)) {
            foreach ($remainingactives[$contextid] as $row) {
                if ($row->active > 0 && empty($banned[$row->filter])) {
                                                            $FILTERLIB_PRIVATE->active[$contextid][$row->filter] = array();
                } else {
                                                            unset($FILTERLIB_PRIVATE->active[$contextid][$row->filter]);
                }
            }
        }
    }

        foreach ($filterconfigs as $row) {
        if (isset($FILTERLIB_PRIVATE->active[$row->contextid][$row->filter])) {
            $FILTERLIB_PRIVATE->active[$row->contextid][$row->filter][$row->name] = $row->value;
        }
    }
}


function filter_get_available_in_context($context) {
    global $DB;

            $contextids = explode('/', trim($context->path, '/'));
    array_pop($contextids);
    $contextids = implode(',', $contextids);
    if (empty($contextids)) {
        throw new coding_exception('filter_get_available_in_context cannot be called with the system context.');
    }

        $sql = "SELECT parent_states.filter,
                CASE WHEN fa.active IS NULL THEN " . TEXTFILTER_INHERIT . "
                ELSE fa.active END AS localstate,
             parent_states.inheritedstate
         FROM (SELECT f.filter, MAX(f.sortorder) AS sortorder,
                    CASE WHEN MAX(f.active * ctx.depth) > -MIN(f.active * ctx.depth) THEN " . TEXTFILTER_ON . "
                    ELSE " . TEXTFILTER_OFF . " END AS inheritedstate
             FROM {filter_active} f
             JOIN {context} ctx ON f.contextid = ctx.id
             WHERE ctx.id IN ($contextids)
             GROUP BY f.filter
             HAVING MIN(f.active) > " . TEXTFILTER_DISABLED . "
         ) parent_states
         LEFT JOIN {filter_active} fa ON fa.filter = parent_states.filter AND fa.contextid = $context->id
         ORDER BY parent_states.sortorder";
    return $DB->get_records_sql($sql);
}


function filter_get_global_states() {
    global $DB;
    $context = context_system::instance();
    return $DB->get_records('filter_active', array('contextid' => $context->id), 'sortorder', 'filter,active,sortorder');
}


function filter_delete_all_for_filter($filter) {
    global $DB;

    unset_all_config_for_plugin('filter_' . $filter);
    $DB->delete_records('filter_active', array('filter' => $filter));
    $DB->delete_records('filter_config', array('filter' => $filter));
}


function filter_delete_all_for_context($contextid) {
    global $DB;
    $DB->delete_records('filter_active', array('contextid' => $contextid));
    $DB->delete_records('filter_config', array('contextid' => $contextid));
}


function filter_has_global_settings($filter) {
    global $CFG;
    $settingspath = $CFG->dirroot . '/filter/' . $filter . '/settings.php';
    if (is_readable($settingspath)) {
        return true;
    }
    $settingspath = $CFG->dirroot . '/filter/' . $filter . '/filtersettings.php';
    return is_readable($settingspath);
}


function filter_has_local_settings($filter) {
    global $CFG;
    $settingspath = $CFG->dirroot . '/filter/' . $filter . '/filterlocalsettings.php';
    return is_readable($settingspath);
}


function filter_context_may_have_filter_settings($context) {
    return $context->contextlevel != CONTEXT_BLOCK && $context->contextlevel != CONTEXT_USER;
}


function filter_phrases($text, &$link_array, $ignoretagsopen=NULL, $ignoretagsclose=NULL,
        $overridedefaultignore=false) {

    global $CFG;

    static $usedphrases;

    $ignoretags = array();      $tags = array();        
    if (!$overridedefaultignore) {
                                $filterignoretagsopen  = array('<head>' , '<nolink>' , '<span class="nolink">',
                '<script(\s[^>]*?)?>', '<textarea(\s[^>]*?)?>',
                '<select(\s[^>]*?)?>', '<a(\s[^>]*?)?>');
        $filterignoretagsclose = array('</head>', '</nolink>', '</span>',
                 '</script>', '</textarea>', '</select>','</a>');
    } else {
                $filterignoretagsopen = array();
        $filterignoretagsclose = array();
    }

        if ( is_array($ignoretagsopen) ) {
        foreach ($ignoretagsopen as $open) {
            $filterignoretagsopen[] = $open;
        }
        foreach ($ignoretagsclose as $close) {
            $filterignoretagsclose[] = $close;
        }
    }

                $filterinvalidprefixes = '([^\W_])';
    $filterinvalidsuffixes = '([^\W_])';

        $text = preg_replace('/([#*%])/','\1\1',$text);


        filter_save_ignore_tags($text,$filterignoretagsopen,$filterignoretagsclose,$ignoretags);

        filter_save_tags($text,$tags);

        $size = sizeof($link_array);
    for ($n=0; $n < $size; $n++) {
        $linkobject =& $link_array[$n];

                        if (empty($linkobject->phrase)) {
            continue;
        }

                $intcurrent = intval($linkobject->phrase);
        if (!empty($intcurrent) && strval($intcurrent) == $linkobject->phrase && $intcurrent < 1000) {
            continue;
        }

                 if (!$linkobject->work_calculated) {
            if (!isset($linkobject->hreftagbegin) or !isset($linkobject->hreftagend)) {
                $linkobject->work_hreftagbegin = '<span class="highlight"';
                $linkobject->work_hreftagend   = '</span>';
            } else {
                $linkobject->work_hreftagbegin = $linkobject->hreftagbegin;
                $linkobject->work_hreftagend   = $linkobject->hreftagend;
            }

                                    $linkobject->work_hreftagbegin = preg_replace('/([#*%])/','\1\1',$linkobject->work_hreftagbegin);

            if (empty($linkobject->casesensitive)) {
                $linkobject->work_casesensitive = false;
            } else {
                $linkobject->work_casesensitive = true;
            }
            if (empty($linkobject->fullmatch)) {
                $linkobject->work_fullmatch = false;
            } else {
                $linkobject->work_fullmatch = true;
            }

                        $linkobject->work_phrase = strip_tags($linkobject->phrase);

                                    $linkobject->work_phrase = preg_replace('/([#*%])/','\1\1',$linkobject->work_phrase);

                        if ($linkobject->replacementphrase) {                                    $linkobject->work_replacementphrase = strip_tags($linkobject->replacementphrase);
            } else {                                                 $linkobject->work_replacementphrase = '$1';
            }

                        $linkobject->work_phrase = preg_quote($linkobject->work_phrase, '/');

                        $linkobject->work_calculated = true;

        }

                if (!empty($CFG->filtermatchoneperpage)) {
            if (!empty($usedphrases) && in_array($linkobject->work_phrase,$usedphrases)) {
                continue;
            }
        }

                $modifiers = ($linkobject->work_casesensitive) ? 's' : 'isu'; 
                        if ($linkobject->work_fullmatch) {
            $notfullmatches = array();
            $regexp = '/'.$filterinvalidprefixes.'('.$linkobject->work_phrase.')|('.$linkobject->work_phrase.')'.$filterinvalidsuffixes.'/'.$modifiers;

            preg_match_all($regexp,$text,$list_of_notfullmatches);

            if ($list_of_notfullmatches) {
                foreach (array_unique($list_of_notfullmatches[0]) as $key=>$value) {
                    $notfullmatches['<*'.$key.'*>'] = $value;
                }
                if (!empty($notfullmatches)) {
                    $text = str_replace($notfullmatches,array_keys($notfullmatches),$text);
                }
            }
        }

                if (!empty($CFG->filtermatchonepertext) || !empty($CFG->filtermatchoneperpage)) {
            $resulttext = preg_replace('/('.$linkobject->work_phrase.')/'.$modifiers,
                                      $linkobject->work_hreftagbegin.
                                      $linkobject->work_replacementphrase.
                                      $linkobject->work_hreftagend, $text, 1);
        } else {
            $resulttext = preg_replace('/('.$linkobject->work_phrase.')/'.$modifiers,
                                      $linkobject->work_hreftagbegin.
                                      $linkobject->work_replacementphrase.
                                      $linkobject->work_hreftagend, $text);
        }


                if ($resulttext != $text) {
                        $text = $resulttext;
                        filter_save_ignore_tags($text,$filterignoretagsopen,$filterignoretagsclose,$ignoretags);
                        filter_save_tags($text,$tags);
                        if (!empty($CFG->filtermatchoneperpage)) {
                $usedphrases[] = $linkobject->work_phrase;
            }
        }


                if (!empty($notfullmatches)) {
            $text = str_replace(array_keys($notfullmatches),$notfullmatches,$text);
            unset($notfullmatches);
        }
    }

    
    if (!empty($tags)) {
        $text = str_replace(array_keys($tags), $tags, $text);
    }

    if (!empty($ignoretags)) {
        $ignoretags = array_reverse($ignoretags);             $text = str_replace(array_keys($ignoretags),$ignoretags,$text);
    }

        $text =  preg_replace('/([#*%])(\1)/','\1',$text);

        $text = filter_add_javascript($text);


    return $text;
}


function filter_remove_duplicates($linkarray) {

    $concepts  = array();     $lconcepts = array(); 
    $cleanlinks = array();

    foreach ($linkarray as $key=>$filterobject) {
        if ($filterobject->casesensitive) {
            $exists = in_array($filterobject->phrase, $concepts);
        } else {
            $exists = in_array(core_text::strtolower($filterobject->phrase), $lconcepts);
        }

        if (!$exists) {
            $cleanlinks[] = $filterobject;
            $concepts[] = $filterobject->phrase;
            $lconcepts[] = core_text::strtolower($filterobject->phrase);
        }
    }

    return $cleanlinks;
}


function filter_save_ignore_tags(&$text, $filterignoretagsopen, $filterignoretagsclose, &$ignoretags) {

        foreach ($filterignoretagsopen as $ikey=>$opentag) {
        $closetag = $filterignoretagsclose[$ikey];
                $opentag  = str_replace('/','\/',$opentag);         $closetag = str_replace('/','\/',$closetag);         $pregexp = '/'.$opentag.'(.*?)'.$closetag.'/is';

        preg_match_all($pregexp, $text, $list_of_ignores);
        foreach (array_unique($list_of_ignores[0]) as $key=>$value) {
            $prefix = (string)(count($ignoretags) + 1);
            $ignoretags['<#'.$prefix.TEXTFILTER_EXCL_SEPARATOR.$key.'#>'] = $value;
        }
        if (!empty($ignoretags)) {
            $text = str_replace($ignoretags,array_keys($ignoretags),$text);
        }
    }
}


function filter_save_tags(&$text, &$tags) {

    preg_match_all('/<([^#%*].*?)>/is',$text,$list_of_newtags);
    foreach (array_unique($list_of_newtags[0]) as $ntkey=>$value) {
        $prefix = (string)(count($tags) + 1);
        $tags['<%'.$prefix.TEXTFILTER_EXCL_SEPARATOR.$ntkey.'%>'] = $value;
    }
    if (!empty($tags)) {
        $text = str_replace($tags,array_keys($tags),$text);
    }
}


function filter_add_javascript($text) {
    global $CFG;

    if (stripos($text, '</html>') === FALSE) {
        return $text;     }
    if (strpos($text, 'onclick="return openpopup') === FALSE) {
        return $text;     }
    $js ="
    <script type=\"text/javascript\">
    <!--
        function openpopup(url,name,options,fullscreen) {
          fullurl = \"".$CFG->httpswwwroot."\" + url;
          windowobj = window.open(fullurl,name,options);
          if (fullscreen) {
            windowobj.moveTo(0,0);
            windowobj.resizeTo(screen.availWidth,screen.availHeight);
          }
          windowobj.focus();
          return false;
        }
    // -->
    </script>";
    if (stripos($text, '</head>') !== FALSE) {
                $text = str_ireplace('</head>', $js.'</head>', $text);
        return $text;
    }

        return preg_replace("/<html.*?>/is", "\\0<head>".$js.'</head>', $text);
}
