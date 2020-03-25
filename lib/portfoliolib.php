<?php



defined('MOODLE_INTERNAL') || die();


require_once($CFG->libdir . '/portfolio/constants.php');
require_once($CFG->libdir . '/portfolio/exceptions.php');  require_once($CFG->libdir . '/portfolio/caller.php');




class portfolio_add_button {

    
    private $callbackclass;

    
    private $callbackargs;

    
    private $callbackcomponent;

    
    private $formats;

    
    private $instances;

    
    private $file;

    
    private $intendedmimetype;

    
    public function __construct($options=null) {
        global $SESSION, $CFG;

        if (empty($CFG->enableportfolios)) {
            debugging('Building portfolio add button while portfolios is disabled. This code can be optimised.', DEBUG_DEVELOPER);
        }

        $this->instances = portfolio_instances();
        if (empty($options)) {
            return true;
        }
        $constructoroptions = array('callbackclass', 'callbackargs', 'callbackcomponent');
        foreach ((array)$options as $key => $value) {
            if (!in_array($key, $constructoroptions)) {
                throw new portfolio_button_exception('invalidbuttonproperty', 'portfolio', $key);
            }
        }

        $this->set_callback_options($options['callbackclass'], $options['callbackargs'], $options['callbackcomponent']);
    }

    
    public function set_callback_options($class, array $argarray, $component) {
        global $CFG;

                require_once($CFG->libdir . '/portfolio/caller.php');

                portfolio_include_callback_file($component, $class);

                $test = new $class($argarray);
        unset($test);

        $this->callbackcomponent = $component;
        $this->callbackclass = $class;
        $this->callbackargs = $argarray;
    }

    
    public function set_formats($formats=null) {
        if (is_string($formats)) {
            $formats = array($formats);
        }
        if (empty($formats)) {
            $formats = array();
        }
        if (empty($this->callbackclass)) {
            throw new portfolio_button_exception('noclassbeforeformats', 'portfolio');
        }
        $callerformats = call_user_func(array($this->callbackclass, 'base_supported_formats'));
        $this->formats = portfolio_most_specific_formats($formats, $callerformats);
    }

    
    public function reset_formats() {
        $this->set_formats();
    }


    
    public function set_format_by_file(stored_file $file, $extraformats=null) {
        $this->file = $file;
        $fileformat = portfolio_format_from_mimetype($file->get_mimetype());
        if (is_string($extraformats)) {
            $extraformats = array($extraformats);
        } else if (!is_array($extraformats)) {
            $extraformats = array();
        }
        $this->set_formats(array_merge(array($fileformat), $extraformats));
    }

    
    public function set_format_by_intended_file($extn, $extraformats=null) {
        $mimetype = mimeinfo('type', 'something. ' . $extn);
        $fileformat = portfolio_format_from_mimetype($mimetype);
        $this->intendedmimetype = $fileformat;
        if (is_string($extraformats)) {
            $extraformats = array($extraformats);
        } else if (!is_array($extraformats)) {
            $extraformats = array();
        }
        $this->set_formats(array_merge(array($fileformat), $extraformats));
    }

    
    public function render($format=null, $addstr=null) {
        echo $this->to_html($format, $addstr);
    }

    
    public function to_html($format=null, $addstr=null) {
        global $CFG, $COURSE, $OUTPUT, $USER;
        if (!$this->is_renderable()) {
            return;
        }
        if (empty($this->callbackclass) || empty($this->callbackcomponent)) {
            throw new portfolio_button_exception('mustsetcallbackoptions', 'portfolio');
        }
        if (empty($this->formats)) {
                        $this->set_formats();
        }
        $url = new moodle_url('/portfolio/add.php');
        foreach ($this->callbackargs as $key => $value) {
            if (!empty($value) && !is_string($value) && !is_numeric($value)) {
                $a = new stdClass();
                $a->key = $key;
                $a->value = print_r($value, true);
                debugging(get_string('nonprimative', 'portfolio', $a));
                return;
            }
            $url->param('ca_' . $key, $value);
        }
        $url->param('sesskey', sesskey());
        $url->param('callbackcomponent', $this->callbackcomponent);
        $url->param('callbackclass', $this->callbackclass);
        $url->param('course', (!empty($COURSE)) ? $COURSE->id : 0);
        $url->param('callerformats', implode(',', $this->formats));
        $mimetype = null;
        if ($this->file instanceof stored_file) {
            $mimetype = $this->file->get_mimetype();
        } else if ($this->intendedmimetype) {
            $mimetype = $this->intendedmimetype;
        }
        $selectoutput = '';
        if (count($this->instances) == 1) {
            $tmp = array_values($this->instances);
            $instance = $tmp[0];

            $formats = portfolio_supported_formats_intersect($this->formats, $instance->supported_formats());
            if (count($formats) == 0) {
                                                return;
            }
            if ($error = portfolio_instance_sanity_check($instance)) {
                                                return;
            }
            if (!$instance->allows_multiple_exports() && $already = portfolio_existing_exports($USER->id, $instance->get('plugin'))) {
                                return;
            }
            if ($mimetype&& !$instance->file_mime_check($mimetype)) {
                                                return;
            }
            $url->param('instance', $instance->get('id'));
        }
        else {
            if (!$selectoutput = portfolio_instance_select($this->instances, $this->formats, $this->callbackclass, $mimetype, 'instance', true)) {
                return;
            }
        }
                if ($format == PORTFOLIO_ADD_FAKE_URL) {
            return $url->out(false);
        }

        if (empty($addstr)) {
            $addstr = get_string('addtoportfolio', 'portfolio');
        }
        if (empty($format)) {
            $format = PORTFOLIO_ADD_FULL_FORM;
        }

        $formoutput = '<form method="post" action="' . $CFG->wwwroot . '/portfolio/add.php" id="portfolio-add-button">' . "\n";
        $formoutput .= html_writer::input_hidden_params($url);
        $linkoutput = '';

        switch ($format) {
            case PORTFOLIO_ADD_FULL_FORM:
                $formoutput .= $selectoutput;
                $formoutput .= "\n" . '<input type="submit" value="' . $addstr .'" />';
                $formoutput .= "\n" . '</form>';
            break;
            case PORTFOLIO_ADD_ICON_FORM:
                $formoutput .= $selectoutput;
                $formoutput .= "\n" . '<input class="portfolio-add-icon" type="image" src="' . $OUTPUT->pix_url('t/portfolioadd') . '" alt=' . $addstr .'" />';
                $formoutput .= "\n" . '</form>';
            break;
            case PORTFOLIO_ADD_ICON_LINK:
                $linkoutput = $OUTPUT->action_icon($url, new pix_icon('t/portfolioadd', $addstr, '',
                    array('class' => 'portfolio-add-icon smallicon')));
            break;
            case PORTFOLIO_ADD_TEXT_LINK:
                $linkoutput = html_writer::link($url, $addstr, array('class' => 'portfolio-add-link',
                    'title' => $addstr));
            break;
            default:
                debugging(get_string('invalidaddformat', 'portfolio', $format));
        }
        $output = (in_array($format, array(PORTFOLIO_ADD_FULL_FORM, PORTFOLIO_ADD_ICON_FORM)) ? $formoutput : $linkoutput);
        return $output;
    }

    
    private function is_renderable() {
        global $CFG;
        if (empty($CFG->enableportfolios)) {
            return false;
        }
        if (defined('PORTFOLIO_INTERNAL')) {
                                    return false;
        }
        if (empty($this->instances) || count($this->instances) == 0) {
            return false;
        }
        return true;
    }

    
    public function get_formats() {
        return $this->formats;
    }

    
    public function get_callbackargs() {
        return $this->callbackargs;
    }

    
    public function get_callbackcomponent() {
        return $this->callbackcomponent;
    }

    
    public function get_callbackclass() {
        return $this->callbackclass;
    }
}


