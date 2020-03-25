<?php



class Minify_CSS {
    
    
    public static function minify($css, $options = array()) 
    {
        $options = array_merge(array(
            'compress' => true,
            'removeCharsets' => true,
            'preserveComments' => true,
            'currentDir' => null,
            'docRoot' => $_SERVER['DOCUMENT_ROOT'],
            'prependRelativePath' => null,
            'symlinks' => array(),
        ), $options);
        
        if ($options['removeCharsets']) {
            $css = preg_replace('/@charset[^;]+;\\s*/', '', $css);
        }
        if ($options['compress']) {
            if (! $options['preserveComments']) {
                $css = Minify_CSS_Compressor::process($css, $options);
            } else {
                $css = Minify_CommentPreserver::process(
                    $css
                    ,array('Minify_CSS_Compressor', 'process')
                    ,array($options)
                );
            }
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
