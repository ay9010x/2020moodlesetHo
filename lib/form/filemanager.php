<?php




global $CFG;

require_once('HTML/QuickForm/element.php');
require_once($CFG->dirroot.'/lib/filelib.php');
require_once($CFG->dirroot.'/repository/lib.php');


class MoodleQuickForm_filemanager extends HTML_QuickForm_element {
    
    public $_helpbutton = '';

    
                protected $_options = array('mainfile' => '', 'subdirs' => 1, 'maxbytes' => -1, 'maxfiles' => -1,
            'accepted_types' => '*', 'return_types' =>  null, 'areamaxbytes' => FILE_AREA_MAX_BYTES_UNLIMITED);

    
    public function __construct($elementName=null, $elementLabel=null, $attributes=null, $options=null) {
        global $CFG, $PAGE;

        $options = (array)$options;
        foreach ($options as $name=>$value) {
            if (array_key_exists($name, $this->_options)) {
                $this->_options[$name] = $value;
            }
        }
        if (!empty($options['maxbytes'])) {
            $this->_options['maxbytes'] = get_user_max_upload_file_size($PAGE->context, $CFG->maxbytes, $options['maxbytes']);
        }
        if (empty($options['return_types'])) {
            $this->_options['return_types'] = (FILE_INTERNAL | FILE_REFERENCE);
        }
        $this->_type = 'filemanager';
        parent::__construct($elementName, $elementLabel, $attributes);
    }

    
    public function MoodleQuickForm_filemanager($elementName=null, $elementLabel=null, $attributes=null, $options=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes, $options);
    }

    
    function onQuickFormEvent($event, $arg, &$caller)
    {
        switch ($event) {
            case 'createElement':
                $caller->setType($arg[0], PARAM_INT);
                break;
        }
        return parent::onQuickFormEvent($event, $arg, $caller);
    }

    
    function setName($name) {
        $this->updateAttributes(array('name'=>$name));
    }

    
    function getName() {
        return $this->getAttribute('name');
    }

    
    function setValue($value) {
        $this->updateAttributes(array('value'=>$value));
    }

    
    function getValue() {
        return $this->getAttribute('value');
    }

    
    function getMaxbytes() {
        return $this->_options['maxbytes'];
    }

    
    function setMaxbytes($maxbytes) {
        global $CFG, $PAGE;
        $this->_options['maxbytes'] = get_user_max_upload_file_size($PAGE->context, $CFG->maxbytes, $maxbytes);
    }

    
    function getAreamaxbytes() {
        return $this->_options['areamaxbytes'];
    }

    
    function setAreamaxbytes($areamaxbytes) {
        $this->_options['areamaxbytes'] = $areamaxbytes;
    }

    
    function getSubdirs() {
        return $this->_options['subdirs'];
    }

    
    function setSubdirs($allow) {
        $this->_options['subdirs'] = $allow;
    }

    
    function getMaxfiles() {
        return $this->_options['maxfiles'];
    }

    
    function setMaxfiles($num) {
        $this->_options['maxfiles'] = $num;
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
        global $CFG, $USER, $COURSE, $PAGE, $OUTPUT;
        require_once("$CFG->dirroot/repository/lib.php");

                if (isguestuser() or !isloggedin()) {
            print_error('noguest');
        }

        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        }

        $id          = $this->_attributes['id'];
        $elname      = $this->_attributes['name'];
        $subdirs     = $this->_options['subdirs'];
        $maxbytes    = $this->_options['maxbytes'];
        $draftitemid = $this->getValue();
        $accepted_types = $this->_options['accepted_types'];

        if (empty($draftitemid)) {
                        require_once("$CFG->libdir/filelib.php");
            $this->setValue(file_get_unused_draft_itemid());
            $draftitemid = $this->getValue();
        }

        $client_id = uniqid();

                $options = new stdClass();
        $options->mainfile  = $this->_options['mainfile'];
        $options->maxbytes  = $this->_options['maxbytes'];
        $options->maxfiles  = $this->getMaxfiles();
        $options->client_id = $client_id;
        $options->itemid    = $draftitemid;
        $options->subdirs   = $this->_options['subdirs'];
        $options->target    = $id;
        $options->accepted_types = $accepted_types;
        $options->return_types = $this->_options['return_types'];
        $options->context = $PAGE->context;
        $options->areamaxbytes = $this->_options['areamaxbytes'];

        $html = $this->_getTabs();
        $fm = new form_filemanager($options);
        $output = $PAGE->get_renderer('core', 'files');
        $html .= $output->render($fm);

        $html .= html_writer::empty_tag('input', array('value' => $draftitemid, 'name' => $elname, 'type' => 'hidden'));
                $html .= html_writer::empty_tag('input', array('value' => '', 'id' => 'id_'.$elname, 'type' => 'hidden'));

        return $html;
    }
}


