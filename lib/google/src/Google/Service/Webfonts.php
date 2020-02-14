<?php



class Google_Service_Webfonts extends Google_Service
{


  public $webfonts;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'webfonts/v1/';
    $this->version = 'v1';
    $this->serviceName = 'webfonts';

    $this->webfonts = new Google_Service_Webfonts_Webfonts_Resource(
        $this,
        $this->serviceName,
        'webfonts',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'webfonts',
              'httpMethod' => 'GET',
              'parameters' => array(
                'sort' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
  }
}



class Google_Service_Webfonts_Webfonts_Resource extends Google_Service_Resource
{

  
  public function listWebfonts($optParams = array())
  {
    $params = array();
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Webfonts_WebfontList");
  }
}




class Google_Service_Webfonts_Webfont extends Google_Collection
{
  protected $collection_key = 'variants';
  protected $internal_gapi_mappings = array(
  );
  public $category;
  public $family;
  public $files;
  public $kind;
  public $lastModified;
  public $subsets;
  public $variants;
  public $version;


  public function setCategory($category)
  {
    $this->category = $category;
  }
  public function getCategory()
  {
    return $this->category;
  }
  public function setFamily($family)
  {
    $this->family = $family;
  }
  public function getFamily()
  {
    return $this->family;
  }
  public function setFiles($files)
  {
    $this->files = $files;
  }
  public function getFiles()
  {
    return $this->files;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setLastModified($lastModified)
  {
    $this->lastModified = $lastModified;
  }
  public function getLastModified()
  {
    return $this->lastModified;
  }
  public function setSubsets($subsets)
  {
    $this->subsets = $subsets;
  }
  public function getSubsets()
  {
    return $this->subsets;
  }
  public function setVariants($variants)
  {
    $this->variants = $variants;
  }
  public function getVariants()
  {
    return $this->variants;
  }
  public function setVersion($version)
  {
    $this->version = $version;
  }
  public function getVersion()
  {
    return $this->version;
  }
}

class Google_Service_Webfonts_WebfontFiles extends Google_Model
{
}

class Google_Service_Webfonts_WebfontList extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Webfonts_Webfont';
  protected $itemsDataType = 'array';
  public $kind;


  public function setItems($items)
  {
    $this->items = $items;
  }
  public function getItems()
  {
    return $this->items;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
}
