<?php




require_once("HTML/QuickForm/text.php");


class MoodleQuickForm_url extends HTML_QuickForm_text{
    
    var $_helpbutton='';

    
    var $_hiddenLabel=false;

    
    public function __construct($elementName=null, $elementLabel=null, $attributes=null, $options=null) {
        global $CFG;
        require_once("$CFG->dirroot/repository/lib.php");
        $options = (array)$options;
        foreach ($options as $name=>$value) {
            $this->_options[$name] = $value;
        }
        if (!isset($this->_options['usefilepicker'])) {
            $this->_options['usefilepicker'] = true;
        }
        parent::__construct($elementName, $elementLabel, $attributes);
    }

    
    public function MoodleQuickForm_url($elementName=null, $elementLabel=null, $attributes=null, $options=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $attributes, $options);
    }

    
    function setHiddenLabel($hiddenLabel){
        $this->_hiddenLabel = $hiddenLabel;
    }

    
    function toHtml(){
        global $PAGE, $OUTPUT;

        $id     = $this->_attributes['id'];
        $elname = $this->_attributes['name'];

        if ($this->_hiddenLabel) {
            $this->_generateId();
            $str = '<label class="accesshide" for="'.$this->getAttribute('id').'" >'.
                        $this->getLabel().'</label>'.parent::toHtml();
        } else {
            $str = parent::toHtml();
        }
        if (empty($this->_options['usefilepicker'])) {
            return $str;
        }

        $client_id = uniqid();

        $args = new stdClass();
        $args->accepted_types = '*';
        $args->return_types = FILE_EXTERNAL;
        $args->context = $PAGE->context;
        $args->client_id = $client_id;
        $args->env = 'url';
        $fp = new file_picker($args);
        $options = $fp->options;

        if (count($options->repositories) > 0) {
            $straddlink = get_string('choosealink', 'repository');
            $str .= <<<EOD
<button id="filepicker-button-js-{$client_id}" class="visibleifjs">
$straddlink
</button>
EOD;
        }

                $str .= $OUTPUT->render($fp);

        $module = array('name'=>'form_url', 'fullpath'=>'/lib/form/url.js', 'requires'=>array('core_filepicker'));
        $PAGE->requires->js_init_call('M.form_url.init', array($options), true, $module);

        return $str;
    }

    
    function getHelpButton(){
        return $this->_helpbutton;
    }

    
    function getElementTemplateType(){
        if ($this->_flagFrozen){
            return 'static';
        } else {
            return 'default';
        }
    }
}
