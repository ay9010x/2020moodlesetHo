<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/xhprof/xhprof_lib/utils/xhprof_lib.php');
require_once($CFG->libdir . '/xhprof/xhprof_lib/utils/xhprof_runs.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->libdir . '/setuplib.php');
require_once($CFG->libdir . '/phpunit/classes/util.php');
require_once($CFG->dirroot . '/backup/util/xml/xml_writer.class.php');
require_once($CFG->dirroot . '/backup/util/xml/output/xml_output.class.php');
require_once($CFG->dirroot . '/backup/util/xml/output/file_xml_output.class.php');



function profiling_is_running($value = null) {
    static $running = null;

    if (!is_null($value)) {
        $running = (bool)$value;
    }

    return $running;
}


function profiling_is_saved($value = null) {
    static $saved = null;

    if (!is_null($value)) {
        $saved = (bool)$value;
    }

    return $saved;
}


function profiling_start() {
    global $CFG, $SESSION, $SCRIPT;

        if (!extension_loaded('xhprof') && !extension_loaded('tideways')) {
        return false;
    }

        if (empty($CFG->profilingenabled) && empty($CFG->earlyprofilingenabled)) {
        return false;
    }

        if (profiling_is_running() || profiling_is_saved()) {
        return false;
    }

        $script = !empty($SCRIPT) ? $SCRIPT : profiling_get_script();

        $check = 'PROFILEME';
    $profileme = isset($_POST[$check]) || isset($_GET[$check]) || isset($_COOKIE[$check]) ? true : false;
    $profileme = $profileme && !empty($CFG->profilingallowme);
    $check = 'DONTPROFILEME';
    $dontprofileme = isset($_POST[$check]) || isset($_GET[$check]) || isset($_COOKIE[$check]) ? true : false;
    $dontprofileme = $dontprofileme && !empty($CFG->profilingallowme);
    $check = 'PROFILEALL';
    $profileall = isset($_POST[$check]) || isset($_GET[$check]) || isset($_COOKIE[$check]) ? true : false;
    $profileall = $profileall && !empty($CFG->profilingallowall);
    $check = 'PROFILEALLSTOP';
    $profileallstop = isset($_POST[$check]) || isset($_GET[$check]) || isset($_COOKIE[$check]) ? true : false;
    $profileallstop = $profileallstop && !empty($CFG->profilingallowall);

        if ($dontprofileme) {
        return false;
    }

        if ($profileallstop && !empty($SESSION)) {
        unset($SESSION->profileall);
    }

        if ($profileall && !empty($SESSION)) {
        $SESSION->profileall = true;

        } else if (!empty($SESSION->profileall)) {
        $profileall = true;
    }

        $profileauto = false;
    if (!empty($CFG->profilingautofrec)) {
        $profileauto = (mt_rand(1, $CFG->profilingautofrec) === 1);
    }

        $included = empty($CFG->profilingincluded) ? '' : $CFG->profilingincluded;
    $profileincluded = profiling_string_matches($script, $included);

        $excluded = empty($CFG->profilingexcluded) ? '' : $CFG->profilingexcluded;
    $profileexcluded = profiling_string_matches($script, $excluded);

        $profileauto = $profileauto && $profileincluded && !$profileexcluded;

        $profilematch = $profileincluded && !$profileexcluded && empty($CFG->profilingautofrec);

        if (!$profileauto && !$profileme && !$profileall && !$profilematch) {
        return false;
    }

        $ignore = array('call_user_func', 'call_user_func_array');
    if (extension_loaded('tideways')) {
        tideways_enable(TIDEWAYS_FLAGS_CPU + TIDEWAYS_FLAGS_MEMORY, array('ignored_functions' =>  $ignore));
    } else {
        xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY, array('ignored_functions' => $ignore));
    }
    profiling_is_running(true);

        return true;
}


