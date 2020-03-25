<?php



class Google_Service_Storage extends Google_Service
{
  
  const CLOUD_PLATFORM =
      "https://www.googleapis.com/auth/cloud-platform";
  
  const CLOUD_PLATFORM_READ_ONLY =
      "https://www.googleapis.com/auth/cloud-platform.read-only";
  
  const DEVSTORAGE_FULL_CONTROL =
      "https://www.googleapis.com/auth/devstorage.full_control";
  
  const DEVSTORAGE_READ_ONLY =
      "https://www.googleapis.com/auth/devstorage.read_only";
  
  const DEVSTORAGE_READ_WRITE =
      "https://www.googleapis.com/auth/devstorage.read_write";

  public $bucketAccessControls;
  public $buckets;
  public $channels;
  public $defaultObjectAccessControls;
  public $objectAccessControls;
  public $objects;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'storage/v1/';
    $this->version = 'v1';
    $this->serviceName = 'storage';

    $this->bucketAccessControls = new Google_Service_Storage_BucketAccessControls_Resource(
        $this,
        $this->serviceName,
        'bucketAccessControls',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'b/{bucket}/acl/{entity}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'entity' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'b/{bucket}/acl/{entity}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'entity' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'b/{bucket}/acl',
              'httpMethod' => 'POST',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'b/{bucket}/acl',
              'httpMethod' => 'GET',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'patch' => array(
              'path' => 'b/{bucket}/acl/{entity}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'entity' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'b/{bucket}/acl/{entity}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'entity' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->buckets = new Google_Service_Storage_Buckets_Resource(
        $this,
        $this->serviceName,
        'buckets',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'b/{bucket}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'ifMetagenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifMetagenerationNotMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'get' => array(
              'path' => 'b/{bucket}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'ifMetagenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifMetagenerationNotMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'projection' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'insert' => array(
              'path' => 'b',
              'httpMethod' => 'POST',
              'parameters' => array(
                'project' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'predefinedAcl' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'projection' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'predefinedDefaultObjectAcl' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => 'b',
              'httpMethod' => 'GET',
              'parameters' => array(
                'project' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'prefix' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'projection' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),'patch' => array(
              'path' => 'b/{bucket}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'projection' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifMetagenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'predefinedDefaultObjectAcl' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'predefinedAcl' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifMetagenerationNotMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'update' => array(
              'path' => 'b/{bucket}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'projection' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifMetagenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'predefinedDefaultObjectAcl' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'predefinedAcl' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifMetagenerationNotMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->channels = new Google_Service_Storage_Channels_Resource(
        $this,
        $this->serviceName,
        'channels',
        array(
          'methods' => array(
            'stop' => array(
              'path' => 'channels/stop',
              'httpMethod' => 'POST',
              'parameters' => array(),
            ),
          )
        )
    );
    $this->defaultObjectAccessControls = new Google_Service_Storage_DefaultObjectAccessControls_Resource(
        $this,
        $this->serviceName,
        'defaultObjectAccessControls',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'b/{bucket}/defaultObjectAcl/{entity}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'entity' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'b/{bucket}/defaultObjectAcl/{entity}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'entity' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'b/{bucket}/defaultObjectAcl',
              'httpMethod' => 'POST',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'b/{bucket}/defaultObjectAcl',
              'httpMethod' => 'GET',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'ifMetagenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifMetagenerationNotMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'patch' => array(
              'path' => 'b/{bucket}/defaultObjectAcl/{entity}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'entity' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'b/{bucket}/defaultObjectAcl/{entity}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'entity' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->objectAccessControls = new Google_Service_Storage_ObjectAccessControls_Resource(
        $this,
        $this->serviceName,
        'objectAccessControls',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'b/{bucket}/o/{object}/acl/{entity}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'object' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'entity' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'generation' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'get' => array(
              'path' => 'b/{bucket}/o/{object}/acl/{entity}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'object' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'entity' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'generation' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'insert' => array(
              'path' => 'b/{bucket}/o/{object}/acl',
              'httpMethod' => 'POST',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'object' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'generation' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => 'b/{bucket}/o/{object}/acl',
              'httpMethod' => 'GET',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'object' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'generation' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'patch' => array(
              'path' => 'b/{bucket}/o/{object}/acl/{entity}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'object' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'entity' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'generation' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'update' => array(
              'path' => 'b/{bucket}/o/{object}/acl/{entity}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'object' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'entity' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'generation' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->objects = new Google_Service_Storage_Objects_Resource(
        $this,
        $this->serviceName,
        'objects',
        array(
          'methods' => array(
            'compose' => array(
              'path' => 'b/{destinationBucket}/o/{destinationObject}/compose',
              'httpMethod' => 'POST',
              'parameters' => array(
                'destinationBucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'destinationObject' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'ifGenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifMetagenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'destinationPredefinedAcl' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'copy' => array(
              'path' => 'b/{sourceBucket}/o/{sourceObject}/copyTo/b/{destinationBucket}/o/{destinationObject}',
              'httpMethod' => 'POST',
              'parameters' => array(
                'sourceBucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'sourceObject' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'destinationBucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'destinationObject' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'ifSourceGenerationNotMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifGenerationNotMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifSourceMetagenerationNotMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifMetagenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'sourceGeneration' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'destinationPredefinedAcl' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifSourceGenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifSourceMetagenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifGenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifMetagenerationNotMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'projection' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'delete' => array(
              'path' => 'b/{bucket}/o/{object}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'object' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'ifGenerationNotMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'generation' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifMetagenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifGenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifMetagenerationNotMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'get' => array(
              'path' => 'b/{bucket}/o/{object}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'object' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'ifGenerationNotMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'generation' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifMetagenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifGenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifMetagenerationNotMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'projection' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'insert' => array(
              'path' => 'b/{bucket}/o',
              'httpMethod' => 'POST',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'predefinedAcl' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'projection' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifGenerationNotMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifMetagenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'contentEncoding' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifGenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifMetagenerationNotMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'name' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => 'b/{bucket}/o',
              'httpMethod' => 'GET',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'projection' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'versions' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'prefix' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'delimiter' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'patch' => array(
              'path' => 'b/{bucket}/o/{object}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'object' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'predefinedAcl' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifGenerationNotMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'generation' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifMetagenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifGenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifMetagenerationNotMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'projection' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'rewrite' => array(
              'path' => 'b/{sourceBucket}/o/{sourceObject}/rewriteTo/b/{destinationBucket}/o/{destinationObject}',
              'httpMethod' => 'POST',
              'parameters' => array(
                'sourceBucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'sourceObject' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'destinationBucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'destinationObject' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'ifSourceGenerationNotMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifGenerationNotMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'rewriteToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifSourceMetagenerationNotMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifMetagenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'sourceGeneration' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'destinationPredefinedAcl' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifSourceGenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'maxBytesRewrittenPerCall' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifSourceMetagenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifGenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifMetagenerationNotMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'projection' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'update' => array(
              'path' => 'b/{bucket}/o/{object}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'object' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'predefinedAcl' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifGenerationNotMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'generation' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifMetagenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifGenerationMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ifMetagenerationNotMatch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'projection' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'watchAll' => array(
              'path' => 'b/{bucket}/o/watch',
              'httpMethod' => 'POST',
              'parameters' => array(
                'bucket' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'projection' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'versions' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'prefix' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'delimiter' => array(
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



class Google_Service_Storage_BucketAccessControls_Resource extends Google_Service_Resource
{

  
  public function delete($bucket, $entity, $optParams = array())
  {
    $params = array('bucket' => $bucket, 'entity' => $entity);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($bucket, $entity, $optParams = array())
  {
    $params = array('bucket' => $bucket, 'entity' => $entity);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Storage_BucketAccessControl");
  }

  
  public function insert($bucket, Google_Service_Storage_BucketAccessControl $postBody, $optParams = array())
  {
    $params = array('bucket' => $bucket, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Storage_BucketAccessControl");
  }

  
  public function listBucketAccessControls($bucket, $optParams = array())
  {
    $params = array('bucket' => $bucket);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Storage_BucketAccessControls");
  }

  
  public function patch($bucket, $entity, Google_Service_Storage_BucketAccessControl $postBody, $optParams = array())
  {
    $params = array('bucket' => $bucket, 'entity' => $entity, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Storage_BucketAccessControl");
  }

  
  public function update($bucket, $entity, Google_Service_Storage_BucketAccessControl $postBody, $optParams = array())
  {
    $params = array('bucket' => $bucket, 'entity' => $entity, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Storage_BucketAccessControl");
  }
}


class Google_Service_Storage_Buckets_Resource extends Google_Service_Resource
{

  
  public function delete($bucket, $optParams = array())
  {
    $params = array('bucket' => $bucket);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($bucket, $optParams = array())
  {
    $params = array('bucket' => $bucket);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Storage_Bucket");
  }

  
  public function insert($project, Google_Service_Storage_Bucket $postBody, $optParams = array())
  {
    $params = array('project' => $project, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Storage_Bucket");
  }

  
  public function listBuckets($project, $optParams = array())
  {
    $params = array('project' => $project);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Storage_Buckets");
  }

  
  public function patch($bucket, Google_Service_Storage_Bucket $postBody, $optParams = array())
  {
    $params = array('bucket' => $bucket, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Storage_Bucket");
  }

  
  public function update($bucket, Google_Service_Storage_Bucket $postBody, $optParams = array())
  {
    $params = array('bucket' => $bucket, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Storage_Bucket");
  }
}


class Google_Service_Storage_Channels_Resource extends Google_Service_Resource
{

  
  public function stop(Google_Service_Storage_Channel $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('stop', array($params));
  }
}


class Google_Service_Storage_DefaultObjectAccessControls_Resource extends Google_Service_Resource
{

  
  public function delete($bucket, $entity, $optParams = array())
  {
    $params = array('bucket' => $bucket, 'entity' => $entity);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($bucket, $entity, $optParams = array())
  {
    $params = array('bucket' => $bucket, 'entity' => $entity);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Storage_ObjectAccessControl");
  }

  
  public function insert($bucket, Google_Service_Storage_ObjectAccessControl $postBody, $optParams = array())
  {
    $params = array('bucket' => $bucket, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Storage_ObjectAccessControl");
  }

  
  public function listDefaultObjectAccessControls($bucket, $optParams = array())
  {
    $params = array('bucket' => $bucket);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Storage_ObjectAccessControls");
  }

  
  public function patch($bucket, $entity, Google_Service_Storage_ObjectAccessControl $postBody, $optParams = array())
  {
    $params = array('bucket' => $bucket, 'entity' => $entity, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Storage_ObjectAccessControl");
  }

  
  public function update($bucket, $entity, Google_Service_Storage_ObjectAccessControl $postBody, $optParams = array())
  {
    $params = array('bucket' => $bucket, 'entity' => $entity, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Storage_ObjectAccessControl");
  }
}


class Google_Service_Storage_ObjectAccessControls_Resource extends Google_Service_Resource
{

  
  public function delete($bucket, $object, $entity, $optParams = array())
  {
    $params = array('bucket' => $bucket, 'object' => $object, 'entity' => $entity);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($bucket, $object, $entity, $optParams = array())
  {
    $params = array('bucket' => $bucket, 'object' => $object, 'entity' => $entity);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Storage_ObjectAccessControl");
  }

  
  public function insert($bucket, $object, Google_Service_Storage_ObjectAccessControl $postBody, $optParams = array())
  {
    $params = array('bucket' => $bucket, 'object' => $object, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Storage_ObjectAccessControl");
  }

  
  public function listObjectAccessControls($bucket, $object, $optParams = array())
  {
    $params = array('bucket' => $bucket, 'object' => $object);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Storage_ObjectAccessControls");
  }

  
  public function patch($bucket, $object, $entity, Google_Service_Storage_ObjectAccessControl $postBody, $optParams = array())
  {
    $params = array('bucket' => $bucket, 'object' => $object, 'entity' => $entity, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Storage_ObjectAccessControl");
  }

  
  public function update($bucket, $object, $entity, Google_Service_Storage_ObjectAccessControl $postBody, $optParams = array())
  {
    $params = array('bucket' => $bucket, 'object' => $object, 'entity' => $entity, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Storage_ObjectAccessControl");
  }
}


class Google_Service_Storage_Objects_Resource extends Google_Service_Resource
{

  
  public function compose($destinationBucket, $destinationObject, Google_Service_Storage_ComposeRequest $postBody, $optParams = array())
  {
    $params = array('destinationBucket' => $destinationBucket, 'destinationObject' => $destinationObject, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('compose', array($params), "Google_Service_Storage_StorageObject");
  }

  
  public function copy($sourceBucket, $sourceObject, $destinationBucket, $destinationObject, Google_Service_Storage_StorageObject $postBody, $optParams = array())
  {
    $params = array('sourceBucket' => $sourceBucket, 'sourceObject' => $sourceObject, 'destinationBucket' => $destinationBucket, 'destinationObject' => $destinationObject, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('copy', array($params), "Google_Service_Storage_StorageObject");
  }

  
  public function delete($bucket, $object, $optParams = array())
  {
    $params = array('bucket' => $bucket, 'object' => $object);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($bucket, $object, $optParams = array())
  {
    $params = array('bucket' => $bucket, 'object' => $object);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Storage_StorageObject");
  }

  
  public function insert($bucket, Google_Service_Storage_StorageObject $postBody, $optParams = array())
  {
    $params = array('bucket' => $bucket, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Storage_StorageObject");
  }

  
  public function listObjects($bucket, $optParams = array())
  {
    $params = array('bucket' => $bucket);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Storage_Objects");
  }

  
  public function patch($bucket, $object, Google_Service_Storage_StorageObject $postBody, $optParams = array())
  {
    $params = array('bucket' => $bucket, 'object' => $object, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Storage_StorageObject");
  }

  
  public function rewrite($sourceBucket, $sourceObject, $destinationBucket, $destinationObject, Google_Service_Storage_StorageObject $postBody, $optParams = array())
  {
    $params = array('sourceBucket' => $sourceBucket, 'sourceObject' => $sourceObject, 'destinationBucket' => $destinationBucket, 'destinationObject' => $destinationObject, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('rewrite', array($params), "Google_Service_Storage_RewriteResponse");
  }

  
  public function update($bucket, $object, Google_Service_Storage_StorageObject $postBody, $optParams = array())
  {
    $params = array('bucket' => $bucket, 'object' => $object, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Storage_StorageObject");
  }

  
  public function watchAll($bucket, Google_Service_Storage_Channel $postBody, $optParams = array())
  {
    $params = array('bucket' => $bucket, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('watchAll', array($params), "Google_Service_Storage_Channel");
  }
}




class Google_Service_Storage_Bucket extends Google_Collection
{
  protected $collection_key = 'defaultObjectAcl';
  protected $internal_gapi_mappings = array(
  );
  protected $aclType = 'Google_Service_Storage_BucketAccessControl';
  protected $aclDataType = 'array';
  protected $corsType = 'Google_Service_Storage_BucketCors';
  protected $corsDataType = 'array';
  protected $defaultObjectAclType = 'Google_Service_Storage_ObjectAccessControl';
  protected $defaultObjectAclDataType = 'array';
  public $etag;
  public $id;
  public $kind;
  protected $lifecycleType = 'Google_Service_Storage_BucketLifecycle';
  protected $lifecycleDataType = '';
  public $location;
  protected $loggingType = 'Google_Service_Storage_BucketLogging';
  protected $loggingDataType = '';
  public $metageneration;
  public $name;
  protected $ownerType = 'Google_Service_Storage_BucketOwner';
  protected $ownerDataType = '';
  public $projectNumber;
  public $selfLink;
  public $storageClass;
  public $timeCreated;
  public $updated;
  protected $versioningType = 'Google_Service_Storage_BucketVersioning';
  protected $versioningDataType = '';
  protected $websiteType = 'Google_Service_Storage_BucketWebsite';
  protected $websiteDataType = '';


  public function setAcl($acl)
  {
    $this->acl = $acl;
  }
  public function getAcl()
  {
    return $this->acl;
  }
  public function setCors($cors)
  {
    $this->cors = $cors;
  }
  public function getCors()
  {
    return $this->cors;
  }
  public function setDefaultObjectAcl($defaultObjectAcl)
  {
    $this->defaultObjectAcl = $defaultObjectAcl;
  }
  public function getDefaultObjectAcl()
  {
    return $this->defaultObjectAcl;
  }
  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setLifecycle(Google_Service_Storage_BucketLifecycle $lifecycle)
  {
    $this->lifecycle = $lifecycle;
  }
  public function getLifecycle()
  {
    return $this->lifecycle;
  }
  public function setLocation($location)
  {
    $this->location = $location;
  }
  public function getLocation()
  {
    return $this->location;
  }
  public function setLogging(Google_Service_Storage_BucketLogging $logging)
  {
    $this->logging = $logging;
  }
  public function getLogging()
  {
    return $this->logging;
  }
  public function setMetageneration($metageneration)
  {
    $this->metageneration = $metageneration;
  }
  public function getMetageneration()
  {
    return $this->metageneration;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setOwner(Google_Service_Storage_BucketOwner $owner)
  {
    $this->owner = $owner;
  }
  public function getOwner()
  {
    return $this->owner;
  }
  public function setProjectNumber($projectNumber)
  {
    $this->projectNumber = $projectNumber;
  }
  public function getProjectNumber()
  {
    return $this->projectNumber;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setStorageClass($storageClass)
  {
    $this->storageClass = $storageClass;
  }
  public function getStorageClass()
  {
    return $this->storageClass;
  }
  public function setTimeCreated($timeCreated)
  {
    $this->timeCreated = $timeCreated;
  }
  public function getTimeCreated()
  {
    return $this->timeCreated;
  }
  public function setUpdated($updated)
  {
    $this->updated = $updated;
  }
  public function getUpdated()
  {
    return $this->updated;
  }
  public function setVersioning(Google_Service_Storage_BucketVersioning $versioning)
  {
    $this->versioning = $versioning;
  }
  public function getVersioning()
  {
    return $this->versioning;
  }
  public function setWebsite(Google_Service_Storage_BucketWebsite $website)
  {
    $this->website = $website;
  }
  public function getWebsite()
  {
    return $this->website;
  }
}

class Google_Service_Storage_BucketAccessControl extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $bucket;
  public $domain;
  public $email;
  public $entity;
  public $entityId;
  public $etag;
  public $id;
  public $kind;
  protected $projectTeamType = 'Google_Service_Storage_BucketAccessControlProjectTeam';
  protected $projectTeamDataType = '';
  public $role;
  public $selfLink;


  public function setBucket($bucket)
  {
    $this->bucket = $bucket;
  }
  public function getBucket()
  {
    return $this->bucket;
  }
  public function setDomain($domain)
  {
    $this->domain = $domain;
  }
  public function getDomain()
  {
    return $this->domain;
  }
  public function setEmail($email)
  {
    $this->email = $email;
  }
  public function getEmail()
  {
    return $this->email;
  }
  public function setEntity($entity)
  {
    $this->entity = $entity;
  }
  public function getEntity()
  {
    return $this->entity;
  }
  public function setEntityId($entityId)
  {
    $this->entityId = $entityId;
  }
  public function getEntityId()
  {
    return $this->entityId;
  }
  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setProjectTeam(Google_Service_Storage_BucketAccessControlProjectTeam $projectTeam)
  {
    $this->projectTeam = $projectTeam;
  }
  public function getProjectTeam()
  {
    return $this->projectTeam;
  }
  public function setRole($role)
  {
    $this->role = $role;
  }
  public function getRole()
  {
    return $this->role;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
}

class Google_Service_Storage_BucketAccessControlProjectTeam extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $projectNumber;
  public $team;


  public function setProjectNumber($projectNumber)
  {
    $this->projectNumber = $projectNumber;
  }
  public function getProjectNumber()
  {
    return $this->projectNumber;
  }
  public function setTeam($team)
  {
    $this->team = $team;
  }
  public function getTeam()
  {
    return $this->team;
  }
}

class Google_Service_Storage_BucketAccessControls extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Storage_BucketAccessControl';
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

class Google_Service_Storage_BucketCors extends Google_Collection
{
  protected $collection_key = 'responseHeader';
  protected $internal_gapi_mappings = array(
  );
  public $maxAgeSeconds;
  public $method;
  public $origin;
  public $responseHeader;


  public function setMaxAgeSeconds($maxAgeSeconds)
  {
    $this->maxAgeSeconds = $maxAgeSeconds;
  }
  public function getMaxAgeSeconds()
  {
    return $this->maxAgeSeconds;
  }
  public function setMethod($method)
  {
    $this->method = $method;
  }
  public function getMethod()
  {
    return $this->method;
  }
  public function setOrigin($origin)
  {
    $this->origin = $origin;
  }
  public function getOrigin()
  {
    return $this->origin;
  }
  public function setResponseHeader($responseHeader)
  {
    $this->responseHeader = $responseHeader;
  }
  public function getResponseHeader()
  {
    return $this->responseHeader;
  }
}

class Google_Service_Storage_BucketLifecycle extends Google_Collection
{
  protected $collection_key = 'rule';
  protected $internal_gapi_mappings = array(
  );
  protected $ruleType = 'Google_Service_Storage_BucketLifecycleRule';
  protected $ruleDataType = 'array';


  public function setRule($rule)
  {
    $this->rule = $rule;
  }
  public function getRule()
  {
    return $this->rule;
  }
}

class Google_Service_Storage_BucketLifecycleRule extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $actionType = 'Google_Service_Storage_BucketLifecycleRuleAction';
  protected $actionDataType = '';
  protected $conditionType = 'Google_Service_Storage_BucketLifecycleRuleCondition';
  protected $conditionDataType = '';


  public function setAction(Google_Service_Storage_BucketLifecycleRuleAction $action)
  {
    $this->action = $action;
  }
  public function getAction()
  {
    return $this->action;
  }
  public function setCondition(Google_Service_Storage_BucketLifecycleRuleCondition $condition)
  {
    $this->condition = $condition;
  }
  public function getCondition()
  {
    return $this->condition;
  }
}

class Google_Service_Storage_BucketLifecycleRuleAction extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $type;


  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
}

class Google_Service_Storage_BucketLifecycleRuleCondition extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $age;
  public $createdBefore;
  public $isLive;
  public $numNewerVersions;


  public function setAge($age)
  {
    $this->age = $age;
  }
  public function getAge()
  {
    return $this->age;
  }
  public function setCreatedBefore($createdBefore)
  {
    $this->createdBefore = $createdBefore;
  }
  public function getCreatedBefore()
  {
    return $this->createdBefore;
  }
  public function setIsLive($isLive)
  {
    $this->isLive = $isLive;
  }
  public function getIsLive()
  {
    return $this->isLive;
  }
  public function setNumNewerVersions($numNewerVersions)
  {
    $this->numNewerVersions = $numNewerVersions;
  }
  public function getNumNewerVersions()
  {
    return $this->numNewerVersions;
  }
}

class Google_Service_Storage_BucketLogging extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $logBucket;
  public $logObjectPrefix;


  public function setLogBucket($logBucket)
  {
    $this->logBucket = $logBucket;
  }
  public function getLogBucket()
  {
    return $this->logBucket;
  }
  public function setLogObjectPrefix($logObjectPrefix)
  {
    $this->logObjectPrefix = $logObjectPrefix;
  }
  public function getLogObjectPrefix()
  {
    return $this->logObjectPrefix;
  }
}

class Google_Service_Storage_BucketOwner extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $entity;
  public $entityId;


  public function setEntity($entity)
  {
    $this->entity = $entity;
  }
  public function getEntity()
  {
    return $this->entity;
  }
  public function setEntityId($entityId)
  {
    $this->entityId = $entityId;
  }
  public function getEntityId()
  {
    return $this->entityId;
  }
}

class Google_Service_Storage_BucketVersioning extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $enabled;


  public function setEnabled($enabled)
  {
    $this->enabled = $enabled;
  }
  public function getEnabled()
  {
    return $this->enabled;
  }
}

class Google_Service_Storage_BucketWebsite extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $mainPageSuffix;
  public $notFoundPage;


  public function setMainPageSuffix($mainPageSuffix)
  {
    $this->mainPageSuffix = $mainPageSuffix;
  }
  public function getMainPageSuffix()
  {
    return $this->mainPageSuffix;
  }
  public function setNotFoundPage($notFoundPage)
  {
    $this->notFoundPage = $notFoundPage;
  }
  public function getNotFoundPage()
  {
    return $this->notFoundPage;
  }
}

class Google_Service_Storage_Buckets extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Storage_Bucket';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;


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

class Google_Service_Storage_Channel extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $address;
  public $expiration;
  public $id;
  public $kind;
  public $params;
  public $payload;
  public $resourceId;
  public $resourceUri;
  public $token;
  public $type;


  public function setAddress($address)
  {
    $this->address = $address;
  }
  public function getAddress()
  {
    return $this->address;
  }
  public function setExpiration($expiration)
  {
    $this->expiration = $expiration;
  }
  public function getExpiration()
  {
    return $this->expiration;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setParams($params)
  {
    $this->params = $params;
  }
  public function getParams()
  {
    return $this->params;
  }
  public function setPayload($payload)
  {
    $this->payload = $payload;
  }
  public function getPayload()
  {
    return $this->payload;
  }
  public function setResourceId($resourceId)
  {
    $this->resourceId = $resourceId;
  }
  public function getResourceId()
  {
    return $this->resourceId;
  }
  public function setResourceUri($resourceUri)
  {
    $this->resourceUri = $resourceUri;
  }
  public function getResourceUri()
  {
    return $this->resourceUri;
  }
  public function setToken($token)
  {
    $this->token = $token;
  }
  public function getToken()
  {
    return $this->token;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
}

class Google_Service_Storage_ChannelParams extends Google_Model
{
}

class Google_Service_Storage_ComposeRequest extends Google_Collection
{
  protected $collection_key = 'sourceObjects';
  protected $internal_gapi_mappings = array(
  );
  protected $destinationType = 'Google_Service_Storage_StorageObject';
  protected $destinationDataType = '';
  public $kind;
  protected $sourceObjectsType = 'Google_Service_Storage_ComposeRequestSourceObjects';
  protected $sourceObjectsDataType = 'array';


  public function setDestination(Google_Service_Storage_StorageObject $destination)
  {
    $this->destination = $destination;
  }
  public function getDestination()
  {
    return $this->destination;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setSourceObjects($sourceObjects)
  {
    $this->sourceObjects = $sourceObjects;
  }
  public function getSourceObjects()
  {
    return $this->sourceObjects;
  }
}

class Google_Service_Storage_ComposeRequestSourceObjects extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $generation;
  public $name;
  protected $objectPreconditionsType = 'Google_Service_Storage_ComposeRequestSourceObjectsObjectPreconditions';
  protected $objectPreconditionsDataType = '';


  public function setGeneration($generation)
  {
    $this->generation = $generation;
  }
  public function getGeneration()
  {
    return $this->generation;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setObjectPreconditions(Google_Service_Storage_ComposeRequestSourceObjectsObjectPreconditions $objectPreconditions)
  {
    $this->objectPreconditions = $objectPreconditions;
  }
  public function getObjectPreconditions()
  {
    return $this->objectPreconditions;
  }
}

class Google_Service_Storage_ComposeRequestSourceObjectsObjectPreconditions extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $ifGenerationMatch;


  public function setIfGenerationMatch($ifGenerationMatch)
  {
    $this->ifGenerationMatch = $ifGenerationMatch;
  }
  public function getIfGenerationMatch()
  {
    return $this->ifGenerationMatch;
  }
}

class Google_Service_Storage_ObjectAccessControl extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $bucket;
  public $domain;
  public $email;
  public $entity;
  public $entityId;
  public $etag;
  public $generation;
  public $id;
  public $kind;
  public $object;
  protected $projectTeamType = 'Google_Service_Storage_ObjectAccessControlProjectTeam';
  protected $projectTeamDataType = '';
  public $role;
  public $selfLink;


  public function setBucket($bucket)
  {
    $this->bucket = $bucket;
  }
  public function getBucket()
  {
    return $this->bucket;
  }
  public function setDomain($domain)
  {
    $this->domain = $domain;
  }
  public function getDomain()
  {
    return $this->domain;
  }
  public function setEmail($email)
  {
    $this->email = $email;
  }
  public function getEmail()
  {
    return $this->email;
  }
  public function setEntity($entity)
  {
    $this->entity = $entity;
  }
  public function getEntity()
  {
    return $this->entity;
  }
  public function setEntityId($entityId)
  {
    $this->entityId = $entityId;
  }
  public function getEntityId()
  {
    return $this->entityId;
  }
  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setGeneration($generation)
  {
    $this->generation = $generation;
  }
  public function getGeneration()
  {
    return $this->generation;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setObject($object)
  {
    $this->object = $object;
  }
  public function getObject()
  {
    return $this->object;
  }
  public function setProjectTeam(Google_Service_Storage_ObjectAccessControlProjectTeam $projectTeam)
  {
    $this->projectTeam = $projectTeam;
  }
  public function getProjectTeam()
  {
    return $this->projectTeam;
  }
  public function setRole($role)
  {
    $this->role = $role;
  }
  public function getRole()
  {
    return $this->role;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
}

class Google_Service_Storage_ObjectAccessControlProjectTeam extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $projectNumber;
  public $team;


  public function setProjectNumber($projectNumber)
  {
    $this->projectNumber = $projectNumber;
  }
  public function getProjectNumber()
  {
    return $this->projectNumber;
  }
  public function setTeam($team)
  {
    $this->team = $team;
  }
  public function getTeam()
  {
    return $this->team;
  }
}

class Google_Service_Storage_ObjectAccessControls extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $items;
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

class Google_Service_Storage_Objects extends Google_Collection
{
  protected $collection_key = 'prefixes';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Storage_StorageObject';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;
  public $prefixes;


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
  public function setPrefixes($prefixes)
  {
    $this->prefixes = $prefixes;
  }
  public function getPrefixes()
  {
    return $this->prefixes;
  }
}

class Google_Service_Storage_RewriteResponse extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $done;
  public $kind;
  public $objectSize;
  protected $resourceType = 'Google_Service_Storage_StorageObject';
  protected $resourceDataType = '';
  public $rewriteToken;
  public $totalBytesRewritten;


  public function setDone($done)
  {
    $this->done = $done;
  }
  public function getDone()
  {
    return $this->done;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setObjectSize($objectSize)
  {
    $this->objectSize = $objectSize;
  }
  public function getObjectSize()
  {
    return $this->objectSize;
  }
  public function setResource(Google_Service_Storage_StorageObject $resource)
  {
    $this->resource = $resource;
  }
  public function getResource()
  {
    return $this->resource;
  }
  public function setRewriteToken($rewriteToken)
  {
    $this->rewriteToken = $rewriteToken;
  }
  public function getRewriteToken()
  {
    return $this->rewriteToken;
  }
  public function setTotalBytesRewritten($totalBytesRewritten)
  {
    $this->totalBytesRewritten = $totalBytesRewritten;
  }
  public function getTotalBytesRewritten()
  {
    return $this->totalBytesRewritten;
  }
}

class Google_Service_Storage_StorageObject extends Google_Collection
{
  protected $collection_key = 'acl';
  protected $internal_gapi_mappings = array(
  );
  protected $aclType = 'Google_Service_Storage_ObjectAccessControl';
  protected $aclDataType = 'array';
  public $bucket;
  public $cacheControl;
  public $componentCount;
  public $contentDisposition;
  public $contentEncoding;
  public $contentLanguage;
  public $contentType;
  public $crc32c;
  public $etag;
  public $generation;
  public $id;
  public $kind;
  public $md5Hash;
  public $mediaLink;
  public $metadata;
  public $metageneration;
  public $name;
  protected $ownerType = 'Google_Service_Storage_StorageObjectOwner';
  protected $ownerDataType = '';
  public $selfLink;
  public $size;
  public $storageClass;
  public $timeCreated;
  public $timeDeleted;
  public $updated;


  public function setAcl($acl)
  {
    $this->acl = $acl;
  }
  public function getAcl()
  {
    return $this->acl;
  }
  public function setBucket($bucket)
  {
    $this->bucket = $bucket;
  }
  public function getBucket()
  {
    return $this->bucket;
  }
  public function setCacheControl($cacheControl)
  {
    $this->cacheControl = $cacheControl;
  }
  public function getCacheControl()
  {
    return $this->cacheControl;
  }
  public function setComponentCount($componentCount)
  {
    $this->componentCount = $componentCount;
  }
  public function getComponentCount()
  {
    return $this->componentCount;
  }
  public function setContentDisposition($contentDisposition)
  {
    $this->contentDisposition = $contentDisposition;
  }
  public function getContentDisposition()
  {
    return $this->contentDisposition;
  }
  public function setContentEncoding($contentEncoding)
  {
    $this->contentEncoding = $contentEncoding;
  }
  public function getContentEncoding()
  {
    return $this->contentEncoding;
  }
  public function setContentLanguage($contentLanguage)
  {
    $this->contentLanguage = $contentLanguage;
  }
  public function getContentLanguage()
  {
    return $this->contentLanguage;
  }
  public function setContentType($contentType)
  {
    $this->contentType = $contentType;
  }
  public function getContentType()
  {
    return $this->contentType;
  }
  public function setCrc32c($crc32c)
  {
    $this->crc32c = $crc32c;
  }
  public function getCrc32c()
  {
    return $this->crc32c;
  }
  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setGeneration($generation)
  {
    $this->generation = $generation;
  }
  public function getGeneration()
  {
    return $this->generation;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setMd5Hash($md5Hash)
  {
    $this->md5Hash = $md5Hash;
  }
  public function getMd5Hash()
  {
    return $this->md5Hash;
  }
  public function setMediaLink($mediaLink)
  {
    $this->mediaLink = $mediaLink;
  }
  public function getMediaLink()
  {
    return $this->mediaLink;
  }
  public function setMetadata($metadata)
  {
    $this->metadata = $metadata;
  }
  public function getMetadata()
  {
    return $this->metadata;
  }
  public function setMetageneration($metageneration)
  {
    $this->metageneration = $metageneration;
  }
  public function getMetageneration()
  {
    return $this->metageneration;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setOwner(Google_Service_Storage_StorageObjectOwner $owner)
  {
    $this->owner = $owner;
  }
  public function getOwner()
  {
    return $this->owner;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setSize($size)
  {
    $this->size = $size;
  }
  public function getSize()
  {
    return $this->size;
  }
  public function setStorageClass($storageClass)
  {
    $this->storageClass = $storageClass;
  }
  public function getStorageClass()
  {
    return $this->storageClass;
  }
  public function setTimeCreated($timeCreated)
  {
    $this->timeCreated = $timeCreated;
  }
  public function getTimeCreated()
  {
    return $this->timeCreated;
  }
  public function setTimeDeleted($timeDeleted)
  {
    $this->timeDeleted = $timeDeleted;
  }
  public function getTimeDeleted()
  {
    return $this->timeDeleted;
  }
  public function setUpdated($updated)
  {
    $this->updated = $updated;
  }
  public function getUpdated()
  {
    return $this->updated;
  }
}

class Google_Service_Storage_StorageObjectMetadata extends Google_Model
{
}

class Google_Service_Storage_StorageObjectOwner extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $entity;
  public $entityId;


  public function setEntity($entity)
  {
    $this->entity = $entity;
  }
  public function getEntity()
  {
    return $this->entity;
  }
  public function setEntityId($entityId)
  {
    $this->entityId = $entityId;
  }
  public function getEntityId()
  {
    return $this->entityId;
  }
}
