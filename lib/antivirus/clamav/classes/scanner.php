<?php



namespace antivirus_clamav;

defined('MOODLE_INTERNAL') || die();


class scanner extends \core\antivirus\scanner {
    
    public function is_configured() {
        return (bool)$this->get_config('pathtoclam');
    }
    
    public function scan_file($file, $filename, $deleteinfected) {
        global $CFG;

        if (!is_readable($file)) {
                        debugging('File is not readable.');
            return;
        }

                list($return, $notice) = $this->scan_file_execute_commandline($file);

        if ($return == 0) {
                        return;
        } else if ($return == 1) {
                        if ($deleteinfected) {
                unlink($file);
            }
            throw new \core\antivirus\scanner_exception('virusfounduser', '', array('filename' => $filename));
        } else {
                        $this->message_admins($notice);
            if ($this->get_config('clamfailureonupload') === 'actlikevirus') {
                if ($deleteinfected) {
                    unlink($file);
                }
                throw new \core\antivirus\scanner_exception('virusfounduser', '', array('filename' => $filename));
            } else {
                return;
            }
        }
    }

    
    private function get_clam_error_code($returncode) {
        $returncodes = array();
        $returncodes[0] = 'No virus found.';
        $returncodes[1] = 'Virus(es) found.';
        $returncodes[2] = ' An error occured';                 $returncodes[40] = 'Unknown option passed.';
        $returncodes[50] = 'Database initialization error.';
        $returncodes[52] = 'Not supported file type.';
        $returncodes[53] = 'Can\'t open directory.';
        $returncodes[54] = 'Can\'t open file. (ofm)';
        $returncodes[55] = 'Error reading file. (ofm)';
        $returncodes[56] = 'Can\'t stat input file / directory.';
        $returncodes[57] = 'Can\'t get absolute path name of current working directory.';
        $returncodes[58] = 'I/O error, please check your filesystem.';
        $returncodes[59] = 'Can\'t get information about current user from /etc/passwd.';
        $returncodes[60] = 'Can\'t get information about user \'clamav\' (default name) from /etc/passwd.';
        $returncodes[61] = 'Can\'t fork.';
        $returncodes[63] = 'Can\'t create temporary files/directories (check permissions).';
        $returncodes[64] = 'Can\'t write to temporary directory (please specify another one).';
        $returncodes[70] = 'Can\'t allocate and clear memory (calloc).';
        $returncodes[71] = 'Can\'t allocate memory (malloc).';
        if (isset($returncodes[$returncode])) {
            return $returncodes[$returncode];
        }
        return get_string('unknownerror', 'antivirus_clamav');
    }

    
    public function scan_file_execute_commandline($file) {
        $pathtoclam = trim($this->get_config('pathtoclam'));

        if (!file_exists($pathtoclam) or !is_executable($pathtoclam)) {
                        $notice = get_string('invalidpathtoclam', 'antivirus_clamav', $pathtoclam);
            return array(-1, $notice);
        }

        $clamparam = ' --stdout ';
                                                if (basename($pathtoclam) == 'clamdscan') {
            $clamparam .= '--fdpass ';
        }
                $cmd = escapeshellcmd($pathtoclam).$clamparam.escapeshellarg($file);
        exec($cmd, $output, $return);
        $notice = '';
        if ($return > 1) {
            $notice = get_string('clamfailed', 'antivirus_clamav', $this->get_clam_error_code($return));
            $notice .= "\n\n". implode("\n", $output);
        }

        return array($return, $notice);
    }
}
