<?php



defined('MOODLE_INTERNAL') || die();




define('FORMAT_MOODLE',   '0');


define('FORMAT_HTML',     '1');


define('FORMAT_PLAIN',    '2');


define('FORMAT_WIKI',     '3');


define('FORMAT_MARKDOWN', '4');


define('URL_MATCH_BASE', 0);


define('URL_MATCH_PARAMS', 1);


define('URL_MATCH_EXACT', 2);



function s($var) {

    if ($var === false) {
        return '0';
    }

                return preg_replace('/&amp;#(\d+|x[0-9a-f]+);/i', '&#$1;', htmlspecialchars($var, ENT_QUOTES, 'UTF-8'));
}


function p($var) {
    echo s($var);
}


function addslashes_js($var) {
    if (is_string($var)) {
        $var = str_replace('\\', '\\\\', $var);
        $var = str_replace(array('\'', '"', "\n", "\r", "\0"), array('\\\'', '\\"', '\\n', '\\r', '\\0'), $var);
        $var = str_replace('</', '<\/', $var);       } else if (is_array($var)) {
        $var = array_map('addslashes_js', $var);
    } else if (is_object($var)) {
        $a = get_object_vars($var);
        foreach ($a as $key => $value) {
            $a[$key] = addslashes_js($value);
        }
        $var = (object)$a;
    }
    return $var;
}


function strip_querystring($url) {

    if ($commapos = strpos($url, '?')) {
        return substr($url, 0, $commapos);
    } else {
        return $url;
    }
}


function me() {
    global $ME;
    return $ME;
}


function qualified_me() {
    global $FULLME, $PAGE, $CFG;

    if (isset($PAGE) and $PAGE->has_set_url()) {
                return $PAGE->url->out(false);

    } else {
        if ($FULLME === null) {
                        return false;
        }
        if (!empty($CFG->sslproxy)) {
                        return preg_replace('/^http:/', 'https:', $FULLME, 1);
        } else {
            return $FULLME;
        }
    }
}


function is_https() {
    global $CFG;

    return (strpos($CFG->httpswwwroot, 'https://') === 0);
}


function get_local_referer($stripquery = true) {
    if (isset($_SERVER['HTTP_REFERER'])) {
        $referer = clean_param($_SERVER['HTTP_REFERER'], PARAM_LOCALURL);
        if ($stripquery) {
            return strip_querystring($referer);
        } else {
            return $referer;
        }
    } else {
        return '';
    }
}


class moodle_url {

    
    protected $scheme = '';

    
    protected $host = '';

    
    protected $port = '';

    
    protected $user = '';

    
    protected $pass = '';

    
    protected $path = '';

    
    protected $slashargument = '';

    
    protected $anchor = null;

    
    protected $params = array();

    
    public function __construct($url, array $params = null, $anchor = null) {
        global $CFG;

        if ($url instanceof moodle_url) {
            $this->scheme = $url->scheme;
            $this->host = $url->host;
            $this->port = $url->port;
            $this->user = $url->user;
            $this->pass = $url->pass;
            $this->path = $url->path;
            $this->slashargument = $url->slashargument;
            $this->params = $url->params;
            $this->anchor = $url->anchor;

        } else {
                        $apos = strpos($url, '#');
            if ($apos !== false) {
                $anchor = substr($url, $apos);
                $anchor = ltrim($anchor, '#');
                $this->set_anchor($anchor);
                $url = substr($url, 0, $apos);
            }

                        if (strpos($url, '/') === 0) {
                                                $url = $CFG->wwwroot.$url;
            }

                        if ($CFG->admin !== 'admin') {
                if (strpos($url, "$CFG->wwwroot/admin/") === 0) {
                    $url = str_replace("$CFG->wwwroot/admin/", "$CFG->wwwroot/$CFG->admin/", $url);
                }
            }

                        $parts = parse_url($url);
            if ($parts === false) {
                throw new moodle_exception('invalidurl');
            }
            if (isset($parts['query'])) {
                                parse_str(str_replace('&amp;', '&', $parts['query']), $this->params);
            }
            unset($parts['query']);
            foreach ($parts as $key => $value) {
                $this->$key = $value;
            }

                        $pos = strpos($this->path, '.php/');
            if ($pos !== false) {
                $this->slashargument = substr($this->path, $pos + 4);
                $this->path = substr($this->path, 0, $pos + 4);
            }
        }

        $this->params($params);
        if ($anchor !== null) {
            $this->anchor = (string)$anchor;
        }
    }

    
    public function params(array $params = null) {
        $params = (array)$params;

        foreach ($params as $key => $value) {
            if (is_int($key)) {
                throw new coding_exception('Url parameters can not have numeric keys!');
            }
            if (!is_string($value)) {
                if (is_array($value)) {
                    throw new coding_exception('Url parameters values can not be arrays!');
                }
                if (is_object($value) and !method_exists($value, '__toString')) {
                    throw new coding_exception('Url parameters values can not be objects, unless __toString() is defined!');
                }
            }
            $this->params[$key] = (string)$value;
        }
        return $this->params;
    }

    
    public function remove_params($params = null) {
        if (!is_array($params)) {
            $params = func_get_args();
        }
        foreach ($params as $param) {
            unset($this->params[$param]);
        }
        return $this->params;
    }

    
    public function remove_all_params($params = null) {
        $this->params = array();
        $this->slashargument = '';
    }

    
    public function param($paramname, $newvalue = '') {
        if (func_num_args() > 1) {
                        $this->params(array($paramname => $newvalue));
        }
        if (isset($this->params[$paramname])) {
            return $this->params[$paramname];
        } else {
            return null;
        }
    }

    
    protected function merge_overrideparams(array $overrideparams = null) {
        $overrideparams = (array)$overrideparams;
        $params = $this->params;
        foreach ($overrideparams as $key => $value) {
            if (is_int($key)) {
                throw new coding_exception('Overridden parameters can not have numeric keys!');
            }
            if (is_array($value)) {
                throw new coding_exception('Overridden parameters values can not be arrays!');
            }
            if (is_object($value) and !method_exists($value, '__toString')) {
                throw new coding_exception('Overridden parameters values can not be objects, unless __toString() is defined!');
            }
            $params[$key] = (string)$value;
        }
        return $params;
    }

    
    public function get_query_string($escaped = true, array $overrideparams = null) {
        $arr = array();
        if ($overrideparams !== null) {
            $params = $this->merge_overrideparams($overrideparams);
        } else {
            $params = $this->params;
        }
        foreach ($params as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $index => $value) {
                    $arr[] = rawurlencode($key.'['.$index.']')."=".rawurlencode($value);
                }
            } else {
                if (isset($val) && $val !== '') {
                    $arr[] = rawurlencode($key)."=".rawurlencode($val);
                } else {
                    $arr[] = rawurlencode($key);
                }
            }
        }
        if ($escaped) {
            return implode('&amp;', $arr);
        } else {
            return implode('&', $arr);
        }
    }

    
    public function __toString() {
        return $this->out(true);
    }

    
    public function out($escaped = true, array $overrideparams = null) {

        global $CFG;

        if (!is_bool($escaped)) {
            debugging('Escape parameter must be of type boolean, '.gettype($escaped).' given instead.');
        }

        $url = $this;

                if (isset($CFG->urlrewriteclass) && !isset($CFG->upgraderunning)) {
            $class = $CFG->urlrewriteclass;
            $pluginurl = $class::url_rewrite($url);
            if ($pluginurl instanceof moodle_url) {
                $url = $pluginurl;
            }
        }

        return $url->raw_out($escaped, $overrideparams);

    }

    
    public function raw_out($escaped = true, array $overrideparams = null) {
        if (!is_bool($escaped)) {
            debugging('Escape parameter must be of type boolean, '.gettype($escaped).' given instead.');
        }

        $uri = $this->out_omit_querystring().$this->slashargument;

        $querystring = $this->get_query_string($escaped, $overrideparams);
        if ($querystring !== '') {
            $uri .= '?' . $querystring;
        }
        if (!is_null($this->anchor)) {
            $uri .= '#'.$this->anchor;
        }

        return $uri;
    }

    
    public function out_omit_querystring($includeanchor = false) {

        $uri = $this->scheme ? $this->scheme.':'.((strtolower($this->scheme) == 'mailto') ? '':'//'): '';
        $uri .= $this->user ? $this->user.($this->pass? ':'.$this->pass:'').'@':'';
        $uri .= $this->host ? $this->host : '';
        $uri .= $this->port ? ':'.$this->port : '';
        $uri .= $this->path ? $this->path : '';
        if ($includeanchor and !is_null($this->anchor)) {
            $uri .= '#' . $this->anchor;
        }

        return $uri;
    }

    
    public function compare(moodle_url $url, $matchtype = URL_MATCH_EXACT) {

        $baseself = $this->out_omit_querystring();
        $baseother = $url->out_omit_querystring();

                if (substr($baseself, -1) == '/') {
            $baseself .= 'index.php';
        }
        if (substr($baseother, -1) == '/') {
            $baseother .= 'index.php';
        }

                if ($baseself != $baseother) {
            return false;
        }

        if ($matchtype == URL_MATCH_BASE) {
            return true;
        }

        $urlparams = $url->params();
        foreach ($this->params() as $param => $value) {
            if ($param == 'sesskey') {
                continue;
            }
            if (!array_key_exists($param, $urlparams) || $urlparams[$param] != $value) {
                return false;
            }
        }

        if ($matchtype == URL_MATCH_PARAMS) {
            return true;
        }

        foreach ($urlparams as $param => $value) {
            if ($param == 'sesskey') {
                continue;
            }
            if (!array_key_exists($param, $this->params()) || $this->param($param) != $value) {
                return false;
            }
        }

        if ($url->anchor !== $this->anchor) {
            return false;
        }

        return true;
    }

    
    public function set_anchor($anchor) {
        if (is_null($anchor)) {
                        $this->anchor = null;
        } else if ($anchor === '') {
                        $this->anchor = '';
        } else if (preg_match('|[a-zA-Z\_\:][a-zA-Z0-9\_\-\.\:]*|', $anchor)) {
                        $this->anchor = $anchor;
        } else {
                        $this->anchor = null;
        }
    }

    
    public function set_scheme($scheme) {
                if (preg_match('/^[a-zA-Z][a-zA-Z0-9+.-]*$/', $scheme)) {
            $this->scheme = $scheme;
        } else {
            throw new coding_exception('Bad URL scheme.');
        }
    }

    
    public function set_slashargument($path, $parameter = 'file', $supported = null) {
        global $CFG;
        if (is_null($supported)) {
            $supported = !empty($CFG->slasharguments);
        }

        if ($supported) {
            $parts = explode('/', $path);
            $parts = array_map('rawurlencode', $parts);
            $path  = implode('/', $parts);
            $this->slashargument = $path;
            unset($this->params[$parameter]);

        } else {
            $this->slashargument = '';
            $this->params[$parameter] = $path;
        }
    }

    
    
    public static function make_file_url($urlbase, $path, $forcedownload = false) {
        $params = array();
        if ($forcedownload) {
            $params['forcedownload'] = 1;
        }
        $url = new moodle_url($urlbase, $params);
        $url->set_slashargument($path);
        return $url;
    }

    
    public static function make_pluginfile_url($contextid, $component, $area, $itemid, $pathname, $filename,
                                               $forcedownload = false) {
        global $CFG;
        $urlbase = "$CFG->httpswwwroot/pluginfile.php";
        if ($itemid === null) {
            return self::make_file_url($urlbase, "/$contextid/$component/$area".$pathname.$filename, $forcedownload);
        } else {
            return self::make_file_url($urlbase, "/$contextid/$component/$area/$itemid".$pathname.$filename, $forcedownload);
        }
    }

    
    public static function make_webservice_pluginfile_url($contextid, $component, $area, $itemid, $pathname, $filename,
                                               $forcedownload = false) {
        global $CFG;
        $urlbase = "$CFG->httpswwwroot/webservice/pluginfile.php";
        if ($itemid === null) {
            return self::make_file_url($urlbase, "/$contextid/$component/$area".$pathname.$filename, $forcedownload);
        } else {
            return self::make_file_url($urlbase, "/$contextid/$component/$area/$itemid".$pathname.$filename, $forcedownload);
        }
    }

    
    public static function make_draftfile_url($draftid, $pathname, $filename, $forcedownload = false) {
        global $CFG, $USER;
        $urlbase = "$CFG->httpswwwroot/draftfile.php";
        $context = context_user::instance($USER->id);

        return self::make_file_url($urlbase, "/$context->id/user/draft/$draftid".$pathname.$filename, $forcedownload);
    }

    
    public static function make_legacyfile_url($courseid, $filepath, $forcedownload = false) {
        global $CFG;

        $urlbase = "$CFG->wwwroot/file.php";
        return self::make_file_url($urlbase, '/'.$courseid.'/'.$filepath, $forcedownload);
    }

    
    public function out_as_local_url($escaped = true, array $overrideparams = null) {
        global $CFG;

        $url = $this->out($escaped, $overrideparams);
        $httpswwwroot = str_replace("http://", "https://", $CFG->wwwroot);

                if (($url === $CFG->wwwroot) || (strpos($url, $CFG->wwwroot.'/') === 0)) {
            $localurl = substr($url, strlen($CFG->wwwroot));
            return !empty($localurl) ? $localurl : '';
        } else if (($url === $httpswwwroot) || (strpos($url, $httpswwwroot.'/') === 0)) {
            $localurl = substr($url, strlen($httpswwwroot));
            return !empty($localurl) ? $localurl : '';
        } else {
            throw new coding_exception('out_as_local_url called on a non-local URL');
        }
    }

    
    public function get_path($includeslashargument = true) {
        return $this->path . ($includeslashargument ? $this->slashargument : '');
    }

    
    public function get_param($name) {
        if (array_key_exists($name, $this->params)) {
            return $this->params[$name];
        } else {
            return null;
        }
    }

    
    public function get_scheme() {
        return $this->scheme;
    }

    
    public function get_host() {
        return $this->host;
    }

    
    public function get_port() {
        return $this->port;
    }
}


