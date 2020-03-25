<?php





defined('MOODLE_INTERNAL') || die();


define('TABLE_VAR_SORT',   1);
define('TABLE_VAR_HIDE',   2);
define('TABLE_VAR_SHOW',   3);
define('TABLE_VAR_IFIRST', 4);
define('TABLE_VAR_ILAST',  5);
define('TABLE_VAR_PAGE',   6);
define('TABLE_VAR_RESET',  7);



define('TABLE_P_TOP',    1);
define('TABLE_P_BOTTOM', 2);




class flexible_table {

    var $uniqueid        = NULL;
    var $attributes      = array();
    var $headers         = array();

    
    private $helpforheaders = array();
    var $columns         = array();
    var $column_style    = array();
    var $column_class    = array();
    var $column_suppress = array();
    var $column_nosort   = array('userpic');
    private $column_textsort = array();
    
    var $setup           = false;
    var $baseurl         = NULL;
    var $request         = array();

    
    private $persistent = false;
    var $is_collapsible = false;
    var $is_sortable    = false;
    var $use_pages      = false;
    var $use_initials   = false;

    var $maxsortkeys = 2;
    var $pagesize    = 30;
    var $currpage    = 0;
    var $totalrows   = 0;
    var $currentrow  = 0;
    var $sort_default_column = NULL;
    var $sort_default_order  = SORT_ASC;

    
    var $showdownloadbuttonsat= array(TABLE_P_TOP);

    
    public $useridfield = 'id';

    
    var $download  = '';

    
    var $downloadable = false;

    
    var $started_output = false;