function portfolio_instance_select($instances, $callerformats, $callbackclass, $mimetype=null, $selectname='instance', $return=false, $returnarray=false) {
    global $CFG, $USER;

    if (empty($CFG->enableportfolios)) {
        return;
    }

    $insane = portfolio_instance_sanity_check();
    $pinsane = portfolio_plugin_sanity_check();

    $count = 0;
    $selectoutput = "\n" . '<label class="accesshide" for="instanceid">' . get_string('plugin', 'portfolio') . '</label>';
    $selectoutput .= "\n" . '<select id="instanceid" name="' . $selectname . '">' . "\n";
    $existingexports = portfolio_existing_exports_by_plugin($USER->id);
    foreach ($instances as $instance) {
        $formats = portfolio_supported_formats_intersect($callerformats, $instance->supported_formats());
        if (count($formats) == 0) {
                        continue;
        }
        if (array_key_exists($instance->get('id'), $insane)) {
                                    continue;
        } else if (array_key_exists($instance->get('plugin'), $pinsane)) {
                                    continue;
        }
        if (!$instance->allows_multiple_exports() && in_array($instance->get('plugin'), $existingexports)) {
                        continue;
        }
        if ($mimetype && !$instance->file_mime_check($mimetype)) {
                                    continue;
        }
        $count++;
        $selectoutput .= "\n" . '<option value="' . $instance->get('id') . '">' . $instance->get('name') . '</option>' . "\n";
        $options[$instance->get('id')] = $instance->get('name');
    }
    if (empty($count)) {
                        return;
    }
    $selectoutput .= "\n" . "</select>\n";
    if (!empty($returnarray)) {
        return $options;
    }
    if (!empty($return)) {
        return $selectoutput;
    }
    echo $selectoutput;
}