function profiling_stop() {
    global $CFG, $DB, $SCRIPT;

        if (!extension_loaded('xhprof') && !extension_loaded('tideways')) {
        return false;
    }

        if (empty($CFG->profilingenabled) && empty($CFG->earlyprofilingenabled)) {
        return false;
    }

        if (!profiling_is_running() || profiling_is_saved()) {
        return false;
    }

        $script = !empty($SCRIPT) ? $SCRIPT : profiling_get_script();

        profiling_is_running(false);
    if (extension_loaded('tideways')) {
        $data = tideways_disable();
    } else {
        $data = xhprof_disable();
    }

                $tables = $DB->get_tables();
    if (!in_array('profiling', $tables)) {
        return false;
    }

    $run = new moodle_xhprofrun();
    $run->prepare_run($script);
    $runid = $run->save_run($data, null);
    profiling_is_saved(true);

        profiling_prune_old_runs($runid);

        return true;
}

function profiling_prune_old_runs($exception = 0) {
    global $CFG, $DB;

        if (empty($CFG->profilinglifetime)) {
        return;
    }

    $cuttime = time() - ($CFG->profilinglifetime * 60);
    $params = array('cuttime' => $cuttime, 'exception' => $exception);

    $DB->delete_records_select('profiling', 'runreference = 0 AND
                                             timecreated < :cuttime AND
                                             runid != :exception', $params);
}


function profiling_get_script() {
    global $CFG;

    $wwwroot = parse_url($CFG->wwwroot);

    if (!isset($wwwroot['path'])) {
        $wwwroot['path'] = '';
    }
    $wwwroot['path'] .= '/';

    $path = $_SERVER['SCRIPT_NAME'];

    if (strpos($path, $wwwroot['path']) === 0) {
        return substr($path, strlen($wwwroot['path']) - 1);
    }
    return '';
}

function profiling_urls($report, $runid, $runid2 = null) {
    global $CFG;

    $url = '';
    switch ($report) {
        case 'run':
            $url = $CFG->wwwroot . '/lib/xhprof/xhprof_html/index.php?run=' . $runid;
            break;
        case 'diff':
            $url = $CFG->wwwroot . '/lib/xhprof/xhprof_html/index.php?run1=' . $runid . '&amp;run2=' . $runid2;
            break;
        case 'graph':
            $url = $CFG->wwwroot . '/lib/xhprof/xhprof_html/callgraph.php?run=' . $runid;
            break;
    }
    return $url;
}


function profiling_print_run($run, $prevreferences = null) {
    global $CFG, $OUTPUT;

    $output = '';

        $checked = $run->runreference ? ' checked=checked' : '';
    $referenceform = "<form id=\"profiling_runreference\" action=\"index.php\" method=\"GET\">" .
                     "<input type=\"hidden\" name=\"sesskey\" value=\"" . sesskey() . "\"/>".
                     "<input type=\"hidden\" name=\"runid\" value=\"$run->runid\"/>".
                     "<input type=\"hidden\" name=\"listurl\" value=\"$run->url\"/>".
                     "<input type=\"checkbox\" name=\"runreference\" value=\"1\"$checked/>&nbsp;".
                     "<input type=\"text\" name=\"runcomment\" value=\"$run->runcomment\"/>&nbsp;".
                     "<input type=\"submit\" value=\"" . get_string('savechanges') ."\"/>".
                     "</form>";

    $table = new html_table();
    $table->align = array('right', 'left');
    $table->tablealign = 'center';
    $table->attributes['class'] = 'profilingruntable';
    $table->colclasses = array('label', 'value');
    $table->data = array(
       array(get_string('runid', 'tool_profiling'), $run->runid),
       array(get_string('url'), $run->url),
       array(get_string('date'), userdate($run->timecreated, '%d %B %Y, %H:%M')),
       array(get_string('executiontime', 'tool_profiling'), format_float($run->totalexecutiontime / 1000, 3) . ' ms'),
       array(get_string('cputime', 'tool_profiling'), format_float($run->totalcputime / 1000, 3) . ' ms'),
       array(get_string('calls', 'tool_profiling'), $run->totalcalls),
       array(get_string('memory', 'tool_profiling'), format_float($run->totalmemory / 1024, 0) . ' KB'),
       array(get_string('markreferencerun', 'tool_profiling'), $referenceform));
    $output = $OUTPUT->box(html_writer::table($table), 'generalbox boxwidthwide boxaligncenter profilingrunbox', 'profiling_summary');
        $strviewdetails = get_string('viewdetails', 'tool_profiling');
    $url = profiling_urls('run', $run->runid);
    $output .= $OUTPUT->heading('<a href="' . $url . '" onclick="javascript:window.open(' . "'" . $url . "'" . ');' .
                                'return false;"' . ' title="">' . $strviewdetails . '</a>', 3, 'main profilinglink');

        if ($prevreferences) {
        $table = new html_table();
        $table->align = array('left', 'left');
        $table->head = array(get_string('date'), get_string('runid', 'tool_profiling'), get_string('comment', 'tool_profiling'));
        $table->tablealign = 'center';
        $table->attributes['class'] = 'flexible generaltable generalbox';
        $table->colclasses = array('value', 'value', 'value');
        $table->data = array();

        $output .= $OUTPUT->heading(get_string('viewdiff', 'tool_profiling'), 3, 'main profilinglink');

        foreach ($prevreferences as $reference) {
            $url = 'index.php?runid=' . $run->runid . '&amp;runid2=' . $reference->runid . '&amp;listurl=' . urlencode($run->url);
            $row = array(userdate($reference->timecreated), '<a href="' . $url . '" title="">'.$reference->runid.'</a>', $reference->runcomment);
            $table->data[] = $row;
        }
        $output .= $OUTPUT->box(html_writer::table($table), 'profilingrunbox', 'profiling_diffs');

    }
        $strexport = get_string('exportthis', 'tool_profiling');
    $url = 'export.php?runid=' . $run->runid . '&amp;listurl=' . urlencode($run->url);
    $output.=$OUTPUT->heading('<a href="' . $url . '" title="">' . $strexport . '</a>', 3, 'main profilinglink');

    return $output;
}

function profiling_print_rundiff($run1, $run2) {
    global $CFG, $OUTPUT;

    $output = '';

        $referencetext1 = ($run1->runreference ? get_string('yes') : get_string('no')) .
                      ($run1->runcomment ? ' - ' . s($run1->runcomment) : '');
    $referencetext2 = ($run2->runreference ? get_string('yes') : get_string('no')) .
                      ($run2->runcomment ? ' - ' . s($run2->runcomment) : '');

        $diffexecutiontime = profiling_get_difference($run1->totalexecutiontime, $run2->totalexecutiontime, 'ms', 1000);
    $diffcputime       = profiling_get_difference($run1->totalcputime, $run2->totalcputime, 'ms', 1000);
    $diffcalls         = profiling_get_difference($run1->totalcalls, $run2->totalcalls);
    $diffmemory        = profiling_get_difference($run1->totalmemory, $run2->totalmemory, 'KB', 1024);

    $table = new html_table();
    $table->align = array('right', 'left', 'left', 'left');
    $table->tablealign = 'center';
    $table->attributes['class'] = 'profilingruntable';
    $table->colclasses = array('label', 'value1', 'value2');
    $table->data = array(
       array(get_string('runid', 'tool_profiling'),
           '<a href="index.php?runid=' . $run1->runid . '&listurl=' . urlencode($run1->url) . '" title="">' . $run1->runid . '</a>',
           '<a href="index.php?runid=' . $run2->runid . '&listurl=' . urlencode($run2->url) . '" title="">' . $run2->runid . '</a>'),
       array(get_string('url'), $run1->url, $run2->url),
       array(get_string('date'), userdate($run1->timecreated, '%d %B %Y, %H:%M'),
           userdate($run2->timecreated, '%d %B %Y, %H:%M')),
       array(get_string('executiontime', 'tool_profiling'),
           format_float($run1->totalexecutiontime / 1000, 3) . ' ms',
           format_float($run2->totalexecutiontime / 1000, 3) . ' ms ' . $diffexecutiontime),
       array(get_string('cputime', 'tool_profiling'),
           format_float($run1->totalcputime / 1000, 3) . ' ms',
           format_float($run2->totalcputime / 1000, 3) . ' ms ' . $diffcputime),
       array(get_string('calls', 'tool_profiling'), $run1->totalcalls, $run2->totalcalls . ' ' . $diffcalls),
       array(get_string('memory', 'tool_profiling'),
           format_float($run1->totalmemory / 1024, 0) . ' KB',
           format_float($run2->totalmemory / 1024, 0) . ' KB ' . $diffmemory),
       array(get_string('referencerun', 'tool_profiling'), $referencetext1, $referencetext2));
    $output = $OUTPUT->box(html_writer::table($table), 'generalbox boxwidthwide boxaligncenter profilingrunbox', 'profiling_summary');
        $strviewdetails = get_string('viewdiffdetails', 'tool_profiling');
    $url = profiling_urls('diff', $run1->runid, $run2->runid);
        $output.=$OUTPUT->heading('<a href="' . $url . '" onclick="javascript:window.open(' . "'" . $url . "'" . ');' .
                              'return false;"' . ' title="">' . $strviewdetails . '</a>', 3, 'main profilinglink');
    return $output;
}


function profiling_list_controls($listurl) {
    global $CFG;

    $output = '<p class="centerpara buttons">';
    $output .= '&nbsp;<a href="import.php">[' . get_string('import', 'tool_profiling') . ']</a>';
    $output .= '</p>';

    return $output;
}


function profiling_string_matches($string, $patterns) {
    $patterns = explode(',', $patterns);
    foreach ($patterns as $pattern) {
                $pattern = str_replace('\*', '.*', preg_quote(trim($pattern), '~'));
                if (empty($pattern)) {
            continue;
        }
        if (preg_match('~' . $pattern . '~', $string)) {
            return true;
        }
    }
    return false;
}


function profiling_get_difference($number1, $number2, $units = '', $factor = 1, $numdec = 2) {
    $numdiff = $number2 - $number1;
    $perdiff = 0;
    if ($number1 != $number2) {
        $perdiff = $number1 != 0 ? ($number2 * 100 / $number1) - 100 : 0;
    }
    $sign      = $number2 > $number1 ? '+' : '';
    $delta     = abs($perdiff) > 0.25 ? '&Delta;' : '&asymp;';
    $spanclass = $number2 > $number1 ? 'worse' : ($number1 > $number2 ? 'better' : 'same');
    $importantclass= abs($perdiff) > 1 ? ' profiling_important' : '';
    $startspan = '<span class="profiling_' . $spanclass . $importantclass . '">';
    $endspan   = '</span>';
    $fnumdiff = $sign . format_float($numdiff / $factor, $numdec);
    $fperdiff = $sign . format_float($perdiff, $numdec);
    return $startspan . $delta . ' ' . $fnumdiff . ' ' . $units . ' (' . $fperdiff . '%)' . $endspan;
}


function profiling_export_runs(array $runids, $file) {
    global $CFG, $DB;

        if (empty($runids)) {
        return false;
    }

        list ($insql, $inparams) = $DB->get_in_or_equal($runids);
    $reccount = $DB->count_records_select('profiling', 'runid ' . $insql, $inparams);
    if ($reccount != count($runids)) {
        return false;
    }

        $base = dirname($file);
    if (!is_writable($base)) {
        return false;
    }

        $tmpdir = $base . '/' . md5(implode($runids) . time() . random_string(20));
    mkdir($tmpdir);

        $status = profiling_export_generate($runids, $tmpdir);

        if ($status) {
        $status = profiling_export_package($file, $tmpdir);
    }

        fulldelete($tmpdir);
    return $status;
}


function profiling_import_runs($file, $commentprefix = '') {
    global $DB;

        if (!file_exists($file) or !is_readable($file) or !is_writable(dirname($file))) {
        return false;
    }

        $tmpdir = dirname($file) . '/' . time() . '_' . random_string(4);
    $fp = get_file_packer('application/vnd.moodle.profiling');
    $status = $fp->extract_to_pathname($file, $tmpdir);

        if ($status) {
        $mfile = $tmpdir . '/moodle_profiling_runs.xml';
        if (!file_exists($mfile) or !is_readable($mfile)) {
            $status = false;
        } else {
            $mdom = new DOMDocument();
            if (!$mdom->load($mfile)) {
                $status = false;
            } else {
                $status = @$mdom->schemaValidateSource(profiling_get_import_main_schema());
            }
        }
    }

        if ($status) {
        $runs = $mdom->getElementsByTagName('run');
        foreach ($runs as $run) {
            $rfile = $tmpdir . '/' . clean_param($run->getAttribute('ref'), PARAM_FILE);
            if (!file_exists($rfile) or !is_readable($rfile)) {
                $status = false;
            } else {
                $rdom = new DOMDocument();
                if (!$rdom->load($rfile)) {
                    $status = false;
                } else {
                    $status = @$rdom->schemaValidateSource(profiling_get_import_run_schema());
                }
            }
        }
    }

        if ($status) {
        reset($runs);
        foreach ($runs as $run) {
            $rfile = $tmpdir . '/' . $run->getAttribute('ref');
            $rdom = new DOMDocument();
            $rdom->load($rfile);
            $runarr = array();
            $runarr['runid'] = clean_param($rdom->getElementsByTagName('runid')->item(0)->nodeValue, PARAM_ALPHANUMEXT);
            $runarr['url'] = clean_param($rdom->getElementsByTagName('url')->item(0)->nodeValue, PARAM_CLEAN);
            $runarr['runreference'] = clean_param($rdom->getElementsByTagName('runreference')->item(0)->nodeValue, PARAM_INT);
            $runarr['runcomment'] = $commentprefix . clean_param($rdom->getElementsByTagName('runcomment')->item(0)->nodeValue, PARAM_CLEAN);
            $runarr['timecreated'] = time();             $runarr['totalexecutiontime'] = clean_param($rdom->getElementsByTagName('totalexecutiontime')->item(0)->nodeValue, PARAM_INT);
            $runarr['totalcputime'] = clean_param($rdom->getElementsByTagName('totalcputime')->item(0)->nodeValue, PARAM_INT);
            $runarr['totalcalls'] = clean_param($rdom->getElementsByTagName('totalcalls')->item(0)->nodeValue, PARAM_INT);
            $runarr['totalmemory'] = clean_param($rdom->getElementsByTagName('totalmemory')->item(0)->nodeValue, PARAM_INT);
            $runarr['data'] = clean_param($rdom->getElementsByTagName('data')->item(0)->nodeValue, PARAM_CLEAN);
                        if (!$DB->record_exists('profiling', array('runid' => $runarr['runid']))) {
                $DB->insert_record('profiling', $runarr);
            } else {
                return false;
            }
        }
    }

        remove_dir($tmpdir);

    return $status;
}


function profiling_export_generate(array $runids, $tmpdir) {
    global $CFG, $DB;

        $release = $CFG->release;
    $version = $CFG->version;
    $dbtype = $CFG->dbtype;
    $githash = phpunit_util::get_git_hash();
    $date = time();

        $mainxo = new file_xml_output($tmpdir . '/moodle_profiling_runs.xml');
    $mainxw = new xml_writer($mainxo);

        $mainxw->start();
    $mainxw->begin_tag('moodle_profiling_runs');

        $mainxw->begin_tag('info');
    $mainxw->full_tag('release', $release);
    $mainxw->full_tag('version', $version);
    $mainxw->full_tag('dbtype', $dbtype);
    if ($githash) {
        $mainxw->full_tag('githash', $githash);
    }
    $mainxw->full_tag('date', $date);
    $mainxw->end_tag('info');

        $mainxw->begin_tag('runs');
    foreach ($runids as $runid) {
                $run = $DB->get_record('profiling', array('runid' => $runid), '*', MUST_EXIST);
        $attributes = array(
                'id' => $run->id,
                'ref' => $run->runid . '.xml');
        $mainxw->full_tag('run', null, $attributes);
                $runxo = new file_xml_output($tmpdir . '/' . $attributes['ref']);
        $runxw = new xml_writer($runxo);
        $runxw->start();
        $runxw->begin_tag('moodle_profiling_run');
        $runxw->full_tag('id', $run->id);
        $runxw->full_tag('runid', $run->runid);
        $runxw->full_tag('url', $run->url);
        $runxw->full_tag('runreference', $run->runreference);
        $runxw->full_tag('runcomment', $run->runcomment);
        $runxw->full_tag('timecreated', $run->timecreated);
        $runxw->full_tag('totalexecutiontime', $run->totalexecutiontime);
        $runxw->full_tag('totalcputime', $run->totalcputime);
        $runxw->full_tag('totalcalls', $run->totalcalls);
        $runxw->full_tag('totalmemory', $run->totalmemory);
        $runxw->full_tag('data', $run->data);
        $runxw->end_tag('moodle_profiling_run');
        $runxw->stop();
    }
    $mainxw->end_tag('runs');
    $mainxw->end_tag('moodle_profiling_runs');
    $mainxw->stop();

    return true;
}


function profiling_export_package($file, $tmpdir) {
        $filestemp = get_directory_list($tmpdir, '', false, true, true);
    $files = array();

        foreach ($filestemp as $filetemp) {
        $files[$filetemp] = $tmpdir . '/' . $filetemp;
    }

        $zippacker = get_file_packer('application/zip');

        $zippacker->archive_to_pathname($files, $file);

    return true;
}


function profiling_get_import_main_schema() {
    $schema = <<<EOS
<?xml version="1.0" encoding="UTF-8"?>
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" elementFormDefault="qualified">
  <xs:element name="moodle_profiling_runs">
    <xs:complexType>
      <xs:sequence>
        <xs:element ref="info"/>
        <xs:element ref="runs"/>
      </xs:sequence>
    </xs:complexType>
  </xs:element>
  <xs:element name="info">
    <xs:complexType>
      <xs:sequence>
        <xs:element type="xs:string" name="release"/>
        <xs:element type="xs:decimal" name="version"/>
        <xs:element type="xs:string" name="dbtype"/>
        <xs:element type="xs:string" minOccurs="0" name="githash"/>
        <xs:element type="xs:int" name="date"/>
      </xs:sequence>
    </xs:complexType>
  </xs:element>
  <xs:element name="runs">
    <xs:complexType>
      <xs:sequence>
        <xs:element maxOccurs="unbounded" ref="run"/>
      </xs:sequence>
    </xs:complexType>
  </xs:element>
  <xs:element name="run">
    <xs:complexType>
      <xs:attribute type="xs:int" name="id"/>
      <xs:attribute type="xs:string" name="ref"/>
    </xs:complexType>
  </xs:element>
</xs:schema>
EOS;
    return $schema;
}


function profiling_get_import_run_schema() {
    $schema = <<<EOS
<xs:schema xmlns:xs="http://www.w3.org/2001/XMLSchema" elementFormDefault="qualified">
  <xs:element name="moodle_profiling_run">
    <xs:complexType>
      <xs:sequence>
        <xs:element type="xs:int" name="id"/>
        <xs:element type="xs:string" name="runid"/>
        <xs:element type="xs:string" name="url"/>
        <xs:element type="xs:int" name="runreference"/>
        <xs:element type="xs:string" name="runcomment"/>
        <xs:element type="xs:int" name="timecreated"/>
        <xs:element type="xs:int" name="totalexecutiontime"/>
        <xs:element type="xs:int" name="totalcputime"/>
        <xs:element type="xs:int" name="totalcalls"/>
        <xs:element type="xs:int" name="totalmemory"/>
        <xs:element type="xs:string" name="data"/>
      </xs:sequence>
    </xs:complexType>
  </xs:element>
</xs:schema>
EOS;
    return $schema;
}

class moodle_xhprofrun implements iXHProfRuns {

    protected $runid = null;
    protected $url = null;
    protected $totalexecutiontime = 0;
    protected $totalcputime = 0;
    protected $totalcalls = 0;
    protected $totalmemory = 0;
    protected $timecreated = 0;

    public function __construct() {
        $this->timecreated = time();
    }

    
    public function get_run($run_id, $type, &$run_desc) {
        global $DB;

        $rec = $DB->get_record('profiling', array('runid' => $run_id), '*', MUST_EXIST);

        $this->runid = $rec->runid;
        $this->url = $rec->url;
        $this->totalexecutiontime = $rec->totalexecutiontime;
        $this->totalcputime = $rec->totalcputime;
        $this->totalcalls = $rec->totalcalls;
        $this->totalmemory = $rec->totalmemory;
        $this->timecreated = $rec->timecreated;

        $run_desc = $this->url . ($rec->runreference ? ' (R) ' : ' ') . ' - ' . s($rec->runcomment);

        return unserialize(base64_decode($rec->data));
    }

    
    public function save_run($xhprof_data, $type, $run_id = null) {
        global $DB;

        if (is_null($this->url)) {
            xhprof_error("Warning: You must use the prepare_run() method before saving it");
        }

                $this->runid = is_null($run_id) ? md5($this->url . '-' . uniqid()) : $run_id;

                $this->totalexecutiontime = $xhprof_data['main()']['wt'];
        $this->totalcputime = $xhprof_data['main()']['cpu'];
        $this->totalcalls = array_reduce($xhprof_data, array($this, 'sum_calls'));
        $this->totalmemory = $xhprof_data['main()']['mu'];

                $rec = new stdClass();
        $rec->runid = $this->runid;
        $rec->url = $this->url;
        $rec->data = base64_encode(serialize($xhprof_data));
        $rec->totalexecutiontime = $this->totalexecutiontime;
        $rec->totalcputime = $this->totalcputime;
        $rec->totalcalls = $this->totalcalls;
        $rec->totalmemory = $this->totalmemory;
        $rec->timecreated = $this->timecreated;

        $DB->insert_record('profiling', $rec);
        return $this->runid;
    }

    public function prepare_run($url) {
        $this->url = $url;
    }

    
    protected function sum_calls($sum, $data) {
        return $sum + $data['ct'];
    }
}


class xhprof_table_sql extends table_sql {

    protected $listurlmode = false;

    
    function get_row_class($row) {
        return $row->runreference ? 'referencerun' : '';     }

    
    function set_listurlmode($listurlmode) {
        $this->listurlmode = $listurlmode;
    }

    
    protected function col_url($row) {
        global $OUTPUT;

                $scripturl = new moodle_url('/admin/tool/profiling/index.php', array('script' => $row->url, 'listurl' => $row->url));
        $scriptaction = $OUTPUT->action_link($scripturl, $row->url);

                if ($this->listurlmode) {
            $detailsaction = '';
        } else {
                        $detailsimg = $OUTPUT->pix_icon('t/right', get_string('profilingfocusscript', 'tool_profiling', $row->url));
            $detailsurl = new moodle_url('/admin/tool/profiling/index.php', array('listurl' => $row->url));
            $detailsaction = $OUTPUT->action_link($detailsurl, $detailsimg);
        }

        return $scriptaction . '&nbsp;' . $detailsaction;
    }

    
    protected function col_timecreated($row) {
        global $OUTPUT;
        $fdate = userdate($row->timecreated, '%d %b %Y, %H:%M');
        $url = new moodle_url('/admin/tool/profiling/index.php', array('runid' => $row->runid, 'listurl' => $row->url));
        return $OUTPUT->action_link($url, $fdate);
    }

    
    protected function col_totalexecutiontime($row) {
        return format_float($row->totalexecutiontime / 1000, 3) . ' ms';
    }

    
    protected function col_totalcputime($row) {
        return format_float($row->totalcputime / 1000, 3) . ' ms';
    }

    
    protected function col_totalmemory($row) {
        return format_float($row->totalmemory / 1024, 3) . ' KB';
    }
}
