<?php



defined('MOODLE_INTERNAL') || die();


require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/DHTMLRulesTableless.php';
require_once 'HTML/QuickForm/Renderer/Tableless.php';
require_once 'HTML/QuickForm/Rule.php';

require_once $CFG->libdir.'/filelib.php';


define('EDITOR_UNLIMITED_FILES', -1);


function pear_handle_error($error){
    echo '<strong>'.$error->GetMessage().'</strong> '.$error->getUserInfo();
    echo '<br /> <strong>Backtrace </strong>:';
    print_object($error->backtrace);
}

if ($CFG->debugdeveloper) {
        $GLOBALS['_PEAR_default_error_mode'] = PEAR_ERROR_CALLBACK;
    $GLOBALS['_PEAR_default_error_options'] = 'pear_handle_error';
}


function form_init_date_js() {
    global $PAGE;
    static $done = false;
    if (!$done) {
        $calendar = \core_calendar\type_factory::get_calendar_instance();
        $module   = 'moodle-form-dateselector';
        $function = 'M.form.dateselector.init_date_selectors';
        $defaulttimezone = date_default_timezone_get();

        $config = array(array(
            'firstdayofweek'    => $calendar->get_starting_weekday(),
            'mon'               => date_format_string(strtotime("Monday"), '%a', $defaulttimezone),
            'tue'               => date_format_string(strtotime("Tuesday"), '%a', $defaulttimezone),
            'wed'               => date_format_string(strtotime("Wednesday"), '%a', $defaulttimezone),
            'thu'               => date_format_string(strtotime("Thursday"), '%a', $defaulttimezone),
            'fri'               => date_format_string(strtotime("Friday"), '%a', $defaulttimezone),
            'sat'               => date_format_string(strtotime("Saturday"), '%a', $defaulttimezone),
            'sun'               => date_format_string(strtotime("Sunday"), '%a', $defaulttimezone),
            'january'           => date_format_string(strtotime("January 1"), '%B', $defaulttimezone),
            'february'          => date_format_string(strtotime("February 1"), '%B', $defaulttimezone),
            'march'             => date_format_string(strtotime("March 1"), '%B', $defaulttimezone),
            'april'             => date_format_string(strtotime("April 1"), '%B', $defaulttimezone),
            'may'               => date_format_string(strtotime("May 1"), '%B', $defaulttimezone),
            'june'              => date_format_string(strtotime("June 1"), '%B', $defaulttimezone),
            'july'              => date_format_string(strtotime("July 1"), '%B', $defaulttimezone),
            'august'            => date_format_string(strtotime("August 1"), '%B', $defaulttimezone),
            'september'         => date_format_string(strtotime("September 1"), '%B', $defaulttimezone),
            'october'           => date_format_string(strtotime("October 1"), '%B', $defaulttimezone),
            'november'          => date_format_string(strtotime("November 1"), '%B', $defaulttimezone),
            'december'          => date_format_string(strtotime("December 1"), '%B', $defaulttimezone)
        ));
        $PAGE->requires->yui_module($module, $function, $config);
        $done = true;
    }
}


abstract class moodleform {
    
    protected $_formname;       
    