function data_submitted() {

    if (empty($_POST)) {
        return false;
    } else {
        return (object)fix_utf8($_POST);
    }
}


function break_up_long_words($string, $maxsize=20, $cutchar=' ') {

        $tags = array();
    filter_save_tags($string, $tags);

        $output = '';
    $length = core_text::strlen($string);
    $wordlength = 0;

    for ($i=0; $i<$length; $i++) {
        $char = core_text::substr($string, $i, 1);
        if ($char == ' ' or $char == "\t" or $char == "\n" or $char == "\r" or $char == "<" or $char == ">") {
            $wordlength = 0;
        } else {
            $wordlength++;
            if ($wordlength > $maxsize) {
                $output .= $cutchar;
                $wordlength = 0;
            }
        }
        $output .= $char;
    }

        if (!empty($tags)) {
        $output = str_replace(array_keys($tags), $tags, $output);
    }

    return $output;
}


function close_window($delay = 0, $reloadopener = false) {
    global $PAGE, $OUTPUT;

    if (!$PAGE->headerprinted) {
        $PAGE->set_title(get_string('closewindow'));
        echo $OUTPUT->header();
    } else {
        $OUTPUT->container_end_all(false);
    }

    if ($reloadopener) {
                $PAGE->requires->js_function_call('window.opener.location.reload', array(true));
    }
    $OUTPUT->notification(get_string('windowclosing'), 'notifysuccess');

    $PAGE->requires->js_function_call('close_window', array(new stdClass()), false, $delay);

    echo $OUTPUT->footer();
    exit;
}


function page_doc_link($text='') {
    global $OUTPUT, $PAGE;
    $path = page_get_doc_link_path($PAGE);
    if (!$path) {
        return '';
    }
    return $OUTPUT->doc_link($path, $text);
}


function page_get_doc_link_path(moodle_page $page) {
    global $CFG;

    if (empty($CFG->docroot) || during_initial_install()) {
        return '';
    }
    if (!has_capability('moodle/site:doclinks', $page->context)) {
        return '';
    }

    $path = $page->docspath;
    if (!$path) {
        return '';
    }
    return $path;
}



function validate_email($address) {

    return (preg_match('#^[-!\#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+'.
                 '(\.[-!\#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+)*'.
                  '@'.
                  '[-!\#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.'.
                  '[-!\#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$#',
                  $address));
}


