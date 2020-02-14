<?php

class Horde_Support_ConsistentHash
{
    
    protected $_numberOfReplicas = 100;

    
    protected $_circle = array();

    
    protected $_pointMap = array();

    
    protected $_pointCount = 0;

    
    protected $_nodes = array();

    
    protected $_nodeCount = 0;

    
    public function __construct($nodes = array(), $weight = 1, $numberOfReplicas = 100)
    {
        $this->_numberOfReplicas = $numberOfReplicas;
        $this->addNodes($nodes, $weight);
    }

    
    public function get($key)
    {
        $nodes = $this->getNodes($key, 1);
        if (!$nodes) {
            throw new Exception('No nodes found');
        }
        return $nodes[0];
    }

    
    public function getNodes($key, $count = 5)
    {
                if ($this->_nodeCount < $count) {
            throw new Exception('Not enough nodes (have ' . $this->_nodeCount . ', ' . $count . ' requested)');
        }
        if ($this->_nodeCount == 0) {
            return array();
        }

                if ($this->_nodeCount == 1) {
            return array($this->_nodes[0]['n']);
        }

        $hash = $this->hash(serialize($key));

                $low = 0;
        $high = $this->_pointCount - 1;
        $index = null;
        while (true) {
            $mid = (int)(($low + $high) / 2);
            if ($mid == $this->_pointCount) {
                $index = 0;
                break;
            }

            $midval = $this->_pointMap[$mid];
            $midval1 = ($mid == 0) ? 0 : $this->_pointMap[$mid - 1];
            if ($midval1 < $hash && $hash <= $midval) {
                $index = $mid;
                break;
            }

            if ($midval > $hash) {
                $high = $mid - 1;
            } else {
                $low = $mid + 1;
            }

            if ($low > $high) {
                $index = 0;
                break;
            }
        }

        $nodes = array();
        while (count($nodes) < $count) {
            $nodeIndex = $this->_pointMap[$index++ % $this->_pointCount];
            $nodes[$nodeIndex] = $this->_nodes[$this->_circle[$nodeIndex]]['n'];
        }
        return array_values($nodes);
    }

    
    public function add($node, $weight = 1)
    {
                        $this->addNodes(array($node), $weight);
    }

    
    public function addNodes($nodes, $weight = 1)
    {
        foreach ($nodes as $node) {
            $this->_nodes[] = array('n' => $node, 'w' => $weight);
            $this->_nodeCount++;

            $nodeIndex = $this->_nodeCount - 1;
            $nodeString = serialize($node);

            $numberOfReplicas = (int)($weight * $this->_numberOfReplicas);
            for ($i = 0; $i < $numberOfReplicas; $i++) {
                $this->_circle[$this->hash($nodeString . $i)] = $nodeIndex;
            }
        }

        $this->_updateCircle();
    }

    
    public function remove($node)
    {
        $nodeIndex = null;
        $nodeString = serialize($node);

                foreach (array_keys($this->_nodes) as $i) {
            if ($this->_nodes[$i]['n'] === $node) {
                $nodeIndex = $i;
                break;
            }
        }

        if (is_null($nodeIndex)) {
            throw new InvalidArgumentException('Node was not in the hash');
        }

                $numberOfReplicas = (int)($this->_nodes[$nodeIndex]['w'] * $this->_numberOfReplicas);
        for ($i = 0; $i < $numberOfReplicas; $i++) {
            unset($this->_circle[$this->hash($nodeString . $i)]);
        }
        $this->_updateCircle();

                unset($this->_nodes[$nodeIndex]);
        $this->_nodeCount--;
    }

    
    public function hash($key)
    {
        return 'h' . substr(hash('md5', $key), 0, 8);
    }

    
    protected function _updateCircle()
    {
                ksort($this->_circle);

                        $this->_pointMap = array_keys($this->_circle);
        $this->_pointCount = count($this->_pointMap);
    }

}
