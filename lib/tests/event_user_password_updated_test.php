<?php



defined('MOODLE_INTERNAL') || die();


class core_event_user_password_updated_testcase extends advanced_testcase {
    
    public function test_event() {
        $this->resetAfterTest();

        $user1 = $this->getDataGenerator()->create_user();
        $context1 = context_user::instance($user1->id);
        $user2 = $this->getDataGenerator()->create_user();
        $context2 = context_user::instance($user2->id);

        $this->setUser($user1);

                $event = \core\event\user_password_updated::create_from_user($user1);
        $this->assertEventContextNotUsed($event);
        $this->assertEquals($user1->id, $event->relateduserid);
        $this->assertSame($context1, $event->get_context());
        $this->assertEventLegacyLogData(null, $event);
        $this->assertFalse($event->other['forgottenreset']);
        $event->trigger();

                $event = \core\event\user_password_updated::create_from_user($user2);
        $this->assertEventContextNotUsed($event);
        $this->assertEquals($user2->id, $event->relateduserid);
        $this->assertSame($context2, $event->get_context());
        $this->assertEventLegacyLogData(null, $event);
        $this->assertFalse($event->other['forgottenreset']);
        $event->trigger();

                $event = \core\event\user_password_updated::create_from_user($user1, true);
        $this->assertEventContextNotUsed($event);
        $this->assertEquals($user1->id, $event->relateduserid);
        $this->assertSame($context1, $event->get_context());
        $this->assertEventLegacyLogData(array(SITEID, 'user', 'set password', 'profile.php?id='.$user1->id, $user1->id), $event);
        $this->assertTrue($event->other['forgottenreset']);
        $event->trigger();
    }
}
