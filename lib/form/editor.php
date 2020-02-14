<?php




global $CFG;

require_once('HTML/QuickForm/element.php');
require_once($CFG->dirroot.'/lib/filelib.php');
require_once($CFG->dirroot.'/repository/lib.php');


class MoodleQuickForm_editor extends HTML_QuickForm_element {
    
    public $_helpbutton = '';

    
    public $_type       = 'editor';

    
    protected $_options = array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 0, 'changeformat' => 0,
            'areamaxbytes' => FILE_AREA_MAX_BYTES_UNLIMITED, 'context' => null, 'noclean' => 0, 'trusttext' => 0,
            'return_types' => 7, 'enable_filemanagement' => true);
    
    
    protected $_values     = array('text'=>null, 'format'=>null, 'itemid'=>null);

    
    public function __construct($elementName=null, $elementLabel=null, $attributes=null, $options=null) {
        global $CFG, $PAGE;

        $options = (array)$options;
        foreach ($options as $name=>$value) {
            if (array_key_exists($name, $this->_options)) {
                $this->_options[$name] = $value;
            }
        }
        if (!empty($options['maxbytes'])) {
            $this->_options['maxbytes'] = get_max_upload_file_size($CFG->maxbytes, $options['maxbytes']);
        }
        if (!$this->_options['context']) {
                        if (!empty($PAGE->context->id)) {
                $this->_options['context'] = $PAGE->context;
            } else {
                $this->_options['context'] = context_system::instance();
            }
        }
        $this->_options['trusted'] = trusttext_trusted($this->_options['context']);
        parent::__construct($elementName, $elementLabel, $attributes);

                $this->_options['subdirs'] = (int)($this->_options['subdirs'] == 1);

        editors_head_setup();
    }

    
    public function MoodleQuickForm_editor($elementName=null, $elementLabel=null, $attributes=null, $options=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes, $options);
    }

    
    function onQuickFormEvent($event, $arg, &$caller)
    {
        switch ($event) {
            case 'createElement':
                $caller->setType($arg[0] . '[format]', PARAM_ALPHANUM);
                $caller->setType($arg[0] . '[itemid]', PARAM_INT);
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

    
    function setValue($values) {
        $values = (array)$values;
        foreach ($values as $name=>$value) {
            if (array_key_exists($name, $this->_values)) {
                $this->_values[$name] = $value;
            }
        }
    }

    
    function getValue() {
        return $this->_values;
    }

    
    function getMaxbytes() {
        return $this->_options['maxbytes'];
    }

    
    function setMaxbytes($maxbytes) {
        global $CFG;
        $this->_options['maxbytes'] = get_max_upload_file_size($CFG->maxbytes, $maxbytes);
    }

     
    function getAreamaxbytes() {
        return $this->_options['areamaxbytes'];
    }

    
    function setAreamaxbytes($areamaxbytes) {
        $this->_options['areamaxbytes'] = $areamaxbytes;
    }

    
    function getMaxfiles() {
        return $this->_options['maxfiles'];
    }

    
    function setMaxfiles($num) {
        $this->_options['maxfiles'] = $num;
    }

    
    function getSubdirs() {
        return $this->_options['subdirs'];
    }

    
    function setSubdirs($allow) {
        $this->_options['subdirs'] = (int)($allow == 1);
    }

    
    function getFormat() {
        return $this->_values['format'];
    }

    
    function isRequired() {
        return (isset($this->_options['required']) && $this->_options['required']);
    }

    
    function setHelpButton($_helpbuttonargs, $function='_helpbutton') {
        throw new coding_exception('setHelpButton() can not be used any more, please see MoodleQuickForm::addHelpButton().');
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
        global $CFG, $PAGE;
        require_once($CFG->dirroot.'/repository/lib.php');

        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        }

        $ctx = $this->_options['context'];

        $id           = $this->_attributes['id'];
        $elname       = $this->_attributes['name'];

        $subdirs      = $this->_options['subdirs'];
        $maxbytes     = $this->_options['maxbytes'];
        $areamaxbytes = $this->_options['areamaxbytes'];
        $maxfiles     = $this->_options['maxfiles'];
        $changeformat = $this->_options['changeformat']; 
        $text         = $this->_values['text'];
        $format       = $this->_values['format'];
        $draftitemid  = $this->_values['itemid'];

                if (isguestuser() or !isloggedin()) {
            $maxfiles = 0;
        }

        $str = $this->_getTabs();
        $str .= '<div>';

        $editor = editors_get_preferred_editor($format);
        $strformats = format_text_menu();
        $formats =  $editor->get_supported_formats();
        foreach ($formats as $fid) {
            $formats[$fid] = $strformats[$fid];
        }

                        $fpoptions = array();
        if ($maxfiles != 0 ) {
            if (empty($draftitemid)) {
                                require_once("$CFG->libdir/filelib.php");
                $this->setValue(array('itemid'=>file_get_unused_draft_itemid()));
                $draftitemid = $this->_values['itemid'];
            }

            $args = new stdClass();
                        $args->accepted_types = array('web_image');
            $args->return_types = $this->_options['return_types'];
            $args->context = $ctx;
            $args->env = 'filepicker';
                        $image_options = initialise_filepicker($args);
            $image_options->context = $ctx;
            $image_options->client_id = uniqid();
            $image_options->maxbytes = $this->_options['maxbytes'];
            $image_options->areamaxbytes = $this->_options['areamaxbytes'];
            $image_options->env = 'editor';
            $image_options->itemid = $draftitemid;

                        $args->accepted_types = array('video', 'audio');
            $media_options = initialise_filepicker($args);
            $media_options->context = $ctx;
            $media_options->client_id = uniqid();
            $media_options->maxbytes  = $this->_options['maxbytes'];
            $media_options->areamaxbytes  = $this->_options['areamaxbytes'];
            $media_options->env = 'editor';
            $media_options->itemid = $draftitemid;

                        $args->accepted_types = '*';
            $link_options = initialise_filepicker($args);
            $link_options->context = $ctx;
            $link_options->client_id = uniqid();
            $link_options->maxbytes  = $this->_options['maxbytes'];
            $link_options->areamaxbytes  = $this->_options['areamaxbytes'];
            $link_options->env = 'editor';
            $link_options->itemid = $draftitemid;

            $fpoptions['image'] = $image_options;
            $fpoptions['media'] = $media_options;
            $fpoptions['link'] = $link_options;
        }

                if (($editor instanceof tinymce_texteditor)  && !is_null($this->getAttribute('onchange'))) {
            $this->_options['required'] = true;
        }

                $editor->set_text($text);
        $editor->use_editor($id, $this->_options, $fpoptions);

        $rows = empty($this->_attributes['rows']) ? 15 : $this->_attributes['rows'];
        $cols = empty($this->_attributes['cols']) ? 80 : $this->_attributes['cols'];

                $editorrules = '';
        if (!is_null($this->getAttribute('onblur')) && !is_null($this->getAttribute('onchange'))) {
            $editorrules = ' onblur="'.htmlspecialchars($this->getAttribute('onblur')).'" onchange="'.htmlspecialchars($this->getAttribute('onchange')).'"';
        }
        $str .= '<div><textarea id="'.$id.'" name="'.$elname.'[text]" rows="'.$rows.'" cols="'.$cols.'" spellcheck="true"'.$editorrules.'>';
        $str .= s($text);
        $str .= '</textarea></div>';

        $str .= '<div>';
        if (count($formats)>1) {
            $str .= html_writer::label(get_string('format'), 'menu'. $elname. 'format', false, array('class' => 'accesshide'));
            $str .= html_writer::select($formats, $elname.'[format]', $format, false, array('id' => 'menu'. $elname. 'format'));
        } else {
            $keys = array_keys($formats);
            $str .= html_writer::empty_tag('input',
                    array('name'=>$elname.'[format]', 'type'=> 'hidden', 'value' => array_pop($keys)));
        }
        $str .= '</div>';

                        if (!during_initial_install() && empty($CFG->adminsetuppending)) {
                        if ($maxfiles != 0 ) {
                $str .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $elname.'[itemid]',
                        'value' => $draftitemid));

                                $editorurl = new moodle_url("$CFG->wwwroot/repository/draftfiles_manager.php", array(
                    'action'=>'browse',
                    'env'=>'editor',
                    'itemid'=>$draftitemid,
                    'subdirs'=>$subdirs,
                    'maxbytes'=>$maxbytes,
                    'areamaxbytes' => $areamaxbytes,
                    'maxfiles'=>$maxfiles,
                    'ctx_id'=>$ctx->id,
                    'course'=>$PAGE->course->id,
                    'sesskey'=>sesskey(),
                    ));
                $str .= '<noscript>';
                $str .= "<div><object type='text/html' data='$editorurl' height='160' width='600' style='border:1px solid #000'></object></div>";
                $str .= '</noscript>';
            }
        }


        $str .= '</div>';

        return $str;
    }

    
    function getFrozenHtml() {

        return '';
    }
}