    protected $_form;

    
    protected $_customdata;

    
    protected $_ajaxformdata;

    
    protected $_definition_finalized = false;

    
    protected $_validated = null;

    
    public function __construct($action=null, $customdata=null, $method='post', $target='', $attributes=null, $editable=true,
                                $ajaxformdata=null) {
        global $CFG, $FULLME;
                if (empty($attributes)) {
            $attributes = array('autocomplete'=>'off');
        } else if (is_array($attributes)) {
            $attributes['autocomplete'] = 'off';
        } else {
            if (strpos($attributes, 'autocomplete') === false) {
                $attributes .= ' autocomplete="off" ';
            }
        }


        if (empty($action)){
                        $action = strip_querystring($FULLME);
            if (!empty($CFG->sslproxy)) {
                                $action = preg_replace('/^http:/', 'https:', $action, 1);
            }
                                }
                $this->_customdata = $customdata;
        $this->_formname = $this->get_form_identifier();
        $this->_ajaxformdata = $ajaxformdata;

        $this->_form = new MoodleQuickForm($this->_formname, $method, $action, $target, $attributes);
        if (!$editable){
            $this->_form->hardFreeze();
        }

        $this->definition();

        $this->_form->addElement('hidden', 'sesskey', null);         $this->_form->setType('sesskey', PARAM_RAW);
        $this->_form->setDefault('sesskey', sesskey());
        $this->_form->addElement('hidden', '_qf__'.$this->_formname, null);           $this->_form->setType('_qf__'.$this->_formname, PARAM_RAW);
        $this->_form->setDefault('_qf__'.$this->_formname, 1);
        $this->_form->_setDefaultRuleMessages();

                $this->_process_submission($method);
    }

    
    public function moodleform($action=null, $customdata=null, $method='post', $target='', $attributes=null, $editable=true) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($action, $customdata, $method, $target, $attributes, $editable);
    }

    
    protected function get_form_identifier() {
        $class = get_class($this);

        return preg_replace('/[^a-z0-9_]/i', '_', $class);
    }

    
    function focus($name=NULL) {
        $form =& $this->_form;
        $elkeys = array_keys($form->_elementIndex);
        $error = false;
        if (isset($form->_errors) &&  0 != count($form->_errors)){
            $errorkeys = array_keys($form->_errors);
            $elkeys = array_intersect($elkeys, $errorkeys);
            $error = true;
        }

        if ($error or empty($name)) {
            $names = array();
            while (empty($names) and !empty($elkeys)) {
                $el = array_shift($elkeys);
                $names = $form->_getElNamesRecursive($el);
            }
            if (!empty($names)) {
                $name = array_shift($names);
            }
        }

        $focus = '';
        if (!empty($name)) {
            $focus = 'forms[\''.$form->getAttribute('id').'\'].elements[\''.$name.'\']';
        }

        return $focus;
     }

    
    function _process_submission($method) {
        $submission = array();
        if (!empty($this->_ajaxformdata)) {
            $submission = $this->_ajaxformdata;
        } else if ($method == 'post') {
            if (!empty($_POST)) {
                $submission = $_POST;
            }
        } else {
            $submission = $_GET;
            merge_query_params($submission, $_POST);         }

                        if (array_key_exists('_qf__'.$this->_formname, $submission) and $submission['_qf__'.$this->_formname] == 1) {
            if (!confirm_sesskey()) {
                print_error('invalidsesskey');
            }
            $files = $_FILES;
        } else {
            $submission = array();
            $files = array();
        }
        $this->detectMissingSetType();

        $this->_form->updateSubmission($submission, $files);
    }

    
    protected function _get_post_params() {
        return $_POST;
    }

    
    function _validate_files(&$files) {
        global $CFG, $COURSE;

        $files = array();

        if (empty($_FILES)) {
                                    return true;
        }

        $errors = array();
        $filenames = array();

                foreach ($_FILES as $elname=>$file) {
            $required = $this->_form->isElementRequired($elname);

            if ($file['error'] == 4 and $file['size'] == 0) {
                if ($required) {
                    $errors[$elname] = get_string('required');
                }
                unset($_FILES[$elname]);
                continue;
            }

            if (!empty($file['error'])) {
                $errors[$elname] = file_get_upload_error($file['error']);
                unset($_FILES[$elname]);
                continue;
            }

            if (!is_uploaded_file($file['tmp_name'])) {
                                $errors[$elname] = get_string('error');
                unset($_FILES[$elname]);
                continue;
            }

            if (!$this->_form->elementExists($elname) or !$this->_form->getElementType($elname)=='file') {
                                unset($_FILES[$elname]);
                continue;
            }

            
            $filename = clean_param($_FILES[$elname]['name'], PARAM_FILE);
            if ($filename === '') {
                                $errors[$elname] = get_string('error');
                unset($_FILES[$elname]);
                continue;
            }
            if (in_array($filename, $filenames)) {
                                $errors[$elname] = get_string('error');
                unset($_FILES[$elname]);
                continue;
            }
            $filenames[] = $filename;
            $_FILES[$elname]['name'] = $filename;

            $files[$elname] = $_FILES[$elname]['tmp_name'];
        }

                if (count($errors) == 0){
            return true;

        } else {
            $files = array();
            return $errors;
        }
    }

    
    protected function validate_draft_files() {
        global $USER;
        $mform =& $this->_form;

        $errors = array();
                        foreach ($mform->_rules as $elementname => $rules) {
            $elementtype = $mform->getElementType($elementname);
                        if (($elementtype == 'filepicker') || ($elementtype == 'filemanager')){
                                foreach ($rules as $rule) {
                    if ($rule['type'] == 'required') {
                        $draftid = (int)$mform->getSubmitValue($elementname);
                        $fs = get_file_storage();
                        $context = context_user::instance($USER->id);
                        if (!$files = $fs->get_area_files($context->id, 'user', 'draft', $draftid, 'id DESC', false)) {
                            $errors[$elementname] = $rule['message'];
                        }
                    }
                }
            }
        }
                        foreach ($mform->_elements as $element) {
            if ($element->_type == 'filemanager') {
                $maxfiles = $element->getMaxfiles();
                if ($maxfiles > 0) {
                    $draftid = (int)$element->getValue();
                    $fs = get_file_storage();
                    $context = context_user::instance($USER->id);
                    $files = $fs->get_area_files($context->id, 'user', 'draft', $draftid, '', false);
                    if (count($files) > $maxfiles) {
                        $errors[$element->getName()] = get_string('err_maxfiles', 'form', $maxfiles);
                    }
                }
            }
        }
        if (empty($errors)) {
            return true;
        } else {
            return $errors;
        }
    }

    
    function set_data($default_values) {
        if (is_object($default_values)) {
            $default_values = (array)$default_values;
        }
        $this->_form->setDefaults($default_values);
    }

    
    function is_submitted() {
        return $this->_form->isSubmitted();
    }

    
    function no_submit_button_pressed(){
        static $nosubmit = null;         if (!is_null($nosubmit)){
            return $nosubmit;
        }
        $mform =& $this->_form;
        $nosubmit = false;
        if (!$this->is_submitted()){
            return false;
        }
        foreach ($mform->_noSubmitButtons as $nosubmitbutton){
            if (optional_param($nosubmitbutton, 0, PARAM_RAW)){
                $nosubmit = true;
                break;
            }
        }
        return $nosubmit;
    }


    
    function is_validated() {
                if (!$this->_definition_finalized) {
            $this->_definition_finalized = true;
            $this->definition_after_data();
        }

        return $this->validate_defined_fields();
    }

    
    function validate_defined_fields($validateonnosubmit=false) {
        $mform =& $this->_form;
        if ($this->no_submit_button_pressed() && empty($validateonnosubmit)){
            return false;
        } elseif ($this->_validated === null) {
            $internal_val = $mform->validate();

            $files = array();
            $file_val = $this->_validate_files($files);
                                    $draftfilevalue = $this->validate_draft_files();

            if ($file_val !== true && $draftfilevalue !== true) {
                $file_val = array_merge($file_val, $draftfilevalue);
            } else if ($draftfilevalue !== true) {
                $file_val = $draftfilevalue;
            } 
            if ($file_val !== true) {
                if (!empty($file_val)) {
                    foreach ($file_val as $element=>$msg) {
                        $mform->setElementError($element, $msg);
                    }
                }
                $file_val = false;
            }

            $data = $mform->exportValues();
            $moodle_val = $this->validation($data, $files);
            if ((is_array($moodle_val) && count($moodle_val)!==0)) {
                                foreach ($moodle_val as $element=>$msg) {
                    $mform->setElementError($element, $msg);
                }
                $moodle_val = false;

            } else {
                                $moodle_val = true;
            }

            $this->_validated = ($internal_val and $moodle_val and $file_val);
        }
        return $this->_validated;
    }

    
    function is_cancelled(){
        $mform =& $this->_form;
        if ($mform->isSubmitted()){
            foreach ($mform->_cancelButtons as $cancelbutton){
                if (optional_param($cancelbutton, 0, PARAM_RAW)){
                    return true;
                }
            }
        }
        return false;
    }

    
    function get_data() {
        $mform =& $this->_form;

        if (!$this->is_cancelled() and $this->is_submitted() and $this->is_validated()) {
            $data = $mform->exportValues();
            unset($data['sesskey']);             unset($data['_qf__'.$this->_formname]);               if (empty($data)) {
                return NULL;
            } else {
                return (object)$data;
            }
        } else {
            return NULL;
        }
    }

    
    function get_submitted_data() {
        $mform =& $this->_form;

        if ($this->is_submitted()) {
            $data = $mform->exportValues();
            unset($data['sesskey']);             unset($data['_qf__'.$this->_formname]);               if (empty($data)) {
                return NULL;
            } else {
                return (object)$data;
            }
        } else {
            return NULL;
        }
    }

    
    function save_files($destination) {
        debugging('Not used anymore, please fix code! Use save_stored_file() or save_file() instead');
        return false;
    }

    
    function get_new_filename($elname=null) {
        global $USER;

        if (!$this->is_submitted() or !$this->is_validated()) {
            return false;
        }

        if (is_null($elname)) {
            if (empty($_FILES)) {
                return false;
            }
            reset($_FILES);
            $elname = key($_FILES);
        }

        if (empty($elname)) {
            return false;
        }

        $element = $this->_form->getElement($elname);

        if ($element instanceof MoodleQuickForm_filepicker || $element instanceof MoodleQuickForm_filemanager) {
            $values = $this->_form->exportValues($elname);
            if (empty($values[$elname])) {
                return false;
            }
            $draftid = $values[$elname];
            $fs = get_file_storage();
            $context = context_user::instance($USER->id);
            if (!$files = $fs->get_area_files($context->id, 'user', 'draft', $draftid, 'id DESC', false)) {
                return false;
            }
            $file = reset($files);
            return $file->get_filename();
        }

        if (!isset($_FILES[$elname])) {
            return false;
        }

        return $_FILES[$elname]['name'];
    }

    
    function save_file($elname, $pathname, $override=false) {
        global $USER;

        if (!$this->is_submitted() or !$this->is_validated()) {
            return false;
        }
        if (file_exists($pathname)) {
            if ($override) {
                if (!@unlink($pathname)) {
                    return false;
                }
            } else {
                return false;
            }
        }

        $element = $this->_form->getElement($elname);

        if ($element instanceof MoodleQuickForm_filepicker || $element instanceof MoodleQuickForm_filemanager) {
            $values = $this->_form->exportValues($elname);
            if (empty($values[$elname])) {
                return false;
            }
            $draftid = $values[$elname];
            $fs = get_file_storage();
            $context = context_user::instance($USER->id);
            if (!$files = $fs->get_area_files($context->id, 'user', 'draft', $draftid, 'id DESC', false)) {
                return false;
            }
            $file = reset($files);

            return $file->copy_content_to($pathname);

        } else if (isset($_FILES[$elname])) {
            return copy($_FILES[$elname]['tmp_name'], $pathname);
        }

        return false;
    }

    
    function save_temp_file($elname) {
        if (!$this->get_new_filename($elname)) {
            return false;
        }
        if (!$dir = make_temp_directory('forms')) {
            return false;
        }
        if (!$tempfile = tempnam($dir, 'tempup_')) {
            return false;
        }
        if (!$this->save_file($elname, $tempfile, true)) {
                        @unlink($tempfile);
            return false;
        }

        return $tempfile;
    }

    
    protected function get_draft_files($elname) {
        global $USER;

        if (!$this->is_submitted()) {
            return false;
        }

        $element = $this->_form->getElement($elname);

        if ($element instanceof MoodleQuickForm_filepicker || $element instanceof MoodleQuickForm_filemanager) {
            $values = $this->_form->exportValues($elname);
            if (empty($values[$elname])) {
                return false;
            }
            $draftid = $values[$elname];
            $fs = get_file_storage();
            $context = context_user::instance($USER->id);
            if (!$files = $fs->get_area_files($context->id, 'user', 'draft', $draftid, 'id DESC', false)) {
                return null;
            }
            return $files;
        }
        return null;
    }

    
    function save_stored_file($elname, $newcontextid, $newcomponent, $newfilearea, $newitemid, $newfilepath='/',
                              $newfilename=null, $overwrite=false, $newuserid=null) {
        global $USER;

        if (!$this->is_submitted() or !$this->is_validated()) {
            return false;
        }

        if (empty($newuserid)) {
            $newuserid = $USER->id;
        }

        $element = $this->_form->getElement($elname);
        $fs = get_file_storage();

        if ($element instanceof MoodleQuickForm_filepicker) {
            $values = $this->_form->exportValues($elname);
            if (empty($values[$elname])) {
                return false;
            }
            $draftid = $values[$elname];
            $context = context_user::instance($USER->id);
            if (!$files = $fs->get_area_files($context->id, 'user' ,'draft', $draftid, 'id DESC', false)) {
                return false;
            }
            $file = reset($files);
            if (is_null($newfilename)) {
                $newfilename = $file->get_filename();
            }

            if ($overwrite) {
                if ($oldfile = $fs->get_file($newcontextid, $newcomponent, $newfilearea, $newitemid, $newfilepath, $newfilename)) {
                    if (!$oldfile->delete()) {
                        return false;
                    }
                }
            }

            $file_record = array('contextid'=>$newcontextid, 'component'=>$newcomponent, 'filearea'=>$newfilearea, 'itemid'=>$newitemid,
                                 'filepath'=>$newfilepath, 'filename'=>$newfilename, 'userid'=>$newuserid);
            return $fs->create_file_from_storedfile($file_record, $file);

        } else if (isset($_FILES[$elname])) {
            $filename = is_null($newfilename) ? $_FILES[$elname]['name'] : $newfilename;

            if ($overwrite) {
                if ($oldfile = $fs->get_file($newcontextid, $newcomponent, $newfilearea, $newitemid, $newfilepath, $newfilename)) {
                    if (!$oldfile->delete()) {
                        return false;
                    }
                }
            }

            $file_record = array('contextid'=>$newcontextid, 'component'=>$newcomponent, 'filearea'=>$newfilearea, 'itemid'=>$newitemid,
                                 'filepath'=>$newfilepath, 'filename'=>$newfilename, 'userid'=>$newuserid);
            return $fs->create_file_from_pathname($file_record, $_FILES[$elname]['tmp_name']);
        }

        return false;
    }

    
    function get_file_content($elname) {
        global $USER;

        if (!$this->is_submitted() or !$this->is_validated()) {
            return false;
        }

        $element = $this->_form->getElement($elname);

        if ($element instanceof MoodleQuickForm_filepicker || $element instanceof MoodleQuickForm_filemanager) {
            $values = $this->_form->exportValues($elname);
            if (empty($values[$elname])) {
                return false;
            }
            $draftid = $values[$elname];
            $fs = get_file_storage();
            $context = context_user::instance($USER->id);
            if (!$files = $fs->get_area_files($context->id, 'user', 'draft', $draftid, 'id DESC', false)) {
                return false;
            }
            $file = reset($files);

            return $file->get_content();

        } else if (isset($_FILES[$elname])) {
            return file_get_contents($_FILES[$elname]['tmp_name']);
        }

        return false;
    }

    
    function display() {
                if (!$this->_definition_finalized) {
            $this->_definition_finalized = true;
            $this->definition_after_data();
        }

        $this->_form->display();
    }

    
    public function render() {
        ob_start();
        $this->display();
        $out = ob_get_contents();
        ob_end_clean();
        return $out;
    }

    
    protected abstract function definition();

    
    function definition_after_data(){
    }

    
    function validation($data, $files) {
        return array();
    }

    
    function repeat_elements_fix_clone($i, $elementclone, &$namecloned) {
        $name = $elementclone->getName();
        $namecloned[] = $name;

        if (!empty($name)) {
            $elementclone->setName($name."[$i]");
        }

        if (is_a($elementclone, 'HTML_QuickForm_header')) {
            $value = $elementclone->_text;
            $elementclone->setValue(str_replace('{no}', ($i+1), $value));

        } else if (is_a($elementclone, 'HTML_QuickForm_submit') || is_a($elementclone, 'HTML_QuickForm_button')) {
            $elementclone->setValue(str_replace('{no}', ($i+1), $elementclone->getValue()));

        } else {
            $value=$elementclone->getLabel();
            $elementclone->setLabel(str_replace('{no}', ($i+1), $value));
        }
    }

    
    function repeat_elements($elementobjs, $repeats, $options, $repeathiddenname,
            $addfieldsname, $addfieldsno=5, $addstring=null, $addbuttoninside=false){
        if ($addstring===null){
            $addstring = get_string('addfields', 'form', $addfieldsno);
        } else {
            $addstring = str_ireplace('{no}', $addfieldsno, $addstring);
        }
        $repeats = optional_param($repeathiddenname, $repeats, PARAM_INT);
        $addfields = optional_param($addfieldsname, '', PARAM_TEXT);
        if (!empty($addfields)){
            $repeats += $addfieldsno;
        }
        $mform =& $this->_form;
        $mform->registerNoSubmitButton($addfieldsname);
        $mform->addElement('hidden', $repeathiddenname, $repeats);
        $mform->setType($repeathiddenname, PARAM_INT);
                $mform->setConstants(array($repeathiddenname=>$repeats));
        $namecloned = array();
        for ($i = 0; $i < $repeats; $i++) {
            foreach ($elementobjs as $elementobj){
                $elementclone = fullclone($elementobj);
                $this->repeat_elements_fix_clone($i, $elementclone, $namecloned);

                if ($elementclone instanceof HTML_QuickForm_group && !$elementclone->_appendName) {
                    foreach ($elementclone->getElements() as $el) {
                        $this->repeat_elements_fix_clone($i, $el, $namecloned);
                    }
                    $elementclone->setLabel(str_replace('{no}', $i + 1, $elementclone->getLabel()));
                }

                $mform->addElement($elementclone);
            }
        }
        for ($i=0; $i<$repeats; $i++) {
            foreach ($options as $elementname => $elementoptions){
                $pos=strpos($elementname, '[');
                if ($pos!==FALSE){
                    $realelementname = substr($elementname, 0, $pos)."[$i]";
                    $realelementname .= substr($elementname, $pos);
                }else {
                    $realelementname = $elementname."[$i]";
                }
                foreach ($elementoptions as  $option => $params){

                    switch ($option){
                        case 'default' :
                            $mform->setDefault($realelementname, str_replace('{no}', $i + 1, $params));
                            break;
                        case 'helpbutton' :
                            $params = array_merge(array($realelementname), $params);
                            call_user_func_array(array(&$mform, 'addHelpButton'), $params);
                            break;
                        case 'disabledif' :
                            foreach ($namecloned as $num => $name){
                                if ($params[0] == $name){
                                    $params[0] = $params[0]."[$i]";
                                    break;
                                }
                            }
                            $params = array_merge(array($realelementname), $params);
                            call_user_func_array(array(&$mform, 'disabledIf'), $params);
                            break;
                        case 'rule' :
                            if (is_string($params)){
                                $params = array(null, $params, null, 'client');
                            }
                            $params = array_merge(array($realelementname), $params);
                            call_user_func_array(array(&$mform, 'addRule'), $params);
                            break;

                        case 'type':
                            $mform->setType($realelementname, $params);
                            break;

                        case 'expanded':
                            $mform->setExpanded($realelementname, $params);
                            break;

                        case 'advanced' :
                            $mform->setAdvanced($realelementname, $params);
                            break;
                    }
                }
            }
        }
        $mform->addElement('submit', $addfieldsname, $addstring);

        if (!$addbuttoninside) {
            $mform->closeHeaderBefore($addfieldsname);
        }

        return $repeats;
    }

    
    function add_checkbox_controller($groupid, $text = null, $attributes = null, $originalValue = 0) {
        global $CFG, $PAGE;

                $checkboxcontrollername = 'nosubmit_checkbox_controller' . $groupid;
        $checkboxcontrollerparam = 'checkbox_controller'. $groupid;
        $checkboxgroupclass = 'checkboxgroup'.$groupid;

                if (empty($text)) {
            $text = get_string('selectallornone', 'form');
        }

        $mform = $this->_form;
        $selectvalue = optional_param($checkboxcontrollerparam, null, PARAM_INT);
        $contollerbutton = optional_param($checkboxcontrollername, null, PARAM_ALPHAEXT);

        $newselectvalue = $selectvalue;
        if (is_null($selectvalue)) {
            $newselectvalue = $originalValue;
        } else if (!is_null($contollerbutton)) {
            $newselectvalue = (int) !$selectvalue;
        }
                if (!is_null($contollerbutton) || is_null($selectvalue)) {
            foreach ($mform->_elements as $element) {
                if (($element instanceof MoodleQuickForm_advcheckbox) &&
                        $element->getAttribute('class') == $checkboxgroupclass &&
                        !$element->isFrozen()) {
                    $mform->setConstants(array($element->getName() => $newselectvalue));
                }
            }
        }

        $mform->addElement('hidden', $checkboxcontrollerparam, $newselectvalue, array('id' => "id_".$checkboxcontrollerparam));
        $mform->setType($checkboxcontrollerparam, PARAM_INT);
        $mform->setConstants(array($checkboxcontrollerparam => $newselectvalue));

        $PAGE->requires->yui_module('moodle-form-checkboxcontroller', 'M.form.checkboxcontroller',
                array(
                    array('groupid' => $groupid,
                        'checkboxclass' => $checkboxgroupclass,
                        'checkboxcontroller' => $checkboxcontrollerparam,
                        'controllerbutton' => $checkboxcontrollername)
                    )
                );

        require_once("$CFG->libdir/form/submit.php");
        $submitlink = new MoodleQuickForm_submit($checkboxcontrollername, $attributes);
        $mform->addElement($submitlink);
        $mform->registerNoSubmitButton($checkboxcontrollername);
        $mform->setDefault($checkboxcontrollername, $text);
    }

    
    function add_action_buttons($cancel = true, $submitlabel=null){
        if (is_null($submitlabel)){
            $submitlabel = get_string('savechanges');
        }
        $mform =& $this->_form;
        if ($cancel){
                        $buttonarray=array();
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton', $submitlabel);
            $buttonarray[] = &$mform->createElement('cancel');
            $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
            $mform->closeHeaderBefore('buttonar');
        } else {
                        $mform->addElement('submit', 'submitbutton', $submitlabel);
            $mform->closeHeaderBefore('submitbutton');
        }
    }

    
    function init_javascript_enhancement($element, $enhancement, array $options=array(), array $strings=null) {
        global $PAGE;
        if (is_string($element)) {
            $element = $this->_form->getElement($element);
        }
        if (is_object($element)) {
            $element->_generateId();
            $elementid = $element->getAttribute('id');
            $PAGE->requires->js_init_call('M.form.init_'.$enhancement, array($elementid, $options));
            if (is_array($strings)) {
                foreach ($strings as $string) {
                    if (is_array($string)) {
                        call_user_func_array(array($PAGE->requires, 'string_for_js'), $string);
                    } else {
                        $PAGE->requires->string_for_js($string, 'moodle');
                    }
                }
            }
        }
    }

    
    public static function get_js_module() {
        global $CFG;
        return array(
            'name' => 'mform',
            'fullpath' => '/lib/form/form.js',
            'requires' => array('base', 'node')
        );
    }

    
    private function detectMissingSetType() {
        global $CFG;

        if (!$CFG->debugdeveloper) {
                        return;
        }

        $mform = $this->_form;
        foreach ($mform->_elements as $element) {
            $group = false;
            $elements = array($element);

            if ($element->getType() == 'group') {
                $group = $element;
                $elements = $element->getElements();
            }

            foreach ($elements as $index => $element) {
                switch ($element->getType()) {
                    case 'hidden':
                    case 'text':
                    case 'url':
                        if ($group) {
                            $name = $group->getElementName($index);
                        } else {
                            $name = $element->getName();
                        }
                        $key = $name;
                        $found = array_key_exists($key, $mform->_types);
                                                                                                                                                while (!$found && strrpos($key, '[') !== false) {
                            $pos = strrpos($key, '[');
                            $key = substr($key, 0, $pos);
                            $found = array_key_exists($key, $mform->_types);
                        }
                        if (!$found) {
                            debugging("Did you remember to call setType() for '$name'? ".
                                'Defaulting to PARAM_RAW cleaning.', DEBUG_DEVELOPER);
                        }
                        break;
                }
            }
        }
    }

    
    public static function mock_submit($simulatedsubmitteddata, $simulatedsubmittedfiles = array(), $method = 'post',
                                       $formidentifier = null) {
        $_FILES = $simulatedsubmittedfiles;
        if ($formidentifier === null) {
            $formidentifier = get_called_class();
            $formidentifier = str_replace('\\', '_', $formidentifier);         }
        $simulatedsubmitteddata['_qf__'.$formidentifier] = 1;
        $simulatedsubmitteddata['sesskey'] = sesskey();
        if (strtolower($method) === 'get') {
            $_GET = $simulatedsubmitteddata;
        } else {
            $_POST = $simulatedsubmitteddata;
        }
    }
}


