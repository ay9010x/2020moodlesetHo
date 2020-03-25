<?php



class Minify_Controller_Groups extends Minify_Controller_Base {
    
    
    public function setupSources($options) {
                $groups = $options['groups'];
        unset($options['groups']);
        
                $pi = isset($_SERVER['ORIG_PATH_INFO'])
            ? substr($_SERVER['ORIG_PATH_INFO'], 1) 
            : (isset($_SERVER['PATH_INFO'])
                ? substr($_SERVER['PATH_INFO'], 1) 
                : false
            );
        if (false === $pi || ! isset($groups[$pi])) {
                        $this->log("Missing PATH_INFO or no group set for \"$pi\"");
            return $options;
        }
        $sources = array();
        
        $files = $groups[$pi];
                if (is_object($files)) {
            $files = array($files);
        } elseif (! is_array($files)) {
            $files = (array)$files;
        }
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

