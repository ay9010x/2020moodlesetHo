<?php



class Horde_Mail_Rfc822_GroupList extends Horde_Mail_Rfc822_List
{
    
    public function add($obs)
    {
        if ($obs instanceof Horde_Mail_Rfc822_Object) {
            $obs = array($obs);
        }

        foreach ($obs as $val) {
            
            if ($val instanceof Horde_Mail_Rfc822_Address) {
                parent::add($val);
            }
        }
    }

    
    public function groupCount()
    {
        return 0;
    }

}