function get_file_argument() {
    global $SCRIPT;

    $relativepath = optional_param('file', false, PARAM_PATH);

    if ($relativepath !== false and $relativepath !== '') {
        return $relativepath;
    }
    $relativepath = false;

        if (stripos($_SERVER['SERVER_SOFTWARE'], 'iis') !== false) {
                                                if (isset($_SERVER['PATH_INFO']) and $_SERVER['PATH_INFO'] !== '') {
                        if (strpos($_SERVER['PATH_INFO'], $SCRIPT) === false) {
                $relativepath = clean_param(urldecode($_SERVER['PATH_INFO']), PARAM_PATH);
            }
        }
    } else {
                if (isset($_SERVER['PATH_INFO'])) {
            if (isset($_SERVER['SCRIPT_NAME']) and strpos($_SERVER['PATH_INFO'], $_SERVER['SCRIPT_NAME']) === 0) {
                $relativepath = substr($_SERVER['PATH_INFO'], strlen($_SERVER['SCRIPT_NAME']));
            } else {
                $relativepath = $_SERVER['PATH_INFO'];
            }
            $relativepath = clean_param($relativepath, PARAM_PATH);
        }
    }

    return $relativepath;
}


function format_text_menu() {
    return array (FORMAT_MOODLE => get_string('formattext'),
                  FORMAT_HTML => get_string('formathtml'),
                  FORMAT_PLAIN => get_string('formatplain'),
                  FORMAT_MARKDOWN => get_string('formatmarkdown'));
}


function format_text($text, $format = FORMAT_MOODLE, $options = null, $courseiddonotuse = null) {
    global $CFG, $DB, $PAGE;

    if ($text === '' || is_null($text)) {
                return '';
    }

        $options = (array)$options;

    if (!isset($options['trusted'])) {
        $options['trusted'] = false;
    }
    if (!isset($options['noclean'])) {
        if ($options['trusted'] and trusttext_active()) {
                        $options['noclean'] = true;
        } else {
            $options['noclean'] = false;
        }
    }
    if (!isset($options['nocache'])) {
        $options['nocache'] = false;
    }
    if (!isset($options['filter'])) {
        $options['filter'] = true;
    }
    if (!isset($options['para'])) {
        $options['para'] = true;
    }
    if (!isset($options['newlines'])) {
        $options['newlines'] = true;
    }
    if (!isset($options['overflowdiv'])) {
        $options['overflowdiv'] = false;
    }
    $options['blanktarget'] = !empty($options['blanktarget']);

        if (empty($CFG->version) or $CFG->version < 2013051400 or during_initial_install()) {
                $context = null;

    } else if (isset($options['context'])) {         if (is_object($options['context'])) {
            $context = $options['context'];
        } else {
            $context = context::instance_by_id($options['context']);
        }
    } else if ($courseiddonotuse) {
                $context = context_course::instance($courseiddonotuse);
    } else {
                $context = $PAGE->context;
    }

    if (!$context) {
                $options['nocache'] = true;
        $options['filter']  = false;
    }

    if ($options['filter']) {
        $filtermanager = filter_manager::instance();
        $filtermanager->setup_page_for_filters($PAGE, $context);         $filteroptions = array(
            'originalformat' => $format,
            'noclean' => $options['noclean'],
        );
    } else {
        $filtermanager = new null_filter_manager();
        $filteroptions = array();
    }

    switch ($format) {
        case FORMAT_HTML:
            if (!$options['noclean']) {
                $text = clean_text($text, FORMAT_HTML, $options);
            }
            $text = $filtermanager->filter_text($text, $context, $filteroptions);
            break;

        case FORMAT_PLAIN:
            $text = s($text);             $text = rebuildnolinktag($text);
            $text = str_replace('  ', '&nbsp; ', $text);
            $text = nl2br($text);
            break;

        case FORMAT_WIKI:
                        $text = '<p>NOTICE: Wiki-like formatting has been removed from Moodle.  You should not be seeing
                     this message as all texts should have been converted to Markdown format instead.
                     Please post a bug report to http://moodle.org/bugs with information about where you
                     saw this message.</p>'.s($text);
            break;

        case FORMAT_MARKDOWN:
            $text = markdown_to_html($text);
            if (!$options['noclean']) {
                $text = clean_text($text, FORMAT_HTML, $options);
            }
            $text = $filtermanager->filter_text($text, $context, $filteroptions);
            break;

        default:              $text = text_to_html($text, null, $options['para'], $options['newlines']);
            if (!$options['noclean']) {
                $text = clean_text($text, FORMAT_HTML, $options);
            }
            $text = $filtermanager->filter_text($text, $context, $filteroptions);
            break;
    }
    if ($options['filter']) {
                                        $text = str_replace("\"$CFG->httpswwwroot/draftfile.php", "\"$CFG->httpswwwroot/brokenfile.php#", $text);

        if ($CFG->debugdeveloper) {
            if (strpos($text, '@@PLUGINFILE@@/') !== false) {
                debugging('Before calling format_text(), the content must be processed with file_rewrite_pluginfile_urls()',
                    DEBUG_DEVELOPER);
            }
        }
    }

    if (!empty($options['overflowdiv'])) {
        $text = html_writer::tag('div', $text, array('class' => 'no-overflow'));
    }

    if ($options['blanktarget']) {
        $domdoc = new DOMDocument();
        libxml_use_internal_errors(true);
        $domdoc->loadHTML('<?xml version="1.0" encoding="UTF-8" ?>' . $text);
        libxml_clear_errors();
        foreach ($domdoc->getElementsByTagName('a') as $link) {
            if ($link->hasAttribute('target') && strpos($link->getAttribute('target'), '_blank') === false) {
                continue;
            }
            $link->setAttribute('target', '_blank');
            if (strpos($link->getAttribute('rel'), 'noreferrer') === false) {
                $link->setAttribute('rel', trim($link->getAttribute('rel') . ' noreferrer'));
            }
        }

                                        $text = trim(preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $domdoc->saveHTML($domdoc->documentElement)));
    }

    return $text;
}


function reset_text_filters_cache($phpunitreset = false) {
    global $CFG, $DB;

    if ($phpunitreset) {
                        return;
    }

        
        if (empty($CFG->stringfilters)) {
        set_config('filterall', 0);
        return;
    }
    $installedfilters = core_component::get_plugin_list('filter');
    $filters = explode(',', $CFG->stringfilters);
    foreach ($filters as $filter) {
        if (isset($installedfilters[$filter])) {
            set_config('filterall', 1);
            return;
        }
    }
    set_config('filterall', 0);
}


function format_string($string, $striplinks = true, $options = null) {
    global $CFG, $PAGE;

        static $strcache = false;

    if (empty($CFG->version) or $CFG->version < 2013051400 or during_initial_install()) {
                return $string = strip_tags($string);
    }

    if ($strcache === false or count($strcache) > 2000) {
                $strcache = array();
    }

    if (is_numeric($options)) {
                $options  = array('context' => context_course::instance($options));
    } else {
                $options = (array)$options;
    }

    if (empty($options['context'])) {
                $options['context'] = $PAGE->context;
    } else if (is_numeric($options['context'])) {
        $options['context'] = context::instance_by_id($options['context']);
    }
    if (!isset($options['filter'])) {
        $options['filter'] = true;
    }

    $options['escape'] = !isset($options['escape']) || $options['escape'];

    if (!$options['context']) {
                return $string = strip_tags($string);
    }

        $md5 = md5($string.'<+>'.$striplinks.'<+>'.$options['context']->id.'<+>'.$options['escape'].'<+>'.current_language());

        if (isset($strcache[$md5])) {
        return $strcache[$md5];
    }

            $string = $options['escape'] ? replace_ampersands_not_followed_by_entity($string) : $string;

    if (!empty($CFG->filterall) && $options['filter']) {
        $filtermanager = filter_manager::instance();
        $filtermanager->setup_page_for_filters($PAGE, $options['context']);         $string = $filtermanager->filter_string($string, $options['context']);
    }

        if (!empty($CFG->formatstringstriptags)) {
        if ($options['escape']) {
            $string = str_replace(array('<', '>'), array('&lt;', '&gt;'), strip_tags($string));
        } else {
            $string = strip_tags($string);
        }
    } else {
                if ($striplinks) {
                        $string = strip_links($string);
        }
        $string = clean_text($string);
    }

        $strcache[$md5] = $string;

    return $string;
}


function replace_ampersands_not_followed_by_entity($string) {
    return preg_replace("/\&(?![a-zA-Z0-9#]{1,8};)/", "&amp;", $string);
}


function strip_links($string) {
    return preg_replace('/(<a\s[^>]+?>)(.+?)(<\/a>)/is', '$2', $string);
}


function wikify_links($string) {
    return preg_replace('~(<a [^<]*href=["|\']?([^ "\']*)["|\']?[^>]*>([^<]*)</a>)~i', '$3 [ $2 ]', $string);
}


