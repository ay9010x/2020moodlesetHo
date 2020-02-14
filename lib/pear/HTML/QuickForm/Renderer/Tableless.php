<?php


require_once 'HTML/QuickForm/Renderer/Default.php';


class HTML_QuickForm_Renderer_Tableless extends HTML_QuickForm_Renderer_Default
{
   
    var $_headerTemplate = 
        "\n\t\t<legend>{header}</legend>";

   
    var $_elementTemplate = 
        "\n\t\t<div class=\"qfrow\"><label class=\"qflabel\"><!-- BEGIN required --><span class=\"required\">*</span><!-- END required -->{label}</label><div class=\"qfelement<!-- BEGIN error --> error<!-- END error -->\"><!-- BEGIN error --><span class=\"error\">{error}</span><br /><!-- END error -->{element}</div></div><br />";

   
    var $_formTemplate = 
        "\n<form{attributes}>\n\t<div style=\"display: none;\">{hidden}</div>\n{content}\n</form>";

   
    var $_openFieldsetTemplate = "\n\t<fieldset{id}>";

   
    var $_openHiddenFieldsetTemplate = "\n\t<fieldset class=\"hidden\">";

   
    var $_closeFieldsetTemplate = "\n\t</fieldset>";

   
    var $_requiredNoteTemplate = 
        "\n\t\t<div class=\"qfreqnote\">{requiredNote}</div>";

   
   var $_fieldsetsOpen = 0;

   
    var $_stopFieldsetElements = array();

   
    public function __construct() {
        parent::__construct();
    } 
    
    public function HTML_QuickForm_Renderer_Tableless() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    }

   
    function renderHeader(&$header)
    {
        $name = $header->getName();
        $id = empty($name) ? '' : ' id="' . $name . '"';
        if (is_null($header->_text)) {
            $header_html = '';
        }
        elseif (!empty($name) && isset($this->_templates[$name])) {
            $header_html = str_replace('{header}', $header->toHtml(), $this->_templates[$name]);
        } else {
            $header_html = str_replace('{header}', $header->toHtml(), $this->_headerTemplate);
        }
        if ($this->_fieldsetsOpen > 0) {
            $this->_html .= $this->_closeFieldsetTemplate;
            $this->_fieldsetsOpen--;
        }
        $openFieldsetTemplate = str_replace('{id}', $id, $this->_openFieldsetTemplate);
        $this->_html .= $openFieldsetTemplate . $header_html;
        $this->_fieldsetsOpen++;
    } 
   
    function renderElement(&$element, $required, $error)
    {
                if (   in_array($element->getName(), $this->_stopFieldsetElements)
            && $this->_fieldsetsOpen > 0
           ) {
            $this->_html .= $this->_closeFieldsetTemplate;
            $this->_fieldsetsOpen--;
        }
                        if ($this->_fieldsetsOpen === 0) {
            $this->_html .= $this->_openHiddenFieldsetTemplate;
            $this->_fieldsetsOpen++;
        }
        if (!$this->_inGroup) {
            $html = $this->_prepareTemplate($element->getName(), $element->getLabel(), $required, $error);
                                    $element_html = $element->toHtml();
            if (!is_null($element->getAttribute('id'))) {
                $id = $element->getAttribute('id');
            } else {
                $id = $element->getName();
            }
            if (!empty($id) and !is_a($element, 'MoodleQuickForm_group') and !is_a($element, 'HTML_QuickForm_static')) {                 $html = str_replace('<label', '<label for="' . $id . '"', $html);
                $element_html = preg_replace('#name="' . $id . '#',
                                             'id="' . $id . '" name="' . $id . '',
                                             $element_html,
                                             1);
            }
            $this->_html .= str_replace('{element}', $element_html, $html);
        } elseif (!empty($this->_groupElementTemplate)) {
            $html = str_replace('{label}', $element->getLabel(), $this->_groupElementTemplate);
            if ($required) {
                $html = str_replace('<!-- BEGIN required -->', '', $html);
                $html = str_replace('<!-- END required -->', '', $html);
            } else {
                $html = preg_replace("/([ \t\n\r]*)?<!-- BEGIN required -->(\s|\S)*<!-- END required -->([ \t\n\r]*)?/i", '', $html);
            }
            $this->_groupElements[] = str_replace('{element}', $element->toHtml(), $html);

        } else {
            $this->_groupElements[] = $element->toHtml();
        }
    } 
   
    function startForm(&$form)
    {
        $this->_fieldsetsOpen = 0;
        parent::startForm($form);
    } 
   
    function finishForm(&$form)
    {
                if (!empty($form->_required) && !$form->_freezeAll) {
            $requiredNote = $form->getRequiredNote();
                        if ($requiredNote == '<span style="font-size:80%; color:#ff0000;">*</span><span style="font-size:80%;"> denotes required field</span>') {
                $requiredNote = '<span class="required">*</span> denotes required field';
            }
            $this->_html .= str_replace('{requiredNote}', $requiredNote, $this->_requiredNoteTemplate);
        }
                if ($this->_fieldsetsOpen > 0) {
            $this->_html .= $this->_closeFieldsetTemplate;
            $this->_fieldsetsOpen--;
        }
                $html = str_replace('{attributes}', $form->getAttributes(true), $this->_formTemplate);
        if (strpos($this->_formTemplate, '{hidden}')) {
            $html = str_replace('{hidden}', $this->_hiddenHtml, $html);
        } else {
            $this->_html .= $this->_hiddenHtml;
        }
        $this->_hiddenHtml = '';
        $this->_html = str_replace('{content}', $this->_html, $html);
        $this->_html = str_replace('></label>', '>&nbsp;</label>', $this->_html);
                if ('' != ($script = $form->getValidationScript())) {
            $this->_html = $this->_html . "\n" . $script;
        }
    } 
    
    function setOpenFieldsetTemplate($html)
    {
        $this->_openFieldsetTemplate = $html;
    } 
    
    function setOpenHiddenFieldsetTemplate($html)
    {
        $this->_openHiddenFieldsetTemplate = $html;
    } 
    
    function setCloseFieldsetTemplate($html)
    {
        $this->_closeFieldsetTemplate = $html;
    } 
    
    function addStopFieldsetElements($element)
    {
        if (is_array($element)) {
            $this->_stopFieldsetElements = array_merge($this->_stopFieldsetElements,
                                                       $element);
        } else {
            $this->_stopFieldsetElements[] = $element;
        }
    } 
} ?>