    var $exportclass = null;

    
    private $prefs = array();

    
    function __construct($uniqueid) {
        $this->uniqueid = $uniqueid;
        $this->request  = array(
            TABLE_VAR_SORT   => 'tsort',
            TABLE_VAR_HIDE   => 'thide',
            TABLE_VAR_SHOW   => 'tshow',
            TABLE_VAR_IFIRST => 'tifirst',
            TABLE_VAR_ILAST  => 'tilast',
            TABLE_VAR_PAGE   => 'page',
            TABLE_VAR_RESET  => 'treset'
        );
    }

    
    function is_downloading($download = null, $filename='', $sheettitle='') {
        if ($download!==null) {
            $this->sheettitle = $sheettitle;
            $this->is_downloadable(true);
            $this->download = $download;
            $this->filename = clean_filename($filename);
            $this->export_class_instance();
        }
        return $this->download;
    }

    
    function export_class_instance($exportclass = null) {
        if (!is_null($exportclass)) {
            $this->started_output = true;
            $this->exportclass = $exportclass;
            $this->exportclass->table = $this;
        } else if (is_null($this->exportclass) && !empty($this->download)) {
            $this->exportclass = new table_dataformat_export_format($this, $this->download);
            if (!$this->exportclass->document_started()) {
                $this->exportclass->start_document($this->filename);
            }
        }
        return $this->exportclass;
    }

    
    function is_downloadable($downloadable = null) {
        if ($downloadable !== null) {
            $this->downloadable = $downloadable;
        }
        return $this->downloadable;
    }

    
    public function is_persistent($persistent = null) {
        if ($persistent == true) {
            $this->persistent = true;
        }
        return $this->persistent;
    }

    
    function show_download_buttons_at($showat) {
        $this->showdownloadbuttonsat = $showat;
    }

    
    function sortable($bool, $defaultcolumn = NULL, $defaultorder = SORT_ASC) {
        $this->is_sortable = $bool;
        $this->sort_default_column = $defaultcolumn;
        $this->sort_default_order  = $defaultorder;
    }

    
    function text_sorting($column) {
        $this->column_textsort[] = $column;
    }

    
    function no_sorting($column) {
        $this->column_nosort[] = $column;
    }

    
    function is_sortable($column = null) {
        if (empty($column)) {
            return $this->is_sortable;
        }
        if (!$this->is_sortable) {
            return false;
        }
        return !in_array($column, $this->column_nosort);
    }

    
    function collapsible($bool) {
        $this->is_collapsible = $bool;
    }

    
    function pageable($bool) {
        $this->use_pages = $bool;
    }

    
    function initialbars($bool) {
        $this->use_initials = $bool;
    }

    
    function pagesize($perpage, $total) {
        $this->pagesize  = $perpage;
        $this->totalrows = $total;
        $this->use_pages = true;
    }

    
    function set_control_variables($variables) {
        foreach ($variables as $what => $variable) {
            if (isset($this->request[$what])) {
                $this->request[$what] = $variable;
            }
        }
    }

    
    function set_attribute($attribute, $value) {
        $this->attributes[$attribute] = $value;
    }

    
    function column_suppress($column) {
        if (isset($this->column_suppress[$column])) {
            $this->column_suppress[$column] = true;
        }
    }

    
    function column_class($column, $classname) {
        if (isset($this->column_class[$column])) {
            $this->column_class[$column] = ' '.$classname;         }
    }

    
    function column_style($column, $property, $value) {
        if (isset($this->column_style[$column])) {
            $this->column_style[$column][$property] = $value;
        }
    }

    
    function column_style_all($property, $value) {
        foreach (array_keys($this->columns) as $column) {
            $this->column_style[$column][$property] = $value;
        }
    }

    
    function define_baseurl($url) {
        $this->baseurl = new moodle_url($url);
    }

    
    function define_columns($columns) {
        $this->columns = array();
        $this->column_style = array();
        $this->column_class = array();
        $colnum = 0;

        foreach ($columns as $column) {
            $this->columns[$column]         = $colnum++;
            $this->column_style[$column]    = array();
            $this->column_class[$column]    = '';
            $this->column_suppress[$column] = false;
        }
    }

    
    function define_headers($headers) {
        $this->headers = $headers;
    }

    
    public function define_help_for_headers($helpicons) {
        $this->helpforheaders = $helpicons;
    }

    
    function setup() {
        global $SESSION;

        if (empty($this->columns) || empty($this->uniqueid)) {
            return false;
        }

                if ($this->persistent) {
            $this->prefs = json_decode(get_user_preferences('flextable_' . $this->uniqueid), true);
            $oldprefs = $this->prefs;
        } else if (isset($SESSION->flextable[$this->uniqueid])) {
            $this->prefs = $SESSION->flextable[$this->uniqueid];
            $oldprefs = $this->prefs;
        }

                if (!$this->prefs or optional_param($this->request[TABLE_VAR_RESET], false, PARAM_BOOL)) {
            $this->prefs = array(
                'collapse' => array(),
                'sortby'   => array(),
                'i_first'  => '',
                'i_last'   => '',
                'textsort' => $this->column_textsort,
            );
        }

        if (!isset($oldprefs)) {
            $oldprefs = $this->prefs;
        }

        if (($showcol = optional_param($this->request[TABLE_VAR_SHOW], '', PARAM_ALPHANUMEXT)) &&
                isset($this->columns[$showcol])) {
            $this->prefs['collapse'][$showcol] = false;

        } else if (($hidecol = optional_param($this->request[TABLE_VAR_HIDE], '', PARAM_ALPHANUMEXT)) &&
                isset($this->columns[$hidecol])) {
            $this->prefs['collapse'][$hidecol] = true;
            if (array_key_exists($hidecol, $this->prefs['sortby'])) {
                unset($this->prefs['sortby'][$hidecol]);
            }
        }

                foreach (array_keys($this->columns) as $column) {
            if (!empty($this->prefs['collapse'][$column])) {
                $this->column_style[$column]['width'] = '10px';
            }
        }

        if (($sortcol = optional_param($this->request[TABLE_VAR_SORT], '', PARAM_ALPHANUMEXT)) &&
                $this->is_sortable($sortcol) && empty($this->prefs['collapse'][$sortcol]) &&
                (isset($this->columns[$sortcol]) || in_array($sortcol, get_all_user_name_fields())
                && isset($this->columns['fullname']))) {

            if (array_key_exists($sortcol, $this->prefs['sortby'])) {
                                $sortorder = $this->prefs['sortby'][$sortcol] == SORT_ASC ? SORT_DESC : SORT_ASC;
                unset($this->prefs['sortby'][$sortcol]);
                $this->prefs['sortby'] = array_merge(array($sortcol => $sortorder), $this->prefs['sortby']);
            } else {
                                $this->prefs['sortby'] = array_merge(array($sortcol => SORT_ASC), $this->prefs['sortby']);
            }

                        $this->prefs['sortby'] = array_slice($this->prefs['sortby'], 0, $this->maxsortkeys);
        }

                        if (!empty($this->sort_default_column))  {
            if (!array_key_exists($this->sort_default_column, $this->prefs['sortby'])) {
                $defaultsort = array($this->sort_default_column => $this->sort_default_order);
                $this->prefs['sortby'] = array_merge($this->prefs['sortby'], $defaultsort);
            }
        }

        $ilast = optional_param($this->request[TABLE_VAR_ILAST], null, PARAM_RAW);
        if (!is_null($ilast) && ($ilast ==='' || strpos(get_string('alphabet', 'langconfig'), $ilast) !== false)) {
            $this->prefs['i_last'] = $ilast;
        }

        $ifirst = optional_param($this->request[TABLE_VAR_IFIRST], null, PARAM_RAW);
        if (!is_null($ifirst) && ($ifirst === '' || strpos(get_string('alphabet', 'langconfig'), $ifirst) !== false)) {
            $this->prefs['i_first'] = $ifirst;
        }

                if ($this->prefs != $oldprefs) {
            if ($this->persistent) {
                set_user_preference('flextable_' . $this->uniqueid, json_encode($this->prefs));
            } else {
                $SESSION->flextable[$this->uniqueid] = $this->prefs;
            }
        }
        unset($oldprefs);

        if (empty($this->baseurl)) {
            debugging('You should set baseurl when using flexible_table.');
            global $PAGE;
            $this->baseurl = $PAGE->url;
        }

        $this->currpage = optional_param($this->request[TABLE_VAR_PAGE], 0, PARAM_INT);
        $this->setup = true;

                if (empty($this->attributes)) {
            $this->attributes['class'] = 'flexible';
        } else if (!isset($this->attributes['class'])) {
            $this->attributes['class'] = 'flexible';
        } else if (!in_array('flexible', explode(' ', $this->attributes['class']))) {
            $this->attributes['class'] = trim('flexible ' . $this->attributes['class']);
        }
    }

    
    public static function get_sort_for_table($uniqueid) {
        global $SESSION;
        if (isset($SESSION->flextable[$uniqueid])) {
            $prefs = $SESSION->flextable[$uniqueid];
        } else if (!$prefs = json_decode(get_user_preferences('flextable_' . $uniqueid), true)) {
            return '';
        }

        if (empty($prefs['sortby'])) {
            return '';
        }
        if (empty($prefs['textsort'])) {
            $prefs['textsort'] = array();
        }

        return self::construct_order_by($prefs['sortby'], $prefs['textsort']);
    }

    
    public static function construct_order_by($cols, $textsortcols=array()) {
        global $DB;
        $bits = array();

        foreach ($cols as $column => $order) {
            if (in_array($column, $textsortcols)) {
                $column = $DB->sql_order_by_text($column);
            }
            if ($order == SORT_ASC) {
                $bits[] = $column . ' ASC';
            } else {
                $bits[] = $column . ' DESC';
            }
        }

        return implode(', ', $bits);
    }

    
    public function get_sql_sort() {
        return self::construct_order_by($this->get_sort_columns(), $this->column_textsort);
    }

    
    public function get_sort_columns() {
        if (!$this->setup) {
            throw new coding_exception('Cannot call get_sort_columns until you have called setup.');
        }

        if (empty($this->prefs['sortby'])) {
            return array();
        }

        foreach ($this->prefs['sortby'] as $column => $notused) {
            if (isset($this->columns[$column])) {
                continue;             }
            if (in_array($column, get_all_user_name_fields()) &&
                    isset($this->columns['fullname'])) {
                continue;             }
                        unset($this->prefs['sortby'][$column]);
        }

        return $this->prefs['sortby'];
    }

    
    function get_page_start() {
        if (!$this->use_pages) {
            return '';
        }
        return $this->currpage * $this->pagesize;
    }

    
    function get_page_size() {
        if (!$this->use_pages) {
            return '';
        }
        return $this->pagesize;
    }

    
    function get_sql_where() {
        global $DB;

        $conditions = array();
        $params = array();

        if (isset($this->columns['fullname'])) {
            static $i = 0;
            $i++;

            if (!empty($this->prefs['i_first'])) {
                $conditions[] = $DB->sql_like('firstname', ':ifirstc'.$i, false, false);
                $params['ifirstc'.$i] = $this->prefs['i_first'].'%';
            }
            if (!empty($this->prefs['i_last'])) {
                $conditions[] = $DB->sql_like('lastname', ':ilastc'.$i, false, false);
                $params['ilastc'.$i] = $this->prefs['i_last'].'%';
            }
        }

        return array(implode(" AND ", $conditions), $params);
    }

    
    function add_data_keyed($rowwithkeys, $classname = '') {
        $this->add_data($this->get_row_from_keyed($rowwithkeys), $classname);
    }

    
    public function format_and_add_array_of_rows($rowstoadd, $finish = true) {
        foreach ($rowstoadd as $row) {
            if (is_null($row)) {
                $this->add_separator();
            } else {
                $this->add_data_keyed($this->format_row($row));
            }
        }
        if ($finish) {
            $this->finish_output(!$this->is_downloading());
        }
    }

    
    function add_separator() {
        if (!$this->setup) {
            return false;
        }
        $this->add_data(NULL);
    }

    
    function add_data($row, $classname = '') {
        if (!$this->setup) {
            return false;
        }
        if (!$this->started_output) {
            $this->start_output();
        }
        if ($this->exportclass!==null) {
            if ($row === null) {
                $this->exportclass->add_seperator();
            } else {
                $this->exportclass->add_data($row);
            }
        } else {
            $this->print_row($row, $classname);
        }
        return true;
    }

    
    function finish_output($closeexportclassdoc = true) {
        if ($this->exportclass!==null) {
            $this->exportclass->finish_table();
            if ($closeexportclassdoc) {
                $this->exportclass->finish_document();
            }
        } else {
            $this->finish_html();
        }
    }

    
    function wrap_html_start() {
    }

    
    function wrap_html_finish() {
    }

    
    function format_row($row) {
        if (is_array($row)) {
            $row = (object)$row;
        }
        $formattedrow = array();
        foreach (array_keys($this->columns) as $column) {
            $colmethodname = 'col_'.$column;
            if (method_exists($this, $colmethodname)) {
                $formattedcolumn = $this->$colmethodname($row);
            } else {
                $formattedcolumn = $this->other_cols($column, $row);
                if ($formattedcolumn===NULL) {
                    $formattedcolumn = $row->$column;
                }
            }
            $formattedrow[$column] = $formattedcolumn;
        }
        return $formattedrow;
    }

    
    function col_fullname($row) {
        global $COURSE;

        $name = fullname($row);
        if ($this->download) {
            return $name;
        }

        $userid = $row->{$this->useridfield};
        if ($COURSE->id == SITEID) {
            $profileurl = new moodle_url('/user/profile.php', array('id' => $userid));
        } else {
            $profileurl = new moodle_url('/user/view.php',
                    array('id' => $userid, 'course' => $COURSE->id));
        }
        return html_writer::link($profileurl, $name);
    }

    
    function other_cols($column, $row) {
        return NULL;
    }

    
    function format_text($text, $format=FORMAT_MOODLE, $options=NULL, $courseid=NULL) {
        if (!$this->is_downloading()) {
            if (is_null($options)) {
                $options = new stdClass;
            }
                        if (!isset($options->para)) {
                $options->para = false;
            }
            if (!isset($options->newlines)) {
                $options->newlines = false;
            }
            if (!isset($options->smiley)) {
                $options->smiley = false;
            }
            if (!isset($options->filter)) {
                $options->filter = false;
            }
            return format_text($text, $format, $options);
        } else {
            $eci = $this->export_class_instance();
            return $eci->format_text($text, $format, $options, $courseid);
        }
    }
    