function portfolio_instances($visibleonly=true, $useronly=true) {

    global $DB, $USER;

    $values = array();
    $sql = 'SELECT * FROM {portfolio_instance}';

    if ($visibleonly || $useronly) {
        $values[] = 1;
        $sql .= ' WHERE visible = ?';
    }
    if ($useronly) {
        $sql .= ' AND id NOT IN (
                SELECT instance FROM {portfolio_instance_user}
                WHERE userid = ? AND name = ? AND ' . $DB->sql_compare_text('value') . ' = ?
            )';
        $values = array_merge($values, array($USER->id, 'visible', 0));
    }
    $sql .= ' ORDER BY name';

    $instances = array();
    foreach ($DB->get_records_sql($sql, $values) as $instance) {
        $instances[$instance->id] = portfolio_instance($instance->id, $instance);
    }
    return $instances;
}


function portfolio_has_visible_instances() {
    global $DB;
    return $DB->record_exists('portfolio_instance', array('visible' => 1));
}


function portfolio_supported_formats() {
    return array(
        PORTFOLIO_FORMAT_FILE         => 'portfolio_format_file',
        PORTFOLIO_FORMAT_IMAGE        => 'portfolio_format_image',
        PORTFOLIO_FORMAT_RICHHTML     => 'portfolio_format_richhtml',
        PORTFOLIO_FORMAT_PLAINHTML    => 'portfolio_format_plainhtml',
        PORTFOLIO_FORMAT_TEXT         => 'portfolio_format_text',
        PORTFOLIO_FORMAT_VIDEO        => 'portfolio_format_video',
        PORTFOLIO_FORMAT_PDF          => 'portfolio_format_pdf',
        PORTFOLIO_FORMAT_DOCUMENT     => 'portfolio_format_document',
        PORTFOLIO_FORMAT_SPREADSHEET  => 'portfolio_format_spreadsheet',
        PORTFOLIO_FORMAT_PRESENTATION => 'portfolio_format_presentation',
                 PORTFOLIO_FORMAT_LEAP2A       => 'portfolio_format_leap2a',
        PORTFOLIO_FORMAT_RICH         => 'portfolio_format_rich',
    );
}


