<?php



namespace tool_messageinbound;

defined('MOODLE_INTERNAL') || die();


class manager {

    
    const MAILBOX = 'INBOX';

    
    const CONFIRMATIONFOLDER = 'tobeconfirmed';

    
    const MESSAGE_SEEN = '\seen';

    
    const MESSAGE_FLAGGED = '\flagged';

    
    const MESSAGE_DELETED = '\deleted';

    
    protected $imapnamespace = null;

    
    protected $client = null;

    
    protected $addressmanager = null;

    
    protected $currentmessagedata = null;

    
    protected function get_imap_client() {
        global $CFG;

        if (!\core\message\inbound\manager::is_enabled()) {
                        mtrace("Inbound Message not fully configured - exiting early.");
            return false;
        }

        mtrace("Connecting to {$CFG->messageinbound_host} as {$CFG->messageinbound_hostuser}...");

        $configuration = array(
            'username' => $CFG->messageinbound_hostuser,
            'password' => $CFG->messageinbound_hostpass,
            'hostspec' => $CFG->messageinbound_host,
            'secure'   => $CFG->messageinbound_hostssl,
            'debug'    => empty($CFG->debugimap) ? null : fopen('php://stderr', 'w'),
        );

        if (strpos($configuration['hostspec'], ':')) {
            $hostdata = explode(':', $configuration['hostspec']);
            if (count($hostdata) === 2) {
                                $configuration['hostspec'] = $hostdata[0];
                $configuration['port'] = $hostdata[1];
            }
        }

        $this->client = new \Horde_Imap_Client_Socket($configuration);

        try {
            $this->client->login();
            mtrace("Connection established.");

                        $this->ensure_mailboxes_exist();

            return true;

        } catch (\Horde_Imap_Client_Exception $e) {
            $message = $e->getMessage();
            throw new \moodle_exception('imapconnectfailure', 'tool_messageinbound', '', null, $message);
        }
    }

    
    protected function close_connection() {
        if ($this->client) {
            $this->client->close();
        }
        $this->client = null;
    }

    
    protected function get_confirmation_folder() {

        if ($this->imapnamespace === null) {
            if ($this->client->queryCapability('NAMESPACE')) {
                $namespaces = $this->client->getNamespaces(array(), array('ob_return' => true));
                $this->imapnamespace = $namespaces->getNamespace('INBOX');
            } else {
                $this->imapnamespace = '';
            }
        }

        return $this->imapnamespace . self::CONFIRMATIONFOLDER;
    }

    
    protected function get_mailbox() {
                $mailbox = $this->client->currentMailbox();

        if (isset($mailbox['mailbox'])) {
            return $mailbox['mailbox'];
        } else {
            throw new \core\message\inbound\processing_failed_exception('couldnotopenmailbox', 'tool_messageinbound');
        }
    }

    
    public function pickup_messages() {
        if (!$this->get_imap_client()) {
            return false;
        }

                $search = new \Horde_Imap_Client_Search_Query();
        $search->flag(self::MESSAGE_SEEN, false);
        $search->flag(self::MESSAGE_FLAGGED, false);
        mtrace("Searching for Unseen, Unflagged email in the folder '" . self::MAILBOX . "'");
        $results = $this->client->search(self::MAILBOX, $search);

                $query = new \Horde_Imap_Client_Fetch_Query();
        $query->envelope();
        $query->structure();

                $messages = $this->client->fetch(self::MAILBOX, $query, array('ids' => $results['match']));

        mtrace("Found " . $messages->count() . " messages to parse. Parsing...");
        $this->addressmanager = new \core\message\inbound\address_manager();
        foreach ($messages as $message) {
            $this->process_message($message);
        }

                $this->close_connection();

        return true;
    }

    
    public function process_existing_message(\stdClass $maildata) {
                if (!$this->get_imap_client()) {
            return false;
        }

                $search = new \Horde_Imap_Client_Search_Query();
                $search->flag(self::MESSAGE_SEEN, true);
        $search->flag(self::MESSAGE_FLAGGED, true);
        mtrace("Searching for a Seen, Flagged message in the folder '" . $this->get_confirmation_folder() . "'");

                $search->headerText('message-id', $maildata->messageid);
        $search->headerText('to', $maildata->address);

        $results = $this->client->search($this->get_confirmation_folder(), $search);

                $query = new \Horde_Imap_Client_Fetch_Query();
        $query->envelope();
        $query->structure();


                $messages = $this->client->fetch($this->get_confirmation_folder(), $query, array('ids' => $results['match']));
        $this->addressmanager = new \core\message\inbound\address_manager();
        if ($message = $messages->first()) {
            mtrace("--> Found the message. Passing back to the pickup system.");

                        $this->process_message($message, true, true);

                        $this->close_connection();

            mtrace("============================================================================");
            return true;
        } else {
                        $this->close_connection();

            mtrace("============================================================================");
            throw new \core\message\inbound\processing_failed_exception('oldmessagenotfound', 'tool_messageinbound');
        }
    }

    
    public function tidy_old_messages() {
                if (!$this->get_imap_client()) {
            return false;
        }

                mtrace("Searching for messages older than 24 hours in the '" .
                $this->get_confirmation_folder() . "' folder.");
        $this->client->openMailbox($this->get_confirmation_folder());

        $mailbox = $this->get_mailbox();

                $search = new \Horde_Imap_Client_Search_Query();

                $search->intervalSearch(DAYSECS, \Horde_Imap_Client_Search_Query::INTERVAL_OLDER);

        $results = $this->client->search($mailbox, $search);

                $query = new \Horde_Imap_Client_Fetch_Query();
        $query->envelope();

                $messages = $this->client->fetch($mailbox, $query, array('ids' => $results['match']));
        mtrace("Found " . $messages->count() . " messages for removal.");
        foreach ($messages as $message) {
            $this->add_flag_to_message($message->getUid(), self::MESSAGE_DELETED);
        }

        mtrace("Finished removing messages.");
        $this->close_connection();

        return true;
    }

    
    public function process_message(
            \Horde_Imap_Client_Data_Fetch $message,
            $viewreadmessages = false,
            $skipsenderverification = false) {
        global $USER;

                $messageid = new \Horde_Imap_Client_Ids($message->getUid());

        mtrace("- Parsing message " . $messageid);

                $this->add_flag_to_message($messageid, self::MESSAGE_FLAGGED);

        if ($this->is_bulk_message($message, $messageid)) {
            mtrace("- The message has a bulk header set. This is likely an auto-generated reply - discarding.");
            return;
        }

                        $originaluser = $USER;

        $envelope = $message->getEnvelope();
        $recipients = $envelope->to->bare_addresses;
        foreach ($recipients as $recipient) {
            if (!\core\message\inbound\address_manager::is_correct_format($recipient)) {
                                mtrace("- Recipient '{$recipient}' did not match Inbound Message headers.");
                continue;
            }

                        $senders = $message->getEnvelope()->from->bare_addresses;
            if (count($senders) !== 1) {
                mtrace("- Received multiple senders. Only the first sender will be used.");
            }
            $sender = array_shift($senders);

            mtrace("-- Subject:\t"      . $envelope->subject);
            mtrace("-- From:\t"         . $sender);
            mtrace("-- Recipient:\t"    . $recipient);

                        $query = new \Horde_Imap_Client_Fetch_Query();
            $query->structure();
            $messagedata = $this->client->fetch($this->get_mailbox(), $query, array(
                'ids' => $messageid,
            ))->first();

            if (!$viewreadmessages && $this->message_has_flag($messageid, self::MESSAGE_SEEN)) {
                                mtrace("-- Skipping the message - it has been marked as seen - perhaps by another process.");
                continue;
            }

                        $this->add_flag_to_message($messageid, self::MESSAGE_SEEN);

                        $status = $this->addressmanager->process_envelope($recipient, $sender);

            if (($status & ~ \core\message\inbound\address_manager::VALIDATION_DISABLED_HANDLER) !== $status) {
                                mtrace("-- Skipped message - Handler is disabled. Fail code {$status}");
                                $this->process_message_data($envelope, $messagedata, $messageid);
                $this->inform_user_of_error(get_string('handlerdisabled', 'tool_messageinbound', $this->currentmessagedata));
                return;
            }

                                    if (!$this->passes_key_validation($status, $messageid)) {
                                mtrace("-- Skipped message - it does not appear to relate to a Inbound Message pickup. Fail code {$status}");

                                $this->remove_flag_from_message($messageid, self::MESSAGE_SEEN);

                                continue;
            }

                        $user = $this->addressmanager->get_data()->user;
            mtrace("-- Processing the message as user {$user->id} ({$user->username}).");
            cron_setup_user($user);

                                    if (!$this->process_message_data($envelope, $messagedata, $messageid)) {
                mtrace("--- Message could not be found on the server. Is another process removing messages?");
                return;
            }

                                    if (!$skipsenderverification && $status !== 0) {
                                                mtrace("-- Message did not meet validation but is possibly recoverable. Fail code {$status}");
                
                if ($this->handle_verification_failure($messageid, $recipient)) {
                    mtrace("--- Original message retained on mail server and confirmation message sent to user.");
                } else {
                    mtrace("--- Invalid Recipient Handler - unable to save. Informing the user of the failure.");
                    $this->inform_user_of_error(get_string('invalidrecipientfinal', 'tool_messageinbound', $this->currentmessagedata));
                }

                                mtrace("-- Returning to the original user.");
                cron_setup_user($originaluser);
                return;
            }

                        mtrace("-- Validation completed. Fetching rest of message content.");
            $this->process_message_data_body($messagedata, $messageid);

                                    try {
                $result = $this->send_to_handler();
            } catch (\core\message\inbound\processing_failed_exception $e) {
                                                $this->inform_user_of_error($e->getMessage());

                                mtrace("-- Returning to the original user.");
                cron_setup_user($originaluser);
                return;
            } catch (\Exception $e) {
                                mtrace("-- Message processing failed. An unexpected exception was thrown. Details follow.");
                mtrace($e->getMessage());

                                mtrace("-- Returning to the original user.");
                cron_setup_user($originaluser);
                return;
            }

            if ($result) {
                                mtrace("-- Marking the message for removal.");
                $this->add_flag_to_message($messageid, self::MESSAGE_DELETED);
            } else {
                mtrace("-- The Inbound Message processor did not return a success status. Skipping message removal.");
            }

                        mtrace("-- Returning to the original user.");
            cron_setup_user($originaluser);

            mtrace("-- Finished processing " . $message->getUid());

                                    return;
        }
    }

    
    private function process_message_data(
            \Horde_Imap_Client_Data_Envelope $envelope,
            \Horde_Imap_Client_Data_Fetch $basemessagedata,
            $messageid) {

                $mailbox = $this->get_mailbox();

                $structure = $basemessagedata->getStructure();

                $query = new \Horde_Imap_Client_Fetch_Query();
        $query->imapDate();

                $query->headerText();

                $messagedata = $this->client->fetch($mailbox, $query, array('ids' => $messageid))->first();

        if (!$messagedata) {
                        return null;
        }

                $data = new \stdClass();
        $data->messageid = $messagedata->getHeaderText(0, \Horde_Imap_Client_Data_Fetch::HEADER_PARSE)->getValue('Message-ID');
        $data->subject = $envelope->subject;
        $data->timestamp = $messagedata->getImapDate()->__toString();
        $data->envelope = $envelope;
        $data->data = $this->addressmanager->get_data();
        $data->headers = $messagedata->getHeaderText();

        $this->currentmessagedata = $data;

        return $this->currentmessagedata;
    }

    
    private function process_message_data_body(
            \Horde_Imap_Client_Data_Fetch $basemessagedata,
            $messageid) {
        global $CFG;

                $mailbox = $this->get_mailbox();

                $structure = $basemessagedata->getStructure();

                $query = new \Horde_Imap_Client_Fetch_Query();
        $query->fullText();

                $typemap = $structure->contentTypeMap();
        foreach ($typemap as $part => $type) {
                        $query->bodyPart($part, array(
                'decode' => true,
                'peek' => true,
            ));
            $query->bodyPartSize($part);
        }

        $messagedata = $this->client->fetch($mailbox, $query, array('ids' => $messageid))->first();

                $contentplain = '';
        $contenthtml = '';
        $attachments = array(
            'inline' => array(),
            'attachment' => array(),
        );

        $plainpartid = $structure->findBody('plain');
        $htmlpartid = $structure->findBody('html');

        foreach ($typemap as $part => $type) {
                        $stream = $messagedata->getBodyPart($part, true);
            $partdata = $structure->getPart($part);
            $partdata->setContents($stream, array(
                'usestream' => true,
            ));

            if ($part === $plainpartid) {
                $contentplain = $this->process_message_part_body($messagedata, $partdata, $part);

            } else if ($part === $htmlpartid) {
                $contenthtml = $this->process_message_part_body($messagedata, $partdata, $part);

            } else if ($filename = $partdata->getName($part)) {
                if ($attachment = $this->process_message_part_attachment($messagedata, $partdata, $part, $filename)) {
                                                            $disposition = $partdata->getDisposition();
                    $disposition = $disposition == 'inline' ? 'inline' : 'attachment';
                    $attachments[$disposition][] = $attachment;
                }
            }

                    }

                $this->currentmessagedata->plain = $contentplain;
        $this->currentmessagedata->html = $contenthtml;
        $this->currentmessagedata->attachments = $attachments;

        return $this->currentmessagedata;
    }

    
    private function process_message_part_body($messagedata, $partdata, $part) {
        
                $content = $messagedata->getBodyPart($part);
        if (!$messagedata->getBodyPartDecode($part)) {
                        $partdata->setContents($content);
            $content = $partdata->getContents();
        }

                $content = \core_text::convert($content, $partdata->getCharset());

                                $content = clean_param($content, PARAM_RAW);

        return $content;
    }

    
    private function process_message_part_attachment($messagedata, $partdata, $part, $filename) {
        global $CFG;

                $attachment = new \stdClass();
        $attachment->filename       = $filename;
        $attachment->type           = $partdata->getType();
        $attachment->content        = $partdata->getContents();
        $attachment->charset        = $partdata->getCharset();
        $attachment->description    = $partdata->getDescription();
        $attachment->contentid      = $partdata->getContentId();
        $attachment->filesize       = $messagedata->getBodyPartSize($part);

        if (!empty($CFG->antiviruses)) {
            mtrace("--> Attempting virus scan of '{$attachment->filename}'");

                        $itemid = rand(1, 999999999);;
            $directory = make_temp_directory("/messageinbound/{$itemid}", false);
            $filepath = $directory . "/" . $attachment->filename;
            if (!$fp = fopen($filepath, "w")) {
                                mtrace("--> Unable to save the file to disk for virus scanning. Check file permissions.");

                throw new \core\message\inbound\processing_failed_exception('attachmentfilepermissionsfailed',
                        'tool_messageinbound');
            }

            fwrite($fp, $attachment->content);
            fclose($fp);

                        try {
                \core\antivirus\manager::scan_file($filepath, $attachment->filename, true);
            } catch (\core\antivirus\scanner_exception $e) {
                mtrace("--> A virus was found in the attachment '{$attachment->filename}'.");
                $this->inform_attachment_virus();
                return;
            }
        }

        return $attachment;
    }

    
    private function passes_key_validation($status, $messageid) {
                if ((
            $status & ~ \core\message\inbound\address_manager::VALIDATION_SUCCESS
                    & ~ \core\message\inbound\address_manager::VALIDATION_UNKNOWN_DATAKEY
                    & ~ \core\message\inbound\address_manager::VALIDATION_EXPIRED_DATAKEY
                    & ~ \core\message\inbound\address_manager::VALIDATION_INVALID_HASH
                    & ~ \core\message\inbound\address_manager::VALIDATION_ADDRESS_MISMATCH) !== 0) {

                        return false;
        }
        return true;
    }

    
    private function add_flag_to_message($messageid, $flag) {
                $mailbox = $this->get_mailbox();

                $this->client->store($mailbox, array(
            'ids' => new \Horde_Imap_Client_Ids($messageid),
            'add' => $flag,
        ));
    }

    
    private function remove_flag_from_message($messageid, $flag) {
                $mailbox = $this->get_mailbox();

                $this->client->store($mailbox, array(
            'ids' => $messageid,
            'delete' => $flag,
        ));
    }

    
    private function message_has_flag($messageid, $flag) {
                $mailbox = $this->get_mailbox();

                $query = new \Horde_Imap_Client_Fetch_Query();
        $query->flags();
        $query->structure();
        $messagedata = $this->client->fetch($mailbox, $query, array(
            'ids' => $messageid,
        ))->first();
        $flags = $messagedata->getFlags();

        return in_array($flag, $flags);
    }

    
    private function ensure_mailboxes_exist() {

        $requiredmailboxes = array(
            self::MAILBOX,
            $this->get_confirmation_folder(),
        );

        $existingmailboxes = $this->client->listMailboxes($requiredmailboxes);
        foreach ($requiredmailboxes as $mailbox) {
            if (isset($existingmailboxes[$mailbox])) {
                                continue;
            }

            mtrace("Unable to find the '{$mailbox}' mailbox - creating it.");
            $this->client->createMailbox($mailbox);
        }
    }

    
    private function is_bulk_message(
            \Horde_Imap_Client_Data_Fetch $message,
            $messageid) {
        $query = new \Horde_Imap_Client_Fetch_Query();
        $query->headerText(array('peek' => true));

        $messagedata = $this->client->fetch($this->get_mailbox(), $query, array('ids' => $messageid))->first();

                $isbulk = false;

                $precedence = $messagedata->getHeaderText(0, \Horde_Imap_Client_Data_Fetch::HEADER_PARSE)->getValue('Precedence');
        $isbulk = $isbulk || strtolower($precedence) == 'bulk';

                $autoreply = $messagedata->getHeaderText(0, \Horde_Imap_Client_Data_Fetch::HEADER_PARSE)->getValue('X-Autoreply');
        $isbulk = $isbulk || ($autoreply && $autoreply != 'no');

                $autorespond = $messagedata->getHeaderText(0, \Horde_Imap_Client_Data_Fetch::HEADER_PARSE)->getValue('X-Autorespond');
        $isbulk = $isbulk || ($autorespond && $autorespond != 'no');

                $autosubmitted = $messagedata->getHeaderText(0, \Horde_Imap_Client_Data_Fetch::HEADER_PARSE)->getValue('Auto-Submitted');
        $isbulk = $isbulk || ($autosubmitted && $autosubmitted != 'no');

        return $isbulk;
    }

    
    private function send_to_handler() {
        try {
            mtrace("--> Passing to Inbound Message handler {$this->addressmanager->get_handler()->classname}");
            if ($result = $this->addressmanager->handle_message($this->currentmessagedata)) {
                $this->inform_user_of_success($this->currentmessagedata, $result);
                                return true;
            }

        } catch (\core\message\inbound\processing_failed_exception $e) {
            mtrace("-> The Inbound Message handler threw an exception. Unable to process this message. The user has been informed.");
            mtrace("--> " . $e->getMessage());
                        $error = new \stdClass();
            $error->subject     = $this->currentmessagedata->envelope->subject;
            $error->message     = $e->getMessage();
            throw new \core\message\inbound\processing_failed_exception('messageprocessingfailed', 'tool_messageinbound', $error);

        } catch (\Exception $e) {
            mtrace("-> The Inbound Message handler threw an exception. Unable to process this message. User informed.");
            mtrace("--> " . $e->getMessage());
                                    $error = new \stdClass();
            $error->subject     = $this->currentmessagedata->envelope->subject;
            throw new \core\message\inbound\processing_failed_exception('messageprocessingfailedunknown',
                    'tool_messageinbound', $error);

        }

                mtrace("-> The Inbound Message handler reported an error. The message may not have been processed.");

                        return false;
    }

    
    private function handle_verification_failure(
            \Horde_Imap_Client_Ids $messageids,
            $recipient) {
        global $DB, $USER;

        if (!$messageid = $this->currentmessagedata->messageid) {
            mtrace("---> Warning: Unable to determine the Message-ID of the message.");
            return false;
        }

                $this->client->copy(self::MAILBOX, $this->get_confirmation_folder(), array(
                'create'    => true,
                'ids'       => $messageids,
                'move'      => true,
            ));

                $record = new \stdClass();
        $record->messageid = $messageid;
        $record->userid = $USER->id;
        $record->address = $recipient;
        $record->timecreated = time();
        $record->id = $DB->insert_record('messageinbound_messagelist', $record);

                $addressmanager = new \core\message\inbound\address_manager();
        $addressmanager->set_handler('\tool_messageinbound\message\inbound\invalid_recipient_handler');
        $addressmanager->set_data($record->id);

        $eventdata = new \stdClass();
        $eventdata->component           = 'tool_messageinbound';
        $eventdata->name                = 'invalidrecipienthandler';

        $userfrom = clone $USER;
        $userfrom->customheaders = array();
                $userfrom->customheaders[] = 'In-Reply-To: ' . $messageid;

                $eventdata->userfrom            = \core_user::get_support_user();
        $eventdata->userto              = $USER;
        $eventdata->subject             = $this->get_reply_subject($this->currentmessagedata->envelope->subject);
        $eventdata->fullmessage         = get_string('invalidrecipientdescription', 'tool_messageinbound', $this->currentmessagedata);
        $eventdata->fullmessageformat   = FORMAT_PLAIN;
        $eventdata->fullmessagehtml     = get_string('invalidrecipientdescriptionhtml', 'tool_messageinbound', $this->currentmessagedata);
        $eventdata->smallmessage        = $eventdata->fullmessage;
        $eventdata->notification        = 1;
        $eventdata->replyto             = $addressmanager->generate($USER->id);

        mtrace("--> Sending a message to the user to report an verification failure.");
        if (!message_send($eventdata)) {
            mtrace("---> Warning: Message could not be sent.");
            return false;
        }

        return true;
    }

    
    private function inform_user_of_error($error) {
        global $USER;

                $userfrom = clone $USER;
        $userfrom->customheaders = array();

        if ($messageid = $this->currentmessagedata->messageid) {
                        $userfrom->customheaders[] = 'In-Reply-To: ' . $messageid;
        }

        $messagedata = new \stdClass();
        $messagedata->subject = $this->currentmessagedata->envelope->subject;
        $messagedata->error = $error;

        $eventdata = new \stdClass();
        $eventdata->component           = 'tool_messageinbound';
        $eventdata->name                = 'messageprocessingerror';
        $eventdata->userfrom            = $userfrom;
        $eventdata->userto              = $USER;
        $eventdata->subject             = self::get_reply_subject($this->currentmessagedata->envelope->subject);
        $eventdata->fullmessage         = get_string('messageprocessingerror', 'tool_messageinbound', $messagedata);
        $eventdata->fullmessageformat   = FORMAT_PLAIN;
        $eventdata->fullmessagehtml     = get_string('messageprocessingerrorhtml', 'tool_messageinbound', $messagedata);
        $eventdata->smallmessage        = $eventdata->fullmessage;
        $eventdata->notification        = 1;

        if (message_send($eventdata)) {
            mtrace("---> Notification sent to {$USER->email}.");
        } else {
            mtrace("---> Unable to send notification.");
        }
    }

    
    private function inform_user_of_success(\stdClass $messagedata, $handlerresult) {
        global $USER;

                $handler = $this->addressmanager->get_handler();
        $message = $handler->get_success_message($messagedata, $handlerresult);

        if (!$message) {
            mtrace("---> Handler has not defined a success notification e-mail.");
            return false;
        }

                $messageparams = new \stdClass();
        $messageparams->html    = $message->html;
        $messageparams->plain   = $message->plain;
        $messagepreferencesurl = new \moodle_url("/message/edit.php", array('id' => $USER->id));
        $messageparams->messagepreferencesurl = $messagepreferencesurl->out();
        $htmlmessage = get_string('messageprocessingsuccesshtml', 'tool_messageinbound', $messageparams);
        $plainmessage = get_string('messageprocessingsuccess', 'tool_messageinbound', $messageparams);

                $userfrom = clone $USER;
        $userfrom->customheaders = array();

        if ($messageid = $this->currentmessagedata->messageid) {
                        $userfrom->customheaders[] = 'In-Reply-To: ' . $messageid;
        }

        $messagedata = new \stdClass();
        $messagedata->subject = $this->currentmessagedata->envelope->subject;

        $eventdata = new \stdClass();
        $eventdata->component           = 'tool_messageinbound';
        $eventdata->name                = 'messageprocessingsuccess';
        $eventdata->userfrom            = $userfrom;
        $eventdata->userto              = $USER;
        $eventdata->subject             = self::get_reply_subject($this->currentmessagedata->envelope->subject);
        $eventdata->fullmessage         = $plainmessage;
        $eventdata->fullmessageformat   = FORMAT_PLAIN;
        $eventdata->fullmessagehtml     = $htmlmessage;
        $eventdata->smallmessage        = $eventdata->fullmessage;
        $eventdata->notification        = 1;

        if (message_send($eventdata)) {
            mtrace("---> Success notification sent to {$USER->email}.");
        } else {
            mtrace("---> Unable to send success notification.");
        }
        return true;
    }

    
    private function get_reply_subject($subject) {
        $prefix = get_string('replysubjectprefix', 'tool_messageinbound');
        if (!(substr($subject, 0, strlen($prefix)) == $prefix)) {
            $subject = $prefix . ' ' . $subject;
        }

        return $subject;
    }
}