    function print_html() {
        if (!$this->setup) {
            return false;
        }
        $this->finish_html();
    }

    
    function get_initial_first() {
        if (!$this->use_initials) {
            return NULL;
        }

        return $this->prefs['i_first'];
    }

    
    function get_initial_last() {
        if (!$this->use_initials) {
            return NULL;
        }

        return $this->prefs['i_last'];
    }

    
    protected function print_one_initials_bar($alpha, $current, $class, $title, $urlvar) {
        echo html_writer::start_tag('div', array('class' => 'initialbar ' . $class)) .
                $title . ' : ';
        if ($current) {
            echo html_writer::link($this->baseurl->out(false, array($urlvar => '')), get_string('all'));
        } else {
            echo html_writer::tag('strong', get_string('all'));
        }

        foreach ($alpha as $letter) {
            if ($letter === $current) {
                echo html_writer::tag('strong', $letter);
            } else {
                echo html_writer::link($this->baseurl->out(false, array($urlvar => $letter)), $letter);
            }
        }

        echo html_writer::end_tag('div');
    }

    
    function print_initials_bar() {
        if ((!empty($this->prefs['i_last']) || !empty($this->prefs['i_first']) ||$this->use_initials)
                    && isset($this->columns['fullname'])) {

            $alpha  = explode(',', get_string('alphabet', 'langconfig'));

                        if (!empty($this->prefs['i_first'])) {
                $ifirst = $this->prefs['i_first'];
            } else {
                $ifirst = '';
            }
            $this->print_one_initials_bar($alpha, $ifirst, 'firstinitial',
                    get_string('firstname'), $this->request[TABLE_VAR_IFIRST]);

                        if (!empty($this->prefs['i_last'])) {
                $ilast = $this->prefs['i_last'];
            } else {
                $ilast = '';
            }
            $this->print_one_initials_bar($alpha, $ilast, 'lastinitial',
                    get_string('lastname'), $this->request[TABLE_VAR_ILAST]);
        }
    }

    
    function print_nothing_to_display() {
        global $OUTPUT;

                echo $this->render_reset_button();

        $this->print_initials_bar();

        echo $OUTPUT->heading(get_string('nothingtodisplay'));
    }

    
    function get_row_from_keyed($rowwithkeys) {
        if (is_object($rowwithkeys)) {
            $rowwithkeys = (array)$rowwithkeys;
        }
        $row = array();
        foreach (array_keys($this->columns) as $column) {
            if (isset($rowwithkeys[$column])) {
                $row [] = $rowwithkeys[$column];
            } else {
                $row[] ='';
            }
        }
        return $row;
    }

    
    public function download_buttons() {
        global $OUTPUT;

        if ($this->is_downloadable() && !$this->is_downloading()) {
            return $OUTPUT->download_dataformat_selector(get_string('downloadas', 'table'),
                    $this->baseurl->out_omit_querystring(), 'download', $this->baseurl->params());
        } else {
            return '';
        }
    }

    
    function start_output() {
        $this->started_output = true;
        if ($this->exportclass!==null) {
            $this->exportclass->start_table($this->sheettitle);
            $this->exportclass->output_headers($this->headers);
        } else {
            $this->start_html();
            $this->print_headers();
            echo html_writer::start_tag('tbody');
        }
    }

    
    function print_row($row, $classname = '') {
        echo $this->get_row_html($row, $classname);
    }

    
    public function get_row_html($row, $classname = '') {
        static $suppress_lastrow = NULL;
        $rowclasses = array();

        if ($classname) {
            $rowclasses[] = $classname;
        }

        $rowid = $this->uniqueid . '_r' . $this->currentrow;
        $html = '';

        $html .= html_writer::start_tag('tr', array('class' => implode(' ', $rowclasses), 'id' => $rowid));

                if ($row === NULL) {
            $colcount = count($this->columns);
            $html .= html_writer::tag('td', html_writer::tag('div', '',
                    array('class' => 'tabledivider')), array('colspan' => $colcount));

        } else {
            $colbyindex = array_flip($this->columns);
            foreach ($row as $index => $data) {
                $column = $colbyindex[$index];

                if (empty($this->prefs['collapse'][$column])) {
                    if ($this->column_suppress[$column] && $suppress_lastrow !== NULL && $suppress_lastrow[$index] === $data) {
                        $content = '&nbsp;';
                    } else {
                        $content = $data;
                    }
                } else {
                    $content = '&nbsp;';
                }

                $html .= html_writer::tag('td', $content, array(
                        'class' => 'cell c' . $index . $this->column_class[$column],
                        'id' => $rowid . '_c' . $index,
                        'style' => $this->make_styles_string($this->column_style[$column])));
            }
        }

        $html .= html_writer::end_tag('tr');

        $suppress_enabled = array_sum($this->column_suppress);
        if ($suppress_enabled) {
            $suppress_lastrow = $row;
        }
        $this->currentrow++;
        return $html;
    }

    
    function finish_html() {
        global $OUTPUT;
        if (!$this->started_output) {
                        $this->print_nothing_to_display();

        } else {
                                                $emptyrow = array_fill(0, count($this->columns), '');
            while ($this->currentrow < $this->pagesize) {
                $this->print_row($emptyrow, 'emptyrow');
            }

            echo html_writer::end_tag('tbody');
            echo html_writer::end_tag('table');
            echo html_writer::end_tag('div');
            $this->wrap_html_finish();

                        if(in_array(TABLE_P_BOTTOM, $this->showdownloadbuttonsat)) {
                echo $this->download_buttons();
            }

            if($this->use_pages) {
                $pagingbar = new paging_bar($this->totalrows, $this->currpage, $this->pagesize, $this->baseurl);
                $pagingbar->pagevar = $this->request[TABLE_VAR_PAGE];
                echo $OUTPUT->render($pagingbar);
            }
        }
    }

    
    protected function show_hide_link($column, $index) {
        global $OUTPUT;
                
        $ariacontrols = '';
        for ($i = 0; $i < $this->pagesize; $i++) {
            $ariacontrols .= $this->uniqueid . '_r' . $i . '_c' . $index . ' ';
        }

        $ariacontrols = trim($ariacontrols);

        if (!empty($this->prefs['collapse'][$column])) {
            $linkattributes = array('title' => get_string('show') . ' ' . strip_tags($this->headers[$index]),
                                    'aria-expanded' => 'false',
                                    'aria-controls' => $ariacontrols);
            return html_writer::link($this->baseurl->out(false, array($this->request[TABLE_VAR_SHOW] => $column)),
                    html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/switch_plus'), 'alt' => get_string('show'))),
                    $linkattributes);

        } else if ($this->headers[$index] !== NULL) {
            $linkattributes = array('title' => get_string('hide') . ' ' . strip_tags($this->headers[$index]),
                                    'aria-expanded' => 'true',
                                    'aria-controls' => $ariacontrols);
            return html_writer::link($this->baseurl->out(false, array($this->request[TABLE_VAR_HIDE] => $column)),
                    html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/switch_minus'), 'alt' => get_string('hide'))),
                    $linkattributes);
        }
    }

    
    function print_headers() {
        global $CFG, $OUTPUT;

        echo html_writer::start_tag('thead');
        echo html_writer::start_tag('tr');
        foreach ($this->columns as $column => $index) {

            $icon_hide = '';
            if ($this->is_collapsible) {
                $icon_hide = $this->show_hide_link($column, $index);
            }

            $primarysortcolumn = '';
            $primarysortorder  = '';
            if (reset($this->prefs['sortby'])) {
                $primarysortcolumn = key($this->prefs['sortby']);
                $primarysortorder  = current($this->prefs['sortby']);
            }

            switch ($column) {

                case 'fullname':
                                $nameformat = $CFG->fullnamedisplay;
                if ($nameformat == 'language') {
                    $nameformat = get_string('fullnamedisplay');
                }
                $requirednames = order_in_string(get_all_user_name_fields(), $nameformat);

                if (!empty($requirednames)) {
                    if ($this->is_sortable($column)) {
                                                $this->headers[$index] = '';
                        foreach ($requirednames as $name) {
                            $sortname = $this->sort_link(get_string($name),
                                    $name, $primarysortcolumn === $name, $primarysortorder);
                            $this->headers[$index] .= $sortname . ' / ';
                        }
                        $helpicon = '';
                        if (isset($this->helpforheaders[$index])) {
                            $helpicon = $OUTPUT->render($this->helpforheaders[$index]);
                        }
                        $this->headers[$index] = substr($this->headers[$index], 0, -3). $helpicon;
                    }
                }
                break;

                case 'userpic':
                                    break;

                default:
                if ($this->is_sortable($column)) {
                    $helpicon = '';
                    if (isset($this->helpforheaders[$index])) {
                        $helpicon = $OUTPUT->render($this->helpforheaders[$index]);
                    }
                    $this->headers[$index] = $this->sort_link($this->headers[$index],
                            $column, $primarysortcolumn == $column, $primarysortorder) . $helpicon;
                }
            }

            $attributes = array(
                'class' => 'header c' . $index . $this->column_class[$column],
                'scope' => 'col',
            );
            if ($this->headers[$index] === NULL) {
                $content = '&nbsp;';
            } else if (!empty($this->prefs['collapse'][$column])) {
                $content = $icon_hide;
            } else {
                if (is_array($this->column_style[$column])) {
                    $attributes['style'] = $this->make_styles_string($this->column_style[$column]);
                }
                $helpicon = '';
                if (isset($this->helpforheaders[$index]) && !$this->is_sortable($column)) {
                    $helpicon  = $OUTPUT->render($this->helpforheaders[$index]);
                }
                $content = $this->headers[$index] . $helpicon . html_writer::tag('div',
                        $icon_hide, array('class' => 'commands'));
            }
            echo html_writer::tag('th', $content, $attributes);
        }

        echo html_writer::end_tag('tr');
        echo html_writer::end_tag('thead');
    }

    
    protected function sort_icon($isprimary, $order) {
        global $OUTPUT;

        if (!$isprimary) {
            return '';
        }

        if ($order == SORT_ASC) {
            return html_writer::empty_tag('img',
                    array('src' => $OUTPUT->pix_url('t/sort_asc'), 'alt' => get_string('asc'), 'class' => 'iconsort'));
        } else {
            return html_writer::empty_tag('img',
                    array('src' => $OUTPUT->pix_url('t/sort_desc'), 'alt' => get_string('desc'), 'class' => 'iconsort'));
        }
    }

    
    protected function sort_order_name($isprimary, $order) {
        if ($isprimary && $order != SORT_ASC) {
            return get_string('desc');
        } else {
            return get_string('asc');
        }
    }

    
    protected function sort_link($text, $column, $isprimary, $order) {
        return html_writer::link($this->baseurl->out(false,
                array($this->request[TABLE_VAR_SORT] => $column)),
                $text . get_accesshide(get_string('sortby') . ' ' .
                $text . ' ' . $this->sort_order_name($isprimary, $order))) . ' ' .
                $this->sort_icon($isprimary, $order);
    }

    
    function start_html() {
        global $OUTPUT;

                echo $this->render_reset_button();

                
                if ($this->use_pages) {
            $pagingbar = new paging_bar($this->totalrows, $this->currpage, $this->pagesize, $this->baseurl);
            $pagingbar->pagevar = $this->request[TABLE_VAR_PAGE];
            echo $OUTPUT->render($pagingbar);
        }

        if (in_array(TABLE_P_TOP, $this->showdownloadbuttonsat)) {
            echo $this->download_buttons();
        }

        $this->wrap_html_start();
        
        echo html_writer::start_tag('div', array('class' => 'no-overflow'));
        echo html_writer::start_tag('table', $this->attributes);

    }

    
    function make_styles_string($styles) {
        if (empty($styles)) {
            return null;
        }

        $string = '';
        foreach($styles as $property => $value) {
            $string .= $property . ':' . $value . ';';
        }
        return $string;
    }

    
    protected function render_reset_button() {

        if (!$this->can_be_reset()) {
            return '';
        }

        $url = $this->baseurl->out(false, array($this->request[TABLE_VAR_RESET] => 1));

        $html  = html_writer::start_div('resettable mdl-right');
        $html .= html_writer::link($url, get_string('resettable'));
        $html .= html_writer::end_div();

        return $html;
    }

    
    protected function can_be_reset() {

                foreach ($this->prefs as $prefname => $prefval) {

            if ($prefname === 'sortby' and !empty($this->sort_default_column)) {
                                if (empty($prefval) or $prefval !== array($this->sort_default_column => $this->sort_default_order)) {
                    return true;
                }

            } else if ($prefname === 'collapse' and !empty($prefval)) {
                                foreach ($prefval as $columnname => $iscollapsed) {
                    if ($iscollapsed) {
                        return true;
                    }
                }

            } else if (!empty($prefval)) {
                                return true;
            }
        }

        return false;
    }
}



