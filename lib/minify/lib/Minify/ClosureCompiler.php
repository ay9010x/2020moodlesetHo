<?php



class Minify_ClosureCompiler {

    const OPTION_CHARSET = 'charset';
    const OPTION_COMPILATION_LEVEL = 'compilation_level';

    public static $isDebug = false;

    
    public static $jarFile = null;

    
    public static $tempDir = null;

    
    public static $javaExecutable = 'java';

    
    public static function minify($js, $options = array())
    {
        self::_prepare();
        if (! ($tmpFile = tempnam(self::$tempDir, 'cc_'))) {
            throw new Minify_ClosureCompiler_Exception('Minify_ClosureCompiler : could not create temp file in "'.self::$tempDir.'".');
        }
        file_put_contents($tmpFile, $js);
        $cmd = self::_getCmd($options, $tmpFile);
        exec($cmd, $output, $result_code);
        unlink($tmpFile);
        if ($result_code != 0) {
            $message = 'Minify_ClosureCompiler : Closure Compiler execution failed.';
            if (self::$isDebug) { 
                exec($cmd . ' 2>&1', $error);
                if ($error) {
                    $message .= "\nReason:\n" . join("\n", $error);
                }
            } 
            throw new Minify_ClosureCompiler_Exception($message);
        }
        return implode("\n", $output);
    }

    private static function _getCmd($userOptions, $tmpFile)
    {
        $o = array_merge(
            array(
                self::OPTION_CHARSET => 'utf-8',
                self::OPTION_COMPILATION_LEVEL => 'SIMPLE_OPTIMIZATIONS',
            ),
            $userOptions
        );
        $charsetOption = $o[self::OPTION_CHARSET];
        $cmd = self::$javaExecutable . ' -jar ' . escapeshellarg(self::$jarFile)
             . (preg_match('/^[\\da-zA-Z0-9\\-]+$/', $charsetOption)
                ? " --charset {$charsetOption}"
                : '');

        foreach (array(self::OPTION_COMPILATION_LEVEL) as $opt) {
            if ($o[$opt]) {
                $cmd .= " --{$opt} ". escapeshellarg($o[$opt]);
            }
        }
        return $cmd . ' ' . escapeshellarg($tmpFile);
    }

    private static function _prepare()
    {
        if (! is_file(self::$jarFile)) {
            throw new Minify_ClosureCompiler_Exception('Minify_ClosureCompiler : $jarFile('.self::$jarFile.') is not a valid file.');
        }
        if (! is_readable(self::$jarFile)) {
            throw new Minify_ClosureCompiler_Exception('Minify_ClosureCompiler : $jarFile('.self::$jarFile.') is not readable.');
        }
        if (! is_dir(self::$tempDir)) {
            throw new Minify_ClosureCompiler_Exception('Minify_ClosureCompiler : $tempDir('.self::$tempDir.') is not a valid direcotry.');
        }
        if (! is_writable(self::$tempDir)) {
            throw new Minify_ClosureCompiler_Exception('Minify_ClosureCompiler : $tempDir('.self::$tempDir.') is not writable.');
        }
    }
}

class Minify_ClosureCompiler_Exception extends Exception {}
