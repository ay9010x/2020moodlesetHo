<?php



defined('MOODLE_INTERNAL') || die();


class tool_customlang_utils {

    
    const ROUGH_NUMBER_OF_STRINGS = 16500;

    
    protected static $components = null;

    
    private function __construct() {
    }

    
    public static function list_components() {

        $list['moodle'] = 'core';

        $coresubsystems = core_component::get_core_subsystems();
        ksort($coresubsystems);         foreach ($coresubsystems as $name => $location) {
            $list[$name] = 'core_'.$name;
        }

        $plugintypes = core_component::get_plugin_types();
        foreach ($plugintypes as $type => $location) {
            $pluginlist = core_component::get_plugin_list($type);
            foreach ($pluginlist as $name => $ununsed) {
                if ($type == 'mod') {
                                        $list[$name] = $type.'_'.$name;
                } else {
                    $list[$type.'_'.$name] = $type.'_'.$name;
                }
            }
        }

        return $list;
    }

    
    public static function checkout($lang, progress_bar $progressbar = null) {
        global $DB;

                $current = $DB->get_records('tool_customlang_components', null, 'name', 'name,version,id');
        foreach (self::list_components() as $component) {
            if (empty($current[$component])) {
                $record = new stdclass();
                $record->name = $component;
                if (!$version = get_component_version($component)) {
                    $record->version = null;
                } else {
                    $record->version = $version;
                }
                $DB->insert_record('tool_customlang_components', $record);
            } elseif ($version = get_component_version($component)) {
                if (is_null($current[$component]->version) or ($version > $current[$component]->version)) {
                    $DB->set_field('tool_customlang_components', 'version', $version, array('id' => $current[$component]->id));
                }
            }
        }
        unset($current);

                $done = 0;
        $strinprogress = get_string('checkoutinprogress', 'tool_customlang');

                $stringman  = get_string_manager();
        $components = $DB->get_records('tool_customlang_components');
        foreach ($components as $component) {
            $sql = "SELECT stringid, id, lang, componentid, original, master, local, timemodified, timecustomized, outdated, modified
                      FROM {tool_customlang} s
                     WHERE lang = ? AND componentid = ?
                  ORDER BY stringid";
            $current = $DB->get_records_sql($sql, array($lang, $component->id));
            $english = $stringman->load_component_strings($component->name, 'en', true, true);
            if ($lang == 'en') {
                $master =& $english;
            } else {
                $master = $stringman->load_component_strings($component->name, $lang, true, true);
            }
            $local = $stringman->load_component_strings($component->name, $lang, true, false);

            foreach ($english as $stringid => $stringoriginal) {
                $stringmaster = isset($master[$stringid]) ? $master[$stringid] : null;
                $stringlocal = isset($local[$stringid]) ? $local[$stringid] : null;
                $now = time();

                if (!is_null($progressbar)) {
                    $done++;
                    $donepercent = floor(min($done, self::ROUGH_NUMBER_OF_STRINGS) / self::ROUGH_NUMBER_OF_STRINGS * 100);
                    $progressbar->update_full($donepercent, $strinprogress);
                }

                if (isset($current[$stringid])) {
                    $needsupdate     = false;
                    $currentoriginal = $current[$stringid]->original;
                    $currentmaster   = $current[$stringid]->master;
                    $currentlocal    = $current[$stringid]->local;

                    if ($currentoriginal !== $stringoriginal or $currentmaster !== $stringmaster) {
                        $needsupdate = true;
                        $current[$stringid]->original       = $stringoriginal;
                        $current[$stringid]->master         = $stringmaster;
                        $current[$stringid]->timemodified   = $now;
                        $current[$stringid]->outdated       = 1;
                    }

                    if ($stringmaster !== $stringlocal) {
                        $needsupdate = true;
                        $current[$stringid]->local          = $stringlocal;
                        $current[$stringid]->timecustomized = $now;
                    }

                    if ($needsupdate) {
                        $DB->update_record('tool_customlang', $current[$stringid]);
                        continue;
                    }

                } else {
                    $record                 = new stdclass();
                    $record->lang           = $lang;
                    $record->componentid    = $component->id;
                    $record->stringid       = $stringid;
                    $record->original       = $stringoriginal;
                    $record->master         = $stringmaster;
                    $record->timemodified   = $now;
                    $record->outdated       = 0;
                    if ($stringmaster !== $stringlocal) {
                        $record->local          = $stringlocal;
                        $record->timecustomized = $now;
                    } else {
                        $record->local          = null;
                        $record->timecustomized = null;
                    }

                    $DB->insert_record('tool_customlang', $record);
                }
            }
        }

        if (!is_null($progressbar)) {
            $progressbar->update_full(100, get_string('checkoutdone', 'tool_customlang'));
        }
    }

    
    public static function checkin($lang) {
        global $DB, $USER, $CFG;
        require_once($CFG->libdir.'/filelib.php');

        if ($lang !== clean_param($lang, PARAM_LANG)) {
            return false;
        }

                $sql = "SELECT s.*, c.name AS component
                  FROM {tool_customlang} s
                  JOIN {tool_customlang_components} c ON s.componentid = c.id
                 WHERE s.lang = ?
                       AND (s.local IS NOT NULL OR s.modified = 1)
              ORDER BY componentid, stringid";
        $strings = $DB->get_records_sql($sql, array($lang));

        $files = array();
        foreach ($strings as $string) {
            if (!is_null($string->local)) {
                $files[$string->component][$string->stringid] = $string->local;
            }
        }

        fulldelete(self::get_localpack_location($lang));
        foreach ($files as $component => $strings) {
            self::dump_strings($lang, $component, $strings);
        }

        $DB->set_field_select('tool_customlang', 'modified', 0, 'lang = ?', array($lang));
        $sm = get_string_manager();
        $sm->reset_caches();
    }

    
    protected static function get_localpack_location($lang) {
        global $CFG;

        return $CFG->langlocalroot.'/'.$lang.'_local';
    }

    
    protected static function dump_strings($lang, $component, $strings) {
        global $CFG;

        if ($lang !== clean_param($lang, PARAM_LANG)) {
            debugging('Unable to dump local strings for non-installed language pack .'.s($lang));
            return false;
        }
        if ($component !== clean_param($component, PARAM_COMPONENT)) {
            throw new coding_exception('Incorrect component name');
        }
        if (!$filename = self::get_component_filename($component)) {
            debugging('Unable to find the filename for the component '.s($component));
            return false;
        }
        if ($filename !== clean_param($filename, PARAM_FILE)) {
            throw new coding_exception('Incorrect file name '.s($filename));
        }
        list($package, $subpackage) = core_component::normalize_component($component);
        $packageinfo = " * @package    $package";
        if (!is_null($subpackage)) {
            $packageinfo .= "\n * @subpackage $subpackage";
        }
        $filepath = self::get_localpack_location($lang);
        $filepath = $filepath.'/'.$filename;
        if (!is_dir(dirname($filepath))) {
            check_dir_exists(dirname($filepath));
        }

        if (!$f = fopen($filepath, 'w')) {
            debugging('Unable to write '.s($filepath));
            return false;
        }
        fwrite($f, <<<EOF
<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Local language pack from $CFG->wwwroot
 *
$packageinfo
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


EOF
        );

        foreach ($strings as $stringid => $text) {
            if ($stringid !== clean_param($stringid, PARAM_STRINGID)) {
                debugging('Invalid string identifier '.s($stringid));
                continue;
            }
            fwrite($f, '$string[\'' . $stringid . '\'] = ');
            fwrite($f, var_export($text, true));
            fwrite($f, ";\n");
        }
        fclose($f);
        @chmod($filepath, $CFG->filepermissions);
    }

    
    protected static function get_component_filename($component) {
        if (is_null(self::$components)) {
            self::$components = self::list_components();
        }
        $return = false;
        foreach (self::$components as $legacy => $normalized) {
            if ($component === $normalized) {
                $return = $legacy.'.php';
                break;
            }
        }
        return $return;
    }

    
    public static function get_count_of_modified($lang) {
        global $DB;

        return $DB->count_records('tool_customlang', array('lang'=>$lang, 'modified'=>1));
    }

    
    public static function save_filter(stdclass $data, stdclass $persistant) {
        if (!isset($persistant->tool_customlang_filter)) {
            $persistant->tool_customlang_filter = array();
        }
        foreach ($data as $key => $value) {
            if ($key !== 'submit') {
                $persistant->tool_customlang_filter[$key] = serialize($value);
            }
        }
    }

    
    public static function load_filter(stdclass $persistant) {
        $data = new stdclass();
        if (isset($persistant->tool_customlang_filter)) {
            foreach ($persistant->tool_customlang_filter as $key => $value) {
                $data->{$key} = unserialize($value);
            }
        }
        return $data;
    }
}


