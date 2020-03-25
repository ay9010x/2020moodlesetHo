<?php




class generate_documentation extends XMLDBAction {

    
    function init() {
        parent::init();

                $this->sesskey_protected = false; 
                $this->loadStrings(array(
            'backtomainview' => 'tool_xmldb',
            'documentationintro' => 'tool_xmldb'
        ));
    }

    
    function invoke() {
        parent::invoke();

        $result = true;

                $this->does_generate = ACTION_GENERATE_HTML;

                global $CFG, $XMLDB;

        
                $dirpath = required_param('dir', PARAM_PATH);
        $dirpath = $CFG->dirroot . $dirpath;
        $path = $dirpath.'/install.xml';
        if(!file_exists($path) || !is_readable($path)) {
            return false;
        }

                $b = ' <p class="centerpara buttons">';
        $b .= '&nbsp;<a href="index.php?action=main_view#lastused">[' . $this->str['backtomainview'] . ']</a>';
        $b .= '</p>';
        $this->output=$b;

        $c = ' <p class="centerpara">';
        $c .= $this->str['documentationintro'];
        $c .= '</p>';
        $this->output.=$c;

        if(class_exists('XSLTProcessor')) {
                        $doc = new DOMDocument();
            $xsl = new XSLTProcessor();

            $doc->load(dirname(__FILE__).'/xmldb.xsl');
            $xsl->importStyleSheet($doc);

            $doc->load($path);
            $this->output.=$xsl->transformToXML($doc);
            $this->output.=$b;
        } else {
            $this->output.=get_string('extensionrequired','tool_xmldb','xsl');
        }

                if ($this->getPostAction() && $result) {
            return $this->launch($this->getPostAction());
        }

        return $result;
    }
}

