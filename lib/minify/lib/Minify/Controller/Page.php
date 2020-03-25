<?php



class Minify_Controller_Page extends Minify_Controller_Base {
    
    
    public function setupSources($options) {
        if (isset($options['file'])) {
            $sourceSpec = array(
                'filepath' => $options['file']
            );
            $f = $options['file'];
        } else {
                        $sourceSpec = array(
                'content' => $options['content']
                ,'id' => $options['id']
            );
            $f = $options['id'];
            unset($options['content'], $options['id']);
        }
                $this->selectionId = strtr(substr($f, 1 + strlen(dirname(dirname($f)))), '/\\', ',,');

        if (isset($options['minifyAll'])) {
                        $sourceSpec['minifyOptions'] = array(
                'cssMinifier' => array('Minify_CSS', 'minify')
                ,'jsMinifier' => array('JSMin', 'minify')
            );
            unset($options['minifyAll']);
        }
        $this->sources[] = new Minify_Source($sourceSpec);
        
        $options['contentType'] = Minify::TYPE_HTML;
        return $options;
    }
}

