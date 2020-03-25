<?php



class Horde_Imap_Client_Base_Deprecated
{
    
    static public function getCacheId($base_ob, $mailbox, $condstore,
                                      array $addl = array())
    {
        $query = Horde_Imap_Client::STATUS_UIDVALIDITY | Horde_Imap_Client::STATUS_MESSAGES | Horde_Imap_Client::STATUS_UIDNEXT;

        
        if ($condstore) {
            $query |= Horde_Imap_Client::STATUS_HIGHESTMODSEQ;
        } else {
            $query |= Horde_Imap_Client::STATUS_UIDNEXT_FORCE;
        }

        $status = $base_ob->status($mailbox, $query);

        if (empty($status['highestmodseq'])) {
            $parts = array(
                'V' . $status['uidvalidity'],
                'U' . $status['uidnext'],
                'M' . $status['messages']
            );
        } else {
            $parts = array(
                'V' . $status['uidvalidity'],
                'H' . $status['highestmodseq']
            );
        }

        return implode('|', array_merge($parts, $addl));
    }

    
    static public function parseCacheId($id)
    {
        $data = array(
            'H' => 'highestmodseq',
            'M' => 'messages',
            'U' => 'uidnext',
            'V' => 'uidvalidity'
        );
        $info = array();

        foreach (explode('|', $id) as $part) {
            if (isset($data[$part[0]])) {
                $info[$data[$part[0]]] = intval(substr($part, 1));
            }
        }

        return $info;
    }

}
