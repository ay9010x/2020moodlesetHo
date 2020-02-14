<?php




global $CFG;
require_once("$CFG->libdir/form/submit.php");

class MoodleQuickForm_submitlink extends MoodleQuickForm_submit {
    
    var $_js;

    
    var $_onclick;

    
    public function __construct($elementName=null, $value=null, $attributes=null) {
        parent::__construct($elementName, $value, $attributes);
    }

    
    public function MoodleQuickForm_submitlink($elementName=null, $value=null, $attributes=null) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $value, $attributes);
    }

    
    function toHtml() {
        $text = $this->_attributes['value'];

        return "<noscript><div>" . parent::toHtml() . '</div></noscript><script type="text/javascript">' . $this->_js . "\n"
             . 'document.write(\'<a href="#" onclick="' . $this->_onclick . '">'
             . $text . "</a>');\n</script>";
    }
}