class table_sql extends flexible_table {

    public $countsql = NULL;
    public $countparams = NULL;
    
    public $sql = NULL;
    
    public $rawdata = NULL;

    
    public $is_sortable    = true;
    
    public $is_collapsible = true;

    
    function __construct($uniqueid) {
        parent::__construct($uniqueid);
                $this->set_attribute('cellspacing', '0');
        $this->set_attribute('class', 'generaltable generalbox');
    }

    
    function build_table() {

        if ($this->rawdata instanceof \Traversable && !$this->rawdata->valid()) {
            return;
        }
        if (!$this->rawdata) {
            return;
        }

        foreach ($this->rawdata as $row) {
            $formattedrow = $this->format_row($row);
            $this->add_data_keyed($formattedrow,
                $this->get_row_class($row));
        }

        if ($this->rawdata instanceof \core\dml\recordset_walk ||
                $this->rawdata instanceof moodle_recordset) {
            $this->rawdata->close();
        }
    }

    
    function get_row_class($row) {
        return '';
    }

    
    function set_count_sql($sql, array $params = NULL) {
        $this->countsql = $sql;
        $this->countparams = $params;
    }

    
    function set_sql($fields, $from, $where, array $params = NULL) {
        $this->sql = new stdClass();
        $this->sql->fields = $fields;
        $this->sql->from = $from;
        $this->sql->where = $where;
        $this->sql->params = $params;
    }

    
    function query_db($pagesize, $useinitialsbar=true) {
        global $DB;
        if (!$this->is_downloading()) {
            if ($this->countsql === NULL) {
                $this->countsql = 'SELECT COUNT(1) FROM '.$this->sql->from.' WHERE '.$this->sql->where;
                $this->countparams = $this->sql->params;
            }
            $grandtotal = $DB->count_records_sql($this->countsql, $this->countparams);
            if ($useinitialsbar && !$this->is_downloading()) {
                $this->initialbars($grandtotal > $pagesize);
            }

            list($wsql, $wparams) = $this->get_sql_where();
            if ($wsql) {
                $this->countsql .= ' AND '.$wsql;
                $this->countparams = array_merge($this->countparams, $wparams);

                $this->sql->where .= ' AND '.$wsql;
                $this->sql->params = array_merge($this->sql->params, $wparams);

                $total  = $DB->count_records_sql($this->countsql, $this->countparams);
            } else {
                $total = $grandtotal;
            }

            $this->pagesize($pagesize, $total);
        }

                $sort = $this->get_sql_sort();
        if ($sort) {
            $sort = "ORDER BY $sort";
        }
        $sql = "SELECT
                {$this->sql->fields}
                FROM {$this->sql->from}
                WHERE {$this->sql->where}
                {$sort}";

        if (!$this->is_downloading()) {
            $this->rawdata = $DB->get_records_sql($sql, $this->sql->params, $this->get_page_start(), $this->get_page_size());
        } else {
            $this->rawdata = $DB->get_records_sql($sql, $this->sql->params);
        }
    }

    
    function out($pagesize, $useinitialsbar, $downloadhelpbutton='') {
        global $DB;
        if (!$this->columns) {
            $onerow = $DB->get_record_sql("SELECT {$this->sql->fields} FROM {$this->sql->from} WHERE {$this->sql->where}",
                $this->sql->params, IGNORE_MULTIPLE);
                                    $this->define_columns(array_keys((array)$onerow));
            $this->define_headers(array_keys((array)$onerow));
        }
        $this->setup();
        $this->query_db($pagesize, $useinitialsbar);
        $this->build_table();
        $this->finish_output();
    }
}



