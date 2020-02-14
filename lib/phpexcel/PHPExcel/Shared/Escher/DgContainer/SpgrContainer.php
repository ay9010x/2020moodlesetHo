<?php


class PHPExcel_Shared_Escher_DgContainer_SpgrContainer
{
    
    private $parent;

    
    private $children = array();

    
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    
    public function getParent()
    {
        return $this->parent;
    }

    
    public function addChild($child)
    {
        $this->children[] = $child;
        $child->setParent($this);
    }

    
    public function getChildren()
    {
        return $this->children;
    }

    
    public function getAllSpContainers()
    {
        $allSpContainers = array();

        foreach ($this->children as $child) {
            if ($child instanceof PHPExcel_Shared_Escher_DgContainer_SpgrContainer) {
                $allSpContainers = array_merge($allSpContainers, $child->getAllSpContainers());
            } else {
                $allSpContainers[] = $child;
            }
        }

        return $allSpContainers;
    }
}
