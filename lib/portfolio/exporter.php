<?php



defined('MOODLE_INTERNAL') || die();


class portfolio_exporter {

    
    private $caller;

    
    private $instance;

    
    private $noexportconfig;

    
    private $user;

    
    public $instancefile;

    
    public $callercomponent;

    
    private $stage;

    
    private $forcequeue;

    
    private $id;

    
    private $alreadystolen;

    
    private $newfilehashes;

    
    private $format;

    
    private $queued = false;

    
    private $expirytime;

    
    private $deleted = false;

    
    public function __construct(&$instance, &$caller, $callercomponent) {
        $this->instance =& $instance;
        $this->caller =& $caller;
        if ($instance) {
            $this->instancefile = 'portfolio/' . $instance->get('plugin') . '/lib.php';
            $this->instance->set('exporter', $this);
        }
        $this->callercomponent = $callercomponent;
        $this->stage = PORTFOLIO_STAGE_CONFIG;
        $this->caller->set('exporter', $this);
        $this->alreadystolen = array();
        $this->newfilehashes = array();
    }

    
    public function get($field) {
        if ($field == 'format') {
            return portfolio_format_object($this->format);
        } else if ($field == 'formatclass') {
            return $this->format;
        }
        if (property_exists($this, $field)) {
            return $this->{$field};
        }
        $a = (object)array('property' => $field, 'class' => get_class($this));
        throw new portfolio_export_exception($this, 'invalidproperty', 'portfolio', null, $a);
    }

    
    public function set($field, &$value) {
        if (property_exists($this, $field)) {
            $this->{$field} =& $value;
            if ($field == 'instance') {
                $this->instancefile = 'portfolio/' . $this->instance->get('plugin') . '/lib.php';
                $this->instance->set('exporter', $this);
            }
            $this->dirty = true;
            return true;
        }
        $a = (object)array('property' => $field, 'class' => get_class($this));
        throw new portfolio_export_exception($this, 'invalidproperty', 'portfolio', null, $a);

    }

    
    public function set_forcequeue() {
        $this->forcequeue = true;
    }

    
    public function process_stage($stage, $alreadystolen=false) {
        $this->set('stage', $stage);
        if ($alreadystolen) {
            $this->alreadystolen[$stage] = true;
        } else {
            if (!array_key_exists($stage, $this->alreadystolen)) {
                $this->alreadystolen[$stage] = false;
            }
        }
        if (!$this->alreadystolen[$stage] && $url = $this->instance->steal_control($stage)) {
            $this->save();
            redirect($url);         } else {
            $this->save();
        }

        $waiting = $this->instance->get_export_config('wait');
        if ($stage > PORTFOLIO_STAGE_QUEUEORWAIT && empty($waiting)) {
            $stage = PORTFOLIO_STAGE_FINISHED;
        }
        $functionmap = array(
            PORTFOLIO_STAGE_CONFIG        => 'config',
            PORTFOLIO_STAGE_CONFIRM       => 'confirm',
            PORTFOLIO_STAGE_QUEUEORWAIT   => 'queueorwait',
            PORTFOLIO_STAGE_PACKAGE       => 'package',
            PORTFOLIO_STAGE_CLEANUP       => 'cleanup',
            PORTFOLIO_STAGE_SEND          => 'send',
            PORTFOLIO_STAGE_FINISHED      => 'finished'
        );

        $function = 'process_stage_' . $functionmap[$stage];
        try {
            if ($this->$function()) {
                                                                $this->save();
                $stage++;
                return $this->process_stage($stage);
            } else {
                $this->save();
                return false;
            }
        } catch (portfolio_caller_exception $e) {
            portfolio_export_rethrow_exception($this, $e);
        } catch (portfolio_plugin_exception $e) {
            portfolio_export_rethrow_exception($this, $e);
        } catch (portfolio_export_exception $e) {
            throw $e;
        } catch (Exception $e) {
            debugging(get_string('thirdpartyexception', 'portfolio', get_class($e)));
            debugging($e);
            portfolio_export_rethrow_exception($this, $e);
        }
    }

    
    public function instance() {
        return $this->instance;
    }

    
    public function caller() {
        return $this->caller;
    }

    
    public function process_stage_config() {
        global $OUTPUT, $CFG;
        $pluginobj = $callerobj = null;
        if ($this->instance->has_export_config()) {
            $pluginobj = $this->instance;
        }
        if ($this->caller->has_export_config()) {
            $callerobj = $this->caller;
        }
        $formats = portfolio_supported_formats_intersect($this->caller->supported_formats(), $this->instance->supported_formats());
        $expectedtime = $this->instance->expected_time($this->caller->expected_time());
        if (count($formats) == 0) {
                        throw new portfolio_export_exception($this, 'nocommonformats', 'portfolio', null, array('location' => get_class($this->caller), 'formats' => implode(',', $formats)));
        }
                if ($pluginobj || $callerobj || count($formats) > 1 || ($expectedtime != PORTFOLIO_TIME_LOW && $expectedtime != PORTFOLIO_TIME_FORCEQUEUE)) {
            $customdata = array(
                'instance' => $this->instance,
                'id'       => $this->id,
                'plugin' => $pluginobj,
                'caller' => $callerobj,
                'userid' => $this->user->id,
                'formats' => $formats,
                'expectedtime' => $expectedtime,
            );
            require_once($CFG->libdir . '/portfolio/forms.php');
            $mform = new portfolio_export_form('', $customdata);
            if ($mform->is_cancelled()){
                $this->cancel_request();
            } else if ($fromform = $mform->get_data()){
                if (!confirm_sesskey()) {
                    throw new portfolio_export_exception($this, 'confirmsesskeybad');
                }
                $pluginbits = array();
                $callerbits = array();
                foreach ($fromform as $key => $value) {
                    if (strpos($key, 'plugin_') === 0) {
                        $pluginbits[substr($key, 7)]  = $value;
                    } else if (strpos($key, 'caller_') === 0) {
                        $callerbits[substr($key, 7)] = $value;
                    }
                }
                $callerbits['format'] = $pluginbits['format'] = $fromform->format;
                $pluginbits['wait'] = $fromform->wait;
                if ($expectedtime == PORTFOLIO_TIME_LOW) {
                    $pluginbits['wait'] = 1;
                    $pluginbits['hidewait'] = 1;
                } else if ($expectedtime == PORTFOLIO_TIME_FORCEQUEUE) {
                    $pluginbits['wait'] = 0;
                    $pluginbits['hidewait'] = 1;
                    $this->forcequeue = true;
                }
                $callerbits['hideformat'] = $pluginbits['hideformat'] = (count($formats) == 1);
                $this->caller->set_export_config($callerbits);
                $this->instance->set_export_config($pluginbits);
                $this->set('format', $fromform->format);
                return true;
            } else {
                $this->print_header(get_string('configexport', 'portfolio'));
                echo $OUTPUT->box_start();
                $mform->display();
                echo $OUTPUT->box_end();
                echo $OUTPUT->footer();
                return false;
            }
        } else {
            $this->noexportconfig = true;
            $format = array_shift($formats);
            $config = array(
                'hidewait' => 1,
                'wait' => (($expectedtime == PORTFOLIO_TIME_LOW) ? 1 : 0),
                'format' => $format,
                'hideformat' => 1
            );
            $this->set('format', $format);
            $this->instance->set_export_config($config);
            $this->caller->set_export_config(array('format' => $format, 'hideformat' => 1));
            if ($expectedtime == PORTFOLIO_TIME_FORCEQUEUE) {
                $this->forcequeue = true;
            }
            return true;
                    }
    }

    
    public function process_stage_confirm() {
        global $CFG, $DB, $OUTPUT;

        $previous = $DB->get_records(
            'portfolio_log',
            array(
                'userid'      => $this->user->id,
                'portfolio'   => $this->instance->get('id'),
                'caller_sha1' => $this->caller->get_sha1(),
            )
        );
        if (isset($this->noexportconfig) && empty($previous)) {
            return true;
        }
        $strconfirm = get_string('confirmexport', 'portfolio');
        $baseurl = $CFG->wwwroot . '/portfolio/add.php?sesskey=' . sesskey() . '&id=' . $this->get('id');
        $yesurl = $baseurl . '&stage=' . PORTFOLIO_STAGE_QUEUEORWAIT;
        $nourl  = $baseurl . '&cancel=1';
        $this->print_header(get_string('confirmexport', 'portfolio'));
        echo $OUTPUT->box_start();
        echo $OUTPUT->heading(get_string('confirmsummary', 'portfolio'), 3);
        $mainsummary = array();
        if (!$this->instance->get_export_config('hideformat')) {
            $mainsummary[get_string('selectedformat', 'portfolio')] = get_string('format_' . $this->instance->get_export_config('format'), 'portfolio');
        }
        if (!$this->instance->get_export_config('hidewait')) {
            $mainsummary[get_string('selectedwait', 'portfolio')] = get_string(($this->instance->get_export_config('wait') ? 'yes' : 'no'));
        }
        if ($previous) {
            $previousstr = '';
            foreach ($previous as $row) {
                $previousstr .= userdate($row->time);
                if ($row->caller_class != get_class($this->caller)) {
                    if (!empty($row->caller_file)) {
                        portfolio_include_callback_file($row->caller_file);
                    } else if (!empty($row->caller_component)) {
                        portfolio_include_callback_file($row->caller_component);
                    } else {                         continue;
                    }
                    $previousstr .= ' (' . call_user_func(array($row->caller_class, 'display_name')) . ')';
                }
                $previousstr .= '<br />';
            }
            $mainsummary[get_string('exportedpreviously', 'portfolio')] = $previousstr;
        }
        if (!$csummary = $this->caller->get_export_summary()) {
            $csummary = array();
        }
        if (!$isummary = $this->instance->get_export_summary()) {
            $isummary = array();
        }
        $mainsummary = array_merge($mainsummary, $csummary, $isummary);
        $table = new html_table();
        $table->attributes['class'] = 'generaltable exportsummary';
        $table->data = array();
        foreach ($mainsummary as $string => $value) {
            $table->data[] = array($string, $value);
        }
        echo html_writer::table($table);
        echo $OUTPUT->confirm($strconfirm, $yesurl, $nourl);
        echo $OUTPUT->box_end();
        echo $OUTPUT->footer();
        return false;
    }

    
    public function process_stage_queueorwait() {
        global $DB;

        $wait = $this->instance->get_export_config('wait');
        if (empty($wait)) {
            $DB->set_field('portfolio_tempdata', 'queued', 1, array('id' => $this->id));
            $this->queued = true;
            return $this->process_stage_finished(true);
        }
        return true;
    }

    
    public function process_stage_package() {
                                try {
            $this->caller->prepare_package();
        } catch (portfolio_exception $e) {
            throw new portfolio_export_exception($this, 'callercouldnotpackage', 'portfolio', null, $e->getMessage());
        }
        catch (file_exception $e) {
            throw new portfolio_export_exception($this, 'callercouldnotpackage', 'portfolio', null, $e->getMessage());
        }
        try {
            $this->instance->prepare_package();
        }
        catch (portfolio_exception $e) {
            throw new portfolio_export_exception($this, 'plugincouldnotpackage', 'portfolio', null, $e->getMessage());
        }
        catch (file_exception $e) {
            throw new portfolio_export_exception($this, 'plugincouldnotpackage', 'portfolio', null, $e->getMessage());
        }
        return true;
    }

    
    public function process_stage_cleanup($pullok=false) {
        global $CFG, $DB;

        if (!$pullok && $this->get('instance') && !$this->get('instance')->is_push()) {
            return true;
        }
        if ($this->get('instance')) {
                        $this->get('instance')->cleanup();
        }
        $DB->delete_records('portfolio_tempdata', array('id' => $this->id));
        $fs = get_file_storage();
        $fs->delete_area_files(SYSCONTEXTID, 'portfolio', 'exporter', $this->id);
        $this->deleted = true;
        return true;
    }

    
    public function process_stage_send() {
                try {
            $this->instance->send_package();
        }
        catch (portfolio_plugin_exception $e) {
                                    throw new portfolio_export_exception($this, 'failedtosendpackage', 'portfolio', null, $e->getMessage());
        }
                if ($this->get('instance')->is_push()) {
            $this->log_transfer();
        }
        return true;
    }

    
    public function log_transfer() {
        global $DB;
        $l = array(
            'userid' => $this->user->id,
            'portfolio' => $this->instance->get('id'),
            'caller_file'=> '',
            'caller_component' => $this->callercomponent,
            'caller_sha1' => $this->caller->get_sha1(),
            'caller_class' => get_class($this->caller),
            'continueurl' => $this->instance->get_static_continue_url(),
            'returnurl' => $this->caller->get_return_url(),
            'tempdataid' => $this->id,
            'time' => time(),
        );
        $DB->insert_record('portfolio_log', $l);
    }

    
    public function update_log_url($url) {
        global $DB;
        $DB->set_field('portfolio_log', 'continueurl', $url, array('tempdataid' => $this->id));
    }

    
    public function process_stage_finished($queued=false) {
        global $OUTPUT;
        $returnurl = $this->caller->get_return_url();
        $continueurl = $this->instance->get_interactive_continue_url();
        $extras = $this->instance->get_extra_finish_options();

        $key = 'exportcomplete';
        if ($queued || $this->forcequeue) {
            $key = 'exportqueued';
            if ($this->forcequeue) {
                $key = 'exportqueuedforced';
            }
        }
        $this->print_header(get_string($key, 'portfolio'), false);
        self::print_finish_info($returnurl, $continueurl, $extras);
        echo $OUTPUT->footer();
        return false;
    }


    
    public function print_header($headingstr, $summary=true) {
        global $OUTPUT, $PAGE;
        $titlestr = get_string('exporting', 'portfolio');
        $headerstr = get_string('exporting', 'portfolio');

        $PAGE->set_title($titlestr);
        $PAGE->set_heading($headerstr);
        echo $OUTPUT->header();
        echo $OUTPUT->heading($headingstr);

        if (!$summary) {
            return;
        }

        echo $OUTPUT->box_start();
        echo $OUTPUT->box_start();
        echo $this->caller->heading_summary();
        echo $OUTPUT->box_end();
        if ($this->instance) {
            echo $OUTPUT->box_start();
            echo $this->instance->heading_summary();
            echo $OUTPUT->box_end();
        }
        echo $OUTPUT->box_end();
    }

    
    public function cancel_request($logreturn=false) {
        global $CFG;
        if (!isset($this)) {
            return;
        }
        $this->process_stage_cleanup(true);
        if ($logreturn) {
            redirect($CFG->wwwroot . '/user/portfoliologs.php');
        }
        redirect($this->caller->get_return_url());
        exit;
    }

    
    public function save() {
        global $DB;
        if (empty($this->id)) {
            $r = (object)array(
                'data' => base64_encode(serialize($this)),
                'expirytime' => time() + (60*60*24),
                'userid' => $this->user->id,
                'instance' => (empty($this->instance)) ? null : $this->instance->get('id'),
            );
            $this->id = $DB->insert_record('portfolio_tempdata', $r);
            $this->expirytime = $r->expirytime;
            $this->save();         } else {
            if (!$r = $DB->get_record('portfolio_tempdata', array('id' => $this->id))) {
                if (!$this->deleted) {
                                    }
                return;
            }
            $r->data = base64_encode(serialize($this));
            $r->instance = (empty($this->instance)) ? null : $this->instance->get('id');
            $DB->update_record('portfolio_tempdata', $r);
        }
    }

    
    public static function rewaken_object($id) {
        global $DB, $CFG;
        require_once($CFG->libdir . '/filelib.php');
        require_once($CFG->libdir . '/portfolio/exporter.php');
        require_once($CFG->libdir . '/portfolio/caller.php');
        require_once($CFG->libdir . '/portfolio/plugin.php');
        if (!$data = $DB->get_record('portfolio_tempdata', array('id' => $id))) {
                                    if ($log = $DB->get_record('portfolio_log', array('tempdataid' => $id))) {
                self::print_cleaned_export($log);
            }
            throw new portfolio_exception('invalidtempid', 'portfolio');
        }
        $exporter = unserialize(base64_decode($data->data));
        if ($exporter->instancefile) {
            require_once($CFG->dirroot . '/' . $exporter->instancefile);
        }
        if (!empty($exporter->callerfile)) {
            portfolio_include_callback_file($exporter->callerfile);
        } else if (!empty($exporter->callercomponent)) {
            portfolio_include_callback_file($exporter->callercomponent);
        } else {
            return;         }

        $exporter = unserialize(serialize($exporter));
        if (!$exporter->get('id')) {
                                                $exporter->set('id', $id);
            $exporter->save();
        }
        return $exporter;
    }

    
    private function new_file_record_base($name) {
        return (object)array_merge($this->get_base_filearea(), array(
            'filepath' => '/',
            'filename' => $name,
        ));
    }

    
    public function verify_rewaken($readonly=false) {
        global $USER, $CFG;
        if ($this->get('user')->id != $USER->id) {             throw new portfolio_exception('notyours', 'portfolio');
        }
        if (!$readonly && $this->get('instance') && !$this->get('instance')->allows_multiple_exports()) {
            $already = portfolio_existing_exports($this->get('user')->id, $this->get('instance')->get('plugin'));
            $already = array_keys($already);

            if (array_shift($already) != $this->get('id')) {

                $a = (object)array(
                    'plugin'  => $this->get('instance')->get('plugin'),
                    'link'    => $CFG->wwwroot . '/user/portfoliologs.php',
                );
                throw new portfolio_exception('nomultipleexports', 'portfolio', '', $a);
            }
        }
        if (!$this->caller->check_permissions()) {             throw new portfolio_caller_exception('nopermissions', 'portfolio', $this->caller->get_return_url());
        }
    }
    
