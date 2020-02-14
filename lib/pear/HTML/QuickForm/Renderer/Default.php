<?php


require_once('HTML/QuickForm/Renderer.php');


class HTML_QuickForm_Renderer_Default extends HTML_QuickForm_Renderer
{
   
    var $_html;

   
    var $_headerTemplate = 
        "\n\t<tr>\n\t\t<td style=\"white-space: nowrap; background-color: #CCCCCC;\" align=\"left\" valign=\"top\" colspan=\"2\"><b>{header}</b></td>\n\t</tr>";

   
    var $_elementTemplate = 
        "\n\t<tr>\n\t\t<td align=\"right\" valign=\"top\"><!-- BEGIN required --><span style=\"color: #ff0000\">*</span><!-- END required --><b>{label}</b></td>\n\t\t<td valign=\"top\" align=\"left\"><!-- BEGIN error --><span style=\"color: #ff0000\">{error}</span><br /><!-- END error -->\t{element}</td>\n\t</tr>";

   
    var $_formTemplate = 
        "\n<form{attributes}>\n<div>\n{hidden}<table border=\"0\">\n{content}\n</table>\n</div>\n</form>";

   
    var $_requiredNoteTemplate = 
        "\n\t<tr>\n\t\t<td></td>\n\t<td align=\"left\" valign=\"top\">{requiredNote}</td>\n\t</tr>";

   
    var $_templates = array();

   
    var $_groupWraps = array();

   
    var $_groupTemplates = array();

   
    var $_inGroup = false;

   
    var $_groupElements = array();

   
    var $_groupElementTemplate = '';

   
    var $_groupWrap = '';

   
    var $_groupTemplate = '';
    
   
    var $_hiddenHtml = '';

   
    public function __construct() {
        parent::__construct();
    } 
    
    public function HTML_QuickForm_Renderer_Default() {
        debugging('Use of class name as constructor is deprecated', DEBUG_DEVELOPER);
        self::__construct();
    } 
   
    function toHtml()
    {
                        return $this->_hiddenHtml . $this->_html;
    }     
   
    function startForm(&$form)
    {
        $this->_html = '';
        $this->_hiddenHtml = '';
    } 
   
    function finishForm(&$form)
    {
                if (!empty($form->_required) && !$form->_freezeAll) {
            $this->_html .= str_replace('{requiredNote}', $form->getRequiredNote(), $this->_requiredNoteTemplate);
        }
                $html = str_replace('{attributes}', $form->getAttributes(true), $this->_formTemplate);
        if (strpos($this->_formTemplate, '{hidden}')) {
            $html = str_replace('{hidden}', $this->_hiddenHtml, $html);
        } else {
            $this->_html .= $this->_hiddenHtml;
        }
        $this->_hiddenHtml = '';
        $this->_html = str_replace('{content}', $this->_html, $html);
                if ('' != ($script = $form->getValidationScript())) {
            $this->_html = $script . "\n" . $this->_html;
        }
    }       
   
    function renderHeader(&$header)
    {
        $name = $header->getName();
        if (!empty($name) && isset($this->_templates[$name])) {
            $this->_html .= str_replace('{header}', $header->toHtml(), $this->_templates[$name]);
        } else {
            $this->_html .= str_replace('{header}', $header->toHtml(), $this->_headerTemplate);
        }
    } 
   
    function _prepareTemplate($name, $label, $required, $error)
    {
        if (is_array($label)) {
            $nameLabel = array_shift($label);
        } else {
            $nameLabel = $label;
        }
        if (isset($this->_templates[$name])) {
            $html = str_replace('{label}', $nameLabel, $this->_templates[$name]);
        } else {
            $html = str_replace('{label}', $nameLabel, $this->_elementTemplate);
        }
        if ($required) {
            $html = str_replace('<!-- BEGIN required -->', '', $html);
            $html = str_replace('<!-- END required -->', '', $html);
        } else {
            $html = preg_replace("/([ \t\n\r]*)?<!-- BEGIN required -->(\s|\S)*<!-- END required -->([ \t\n\r]*)?/iU", '', $html);
        }
        if (isset($error)) {
            $html = str_replace('{error}', $error, $html);
            $html = str_replace('<!-- BEGIN error -->', '', $html);
            $html = str_replace('<!-- END error -->', '', $html);
        } else {
            $html = preg_replace("/([ \t\n\r]*)?<!-- BEGIN error -->(\s|\S)*<!-- END error -->([ \t\n\r]*)?/iU", '', $html);
        }
        if (is_array($label)) {
            foreach($label as $key => $text) {
                $key  = is_int($key)? $key + 2: $key;
                $html = str_replace("{label_{$key}}", $text, $html);
                $html = str_replace("<!-- BEGIN label_{$key} -->", '', $html);
                $html = str_replace("<!-- END label_{$key} -->", '', $html);
            }
        }
        if (strpos($html, '{label_')) {
            $html = preg_replace('/\s*<!-- BEGIN label_(\S+) -->.*<!-- END label_\1 -->\s*/i', '', $html);
        }
        return $html;
    } 
   
