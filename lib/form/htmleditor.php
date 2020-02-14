<?php




global $CFG;
require_once("$CFG->libdir/form/textarea.php");


class MoodleQuickForm_htmleditor extends MoodleQuickForm_textarea{
    
    var $_type;

    
    var $_options=array('rows'=>10, 'cols'=>45, 'width'=>0,'height'=>0);

    
    public function __construct($elementName=null, $elementLabel=null, $options=array(), $attributes=null){
        parent::__construct($elementName, $elementLabel, $attributes);
                if (is_array($options)) {
            foreach ($options as $name => $value) {
                if (array_key_exists($name, $this->_options)) {
                    if (is_array($value) && is_array($this->_options[$name])) {
                        $this->_options[$name] = @array_merge($this->_options[$name], $value);
                    } else {
                        $this->_options[$name] = $value;
                    }
                }
            }
        }
        $this->_type='htmleditor';

        editors_head_setup();
    }

    
    public function MoodleQuickForm_htmleditor($elementName=null, $elementLabel=null, $options=array(), $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $options, $attributes);
    }

    
    function toHtml(){
        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        } else {
            return $this->_getTabs() .
                    print_textarea(true,
                                    $this->_options['rows'],
                                    $this->_options['cols'],
                                    $this->_options['width'],
                                    $this->_options['height'],
                                    $this->getName(),
                                    preg_replace("/(\r\n|\n|\r)/", '&#010;',$this->getValue()),
                                    0,                                     true,
                                    $this->getAttribute('id'));
        }
    }

    
    function getFrozenHtml()
    {
        $html = format_text($this->getValue());
        return $html . $this->_getPersistantData();
    }
}
