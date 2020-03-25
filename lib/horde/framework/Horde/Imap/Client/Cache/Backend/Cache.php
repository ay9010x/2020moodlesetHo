<?php



class Horde_Imap_Client_Cache_Backend_Cache extends Horde_Imap_Client_Cache_Backend
{
    
    const VERSION = 3;

    
    protected $_cache;

    
    protected $_data = array();

    
    protected $_loaded = array();

    
    protected $_slicemap = array();

    
    protected $_update = array();

    
    public function __construct(array $params = array())
    {
                $params = array_merge(array(
            'lifetime' => 604800,
            'slicesize' => 50
        ), array_filter($params));

        if (!isset($params['cacheob'])) {
            throw new InvalidArgumentException('Missing cacheob parameter.');
        }

        foreach (array('lifetime', 'slicesize') as $val) {
            $params[$val] = intval($params[$val]);
        }

        parent::__construct($params);
    }

    
    protected function _initOb()
    {
        $this->_cache = $this->_params['cacheob'];
        register_shutdown_function(array($this, 'save'));
    }

    
    public function save()
    {
        $lifetime = $this->_params['lifetime'];

        foreach ($this->_update as $mbox => $val) {
            $s = &$this->_slicemap[$mbox];

            if (!empty($val['add'])) {
                if ($s['c'] <= $this->_params['slicesize']) {
                    $val['slice'][] = $s['i'];
                    $this->_loadSlice($mbox, $s['i']);
                }
                $val['slicemap'] = true;

                foreach (array_keys(array_flip($val['add'])) as $uid) {
                    if ($s['c']++ > $this->_params['slicesize']) {
                        $s['c'] = 0;
                        $val['slice'][] = ++$s['i'];
                        $this->_loadSlice($mbox, $s['i']);
                    }
                    $s['s'][$uid] = $s['i'];
                }
            }

            if (!empty($val['slice'])) {
                $d = &$this->_data[$mbox];
                $val['slicemap'] = true;

                foreach (array_keys(array_flip($val['slice'])) as $slice) {
                    $data = array();
                    foreach (array_keys($s['s'], $slice) as $uid) {
                        $data[$uid] = is_array($d[$uid])
                            ? serialize($d[$uid])
                            : $d[$uid];
                    }
                    $this->_cache->set($this->_getCid($mbox, $slice), serialize($data), $lifetime);
                }
            }

            if (!empty($val['slicemap'])) {
                $this->_cache->set($this->_getCid($mbox, 'slicemap'), serialize($s), $lifetime);
            }
        }

        $this->_update = array();
    }

    
    public function get($mailbox, $uids, $fields, $uidvalid)
    {
        $ret = array();
        $this->_loadUids($mailbox, $uids, $uidvalid);

        if (empty($this->_data[$mailbox])) {
            return $ret;
        }

        if (!empty($fields)) {
            $fields = array_flip($fields);
        }
        $ptr = &$this->_data[$mailbox];

        foreach (array_intersect($uids, array_keys($ptr)) as $val) {
            if (is_string($ptr[$val])) {
                $ptr[$val] = @unserialize($ptr[$val]);
            }

            $ret[$val] = (empty($fields) || empty($ptr[$val]))
                ? $ptr[$val]
                : array_intersect_key($ptr[$val], $fields);
        }

        return $ret;
    }

    
    public function getCachedUids($mailbox, $uidvalid)
    {
        $this->_loadSliceMap($mailbox, $uidvalid);
        return array_unique(array_merge(
            array_keys($this->_slicemap[$mailbox]['s']),
            (isset($this->_update[$mailbox]) ? $this->_update[$mailbox]['add'] : array())
        ));
    }

    
    public function set($mailbox, $data, $uidvalid)
    {
        $update = array_keys($data);

        try {
            $this->_loadUids($mailbox, $update, $uidvalid);
        } catch (Horde_Imap_Client_Exception $e) {
                    }

        $d = &$this->_data[$mailbox];
        $s = &$this->_slicemap[$mailbox]['s'];
        $add = $updated = array();

        foreach ($data as $k => $v) {
            if (isset($d[$k])) {
                if (is_string($d[$k])) {
                    $d[$k] = @unserialize($d[$k]);
                }
                $d[$k] = is_array($d[$k])
                    ? array_merge($d[$k], $v)
                    : $v;
                if (isset($s[$k])) {
                    $updated[$s[$k]] = true;
                }
            } else {
                $d[$k] = $v;
                $add[] = $k;
            }
        }

        $this->_toUpdate($mailbox, 'add', $add);
        $this->_toUpdate($mailbox, 'slice', array_keys($updated));
    }

    
    public function getMetaData($mailbox, $uidvalid, $entries)
    {
        $this->_loadSliceMap($mailbox, $uidvalid);

        return empty($entries)
            ? $this->_slicemap[$mailbox]['d']
            : array_intersect_key($this->_slicemap[$mailbox]['d'], array_flip($entries));
    }

    
    public function setMetaData($mailbox, $data)
    {
        $this->_loadSliceMap($mailbox, isset($data['uidvalid']) ? $data['uidvalid'] : null);
        $this->_slicemap[$mailbox]['d'] = array_merge($this->_slicemap[$mailbox]['d'], $data);
        $this->_toUpdate($mailbox, 'slicemap', true);
    }

    
    public function deleteMsgs($mailbox, $uids)
    {
        if (empty($uids)) {
            return;
        }

        $this->_loadSliceMap($mailbox);

        $slicemap = &$this->_slicemap[$mailbox];
        $deleted = array_intersect_key($slicemap['s'], array_flip($uids));

        if (isset($this->_update[$mailbox])) {
            $this->_update[$mailbox]['add'] = array_diff(
                $this->_update[$mailbox]['add'],
                $uids
            );
        }

        if (empty($deleted)) {
            return;
        }

        $this->_loadUids($mailbox, array_keys($deleted));
        $d = &$this->_data[$mailbox];

        foreach (array_keys($deleted) as $id) {
            unset($d[$id], $slicemap['s'][$id]);
        }

        foreach (array_unique($deleted) as $slice) {
            
            if (($slice != $slicemap['i']) &&
                ($slice_uids = array_keys($slicemap['s'], $slice)) &&
                ($this->_params['slicesize'] * 0.1) > count($slice_uids)) {
                $this->_toUpdate($mailbox, 'add', $slice_uids);
                $this->_cache->expire($this->_getCid($mailbox, $slice));
                foreach ($slice_uids as $val) {
                    unset($slicemap['s'][$val]);
                }
            } else {
                $this->_toUpdate($mailbox, 'slice', array($slice));
            }
        }
    }

    
    public function deleteMailbox($mailbox)
    {
        $this->_loadSliceMap($mailbox);
        $this->_deleteMailbox($mailbox);
    }

    
    public function clear($lifetime)
    {
        $this->_cache->clear();
        $this->_data = $this->_loaded = $this->_slicemap = $this->_update = array();
    }

    
    protected function _getCid($mailbox, $slice)
    {
        return implode('|', array(
            'horde_imap_client',
            $this->_params['username'],
            $mailbox,
            $this->_params['hostspec'],
            $this->_params['port'],
            $slice,
            self::VERSION
        ));
    }

    
    protected function _deleteMailbox($mbox)
    {
        foreach (array_merge(array_keys(array_flip($this->_slicemap[$mbox]['s'])), array('slicemap')) as $slice) {
            $cid = $this->_getCid($mbox, $slice);
            $this->_cache->expire($cid);
            unset($this->_loaded[$cid]);
        }

        unset(
            $this->_data[$mbox],
            $this->_slicemap[$mbox],
            $this->_update[$mbox]
        );
    }

    
    protected function _loadUids($mailbox, $uids, $uidvalid = null)
    {
        if (!isset($this->_data[$mailbox])) {
            $this->_data[$mailbox] = array();
        }

        $this->_loadSliceMap($mailbox, $uidvalid);

        if (!empty($uids)) {
            foreach (array_unique(array_intersect_key($this->_slicemap[$mailbox]['s'], array_flip($uids))) as $slice) {
                $this->_loadSlice($mailbox, $slice);
            }
        }
    }

    
    protected function _loadSlice($mailbox, $slice)
    {
        $cache_id = $this->_getCid($mailbox, $slice);

        if (!empty($this->_loaded[$cache_id])) {
            return;
        }

        if ((($data = $this->_cache->get($cache_id, 0)) !== false) &&
            ($data = @unserialize($data)) &&
            is_array($data)) {
            $this->_data[$mailbox] += $data;
            $this->_loaded[$cache_id] = true;
        } else {
            $ptr = &$this->_slicemap[$mailbox];

                        foreach (array_keys($ptr['s'], $slice) as $val) {
                unset($ptr['s'][$val]);
            }

            if ($slice == $ptr['i']) {
                $ptr['c'] = 0;
            }
        }
    }

    
    protected function _loadSliceMap($mailbox, $uidvalid = null)
    {
        if (!isset($this->_slicemap[$mailbox]) &&
            (($data = $this->_cache->get($this->_getCid($mailbox, 'slicemap'), 0)) !== false) &&
            ($slice = @unserialize($data)) &&
            is_array($slice)) {
            $this->_slicemap[$mailbox] = $slice;
        }

        if (isset($this->_slicemap[$mailbox])) {
            $ptr = &$this->_slicemap[$mailbox];
            if (is_null($ptr['d']['uidvalid'])) {
                $ptr['d']['uidvalid'] = $uidvalid;
                return;
            } elseif (!is_null($uidvalid) &&
                      ($ptr['d']['uidvalid'] != $uidvalid)) {
                $this->_deleteMailbox($mailbox);
            } else {
                return;
            }
        }

        $this->_slicemap[$mailbox] = array(
                        'c' => 0,
                                    'd' => array('uidvalid' => $uidvalid),
                        'i' => 0,
                        's' => array()
        );
    }

    
    protected function _toUpdate($mailbox, $type, $data)
    {
        if (!isset($this->_update[$mailbox])) {
            $this->_update[$mailbox] = array(
                'add' => array(),
                'slice' => array()
            );
        }

        $this->_update[$mailbox][$type] = ($type == 'slicemap')
            ? $data
            : array_merge($this->_update[$mailbox][$type], $data);
    }

    

    
    public function serialize()
    {
        $this->save();
        return parent::serialize();
    }

}