function portfolio_format_from_mimetype($mimetype) {
    global $CFG;
    static $alreadymatched;
    if (empty($alreadymatched)) {
        $alreadymatched = array();
    }
    if (array_key_exists($mimetype, $alreadymatched)) {
        return $alreadymatched[$mimetype];
    }
    $allformats = portfolio_supported_formats();
    require_once($CFG->libdir . '/portfolio/formats.php');
    foreach ($allformats as $format => $classname) {
        $supportedmimetypes = call_user_func(array($classname, 'mimetypes'));
        if (!is_array($supportedmimetypes)) {
            debugging("one of the portfolio format classes, $classname, said it supported something funny for mimetypes, should have been array...");
            debugging(print_r($supportedmimetypes, true));
            continue;
        }
        if (in_array($mimetype, $supportedmimetypes)) {
            $alreadymatched[$mimetype] = $format;
            return $format;
        }
    }
    return PORTFOLIO_FORMAT_FILE; }


function portfolio_supported_formats_intersect($callerformats, $pluginformats) {
    global $CFG;
    $allformats = portfolio_supported_formats();
    $intersection = array();
    foreach ($callerformats as $cf) {
        if (!array_key_exists($cf, $allformats)) {
            if (!portfolio_format_is_abstract($cf)) {
                debugging(get_string('invalidformat', 'portfolio', $cf));
            }
            continue;
        }
        require_once($CFG->libdir . '/portfolio/formats.php');
        $cfobj = new $allformats[$cf]();
        foreach ($pluginformats as $p => $pf) {
            if (!array_key_exists($pf, $allformats)) {
                if (!portfolio_format_is_abstract($pf)) {
                    debugging(get_string('invalidformat', 'portfolio', $pf));
                }
                unset($pluginformats[$p]);                 continue;
            }
            if ($cfobj instanceof $allformats[$pf]) {
                $intersection[] = $cf;
            }
        }
    }
    return $intersection;
}


function portfolio_format_is_abstract($format) {
    if (class_exists($format)) {
        $class = $format;
    } else if (class_exists('portfolio_format_' . $format)) {
        $class = 'portfolio_format_' . $format;
    } else {
        $allformats = portfolio_supported_formats();
        if (array_key_exists($format, $allformats)) {
            $class = $allformats[$format];
        }
    }
    if (empty($class)) {
        return true;     }
    $rc = new ReflectionClass($class);
    return $rc->isAbstract();
}


function portfolio_most_specific_formats($specificformats, $generalformats) {
    global $CFG;
    $allformats = portfolio_supported_formats();
    if (empty($specificformats)) {
        return $generalformats;
    } else if (empty($generalformats)) {
        return $specificformats;
    }
    $removedformats = array();
    foreach ($specificformats as $k => $f) {
                if (!array_key_exists($f, $allformats)) {
            if (!portfolio_format_is_abstract($f)) {
                throw new portfolio_button_exception('invalidformat', 'portfolio', $f);
            }
        }
        if (in_array($f, $removedformats)) {
                                    unset($specificformats[$k]);
        }
        require_once($CFG->libdir . '/portfolio/formats.php');
        $fobj = new $allformats[$f];
        foreach ($generalformats as $key => $cf) {
            if (in_array($cf, $removedformats)) {
                                continue;
            }
            $cfclass = $allformats[$cf];
            $cfobj = new $allformats[$cf];
            if ($fobj instanceof $cfclass && $cfclass != get_class($fobj)) {
                                unset($generalformats[$key]);
                $removedformats[] = $cf;
                continue;
            }
                        if ($fobj->conflicts($cf)) {
                                unset($generalformats[$key]);
                $removedformats[] = $cf;
                continue;
            }
            if ($cfobj->conflicts($f)) {
                                $removedformats[] = $cf;
                unset($generalformats[$key]);
                continue;
            }
        }
                    }

        $finalformats =  array_unique(array_merge(array_values($specificformats), array_values($generalformats)));
        return $finalformats;
}


function portfolio_format_object($name) {
    global $CFG;
    require_once($CFG->libdir . '/portfolio/formats.php');
    $formats = portfolio_supported_formats();
    return new $formats[$name];
}


