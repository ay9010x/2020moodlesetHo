<?php




defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/resource/backup/moodle2/backup_resource_stepslib.php');


class backup_resource_activity_task extends backup_activity_task {

    
    protected static $resourceoldexists = null;

    
    protected function define_my_settings() {
    }

    
    protected function define_my_steps() {
        $this->add_step(new backup_resource_activity_structure_step('resource_structure', 'resource.xml'));
    }

    
    static public function encode_content_links($content) {
        global $CFG, $DB;

        $base = preg_quote($CFG->wwwroot,"/");

                $search="/(".$base."\/mod\/resource\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@RESOURCEINDEX*$2@$', $content);

                $search = "/(".$base."\/mod\/resource\/view.php\?id\=)([0-9]+)/";
                $search2 = "/(".$base."\/mod\/resource\/view.php\?r\=)([0-9]+)/";

                if (static::$resourceoldexists === null) {
            static::$resourceoldexists = $DB->record_exists('resource_old', array());
        }

                        if (static::$resourceoldexists) {
                        $result = preg_match_all($search, $content, $matches, PREG_PATTERN_ORDER);

                        if ($result) {
                list($insql, $params) = $DB->get_in_or_equal($matches[2]);
                $oldrecs = $DB->get_records_select('resource_old', "cmid $insql", $params, '', 'cmid, newmodule');

                for ($i = 0; $i < count($matches[0]); $i++) {
                    $cmid = $matches[2][$i];
                    if (isset($oldrecs[$cmid])) {
                                                $replace = '$@' . strtoupper($oldrecs[$cmid]->newmodule) . 'VIEWBYID*' . $cmid . '@$';
                    } else {
                                                $replace = '$@RESOURCEVIEWBYID*'.$cmid.'@$';
                    }
                    $content = str_replace($matches[0][$i], $replace, $content);
                }
            }

            $matches = null;
            $result = preg_match_all($search2, $content, $matches, PREG_PATTERN_ORDER);

                        if (!$result) {
                return $content;
            }
                        list($insql, $params) = $DB->get_in_or_equal($matches[2]);
            $oldrecs = $DB->get_records_select('resource_old', "oldid $insql", $params, '', 'oldid, cmid, newmodule');

            for ($i = 0; $i < count($matches[0]); $i++) {
                $recordid = $matches[2][$i];
                if (isset($oldrecs[$recordid])) {
                                        $replace = '$@' . strtoupper($oldrecs[$recordid]->newmodule) . 'VIEWBYID*' . $oldrecs[$recordid]->cmid . '@$';
                    $content = str_replace($matches[0][$i], $replace, $content);
                }
            }
        } else {
            $content = preg_replace($search, '$@RESOURCEVIEWBYID*$2@$', $content);
        }
        return $content;
    }
}
