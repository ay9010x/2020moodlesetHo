<?php




require_once('HTML/QuickForm/element.php');


class MoodleQuickForm_selectgroups extends HTML_QuickForm_element {

    
    var $showchoose = false;

    
    var $_optGroups = array();

    
    var $_values = null;

    
    var $_helpbutton='';

    
    var $_hiddenLabel=false;

    
    public function __construct($elementName=null, $elementLabel=null, $optgrps=null, $attributes=null, $showchoose=false)
    {
        $this->showchoose = $showchoose;
        parent::__construct($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_type = 'selectgroups';
        if (isset($optgrps)) {
            $this->loadArrayOptGroups($optgrps);
        }
    }

    
    public function MoodleQuickForm_selectgroups($elementName=null, $elementLabel=null, $optgrps=null, $attributes=null, $showchoose=false) {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct($elementName, $elementLabel, $optgrps, $attributes, $showchoose);
    }

    
    function setSelected($values)
    {
        if (is_string($values) && $this->getMultiple()) {
            $values = preg_split("/[ ]?,[ ]?/", $values);
        }
        if (is_array($values)) {
            $this->_values = array_values($values);
        } else {
            $this->_values = array($values);
        }
    }

    
    function getSelected()
    {
        return $this->_values;
    }

    
    function setName($name)
    {
        $this->updateAttributes(array('name' => $name));
    }

    
    function getName()
    {
        return $this->getAttribute('name');
    }

    
    function getPrivateName()
    {
        if ($this->getAttribute('multiple')) {
            return $this->getName() . '[]';
        } else {
            return $this->getName();
        }
    }

    
    function setValue($value)
    {
        $this->setSelected($value);
    }

    
    function getValue()
    {
        return $this->_values;
    }

    
    function setSize($size)
    {
        $this->updateAttributes(array('size' => $size));
    }

    
    function getSize()
    {
        return $this->getAttribute('size');
    }

    
    function setMultiple($multiple)
    {
        if ($multiple) {
            $this->updateAttributes(array('multiple' => 'multiple'));
        } else {
            $this->removeAttribute('multiple');
        }
    }

    
    function getMultiple()
    {
        return (bool)$this->getAttribute('multiple');
    }

    
    function loadArrayOptGroups($arr, $values=null)
    {
        if (!is_array($arr)) {
            return self::raiseError('Argument 1 of HTML_Select::loadArrayOptGroups is not a valid array');
        }
        if (isset($values)) {
            $this->setSelected($values);
        }
        foreach ($arr as $key => $val) {
                        $this->addOptGroup($key, $val);
        }
        return true;
    }

    
    function addOptGroup($text, $value, $attributes=null)
    {
        if (null === $attributes) {
            $attributes = array('label' => $text);
        } else {
            $attributes = $this->_parseAttributes($attributes);
            $this->_updateAttrArray($attributes, array('label' => $text));
        }
        $index = count($this->_optGroups);
        $this->_optGroups[$index] = array('attr' => $attributes);
        $this->loadArrayOptions($index, $value);
    }

    
    function loadArrayOptions($optgroup, $arr, $values=null)
    {
        if (!is_array($arr)) {
            return self::raiseError('Argument 1 of HTML_Select::loadArray is not a valid array');
        }
        if (isset($values)) {
            $this->setSelected($values);
        }
        foreach ($arr as $key => $val) {
                        $this->addOption($optgroup, $val, $key);
        }
        return true;
    }

    
    function addOption($optgroup, $text, $value, $attributes=null)
    {
        if (null === $attributes) {
            $attributes = array('value' => $value);
        } else {
            $attributes = $this->_parseAttributes($attributes);
            if (isset($attributes['selected'])) {
                                $this->_removeAttr('selected', $attributes);
                if (is_null($this->_values)) {
                    $this->_values = array($value);
                } elseif (!in_array($value, $this->_values)) {
                    $this->_values[] = $value;
                }
            }
            $this->_updateAttrArray($attributes, array('value' => $value));
        }
        $this->_optGroups[$optgroup]['options'][] = array('text' => $text, 'attr' => $attributes);
    }

    
    function toHtml()
    {
        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        } else {
            $tabs    = $this->_getTabs();
            $strHtml = '';

            if ($this->getComment() != '') {
                $strHtml .= $tabs . '<!-- ' . $this->getComment() . " //-->\n";
            }

            if (!$this->getMultiple()) {
                $attrString = $this->_getAttrString($this->_attributes);
            } else {
                $myName = $this->getName();
                $this->setName($myName . '[]');
                $attrString = $this->_getAttrString($this->_attributes);
                $this->setName($myName);
            }
            $strHtml .= $tabs;
            if ($this->_hiddenLabel){
                $this->_generateId();
                $strHtml .= '<label class="accesshide" for="'.$this->getAttribute('id').'" >'.
                            $this->getLabel().'</label>';
            }
            $strHtml .=  '<select' . $attrString . ">\n";
            if ($this->showchoose) {
                $strHtml .= $tabs . "\t\t<option value=\"\">" . get_string('choose') . "...</option>\n";
            }
            foreach ($this->_optGroups as $optGroup) {
                if (empty($optGroup['options'])) {
                                        continue;
                }
                $strHtml .= $tabs . "\t<optgroup" . ($this->_getAttrString($optGroup['attr'])) . '>';
                foreach ($optGroup['options'] as $option){
                    if (is_array($this->_values) && in_array((string)$option['attr']['value'], $this->_values)) {
                        $this->_updateAttrArray($option['attr'], array('selected' => 'selected'));
                    }
                    $strHtml .= $tabs . "\t\t<option" . $this->_getAttrString($option['attr']) . '>' .
                                $option['text'] . "</option>\n";
                }
                $strHtml .= $tabs . "\t</optgroup>\n";
            }
            return $strHtml . $tabs . '</select>';
        }
    }

    
    function getFrozenHtml()
    {
        $value = array();
        if (is_array($this->_values)) {
            foreach ($this->_values as $key => $val) {
                foreach ($this->_optGroups as $optGroup) {
                    if (empty($optGroup['options'])) {
                        continue;
                    }
                    for ($i = 0, $optCount = count($optGroup['options']); $i < $optCount; $i++) {
                        if ((string)$val == (string)$optGroup['options'][$i]['attr']['value']) {
                            $value[$key] = $optGroup['options'][$i]['text'];
                            break;
                        }
                    }
                }
            }
        }
        $html = empty($value)? '&nbsp;': join('<br />', $value);
        if ($this->_persistantFreeze) {
            $name = $this->getPrivateName();
                        if (1 == count($value)) {
                $id     = $this->getAttribute('id');
                $idAttr = isset($id)? array('id' => $id): array();
            } else {
                $idAttr = array();
            }
            foreach ($value as $key => $item) {
                $html .= '<input' . $this->_getAttrString(array(
                             'type'  => 'hidden',
                             'name'  => $name,
                             'value' => $this->_values[$key]
                         ) + $idAttr) . ' />';
            }
        }
        return $html;
    }

   
    function exportValue(&$submitValues, $assoc = false)
    {
        if (empty($this->_optGroups)) {
            return $this->_prepareValue(null, $assoc);
        }

        $value = $this->_findValue($submitValues);
        if (is_null($value)) {
            $value = $this->getValue();
        }
        $value = (array)$value;

        $cleaned = array();
        foreach ($value as $v) {
            foreach ($this->_optGroups as $optGroup){
                if (empty($optGroup['options'])) {
                    continue;
                }
                foreach ($optGroup['options'] as $option) {
                    if ((string)$option['attr']['value'] === (string)$v) {
                        $cleaned[] = (string)$option['attr']['value'];
                        break;
                    }
                }
            }
        }

        if (empty($cleaned)) {
            return $this->_prepareValue(null, $assoc);
        }
        if ($this->getMultiple()) {
            return $this->_prepareValue($cleaned, $assoc);
        } else {
            return $this->_prepareValue($cleaned[0], $assoc);
        }
    }

    
    function onQuickFormEvent($event, $arg, &$caller)
    {
        if ('updateValue' == $event) {
            $value = $this->_findValue($caller->_constantValues);
            if (null === $value) {
                $value = $this->_findValue($caller->_submitValues);
                                                if (null === $value && (!$caller->isSubmitted() || !$this->getMultiple())) {
                    $value = $this->_findValue($caller->_defaultValues);
                }
            }
            if (null !== $value) {
                $this->setValue($value);
            }
            return true;
        } else {
            return parent::onQuickFormEvent($event, $arg, $caller);
        }
    }

    
    function setHiddenLabel($hiddenLabel){
        $this->_hiddenLabel = $hiddenLabel;
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
