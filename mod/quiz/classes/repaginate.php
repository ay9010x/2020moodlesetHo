<?php



namespace mod_quiz;
defined('MOODLE_INTERNAL') || die();


class repaginate {

    
    const LINK = 1;
    
    const UNLINK = 2;

    
    private $quizid;
    
    private $slots;

    
    public function __construct($quizid = 0, $slots = null) {
        global $DB;
        $this->quizid = $quizid;
        if (!$this->quizid) {
            $this->slots = array();
        }
        if (!$slots) {
            $this->slots = $DB->get_records('quiz_slots', array('quizid' => $this->quizid), 'slot');
        } else {
            $this->slots = $slots;
        }
    }

    
    protected function repaginate_this_slot($slot, $newpagenumber) {
        $newslot = clone($slot);
        $newslot->page = $newpagenumber;
        return $newslot;
    }

    
    protected function get_this_slot($slots, $slotnumber) {
        foreach ($slots as $key => $slot) {
            if ($slot->slot == $slotnumber) {
                return $slot;
            }
        }
        return null;
    }

    
    protected function get_slots_by_slot_number($slots) {
        if (!$slots) {
            return array();
        }
        $newslots = array();
        foreach ($slots as $slot) {
            $newslots[$slot->slot] = $slot;
        }
        return $newslots;
    }

    
    protected function get_slots_by_slotid($slots) {
        if (!$slots) {
            return array();
        }
        $newslots = array();
        foreach ($slots as $slot) {
            $newslots[$slot->id] = $slot;
        }
        return $newslots;
    }

    
    public function repaginate_slots($nextslotnumber, $type) {
        global $DB;
        $this->slots = $DB->get_records('quiz_slots', array('quizid' => $this->quizid), 'slot');
        $nextslot = null;
        $newslots = array();
        foreach ($this->slots as $slot) {
            if ($slot->slot < $nextslotnumber) {
                $newslots[$slot->id] = $slot;
            } else if ($slot->slot == $nextslotnumber) {
                $nextslot = $this->repaginate_next_slot($nextslotnumber, $type);

                                $DB->update_record('quiz_slots', $nextslot, true);

                                $newslots[$slot->id] = $nextslot;
            }
        }
        if ($nextslot) {
            $newslots = array_merge($newslots, $this->repaginate_the_rest($this->slots, $nextslotnumber, $type));
            $this->slots = $this->get_slots_by_slotid($newslots);
        }
    }

    
    public function repaginate_next_slot($nextslotnumber, $type) {
        $currentslotnumber = $nextslotnumber - 1;
        if (!($currentslotnumber && $nextslotnumber)) {
            return null;
        }
        $currentslot = $this->get_this_slot($this->slots, $currentslotnumber);
        $nextslot = $this->get_this_slot($this->slots, $nextslotnumber);

        if ($type === self::LINK) {
            return $this->repaginate_this_slot($nextslot, $currentslot->page);
        } else if ($type === self::UNLINK) {
            return $this->repaginate_this_slot($nextslot, $nextslot->page + 1);
        }
        return null;
    }

    
    public function repaginate_n_question_per_page($slots, $number) {
        $slots = $this->get_slots_by_slot_number($slots);
        $newslots = array();
        $count = 0;
        $page = 1;
        foreach ($slots as $key => $slot) {
            for ($page + $count; $page < ($number + $count + 1); $page++) {
                if ($slot->slot >= $page) {
                    $slot->page = $page;
                    $count++;
                }
            }
            $newslots[$slot->id] = $slot;
        }
        return $newslots;
    }

    
    public function repaginate_the_rest($quizslots, $slotfrom, $type, $dbupdate = true) {
        global $DB;
        if (!$quizslots) {
            return null;
        }
        $newslots = array();
        foreach ($quizslots as $slot) {
            if ($type == self::LINK) {
                if ($slot->slot <= $slotfrom) {
                    continue;
                }
                $slot->page = $slot->page - 1;
            } else if ($type == self::UNLINK) {
                if ($slot->slot <= $slotfrom - 1) {
                    continue;
                }
                $slot->page = $slot->page + 1;
            }
                        if ($dbupdate) {
                $DB->update_record('quiz_slots', $slot);
            }
            $newslots[$slot->id] = $slot;
        }
        return $newslots;
    }
}