    public function copy_existing_file($oldfile) {
        if (array_key_exists($oldfile->get_contenthash(), $this->newfilehashes)) {
            return $this->newfilehashes[$oldfile->get_contenthash()];
        }
        $fs = get_file_storage();
        $file_record = $this->new_file_record_base($oldfile->get_filename());
        if ($dir = $this->get('format')->get_file_directory()) {
            $file_record->filepath = '/'. $dir . '/';
        }
        try {
            $newfile = $fs->create_file_from_storedfile($file_record, $oldfile->get_id());
            $this->newfilehashes[$newfile->get_contenthash()] = $newfile;
            return $newfile;
        } catch (file_exception $e) {
            return false;
        }
    }

    
    public function write_new_file($content, $name, $manifest=true) {
        $fs = get_file_storage();
        $file_record = $this->new_file_record_base($name);
        if (empty($manifest) && ($dir = $this->get('format')->get_file_directory())) {
            $file_record->filepath = '/' . $dir . '/';
        }
        return $fs->create_file_from_string($file_record, $content);
    }

    
    public function zip_tempfiles($filename='portfolio-export.zip', $filepath='/final/') {
        $zipper = new zip_packer();

        list ($contextid, $component, $filearea, $itemid) = array_values($this->get_base_filearea());
        if ($newfile = $zipper->archive_to_storage($this->get_tempfiles(), $contextid, $component, $filearea, $itemid, $filepath, $filename, $this->user->id)) {
            return $newfile;
        }
        return false;

    }

    
    public function get_tempfiles($skipfile='portfolio-export.zip') {
        $fs = get_file_storage();
        $files = $fs->get_area_files(SYSCONTEXTID, 'portfolio', 'exporter', $this->id, 'sortorder, itemid, filepath, filename', false);
        if (empty($files)) {
            return array();
        }
        $returnfiles = array();
        foreach ($files as $f) {
            if ($f->get_filename() == $skipfile) {
                continue;
            }
            $returnfiles[$f->get_filepath() . $f->get_filename()] = $f;
        }
        return $returnfiles;
    }

    
    public function get_base_filearea() {
        return array(
            'contextid' => SYSCONTEXTID,
            'component' => 'portfolio',
            'filearea'  => 'exporter',
            'itemid'    => $this->id,
        );
    }

    
    public static function print_expired_export() {
        global $CFG, $OUTPUT, $PAGE;
        $title = get_string('exportexpired', 'portfolio');
        $PAGE->navbar->add(get_string('exportexpired', 'portfolio'));
        $PAGE->set_title($title);
        $PAGE->set_heading($title);
        echo $OUTPUT->header();
        echo $OUTPUT->notification(get_string('exportexpireddesc', 'portfolio'));
        echo $OUTPUT->continue_button($CFG->wwwroot);
        echo $OUTPUT->footer();
        exit;
    }

    
    public static function print_cleaned_export($log, $instance=null) {
        global $CFG, $OUTPUT, $PAGE;
        if (empty($instance) || !$instance instanceof portfolio_plugin_base) {
            $instance = portfolio_instance($log->portfolio);
        }
        $title = get_string('exportalreadyfinished', 'portfolio');
        $PAGE->navbar->add($title);
        $PAGE->set_title($title);
        $PAGE->set_heading($title);
        echo $OUTPUT->header();
        echo $OUTPUT->notification(get_string('exportalreadyfinished', 'portfolio'));
        self::print_finish_info($log->returnurl, $instance->resolve_static_continue_url($log->continueurl));
        echo $OUTPUT->continue_button($CFG->wwwroot);
        echo $OUTPUT->footer();
        exit;
    }

    
    public static function print_finish_info($returnurl, $continueurl, $extras=null) {
        if ($returnurl) {
            echo '<a href="' . $returnurl . '">' . get_string('returntowhereyouwere', 'portfolio') . '</a><br />';
        }
        if ($continueurl) {
            echo '<a href="' . $continueurl . '">' . get_string('continuetoportfolio', 'portfolio') . '</a><br />';
        }
        if (is_array($extras)) {
            foreach ($extras as $link => $string) {
                echo '<a href="' . $link . '">' . $string . '</a><br />';
            }
        }
    }
}
