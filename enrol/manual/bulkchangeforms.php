<?php



defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/enrol/bulkchange_forms.php");


class enrol_manual_editselectedusers_form extends enrol_bulk_enrolment_change_form {}


class enrol_manual_deleteselectedusers_form extends enrol_bulk_enrolment_confirm_form {}
