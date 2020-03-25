<?php



class Minify_YUICompressor {

    
    public static $jarFile = null;
    
    
    public static $tempDir = null;
    
    
    public static $javaExecutable = 'java';
    
    
    public static function minifyJs($js, $options = array())
    {
        return self::_minify('js', $js, $options);
    }
    
    
    public static function minifyCss($css, $options = array())
    {
        return self::_minify('css', $css, $options);
    }
    
    private static function _minify($type, $content, $options)
    {
        self::_prepare();
        if (! ($tmpFile = tempnam(self::$tempDir, 'yuic_'))) {
            throw new Exception('Minify_YUICompressor : could not create temp file in "'.self::$tempDir.'".');
        }
        file_put_contents($tmpFile, $content);
        exec(self::_getCmd($options, $type, $tmpFile), $output, $result_code);
        unlink($tmpFile);
        if ($result_code != 0) {
            throw new Exception('Minify_YUICompressor : YUI compressor execution failed.');
        }
        return implode("\n", $output);
    }
    
    private static function _getCmd($userOptions, $type, $tmpFile)
    {
        $o = array_merge(
            array(
                'charset' => ''
                ,'line-break' => 5000
                ,'type' => $type
                ,'nomunge' => false
                ,'preserve-semi' => false
                ,'disable-optimizations' => false
	            ,'stack-size' => ''
            )
            ,$userOptions
        );
        $cmd = self::$javaExecutable
	         . (!empty($o['stack-size'])
	            ? ' -Xss' . $o['stack-size']
	            : '')
	         . ' -jar ' . escapeshellarg(self::$jarFile)
             . " --type {$type}"
             . (preg_match('/^[\\da-zA-Z0-9\\-]+$/', $o['charset'])
                ? " --charset {$o['charset']}" 
                : '')
             . (is_numeric($o['line-break']) && $o['line-break'] >= 0
                ? ' --line-break ' . (int)$o['line-break']
                : '');
        if ($type === 'js') {
            foreach (array('nomunge', 'preserve-semi', 'disable-optimizations') as $opt) {
                $cmd .= $o[$opt] 
                    ? " --{$opt}"
                    : '';
            }
        }
        return $cmd . ' ' . escapeshellarg($tmpFile);
    }
    
    private static function _prepare()
    {
        if (! is_file(self::$jarFile)) {
            throw new Exception('Minify_YUICompressor : $jarFile('.self::$jarFile.') is not a valid file.');
        }
        if (! is_readable(self::$jarFile)) {
            throw new Exception('Minify_YUICompressor : $jarFile('.self::$jarFile.') is not readable.');
        }
        if (! is_dir(self::$tempDir)) {
            throw new Exception('Minify_YUICompressor : $tempDir('.self::$tempDir.') is not a valid direcotry.');
        }
        if (! is_writable(self::$tempDir)) {
            throw new Exception('Minify_YUICompressor : $tempDir('.self::$tempDir.') is not writable.');
        }
    }
}