class table_default_export_format_parent {
    
    var $table;

    
    var $documentstarted = false;

    
    public function __construct(&$table) {
        $this->table =& $table;
    }

    
    public function table_default_export_format_parent(&$table) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($table);
    }

    function set_table(&$table) {
        $this->table =& $table;
    }

    function add_data($row) {
        return false;
    }

    function add_seperator() {
        return false;
    }

    function document_started() {
        return $this->documentstarted;
    }
    
    function format_text($text, $format=FORMAT_MOODLE, $options=NULL, $courseid=NULL) {
                $text = str_replace(array('</p>', "\n", "\r"), '   ', $text);
        return strip_tags($text);
    }
}


class table_dataformat_export_format extends table_default_export_format_parent {

    
    protected $dataformat;

    
    protected $rownum = 0;

    
    protected $columns;

    
    public function __construct(&$table, $dataformat) {
        parent::__construct($table);

        if (ob_get_length()) {
            throw new coding_exception("Output can not be buffered before instantiating table_dataformat_export_format");
        }

        $classname = 'dataformat_' . $dataformat . '\writer';
        if (!class_exists($classname)) {
            throw new coding_exception("Unable to locate dataformat/$dataformat/classes/writer.php");
        }
        $this->dataformat = new $classname;

                set_time_limit(0);

                \core\session\manager::write_close();
    }

    
    public function start_document($filename) {
        $this->filename = $filename;
        $this->documentstarted = true;
        $this->dataformat->set_filename($filename);
    }

    
    public function start_table($sheettitle) {
        $this->dataformat->set_sheettitle($sheettitle);
        $this->dataformat->send_http_headers();
    }

    
    public function output_headers($headers) {
        $this->columns = $headers;
        $this->dataformat->write_header($headers);
    }

    
    public function add_data($row) {
        $this->dataformat->write_record($row, $this->rownum++);
        return true;
    }

    
    public function finish_table() {
        $this->dataformat->write_footer($this->columns);
    }

    
    public function finish_document() {
        exit;
    }

}

