<?php



class Minify_Controller_Files extends Minify_Controller_Base {
    
    
    public function setupSources($options) {
                
        $files = $options['files'];
                if (is_object($files)) {
            $files = array($files);
        } elseif (! is_array($files)) {
            $files = (array)$files;
        }
        unset($options['files']);
        
        $sources = array();
        foreach ($files as $file) {
            if ($file instanceof Minify_Source) {
                $sources[] = $file;
                continue;
            }
            if (0 === strpos($file, '//')) {
                $file = $_SERVER['DOCUMENT_ROOT'] . substr($file, 1);
            }
            $realPath = realpath($file);
            if (is_file($realPath)) {
                $sources[] = new Minify_Source(array(
                    'filepath' => $realPath
                ));    
            } else {
                $this->log("The path \"{$file}\" could not be found (or was not a file)");
                return $options;
            }
        }
        if ($sources) {
            $this->sources = $sources;
        }
        return $options;
    }
}