class form_filemanager implements renderable {
    
    public $options;

    
    public function __construct(stdClass $options) {
        global $CFG, $USER, $PAGE;
        require_once($CFG->dirroot. '/repository/lib.php');
        $defaults = array(
            'maxbytes'=>-1,
            'areamaxbytes' => FILE_AREA_MAX_BYTES_UNLIMITED,
            'maxfiles'=>-1,
            'itemid'=>0,
            'subdirs'=>0,
            'client_id'=>uniqid(),
            'accepted_types'=>'*',
            'return_types'=>FILE_INTERNAL,
            'context'=>$PAGE->context,
            'author'=>fullname($USER),
            'licenses'=>array()
            );
        if (!empty($CFG->licenses)) {
            $array = explode(',', $CFG->licenses);
            foreach ($array as $license) {
                $l = new stdClass();
                $l->shortname = $license;
                $l->fullname = get_string($license, 'license');
                $defaults['licenses'][] = $l;
            }
        }
        if (!empty($CFG->sitedefaultlicense)) {
            $defaults['defaultlicense'] = $CFG->sitedefaultlicense;
        }
        foreach ($defaults as $key=>$value) {
                        if (!isset($options->$key)) {
                $options->$key = $value;
            }
        }

        $fs = get_file_storage();

                $this->options = file_get_drafarea_files($options->itemid, '/');

                $usercontext = context_user::instance($USER->id);
        $files = $fs->get_area_files($usercontext->id, 'user', 'draft', $options->itemid, 'id', false);
        $filecount = count($files);
        $this->options->filecount = $filecount;

                foreach ($options as $name=>$value) {
            $this->options->$name = $value;
        }

                        $coursebytes = $maxbytes = 0;
        list($context, $course, $cm) = get_context_info_array($this->options->context->id);
        if (is_object($course)) {
            $coursebytes = $course->maxbytes;
        }
        if (!empty($this->options->maxbytes) && $this->options->maxbytes > 0) {
            $maxbytes = $this->options->maxbytes;
        }
        $this->options->maxbytes = get_user_max_upload_file_size($context, $CFG->maxbytes, $coursebytes, $maxbytes);

                $params = new stdClass();
        $params->accepted_types = $options->accepted_types;
        $params->return_types = $options->return_types;
        $params->context = $options->context;
        $params->env = 'filemanager';
        $params->disable_types = !empty($options->disable_types)?$options->disable_types:array();
        $filepicker_options = initialise_filepicker($params);
        $this->options->filepicker = $filepicker_options;
    }

    public function get_nonjsurl() {
        global $PAGE;
        return new moodle_url('/repository/draftfiles_manager.php', array(
            'env'=>'filemanager',
            'action'=>'browse',
            'itemid'=>$this->options->itemid,
            'subdirs'=>$this->options->subdirs,
            'maxbytes'=>$this->options->maxbytes,
            'areamaxbytes' => $this->options->areamaxbytes,
            'maxfiles'=>$this->options->maxfiles,
            'ctx_id'=>$PAGE->context->id,             'course'=>$PAGE->course->id,             'sesskey'=>sesskey(),
            ));
    }
}
