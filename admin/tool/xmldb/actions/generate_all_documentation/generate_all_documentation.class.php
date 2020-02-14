<?php




class generate_all_documentation extends XMLDBAction {

    
    function init() {
        parent::init();

                $this->sesskey_protected = false; 
                $this->loadStrings(array(
            'backtomainview' => 'tool_xmldb',
            'documentationintro' => 'tool_xmldb',
            'docindex' => 'tool_xmldb'
        ));
    }

    
    function invoke() {
        parent::invoke();

        $result = true;

                $this->does_generate = ACTION_GENERATE_HTML;

                global $CFG, $XMLDB;

        
                $b = ' <p class="centerpara buttons">';
        $b .= '&nbsp;<a href="index.php?action=main_view#lastused">[' . $this->str['backtomainview'] . ']</a>';
        $b .= '</p>';
        $this->output=$b;

        $c = ' <p class="centerpara">';
        $c .= $this->str['documentationintro'];
        $c .= '</p>';
        $this->output.=$c;

        $this->docs = '';

        if(class_exists('XSLTProcessor')) {

            $doc = new DOMDocument();
            $xsl = new XSLTProcessor();

            $doc->load(dirname(__FILE__).'/../generate_documentation/xmldb.xsl');
            $xsl->importStyleSheet($doc);

            $dbdirs = get_db_directories();
            sort($dbdirs);
            $index = $this->str['docindex'] . ' ';
            foreach ($dbdirs as $path) {

                if (!file_exists($path . '/install.xml')) {
                    continue;
                }

                $dir = trim(dirname(str_replace($CFG->dirroot, '', $path)), '/');
                $index .= '<a href="#file_' . str_replace('/', '_', $dir) . '">' . $dir . '</a>, ';
                $this->docs .= '<div class="file" id="file_' . str_replace('/', '_', $dir) . '">';
                $this->docs .= '<h2>' . $dir . '</h2>';

                $doc->load($path . '/install.xml');
                $this->docs.=$xsl->transformToXML($doc);

                $this->docs .= '</div>';
            }

            $this->output .= '<div id="file_idex">' . trim($index, ' ,') . '</div>' . $this->docs;

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

