<?php



class Google_Service_Blogger extends Google_Service
{
  
  const BLOGGER =
      "https://www.googleapis.com/auth/blogger";
  
  const BLOGGER_READONLY =
      "https://www.googleapis.com/auth/blogger.readonly";

  public $blogUserInfos;
  public $blogs;
  public $comments;
  public $pageViews;
  public $pages;
  public $postUserInfos;
  public $posts;
  public $users;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'blogger/v3/';
    $this->version = 'v3';
    $this->serviceName = 'blogger';

    $this->blogUserInfos = new Google_Service_Blogger_BlogUserInfos_Resource(
        $this,
        $this->serviceName,
        'blogUserInfos',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'users/{userId}/blogs/{blogId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'maxPosts' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),
          )
        )
    );
    $this->blogs = new Google_Service_Blogger_Blogs_Resource(
        $this,
        $this->serviceName,
        'blogs',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'blogs/{blogId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'maxPosts' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'view' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'getByUrl' => array(
              'path' => 'blogs/byurl',
              'httpMethod' => 'GET',
              'parameters' => array(
                'url' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'view' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'listByUser' => array(
              'path' => 'users/{userId}/blogs',
              'httpMethod' => 'GET',
              'parameters' => array(
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'fetchUserInfo' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'status' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'role' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'view' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->comments = new Google_Service_Blogger_Comments_Resource(
        $this,
        $this->serviceName,
        'comments',
        array(
          'methods' => array(
            'approve' => array(
              'path' => 'blogs/{blogId}/posts/{postId}/comments/{commentId}/approve',
              'httpMethod' => 'POST',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'postId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'commentId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'blogs/{blogId}/posts/{postId}/comments/{commentId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'postId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'commentId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'blogs/{blogId}/posts/{postId}/comments/{commentId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'postId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'commentId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'view' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => 'blogs/{blogId}/posts/{postId}/comments',
              'httpMethod' => 'GET',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'postId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'status' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'startDate' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'endDate' => array(
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
                'fetchBodies' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'view' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'listByBlog' => array(
              'path' => 'blogs/{blogId}/comments',
              'httpMethod' => 'GET',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'status' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'startDate' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'endDate' => array(
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
                'fetchBodies' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'markAsSpam' => array(
              'path' => 'blogs/{blogId}/posts/{postId}/comments/{commentId}/spam',
              'httpMethod' => 'POST',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'postId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'commentId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'removeContent' => array(
              'path' => 'blogs/{blogId}/posts/{postId}/comments/{commentId}/removecontent',
              'httpMethod' => 'POST',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'postId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'commentId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->pageViews = new Google_Service_Blogger_PageViews_Resource(
        $this,
        $this->serviceName,
        'pageViews',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'blogs/{blogId}/pageviews',
              'httpMethod' => 'GET',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'range' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->pages = new Google_Service_Blogger_Pages_Resource(
        $this,
        $this->serviceName,
        'pages',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'blogs/{blogId}/pages/{pageId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'blogs/{blogId}/pages/{pageId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'view' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'insert' => array(
              'path' => 'blogs/{blogId}/pages',
              'httpMethod' => 'POST',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'isDraft' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'list' => array(
              'path' => 'blogs/{blogId}/pages',
              'httpMethod' => 'GET',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'status' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'fetchBodies' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'view' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'patch' => array(
              'path' => 'blogs/{blogId}/pages/{pageId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'revert' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'publish' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'publish' => array(
              'path' => 'blogs/{blogId}/pages/{pageId}/publish',
              'httpMethod' => 'POST',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'revert' => array(
              'path' => 'blogs/{blogId}/pages/{pageId}/revert',
              'httpMethod' => 'POST',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'blogs/{blogId}/pages/{pageId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'revert' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'publish' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),
          )
        )
    );
    $this->postUserInfos = new Google_Service_Blogger_PostUserInfos_Resource(
        $this,
        $this->serviceName,
        'postUserInfos',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'users/{userId}/blogs/{blogId}/posts/{postId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'postId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'maxComments' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),'list' => array(
              'path' => 'users/{userId}/blogs/{blogId}/posts',
              'httpMethod' => 'GET',
              'parameters' => array(
                'userId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'orderBy' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'startDate' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'endDate' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'labels' => array(
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
                'status' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'fetchBodies' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'view' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->posts = new Google_Service_Blogger_Posts_Resource(
        $this,
        $this->serviceName,
        'posts',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'blogs/{blogId}/posts/{postId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'postId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'blogs/{blogId}/posts/{postId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'postId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'fetchBody' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'maxComments' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'fetchImages' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'view' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'getByPath' => array(
              'path' => 'blogs/{blogId}/posts/bypath',
              'httpMethod' => 'GET',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'path' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'maxComments' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'view' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'insert' => array(
              'path' => 'blogs/{blogId}/posts',
              'httpMethod' => 'POST',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'fetchImages' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'isDraft' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'fetchBody' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'list' => array(
              'path' => 'blogs/{blogId}/posts',
              'httpMethod' => 'GET',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'orderBy' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'startDate' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'endDate' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'labels' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'fetchImages' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'status' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'repeated' => true,
                ),
                'fetchBodies' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'view' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'patch' => array(
              'path' => 'blogs/{blogId}/posts/{postId}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'postId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'revert' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'publish' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'fetchBody' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'maxComments' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'fetchImages' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'publish' => array(
              'path' => 'blogs/{blogId}/posts/{postId}/publish',
              'httpMethod' => 'POST',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'postId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'publishDate' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'revert' => array(
              'path' => 'blogs/{blogId}/posts/{postId}/revert',
              'httpMethod' => 'POST',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'postId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'search' => array(
              'path' => 'blogs/{blogId}/posts/search',
              'httpMethod' => 'GET',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'q' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'orderBy' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'fetchBodies' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'update' => array(
              'path' => 'blogs/{blogId}/posts/{postId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'blogId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'postId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'revert' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'publish' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'fetchBody' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'maxComments' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'fetchImages' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),
          )
        )
    );
    $this->users = new Google_Service_Blogger_Users_Resource(
        $this,
        $this->serviceName,
        'users',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'users/{userId}',
              'httpMethod' => 'GET',
              'parameters' => array(
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



class Google_Service_Blogger_BlogUserInfos_Resource extends Google_Service_Resource
{

  
  public function get($userId, $blogId, $optParams = array())
  {
    $params = array('userId' => $userId, 'blogId' => $blogId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Blogger_BlogUserInfo");
  }
}


class Google_Service_Blogger_Blogs_Resource extends Google_Service_Resource
{

  
  public function get($blogId, $optParams = array())
  {
    $params = array('blogId' => $blogId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Blogger_Blog");
  }

  
  public function getByUrl($url, $optParams = array())
  {
    $params = array('url' => $url);
    $params = array_merge($params, $optParams);
    return $this->call('getByUrl', array($params), "Google_Service_Blogger_Blog");
  }

  
  public function listByUser($userId, $optParams = array())
  {
    $params = array('userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('listByUser', array($params), "Google_Service_Blogger_BlogList");
  }
}


class Google_Service_Blogger_Comments_Resource extends Google_Service_Resource
{

  
  public function approve($blogId, $postId, $commentId, $optParams = array())
  {
    $params = array('blogId' => $blogId, 'postId' => $postId, 'commentId' => $commentId);
    $params = array_merge($params, $optParams);
    return $this->call('approve', array($params), "Google_Service_Blogger_Comment");
  }

  
  public function delete($blogId, $postId, $commentId, $optParams = array())
  {
    $params = array('blogId' => $blogId, 'postId' => $postId, 'commentId' => $commentId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($blogId, $postId, $commentId, $optParams = array())
  {
    $params = array('blogId' => $blogId, 'postId' => $postId, 'commentId' => $commentId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Blogger_Comment");
  }

  
  public function listComments($blogId, $postId, $optParams = array())
  {
    $params = array('blogId' => $blogId, 'postId' => $postId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Blogger_CommentList");
  }

  
  public function listByBlog($blogId, $optParams = array())
  {
    $params = array('blogId' => $blogId);
    $params = array_merge($params, $optParams);
    return $this->call('listByBlog', array($params), "Google_Service_Blogger_CommentList");
  }

  
  public function markAsSpam($blogId, $postId, $commentId, $optParams = array())
  {
    $params = array('blogId' => $blogId, 'postId' => $postId, 'commentId' => $commentId);
    $params = array_merge($params, $optParams);
    return $this->call('markAsSpam', array($params), "Google_Service_Blogger_Comment");
  }

  
  public function removeContent($blogId, $postId, $commentId, $optParams = array())
  {
    $params = array('blogId' => $blogId, 'postId' => $postId, 'commentId' => $commentId);
    $params = array_merge($params, $optParams);
    return $this->call('removeContent', array($params), "Google_Service_Blogger_Comment");
  }
}


class Google_Service_Blogger_PageViews_Resource extends Google_Service_Resource
{

  
  public function get($blogId, $optParams = array())
  {
    $params = array('blogId' => $blogId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Blogger_Pageviews");
  }
}


class Google_Service_Blogger_Pages_Resource extends Google_Service_Resource
{

  
  public function delete($blogId, $pageId, $optParams = array())
  {
    $params = array('blogId' => $blogId, 'pageId' => $pageId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($blogId, $pageId, $optParams = array())
  {
    $params = array('blogId' => $blogId, 'pageId' => $pageId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Blogger_Page");
  }

  
  public function insert($blogId, Google_Service_Blogger_Page $postBody, $optParams = array())
  {
    $params = array('blogId' => $blogId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Blogger_Page");
  }

  
  public function listPages($blogId, $optParams = array())
  {
    $params = array('blogId' => $blogId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Blogger_PageList");
  }

  
  public function patch($blogId, $pageId, Google_Service_Blogger_Page $postBody, $optParams = array())
  {
    $params = array('blogId' => $blogId, 'pageId' => $pageId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Blogger_Page");
  }

  
  public function publish($blogId, $pageId, $optParams = array())
  {
    $params = array('blogId' => $blogId, 'pageId' => $pageId);
    $params = array_merge($params, $optParams);
    return $this->call('publish', array($params), "Google_Service_Blogger_Page");
  }

  
  public function revert($blogId, $pageId, $optParams = array())
  {
    $params = array('blogId' => $blogId, 'pageId' => $pageId);
    $params = array_merge($params, $optParams);
    return $this->call('revert', array($params), "Google_Service_Blogger_Page");
  }

  
  public function update($blogId, $pageId, Google_Service_Blogger_Page $postBody, $optParams = array())
  {
    $params = array('blogId' => $blogId, 'pageId' => $pageId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Blogger_Page");
  }
}


class Google_Service_Blogger_PostUserInfos_Resource extends Google_Service_Resource
{

  
  public function get($userId, $blogId, $postId, $optParams = array())
  {
    $params = array('userId' => $userId, 'blogId' => $blogId, 'postId' => $postId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Blogger_PostUserInfo");
  }

  
  public function listPostUserInfos($userId, $blogId, $optParams = array())
  {
    $params = array('userId' => $userId, 'blogId' => $blogId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Blogger_PostUserInfosList");
  }
}


class Google_Service_Blogger_Posts_Resource extends Google_Service_Resource
{

  
  public function delete($blogId, $postId, $optParams = array())
  {
    $params = array('blogId' => $blogId, 'postId' => $postId);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function get($blogId, $postId, $optParams = array())
  {
    $params = array('blogId' => $blogId, 'postId' => $postId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Blogger_Post");
  }

  
  public function getByPath($blogId, $path, $optParams = array())
  {
    $params = array('blogId' => $blogId, 'path' => $path);
    $params = array_merge($params, $optParams);
    return $this->call('getByPath', array($params), "Google_Service_Blogger_Post");
  }

  
  public function insert($blogId, Google_Service_Blogger_Post $postBody, $optParams = array())
  {
    $params = array('blogId' => $blogId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_Blogger_Post");
  }

  
  public function listPosts($blogId, $optParams = array())
  {
    $params = array('blogId' => $blogId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_Blogger_PostList");
  }

  
  public function patch($blogId, $postId, Google_Service_Blogger_Post $postBody, $optParams = array())
  {
    $params = array('blogId' => $blogId, 'postId' => $postId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('patch', array($params), "Google_Service_Blogger_Post");
  }

  
  public function publish($blogId, $postId, $optParams = array())
  {
    $params = array('blogId' => $blogId, 'postId' => $postId);
    $params = array_merge($params, $optParams);
    return $this->call('publish', array($params), "Google_Service_Blogger_Post");
  }

  
  public function revert($blogId, $postId, $optParams = array())
  {
    $params = array('blogId' => $blogId, 'postId' => $postId);
    $params = array_merge($params, $optParams);
    return $this->call('revert', array($params), "Google_Service_Blogger_Post");
  }

  
  public function search($blogId, $q, $optParams = array())
  {
    $params = array('blogId' => $blogId, 'q' => $q);
    $params = array_merge($params, $optParams);
    return $this->call('search', array($params), "Google_Service_Blogger_PostList");
  }

  
  public function update($blogId, $postId, Google_Service_Blogger_Post $postBody, $optParams = array())
  {
    $params = array('blogId' => $blogId, 'postId' => $postId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_Blogger_Post");
  }
}


class Google_Service_Blogger_Users_Resource extends Google_Service_Resource
{

  
  public function get($userId, $optParams = array())
  {
    $params = array('userId' => $userId);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_Blogger_User");
  }
}




class Google_Service_Blogger_Blog extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $customMetaData;
  public $description;
  public $id;
  public $kind;
  protected $localeType = 'Google_Service_Blogger_BlogLocale';
  protected $localeDataType = '';
  public $name;
  protected $pagesType = 'Google_Service_Blogger_BlogPages';
  protected $pagesDataType = '';
  protected $postsType = 'Google_Service_Blogger_BlogPosts';
  protected $postsDataType = '';
  public $published;
  public $selfLink;
  public $status;
  public $updated;
  public $url;


  public function setCustomMetaData($customMetaData)
  {
    $this->customMetaData = $customMetaData;
  }
  public function getCustomMetaData()
  {
    return $this->customMetaData;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
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
  public function setLocale(Google_Service_Blogger_BlogLocale $locale)
  {
    $this->locale = $locale;
  }
  public function getLocale()
  {
    return $this->locale;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setPages(Google_Service_Blogger_BlogPages $pages)
  {
    $this->pages = $pages;
  }
  public function getPages()
  {
    return $this->pages;
  }
  public function setPosts(Google_Service_Blogger_BlogPosts $posts)
  {
    $this->posts = $posts;
  }
  public function getPosts()
  {
    return $this->posts;
  }
  public function setPublished($published)
  {
    $this->published = $published;
  }
  public function getPublished()
  {
    return $this->published;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setStatus($status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
  public function setUpdated($updated)
  {
    $this->updated = $updated;
  }
  public function getUpdated()
  {
    return $this->updated;
  }
  public function setUrl($url)
  {
    $this->url = $url;
  }
  public function getUrl()
  {
    return $this->url;
  }
}

class Google_Service_Blogger_BlogList extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $blogUserInfosType = 'Google_Service_Blogger_BlogUserInfo';
  protected $blogUserInfosDataType = 'array';
  protected $itemsType = 'Google_Service_Blogger_Blog';
  protected $itemsDataType = 'array';
  public $kind;


  public function setBlogUserInfos($blogUserInfos)
  {
    $this->blogUserInfos = $blogUserInfos;
  }
  public function getBlogUserInfos()
  {
    return $this->blogUserInfos;
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
}

class Google_Service_Blogger_BlogLocale extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $country;
  public $language;
  public $variant;


  public function setCountry($country)
  {
    $this->country = $country;
  }
  public function getCountry()
  {
    return $this->country;
  }
  public function setLanguage($language)
  {
    $this->language = $language;
  }
  public function getLanguage()
  {
    return $this->language;
  }
  public function setVariant($variant)
  {
    $this->variant = $variant;
  }
  public function getVariant()
  {
    return $this->variant;
  }
}

class Google_Service_Blogger_BlogPages extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $selfLink;
  public $totalItems;


  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setTotalItems($totalItems)
  {
    $this->totalItems = $totalItems;
  }
  public function getTotalItems()
  {
    return $this->totalItems;
  }
}

class Google_Service_Blogger_BlogPerUserInfo extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $blogId;
  public $hasAdminAccess;
  public $kind;
  public $photosAlbumKey;
  public $role;
  public $userId;


  public function setBlogId($blogId)
  {
    $this->blogId = $blogId;
  }
  public function getBlogId()
  {
    return $this->blogId;
  }
  public function setHasAdminAccess($hasAdminAccess)
  {
    $this->hasAdminAccess = $hasAdminAccess;
  }
  public function getHasAdminAccess()
  {
    return $this->hasAdminAccess;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setPhotosAlbumKey($photosAlbumKey)
  {
    $this->photosAlbumKey = $photosAlbumKey;
  }
  public function getPhotosAlbumKey()
  {
    return $this->photosAlbumKey;
  }
  public function setRole($role)
  {
    $this->role = $role;
  }
  public function getRole()
  {
    return $this->role;
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

class Google_Service_Blogger_BlogPosts extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Blogger_Post';
  protected $itemsDataType = 'array';
  public $selfLink;
  public $totalItems;


  public function setItems($items)
  {
    $this->items = $items;
  }
  public function getItems()
  {
    return $this->items;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setTotalItems($totalItems)
  {
    $this->totalItems = $totalItems;
  }
  public function getTotalItems()
  {
    return $this->totalItems;
  }
}

class Google_Service_Blogger_BlogUserInfo extends Google_Model
{
  protected $internal_gapi_mappings = array(
        "blogUserInfo" => "blog_user_info",
  );
  protected $blogType = 'Google_Service_Blogger_Blog';
  protected $blogDataType = '';
  protected $blogUserInfoType = 'Google_Service_Blogger_BlogPerUserInfo';
  protected $blogUserInfoDataType = '';
  public $kind;


  public function setBlog(Google_Service_Blogger_Blog $blog)
  {
    $this->blog = $blog;
  }
  public function getBlog()
  {
    return $this->blog;
  }
  public function setBlogUserInfo(Google_Service_Blogger_BlogPerUserInfo $blogUserInfo)
  {
    $this->blogUserInfo = $blogUserInfo;
  }
  public function getBlogUserInfo()
  {
    return $this->blogUserInfo;
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

class Google_Service_Blogger_Comment extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $authorType = 'Google_Service_Blogger_CommentAuthor';
  protected $authorDataType = '';
  protected $blogType = 'Google_Service_Blogger_CommentBlog';
  protected $blogDataType = '';
  public $content;
  public $id;
  protected $inReplyToType = 'Google_Service_Blogger_CommentInReplyTo';
  protected $inReplyToDataType = '';
  public $kind;
  protected $postType = 'Google_Service_Blogger_CommentPost';
  protected $postDataType = '';
  public $published;
  public $selfLink;
  public $status;
  public $updated;


  public function setAuthor(Google_Service_Blogger_CommentAuthor $author)
  {
    $this->author = $author;
  }
  public function getAuthor()
  {
    return $this->author;
  }
  public function setBlog(Google_Service_Blogger_CommentBlog $blog)
  {
    $this->blog = $blog;
  }
  public function getBlog()
  {
    return $this->blog;
  }
  public function setContent($content)
  {
    $this->content = $content;
  }
  public function getContent()
  {
    return $this->content;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setInReplyTo(Google_Service_Blogger_CommentInReplyTo $inReplyTo)
  {
    $this->inReplyTo = $inReplyTo;
  }
  public function getInReplyTo()
  {
    return $this->inReplyTo;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setPost(Google_Service_Blogger_CommentPost $post)
  {
    $this->post = $post;
  }
  public function getPost()
  {
    return $this->post;
  }
  public function setPublished($published)
  {
    $this->published = $published;
  }
  public function getPublished()
  {
    return $this->published;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setStatus($status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
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

class Google_Service_Blogger_CommentAuthor extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $displayName;
  public $id;
  protected $imageType = 'Google_Service_Blogger_CommentAuthorImage';
  protected $imageDataType = '';
  public $url;


  public function setDisplayName($displayName)
  {
    $this->displayName = $displayName;
  }
  public function getDisplayName()
  {
    return $this->displayName;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setImage(Google_Service_Blogger_CommentAuthorImage $image)
  {
    $this->image = $image;
  }
  public function getImage()
  {
    return $this->image;
  }
  public function setUrl($url)
  {
    $this->url = $url;
  }
  public function getUrl()
  {
    return $this->url;
  }
}

class Google_Service_Blogger_CommentAuthorImage extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $url;


  public function setUrl($url)
  {
    $this->url = $url;
  }
  public function getUrl()
  {
    return $this->url;
  }
}

class Google_Service_Blogger_CommentBlog extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;


  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
}

class Google_Service_Blogger_CommentInReplyTo extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;


  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
}

class Google_Service_Blogger_CommentList extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  protected $itemsType = 'Google_Service_Blogger_Comment';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;
  public $prevPageToken;


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
  public function setPrevPageToken($prevPageToken)
  {
    $this->prevPageToken = $prevPageToken;
  }
  public function getPrevPageToken()
  {
    return $this->prevPageToken;
  }
}

class Google_Service_Blogger_CommentPost extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;


  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
}

class Google_Service_Blogger_Page extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $authorType = 'Google_Service_Blogger_PageAuthor';
  protected $authorDataType = '';
  protected $blogType = 'Google_Service_Blogger_PageBlog';
  protected $blogDataType = '';
  public $content;
  public $etag;
  public $id;
  public $kind;
  public $published;
  public $selfLink;
  public $status;
  public $title;
  public $updated;
  public $url;


  public function setAuthor(Google_Service_Blogger_PageAuthor $author)
  {
    $this->author = $author;
  }
  public function getAuthor()
  {
    return $this->author;
  }
  public function setBlog(Google_Service_Blogger_PageBlog $blog)
  {
    $this->blog = $blog;
  }
  public function getBlog()
  {
    return $this->blog;
  }
  public function setContent($content)
  {
    $this->content = $content;
  }
  public function getContent()
  {
    return $this->content;
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
  public function setPublished($published)
  {
    $this->published = $published;
  }
  public function getPublished()
  {
    return $this->published;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setStatus($status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
  public function setUpdated($updated)
  {
    $this->updated = $updated;
  }
  public function getUpdated()
  {
    return $this->updated;
  }
  public function setUrl($url)
  {
    $this->url = $url;
  }
  public function getUrl()
  {
    return $this->url;
  }
}

class Google_Service_Blogger_PageAuthor extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $displayName;
  public $id;
  protected $imageType = 'Google_Service_Blogger_PageAuthorImage';
  protected $imageDataType = '';
  public $url;


  public function setDisplayName($displayName)
  {
    $this->displayName = $displayName;
  }
  public function getDisplayName()
  {
    return $this->displayName;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setImage(Google_Service_Blogger_PageAuthorImage $image)
  {
    $this->image = $image;
  }
  public function getImage()
  {
    return $this->image;
  }
  public function setUrl($url)
  {
    $this->url = $url;
  }
  public function getUrl()
  {
    return $this->url;
  }
}

class Google_Service_Blogger_PageAuthorImage extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $url;


  public function setUrl($url)
  {
    $this->url = $url;
  }
  public function getUrl()
  {
    return $this->url;
  }
}

class Google_Service_Blogger_PageBlog extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;


  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
}

class Google_Service_Blogger_PageList extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  protected $itemsType = 'Google_Service_Blogger_Page';
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

class Google_Service_Blogger_Pageviews extends Google_Collection
{
  protected $collection_key = 'counts';
  protected $internal_gapi_mappings = array(
  );
  public $blogId;
  protected $countsType = 'Google_Service_Blogger_PageviewsCounts';
  protected $countsDataType = 'array';
  public $kind;


  public function setBlogId($blogId)
  {
    $this->blogId = $blogId;
  }
  public function getBlogId()
  {
    return $this->blogId;
  }
  public function setCounts($counts)
  {
    $this->counts = $counts;
  }
  public function getCounts()
  {
    return $this->counts;
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

class Google_Service_Blogger_PageviewsCounts extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $count;
  public $timeRange;


  public function setCount($count)
  {
    $this->count = $count;
  }
  public function getCount()
  {
    return $this->count;
  }
  public function setTimeRange($timeRange)
  {
    $this->timeRange = $timeRange;
  }
  public function getTimeRange()
  {
    return $this->timeRange;
  }
}

class Google_Service_Blogger_Post extends Google_Collection
{
  protected $collection_key = 'labels';
  protected $internal_gapi_mappings = array(
  );
  protected $authorType = 'Google_Service_Blogger_PostAuthor';
  protected $authorDataType = '';
  protected $blogType = 'Google_Service_Blogger_PostBlog';
  protected $blogDataType = '';
  public $content;
  public $customMetaData;
  public $etag;
  public $id;
  protected $imagesType = 'Google_Service_Blogger_PostImages';
  protected $imagesDataType = 'array';
  public $kind;
  public $labels;
  protected $locationType = 'Google_Service_Blogger_PostLocation';
  protected $locationDataType = '';
  public $published;
  public $readerComments;
  protected $repliesType = 'Google_Service_Blogger_PostReplies';
  protected $repliesDataType = '';
  public $selfLink;
  public $status;
  public $title;
  public $titleLink;
  public $updated;
  public $url;


  public function setAuthor(Google_Service_Blogger_PostAuthor $author)
  {
    $this->author = $author;
  }
  public function getAuthor()
  {
    return $this->author;
  }
  public function setBlog(Google_Service_Blogger_PostBlog $blog)
  {
    $this->blog = $blog;
  }
  public function getBlog()
  {
    return $this->blog;
  }
  public function setContent($content)
  {
    $this->content = $content;
  }
  public function getContent()
  {
    return $this->content;
  }
  public function setCustomMetaData($customMetaData)
  {
    $this->customMetaData = $customMetaData;
  }
  public function getCustomMetaData()
  {
    return $this->customMetaData;
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
  public function setImages($images)
  {
    $this->images = $images;
  }
  public function getImages()
  {
    return $this->images;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setLabels($labels)
  {
    $this->labels = $labels;
  }
  public function getLabels()
  {
    return $this->labels;
  }
  public function setLocation(Google_Service_Blogger_PostLocation $location)
  {
    $this->location = $location;
  }
  public function getLocation()
  {
    return $this->location;
  }
  public function setPublished($published)
  {
    $this->published = $published;
  }
  public function getPublished()
  {
    return $this->published;
  }
  public function setReaderComments($readerComments)
  {
    $this->readerComments = $readerComments;
  }
  public function getReaderComments()
  {
    return $this->readerComments;
  }
  public function setReplies(Google_Service_Blogger_PostReplies $replies)
  {
    $this->replies = $replies;
  }
  public function getReplies()
  {
    return $this->replies;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setStatus($status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
  public function setTitleLink($titleLink)
  {
    $this->titleLink = $titleLink;
  }
  public function getTitleLink()
  {
    return $this->titleLink;
  }
  public function setUpdated($updated)
  {
    $this->updated = $updated;
  }
  public function getUpdated()
  {
    return $this->updated;
  }
  public function setUrl($url)
  {
    $this->url = $url;
  }
  public function getUrl()
  {
    return $this->url;
  }
}

class Google_Service_Blogger_PostAuthor extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $displayName;
  public $id;
  protected $imageType = 'Google_Service_Blogger_PostAuthorImage';
  protected $imageDataType = '';
  public $url;


  public function setDisplayName($displayName)
  {
    $this->displayName = $displayName;
  }
  public function getDisplayName()
  {
    return $this->displayName;
  }
  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setImage(Google_Service_Blogger_PostAuthorImage $image)
  {
    $this->image = $image;
  }
  public function getImage()
  {
    return $this->image;
  }
  public function setUrl($url)
  {
    $this->url = $url;
  }
  public function getUrl()
  {
    return $this->url;
  }
}

class Google_Service_Blogger_PostAuthorImage extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $url;


  public function setUrl($url)
  {
    $this->url = $url;
  }
  public function getUrl()
  {
    return $this->url;
  }
}

class Google_Service_Blogger_PostBlog extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;


  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
}

class Google_Service_Blogger_PostImages extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $url;


  public function setUrl($url)
  {
    $this->url = $url;
  }
  public function getUrl()
  {
    return $this->url;
  }
}

class Google_Service_Blogger_PostList extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  protected $itemsType = 'Google_Service_Blogger_Post';
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

class Google_Service_Blogger_PostLocation extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $lat;
  public $lng;
  public $name;
  public $span;


  public function setLat($lat)
  {
    $this->lat = $lat;
  }
  public function getLat()
  {
    return $this->lat;
  }
  public function setLng($lng)
  {
    $this->lng = $lng;
  }
  public function getLng()
  {
    return $this->lng;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setSpan($span)
  {
    $this->span = $span;
  }
  public function getSpan()
  {
    return $this->span;
  }
}

class Google_Service_Blogger_PostPerUserInfo extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $blogId;
  public $hasEditAccess;
  public $kind;
  public $postId;
  public $userId;


  public function setBlogId($blogId)
  {
    $this->blogId = $blogId;
  }
  public function getBlogId()
  {
    return $this->blogId;
  }
  public function setHasEditAccess($hasEditAccess)
  {
    $this->hasEditAccess = $hasEditAccess;
  }
  public function getHasEditAccess()
  {
    return $this->hasEditAccess;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setPostId($postId)
  {
    $this->postId = $postId;
  }
  public function getPostId()
  {
    return $this->postId;
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

class Google_Service_Blogger_PostReplies extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Blogger_Comment';
  protected $itemsDataType = 'array';
  public $selfLink;
  public $totalItems;


  public function setItems($items)
  {
    $this->items = $items;
  }
  public function getItems()
  {
    return $this->items;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setTotalItems($totalItems)
  {
    $this->totalItems = $totalItems;
  }
  public function getTotalItems()
  {
    return $this->totalItems;
  }
}

class Google_Service_Blogger_PostUserInfo extends Google_Model
{
  protected $internal_gapi_mappings = array(
        "postUserInfo" => "post_user_info",
  );
  public $kind;
  protected $postType = 'Google_Service_Blogger_Post';
  protected $postDataType = '';
  protected $postUserInfoType = 'Google_Service_Blogger_PostPerUserInfo';
  protected $postUserInfoDataType = '';


  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setPost(Google_Service_Blogger_Post $post)
  {
    $this->post = $post;
  }
  public function getPost()
  {
    return $this->post;
  }
  public function setPostUserInfo(Google_Service_Blogger_PostPerUserInfo $postUserInfo)
  {
    $this->postUserInfo = $postUserInfo;
  }
  public function getPostUserInfo()
  {
    return $this->postUserInfo;
  }
}

class Google_Service_Blogger_PostUserInfosList extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $itemsType = 'Google_Service_Blogger_PostUserInfo';
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

class Google_Service_Blogger_User extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $about;
  protected $blogsType = 'Google_Service_Blogger_UserBlogs';
  protected $blogsDataType = '';
  public $created;
  public $displayName;
  public $id;
  public $kind;
  protected $localeType = 'Google_Service_Blogger_UserLocale';
  protected $localeDataType = '';
  public $selfLink;
  public $url;


  public function setAbout($about)
  {
    $this->about = $about;
  }
  public function getAbout()
  {
    return $this->about;
  }
  public function setBlogs(Google_Service_Blogger_UserBlogs $blogs)
  {
    $this->blogs = $blogs;
  }
  public function getBlogs()
  {
    return $this->blogs;
  }
  public function setCreated($created)
  {
    $this->created = $created;
  }
  public function getCreated()
  {
    return $this->created;
  }
  public function setDisplayName($displayName)
  {
    $this->displayName = $displayName;
  }
  public function getDisplayName()
  {
    return $this->displayName;
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
  public function setLocale(Google_Service_Blogger_UserLocale $locale)
  {
    $this->locale = $locale;
  }
  public function getLocale()
  {
    return $this->locale;
  }
  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
  public function setUrl($url)
  {
    $this->url = $url;
  }
  public function getUrl()
  {
    return $this->url;
  }
}

class Google_Service_Blogger_UserBlogs extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $selfLink;


  public function setSelfLink($selfLink)
  {
    $this->selfLink = $selfLink;
  }
  public function getSelfLink()
  {
    return $this->selfLink;
  }
}

class Google_Service_Blogger_UserLocale extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $country;
  public $language;
  public $variant;


  public function setCountry($country)
  {
    $this->country = $country;
  }
  public function getCountry()
  {
    return $this->country;
  }
  public function setLanguage($language)
  {
    $this->language = $language;
  }
  public function getLanguage()
  {
    return $this->language;
  }
  public function setVariant($variant)
  {
    $this->variant = $variant;
  }
  public function getVariant()
  {
    return $this->variant;
  }
}
