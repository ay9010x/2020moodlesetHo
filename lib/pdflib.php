<?php




defined('MOODLE_INTERNAL') || die();

if (!defined('PDF_CUSTOM_FONT_PATH')) {
    
    define('PDF_CUSTOM_FONT_PATH', $CFG->dataroot.'/fonts/');
}

if (!defined('PDF_DEFAULT_FONT')) {
    
    define('PDF_DEFAULT_FONT', 'FreeSerif');
}


define('K_TCPDF_EXTERNAL_CONFIG', 1);



function tcpdf_init_k_font_path() {
    global $CFG;

    $defaultfonts = $CFG->dirroot.'/lib/tcpdf/fonts/';

    if (!defined('K_PATH_FONTS')) {
        if (is_dir(PDF_CUSTOM_FONT_PATH)) {
                                                            
                        $somestandardfiles = array('courier',  'helvetica', 'times', 'symbol', 'zapfdingbats', 'freeserif', 'freesans');
            $missing = false;
            foreach ($somestandardfiles as $file) {
                if (!file_exists(PDF_CUSTOM_FONT_PATH . $file . '.php')) {
                    $missing = true;
                    break;
                }
            }
            if ($missing) {
                define('K_PATH_FONTS', $defaultfonts);
            } else {
                define('K_PATH_FONTS', PDF_CUSTOM_FONT_PATH);
            }
        } else {
            define('K_PATH_FONTS', $defaultfonts);
        }
    }

    if (!defined('PDF_FONT_NAME_MAIN')) {
        define('PDF_FONT_NAME_MAIN', strtolower(PDF_DEFAULT_FONT));
    }
}
tcpdf_init_k_font_path();


define('K_PATH_MAIN', $CFG->dirroot.'/lib/tcpdf/');


define('K_PATH_URL', $CFG->wwwroot . '/lib/tcpdf/');


define('K_PATH_CACHE', $CFG->cachedir . '/tcpdf/');


define('K_PATH_IMAGES', $CFG->dirroot . '/');


define('K_BLANK_IMAGE', K_PATH_IMAGES . 'pix/spacer.gif');


define('K_CELL_HEIGHT_RATIO', 1.25);


define('K_SMALL_RATIO', 2/3);


define('K_TCPDF_THROW_EXCEPTION_ERROR', true);

require_once(dirname(__FILE__).'/tcpdf/tcpdf.php');


class pdf extends TCPDF {

    
    public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8') {
        make_cache_directory('tcpdf');

        parent::__construct($orientation, $unit, $format, $unicode, $encoding);

                $this->l['w_page']          = get_string('page');
        $this->l['a_meta_language'] = current_language();
        $this->l['a_meta_charset']  = 'UTF-8';
        $this->l['a_meta_dir']      = get_string('thisdirection', 'langconfig');
    }

    
    public function Output($name='doc.pdf', $dest='I') {
        $olddebug = error_reporting(0);
        $result  = parent::output($name, $dest);
        error_reporting($olddebug);
        return $result;
    }

    
    public function is_core_font_family($fontfamily) {
        return isset($this->CoreFonts[$fontfamily]);
    }

    
    public function get_font_families() {
        $families = array();
        foreach ($this->fontlist as $font) {
            if (strpos($font, 'uni2cid') === 0) {
                                continue;
            }
            if (strpos($font, 'cid0') === 0) {
                                continue;
            }
            if (substr($font, -2) === 'bi') {
                $family = substr($font, 0, -2);
                if (in_array($family, $this->fontlist)) {
                    $families[$family]['BI'] = 'BI';
                    continue;
                }
            }
            if (substr($font, -1) === 'i') {
                $family = substr($font, 0, -1);
                if (in_array($family, $this->fontlist)) {
                    $families[$family]['I'] = 'I';
                    continue;
                }
            }
            if (substr($font, -1) === 'b') {
                $family = substr($font, 0, -1);
                if (in_array($family, $this->fontlist)) {
                    $families[$family]['B'] = 'B';
                    continue;
                }
            }
                        $families[$font]['R'] = 'R';
        }

                ksort($families);
        foreach ($families as $k => $v) {
            krsort($families[$k]);
        }

        return $families;
    }
}
