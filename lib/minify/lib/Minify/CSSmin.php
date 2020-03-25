<?php



class Minify_CSSmin {
    
    
    public static function minify($css, $options = array()) 
    {
        $options = array_merge(array(
            'compress' => true,
            'removeCharsets' => true,
            'currentDir' => null,
            'docRoot' => $_SERVER['DOCUMENT_ROOT'],
            'prependRelativePath' => null,
            'symlinks' => array(),
        ), $options);
        
        if ($options['removeCharsets']) {
            $css = preg_replace('/@charset[^;]+;\\s*/', '', $css);
        }
        if ($options['compress']) {
            $obj = new CSSmin();
            $css = $obj->run($css);
        }
        if (! $options['currentDir'] && ! $options['prependRelativePath']) {
            return $css;
        }
        if ($options['currentDir']) {
            return Minify_CSS_UriRewriter::rewrite(
                $css
                ,$options['currentDir']
                ,$options['docRoot']
                ,$options['symlinks']
            );  
        } else {
            return Minify_CSS_UriRewriter::prepend(
                $css
                ,$options['prependRelativePath']
            );
        }
    }
}