class MoodleQuickForm extends HTML_QuickForm_DHTMLRulesTableless {
    
    var $_types = array();

    
    var $_dependencies = array();

    
    var $_noSubmitButtons=array();

    
    var $_cancelButtons=array();

    
    var $_advancedElements = array();

    
    var $_collapsibleElements = array();

    
    var $_disableShortforms = false;

    
    protected $_use_form_change_checker = true;

    
    var $_formName = '';

    
    var $_pageparams = '';

    
    protected $clientvalidation = false;

    
    public function __construct($formName, $method, $action, $target='', $attributes=null) {
        global $CFG, $OUTPUT;

        static $formcounter = 1;

                HTML_Common::__construct($attributes);
        $target = empty($target) ? array() : array('target' => $target);
        $this->_formName = $formName;
        if (is_a($action, 'moodle_url')){
            $this->_pageparams = html_writer::input_hidden_params($action);
            $action = $action->out_omit_querystring();
        } else {
            $this->_pageparams = '';
        }
                $attributes = array('action' => $action, 'method' => $method, 'accept-charset' => 'utf-8') + $target;
        if (is_null($this->getAttribute('id'))) {
            $attributes['id'] = 'mform' . $formcounter;
        }
        $formcounter++;
        $this->updateAttributes($attributes);

                $oldclass=   $this->getAttribute('class');
        if (!empty($oldclass)){
            $this->updateAttributes(array('class'=>$oldclass.' mform'));
        }else {
            $this->updateAttributes(array('class'=>'mform'));
        }
        $this->_reqHTML = '<img class="req" title="'.get_string('requiredelement', 'form').'" alt="'.get_string('requiredelement', 'form').'" src="'.$OUTPUT->pix_url('req') .'" />';
        $this->_advancedHTML = '<img class="adv" title="'.get_string('advancedelement', 'form').'" alt="'.get_string('advancedelement', 'form').'" src="'.$OUTPUT->pix_url('adv') .'" />';
        $this->setRequiredNote(get_string('somefieldsrequired', 'form', '<img alt="'.get_string('requiredelement', 'form').'" src="'.$OUTPUT->pix_url('req') .'" />'));
    }

    
    public function MoodleQuickForm($formName, $method, $action, $target='', $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($formName, $method, $action, $target, $attributes);
    }

    
    function setAdvanced($elementName, $advanced = true) {
        if ($advanced){
            $this->_advancedElements[$elementName]='';
        } elseif (isset($this->_advancedElements[$elementName])) {
            unset($this->_advancedElements[$elementName]);
        }
    }

    
    function setExpanded($headername, $expanded = true, $ignoreuserstate = false) {
        if (empty($headername)) {
            return;
        }
        $element = $this->getElement($headername);
        if ($element->getType() != 'header') {
            debugging('Cannot use setExpanded on non-header elements', DEBUG_DEVELOPER);
            return;
        }
        if (!$headerid = $element->getAttribute('id')) {
            $element->_generateId();
            $headerid = $element->getAttribute('id');
        }
        if ($this->getElementType('mform_isexpanded_' . $headerid) === false) {
                        $formexpanded = optional_param('mform_isexpanded_' . $headerid, -1, PARAM_INT);
            if (!$ignoreuserstate && $formexpanded != -1) {
                                $expanded = $formexpanded;
            }
                        $this->addElement('hidden', 'mform_isexpanded_' . $headerid);
            $this->setType('mform_isexpanded_' . $headerid, PARAM_INT);
            $this->setConstant('mform_isexpanded_' . $headerid, (int) $expanded);
        }
        $this->_collapsibleElements[$headername] = !$expanded;
    }

    
    function addAdvancedStatusElement($headerid, $showmore=false){
                if ($this->getElementType('mform_showmore_' . $headerid) === false) {
                        $formshowmore = optional_param('mform_showmore_' . $headerid, -1, PARAM_INT);
            if (!$showmore && $formshowmore != -1) {
                                $showmore = $formshowmore;
            }
                        $this->addElement('hidden', 'mform_showmore_' . $headerid);
            $this->setType('mform_showmore_' . $headerid, PARAM_INT);
            $this->setConstant('mform_showmore_' . $headerid, (int)$showmore);
        }
    }

    
    function setShowAdvanced($showadvancedNow = null){
        debugging('Call to deprecated function setShowAdvanced. See "Show more.../Show less..." in shortforms yui module.');
    }

    
    function getShowAdvanced(){
        debugging('Call to deprecated function setShowAdvanced. See "Show more.../Show less..." in shortforms yui module.');
        return false;
    }

    
    function setDisableShortforms ($disable = true) {
        $this->_disableShortforms = $disable;
    }

    
    public function disable_form_change_checker() {
        $this->_use_form_change_checker = false;
    }

    
    public function enable_form_change_checker() {
        $this->_use_form_change_checker = true;
    }

    
    public function is_form_change_checker_enabled() {
        return $this->_use_form_change_checker;
    }

    
    function accept(&$renderer) {
        if (method_exists($renderer, 'setAdvancedElements')){
                                                $stopFields = $renderer->getStopFieldSetElements();
            $lastHeader = null;
            $lastHeaderAdvanced = false;
            $anyAdvanced = false;
            $anyError = false;
            foreach (array_keys($this->_elements) as $elementIndex){
                $element =& $this->_elements[$elementIndex];

                                if ($element->getType()=='header' || in_array($element->getName(), $stopFields)){
                    if ($anyAdvanced && !is_null($lastHeader)) {
                        $lastHeader->_generateId();
                        $this->setAdvanced($lastHeader->getName());
                        $this->addAdvancedStatusElement($lastHeader->getAttribute('id'), $anyError);
                    }
                    $lastHeaderAdvanced = false;
                    unset($lastHeader);
                    $lastHeader = null;
                } elseif ($lastHeaderAdvanced) {
                    $this->setAdvanced($element->getName());
                }

                if ($element->getType()=='header'){
                    $lastHeader =& $element;
                    $anyAdvanced = false;
                    $anyError = false;
                    $lastHeaderAdvanced = isset($this->_advancedElements[$element->getName()]);
                } elseif (isset($this->_advancedElements[$element->getName()])){
                    $anyAdvanced = true;
                    if (isset($this->_errors[$element->getName()])) {
                        $anyError = true;
                    }
                }
            }
                        if ($anyAdvanced && !is_null($lastHeader)){
                $this->setAdvanced($lastHeader->getName());
                $lastHeader->_generateId();
                $this->addAdvancedStatusElement($lastHeader->getAttribute('id'), $anyError);
            }
            $renderer->setAdvancedElements($this->_advancedElements);
        }
        if (method_exists($renderer, 'setCollapsibleElements') && !$this->_disableShortforms) {

                        $headerscount = 0;
            foreach (array_keys($this->_elements) as $elementIndex){
                $element =& $this->_elements[$elementIndex];
                if ($element->getType() == 'header') {
                    $headerscount++;
                }
            }

            $anyrequiredorerror = false;
            $headercounter = 0;
            $headername = null;
            foreach (array_keys($this->_elements) as $elementIndex){
                $element =& $this->_elements[$elementIndex];

                if ($element->getType() == 'header') {
                    $headercounter++;
                    $element->_generateId();
                    $headername = $element->getName();
                    $anyrequiredorerror = false;
                } else if (in_array($element->getName(), $this->_required) || isset($this->_errors[$element->getName()])) {
                    $anyrequiredorerror = true;
                } else {
                                                        }

                if ($element->getType() == 'header') {
                    if ($headercounter === 1 && !isset($this->_collapsibleElements[$headername])) {
                                                $this->setExpanded($headername, true);
                    } else if (($headercounter === 2 && $headerscount === 2) && !isset($this->_collapsibleElements[$headername])) {
                                                                        $this->setExpanded($headername, true);
                    }
                } else if ($anyrequiredorerror) {
                                        $this->setExpanded($headername, true, true);
                } else if (!isset($this->_collapsibleElements[$headername])) {
                                        $this->setExpanded($headername, false);
                }
            }

                        $renderer->setCollapsibleElements($this->_collapsibleElements);
        }
        parent::accept($renderer);
    }

    
    function closeHeaderBefore($elementName){
        $renderer =& $this->defaultRenderer();
        $renderer->addStopFieldsetElements($elementName);
    }

    
    function setType($elementname, $paramtype) {
        $this->_types[$elementname] = $paramtype;
    }

    
    function setTypes($paramtypes) {
        $this->_types = $paramtypes + $this->_types;
    }

    
    public function getCleanType($elementname, $value, $default = PARAM_RAW) {
        $type = $default;
        if (array_key_exists($elementname, $this->_types)) {
            $type = $this->_types[$elementname];
        }
        if (is_array($value)) {
            $default = $type;
            $type = array();
            foreach ($value as $subkey => $subvalue) {
                $typekey = "$elementname" . "[$subkey]";
                if (array_key_exists($typekey, $this->_types)) {
                    $subtype = $this->_types[$typekey];
                } else {
                    $subtype = $default;
                }
                if (is_array($subvalue)) {
                    $type[$subkey] = $this->getCleanType($typekey, $subvalue, $subtype);
                } else {
                    $type[$subkey] = $subtype;
                }
            }
        }
        return $type;
    }

    
    public function getCleanedValue($value, $type) {
        if (is_array($type) && is_array($value)) {
            foreach ($type as $key => $param) {
                $value[$key] = $this->getCleanedValue($value[$key], $param);
            }
        } else if (!is_array($type) && !is_array($value)) {
            $value = clean_param($value, $type);
        } else if (!is_array($type) && is_array($value)) {
            $value = clean_param_array($value, $type, true);
        } else {
            throw new coding_exception('Unexpected type or value received in MoodleQuickForm::getCleanedValue()');
        }
        return $value;
    }

    
    function updateSubmission($submission, $files) {
        $this->_flagSubmitted = false;

        if (empty($submission)) {
            $this->_submitValues = array();
        } else {
            foreach ($submission as $key => $s) {
                $type = $this->getCleanType($key, $s);
                $submission[$key] = $this->getCleanedValue($s, $type);
            }
            $this->_submitValues = $submission;
            $this->_flagSubmitted = true;
        }

        if (empty($files)) {
            $this->_submitFiles = array();
        } else {
            $this->_submitFiles = $files;
            $this->_flagSubmitted = true;
        }

                 foreach (array_keys($this->_elements) as $key) {
             $this->_elements[$key]->onQuickFormEvent('updateValue', null, $this);
         }
    }

    
    function getReqHTML(){
        return $this->_reqHTML;
    }

    
    function getAdvancedHTML(){
        return $this->_advancedHTML;
    }

    
    function setDefault($elementName, $defaultValue){
        $this->setDefaults(array($elementName=>$defaultValue));
    }

    
    function addHelpButton($elementname, $identifier, $component = 'moodle', $linktext = '', $suppresscheck = false) {
        global $OUTPUT;
        if (array_key_exists($elementname, $this->_elementIndex)) {
            $element = $this->_elements[$this->_elementIndex[$elementname]];
            $element->_helpbutton = $OUTPUT->help_icon($identifier, $component, $linktext);
        } else if (!$suppresscheck) {
            debugging(get_string('nonexistentformelements', 'form', $elementname));
        }
    }

    
    function setConstant($elname, $value) {
        $this->_constantValues = HTML_QuickForm::arrayMerge($this->_constantValues, array($elname=>$value));
        $element =& $this->getElement($elname);
        $element->onQuickFormEvent('updateValue', null, $this);
    }

    
    function exportValues($elementList = null){
        $unfiltered = array();
        if (null === $elementList) {
                        foreach (array_keys($this->_elements) as $key) {
                if ($this->_elements[$key]->isFrozen() && !$this->_elements[$key]->_persistantFreeze) {
                    $varname = $this->_elements[$key]->_attributes['name'];
                    $value = '';
                                        if (isset($this->_defaultValues[$varname])) {
                        $value = $this->prepare_fixed_value($varname, $this->_defaultValues[$varname]);
                    }
                } else {
                    $value = $this->_elements[$key]->exportValue($this->_submitValues, true);
                }

                if (is_array($value)) {
                                        $unfiltered = HTML_QuickForm::arrayMerge($unfiltered, $value);
                }
            }
        } else {
            if (!is_array($elementList)) {
                $elementList = array_map('trim', explode(',', $elementList));
            }
            foreach ($elementList as $elementName) {
                $value = $this->exportValue($elementName);
                if (@PEAR::isError($value)) {
                    return $value;
                }
                                $unfiltered = HTML_QuickForm::arrayMerge($unfiltered, $value);
            }
        }

        if (is_array($this->_constantValues)) {
            $unfiltered = HTML_QuickForm::arrayMerge($unfiltered, $this->_constantValues);
        }
        return $unfiltered;
    }

    
    protected function prepare_fixed_value($name, $value) {
        if (null === $value) {
            return null;
        } else {
            if (!strpos($name, '[')) {
                return array($name => $value);
            } else {
                $valueAry = array();
                $myIndex  = "['" . str_replace(array(']', '['), array('', "']['"), $name) . "']";
                eval("\$valueAry$myIndex = \$value;");
                return $valueAry;
            }
        }
    }

    
    function addRule($element, $message, $type, $format=null, $validation='server', $reset = false, $force = false)
    {
        parent::addRule($element, $message, $type, $format, $validation, $reset, $force);
        if ($validation == 'client') {
            $this->clientvalidation = true;
        }

    }

    
    function addGroupRule($group, $arg1, $type='', $format=null, $howmany=0, $validation = 'server', $reset = false)
    {
        parent::addGroupRule($group, $arg1, $type, $format, $howmany, $validation, $reset);
        if (is_array($arg1)) {
             foreach ($arg1 as $rules) {
                foreach ($rules as $rule) {
                    $validation = (isset($rule[3]) && 'client' == $rule[3])? 'client': 'server';
                    if ($validation == 'client') {
                        $this->clientvalidation = true;
                    }
                }
            }
        } elseif (is_string($arg1)) {
            if ($validation == 'client') {
                $this->clientvalidation = true;
            }
        }
    }

    
    function getValidationScript()
    {
        if (empty($this->_rules) || $this->clientvalidation === false) {
            return '';
        }

        include_once('HTML/QuickForm/RuleRegistry.php');
        $registry =& HTML_QuickForm_RuleRegistry::singleton();
        $test = array();
        $js_escape = array(
            "\r"    => '\r',
            "\n"    => '\n',
            "\t"    => '\t',
            "'"     => "\\'",
            '"'     => '\"',
            '\\'    => '\\\\'
        );

        foreach ($this->_rules as $elementName => $rules) {
            foreach ($rules as $rule) {
                if ('client' == $rule['validation']) {
                    unset($element); 
                    $dependent  = isset($rule['dependent']) && is_array($rule['dependent']);
                    $rule['message'] = strtr($rule['message'], $js_escape);

                    if (isset($rule['group'])) {
                        $group    =& $this->getElement($rule['group']);
                                                if ($group->isFrozen()) {
                            continue 2;
                        }
                        $elements =& $group->getElements();
                        foreach (array_keys($elements) as $key) {
                            if ($elementName == $group->getElementName($key)) {
                                $element =& $elements[$key];
                                break;
                            }
                        }
                    } elseif ($dependent) {
                        $element   =  array();
                        $element[] =& $this->getElement($elementName);
                        foreach ($rule['dependent'] as $elName) {
                            $element[] =& $this->getElement($elName);
                        }
                    } else {
                        $element =& $this->getElement($elementName);
                    }
                                        if (is_object($element) && $element->isFrozen()) {
                        continue 2;
                    } elseif (is_array($element)) {
                        foreach (array_keys($element) as $key) {
                            if ($element[$key]->isFrozen()) {
                                continue 3;
                            }
                        }
                    }
                                        $fullelementname = $elementName;
                    if ($element->getType() == 'editor') {
                        $fullelementname .= '[text]';
                                                                        if (is_null($rule['format'])) {
                            $rule['format'] = $element->getFormat();
                        }
                    }
                                        $test[$fullelementname][0][] = $registry->getValidationScript($element, $fullelementname, $rule);
                    $test[$fullelementname][1]=$element;
                                    }
            }
        }

                        unset($element);

        $js = '
<script type="text/javascript">
//<![CDATA[

var skipClientValidation = false;

(function() {

    function qf_errorHandler(element, _qfMsg, escapedName) {
      div = element.parentNode;

      if ((div == undefined) || (element.name == undefined)) {
        //no checking can be done for undefined elements so let server handle it.
        return true;
      }

      if (_qfMsg != \'\') {
        var errorSpan = document.getElementById(\'id_error_\' + escapedName);
        if (!errorSpan) {
          errorSpan = document.createElement("span");
          errorSpan.id = \'id_error_\' + escapedName;
          errorSpan.className = "error";
          element.parentNode.insertBefore(errorSpan, element.parentNode.firstChild);
          document.getElementById(errorSpan.id).setAttribute(\'TabIndex\', \'0\');
          document.getElementById(errorSpan.id).focus();
        }

        while (errorSpan.firstChild) {
          errorSpan.removeChild(errorSpan.firstChild);
        }

        errorSpan.appendChild(document.createTextNode(_qfMsg.substring(3)));

        if (div.className.substr(div.className.length - 6, 6) != " error"
          && div.className != "error") {
            div.className += " error";
            linebreak = document.createElement("br");
            linebreak.className = "error";
            linebreak.id = \'id_error_break_\' + escapedName;
            errorSpan.parentNode.insertBefore(linebreak, errorSpan.nextSibling);
        }

        return false;
      } else {
        var errorSpan = document.getElementById(\'id_error_\' + escapedName);
        if (errorSpan) {
          errorSpan.parentNode.removeChild(errorSpan);
        }
        var linebreak = document.getElementById(\'id_error_break_\' + escapedName);
        if (linebreak) {
          linebreak.parentNode.removeChild(linebreak);
        }

        if (div.className.substr(div.className.length - 6, 6) == " error") {
          div.className = div.className.substr(0, div.className.length - 6);
        } else if (div.className == "error") {
          div.className = "";
        }

        return true;
      }
    }';
        $validateJS = '';
        foreach ($test as $elementName => $jsandelement) {
                                    list($jsArr,$element)=$jsandelement;
                        $escapedElementName = preg_replace_callback(
                '/[_\[\]-]/',
                create_function('$matches', 'return sprintf("_%2x",ord($matches[0]));'),
                $elementName);
            $valFunc = 'validate_' . $this->_formName . '_' . $escapedElementName . '(ev.target, \''.$escapedElementName.'\')';

            $js .= '
    function validate_' . $this->_formName . '_' . $escapedElementName . '(element, escapedName) {
      if (undefined == element) {
         //required element was not found, then let form be submitted without client side validation
         return true;
      }
      var value = \'\';
      var errFlag = new Array();
      var _qfGroups = {};
      var _qfMsg = \'\';
      var frm = element.parentNode;
      if ((undefined != element.name) && (frm != undefined)) {
          while (frm && frm.nodeName.toUpperCase() != "FORM") {
            frm = frm.parentNode;
          }
        ' . join("\n", $jsArr) . '
          return qf_errorHandler(element, _qfMsg, escapedName);
      } else {
        //element name should be defined else error msg will not be displayed.
        return true;
      }
    }

    document.getElementById(\'' . $element->_attributes['id'] . '\').addEventListener(\'blur\', function(ev) {
        ' . $valFunc . '
    });
    document.getElementById(\'' . $element->_attributes['id'] . '\').addEventListener(\'change\', function(ev) {
        ' . $valFunc . '
    });
';
            $validateJS .= '
      ret = validate_' . $this->_formName . '_' . $escapedElementName.'(frm.elements[\''.$elementName.'\'], \''.$escapedElementName.'\') && ret;
      if (!ret && !first_focus) {
        first_focus = true;
        Y.use(\'moodle-core-event\', function() {
            Y.Global.fire(M.core.globalEvents.FORM_ERROR, {formid: \'' . $this->_attributes['id'] . '\',
                                                           elementid: \'id_error_' . $escapedElementName . '\'});
            document.getElementById(\'id_error_' . $escapedElementName . '\').focus();
        });
      }
';

                                                                                                                                         }
        $js .= '

    function validate_' . $this->_formName . '() {
      if (skipClientValidation) {
         return true;
      }
      var ret = true;

      var frm = document.getElementById(\''. $this->_attributes['id'] .'\')
      var first_focus = false;
    ' . $validateJS . ';
      return ret;
    }


    document.getElementById(\'' . $this->_attributes['id'] . '\').addEventListener(\'submit\', function(ev) {
        try {
            var myValidator = validate_' . $this->_formName . ';
        } catch(e) {
            return true;
        }
        if (typeof window.tinyMCE !== \'undefined\') {
            window.tinyMCE.triggerSave();
        }
        if (!myValidator()) {
            ev.preventDefault();
        }
    });
})();
//]]>
</script>';
        return $js;
    } 
    
    function _setDefaultRuleMessages(){
        foreach ($this->_rules as $field => $rulesarr){
            foreach ($rulesarr as $key => $rule){
                if ($rule['message']===null){
                    $a=new stdClass();
                    $a->format=$rule['format'];
                    $str=get_string('err_'.$rule['type'], 'form', $a);
                    if (strpos($str, '[[')!==0){
                        $this->_rules[$field][$key]['message']=$str;
                    }
                }
            }
        }
    }

    
    function getLockOptionObject(){
        $result = array();
        foreach ($this->_dependencies as $dependentOn => $conditions){
            $result[$dependentOn] = array();
            foreach ($conditions as $condition=>$values) {
                $result[$dependentOn][$condition] = array();
                foreach ($values as $value=>$dependents) {
                    $result[$dependentOn][$condition][$value] = array();
                    $i = 0;
                    foreach ($dependents as $dependent) {
                        $elements = $this->_getElNamesRecursive($dependent);
                        if (empty($elements)) {
                                                        $elements = array($dependent);
                        }
                        foreach($elements as $element) {
                            if ($element == $dependentOn) {
                                continue;
                            }
                            $result[$dependentOn][$condition][$value][] = $element;
                        }
                    }
                }
            }
        }
        return array($this->getAttribute('id'), $result);
    }

    
    function _getElNamesRecursive($element) {
        if (is_string($element)) {
            if (!$this->elementExists($element)) {
                return array();
            }
            $element = $this->getElement($element);
        }

        if (is_a($element, 'HTML_QuickForm_group')) {
            $elsInGroup = $element->getElements();
            $elNames = array();
            foreach ($elsInGroup as $elInGroup){
                if (is_a($elInGroup, 'HTML_QuickForm_group')) {
                                        $elNames = array_merge($elNames, $this->_getElNamesRecursive($elInGroup));
                } else {
                    $elNames[] = $element->getElementName($elInGroup->getName());
                }
            }

        } else if (is_a($element, 'HTML_QuickForm_header')) {
            return array();

        } else if (is_a($element, 'HTML_QuickForm_hidden')) {
            return array();

        } else if (method_exists($element, 'getPrivateName') &&
                !($element instanceof HTML_QuickForm_advcheckbox)) {
                                                return array($element->getPrivateName());

        } else {
            $elNames = array($element->getName());
        }

        return $elNames;
    }

    
    function disabledIf($elementName, $dependentOn, $condition = 'notchecked', $value='1') {
                        if (is_array($value)) {
            $value = implode('|', $value);
        }
        if (!array_key_exists($dependentOn, $this->_dependencies)) {
            $this->_dependencies[$dependentOn] = array();
        }
        if (!array_key_exists($condition, $this->_dependencies[$dependentOn])) {
            $this->_dependencies[$dependentOn][$condition] = array();
        }
        if (!array_key_exists($value, $this->_dependencies[$dependentOn][$condition])) {
            $this->_dependencies[$dependentOn][$condition][$value] = array();
        }
        $this->_dependencies[$dependentOn][$condition][$value][] = $elementName;
    }

    
    function registerNoSubmitButton($buttonname){
        $this->_noSubmitButtons[]=$buttonname;
    }

    
    function isNoSubmitButton($buttonname){
        return (array_search($buttonname, $this->_noSubmitButtons)!==FALSE);
    }

    
    function _registerCancelButton($addfieldsname){
        $this->_cancelButtons[]=$addfieldsname;
    }

    
    function hardFreeze($elementList=null)
    {
        if (!isset($elementList)) {
            $this->_freezeAll = true;
            $elementList = array();
        } else {
            if (!is_array($elementList)) {
                $elementList = preg_split('/[ ]*,[ ]*/', $elementList);
            }
            $elementList = array_flip($elementList);
        }

        foreach (array_keys($this->_elements) as $key) {
            $name = $this->_elements[$key]->getName();
            if ($this->_freezeAll || isset($elementList[$name])) {
                $this->_elements[$key]->freeze();
                $this->_elements[$key]->setPersistantFreeze(false);
                unset($elementList[$name]);

                                $this->_rules[$name] = array();
                                $unset = array_search($name, $this->_required);
                if ($unset !== false) {
                    unset($this->_required[$unset]);
                }
            }
        }

        if (!empty($elementList)) {
            return self::raiseError(null, QUICKFORM_NONEXIST_ELEMENT, null, E_USER_WARNING, "Nonexistant element(s): '" . implode("', '", array_keys($elementList)) . "' in HTML_QuickForm::freeze()", 'HTML_QuickForm_Error', true);
        }
        return true;
    }

    
    function hardFreezeAllVisibleExcept($elementList)
    {
        $elementList = array_flip($elementList);
        foreach (array_keys($this->_elements) as $key) {
            $name = $this->_elements[$key]->getName();
            $type = $this->_elements[$key]->getType();

            if ($type == 'hidden'){
                            } elseif (!isset($elementList[$name])) {
                $this->_elements[$key]->freeze();
                $this->_elements[$key]->setPersistantFreeze(false);

                                $this->_rules[$name] = array();
                                $unset = array_search($name, $this->_required);
                if ($unset !== false) {
                    unset($this->_required[$unset]);
                }
            }
        }
        return true;
    }

   
    function isSubmitted()
    {
        return parent::isSubmitted() && (!$this->isFrozen());
    }
}


class MoodleQuickForm_Renderer extends HTML_QuickForm_Renderer_Tableless{

    
    var $_elementTemplates;

    
    var $_openHiddenFieldsetTemplate = "\n\t<fieldset class=\"hidden\"><div>";

    
    var $_headerTemplate =
       "\n\t\t<legend class=\"ftoggler\">{header}</legend>\n\t\t<div class=\"fcontainer clearfix\">\n\t\t";

    
    var $_openFieldsetTemplate = "\n\t<fieldset class=\"{classes}\" {id}>";

    
    var $_closeFieldsetTemplate = "\n\t\t</div></fieldset>";

    
    var $_requiredNoteTemplate =
        "\n\t\t<div class=\"fdescription required\">{requiredNote}</div>";

    
    var $_collapseButtonsTemplate =
        "\n\t<div class=\"collapsible-actions\"><span class=\"collapseexpand\">{strexpandall}</span></div>";

    
    var $_advancedElements = array();

    
    var $_collapsibleElements = array();

    
    var $_collapseButtons = '';

    
    public function __construct() {
                        $this->_elementTemplates = array(
        'default'=>"\n\t\t".'<div id="{id}" class="fitem {advanced}<!-- BEGIN required --> required<!-- END required --> fitem_{type} {emptylabel} {class}" {aria-live}><div class="fitemtitle"><label>{label}<!-- BEGIN required -->{req}<!-- END required -->{advancedimg} </label>{help}</div><div class="felement {type}<!-- BEGIN error --> error<!-- END error -->"><!-- BEGIN error --><span class="error" tabindex="0">{error}</span><br /><!-- END error -->{element}</div></div>',

        'actionbuttons'=>"\n\t\t".'<div id="{id}" class="fitem fitem_actionbuttons fitem_{type} {class}"><div class="felement {type}">{element}</div></div>',

        'fieldset'=>"\n\t\t".'<div id="{id}" class="fitem {advanced} {class}<!-- BEGIN required --> required<!-- END required --> fitem_{type} {emptylabel}"><div class="fitemtitle"><div class="fgrouplabel"><label>{label}<!-- BEGIN required -->{req}<!-- END required -->{advancedimg} </label>{help}</div></div><fieldset class="felement {type}<!-- BEGIN error --> error<!-- END error -->"><!-- BEGIN error --><span class="error" tabindex="0">{error}</span><br /><!-- END error -->{element}</fieldset></div>',

        'static'=>"\n\t\t".'<div id="{id}" class="fitem {advanced} {emptylabel} {class}"><div class="fitemtitle"><div class="fstaticlabel">{label}<!-- BEGIN required -->{req}<!-- END required -->{advancedimg} {help}</div></div><div class="felement fstatic <!-- BEGIN error --> error<!-- END error -->"><!-- BEGIN error --><span class="error" tabindex="0">{error}</span><br /><!-- END error -->{element}</div></div>',

        'warning'=>"\n\t\t".'<div id="{id}" class="fitem {advanced} {emptylabel} {class}">{element}</div>',

        'nodisplay'=>'');

        parent::__construct();
    }

    
    public function MoodleQuickForm_Renderer() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

    
    function setAdvancedElements($elements){
        $this->_advancedElements = $elements;
    }

    
    function setCollapsibleElements($elements) {
        $this->_collapsibleElements = $elements;
    }

    
    function startForm(&$form){
        global $PAGE;
        $this->_reqHTML = $form->getReqHTML();
        $this->_elementTemplates = str_replace('{req}', $this->_reqHTML, $this->_elementTemplates);
        $this->_advancedHTML = $form->getAdvancedHTML();
        $this->_collapseButtons = '';
        $formid = $form->getAttribute('id');
        parent::startForm($form);
                $this->_hiddenHtml .= prevent_form_autofill_password();
        if ($form->isFrozen()){
            $this->_formTemplate = "\n<div class=\"mform frozen\">\n{content}\n</div>";
        } else {
            $this->_formTemplate = "\n<form{attributes}>\n\t<div style=\"display: none;\">{hidden}</div>\n{collapsebtns}\n{content}\n</form>";
            $this->_hiddenHtml .= $form->_pageparams;
        }

        if ($form->is_form_change_checker_enabled()) {
            $PAGE->requires->yui_module('moodle-core-formchangechecker',
                    'M.core_formchangechecker.init',
                    array(array(
                        'formid' => $formid
                    ))
            );
            $PAGE->requires->string_for_js('changesmadereallygoaway', 'moodle');
        }
        if (!empty($this->_collapsibleElements)) {
            if (count($this->_collapsibleElements) > 1) {
                $this->_collapseButtons = $this->_collapseButtonsTemplate;
                $this->_collapseButtons = str_replace('{strexpandall}', get_string('expandall'), $this->_collapseButtons);
                $PAGE->requires->strings_for_js(array('collapseall', 'expandall'), 'moodle');
            }
            $PAGE->requires->yui_module('moodle-form-shortforms', 'M.form.shortforms', array(array('formid' => $formid)));
        }
        if (!empty($this->_advancedElements)){
            $PAGE->requires->strings_for_js(array('showmore', 'showless'), 'form');
            $PAGE->requires->yui_module('moodle-form-showadvanced', 'M.form.showadvanced', array(array('formid' => $formid)));
        }
    }

    
    function startGroup(&$group, $required, $error){
                $group->_generateId();

                $groupid = 'fgroup_' . $group->getAttribute('id');

                $group->updateAttributes(array('id' => $groupid));

        if (method_exists($group, 'getElementTemplateType')){
            $html = $this->_elementTemplates[$group->getElementTemplateType()];
        }else{
            $html = $this->_elementTemplates['default'];

        }

        if (isset($this->_advancedElements[$group->getName()])){
            $html =str_replace(' {advanced}', ' advanced', $html);
            $html =str_replace('{advancedimg}', $this->_advancedHTML, $html);
        } else {
            $html =str_replace(' {advanced}', '', $html);
            $html =str_replace('{advancedimg}', '', $html);
        }
        if (method_exists($group, 'getHelpButton')){
            $html =str_replace('{help}', $group->getHelpButton(), $html);
        }else{
            $html =str_replace('{help}', '', $html);
        }
        $html = str_replace('{id}', $group->getAttribute('id'), $html);
        $html =str_replace('{name}', $group->getName(), $html);
        $html =str_replace('{type}', 'fgroup', $html);
        $html =str_replace('{class}', $group->getAttribute('class'), $html);
        $emptylabel = '';
        if ($group->getLabel() == '') {
            $emptylabel = 'femptylabel';
        }
        $html = str_replace('{emptylabel}', $emptylabel, $html);

        $this->_templates[$group->getName()]=$html;
                                if (   in_array($group->getName(), $this->_stopFieldsetElements)
            && $this->_fieldsetsOpen > 0
           ) {
            $this->_html .= $this->_closeFieldsetTemplate;
            $this->_fieldsetsOpen--;
        }
        parent::startGroup($group, $required, $error);
    }

    
    function renderElement(&$element, $required, $error){
                $element->_generateId();

                        if (($this->_inGroup) and !empty($this->_groupElementTemplate)) {
                        $html = $this->_groupElementTemplate;
        }
        elseif (method_exists($element, 'getElementTemplateType')){
            $html = $this->_elementTemplates[$element->getElementTemplateType()];
        }else{
            $html = $this->_elementTemplates['default'];
        }
        if (isset($this->_advancedElements[$element->getName()])){
            $html = str_replace(' {advanced}', ' advanced', $html);
            $html = str_replace(' {aria-live}', ' aria-live="polite"', $html);
        } else {
            $html = str_replace(' {advanced}', '', $html);
            $html = str_replace(' {aria-live}', '', $html);
        }
        if (isset($this->_advancedElements[$element->getName()])||$element->getName() == 'mform_showadvanced'){
            $html =str_replace('{advancedimg}', $this->_advancedHTML, $html);
        } else {
            $html =str_replace('{advancedimg}', '', $html);
        }
        $html =str_replace('{id}', 'fitem_' . $element->getAttribute('id'), $html);
        $html =str_replace('{type}', 'f'.$element->getType(), $html);
        $html =str_replace('{name}', $element->getName(), $html);
        $html =str_replace('{class}', $element->getAttribute('class'), $html);
        $emptylabel = '';
        if ($element->getLabel() == '') {
            $emptylabel = 'femptylabel';
        }
        $html = str_replace('{emptylabel}', $emptylabel, $html);
        if (method_exists($element, 'getHelpButton')){
            $html = str_replace('{help}', $element->getHelpButton(), $html);
        }else{
            $html = str_replace('{help}', '', $html);

        }
        if (($this->_inGroup) and !empty($this->_groupElementTemplate)) {
            $this->_groupElementTemplate = $html;
        }
        elseif (!isset($this->_templates[$element->getName()])) {
            $this->_templates[$element->getName()] = $html;
        }

        parent::renderElement($element, $required, $error);
    }

    
    function finishForm(&$form){
        global $PAGE;
        if ($form->isFrozen()){
            $this->_hiddenHtml = '';
        }
        parent::finishForm($form);
        $this->_html = str_replace('{collapsebtns}', $this->_collapseButtons, $this->_html);
        if (!$form->isFrozen()) {
            $args = $form->getLockOptionObject();
            if (count($args[1]) > 0) {
                $PAGE->requires->js_init_call('M.form.initFormDependencies', $args, true, moodleform::get_js_module());
            }
        }
    }
   
    function renderHeader(&$header) {
        global $PAGE;

        $header->_generateId();
        $name = $header->getName();

        $id = empty($name) ? '' : ' id="' . $header->getAttribute('id') . '"';
        if (is_null($header->_text)) {
            $header_html = '';
        } elseif (!empty($name) && isset($this->_templates[$name])) {
            $header_html = str_replace('{header}', $header->toHtml(), $this->_templates[$name]);
        } else {
            $header_html = str_replace('{header}', $header->toHtml(), $this->_headerTemplate);
        }

        if ($this->_fieldsetsOpen > 0) {
            $this->_html .= $this->_closeFieldsetTemplate;
            $this->_fieldsetsOpen--;
        }

                $arialive = '';
        $fieldsetclasses = array('clearfix');
        if (isset($this->_collapsibleElements[$header->getName()])) {
            $fieldsetclasses[] = 'collapsible';
            if ($this->_collapsibleElements[$header->getName()]) {
                $fieldsetclasses[] = 'collapsed';
            }
        }

        if (isset($this->_advancedElements[$name])){
            $fieldsetclasses[] = 'containsadvancedelements';
        }

        $openFieldsetTemplate = str_replace('{id}', $id, $this->_openFieldsetTemplate);
        $openFieldsetTemplate = str_replace('{classes}', join(' ', $fieldsetclasses), $openFieldsetTemplate);

        $this->_html .= $openFieldsetTemplate . $header_html;
        $this->_fieldsetsOpen++;
    }

    
    function getStopFieldsetElements(){
        return $this->_stopFieldsetElements;
    }
}


class MoodleQuickForm_Rule_Required extends HTML_QuickForm_Rule {
    
    function validate($value, $options = null) {
        global $CFG;
        if (is_array($value) && array_key_exists('text', $value)) {
            $value = $value['text'];
        }
        if (is_array($value)) {
                        $value = implode('', $value);
        }
        $stripvalues = array(
            '#</?(?!img|canvas|hr).*?>#im',             '#(\xc2\xa0|\s|&nbsp;)#',         );
        if (!empty($CFG->strictformsrequired)) {
            $value = preg_replace($stripvalues, '', (string)$value);
        }
        if ((string)$value == '') {
            return false;
        }
        return true;
    }

    
    function getValidationScript($format = null) {
        global $CFG;
        if (!empty($CFG->strictformsrequired)) {
            if (!empty($format) && $format == FORMAT_HTML) {
                return array('', "{jsVar}.replace(/(<(?!img|hr|canvas)[^>]*>)|&nbsp;|\s+/ig, '') == ''");
            } else {
                return array('', "{jsVar}.replace(/^\s+$/g, '') == ''");
            }
        } else {
            return array('', "{jsVar} == ''");
        }
    }
}


$GLOBALS['_HTML_QuickForm_default_renderer'] = new MoodleQuickForm_Renderer();


MoodleQuickForm::registerElementType('advcheckbox', "$CFG->libdir/form/advcheckbox.php", 'MoodleQuickForm_advcheckbox');
MoodleQuickForm::registerElementType('autocomplete', "$CFG->libdir/form/autocomplete.php", 'MoodleQuickForm_autocomplete');
MoodleQuickForm::registerElementType('button', "$CFG->libdir/form/button.php", 'MoodleQuickForm_button');
MoodleQuickForm::registerElementType('cancel', "$CFG->libdir/form/cancel.php", 'MoodleQuickForm_cancel');
MoodleQuickForm::registerElementType('course', "$CFG->libdir/form/course.php", 'MoodleQuickForm_course');
MoodleQuickForm::registerElementType('searchableselector', "$CFG->libdir/form/searchableselector.php", 'MoodleQuickForm_searchableselector');
MoodleQuickForm::registerElementType('checkbox', "$CFG->libdir/form/checkbox.php", 'MoodleQuickForm_checkbox');
MoodleQuickForm::registerElementType('date_selector', "$CFG->libdir/form/dateselector.php", 'MoodleQuickForm_date_selector');
MoodleQuickForm::registerElementType('date_time_selector', "$CFG->libdir/form/datetimeselector.php", 'MoodleQuickForm_date_time_selector');
MoodleQuickForm::registerElementType('duration', "$CFG->libdir/form/duration.php", 'MoodleQuickForm_duration');
MoodleQuickForm::registerElementType('editor', "$CFG->libdir/form/editor.php", 'MoodleQuickForm_editor');
MoodleQuickForm::registerElementType('filemanager', "$CFG->libdir/form/filemanager.php", 'MoodleQuickForm_filemanager');
MoodleQuickForm::registerElementType('filepicker', "$CFG->libdir/form/filepicker.php", 'MoodleQuickForm_filepicker');
MoodleQuickForm::registerElementType('grading', "$CFG->libdir/form/grading.php", 'MoodleQuickForm_grading');
MoodleQuickForm::registerElementType('group', "$CFG->libdir/form/group.php", 'MoodleQuickForm_group');
MoodleQuickForm::registerElementType('header', "$CFG->libdir/form/header.php", 'MoodleQuickForm_header');
MoodleQuickForm::registerElementType('hidden', "$CFG->libdir/form/hidden.php", 'MoodleQuickForm_hidden');
MoodleQuickForm::registerElementType('htmleditor', "$CFG->libdir/form/htmleditor.php", 'MoodleQuickForm_htmleditor');
MoodleQuickForm::registerElementType('listing', "$CFG->libdir/form/listing.php", 'MoodleQuickForm_listing');
MoodleQuickForm::registerElementType('modgrade', "$CFG->libdir/form/modgrade.php", 'MoodleQuickForm_modgrade');
MoodleQuickForm::registerElementType('modvisible', "$CFG->libdir/form/modvisible.php", 'MoodleQuickForm_modvisible');
MoodleQuickForm::registerElementType('password', "$CFG->libdir/form/password.php", 'MoodleQuickForm_password');
MoodleQuickForm::registerElementType('passwordunmask', "$CFG->libdir/form/passwordunmask.php", 'MoodleQuickForm_passwordunmask');
MoodleQuickForm::registerElementType('questioncategory', "$CFG->libdir/form/questioncategory.php", 'MoodleQuickForm_questioncategory');
MoodleQuickForm::registerElementType('radio', "$CFG->libdir/form/radio.php", 'MoodleQuickForm_radio');
MoodleQuickForm::registerElementType('recaptcha', "$CFG->libdir/form/recaptcha.php", 'MoodleQuickForm_recaptcha');
MoodleQuickForm::registerElementType('select', "$CFG->libdir/form/select.php", 'MoodleQuickForm_select');
MoodleQuickForm::registerElementType('selectgroups', "$CFG->libdir/form/selectgroups.php", 'MoodleQuickForm_selectgroups');
MoodleQuickForm::registerElementType('selectwithlink', "$CFG->libdir/form/selectwithlink.php", 'MoodleQuickForm_selectwithlink');
MoodleQuickForm::registerElementType('selectyesno', "$CFG->libdir/form/selectyesno.php", 'MoodleQuickForm_selectyesno');
MoodleQuickForm::registerElementType('static', "$CFG->libdir/form/static.php", 'MoodleQuickForm_static');
MoodleQuickForm::registerElementType('submit', "$CFG->libdir/form/submit.php", 'MoodleQuickForm_submit');
MoodleQuickForm::registerElementType('submitlink', "$CFG->libdir/form/submitlink.php", 'MoodleQuickForm_submitlink');
MoodleQuickForm::registerElementType('tags', "$CFG->libdir/form/tags.php", 'MoodleQuickForm_tags');
MoodleQuickForm::registerElementType('text', "$CFG->libdir/form/text.php", 'MoodleQuickForm_text');
MoodleQuickForm::registerElementType('textarea', "$CFG->libdir/form/textarea.php", 'MoodleQuickForm_textarea');
MoodleQuickForm::registerElementType('url', "$CFG->libdir/form/url.php", 'MoodleQuickForm_url');
MoodleQuickForm::registerElementType('warning', "$CFG->libdir/form/warning.php", 'MoodleQuickForm_warning');

MoodleQuickForm::registerRule('required', null, 'MoodleQuickForm_Rule_Required', "$CFG->libdir/formslib.php");
