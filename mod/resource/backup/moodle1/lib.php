<?php




defined('MOODLE_INTERNAL') || die();


class moodle1_mod_resource_handler extends moodle1_mod_handler {

    
    protected $fileman = null;

    
    private $successors = array();

    
    public function get_paths() {
        return array(
            new convert_path(
                'resource', '/MOODLE_BACKUP/COURSE/MODULES/MOD/RESOURCE',
                array(
                    'renamefields' => array(
                        'summary' => 'intro',
                    ),
                    'newfields' => array(
                        'introformat' => 0,
                    ),
                    'dropfields' => array(
                        'modtype',
                    ),
                )
            )
        );
    }

    
    public function process_resource(array $data, array $raw) {
        global $CFG;
        require_once("$CFG->libdir/resourcelib.php");

                if ($CFG->texteditors !== 'textarea') {
            $data['intro']       = text_to_html($data['intro'], false, false, true);
            $data['introformat'] = FORMAT_HTML;
        }

                if (!array_key_exists('popup', $data) or is_null($data['popup'])) {
            $data['popup'] = '';
        }
        if (!array_key_exists ('options', $data) or is_null($data['options'])) {
            $data['options'] = '';
        }

                if ($successor = $this->get_successor($data['type'], $data['reference'])) {
                        $instanceid = $data['id'];

                                    $resourcemodinfo  = $this->converter->get_stash('modinfo_resource');
            $successormodinfo = $this->converter->get_stash('modinfo_'.$successor->get_modname());
            $successormodinfo['instances'][$instanceid] = $resourcemodinfo['instances'][$instanceid];
            unset($resourcemodinfo['instances'][$instanceid]);
            $this->converter->set_stash('modinfo_resource', $resourcemodinfo);
            $this->converter->set_stash('modinfo_'.$successor->get_modname(), $successormodinfo);

                        $cminfo = $this->get_cminfo($instanceid);

                                                $plugin = new stdClass();
            $plugin->version = null;
            $module = $plugin;
            include $CFG->dirroot.'/mod/'.$successor->get_modname().'/version.php';
            $cminfo['version'] = $plugin->version;

                        $cminfo['modulename'] = $successor->get_modname();
            $this->converter->set_stash('cminfo_'.$cminfo['modulename'], $cminfo, $instanceid);

                        $coursecontents = $this->converter->get_stash('coursecontents');
            $coursecontents[$cminfo['id']]['modulename'] = $successor->get_modname();
            $this->converter->set_stash('coursecontents', $coursecontents);

                        return $successor->process_legacy_resource($data, $raw);
        }

        
        $resource = array();
        $resource['id']              = $data['id'];
        $resource['name']            = $data['name'];
        $resource['intro']           = $data['intro'];
        $resource['introformat']     = $data['introformat'];
        $resource['tobemigrated']    = 0;
        $resource['legacyfiles']     = RESOURCELIB_LEGACYFILES_ACTIVE;
        $resource['legacyfileslast'] = null;
        $resource['filterfiles']     = 0;
        $resource['revision']        = 1;
        $resource['timemodified']    = $data['timemodified'];

                $options = array('printintro' => 1);
        if ($data['options'] == 'frame') {
            $resource['display'] = RESOURCELIB_DISPLAY_FRAME;

        } else if ($data['options'] == 'objectframe') {
            $resource['display'] = RESOURCELIB_DISPLAY_EMBED;

        } else if ($data['options'] == 'forcedownload') {
            $resource['display'] = RESOURCELIB_DISPLAY_DOWNLOAD;

        } else if ($data['popup']) {
            $resource['display'] = RESOURCELIB_DISPLAY_POPUP;
            $rawoptions = explode(',', $data['popup']);
            foreach ($rawoptions as $rawoption) {
                list($name, $value) = explode('=', trim($rawoption), 2);
                if ($value > 0 and ($name == 'width' or $name == 'height')) {
                    $options['popup'.$name] = $value;
                    continue;
                }
            }

        } else {
            $resource['display'] = RESOURCELIB_DISPLAY_AUTO;
        }
        $resource['displayoptions'] = serialize($options);

                $instanceid     = $resource['id'];
        $currentcminfo  = $this->get_cminfo($instanceid);
        $moduleid       = $currentcminfo['id'];
        $contextid      = $this->converter->get_contextid(CONTEXT_MODULE, $moduleid);

                $this->fileman = $this->converter->get_file_manager($contextid, 'mod_resource');

                $this->fileman->filearea = 'intro';
        $this->fileman->itemid   = 0;
        $resource['intro'] = moodle1_converter::migrate_referenced_files($resource['intro'], $this->fileman);

                $reference = $data['reference'];
        if (strpos($reference, '$@FILEPHP@$') === 0) {
            $reference = str_replace(array('$@FILEPHP@$', '$@SLASH@$', '$@FORCEDOWNLOAD@$'), array('', '/', ''), $reference);
        }
        $this->fileman->filearea = 'content';
        $this->fileman->itemid   = 0;

                $curfilepath = '/';
        if ($reference) {
            $curfilepath = pathinfo('/'.$reference, PATHINFO_DIRNAME);
            if ($curfilepath != '/') {
                $curfilepath .= '/';
            }
        }
        try {
            $this->fileman->migrate_file('course_files/'.$reference, $curfilepath, null, 1);
        } catch (moodle1_convert_exception $e) {
                        $this->log('error migrating the resource main file', backup::LOG_WARNING, 'course_files/'.$reference);
        }

                $this->open_xml_writer("activities/resource_{$moduleid}/resource.xml");
        $this->xmlwriter->begin_tag('activity', array('id' => $instanceid, 'moduleid' => $moduleid,
            'modulename' => 'resource', 'contextid' => $contextid));
        $this->write_xml('resource', $resource, array('/resource/id'));
        $this->xmlwriter->end_tag('activity');
        $this->close_xml_writer();

                $this->open_xml_writer("activities/resource_{$currentcminfo['id']}/inforef.xml");
        $this->xmlwriter->begin_tag('inforef');
        $this->xmlwriter->begin_tag('fileref');
        foreach ($this->fileman->get_fileids() as $fileid) {
            $this->write_xml('file', array('id' => $fileid));
        }
        $this->xmlwriter->end_tag('fileref');
        $this->xmlwriter->end_tag('inforef');
        $this->close_xml_writer();
    }

    
    public function on_resource_end(array $data) {
        if ($successor = $this->get_successor($data['type'], $data['reference'])) {
            $successor->on_legacy_resource_end($data);
        }
    }

    
    