    function renderElement(&$element, $required, $error)
    {
        if (!$this->_inGroup) {
            $html = $this->_prepareTemplate($element->getName(), $element->getLabel(), $required, $error);
            $this->_html .= str_replace('{element}', $element->toHtml(), $html);

        } elseif (!empty($this->_groupElementTemplate)) {
            $html = str_replace('{label}', $element->getLabel(), $this->_groupElementTemplate);
            if ($required) {
                $html = str_replace('<!-- BEGIN required -->', '', $html);
                $html = str_replace('<!-- END required -->', '', $html);
            } else {
                $html = preg_replace("/([ \t\n\r]*)?<!-- BEGIN required -->(\s|\S)*<!-- END required -->([ \t\n\r]*)?/iU", '', $html);
            }
            $this->_groupElements[] = str_replace('{element}', $element->toHtml(), $html);

        } else {
            $this->_groupElements[] = $element->toHtml();
        }
    }    
   
    function renderHidden(&$element)
    {
        $this->_hiddenHtml .= $element->toHtml() . "\n";
    } 
   
    function renderHtml(&$data)
    {
        $this->_html .= $data->toHtml();
    } 
   
    function startGroup(&$group, $required, $error)
    {
        $name = $group->getName();
        $this->_groupTemplate        = $this->_prepareTemplate($name, $group->getLabel(), $required, $error);
        $this->_groupElementTemplate = empty($this->_groupTemplates[$name])? '': $this->_groupTemplates[$name];
        $this->_groupWrap            = empty($this->_groupWraps[$name])? '': $this->_groupWraps[$name];
        $this->_groupElements        = array();
        $this->_inGroup              = true;
    } 
   
    function finishGroup(&$group)
    {
        $separator = $group->_separator;
        if (is_array($separator)) {
            $count = count($separator);
            $html  = '';
            for ($i = 0; $i < count($this->_groupElements); $i++) {
                $html .= (0 == $i? '': $separator[($i - 1) % $count]) . $this->_groupElements[$i];
            }
        } else {
            if (is_null($separator)) {
                $separator = '&nbsp;';
            }
            $html = implode((string)$separator, $this->_groupElements);
        }
        if (!empty($this->_groupWrap)) {
            $html = str_replace('{content}', $html, $this->_groupWrap);
        }
        $this->_html   .= str_replace('{element}', $html, $this->_groupTemplate);
        $this->_inGroup = false;
    } 
    
    function setElementTemplate($html, $element = null)
    {
        if (is_null($element)) {
            $this->_elementTemplate = $html;
        } else {
            $this->_templates[$element] = $html;
        }
    } 

    
    function setGroupTemplate($html, $group)
    {
        $this->_groupWraps[$group] = $html;
    } 
    
    function setGroupElementTemplate($html, $group)
    {
        $this->_groupTemplates[$group] = $html;
    } 
    
    function setHeaderTemplate($html)
    {
        $this->_headerTemplate = $html;
    } 
    
    function setFormTemplate($html)
    {
        $this->_formTemplate = $html;
    } 
    
    function setRequiredNoteTemplate($html)
    {
        $this->_requiredNoteTemplate = $html;
    } 
    
    function clearAllTemplates()
    {
        $this->setElementTemplate('{element}');
        $this->setFormTemplate("\n\t<form{attributes}>{content}\n\t</form>\n");
        $this->setRequiredNoteTemplate('');
        $this->_templates = array();
    } } ?>
