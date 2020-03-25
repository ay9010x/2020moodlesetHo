<?php



class Horde_Imap_Client_Socket_Catenate
{
    
    protected $_socket;

    
    public function __construct(Horde_Imap_Client_Socket $socket)
    {
        $this->_socket = $socket;
    }

    
    public function fetchFromUrl(Horde_Imap_Client_Url $url)
    {
        $ids_ob = $this->_socket->getIdsOb($url->uid);

                if (is_null($url->section)) {
            $query = new Horde_Imap_Client_Fetch_Query();
            $query->fullText(array(
                'peek' => true
            ));

            $fetch = $this->_socket->fetch($url->mailbox, $query, array(
                'ids' => $ids_ob
            ));
            return $fetch[$url->uid]->getFullMsg(true);
        }

        $section = trim($url->section);

                if (($pos = stripos($section, 'HEADER.FIELDS')) !== false) {
            $hdr_pos = strpos($section, '(');
            $cmd = substr($section, 0, $hdr_pos);

            $query = new Horde_Imap_Client_Fetch_Query();
            $query->headers(
                'section',
                explode(' ', substr($section, $hdr_pos + 1, strrpos($section, ')') - $hdr_pos)),
                array(
                    'id' => ($pos ? substr($section, 0, $pos - 1) : 0),
                    'notsearch' => (stripos($cmd, '.NOT') !== false),
                    'peek' => true
                )
            );

            $fetch = $this->_socket->fetch($url->mailbox, $query, array(
                'ids' => $ids_ob
            ));
            return $fetch[$url->uid]->getHeaders('section', Horde_Imap_Client_Data_Fetch::HEADER_STREAM);
        }

                if (is_numeric(substr($section, -1))) {
            $query = new Horde_Imap_Client_Fetch_Query();
            $query->bodyPart($section, array(
                'peek' => true
            ));

            $fetch = $this->_socket->fetch($url->mailbox, $query, array(
                'ids' => $ids_ob
            ));
            return $fetch[$url->uid]->getBodyPart($section, true);
        }

                if (($pos = stripos($section, 'HEADER')) !== false) {
            $id = $pos
                ? substr($section, 0, $pos - 1)
                : 0;

            $query = new Horde_Imap_Client_Fetch_Query();
            $query->headerText(array(
                'id' => $id,
                'peek' => true
            ));

            $fetch = $this->_socket->fetch($url->mailbox, $query, array(
                'ids' => $ids_ob
            ));
            return $fetch[$url->uid]->getHeaderText($id, Horde_Imap_Client_Data_Fetch::HEADER_STREAM);
        }

                if (($pos = stripos($section, 'TEXT')) !== false) {
            $id = $pos
                ? substr($section, 0, $pos - 1)
                : 0;

            $query = new Horde_Imap_Client_Fetch_Query();
            $query->bodyText(array(
                'id' => $id,
                'peek' => true
            ));

            $fetch = $this->_socket->fetch($url->mailbox, $query, array(
                'ids' => $ids_ob
            ));
            return $fetch[$url->uid]->getBodyText($id, true);
        }

                if (($pos = stripos($section, 'MIME')) !== false) {
            $id = $pos
                ? substr($section, 0, $pos - 1)
                : 0;

            $query = new Horde_Imap_Client_Fetch_Query();
            $query->mimeHeader($id, array(
                'peek' => true
            ));

            $fetch = $this->_socket->fetch($url->mailbox, $query, array(
                'ids' => $ids_ob
            ));
            return $fetch[$url->uid]->getMimeHeader($id, Horde_Imap_Client_Data_Fetch::HEADER_STREAM);
        }

        return null;
    }

}