    protected function get_successor($type, $reference) {

        switch ($type) {
            case 'text':
            case 'html':
                $name = 'page';
                break;
            case 'directory':
                $name = 'folder';
                break;
            case 'ims':
                $name = 'imscp';
                break;
            case 'file':
                                                if (strpos($reference, '$@FILEPHP@$') === 0) {
                    $name = null;
                    break;
                }
                                if (strpos($reference, '://') or strpos($reference, '/') === 0) {
                    $name = 'url';
                } else {
                    $name = null;
                }
                break;
            default:
                throw new moodle1_convert_exception('unknown_resource_successor', $type);
        }

        if (is_null($name)) {
            return null;
        }

        if (!isset($this->successors[$name])) {
            $this->log('preparing resource successor handler', backup::LOG_DEBUG, $name);
            $class = 'moodle1_mod_'.$name.'_handler';
            $this->successors[$name] = new $class($this->converter, 'mod', $name);

                        $modnames = $this->converter->get_stash('modnameslist');
            $modnames[] = $name;
            $modnames = array_unique($modnames);             $this->converter->set_stash('modnameslist', $modnames);

                        $modinfo = $this->converter->get_stash('modinfo_resource');
            $modinfo['name'] = $name;
            $modinfo['instances'] = array();
            $this->converter->set_stash('modinfo_'.$name, $modinfo);
        }

        return $this->successors[$name];
     }
}
