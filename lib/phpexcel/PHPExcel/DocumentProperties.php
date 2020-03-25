<?php


class PHPExcel_DocumentProperties
{
    
    const PROPERTY_TYPE_BOOLEAN = 'b';
    const PROPERTY_TYPE_INTEGER = 'i';
    const PROPERTY_TYPE_FLOAT   = 'f';
    const PROPERTY_TYPE_DATE    = 'd';
    const PROPERTY_TYPE_STRING  = 's';
    const PROPERTY_TYPE_UNKNOWN = 'u';

    
    private $creator = 'Unknown Creator';

    
    private $lastModifiedBy;

    
    private $created;

    
    private $modified;

    
    private $title = 'Untitled Spreadsheet';

    
    private $description = '';

    
    private $subject = '';

    
    private $keywords = '';

    
    private $category = '';

    
    private $manager = '';

    
    private $company = 'Microsoft Corporation';

    
    private $customProperties = array();


    
    public function __construct()
    {
                $this->lastModifiedBy = $this->creator;
        $this->created  = time();
        $this->modified = time();
    }

    
    public function getCreator()
    {
        return $this->creator;
    }

    
    public function setCreator($pValue = '')
    {
        $this->creator = $pValue;
        return $this;
    }

    
    public function getLastModifiedBy()
    {
        return $this->lastModifiedBy;
    }

    
    public function setLastModifiedBy($pValue = '')
    {
        $this->lastModifiedBy = $pValue;
        return $this;
    }

    
    public function getCreated()
    {
        return $this->created;
    }

    
    public function setCreated($pValue = null)
    {
        if ($pValue === null) {
            $pValue = time();
        } elseif (is_string($pValue)) {
            if (is_numeric($pValue)) {
                $pValue = intval($pValue);
            } else {
                $pValue = strtotime($pValue);
            }
        }

        $this->created = $pValue;
        return $this;
    }

    
    public function getModified()
    {
        return $this->modified;
    }

    
    public function setModified($pValue = null)
    {
        if ($pValue === null) {
            $pValue = time();
        } elseif (is_string($pValue)) {
            if (is_numeric($pValue)) {
                $pValue = intval($pValue);
            } else {
                $pValue = strtotime($pValue);
            }
        }

        $this->modified = $pValue;
        return $this;
    }

    
    public function getTitle()
    {
        return $this->title;
    }

    
    public function setTitle($pValue = '')
    {
        $this->title = $pValue;
        return $this;
    }

    
    public function getDescription()
    {
        return $this->description;
    }

    
    public function setDescription($pValue = '')
    {
        $this->description = $pValue;
        return $this;
    }

    
    public function getSubject()
    {
        return $this->subject;
    }

    
    public function setSubject($pValue = '')
    {
        $this->subject = $pValue;
        return $this;
    }

    
    public function getKeywords()
    {
        return $this->keywords;
    }

    
    public function setKeywords($pValue = '')
    {
        $this->keywords = $pValue;
        return $this;
    }

    
    public function getCategory()
    {
        return $this->category;
    }

    
    public function setCategory($pValue = '')
    {
        $this->category = $pValue;
        return $this;
    }

    
    public function getCompany()
    {
        return $this->company;
    }

    
    public function setCompany($pValue = '')
    {
        $this->company = $pValue;
        return $this;
    }

    
    public function getManager()
    {
        return $this->manager;
    }

    
    public function setManager($pValue = '')
    {
        $this->manager = $pValue;
        return $this;
    }

    
    public function getCustomProperties()
    {
        return array_keys($this->customProperties);
    }

    
    public function isCustomPropertySet($propertyName)
    {
        return isset($this->customProperties[$propertyName]);
    }

    
    public function getCustomPropertyValue($propertyName)
    {
        if (isset($this->customProperties[$propertyName])) {
            return $this->customProperties[$propertyName]['value'];
        }

    }

    
    public function getCustomPropertyType($propertyName)
    {
        if (isset($this->customProperties[$propertyName])) {
            return $this->customProperties[$propertyName]['type'];
        }

    }

    
    public function setCustomProperty($propertyName, $propertyValue = '', $propertyType = null)
    {
        if (($propertyType === null) || (!in_array($propertyType, array(self::PROPERTY_TYPE_INTEGER,
                                                                        self::PROPERTY_TYPE_FLOAT,
                                                                        self::PROPERTY_TYPE_STRING,
                                                                        self::PROPERTY_TYPE_DATE,
                                                                        self::PROPERTY_TYPE_BOOLEAN)))) {
            if ($propertyValue === null) {
                $propertyType = self::PROPERTY_TYPE_STRING;
            } elseif (is_float($propertyValue)) {
                $propertyType = self::PROPERTY_TYPE_FLOAT;
            } elseif (is_int($propertyValue)) {
                $propertyType = self::PROPERTY_TYPE_INTEGER;
            } elseif (is_bool($propertyValue)) {
                $propertyType = self::PROPERTY_TYPE_BOOLEAN;
            } else {
                $propertyType = self::PROPERTY_TYPE_STRING;
            }
        }

        $this->customProperties[$propertyName] = array(
            'value' => $propertyValue,
            'type' => $propertyType
        );
        return $this;
    }

    
    public function __clone()
    {
        $vars = get_object_vars($this);
        foreach ($vars as $key => $value) {
            if (is_object($value)) {
                $this->$key = clone $value;
            } else {
                $this->$key = $value;
            }
        }
    }