function portfolio_instance($instanceid, $record=null) {
    global $DB, $CFG;

    if ($record) {
        $instance  = $record;
    } else {
        if (!$instance = $DB->get_record('portfolio_instance', array('id' => $instanceid))) {
            throw new portfolio_exception('invalidinstance', 'portfolio');
        }
    }
    require_once($CFG->libdir . '/portfolio/plugin.php');
    require_once($CFG->dirroot . '/portfolio/'. $instance->plugin . '/lib.php');
    $classname = 'portfolio_plugin_' . $instance->plugin;
    return new $classname($instanceid, $instance);
}


function portfolio_static_function($plugin, $function) {
    global $CFG;

    $pname = null;
    if (is_object($plugin) || is_array($plugin)) {
        $plugin = (object)$plugin;
        $pname = $plugin->name;
    } else {
        $pname = $plugin;
    }

    $args = func_get_args();
    if (count($args) <= 2) {
        $args = array();
    }
    else {
        array_shift($args);
        array_shift($args);
    }

    require_once($CFG->libdir . '/portfolio/plugin.php');
    require_once($CFG->dirroot . '/portfolio/' . $plugin .  '/lib.php');
    return call_user_func_array(array('portfolio_plugin_' . $plugin, $function), $args);
}


function portfolio_plugin_sanity_check($plugins=null) {
    global $DB;
    if (is_string($plugins)) {
        $plugins = array($plugins);
    } else if (empty($plugins)) {
        $plugins = core_component::get_plugin_list('portfolio');
        $plugins = array_keys($plugins);
    }

    $insane = array();
    foreach ($plugins as $plugin) {
        if ($result = portfolio_static_function($plugin, 'plugin_sanity_check')) {
            $insane[$plugin] = $result;
        }
    }
    if (empty($insane)) {
        return array();
    }
    list($where, $params) = $DB->get_in_or_equal(array_keys($insane));
    $where = ' plugin ' . $where;
    $DB->set_field_select('portfolio_instance', 'visible', 0, $where, $params);
    return $insane;
}


function portfolio_instance_sanity_check($instances=null) {
    global $DB;
    if (empty($instances)) {
        $instances = portfolio_instances(false);
    } else if (!is_array($instances)) {
        $instances = array($instances);
    }

    $insane = array();
    foreach ($instances as $instance) {
        if (is_object($instance) && !($instance instanceof portfolio_plugin_base)) {
            $instance = portfolio_instance($instance->id, $instance);
        } else if (is_numeric($instance)) {
            $instance = portfolio_instance($instance);
        }
        if (!($instance instanceof portfolio_plugin_base)) {
            debugging('something weird passed to portfolio_instance_sanity_check, not subclass or id');
            continue;
        }
        if ($result = $instance->instance_sanity_check()) {
            $insane[$instance->get('id')] = $result;
        }
    }
    if (empty($insane)) {
        return array();
    }
    list ($where, $params) = $DB->get_in_or_equal(array_keys($insane));
    $where = ' id ' . $where;
    $DB->set_field_select('portfolio_instance', 'visible', 0, $where, $params);
    portfolio_insane_notify_admins($insane, true);
    return $insane;
}


function portfolio_report_insane($insane, $instances=false, $return=false) {
    global $OUTPUT;
    if (empty($insane)) {
        return;
    }

    static $pluginstr;
    if (empty($pluginstr)) {
        $pluginstr = get_string('plugin', 'portfolio');
    }
    if ($instances) {
        $headerstr = get_string('someinstancesdisabled', 'portfolio');
    } else {
        $headerstr = get_string('somepluginsdisabled', 'portfolio');
    }

    $output = $OUTPUT->notification($headerstr, 'notifyproblem');
    $table = new html_table();
    $table->head = array($pluginstr, '');
    $table->data = array();
    foreach ($insane as $plugin => $reason) {
        if ($instances) {
            $instance = $instances[$plugin];
            $plugin   = $instance->get('plugin');
            $name     = $instance->get('name');
        } else {
            $name = $plugin;
        }
        $table->data[] = array($name, get_string($reason, 'portfolio_' . $plugin));
    }
    $output .= html_writer::table($table);
    $output .= '<br /><br /><br />';

    if ($return) {
        return $output;
    }
    echo $output;
}


