<?php



class FilterASCIIHexDecode
{
    
    public function decode($data)
    {
        $data = preg_replace('/[^0-9A-Fa-f]/', '', rtrim($data, '>'));
        if ((strlen($data) % 2) == 1) {
            $data .= '0';
        }

        return pack('H*', $data);
    }

    
    public function encode($data, $leaveEOD = false)
    {
        return current(unpack('H*', $data)) . ($leaveEOD ? '' : '>');
    }
}