function format_text_email($text, $format) {

    switch ($format) {

        case FORMAT_PLAIN:
            return $text;
            break;

        case FORMAT_WIKI:
                        $text = wikify_links($text);
            return core_text::entities_to_utf8(strip_tags($text), true);
            break;

        case FORMAT_HTML:
            return html_to_text($text);
            break;

        case FORMAT_MOODLE:
        case FORMAT_MARKDOWN:
        default:
            $text = wikify_links($text);
            return core_text::entities_to_utf8(strip_tags($text), true);
            break;
    }
}


function format_module_intro($module, $activity, $cmid, $filter=true) {
    global $CFG;
    require_once("$CFG->libdir/filelib.php");
    $context = context_module::instance($cmid);
    $options = array('noclean' => true, 'para' => false, 'filter' => $filter, 'context' => $context, 'overflowdiv' => true);
    $intro = file_rewrite_pluginfile_urls($activity->intro, 'pluginfile.php', $context->id, 'mod_'.$module, 'intro', null);
    return trim(format_text($intro, $activity->introformat, $options, null));
}


function strip_pluginfile_content($source) {
    $baseurl = '@@PLUGINFILE@@';
        $pattern = '$<[^<>]+["\']' . $baseurl . '[^"\']*["\'][^<>]*>$';
    $stripped = preg_replace($pattern, '', $source);
        return purify_html($stripped);
}


function trusttext_strip($text) {
    if (!is_string($text)) {
                throw new coding_exception('trusttext_strip parameter must be a string');
    }
    while (true) {         $orig = $text;
        $text = str_replace('#####TRUSTTEXT#####', '', $text);
        if (strcmp($orig, $text) === 0) {
            return $text;
        }
    }
}


function trusttext_pre_edit($object, $field, $context) {
    $trustfield  = $field.'trust';
    $formatfield = $field.'format';

    if (!$object->$trustfield or !trusttext_trusted($context)) {
        $object->$field = clean_text($object->$field, $object->$formatfield);
    }

    return $object;
}


function trusttext_trusted($context) {
    return (trusttext_active() and has_capability('moodle/site:trustcontent', $context));
}


function trusttext_active() {
    global $CFG;

    return !empty($CFG->enabletrusttext);
}


function clean_text($text, $format = FORMAT_HTML, $options = array()) {
    $text = (string)$text;

    if ($format != FORMAT_HTML and $format != FORMAT_HTML) {
                    }

    if ($format == FORMAT_PLAIN) {
        return $text;
    }

    if (is_purify_html_necessary($text)) {
        $text = purify_html($text, $options);
    }

                
    return $text;
}


function is_purify_html_necessary($text) {
    if ($text === '') {
        return false;
    }

    if ($text === (string)((int)$text)) {
        return false;
    }

    if (strpos($text, '&') !== false or preg_match('|<[^pesb/]|', $text)) {
                return true;
    }

    $altered = htmlspecialchars($text, ENT_NOQUOTES, 'UTF-8', true);
    if ($altered === $text) {
                return false;
    }

        $altered = preg_replace('|&lt;p&gt;(.*?)&lt;/p&gt;|m', '<p>$1</p>', $altered);
    if ($altered === $text) {
        return false;
    }
    $altered = preg_replace('|&lt;em&gt;([^<>]+?)&lt;/em&gt;|m', '<em>$1</em>', $altered);
    if ($altered === $text) {
        return false;
    }
    $altered = preg_replace('|&lt;strong&gt;([^<>]+?)&lt;/strong&gt;|m', '<strong>$1</strong>', $altered);
    if ($altered === $text) {
        return false;
    }
    $altered = str_replace('&lt;br /&gt;', '<br />', $altered);
    if ($altered === $text) {
        return false;
    }

    return true;
}


function purify_html($text, $options = array()) {
    global $CFG;

    $text = (string)$text;

    static $purifiers = array();
    static $caches = array();

        $version = empty($CFG->version) ? 0 : $CFG->version;
    $cachedir = "$CFG->localcachedir/htmlpurifier/$version";
    if (!file_exists($cachedir)) {
                        $purifiers = array();
        $caches = array();
        gc_collect_cycles();

        make_localcache_directory('htmlpurifier', false);
        check_dir_exists($cachedir);
    }

    $allowid = empty($options['allowid']) ? 0 : 1;
    $allowobjectembed = empty($CFG->allowobjectembed) ? 0 : 1;

    $type = 'type_'.$allowid.'_'.$allowobjectembed;

    if (!array_key_exists($type, $caches)) {
        $caches[$type] = cache::make('core', 'htmlpurifier', array('type' => $type));
    }
    $cache = $caches[$type];

        $key = "|$version|$allowobjectembed|$allowid|$text";
    $filteredtext = $cache->get($key);

    if ($filteredtext === true) {
                return $text;
    } else if ($filteredtext !== false) {
        return $filteredtext;
    }

    if (empty($purifiers[$type])) {
        require_once $CFG->libdir.'/htmlpurifier/HTMLPurifier.safe-includes.php';
        require_once $CFG->libdir.'/htmlpurifier/locallib.php';
        $config = HTMLPurifier_Config::createDefault();

        $config->set('HTML.DefinitionID', 'moodlehtml');
        $config->set('HTML.DefinitionRev', 5);
        $config->set('Cache.SerializerPath', $cachedir);
        $config->set('Cache.SerializerPermissions', $CFG->directorypermissions);
        $config->set('Core.NormalizeNewlines', false);
        $config->set('Core.ConvertDocumentToFragment', true);
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('HTML.Doctype', 'XHTML 1.0 Transitional');
        $config->set('URI.AllowedSchemes', array(
            'http' => true,
            'https' => true,
            'ftp' => true,
            'irc' => true,
            'nntp' => true,
            'news' => true,
            'rtsp' => true,
            'rtmp' => true,
            'teamspeak' => true,
            'gopher' => true,
            'mms' => true,
            'mailto' => true
        ));
        $config->set('Attr.AllowedFrameTargets', array('_blank'));

        if ($allowobjectembed) {
            $config->set('HTML.SafeObject', true);
            $config->set('Output.FlashCompat', true);
            $config->set('HTML.SafeEmbed', true);
        }

        if ($allowid) {
            $config->set('Attr.EnableID', true);
        }

        if ($def = $config->maybeGetRawHTMLDefinition()) {
            $def->addElement('nolink', 'Block', 'Flow', array());                                   $def->addElement('tex', 'Inline', 'Inline', array());                                   $def->addElement('algebra', 'Inline', 'Inline', array());                               $def->addElement('lang', 'Block', 'Flow', array(), array('lang'=>'CDATA'));             $def->addAttribute('span', 'xxxlang', 'CDATA');                             
                                    $def->addElement('video', 'Block', 'Optional: #PCDATA | Flow | source | track', 'Common', [
                'src' => 'URI',
                'crossorigin' => 'Enum#anonymous,use-credentials',
                'poster' => 'URI',
                'preload' => 'Enum#auto,metadata,none',
                'autoplay' => 'Bool',
                'playsinline' => 'Bool',
                'loop' => 'Bool',
                'muted' => 'Bool',
                'controls' => 'Bool',
                'width' => 'Length',
                'height' => 'Length',
            ]);
                        $def->addElement('audio', 'Block', 'Optional: #PCDATA | Flow | source | track', 'Common', [
                'src' => 'URI',
                'crossorigin' => 'Enum#anonymous,use-credentials',
                'preload' => 'Enum#auto,metadata,none',
                'autoplay' => 'Bool',
                'loop' => 'Bool',
                'muted' => 'Bool',
                'controls' => 'Bool'
            ]);
                        $def->addElement('source', false, 'Empty', null, [
                'src' => 'URI',
                'type' => 'Text'
            ]);
                        $def->addElement('track', false, 'Empty', null, [
                'src' => 'URI',
                'kind' => 'Enum#subtitles,captions,descriptions,chapters,metadata',
                'srclang' => 'Text',
                'label' => 'Text',
                'default' => 'Bool',
            ]);

                        $def->manager->addModule(new HTMLPurifier_HTMLModule_Ruby());

                        $def->manager->addModule(new HTMLPurifier_HTMLModule_Noreferrer());
        }

        $purifier = new HTMLPurifier($config);
        $purifiers[$type] = $purifier;
    } else {
        $purifier = $purifiers[$type];
    }

    $multilang = (strpos($text, 'class="multilang"') !== false);

    $filteredtext = $text;
    if ($multilang) {
        $filteredtextregex = '/<span(\s+lang="([a-zA-Z0-9_-]+)"|\s+class="multilang"){2}\s*>/';
        $filteredtext = preg_replace($filteredtextregex, '<span xxxlang="${2}">', $filteredtext);
    }
    $filteredtext = (string)$purifier->purify($filteredtext);
    if ($multilang) {
        $filteredtext = preg_replace('/<span xxxlang="([a-zA-Z0-9_-]+)">/', '<span lang="${1}" class="multilang">', $filteredtext);
    }

    if ($text === $filteredtext) {
                        $cache->set($key, true);
    } else {
        $cache->set($key, $filteredtext);
    }

    return $filteredtext;
}