function portfolio_cron() {
    global $DB, $CFG;

    require_once($CFG->libdir . '/portfolio/exporter.php');
    if ($expired = $DB->get_records_select('portfolio_tempdata', 'expirytime < ?', array(time()), '', 'id')) {
        foreach ($expired as $d) {
            try {
                $e = portfolio_exporter::rewaken_object($d->id);
                $e->process_stage_cleanup(true);
            } catch (Exception $e) {
                mtrace('Exception thrown in portfolio cron while cleaning up ' . $d->id . ': ' . $e->getMessage());
            }
        }
    }

    $process = $DB->get_records('portfolio_tempdata', array('queued' => 1), 'id ASC', 'id');
    foreach ($process as $d) {
        try {
            $exporter = portfolio_exporter::rewaken_object($d->id);
            $exporter->process_stage_package();
            $exporter->process_stage_send();
            $exporter->save();
            $exporter->process_stage_cleanup();
        } catch (Exception $e) {
                        mtrace('Exception thrown in portfolio cron while processing ' . $d->id . ': ' . $e->getMessage());
        }
    }
}


function portfolio_export_rethrow_exception($exporter, $exception) {
    throw new portfolio_export_exception($exporter, $exception->errorcode, $exception->module, $exception->link, $exception->a);
}


function portfolio_expected_time_file($totest) {
    global $CFG;
    if ($totest instanceof stored_file) {
        $totest = array($totest);
    }
    $size = 0;
    foreach ($totest as $file) {
        if (!($file instanceof stored_file)) {
            debugging('something weird passed to portfolio_expected_time_file - not stored_file object');
            debugging(print_r($file, true));
            continue;
        }
        $size += $file->get_filesize();
    }

    $fileinfo = portfolio_filesize_info();

    $moderate = $high = 0; 
    foreach (array('moderate', 'high') as $setting) {
        $settingname = 'portfolio_' . $setting . '_filesize_threshold';
        if (empty($CFG->{$settingname}) || !array_key_exists($CFG->{$settingname}, $fileinfo['options'])) {
            debugging("weird or unset admin value for $settingname, using default instead");
            $$setting = $fileinfo[$setting];
        } else {
            $$setting = $CFG->{$settingname};
        }
    }

    if ($size < $moderate) {
        return PORTFOLIO_TIME_LOW;
    } else if ($size < $high) {
        return PORTFOLIO_TIME_MODERATE;
    }
    return PORTFOLIO_TIME_HIGH;
}



function portfolio_filesize_info() {
    $filesizes = array();
    $sizelist = array(10240, 51200, 102400, 512000, 1048576, 2097152, 5242880, 10485760, 20971520, 52428800);
    foreach ($sizelist as $size) {
        $filesizes[$size] = display_size($size);
    }
    return array(
        'options' => $filesizes,
        'moderate' => 1048576,
        'high'     => 5242880,
    );
}


function portfolio_expected_time_db($recordcount) {
    global $CFG;

    if (empty($CFG->portfolio_moderate_dbsize_threshold)) {
        set_config('portfolio_moderate_dbsize_threshold', 10);
    }
    if (empty($CFG->portfolio_high_dbsize_threshold)) {
        set_config('portfolio_high_dbsize_threshold', 50);
    }
    if ($recordcount < $CFG->portfolio_moderate_dbsize_threshold) {
        return PORTFOLIO_TIME_LOW;
    } else if ($recordcount < $CFG->portfolio_high_dbsize_threshold) {
        return PORTFOLIO_TIME_MODERATE;
    }
    return PORTFOLIO_TIME_HIGH;
}


