<?php



$tasks = array(
            array(
                'classname' => 'mod_scheduler\task\send_reminders',
                'minute' => 'R',
                'hour' => '*',
                'day' => '*',
                'dayofweek' => '*',
                'month' => '*'
            ),
            array(
                'classname' => 'mod_scheduler\task\purge_unused_slots',
                'minute' => '*/5',
                'hour' => '*',
                'day' => '*',
                'dayofweek' => '*',
                'month' => '*'
            )
);