function text_to_html($text, $smileyignored = null, $para = true, $newlines = true) {
        $text = preg_replace("~>([[:space:]]+)<~i", "><", $text);

        $text = preg_replace("~([\n\r])<~i", " <", $text);
    $text = preg_replace("~>([\n\r])~i", "> ", $text);

        if ($newlines) {
        $text = nl2br($text);
    }

        if ($para) {
                return '<div class="text_to_html">'.$text.'</div>';
    } else {
        return $text;
    }
}


function markdown_to_html($text) {
    global $CFG;

    if ($text === '' or $text === null) {
        return $text;
    }

    require_once($CFG->libdir .'/markdown/MarkdownInterface.php');
    require_once($CFG->libdir .'/markdown/Markdown.php');
    require_once($CFG->libdir .'/markdown/MarkdownExtra.php');

    return \Michelf\MarkdownExtra::defaultTransform($text);
}


function html_to_text($html, $width = 75, $dolinks = true) {
    global $CFG;

    require_once($CFG->libdir .'/html2text/lib.php');

    $options = array(
        'width'     => $width,
        'do_links'  => 'table',
    );

    if (empty($dolinks)) {
        $options['do_links'] = 'none';
    }
    $h2t = new core_html2text($html, $options);
    $result = $h2t->getText();

    return $result;
}


function content_to_text($content, $contentformat) {

    switch ($contentformat) {
        case FORMAT_PLAIN:
                        break;
        case FORMAT_MARKDOWN:
            $content = markdown_to_html($content);
            $content = html_to_text($content, 75, false);
            break;
        default:
                                    $content = html_to_text($content, 75, false);
    }

    return trim($content, "\r\n ");
}


function highlight($needle, $haystack, $matchcase = false,
        $prefix = '<span class="highlight">', $suffix = '</span>') {

        if (empty($needle) or empty($haystack)) {
        return $haystack;
    }

        $words = preg_split('/ +/', trim($needle));
    foreach ($words as $index => $word) {
        if (strpos($word, '-') === 0) {
            unset($words[$index]);
        } else if (strpos($word, '+') === 0) {
            $words[$index] = '\b' . preg_quote(ltrim($word, '+'), '/') . '\b';         } else {
            $words[$index] = preg_quote($word, '/');
        }
    }
    $regexp = '/(' . implode('|', $words) . ')/u';     if (!$matchcase) {
        $regexp .= 'i';
    }

        if (empty($words)) {
        return $haystack;
    }

        $chunks = preg_split('/((?:<[^>]*>)+)/', $haystack, -1, PREG_SPLIT_DELIM_CAPTURE);

            $ishtmlchunk = false;
    $result = '';
    foreach ($chunks as $chunk) {
        if ($ishtmlchunk) {
            $result .= $chunk;
        } else {
            $result .= preg_replace($regexp, $prefix . '$1' . $suffix, $chunk);
        }
        $ishtmlchunk = !$ishtmlchunk;
    }

    return $result;
}


function highlightfast($needle, $haystack) {

    if (empty($needle) or empty($haystack)) {
        return $haystack;
    }

    $parts = explode(core_text::strtolower($needle), core_text::strtolower($haystack));

    if (count($parts) === 1) {
        return $haystack;
    }

    $pos = 0;

    foreach ($parts as $key => $part) {
        $parts[$key] = substr($haystack, $pos, strlen($part));
        $pos += strlen($part);

        $parts[$key] .= '<span class="highlight">'.substr($haystack, $pos, strlen($needle)).'</span>';
        $pos += strlen($needle);
    }

    return str_replace('<span class="highlight"></span>', '', join('', $parts));
}


function get_html_lang($dir = false) {
    $direction = '';
    if ($dir) {
        if (right_to_left()) {
            $direction = ' dir="rtl"';
        } else {
            $direction = ' dir="ltr"';
        }
    }
        $language = str_replace('_', '-', current_language());
    @header('Content-Language: '.$language);
    return ($direction.' lang="'.$language.'" xml:lang="'.$language.'"');
}




function send_headers($contenttype, $cacheable = true) {
    global $CFG;

    @header('Content-Type: ' . $contenttype);
    @header('Content-Script-Type: text/javascript');
    @header('Content-Style-Type: text/css');

    if (empty($CFG->additionalhtmlhead) or stripos($CFG->additionalhtmlhead, 'X-UA-Compatible') === false) {
        @header('X-UA-Compatible: IE=edge');
    }

    if ($cacheable) {
                @header('Cache-Control: private, pre-check=0, post-check=0, max-age=0, no-transform');
        @header('Pragma: no-cache');
        @header('Expires: ');
    } else {
                @header('Cache-Control: no-store, no-cache, must-revalidate');
        @header('Cache-Control: post-check=0, pre-check=0, no-transform', false);
        @header('Pragma: no-cache');
        @header('Expires: Mon, 20 Aug 1969 09:23:00 GMT');
        @header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    }
    @header('Accept-Ranges: none');

    if (empty($CFG->allowframembedding)) {
        @header('X-Frame-Options: sameorigin');
    }
}


function link_arrow_right($text, $url='', $accesshide=false, $addclass='') {
    global $OUTPUT;     $arrowclass = 'arrow ';
    if (!$url) {
        $arrowclass .= $addclass;
    }
    $arrow = '<span class="'.$arrowclass.'">'.$OUTPUT->rarrow().'</span>';
    $htmltext = '';
    if ($text) {
        $htmltext = '<span class="arrow_text">'.$text.'</span>&nbsp;';
        if ($accesshide) {
            $htmltext = get_accesshide($htmltext);
        }
    }
    if ($url) {
        $class = 'arrow_link';
        if ($addclass) {
            $class .= ' '.$addclass;
        }
        return '<a class="'.$class.'" href="'.$url.'" title="'.preg_replace('/<.*?>/', '', $text).'">'.$htmltext.$arrow.'</a>';
    }
    return $htmltext.$arrow;
}


function link_arrow_left($text, $url='', $accesshide=false, $addclass='') {
    global $OUTPUT;     $arrowclass = 'arrow ';
    if (! $url) {
        $arrowclass .= $addclass;
    }
    $arrow = '<span class="'.$arrowclass.'">'.$OUTPUT->larrow().'</span>';
    $htmltext = '';
    if ($text) {
        $htmltext = '&nbsp;<span class="arrow_text">'.$text.'</span>';
        if ($accesshide) {
            $htmltext = get_accesshide($htmltext);
        }
    }
    if ($url) {
        $class = 'arrow_link';
        if ($addclass) {
            $class .= ' '.$addclass;
        }
        return '<a class="'.$class.'" href="'.$url.'" title="'.preg_replace('/<.*?>/', '', $text).'">'.$arrow.$htmltext.'</a>';
    }
    return $arrow.$htmltext;
}


function get_accesshide($text, $elem='span', $class='', $attrs='') {
    return "<$elem class=\"accesshide $class\" $attrs>$text</$elem>";
}


function get_separator() {
        return ' '.link_arrow_right($text='/', $url='', $accesshide=true, 'sep').' ';
}


function print_collapsible_region($contents, $classes, $id, $caption, $userpref = '', $default = false, $return = false) {
    $output  = print_collapsible_region_start($classes, $id, $caption, $userpref, $default, true);
    $output .= $contents;
    $output .= print_collapsible_region_end(true);

    if ($return) {
        return $output;
    } else {
        echo $output;
    }
}


function print_collapsible_region_start($classes, $id, $caption, $userpref = '', $default = false, $return = false) {
    global $PAGE;

        if (!empty($userpref) and is_string($userpref)) {
        user_preference_allow_ajax_update($userpref, PARAM_BOOL);
        $collapsed = get_user_preferences($userpref, $default);
    } else {
        $collapsed = $default;
        $userpref = false;
    }

    if ($collapsed) {
        $classes .= ' collapsed';
    }

    $output = '';
    $output .= '<div id="' . $id . '" class="collapsibleregion ' . $classes . '">';
    $output .= '<div id="' . $id . '_sizer">';
    $output .= '<div id="' . $id . '_caption" class="collapsibleregioncaption">';
    $output .= $caption . ' ';
    $output .= '</div><div id="' . $id . '_inner" class="collapsibleregioninner">';
    $PAGE->requires->js_init_call('M.util.init_collapsible_region', array($id, $userpref, get_string('clicktohideshow')));

    if ($return) {
        return $output;
    } else {
        echo $output;
    }
}