function portfolio_insane_notify_admins($insane, $instances=false) {

    global $CFG;

    if (defined('ADMIN_EDITING_PORTFOLIO')) {
        return true;
    }

    $admins = get_admins();

    if (empty($admins)) {
        return;
    }
    if ($instances) {
        $instances = portfolio_instances(false, false);
    }

    $site = get_site();

    $a = new StdClass;
    $a->sitename = format_string($site->fullname, true, array('context' => context_course::instance(SITEID)));
    $a->fixurl   = "$CFG->wwwroot/$CFG->admin/settings.php?section=manageportfolios";
    $a->htmllist = portfolio_report_insane($insane, $instances, true);
    $a->textlist = '';

    foreach ($insane as $k => $reason) {
        if ($instances) {
            $a->textlist = $instances[$k]->get('name') . ': ' . $reason . "\n";
        } else {
            $a->textlist = $k . ': ' . $reason . "\n";
        }
    }

    $subject   = get_string('insanesubject', 'portfolio');
    $plainbody = get_string('insanebody', 'portfolio', $a);
    $htmlbody  = get_string('insanebodyhtml', 'portfolio', $a);
    $smallbody = get_string('insanebodysmall', 'portfolio', $a);

    foreach ($admins as $admin) {
        $eventdata = new stdClass();
        $eventdata->modulename = 'portfolio';
        $eventdata->component = 'portfolio';
        $eventdata->name = 'notices';
        $eventdata->userfrom = get_admin();
        $eventdata->userto = $admin;
        $eventdata->subject = $subject;
        $eventdata->fullmessage = $plainbody;
        $eventdata->fullmessageformat = FORMAT_PLAIN;
        $eventdata->fullmessagehtml = $htmlbody;
        $eventdata->smallmessage = $smallbody;
        message_send($eventdata);
    }
}


function portfolio_export_pagesetup($PAGE, $caller) {
        $caller->set_context($PAGE);

    list($extranav, $cm) = $caller->get_navigation();

        require_login($PAGE->course, false, $cm);

    foreach ($extranav as $navitem) {
        $PAGE->navbar->add($navitem['name']);
    }
    $PAGE->navbar->add(get_string('exporting', 'portfolio'));
}


function portfolio_export_type_to_id($type, $userid) {
    global $DB;
    $sql = 'SELECT t.id FROM {portfolio_tempdata} t JOIN {portfolio_instance} i ON t.instance = i.id WHERE t.userid = ? AND i.plugin = ?';
    return $DB->get_field_sql($sql, array($userid, $type));
}


function portfolio_existing_exports($userid, $type=null) {
    global $DB;
    $sql = 'SELECT t.*,t.instance,i.plugin,i.name FROM {portfolio_tempdata} t JOIN {portfolio_instance} i ON t.instance = i.id WHERE t.userid = ? ';
    $values = array($userid);
    if ($type) {
        $sql .= ' AND i.plugin = ?';
        $values[] = $type;
    }
    return $DB->get_records_sql($sql, $values);
}


function portfolio_existing_exports_by_plugin($userid) {
    global $DB;
    $sql = 'SELECT t.id,i.plugin FROM {portfolio_tempdata} t JOIN {portfolio_instance} i ON t.instance = i.id WHERE t.userid = ? ';
    $values = array($userid);
    return $DB->get_records_sql_menu($sql, $values);
}


function portfolio_format_text_options() {

    $options                = new stdClass();
    $options->para          = false;
    $options->newlines      = true;
    $options->filter        = false;
    $options->noclean       = true;
    $options->overflowdiv   = false;

    return $options;
}


