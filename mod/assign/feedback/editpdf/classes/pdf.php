<?php



namespace assignfeedback_editpdf;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir.'/pdflib.php');
require_once($CFG->dirroot.'/mod/assign/feedback/editpdf/fpdi/fpdi.php');


class pdf extends \FPDI {

    
    protected $currentpage = 0;
    
    protected $pagecount = 0;
    
    protected $scale = 0.0;
    
    protected $imagefolder = null;
    
    protected $filename = null;

    
    const GSPATH_OK = 'ok';
    
    const GSPATH_EMPTY = 'empty';
    
    const GSPATH_DOESNOTEXIST = 'doesnotexist';
    
    const GSPATH_ISDIR = 'isdir';
    
    const GSPATH_NOTEXECUTABLE = 'notexecutable';
    
    const GSPATH_NOTESTFILE = 'notestfile';
    
    const GSPATH_ERROR = 'error';
    
    const MIN_ANNOTATION_WIDTH = 5;
    
    const MIN_ANNOTATION_HEIGHT = 5;

    
    public function combine_pdfs($pdflist, $outfilename) {

        raise_memory_limit(MEMORY_EXTRA);
        $olddebug = error_reporting(0);

        $this->setPageUnit('pt');
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);
        $this->scale = 72.0 / 100.0;
        $this->SetFont('helvetica', '', 16.0 * $this->scale);
        $this->SetTextColor(0, 0, 0);

        $totalpagecount = 0;

        foreach ($pdflist as $file) {
            $pagecount = $this->setSourceFile($file);
            $totalpagecount += $pagecount;
            for ($i = 1; $i<=$pagecount; $i++) {
                $this->create_page_from_source($i);
            }
        }

        $this->save_pdf($outfilename);
        error_reporting($olddebug);

