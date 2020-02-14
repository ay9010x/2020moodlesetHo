<?php


require_once 'HTML/QuickForm/Renderer.php';


class HTML_QuickForm_Renderer_Array extends HTML_QuickForm_Renderer
{
   
    var $_ary;

   
    var $_sectionCount;

   
    var $_currentSection;

   
    var $_currentGroup = null;

   
    var $_elementStyles = array();

   
    var $_collectHidden = false;

   
    var $staticLabels = false;

   
    public function __construct($collectHidden = false, $staticLabels = false) {
        parent::__construct();
        $this->_collectHidden = $collectHidden;
        $this->_staticLabels  = $staticLabels;
    } 
    
    public function HTML_QuickForm_Renderer_Array($collectHidden = false, $staticLabels = false) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($collectHidden, $staticLabels);
    }

   
    function toArray()
    {
        return $this->_ary;
    }


    function startForm(&$form)
    {
        $this->_ary = array(
            'frozen'            => $form->isFrozen(),
            'javascript'        => $form->getValidationScript(),
            'attributes'        => $form->getAttributes(true),
            'requirednote'      => $form->getRequiredNote(),
            'errors'            => array()
        );
        if ($this->_collectHidden) {
            $this->_ary['hidden'] = '';
        }
        $this->_elementIdx     = 1;
        $this->_currentSection = null;
        $this->_sectionCount   = 0;
    } 

    function renderHeader(&$header)
    {
        $this->_ary['sections'][$this->_sectionCount] = array(
            'header' => $header->toHtml(),
            'name'   => $header->getName()
        );
        $this->_currentSection = $this->_sectionCount++;
    } 

    function renderElement(&$element, $required, $error)
    {
        $elAry = $this->_elementToArray($element, $required, $error);
        if (!empty($error)) {
            $this->_ary['errors'][$elAry['name']] = $error;
        }
        $this->_storeArray($elAry);
    } 

    function renderHidden(&$element)
    {
        if ($this->_collectHidden) {
            $this->_ary['hidden'] .= $element->toHtml() . "\n";
        } else {
            $this->renderElement($element, false, null);
        }
    } 

    function startGroup(&$group, $required, $error)
    {
        $this->_currentGroup = $this->_elementToArray($group, $required, $error);
        if (!empty($error)) {
            $this->_ary['errors'][$this->_currentGroup['name']] = $error;
        }
    } 

    function finishGroup(&$group)
    {
        $this->_storeArray($this->_currentGroup);
        $this->_currentGroup = null;
    } 

   
    function _elementToArray(&$element, $required, $error)
    {
        $ret = array(
            'name'      => $element->getName(),
            'value'     => $element->getValue(),
            'type'      => $element->getType(),
            'frozen'    => $element->isFrozen(),
            'required'  => $required,
            'error'     => $error
        );
                $labels = $element->getLabel();
        if (is_array($labels) && $this->_staticLabels) {
            foreach($labels as $key => $label) {
                $key = is_int($key)? $key + 1: $key;
                if (1 === $key) {
                    $ret['label'] = $label;
                } else {
                    $ret['label_' . $key] = $label;
                }
            }
        } else {
            $ret['label'] = $labels;
        }

                if (isset($this->_elementStyles[$ret['name']])) {
            $ret['style'] = $this->_elementStyles[$ret['name']];
        }
        if ('group' == $ret['type']) {
            $ret['separator'] = $element->_separator;
            $ret['elements']  = array();
        } else {
            $ret['html']      = $element->toHtml();
        }
        return $ret;
    }


   
    function _storeArray($elAry)
    {
                if (is_array($this->_currentGroup) && ('group' != $elAry['type'])) {
            $this->_currentGroup['elements'][] = $elAry;
        } elseif (isset($this->_currentSection)) {
            $this->_ary['sections'][$this->_currentSection]['elements'][] = $elAry;
        } else {
            $this->_ary['elements'][] = $elAry;
        }
    }


   
    function setElementStyle($elementName, $styleName = null)
    {
        if (is_array($elementName)) {
            $this->_elementStyles = array_merge($this->_elementStyles, $elementName);
        } else {
            $this->_elementStyles[$elementName] = $styleName;
        }
    }
}
?>