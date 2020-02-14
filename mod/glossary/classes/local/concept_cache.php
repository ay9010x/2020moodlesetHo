<?php



namespace mod_glossary\local;
defined('MOODLE_INTERNAL') || die();


class concept_cache {
    
    public static function cm_updated(\core\event\course_module_updated $event) {
        if ($event->other['modulename'] !== 'glossary') {
            return;
        }
                concept_cache::reset_course_muc($event->courseid);
        concept_cache::reset_global_muc();
    }

    
    public static function reset_caches($phpunitreset = false) {
        if ($phpunitreset) {
            return;
        }
        $cache = \cache::make('mod_glossary', 'concepts');
        $cache->purge();
    }

    
    public static function reset_course_muc($courseid) {
        if (empty($courseid)) {
            return;
        }
        $cache = \cache::make('mod_glossary', 'concepts');
        $cache->delete((int)$courseid);
    }

    
    public static function reset_global_muc() {
        $cache = \cache::make('mod_glossary', 'concepts');
        $cache->delete(0);
    }

    
    public static function reset_glossary($glossary) {
        if (!$glossary->usedynalink) {
            return;
        }
        self::reset_course_muc($glossary->course);
        if ($glossary->globalglossary) {
            self::reset_global_muc();
        }
    }

    
    protected static function fetch_concepts(array $glossaries) {
        global $DB;

        $glossarylist = implode(',', $glossaries);

        $sql = "SELECT id, glossaryid, concept, casesensitive, 0 AS category, fullmatch
                  FROM {glossary_entries}
                 WHERE glossaryid IN ($glossarylist) AND usedynalink = 1 AND approved = 1

                 UNION

                SELECT id, glossaryid, name AS concept, 1 AS casesensitive, 1 AS category, 1 AS fullmatch
                  FROM {glossary_categories}
                 WHERE glossaryid IN ($glossarylist) AND usedynalink = 1

                UNION

                SELECT ge.id, ge.glossaryid, ga.alias AS concept, ge.casesensitive, 0 AS category, ge.fullmatch
                  FROM {glossary_alias} ga
                  JOIN {glossary_entries} ge ON (ga.entryid = ge.id)
                 WHERE ge.glossaryid IN ($glossarylist) AND ge.usedynalink = 1 AND ge.approved = 1";

        $concepts = array();
        $rs = $DB->get_recordset_sql($sql);
        foreach ($rs as $concept) {
            $currentconcept = trim(strip_tags($concept->concept));

                        $currentconcept = replace_ampersands_not_followed_by_entity($currentconcept);

            if (empty($currentconcept)) {
                continue;
            }

                        if (is_number($currentconcept) and $currentconcept < 1000) {
                continue;
            }

            $concept->concept = $currentconcept;

            $concepts[$concept->glossaryid][] = $concept;
        }
        $rs->close();

        return $concepts;
    }

    
    protected static function get_course_concepts($courseid) {
        global $DB;

        if (empty($courseid)) {
            return array(array(), array());
        }

        $courseid = (int)$courseid;

                $modinfo = get_fast_modinfo($courseid);
        $cminfos = $modinfo->get_instances_of('glossary');
        if (!$cminfos) {
                        return array(array(), array());
        }

        $cache = \cache::make('mod_glossary', 'concepts');
        $data = $cache->get($courseid);
        if (is_array($data)) {
            list($glossaries, $allconcepts) = $data;

        } else {
                        $sql = "SELECT g.id, g.name
                      FROM {glossary} g
                      JOIN {course_modules} cm ON (cm.instance = g.id)
                      JOIN {modules} m ON (m.name = 'glossary' AND m.id = cm.module)
                     WHERE g.usedynalink = 1 AND g.course = :course AND cm.visible = 1 AND m.visible = 1
                  ORDER BY g.globalglossary, g.id";
            $glossaries = $DB->get_records_sql_menu($sql, array('course' => $courseid));
            if (!$glossaries) {
                $data = array(array(), array());
                $cache->set($courseid, $data);
                return $data;
            }
            foreach ($glossaries as $id => $name) {
                $name = str_replace(':', '-', $name);
                $glossaries[$id] = replace_ampersands_not_followed_by_entity(strip_tags($name));
            }

            $allconcepts = self::fetch_concepts(array_keys($glossaries));
            foreach ($glossaries as $gid => $unused) {
                if (!isset($allconcepts[$gid])) {
                    unset($glossaries[$gid]);
                }
            }
            if (!$glossaries) {
                                $data = array(array(), array());
                $cache->set($courseid, $data);
                return $data;
            }
            $cache->set($courseid, array($glossaries, $allconcepts));
        }

        $concepts = $allconcepts;

                foreach ($concepts as $modid => $unused) {
            if (!isset($cminfos[$modid])) {
                                unset($concepts[$modid]);
                unset($glossaries[$modid]);
                continue;
            }
            if (!$cminfos[$modid]->uservisible) {
                unset($concepts[$modid]);
                unset($glossaries[$modid]);
                continue;
            }
        }

        return array($glossaries, $concepts);
    }

    
    protected static function get_global_concepts() {
        global $DB;

        $cache = \cache::make('mod_glossary', 'concepts');
        $data = $cache->get(0);
        if (is_array($data)) {
            list($glossaries, $allconcepts) = $data;

        } else {
                        $sql = "SELECT g.id, g.name
                      FROM {glossary} g
                      JOIN {course_modules} cm ON (cm.instance = g.id)
                      JOIN {modules} m ON (m.name = 'glossary' AND m.id = cm.module)
                     WHERE g.usedynalink = 1 AND g.globalglossary = 1 AND cm.visible = 1 AND m.visible = 1
                  ORDER BY g.globalglossary, g.id";
            $glossaries = $DB->get_records_sql_menu($sql);
            if (!$glossaries) {
                $data = array(array(), array());
                $cache->set(0, $data);
                return $data;
            }
            foreach ($glossaries as $id => $name) {
                $name = str_replace(':', '-', $name);
                $glossaries[$id] = replace_ampersands_not_followed_by_entity(strip_tags($name));
            }
            $allconcepts = self::fetch_concepts(array_keys($glossaries));
            foreach ($glossaries as $gid => $unused) {
                if (!isset($allconcepts[$gid])) {
                    unset($glossaries[$gid]);
                }
            }
            $cache->set(0, array($glossaries, $allconcepts));
        }

                        return array($glossaries, $allconcepts);
    }

    
    public static function get_concepts($courseid) {
        list($glossaries, $concepts) = self::get_course_concepts($courseid);
        list($globalglossaries, $globalconcepts) = self::get_global_concepts();

        foreach ($globalconcepts as $gid => $cs) {
            if (!isset($concepts[$gid])) {
                $concepts[$gid] = $cs;
            }
        }
        foreach ($globalglossaries as $gid => $name) {
            if (!isset($glossaries[$gid])) {
                $glossaries[$gid] = $name;
            }
        }

        return array($glossaries, $concepts);
    }
}