function print_collapsible_region_end($return = false) {
    $output = '</div></div></div>';

    if ($return) {
        return $output;
    } else {
        echo $output;
    }
}


function print_group_picture($group, $courseid, $large=false, $return=false, $link=true) {
    global $CFG;

    if (is_array($group)) {
        $output = '';
        foreach ($group as $g) {
            $output .= print_group_picture($g, $courseid, $large, true, $link);
        }
        if ($return) {
            return $output;
        } else {
            echo $output;
            return;
        }
    }

    $context = context_course::instance($courseid);

        if (!$group->picture) {
        return '';
    }

        if ($group->hidepicture and !has_capability('moodle/course:managegroups', $context)) {
        return '';
    }

    if ($link or has_capability('moodle/site:accessallgroups', $context)) {
        $output = '<a href="'. $CFG->wwwroot .'/user/index.php?id='. $courseid .'&amp;group='. $group->id .'">';
    } else {
        $output = '';
    }
    if ($large) {
        $file = 'f1';
    } else {
        $file = 'f2';
    }

    $grouppictureurl = moodle_url::make_pluginfile_url($context->id, 'group', 'icon', $group->id, '/', $file);
    $grouppictureurl->param('rev', $group->picture);
    $output .= '<img class="grouppicture" src="'.$grouppictureurl.'"'.
        ' alt="'.s(get_string('group').' '.$group->name).'" title="'.s($group->name).'"/>';

    if ($link or has_capability('moodle/site:accessallgroups', $context)) {
        $output .= '</a>';
    }

    if ($return) {
        return $output;
    } else {
        echo $output;
    }
}



function print_recent_activity_note($time, $user, $text, $link, $return=false, $viewfullnames=null) {
    static $strftimerecent = null;
    $output = '';

    if (is_null($viewfullnames)) {
        $context = context_system::instance();
        $viewfullnames = has_capability('moodle/site:viewfullnames', $context);
    }

    if (is_null($strftimerecent)) {
        $strftimerecent = get_string('strftimerecent');
    }

    $output .= '<div class="head">';
    $output .= '<div class="date">'.userdate($time, $strftimerecent).'</div>';
    $output .= '<div class="name">'.fullname($user, $viewfullnames).'</div>';
    $output .= '</div>';
    $output .= '<div class="info"><a href="'.$link.'">'.format_string($text, true).'</a></div>';

    if ($return) {
        return $output;
    } else {
        echo $output;
    }
}


function navmenulist($course, $sections, $modinfo, $strsection, $strjumpto, $width=50, $cmid=0) {

    global $CFG, $OUTPUT;

    $section = -1;
    $menu = array();
    $doneheading = false;

    $courseformatoptions = course_get_format($course)->get_format_options();
    $coursecontext = context_course::instance($course->id);

    $menu[] = '<ul class="navmenulist"><li class="jumpto section"><span>'.$strjumpto.'</span><ul>';
    foreach ($modinfo->cms as $mod) {
        if (!$mod->has_view()) {
                        continue;
        }

                if (isset($courseformatoptions['numsections']) && $mod->sectionnum > $courseformatoptions['numsections']) {
            break;
        }

        if (!$mod->uservisible) {             continue;
        }

        if ($mod->sectionnum >= 0 and $section != $mod->sectionnum) {
            $thissection = $sections[$mod->sectionnum];

            if ($thissection->visible or
                    (isset($courseformatoptions['hiddensections']) and !$courseformatoptions['hiddensections']) or
                    has_capability('moodle/course:viewhiddensections', $coursecontext)) {
                $thissection->summary = strip_tags(format_string($thissection->summary, true));
                if (!$doneheading) {
                    $menu[] = '</ul></li>';
                }
                if ($course->format == 'weeks' or empty($thissection->summary)) {
                    $item = $strsection ." ". $mod->sectionnum;
                } else {
                    if (core_text::strlen($thissection->summary) < ($width-3)) {
                        $item = $thissection->summary;
                    } else {
                        $item = core_text::substr($thissection->summary, 0, $width).'...';
                    }
                }
                $menu[] = '<li class="section"><span>'.$item.'</span>';
                $menu[] = '<ul>';
                $doneheading = true;

                $section = $mod->sectionnum;
            } else {
                                continue;
            }
        }

        $url = $mod->modname .'/view.php?id='. $mod->id;
        $mod->name = strip_tags(format_string($mod->name ,true));
        if (core_text::strlen($mod->name) > ($width+5)) {
            $mod->name = core_text::substr($mod->name, 0, $width).'...';
        }
        if (!$mod->visible) {
            $mod->name = '('.$mod->name.')';
        }
        $class = 'activity '.$mod->modname;
        $class .= ($cmid == $mod->id) ? ' selected' : '';
        $menu[] = '<li class="'.$class.'">'.
                  '<img src="'.$OUTPUT->pix_url('icon', $mod->modname) . '" alt="" />'.
                  '<a href="'.$CFG->wwwroot.'/mod/'.$url.'">'.$mod->name.'</a></li>';
    }

    if ($doneheading) {
        $menu[] = '</ul></li>';
    }
    $menu[] = '</ul></li></ul>';

    return implode("\n", $menu);
}


function print_grade_menu($courseid, $name, $current, $includenograde=true, $return=false) {
    global $OUTPUT;

    $output = '';
    $strscale = get_string('scale');
    $strscales = get_string('scales');

    $scales = get_scales_menu($courseid);
    foreach ($scales as $i => $scalename) {
        $grades[-$i] = $strscale .': '. $scalename;
    }
    if ($includenograde) {
        $grades[0] = get_string('nograde');
    }
    for ($i=100; $i>=1; $i--) {
        $grades[$i] = $i;
    }
    $output .= html_writer::select($grades, $name, $current, false);

    $helppix = $OUTPUT->pix_url('help');
    $linkobject = '<span class="helplink"><img class="iconhelp" alt="'.$strscales.'" src="'.$helppix.'" /></span>';
    $link = new moodle_url('/course/scales.php', array('id' => $courseid, 'list' => 1));
    $action = new popup_action('click', $link, 'ratingscales', array('height' => 400, 'width' => 500));
    $output .= $OUTPUT->action_link($link, $linkobject, $action, array('title' => $strscales));

    if ($return) {
        return $output;
    } else {
        echo $output;
    }
}


function mdie($msg='', $errorcode=1) {
    trigger_error($msg);
    exit($errorcode);
}


function notice ($message, $link='', $course=null) {
    global $PAGE, $OUTPUT;

    $message = clean_text($message);   
    if (CLI_SCRIPT) {
        echo("!!$message!!\n");
        exit(1);     }

    if (!$PAGE->headerprinted) {
                $PAGE->set_title(get_string('notice'));
        echo $OUTPUT->header();
    } else {
        echo $OUTPUT->container_end_all(false);
    }

    echo $OUTPUT->box($message, 'generalbox', 'notice');
    echo $OUTPUT->continue_button($link);

    echo $OUTPUT->footer();
    exit(1); }


