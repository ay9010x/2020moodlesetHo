<?php



defined('MOODLE_INTERNAL') || die();


class core_minify {
    
    public static function js($content) {
        global $CFG;
        require_once("$CFG->libdir/minify/lib/JSMinPlus.php");

        try {
            ob_start();             $compressed = JSMinPlus::minify($content);
            if ($compressed !== false) {
                ob_end_clean();
                return $compressed;
            }
            $error = ob_get_clean();

        } catch (Exception $e) {
            ob_end_clean();
            $error = $e->getMessage();
        }

        $return = <<<EOD

try {console.log('Error: Minimisation of JavaScript failed!');} catch (e) {}

// Error: $error
// Problem detected during JavaScript minimisation, please review the following code
// =================================================================================


EOD;

        return $return.$content;
    }

    
    public static function js_files(array $files) {
        if (empty($files)) {
            return '';
        }

        $compressed = array();
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if ($content === false) {
                $compressed[] = "\n\n// Cannot read JS file ".basename(dirname(dirname($file))).'/'.basename(dirname($file)).'/'.basename($file)."\n\n";
                continue;
            }
            $compressed[] = self::js($content);
        }

        return implode(";\n", $compressed);
    }

    
    public static function css($content) {
        global $CFG;
        require_once("$CFG->libdir/minify/lib/Minify/CSS/Compressor.php");

        $error = 'unknown';
        try {
            $compressed = Minify_CSS_Compressor::process($content);
            if ($compressed !== false) {
                return $compressed;
            }

        } catch (Exception $e) {
            $error = $e->getMessage();
        }

        $return = <<<EOD

/* Error: $error */
/* Problem detected during CSS minimisation, please review the following code */
/* ========================================================================== */


EOD;

        return $return.$content;
    }

    
    public static function css_files(array $files) {
        if (empty($files)) {
            return '';
        }

        $compressed = array();
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if ($content === false) {
                $compressed[] = "\n\n/* Cannot read CSS file ".basename(dirname(dirname($file))).'/'.basename(dirname($file)).'/'.basename($file)."*/\n\n";
                continue;
            }
            $compressed[] = self::css($content);
        }

        return implode("\n", $compressed);
    }
}
