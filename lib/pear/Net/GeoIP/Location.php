<?php



class Net_GeoIP_Location implements Serializable
{
    protected $aData = array(
        'countryCode'  => null,
        'countryCode3' => null,
        'countryName'  => null,
        'region'       => null,
        'city'         => null,
        'postalCode'   => null,
        'latitude'     => null,
        'longitude'    => null,
        'areaCode'     => null,
        'dmaCode'      => null
    );


    
    public function distance(Net_GeoIP_Location $loc)
    {
                        $RAD_CONVERT = M_PI / 180;
        $EARTH_DIAMETER = 2 * 6378.2;

        $lat1 = $this->latitude;
        $lon1 = $this->longitude;
        $lat2 = $loc->latitude;
        $lon2 = $loc->longitude;

                $lat1 *= $RAD_CONVERT;
        $lat2 *= $RAD_CONVERT;

                $delta_lat = $lat2 - $lat1;
        $delta_lon = ($lon2 - $lon1) * $RAD_CONVERT;

                $temp = pow(sin($delta_lat/2), 2) + cos($lat1) * cos($lat2) * pow(sin($delta_lon/2), 2);
        return $EARTH_DIAMETER * atan2(sqrt($temp), sqrt(1-$temp));
    }

    
    public function serialize()
    {
        return serialize($this->aData);
    }

    
    public function unserialize($serialized)
    {
        $this->aData = unserialize($serialized);
    }


    
    public function set($name, $val)
    {
        if (array_key_exists($name, $this->aData)) {
            $this->aData[$name] = $val;
        }

        return $this;
    }

    public function __set($name, $val)
    {
        return $this->set($name, $val);
    }

    
    public function getData()
    {
         return $this->aData;
    }


    
    public function __get($name)
    {
        if (array_key_exists($name, $this->aData)) {
            return $this->aData[$name];
        }

        return null;
    }


    
    public function __toString()
    {
        return 'object of type '.__CLASS__.'. data: '.implode(',', $this->aData);
    }


    
    public function __isset($name)
    {
        return (null !== $this->__get($name));
    }

}