    public static function convertProperty($propertyValue, $propertyType)
    {
        switch ($propertyType) {
            case 'empty':                     return '';
                break;
            case 'null':                      return null;
                break;
            case 'i1':                    case 'i2':                    case 'i4':                    case 'i8':                    case 'int':                       return (int) $propertyValue;
                break;
            case 'ui1':                   case 'ui2':                   case 'ui4':                   case 'ui8':                   case 'uint':                      return abs((int) $propertyValue);
                break;
            case 'r4':                    case 'r8':                    case 'decimal':                   return (float) $propertyValue;
                break;
            case 'lpstr':                 case 'lpwstr':                case 'bstr':                      return $propertyValue;
                break;
            case 'date':                  case 'filetime':                  return strtotime($propertyValue);
                break;
            case 'bool':                     return ($propertyValue == 'true') ? true : false;
                break;
            case 'cy':                   case 'error':                case 'vector':               case 'array':                case 'blob':                 case 'oblob':                case 'stream':               case 'ostream':              case 'storage':              case 'ostorage':             case 'vstream':              case 'clsid':                case 'cf':                       return $propertyValue;
                break;
        }
        return $propertyValue;
    }

    public static function convertPropertyType($propertyType)
    {
        switch ($propertyType) {
            case 'i1':                   case 'i2':                   case 'i4':                   case 'i8':                   case 'int':                  case 'ui1':                  case 'ui2':                  case 'ui4':                  case 'ui8':                  case 'uint':                     return self::PROPERTY_TYPE_INTEGER;
                break;
            case 'r4':                   case 'r8':                   case 'decimal':                  return self::PROPERTY_TYPE_FLOAT;
                break;
            case 'empty':                case 'null':                 case 'lpstr':                case 'lpwstr':               case 'bstr':                     return self::PROPERTY_TYPE_STRING;
                break;
            case 'date':                 case 'filetime':                 return self::PROPERTY_TYPE_DATE;
                break;
            case 'bool':                     return self::PROPERTY_TYPE_BOOLEAN;
                break;
            case 'cy':                   case 'error':                case 'vector':               case 'array':                case 'blob':                 case 'oblob':                case 'stream':               case 'ostream':              case 'storage':              case 'ostorage':             case 'vstream':              case 'clsid':                case 'cf':                       return self::PROPERTY_TYPE_UNKNOWN;
                break;
        }
        return self::PROPERTY_TYPE_UNKNOWN;
    }
}