        return $totalpagecount;
    }

    
    public function current_page() {
        return $this->currentpage;
    }

    
    public function page_count() {
        return $this->pagecount;
    }

    
    public function load_pdf($filename) {
        raise_memory_limit(MEMORY_EXTRA);
        $olddebug = error_reporting(0);

        $this->setPageUnit('pt');
        $this->scale = 72.0 / 100.0;
        $this->SetFont('helvetica', '', 16.0 * $this->scale);
        $this->SetFillColor(255, 255, 176);
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(1.0 * $this->scale);
        $this->SetTextColor(0, 0, 0);
        $this->setPrintHeader(false);
        $this->setPrintFooter(false);
        $this->pagecount = $this->setSourceFile($filename);
        $this->filename = $filename;

        error_reporting($olddebug);
        return $this->pagecount;
    }

    
    public function set_pdf($filename, $pagecount = 0) {
        if ($pagecount == 0) {
            return $this->load_pdf($filename);
        } else {
            $this->filename = $filename;
            $this->pagecount = $pagecount;
            return $pagecount;
        }
    }

    
    public function copy_page() {
        if (!$this->filename) {
            return false;
        }
        if ($this->currentpage>=$this->pagecount) {
            return false;
        }
        $this->currentpage++;
        $this->create_page_from_source($this->currentpage);
        return true;
    }

    
    protected function create_page_from_source($pageno) {
                $template = $this->importPage($pageno);
        $size = $this->getTemplateSize($template);
        $orientation = 'P';
        if ($size['w'] > $size['h']) {
            $orientation = 'L';
        }
                $this->AddPage($orientation, array($size['w'], $size['h']));
                $this->setPageOrientation($orientation, false, 0);
                $this->useTemplate($template);
    }

    
    public function copy_remaining_pages() {
        $morepages = true;
        while ($morepages) {
            $morepages = $this->copy_page();
        }
    }

    
    public function add_comment($text, $x, $y, $width, $colour = 'yellow') {
        if (!$this->filename) {
            return false;
        }
        $this->SetDrawColor(51, 51, 51);
        switch ($colour) {
            case 'red':
                $this->SetFillColor(249, 181, 179);
                break;
            case 'green':
                $this->SetFillColor(214, 234, 178);
                break;
            case 'blue':
                $this->SetFillColor(203, 217, 237);
                break;
            case 'white':
                $this->SetFillColor(255, 255, 255);
                break;
            default: 
                $this->SetFillColor(255, 236, 174);
                break;
        }

        $x *= $this->scale;
        $y *= $this->scale;
        $width *= $this->scale;
        $text = str_replace('&lt;', '<', $text);
        $text = str_replace('&gt;', '>', $text);
                        $this->MultiCell($width, 1.0, $text, 0, 'L', 0, 4, $x, $y); 
        if ($colour != 'clear') {
            $newy = $this->GetY();
                        $this->Rect($x, $y, $width, $newy - $y, 'DF');
                        $this->MultiCell($width, 1.0, $text, 0, 'L', 0, 4, $x, $y); 
        }
        return true;
    }

    
    public function add_annotation($sx, $sy, $ex, $ey, $colour = 'yellow', $type = 'line', $path, $imagefolder) {
        global $CFG;
        if (!$this->filename) {
            return false;
        }
        switch ($colour) {
            case 'yellow':
                $colourarray = array(255, 207, 53);
                break;
            case 'green':
                $colourarray = array(153, 202, 62);
                break;
            case 'blue':
                $colourarray = array(125, 159, 211);
                break;
            case 'white':
                $colourarray = array(255, 255, 255);
                break;
            case 'black':
                $colourarray = array(51, 51, 51);
                break;
            default: 
                $colour = 'red';
                $colourarray = array(239, 69, 64);
                break;
        }
        $this->SetDrawColorArray($colourarray);

        $sx *= $this->scale;
        $sy *= $this->scale;
        $ex *= $this->scale;
        $ey *= $this->scale;

        $this->SetLineWidth(3.0 * $this->scale);
        switch ($type) {
            case 'oval':
                $rx = abs($sx - $ex) / 2;
                $ry = abs($sy - $ey) / 2;
                $sx = min($sx, $ex) + $rx;
                $sy = min($sy, $ey) + $ry;

                                if ($rx < self::MIN_ANNOTATION_WIDTH) {
                    $rx = self::MIN_ANNOTATION_WIDTH;
                }
                if ($ry < self::MIN_ANNOTATION_HEIGHT) {
                    $ry = self::MIN_ANNOTATION_HEIGHT;
                }

                $this->Ellipse($sx, $sy, $rx, $ry);
                break;
            case 'rectangle':
                $w = abs($sx - $ex);
                $h = abs($sy - $ey);
                $sx = min($sx, $ex);
                $sy = min($sy, $ey);

                                if ($w < self::MIN_ANNOTATION_WIDTH) {
                    $w = self::MIN_ANNOTATION_WIDTH;
                }
                if ($h < self::MIN_ANNOTATION_HEIGHT) {
                    $h = self::MIN_ANNOTATION_HEIGHT;
                }
                $this->Rect($sx, $sy, $w, $h);
                break;
            case 'highlight':
                $w = abs($sx - $ex);
                $h = 8.0 * $this->scale;
                $sx = min($sx, $ex);
                $sy = min($sy, $ey) + ($h * 0.5);
                $this->SetAlpha(0.5, 'Normal', 0.5, 'Normal');
                $this->SetLineWidth(8.0 * $this->scale);

                                if ($w < self::MIN_ANNOTATION_WIDTH) {
                    $w = self::MIN_ANNOTATION_WIDTH;
                }

                $this->Rect($sx, $sy, $w, $h);
                $this->SetAlpha(1.0, 'Normal', 1.0, 'Normal');
                break;
            case 'pen':
                if ($path) {
                    $scalepath = array();
                    $points = preg_split('/[,:]/', $path);
                    foreach ($points as $point) {
                        $scalepath[] = intval($point) * $this->scale;
                    }

                    if (!empty($scalepath)) {
                        $this->PolyLine($scalepath, 'S');
                    }
                }
                break;
            case 'stamp':
                $imgfile = $imagefolder . '/' . clean_filename($path);
                $w = abs($sx - $ex);
                $h = abs($sy - $ey);
                $sx = min($sx, $ex);
                $sy = min($sy, $ey);

                                $this->Image($imgfile, $sx, $sy, $w, $h);
                break;
            default:                 $this->Line($sx, $sy, $ex, $ey);
                break;
        }
        $this->SetDrawColor(0, 0, 0);
        $this->SetLineWidth(1.0 * $this->scale);

        return true;
    }

    
    public function save_pdf($filename) {
        $olddebug = error_reporting(0);
        $this->Output($filename, 'F');
        error_reporting($olddebug);
    }

    
    public function set_image_folder($folder) {
        $this->imagefolder = $folder;
    }

    
    public function get_image($pageno) {
        global $CFG;

        if (!$this->filename) {
            throw new \coding_exception('Attempting to generate a page image without first setting the PDF filename');
        }

        if (!$this->imagefolder) {
            throw new \coding_exception('Attempting to generate a page image without first specifying the image output folder');
        }

        if (!is_dir($this->imagefolder)) {
            throw new \coding_exception('The specified image output folder is not a valid folder');
        }

        $imagefile = $this->imagefolder.'/image_page' . $pageno . '.png';
        $generate = true;
        if (file_exists($imagefile)) {
            if (filemtime($imagefile)>filemtime($this->filename)) {
                                $generate = false;
            }
        }

        if ($generate) {
                        $gsexec = \escapeshellarg($CFG->pathtogs);
            $imageres = \escapeshellarg(100);
            $imagefilearg = \escapeshellarg($imagefile);
            $filename = \escapeshellarg($this->filename);
            $pagenoinc = \escapeshellarg($pageno + 1);
            $command = "$gsexec -q -sDEVICE=png16m -dSAFER -dBATCH -dNOPAUSE -r$imageres -dFirstPage=$pagenoinc -dLastPage=$pagenoinc ".
                "-dDOINTERPOLATE -dGraphicsAlphaBits=4 -dTextAlphaBits=4 -sOutputFile=$imagefilearg $filename";

            $output = null;
            $result = exec($command, $output);
            if (!file_exists($imagefile)) {
                $fullerror = '<pre>'.get_string('command', 'assignfeedback_editpdf')."\n";
                $fullerror .= $command . "\n\n";
                $fullerror .= get_string('result', 'assignfeedback_editpdf')."\n";
                $fullerror .= htmlspecialchars($result) . "\n\n";
                $fullerror .= get_string('output', 'assignfeedback_editpdf')."\n";
                $fullerror .= htmlspecialchars(implode("\n",$output)) . '</pre>';
                throw new \moodle_exception('errorgenerateimage', 'assignfeedback_editpdf', '', $fullerror);
            }
        }

        return 'image_page'.$pageno.'.png';
    }

    
    public static function ensure_pdf_compatible(\stored_file $file) {
        global $CFG;

        $temparea = \make_temp_directory('assignfeedback_editpdf');
        $hash = $file->get_contenthash();         $tempsrc = $temparea . "/src-$hash.pdf";
        $tempdst = $temparea . "/dst-$hash.pdf";
        $file->copy_content_to($tempsrc); 
        $pdf = new pdf();
        $pagecount = 0;
        try {
            $pagecount = $pdf->load_pdf($tempsrc);
        } catch (\Exception $e) {
                        $pagecount = 0;
        }
        $pdf->Close(); 
        if ($pagecount > 0) {
                        return $tempsrc;
        }

        $gsexec = \escapeshellarg($CFG->pathtogs);
        $tempdstarg = \escapeshellarg($tempdst);
        $tempsrcarg = \escapeshellarg($tempsrc);
        $command = "$gsexec -q -sDEVICE=pdfwrite -dBATCH -dNOPAUSE -sOutputFile=$tempdstarg $tempsrcarg";
        exec($command);
        @unlink($tempsrc);
        if (!file_exists($tempdst)) {
                        return false;
        }

        $pdf = new pdf();
        $pagecount = 0;
        try {
            $pagecount = $pdf->load_pdf($tempdst);
        } catch (\Exception $e) {
                        $pagecount = 0;
        }
        $pdf->Close(); 
        if ($pagecount <= 0) {
            @unlink($tempdst);
                        return false;
        }

        return $tempdst;
    }

    
    public static function test_gs_path($generateimage = true) {
        global $CFG;

        $ret = (object)array(
            'status' => self::GSPATH_OK,
            'message' => null,
        );
        $gspath = $CFG->pathtogs;
        if (empty($gspath)) {
            $ret->status = self::GSPATH_EMPTY;
            return $ret;
        }
        if (!file_exists($gspath)) {
            $ret->status = self::GSPATH_DOESNOTEXIST;
            return $ret;
        }
        if (is_dir($gspath)) {
            $ret->status = self::GSPATH_ISDIR;
            return $ret;
        }
        if (!is_executable($gspath)) {
            $ret->status = self::GSPATH_NOTEXECUTABLE;
            return $ret;
        }

        if (!$generateimage) {
            return $ret;
        }

        $testfile = $CFG->dirroot.'/mod/assign/feedback/editpdf/tests/fixtures/testgs.pdf';
        if (!file_exists($testfile)) {
            $ret->status = self::GSPATH_NOTESTFILE;
            return $ret;
        }

        $testimagefolder = \make_temp_directory('assignfeedback_editpdf_test');
        @unlink($testimagefolder.'/image_page0.png'); 
        $pdf = new pdf();
        $pdf->set_pdf($testfile);
        $pdf->set_image_folder($testimagefolder);
        try {
            $pdf->get_image(0);
        } catch (\moodle_exception $e) {
            $ret->status = self::GSPATH_ERROR;
            $ret->message = $e->getMessage();
        }
        $pdf->Close(); 
        return $ret;
    }

    
    public static function send_test_image() {
        global $CFG;
        header('Content-type: image/png');
        require_once($CFG->libdir.'/filelib.php');

        $testimagefolder = \make_temp_directory('assignfeedback_editpdf_test');
        $testimage = $testimagefolder.'/image_page0.png';
        send_file($testimage, basename($testimage), 0);
        die();
    }

}

