<?php


namespace tool_lp\external;
defined('MOODLE_INTERNAL') || die();

use moodle_url;
use renderer_base;
use core_competency\external\stored_file_exporter;


class user_evidence_summary_exporter extends \core_competency\external\persistent_exporter {

    protected static function define_class() {
        return 'core_competency\\user_evidence';
    }

    protected static function define_other_properties() {
        return array(
            'canmanage' => array(
                'type' => PARAM_BOOL
            ),
            'filecount' => array(
                'type' => PARAM_INT
            ),
            'files' => array(
                'type' => stored_file_exporter::read_properties_definition(),
                'multiple' => true
            ),
            'hasurlorfiles' => array(
                'type' => PARAM_BOOL
            ),
            'urlshort' => array(
                'type' => PARAM_TEXT
            ),
            'competencycount' => array(
                'type' => PARAM_INT
            ),
            'usercompetencies' => array(
                'type' => user_evidence_competency_summary_exporter::read_properties_definition(),
                'optional' => true,
                'multiple' => true
            ),
            'userhasplan' => array(
                'type' => PARAM_BOOL
            ),
        );
    }

    protected function get_other_values(renderer_base $output) {
        $urlshort = '';
        $url = $this->persistent->get_url();
        if (!empty($url)) {
            $murl = new moodle_url($url);
            $shorturl = preg_replace('@^https?://(www\.)?@', '', $murl->out(false));
            $urlshort = shorten_text($shorturl, 30, true);
        }

        $files = array();
        $storedfiles = $this->persistent->get_files();
        if (!empty($storedfiles)) {
            foreach ($storedfiles as $storedfile) {
                $fileexporter = new stored_file_exporter($storedfile, array('context' => $this->related['context']));
                $files[] = $fileexporter->export($output);
            }
        }

        $userevidencecompetencies = array();
        $frameworks = array();
        $scales = array();
        $usercompetencies = $this->persistent->get_user_competencies();
        foreach ($usercompetencies as $usercompetency) {
            $competency = $usercompetency->get_competency();

                        if (!isset($frameworks[$competency->get_competencyframeworkid()])) {
                $frameworks[$competency->get_competencyframeworkid()] = $competency->get_framework();
            }
            $framework = $frameworks[$competency->get_competencyframeworkid()];

                        $scaleid = $competency->get_scaleid();
            if ($scaleid === null) {
                $scaleid = $framework->get_scaleid();
            }
            if (!isset($scales[$framework->get_scaleid()])) {
                $scales[$framework->get_scaleid()] = $framework->get_scale();
            }
            $scale = $scales[$framework->get_scaleid()];

            $related = array('competency' => $competency,
                             'usercompetency' => $usercompetency,
                             'scale' => $scale,
                             'context' => $framework->get_context());

            $userevidencecompetencysummaryexporter = new user_evidence_competency_summary_exporter(null, $related);

            $userevidencecompetencies[] = $userevidencecompetencysummaryexporter->export($output);
        }

        $values = array(
            'canmanage' => $this->persistent->can_manage(),
            'filecount' => count($files),
            'files' => $files,
            'userhasplan' => $this->persistent->user_has_plan(),
            'hasurlorfiles' => !empty($files) || !empty($url),
            'urlshort' => $urlshort,
            'competencycount' => count($userevidencecompetencies),
            'usercompetencies' => $userevidencecompetencies
        );

        return $values;
    }

}