class tool_customlang_menu implements renderable {

    
    protected $items = array();

    public function __construct(array $items = array()) {
        global $CFG;

        foreach ($items as $itemkey => $item) {
            $this->add_item($itemkey, $item['title'], $item['url'], empty($item['method']) ? 'post' : $item['method']);
        }
    }

    
    public function get_items() {
        return $this->items;
    }

    
    public function add_item($key, $title, moodle_url $url, $method) {
        if (isset($this->items[$key])) {
            throw new coding_exception('Menu item already exists');
        }
        if (empty($title) or empty($key)) {
            throw new coding_exception('Empty title or item key not allowed');
        }
        $item = new stdclass();
        $item->title = $title;
        $item->url = $url;
        $item->method = $method;
        $this->items[$key] = $item;
    }
}


class tool_customlang_translator implements renderable {

    
    const PERPAGE = 100;

    
    public $numofrows = 0;

    
    public $handler;

    
    public $lang;

    
    public $currentpage = 0;

    
    public $strings = array();

    
    protected $filter;

    public function __construct(moodle_url $handler, $lang, $filter, $currentpage = 0) {
        global $DB;

        $this->handler      = $handler;
        $this->lang         = $lang;
        $this->filter       = $filter;
        $this->currentpage  = $currentpage;

        if (empty($filter) or empty($filter->component)) {
                        $this->currentpage = 1;
            return;
        }

        list($insql, $inparams) = $DB->get_in_or_equal($filter->component, SQL_PARAMS_NAMED);

        $csql = "SELECT COUNT(*)";
        $fsql = "SELECT s.*, c.name AS component";
        $sql  = "  FROM {tool_customlang_components} c
                   JOIN {tool_customlang} s ON s.componentid = c.id
                  WHERE s.lang = :lang
                        AND c.name $insql";

        $params = array_merge(array('lang' => $lang), $inparams);

        if (!empty($filter->customized)) {
            $sql .= "   AND s.local IS NOT NULL";
        }

        if (!empty($filter->modified)) {
            $sql .= "   AND s.modified = 1";
        }

        if (!empty($filter->stringid)) {
            $sql .= "   AND s.stringid = :stringid";
            $params['stringid'] = $filter->stringid;
        }

        if (!empty($filter->substring)) {
            $sql .= "   AND (".$DB->sql_like('s.original', ':substringoriginal', false)." OR
                             ".$DB->sql_like('s.master', ':substringmaster', false)." OR
                             ".$DB->sql_like('s.local', ':substringlocal', false).")";
            $params['substringoriginal'] = '%'.$filter->substring.'%';
            $params['substringmaster']   = '%'.$filter->substring.'%';
            $params['substringlocal']    = '%'.$filter->substring.'%';
        }

        if (!empty($filter->helps)) {
            $sql .= "   AND ".$DB->sql_like('s.stringid', ':help', false);             $params['help'] = '%\_help';
        } else {
            $sql .= "   AND ".$DB->sql_like('s.stringid', ':link', false, true, true);             $params['link'] = '%\_link';
        }

        $osql = " ORDER BY c.name, s.stringid";

        $this->numofrows = $DB->count_records_sql($csql.$sql, $params);
        $this->strings = $DB->get_records_sql($fsql.$sql.$osql, $params, ($this->currentpage) * self::PERPAGE, self::PERPAGE);
    }
}
