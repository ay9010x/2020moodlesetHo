<?php



namespace tool_filetypes;

defined('MOODLE_INTERNAL') || die();


class utils {
    
    public static function is_extension_invalid($extension, $oldextension = '') {
        $extension = trim($extension);
        if ($extension === '' || $extension[0] === '.') {
            return true;
        }

        $mimeinfo = get_mimetypes_array();
        if ($oldextension !== '') {
            unset($mimeinfo[$oldextension]);
        }

        return array_key_exists($extension, $mimeinfo);
    }

    
    public static function is_defaulticon_allowed($mimetype, $oldextension = '') {
        $mimeinfo = get_mimetypes_array();
        if ($oldextension !== '') {
            unset($mimeinfo[$oldextension]);
        }
        foreach ($mimeinfo as $extension => $values) {
            if ($values['type'] !== $mimetype) {
                continue;
            }
            if (!empty($values['defaulticon'])) {
                return false;
            }
        }
        return true;
    }

    
    public static function get_icons_from_path($path) {
        $icons = array();
        if ($handle = @opendir($path)) {
            while (($file = readdir($handle)) !== false) {
                $matches = array();
                if (preg_match('~(.+?)(?:-24|-32|-48|-64|-72|-80|-96|-128|-256)?\.(?:gif|png)$~',
                        $file, $matches)) {
                    $key = $matches[1];
                    $icons[$key] = $key;
                }
            }
            closedir($handle);
        }
        ksort($icons);
        return $icons;
    }

    
    public static function get_file_icons() {
        global $CFG;
        $path = $CFG->dirroot . '/pix/f';
        return self::get_icons_from_path($path);
    }
}