function portfolio_rewrite_pluginfile_url_callback($contextid, $component, $filearea, $itemid, $format, $options, $matches) {
    $matches = $matches[0]; 
        $dom = new DomDocument();
    if (!$dom->loadHTML($matches)) {
        return $matches;
    }

        $xpath = new DOMXPath($dom);
    $nodes = $xpath->query('/html/body/child::*');
    if (empty($nodes) || count($nodes) > 1) {
                return $matches;
    }
    $dom = $nodes->item(0);

    $attributes = array();
    foreach ($dom->attributes as $attr => $node) {
        $attributes[$attr] = $node->value;
    }
        $fs = get_file_storage();
    $key = 'href';
    if (!array_key_exists('href', $attributes) && array_key_exists('src', $attributes)) {
        $key = 'src';
    }
    if (!array_key_exists($key, $attributes)) {
        debugging('Couldn\'t find an attribute to use that contains @@PLUGINFILE@@ in portfolio_rewrite_pluginfile');
        return $matches;
    }
    $filename = substr($attributes[$key], strpos($attributes[$key], '@@PLUGINFILE@@') + strlen('@@PLUGINFILE@@'));
    $filepath = '/';
    if (strpos($filename, '/') !== 0) {
        $bits = explode('/', $filename);
        $filename = array_pop($bits);
        $filepath = implode('/', $bits);
    }
    if (!$file = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, urldecode($filename))) {
        debugging("Couldn't find a file from the embedded path info context $contextid component $component filearea $filearea itemid $itemid filepath $filepath name $filename");
        return $matches;
    }
    if (empty($options)) {
        $options = array();
    }
    $options['attributes'] = $attributes;
    return $format->file_output($file, $options);
}


function portfolio_include_callback_file($component, $class = null) {
    global $CFG;
    require_once($CFG->libdir . '/adminlib.php');

            $pos = strrpos($component, '/');
    if ($pos !== false) {
                $component = ltrim($component, '/');
                $plugintypes = core_component::get_plugin_types();
                $isvalid = false;
                foreach ($plugintypes as $type => $path) {
                        $path = preg_replace('|^' . preg_quote($CFG->dirroot, '|') . '/|', '', $path);
            if (strrpos($component, $path) === 0) {
                                $isvalid = true;
                $plugintype = $type;
                $pluginpath = $path;
            }
        }
                if (!$isvalid) {
            throw new coding_exception('Somehow a non-valid plugin path was passed, could be a hackz0r attempt, exiting.');
        }
                $component = trim(substr($component, 0, $pos), '/');
                $component = str_replace($pluginpath, $plugintype, $component);
                $component = str_replace('/', '_', $component);
                debugging('The third parameter sent to the function set_callback_options should be the component name, not a file path, please update this.', DEBUG_DEVELOPER);
    }

        if (!get_component_version($component)) {
        throw new portfolio_button_exception('nocallbackcomponent', 'portfolio', '', $component);
    }

        if (!$componentloc = core_component::get_component_directory($component)) {
        throw new portfolio_button_exception('nocallbackcomponent', 'portfolio', '', $component);
    }

            $filefound = false;
    if (file_exists($componentloc . '/locallib.php')) {
        $filefound = true;
        require_once($componentloc . '/locallib.php');
    }
    if (file_exists($componentloc . '/portfoliolib.php')) {
        $filefound = true;
        debugging('Please standardise your plugin by renaming your portfolio callback file to locallib.php, or if that file already exists moving the portfolio functionality there.', DEBUG_DEVELOPER);
        require_once($componentloc . '/portfoliolib.php');
    }
    if (file_exists($componentloc . '/portfolio_callback.php')) {
        $filefound = true;
        debugging('Please standardise your plugin by renaming your portfolio callback file to locallib.php, or if that file already exists moving the portfolio functionality there.', DEBUG_DEVELOPER);
        require_once($componentloc . '/portfolio_callback.php');
    }

        if (!$filefound) {
        throw new portfolio_button_exception('nocallbackfile', 'portfolio', '', $component);
    }

    if (!is_null($class) && !class_exists($class)) {
        throw new portfolio_button_exception('nocallbackclass', 'portfolio', '', $class);
    }
}


function portfolio_rewrite_pluginfile_urls($text, $contextid, $component, $filearea, $itemid, $format, $options=null) {
    $patterns = array(
        '(<(a|A)[^<]*?href="@@PLUGINFILE@@/[^>]*?>.*?</(a|A)>)',
        '(<(img|IMG)\s[^<]*?src="@@PLUGINFILE@@/[^>]*?/?>)',
    );
    $pattern = '~' . implode('|', $patterns) . '~';
    $callback = partial('portfolio_rewrite_pluginfile_url_callback', $contextid, $component, $filearea, $itemid, $format, $options);
    return preg_replace_callback($pattern, $callback, $text);
}