function redirect($url, $message='', $delay=null, $messagetype = \core\output\notification::NOTIFY_INFO) {
    global $OUTPUT, $PAGE, $CFG;

    if (CLI_SCRIPT or AJAX_SCRIPT) {
                throw new moodle_exception('redirecterrordetected', 'error');
    }

    if ($delay === null) {
        $delay = -1;
    }

        if ($PAGE) {
        $PAGE->set_context(null);
        $PAGE->set_pagelayout('redirect');          $PAGE->set_title(get_string('pageshouldredirect', 'moodle'));
    }

    if ($url instanceof moodle_url) {
        $url = $url->out(false);
    }

    $debugdisableredirect = false;
    do {
        if (defined('DEBUGGING_PRINTED')) {
                        $debugdisableredirect = true;
            break;
        }

        if (core_useragent::is_msword()) {
                                                                        $debugdisableredirect = true;
            break;
        }

        if (empty($CFG->debugdisplay) or empty($CFG->debug)) {
                        break;
        }

        if (!function_exists('error_get_last') or !$lasterror = error_get_last()) {
            break;
        }

        if (!($lasterror['type'] & $CFG->debug)) {
                        break;
        }

                if (headers_sent()) {
                        $debugdisableredirect = true;
            break;
        }

        if (ob_get_level() and ob_get_contents()) {
                                    $debugdisableredirect = true;
            break;
        }
    } while (false);

                if (!preg_match('|^[a-z]+:|i', $url)) {
                $hostpart = preg_replace('|^(.*?[^:/])/.*$|', '$1', $CFG->wwwroot);
        if (preg_match('|^/|', $url)) {
                        $url = $hostpart.$url;
        } else {
                        $url = $hostpart.preg_replace('|\?.*$|', '', me()).'/../'.$url;
        }
                while (true) {
            $newurl = preg_replace('|/(?!\.\.)[^/]*/\.\./|', '/', $url);
            if ($newurl == $url) {
                break;
            }
            $url = $newurl;
        }
    }

            $url = preg_replace('/[\x00-\x1F\x7F]/', '', $url);
    $url = str_replace('"', '%22', $url);
    $encodedurl = preg_replace("/\&(?![a-zA-Z0-9#]{1,8};)/", "&amp;", $url);
    $encodedurl = preg_replace('/^.*href="([^"]*)".*$/', "\\1", clean_text('<a href="'.$encodedurl.'" />', FORMAT_HTML));
    $url = str_replace('&amp;', '&', $encodedurl);

    if (!empty($message)) {
        if (!$debugdisableredirect && !headers_sent()) {
                                    \core\notification::add($message, $messagetype);
            $message = null;
            $delay = 0;
        } else {
            if ($delay === -1 || !is_numeric($delay)) {
                $delay = 3;
            }
            $message = clean_text($message);
        }
    } else {
        $message = get_string('pageshouldredirect');
        $delay = 0;
    }

            \core\session\manager::write_close();

    if ($delay == 0 && !$debugdisableredirect && !headers_sent()) {
                @header($_SERVER['SERVER_PROTOCOL'] . ' 303 See Other');
        @header('Location: '.$url);
        echo bootstrap_renderer::plain_redirect_message($encodedurl);
        exit;
    }

        if ($PAGE) {
        $CFG->docroot = false;         echo $OUTPUT->redirect_message($encodedurl, $message, $delay, $debugdisableredirect, $messagetype);
        exit;
    } else {
        echo bootstrap_renderer::early_redirect_message($encodedurl, $message, $delay);
        exit;
    }
}


function obfuscate_email($email) {
    $i = 0;
    $length = strlen($email);
    $obfuscated = '';
    while ($i < $length) {
        if (rand(0, 2) && $email{$i}!='@') {             $obfuscated.='%'.dechex(ord($email{$i}));
        } else {
            $obfuscated.=$email{$i};
        }
        $i++;
    }
    return $obfuscated;
}


function obfuscate_text($plaintext) {
    $i=0;
    $length = core_text::strlen($plaintext);
    $obfuscated='';
    $prevobfuscated = false;
    while ($i < $length) {
        $char = core_text::substr($plaintext, $i, 1);
        $ord = core_text::utf8ord($char);
        $numerical = ($ord >= ord('0')) && ($ord <= ord('9'));
        if ($prevobfuscated and $numerical ) {
            $obfuscated.='&#'.$ord.';';
        } else if (rand(0, 2)) {
            $obfuscated.='&#'.$ord.';';
            $prevobfuscated = true;
        } else {
            $obfuscated.=$char;
            $prevobfuscated = false;
        }
        $i++;
    }
    return $obfuscated;
}


function obfuscate_mailto($email, $label='', $dimmed=false, $subject = '', $body = '') {

    if (empty($label)) {
        $label = $email;
    }

    $label = obfuscate_text($label);
    $email = obfuscate_email($email);
    $mailto = obfuscate_text('mailto');
    $url = new moodle_url("mailto:$email");
    $attrs = array();

    if (!empty($subject)) {
        $url->param('subject', format_string($subject));
    }
    if (!empty($body)) {
        $url->param('body', format_string($body));
    }

        $url = preg_replace('/^mailto/', $mailto, $url->out());

    if ($dimmed) {
        $attrs['title'] = get_string('emaildisable');
        $attrs['class'] = 'dimmed';
    }

    return html_writer::link($url, $label, $attrs);
}


function rebuildnolinktag($text) {

    $text = preg_replace('/&lt;(\/*nolink)&gt;/i', '<$1>', $text);

    return $text;
}


function print_maintenance_message() {
    global $CFG, $SITE, $PAGE, $OUTPUT;

    $PAGE->set_pagetype('maintenance-message');
    $PAGE->set_pagelayout('maintenance');
    $PAGE->set_title(strip_tags($SITE->fullname));
    $PAGE->set_heading($SITE->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('sitemaintenance', 'admin'));
    if (isset($CFG->maintenance_message) and !html_is_blank($CFG->maintenance_message)) {
        echo $OUTPUT->box_start('maintenance_message generalbox boxwidthwide boxaligncenter');
        echo $CFG->maintenance_message;
        echo $OUTPUT->box_end();
    }
    echo $OUTPUT->footer();
    die;
}


function print_tabs($tabrows, $selected = null, $inactive = null, $activated = null, $return = false) {
    global $OUTPUT;

    $tabrows = array_reverse($tabrows);
    $subtree = array();
    foreach ($tabrows as $row) {
        $tree = array();

        foreach ($row as $tab) {
            $tab->inactive = is_array($inactive) && in_array((string)$tab->id, $inactive);
            $tab->activated = is_array($activated) && in_array((string)$tab->id, $activated);
            $tab->selected = (string)$tab->id == $selected;

            if ($tab->activated || $tab->selected) {
                $tab->subtree = $subtree;
            }
            $tree[] = $tab;
        }
        $subtree = $tree;
    }
    $output = $OUTPUT->tabtree($subtree);
    if ($return) {
        return $output;
    } else {
        print $output;
        return !empty($output);
    }
}


function set_debugging($level, $debugdisplay = null) {
    global $CFG;

    $CFG->debug = (int)$level;
    $CFG->debugdeveloper = (($CFG->debug & DEBUG_DEVELOPER) === DEBUG_DEVELOPER);

    if ($debugdisplay !== null) {
        $CFG->debugdisplay = (bool)$debugdisplay;
    }
}


function debugging($message = '', $level = DEBUG_NORMAL, $backtrace = null) {
    global $CFG, $USER;

    $forcedebug = false;
    if (!empty($CFG->debugusers) && $USER) {
        $debugusers = explode(',', $CFG->debugusers);
        $forcedebug = in_array($USER->id, $debugusers);
    }

    if (!$forcedebug and (empty($CFG->debug) || ($CFG->debug != -1 and $CFG->debug < $level))) {
        return false;
    }

    if (!isset($CFG->debugdisplay)) {
        $CFG->debugdisplay = ini_get_bool('display_errors');
    }

    if ($message) {
        if (!$backtrace) {
            $backtrace = debug_backtrace();
        }
        $from = format_backtrace($backtrace, CLI_SCRIPT || NO_DEBUG_DISPLAY);
        if (PHPUNIT_TEST) {
            if (phpunit_util::debugging_triggered($message, $level, $from)) {
                                return true;
            }
        }

        if (NO_DEBUG_DISPLAY) {
                                    error_log('Debugging: ' . $message . ' in '. PHP_EOL . $from);

        } else if ($forcedebug or $CFG->debugdisplay) {
            if (!defined('DEBUGGING_PRINTED')) {
                define('DEBUGGING_PRINTED', 1);             }
            if (CLI_SCRIPT) {
                echo "++ $message ++\n$from";
            } else {
                echo '<div class="notifytiny debuggingmessage" data-rel="debugging">' , $message , $from , '</div>';
            }

        } else {
            trigger_error($message . $from, E_USER_NOTICE);
        }
    }
    return true;
}


function print_location_comment($file, $line, $return = false) {
    if ($return) {
        return "<!-- $file at line $line -->\n";
    } else {
        echo "<!-- $file at line $line -->\n";
    }
}



function right_to_left() {
    return (get_string('thisdirection', 'langconfig') === 'rtl');
}



function fix_align_rtl($align) {
    if (!right_to_left()) {
        return $align;
    }
    if ($align == 'left') {
        return 'right';
    }
    if ($align == 'right') {
        return 'left';
    }
    return $align;
}



function is_in_popup() {
    $inpopup = optional_param('inpopup', '', PARAM_BOOL);

    return ($inpopup);
}


