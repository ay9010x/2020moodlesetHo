<?php



class Google_Service_Licensing extends Google_Service
{
  
  const APPS_LICENSING =
      "https://www.googleapis.com/auth/apps.licensing";

  public $licenseAssignments;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'apps/licensing/v1/product/';
    $this->version = 'v1';
    $this->serviceName = 'licensing';

    $this->licenseAssignments = new Google_Service_Licensing_LicenseAssignments_Resource(
        $this,
        $this->serviceName,
        'licenseAssignments',
        array(
          'methods' => array(
            'delete' => array(
              'path' => '{productId}/sku/{skuId}/user/{userId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'productId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'skuId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => '{productId}/sku/{skuId}/user/{userId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'productId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'skuId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => '{productId}/sku/{skuId}/user',
              'httpMethod' => 'POST',
              'parameters' => array(
                'productId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'skuId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'listForProduct' => array(
              'path' => '{productId}/users',
              'httpMethod' => 'GET',
              'parameters' => array(
                'productId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'customerId' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),'listForProductAndSku' => array(
              'path' => '{productId}/sku/{skuId}/users',
              'httpMethod' => 'GET',
              'parameters' => array(
                'productId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'skuId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'customerId' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),'patch' => array(
              'path' => '{productId}/sku/{skuId}/user/{userId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'productId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'skuId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => '{productId}/sku/{skuId}/user/{userId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'productId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'skuId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
  }
}



class Google_Service_Licensing_LicenseAssignments_Resource extends Google_Service_Resource
{

  
  public function delete($productId, $skuId, $userId, $optParams = array())
  {
    $params = array('productId' => $productId, 'skuId' => $skuId, 'userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($productId, $skuId, $userId, $optParams = array())
  {
    $params = array('productId' => $productId, 'skuId' => $skuId, 'userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Licensing_LicenseAssignment");
  }

  
  public function insert($productId, $skuId, Google_Service_Licensing_LicenseAssignmentInsert $postBody, $optParams = array())
  {
    $params = array('productId' => $productId, 'skuId' => $skuId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Licensing_LicenseAssignment");
  }

  
  public function listForProduct($productId, $customerId, $optParams = array())
  {
    $params = array('productId' => $productId, 'customerId' => $customerId);
    $params = array_merge($params, $optParams);
    return $this->call('listForProduct', array($params), "Google_Service_Licensing_LicenseAssignmentList");
  }

  
  public function listForProductAndSku($productId, $skuId, $customerId, $optParams = array())
  {
    $params = array('productId' => $productId, 'skuId' => $skuId, 'customerId' => $customerId);
    $params = array_merge($params, $optParams);
    return $this->call('listForProductAndSku', array($params), "Google_Service_Licensing_LicenseAssignmentList");
  }

  
  public function patch($productId, $skuId, $userId, Google_Service_Licensing_LicenseAssignment $postBody, $optParams = array())
  {
    $params = array('productId' => $productId, 'skuId' => $skuId, 'userId' => $userId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Licensing_LicenseAssignment");
  }

  
  public function update($productId, $skuId, $userId, Google_Service_Licensing_LicenseAssignment $postBody, $optParams = array())
  {
    $params = array('productId' => $productId, 'skuId' => $skuId, 'userId' => $userId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Licensing_LicenseAssignment");
  }
}




class Google_Service_Licensing_LicenseAssignment extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $etags;
  public $kind;
  public $productId;
  public $selfLink;
  public $skuId;
  public $userId;


  public function setEtags($etags)
  {
    $this->etags = $etags;
  }
  public function getEtags()
  {
    return $this->etags;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setProductId($productId)
  {
    $this->productId = $productId;
  }
  public function getProductId()
  {
    return $this->productId;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setSkuId($skuId)
  {
    $this->skuId = $skuId;
  }
  public function getSkuId()
  {
    return $this->skuId;
  }
  public function setUserId($userId)
  {
    $this->userId = $userId;
  }
  public function getUserId()
  {
    return $this->userId;
  }
}

class Google_Service_Licensing_LicenseAssignmentInsert extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $userId;


  public function setUserId($userId)
  {
    $this->userId = $userId;
  }
  public function getUserId()
  {
    return $this->userId;
  }
}

class Google_Service_Licensing_LicenseAssignmentList extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  protected $itemsType = 'Google_Service_Licensing_LicenseAssignment';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
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
  public function setNextPageToken($nextPageToken)
  {
    $this->nextPageToken = $nextPageToken;
  }
  public function getNextPageToken()
  {
    return $this->nextPageToken;
  }
}
