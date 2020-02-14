<?php




global $CFG;

require_once("HTML/QuickForm/button.php");
require_once($CFG->dirroot.'/repository/lib.php');


class MoodleQuickForm_filepicker extends HTML_QuickForm_input {
    
    public $_helpbutton = '';

    
                protected $_options    = array('maxbytes'=>0, 'accepted_types'=>'*', 'return_types'=>null);

    
    public function __construct($elementName=null, $elementLabel=null, $attributes=null, $options=null) {
        global $CFG, $PAGE;

        $options = (array)$options;
        foreach ($options as $name=>$value) {
            if (array_key_exists($name, $this->_options)) {
                $this->_options[$name] = $value;
            }
        }
        if (empty($options['return_types'])) {
            $this->_options['return_types'] = FILE_INTERNAL;
        }
        $fpmaxbytes = 0;
        if (!empty($options['maxbytes'])) {
            $fpmaxbytes = $options['maxbytes'];
        }
        $coursemaxbytes = 0;
        if (!empty($PAGE->course->maxbytes)) {
            $coursemaxbytes = $PAGE->course->maxbytes;
        }
        $this->_options['maxbytes'] = get_user_max_upload_file_size($PAGE->context, $CFG->maxbytes, $coursemaxbytes, $fpmaxbytes);
        $this->_type = 'filepicker';
        parent::__construct($elementName, $elementLabel, $attributes);
    }

    
    public function MoodleQuickForm_filepicker($elementName=null, $elementLabel=null, $attributes=null, $options=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes, $options);
    }

    
    function getHelpButton() {
        return $this->_helpbutton;
    }

    
    function getElementTemplateType() {
        if ($this->_flagFrozen){
            return 'nodisplay';
        } else {
            return 'default';
        }
    }

    
    function toHtml() {
        global $CFG, $COURSE, $USER, $PAGE, $OUTPUT;
        $id     = $this->_attributes['id'];
        $elname = $this->_attributes['name'];

        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        }
        if (!$draftitemid = (int)$this->getValue()) {
                        $draftitemid = file_get_unused_draft_itemid();
            $this->setValue($draftitemid);
        }

        if ($COURSE->id == SITEID) {
            $context = context_system::instance();
        } else {
            $context = context_course::instance($COURSE->id);
        }

        $client_id = uniqid();

        $args = new stdClass();
                $args->accepted_types = $this->_options['accepted_types']?$this->_options['accepted_types']:'*';
        $args->return_types = $this->_options['return_types'];
        $args->itemid = $draftitemid;
        $args->maxbytes = $this->_options['maxbytes'];
        $args->context = $PAGE->context;
        $args->buttonname = $elname.'choose';
        $args->elementname = $elname;

        $html = $this->_getTabs();
        $fp = new file_picker($args);
        $options = $fp->options;
        $options->context = $PAGE->context;
        $html .= $OUTPUT->render($fp);
        $html .= '<input type="hidden" name="'.$elname.'" id="'.$id.'" value="'.$draftitemid.'" class="filepickerhidden"/>';

        $module = array('name'=>'form_filepicker', 'fullpath'=>'/lib/form/filepicker.js', 'requires'=>array('core_filepicker', 'node', 'node-event-simulate', 'core_dndupload'));
        $PAGE->requires->js_init_call('M.form_filepicker.init', array($fp->options), true, $module);

        $nonjsfilepicker = new moodle_url('/repository/draftfiles_manager.php', array(
            'env'=>'filepicker',
            'action'=>'browse',
            'itemid'=>$draftitemid,
            'subdirs'=>0,
            'maxbytes'=>$options->maxbytes,
            'maxfiles'=>1,
            'ctx_id'=>$PAGE->context->id,
            'course'=>$PAGE->course->id,
            'sesskey'=>sesskey(),
            ));

                $html .= '<noscript>';
        $html .= "<div><object type='text/html' data='$nonjsfilepicker' height='160' width='600' style='border:1px solid #000'></object></div>";
        $html .= '</noscript>';

        return $html;
    }

    
    function exportValue(&$submitValues, $assoc = false) {
        global $USER;

        $draftitemid = $this->_findValue($submitValues);
        if (null === $draftitemid) {
            $draftitemid = $this->getValue();
        }

                if (!is_null($draftitemid)) {
            $fs = get_file_storage();
            $usercontext = context_user::instance($USER->id);
            if ($files = $fs->get_area_files($usercontext->id, 'user', 'draft', $draftitemid, 'id DESC', false)) {
                $file = array_shift($files);
                if ($this->_options['maxbytes']
                    and $this->_options['maxbytes'] !== USER_CAN_IGNORE_FILE_SIZE_LIMITS
                    and $file->get_filesize() > $this->_options['maxbytes']) {

                                        $file->delete();
                }
                foreach ($files as $file) {
                                        $file->delete();
                }
            }
        }

        return $this->_prepareValue($draftitemid, true);
    }
}