class progress_bar {
    
    private $html_id;
    
    private $width;
    
    private $percent = 0;
    
    private $lastupdate = 0;
    
    private $time_start = 0;

    
    public function __construct($htmlid = '', $width = 500, $autostart = false) {
        if (!empty($htmlid)) {
            $this->html_id  = $htmlid;
        } else {
            $this->html_id  = 'pbar_'.uniqid();
        }

        $this->width = $width;

        if ($autostart) {
            $this->create();
        }
    }

    
    public function create() {
        global $PAGE;

        $this->time_start = microtime(true);
        if (CLI_SCRIPT) {
            return;         }

        $PAGE->requires->string_for_js('secondsleft', 'moodle');

        $htmlcode = <<<EOT
        <div class="progressbar_container" style="width: {$this->width}px;" id="{$this->html_id}">
            <h2></h2>
            <div class="progress progress-striped active">
                <div class="bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">&nbsp;</div>
            </div>
            <p></p>
        </div>
EOT;
        flush();
        echo $htmlcode;
        flush();
    }

    
    private function _update($percent, $msg) {
        if (empty($this->time_start)) {
            throw new coding_exception('You must call create() (or use the $autostart ' .
                    'argument to the constructor) before you try updating the progress bar.');
        }

        if (CLI_SCRIPT) {
            return;         }

        $estimate = $this->estimate($percent);

        if ($estimate === null) {
                    } else if ($estimate == 0) {
                    } else if ($this->lastupdate + 20 < time()) {
                    } else if (round($this->percent, 2) === round($percent, 2)) {
                        return;
        }
        if (is_numeric($estimate)) {
            $estimate = get_string('secondsleft', 'moodle', round($estimate, 2));
        }

        $this->percent = round($percent, 2);
        $this->lastupdate = microtime(true);

        echo html_writer::script(js_writer::function_call('updateProgressBar',
            array($this->html_id, $this->percent, $msg, $estimate)));
        flush();
    }

    
    private function estimate($pt) {
        if ($this->lastupdate == 0) {
            return null;
        }
        if ($pt < 0.00001) {
            return null;         }
        if ($pt > 99.99999) {
            return 0;         }
        $consumed = microtime(true) - $this->time_start;
        if ($consumed < 0.001) {
            return null;
        }

        return (100 - $pt) * ($consumed / $pt);
    }

    
    public function update_full($percent, $msg) {
        $percent = max(min($percent, 100), 0);
        $this->_update($percent, $msg);
    }

    
    public function update($cur, $total, $msg) {
        $percent = ($cur / $total) * 100;
        $this->update_full($percent, $msg);
    }

    
    public function restart() {
        $this->percent    = 0;
        $this->lastupdate = 0;
        $this->time_start = 0;
    }
}


abstract class progress_trace {
    
    abstract public function output($message, $depth = 0);

    
    public function finished() {
    }
}


class null_progress_trace extends progress_trace {
    
    public function output($message, $depth = 0) {
    }
}


class text_progress_trace extends progress_trace {
    
    public function output($message, $depth = 0) {
        echo str_repeat('  ', $depth), $message, "\n";
        flush();
    }
}


class html_progress_trace extends progress_trace {
    
    public function output($message, $depth = 0) {
        echo '<p>', str_repeat('&#160;&#160;', $depth), htmlspecialchars($message), "</p>\n";
        flush();
    }
}


class html_list_progress_trace extends progress_trace {
    
    protected $currentdepth = -1;

    
    public function output($message, $depth = 0) {
        $samedepth = true;
        while ($this->currentdepth > $depth) {
            echo "</li>\n</ul>\n";
            $this->currentdepth -= 1;
            if ($this->currentdepth == $depth) {
                echo '<li>';
            }
            $samedepth = false;
        }
        while ($this->currentdepth < $depth) {
            echo "<ul>\n<li>";
            $this->currentdepth += 1;
            $samedepth = false;
        }
        if ($samedepth) {
            echo "</li>\n<li>";
        }
        echo htmlspecialchars($message);
        flush();
    }

    
    public function finished() {
        while ($this->currentdepth >= 0) {
            echo "</li>\n</ul>\n";
            $this->currentdepth -= 1;
        }
    }
}


class error_log_progress_trace extends progress_trace {
    
    protected $prefix;

    
    public function __construct($prefix = '') {
        $this->prefix = $prefix;
    }

    
    public function output($message, $depth = 0) {
        error_log($this->prefix . str_repeat('  ', $depth) . $message);
    }
}


class progress_trace_buffer extends progress_trace {
    
    protected $trace;
    
    protected $passthrough;
    
    protected $buffer;

    
    public function __construct(progress_trace $trace, $passthrough = true) {
        $this->trace       = $trace;
        $this->passthrough = $passthrough;
        $this->buffer      = '';
    }

    
    public function output($message, $depth = 0) {
        ob_start();
        $this->trace->output($message, $depth);
        $this->buffer .= ob_get_contents();
        if ($this->passthrough) {
            ob_end_flush();
        } else {
            ob_end_clean();
        }
    }

    
    public function finished() {
        ob_start();
        $this->trace->finished();
        $this->buffer .= ob_get_contents();
        if ($this->passthrough) {
            ob_end_flush();
        } else {
            ob_end_clean();
        }
    }

    
    public function reset_buffer() {
        $this->buffer = '';
    }

    
    public function get_buffer() {
        return $this->buffer;
    }
}


class combined_progress_trace extends progress_trace {

    
    protected $traces;

    
    public function __construct(array $traces) {
        $this->traces = $traces;
    }

    
    public function output($message, $depth = 0) {
        foreach ($this->traces as $trace) {
            $trace->output($message, $depth);
        }
    }

    
    public function finished() {
        foreach ($this->traces as $trace) {
            $trace->finished();
        }
    }
}


function print_password_policy() {
    global $CFG;

    $message = '';
    if (!empty($CFG->passwordpolicy)) {
        $messages = array();
        $messages[] = get_string('informminpasswordlength', 'auth', $CFG->minpasswordlength);
        if (!empty($CFG->minpassworddigits)) {
            $messages[] = get_string('informminpassworddigits', 'auth', $CFG->minpassworddigits);
        }
        if (!empty($CFG->minpasswordlower)) {
            $messages[] = get_string('informminpasswordlower', 'auth', $CFG->minpasswordlower);
        }
        if (!empty($CFG->minpasswordupper)) {
            $messages[] = get_string('informminpasswordupper', 'auth', $CFG->minpasswordupper);
        }
        if (!empty($CFG->minpasswordnonalphanum)) {
            $messages[] = get_string('informminpasswordnonalphanum', 'auth', $CFG->minpasswordnonalphanum);
        }

        $messages = join(', ', $messages);         $message = get_string('informpasswordpolicy', 'auth', $messages);
    }
    return $message;
}


function get_formatted_help_string($identifier, $component, $ajax = false, $a = null) {
    global $CFG, $OUTPUT;
    $sm = get_string_manager();

        
    $data = new stdClass();

    if ($sm->string_exists($identifier, $component)) {
        $data->heading = format_string(get_string($identifier, $component));
    } else {
                $data->heading = '';
    }

    if ($sm->string_exists($identifier . '_help', $component)) {
        $options = new stdClass();
        $options->trusted = false;
        $options->noclean = false;
        $options->smiley = false;
        $options->filter = false;
        $options->para = true;
        $options->newlines = false;
        $options->overflowdiv = !$ajax;

                $data->text = format_text(get_string($identifier.'_help', $component, $a), FORMAT_MARKDOWN, $options);

        $helplink = $identifier . '_link';
        if ($sm->string_exists($helplink, $component)) {              $link = get_string($helplink, $component);
            $linktext = get_string('morehelp');

            $data->doclink = new stdClass();
            $url = new moodle_url(get_docs_url($link));
            if ($ajax) {
                $data->doclink->link = $url->out();
                $data->doclink->linktext = $linktext;
                $data->doclink->class = ($CFG->doctonewwindow) ? 'helplinkpopup' : '';
            } else {
                $data->completedoclink = html_writer::tag('div', $OUTPUT->doc_link($link, $linktext),
                    array('class' => 'helpdoclink'));
            }
        }
    } else {
        $data->text = html_writer::tag('p',
            html_writer::tag('strong', 'TODO') . ": missing help string [{$identifier}_help, {$component}]");
    }
    return $data;
}


function prevent_form_autofill_password() {
    return '<div class="hide"><input type="text" class="ignoredirty" /><input type="password" class="ignoredirty" /></div>';
}
