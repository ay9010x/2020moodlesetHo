<?php



namespace mod_quiz\question;
defined('MOODLE_INTERNAL') || die();



class qubaids_for_users_attempts extends \qubaid_join {
    
    public function __construct($quizid, $userid, $status = 'finished', $includepreviews = false) {
        $where = 'quiza.quiz = :quizaquiz AND quiza.userid = :userid';
        $params = array('quizaquiz' => $quizid, 'userid' => $userid);

        if (!$includepreviews) {
            $where .= ' AND preview = 0';
        }

        switch ($status) {
            case 'all':
                break;

            case 'finished':
                $where .= ' AND state IN (:state1, :state2)';
                $params['state1'] = \quiz_attempt::FINISHED;
                $params['state2'] = \quiz_attempt::ABANDONED;
                break;

            case 'unfinished':
                $where .= ' AND state IN (:state1, :state2)';
                $params['state1'] = \quiz_attempt::IN_PROGRESS;
                $params['state2'] = \quiz_attempt::OVERDUE;
                break;
        }

        parent::__construct('{quiz_attempts} quiza', 'quiza.uniqueid', $where, $params);
    }
}
