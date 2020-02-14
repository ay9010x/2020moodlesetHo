<?php



defined('MOODLE_INTERNAL') || die();


class mod_glossary_entry_query_builder {

    
    const ALIAS_ALIAS = 'ga';
    
    const ALIAS_CATEGORIES = 'gc';
    
    const ALIAS_ENTRIES_CATEGORIES = 'gec';
    
    const ALIAS_ENTRIES = 'ge';
    
    const ALIAS_USER = 'u';

    
    const NON_APPROVED_NONE = 'na_none';
    
    const NON_APPROVED_ALL = 'na_all';
    
    const NON_APPROVED_ONLY = 'na_only';
    
    const NON_APPROVED_SELF = 'na_self';

    
    protected $fields = array();
    
    protected $joins = array();
    
    protected $from;
    
    protected $glossary;
    
    protected $limitfrom = 0;
    
    protected $limitnum = 0;
    
    protected $params = array();
    
    protected $order = array();
    
    protected $where = array();

    
    public function __construct($glossary = null) {
        $this->from = sprintf('FROM {glossary_entries} %s', self::ALIAS_ENTRIES);
        if (!empty($glossary)) {
            $this->glossary = $glossary;
            $this->where[] = sprintf('(%s.glossaryid = :gid OR %s.sourceglossaryid = :gid2)',
                self::ALIAS_ENTRIES, self::ALIAS_ENTRIES);
            $this->params['gid'] = $glossary->id;
            $this->params['gid2'] = $glossary->id;
        }
    }

    
    public function add_field($field, $table, $alias = null) {
        $field = self::resolve_field($field, $table);
        if (!empty($alias)) {
            $field .= ' AS ' . $alias;
        }
        $this->fields[] = $field;
    }

    
    public function add_user_fields() {
        $this->fields[] = user_picture::fields('u', null, 'userdataid', 'userdata');
    }

    
    protected function build_query($count = false) {
        $sql = 'SELECT ';

        if ($count) {
            $sql .= 'COUNT(\'x\') ';
        } else {
            $sql .= implode(', ', $this->fields) . ' ';
        }

        $sql .= $this->from . ' ';
        $sql .= implode(' ', $this->joins) . ' ';

        if (!empty($this->where)) {
            $sql .= 'WHERE (' . implode(') AND (', $this->where) . ') ';
        }

        if (!$count && !empty($this->order)) {
            $sql .= 'ORDER BY ' . implode(', ', $this->order);
        }

        return $sql;
    }

    
    public function count_records() {
        global $DB;
        return $DB->count_records_sql($this->build_query(true), $this->params);
    }

    
    protected function filter_by_letter($letter, $finalfield) {
        global $DB;

        $letter = core_text::strtoupper($letter);
        $len = core_text::strlen($letter);
        $sql = $DB->sql_substr(sprintf('upper(%s)', $finalfield), 1, $len);

        $this->where[] = "$sql = :letter";
        $this->params['letter'] = $letter;
    }

    
    protected function filter_by_non_letter($finalfield) {
        global $DB;

        $alphabet = explode(',', get_string('alphabet', 'langconfig'));
        list($nia, $aparams) = $DB->get_in_or_equal($alphabet, SQL_PARAMS_NAMED, 'nonletter', false);

        $sql = $DB->sql_substr(sprintf('upper(%s)', $finalfield), 1, 1);

        $this->where[] = "$sql $nia";
        $this->params = array_merge($this->params, $aparams);
    }

    
    public function filter_by_author_letter($letter, $firstnamefirst = false) {
        $field = self::get_fullname_field($firstnamefirst);
        $this->filter_by_letter($letter, $field);
    }

    
    public function filter_by_author_non_letter($firstnamefirst = false) {
        $field = self::get_fullname_field($firstnamefirst);
        $this->filter_by_non_letter($field);
    }

    
    public function filter_by_concept_letter($letter) {
        $this->filter_by_letter($letter, self::resolve_field('concept', 'entries'));
    }

    
    public function filter_by_concept_non_letter() {
        $this->filter_by_non_letter(self::resolve_field('concept', 'entries'));
    }

    
    public function filter_by_non_approved($constant, $userid = null) {
        global $USER;
        if (!$userid) {
            $userid = $USER->id;
        }

        if ($constant === self::NON_APPROVED_ALL) {
            
        } else if ($constant === self::NON_APPROVED_SELF) {
            $this->where[] = sprintf('%s != 0 OR %s = :toapproveuserid',
                self::resolve_field('approved', 'entries'), self::resolve_field('userid', 'entries'));
            $this->params['toapproveuserid'] = $USER->id;

        } else if ($constant === self::NON_APPROVED_NONE) {
            $this->where[] = sprintf('%s != 0', self::resolve_field('approved', 'entries'));

        } else if ($constant === self::NON_APPROVED_ONLY) {
            $this->where[] = sprintf('%s = 0', self::resolve_field('approved', 'entries'));

        } else {
            throw new coding_exception('Invalid constant');
        }
    }

    
    public function filter_by_term($term) {
        $this->where[] = sprintf("(%s = :filterterma OR %s = :filtertermb)",
            self::resolve_field('concept', 'entries'),
            self::resolve_field('alias', 'alias'));
        $this->params['filterterma'] = $term;
        $this->params['filtertermb'] = $term;
    }

    
    public static function get_fullname_field($firstnamefirst = false) {
        global $DB;
        if ($firstnamefirst) {
            return $DB->sql_fullname(self::resolve_field('firstname', 'user'), self::resolve_field('lastname', 'user'));
        }
        return $DB->sql_fullname(self::resolve_field('lastname', 'user'), self::resolve_field('firstname', 'user'));
    }

    
    public function get_records() {
        global $DB;
        return $DB->get_records_sql($this->build_query(), $this->params, $this->limitfrom, $this->limitnum);
    }

    
    public function get_recordset() {
        global $DB;
        return $DB->get_recordset_sql($this->build_query(), $this->params, $this->limitfrom, $this->limitnum);
    }

    
    public static function get_user_from_record($record) {
        return user_picture::unalias($record, null, 'userdataid', 'userdata');
    }

    
    public function join_alias() {
        $this->joins[] = sprintf('LEFT JOIN {glossary_alias} %s ON %s = %s',
            self::ALIAS_ALIAS, self::resolve_field('id', 'entries'), self::resolve_field('entryid', 'alias'));
    }

    
    public function join_category($categoryid) {

        if ($categoryid === GLOSSARY_SHOW_ALL_CATEGORIES) {
            $this->joins[] = sprintf('JOIN {glossary_entries_categories} %s ON %s = %s',
                self::ALIAS_ENTRIES_CATEGORIES, self::resolve_field('id', 'entries'),
                self::resolve_field('entryid', 'entries_categories'));

            $this->joins[] = sprintf('JOIN {glossary_categories} %s ON %s = %s',
                self::ALIAS_CATEGORIES, self::resolve_field('id', 'categories'),
                self::resolve_field('categoryid', 'entries_categories'));

        } else if ($categoryid === GLOSSARY_SHOW_NOT_CATEGORISED) {
            $this->joins[] = sprintf('LEFT JOIN {glossary_entries_categories} %s ON %s = %s',
                self::ALIAS_ENTRIES_CATEGORIES, self::resolve_field('id', 'entries'),
                self::resolve_field('entryid', 'entries_categories'));

        } else {
            $this->joins[] = sprintf('JOIN {glossary_entries_categories} %s ON %s = %s AND %s = :joincategoryid',
                self::ALIAS_ENTRIES_CATEGORIES, self::resolve_field('id', 'entries'),
                self::resolve_field('entryid', 'entries_categories'),
                self::resolve_field('categoryid', 'entries_categories'));
            $this->params['joincategoryid'] = $categoryid;

        }
    }

    
    public function join_user($strict = false) {
        $join = $strict ? 'JOIN' : 'LEFT JOIN';
        $this->joins[] = sprintf("$join {user} %s ON %s = %s",
            self::ALIAS_USER, self::resolve_field('id', 'user'), self::resolve_field('userid', 'entries'));
    }

    
    public function limit($from, $num) {
        $this->limitfrom = $from;
        $this->limitnum = $num;
    }

    
    protected function normalize_direction($direction) {
        $direction = core_text::strtoupper($direction);
        if ($direction == 'DESC') {
            return 'DESC';
        }
        return 'ASC';
    }

    
    public function order_by($field, $table, $direction = '') {
        $direction = self::normalize_direction($direction);
        $this->order[] = self::resolve_field($field, $table) . ' ' . $direction;
    }

    
    public function order_by_author($firstnamefirst = false, $direction = '') {
        $field = self::get_fullname_field($firstnamefirst);
        $direction = self::normalize_direction($direction);
        $this->order[] = $field . ' ' . $direction;
    }

    
    protected static function resolve_field($field, $table) {
        $prefix = constant(__CLASS__ . '::ALIAS_' . core_text::strtoupper($table));
        return sprintf('%s.%s', $prefix, $field);
    }

    
    public function where($field, $table, $value) {
        static $i = 0;
        $sql = self::resolve_field($field, $table) . ' ';

        if ($value === null) {
            $sql .= 'IS NULL';

        } else {
            $param = 'where' . $i++;
            $sql .= " = :$param";
            $this->params[$param] = $value;
        }

        $this->where[] = $sql;
    }

}
