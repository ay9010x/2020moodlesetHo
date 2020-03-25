<?php


namespace core\task;

use core_tag_collection, core_tag_tag, core_tag_area, stdClass;


class tag_cron_task extends scheduled_task {

    
    public function get_name() {
        return get_string('tasktagcron', 'admin');
    }

    
    public function execute() {
        global $CFG;

        if (!empty($CFG->usetags)) {
            $this->compute_correlations();
            $this->cleanup();
        }
    }

    
    public function compute_correlations($mincorrelation = 2) {
        global $DB;

                                                                                        $sql = 'SELECT pairs.tagid, pairs.correlation, pairs.ocurrences, co.id AS correlationid
                  FROM (
                           SELECT ta.tagid, tb.tagid AS correlation, COUNT(*) AS ocurrences
                             FROM {tag_instance} ta
                             JOIN {tag} tga ON ta.tagid = tga.id
                             JOIN {tag_instance} tb ON (ta.itemtype = tb.itemtype AND ta.component = tb.component
                                AND ta.itemid = tb.itemid AND ta.tagid <> tb.tagid)
                             JOIN {tag} tgb ON tb.tagid = tgb.id AND tgb.tagcollid = tga.tagcollid
                         GROUP BY ta.tagid, tb.tagid
                           HAVING COUNT(*) > :mincorrelation
                       ) pairs
             LEFT JOIN {tag_correlation} co ON co.tagid = pairs.tagid
              ORDER BY pairs.tagid ASC, pairs.ocurrences DESC, pairs.correlation ASC';
        $rs = $DB->get_recordset_sql($sql, array('mincorrelation' => $mincorrelation));

                $tagcorrelation = new stdClass;
        $tagcorrelation->id = null;
        $tagcorrelation->tagid = null;
        $tagcorrelation->correlatedtags = array();

                        $correlations = array();

                                foreach ($rs as $row) {
            if ($row->tagid != $tagcorrelation->tagid) {
                                $tagcorrelationid = $this->process_computed_correlation($tagcorrelation);
                if ($tagcorrelationid) {
                    $correlations[] = $tagcorrelationid;
                }
                                                $tagcorrelation = new stdClass;
                $tagcorrelation->id = $row->correlationid;
                $tagcorrelation->tagid = $row->tagid;
                $tagcorrelation->correlatedtags = array();
            }
                        $tagcorrelation->correlatedtags[] = $row->correlation;
        }
                $tagcorrelationid = $this->process_computed_correlation($tagcorrelation);
        if ($tagcorrelationid) {
            $correlations[] = $tagcorrelationid;
        }

                $rs->close();

                if (empty($correlations)) {
                        $DB->delete_records('tag_correlation');
        } else {
            list($sql, $params) = $DB->get_in_or_equal($correlations,
                    SQL_PARAMS_NAMED, 'param0000', false);
            $DB->delete_records_select('tag_correlation', 'id '.$sql, $params);
        }
    }

    
    public function cleanup() {
        global $DB;

                $sql = "SELECT ti.id
                  FROM {tag_instance} ti
             LEFT JOIN {tag} t ON t.id = ti.tagid
                 WHERE t.id IS null";
        $tagids = $DB->get_records_sql($sql);
        $tagarray = array();
        foreach ($tagids as $tagid) {
            $tagarray[] = $tagid->id;
        }

                $sql = "SELECT ti.id
                  FROM {tag_instance} ti, {user} u
                 WHERE ti.itemid = u.id
                   AND ti.itemtype = 'user'
                   AND ti.component = 'core'
                   AND u.deleted = 1";
        $tagids = $DB->get_records_sql($sql);
        foreach ($tagids as $tagid) {
            $tagarray[] = $tagid->id;
        }

                $sql = "SELECT DISTINCT component, itemtype
                  FROM {tag_instance}
                 WHERE itemtype <> 'user' or component <> 'core'";
        $tagareas = $DB->get_records_sql($sql);
        foreach ($tagareas as $tagarea) {
            $sql = 'SELECT ti.id
                      FROM {tag_instance} ti
                 LEFT JOIN {' . $tagarea->itemtype . '} it ON it.id = ti.itemid
                     WHERE it.id IS null
                     AND ti.itemtype = ? AND ti.component = ?';
            $tagids = $DB->get_records_sql($sql, array($tagarea->itemtype, $tagarea->component));
            foreach ($tagids as $tagid) {
                $tagarray[] = $tagid->id;
            }
        }

                if (count($tagarray) > 0) {
            list($sqlin, $params) = $DB->get_in_or_equal($tagarray);
            $sql = "SELECT ti.*, COALESCE(t.name, 'deleted') AS name, COALESCE(t.rawname, 'deleted') AS rawname
                      FROM {tag_instance} ti
                 LEFT JOIN {tag} t ON t.id = ti.tagid
                     WHERE ti.id $sqlin";
            $instances = $DB->get_records_sql($sql, $params);
            $this->bulk_delete_instances($instances);
        }

        core_tag_collection::cleanup_unused_tags();
    }

    
    public function process_computed_correlation(stdClass $tagcorrelation) {
        global $DB;

                if (empty($tagcorrelation->tagid) || !isset($tagcorrelation->correlatedtags) ||
                !is_array($tagcorrelation->correlatedtags)) {
            return false;
        }

        $tagcorrelation->correlatedtags = join(',', $tagcorrelation->correlatedtags);
        if (!empty($tagcorrelation->id)) {
                        $DB->update_record('tag_correlation', $tagcorrelation);
        } else {
                        $tagcorrelation->id = $DB->insert_record('tag_correlation', $tagcorrelation);
        }
        return $tagcorrelation->id;
    }

    
    public function bulk_delete_instances($instances) {
        global $DB;

        $instanceids = array();
        foreach ($instances as $instance) {
            $instanceids[] = $instance->id;
        }

                        list($insql, $params) = $DB->get_in_or_equal($instanceids);
        $sql = 'id ' . $insql;
        $DB->delete_records_select('tag_instance', $sql, $params);

                foreach ($instances as $instance) {
                        \core\event\tag_removed::create_from_tag_instance($instance, $instance->name,
                    $instance->rawname, true)->trigger();
        }
    }
}
