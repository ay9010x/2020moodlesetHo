<?php



class Google_Service_YouTube extends Google_Service
{
  
  const YOUTUBE =
      "https://www.googleapis.com/auth/youtube";
  
  const YOUTUBE_FORCE_SSL =
      "https://www.googleapis.com/auth/youtube.force-ssl";
  
  const YOUTUBE_READONLY =
      "https://www.googleapis.com/auth/youtube.readonly";
  
  const YOUTUBE_UPLOAD =
      "https://www.googleapis.com/auth/youtube.upload";
  
  const YOUTUBEPARTNER =
      "https://www.googleapis.com/auth/youtubepartner";
  
  const YOUTUBEPARTNER_CHANNEL_AUDIT =
      "https://www.googleapis.com/auth/youtubepartner-channel-audit";

  public $activities;
  public $captions;
  public $channelBanners;
  public $channelSections;
  public $channels;
  public $commentThreads;
  public $comments;
  public $guideCategories;
  public $i18nLanguages;
  public $i18nRegions;
  public $liveBroadcasts;
  public $liveStreams;
  public $playlistItems;
  public $playlists;
  public $search;
  public $subscriptions;
  public $thumbnails;
  public $videoAbuseReportReasons;
  public $videoCategories;
  public $videos;
  public $watermarks;
  

  
  public function __construct(Google_Client $client)
  {
    parent::__construct($client);
    $this->rootUrl = 'https://www.googleapis.com/';
    $this->servicePath = 'youtube/v3/';
    $this->version = 'v3';
    $this->serviceName = 'youtube';

    $this->activities = new Google_Service_YouTube_Activities_Resource(
        $this,
        $this->serviceName,
        'activities',
        array(
          'methods' => array(
            'insert' => array(
              'path' => 'activities',
              'httpMethod' => 'POST',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'activities',
              'httpMethod' => 'GET',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'regionCode' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'publishedBefore' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'channelId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'mine' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'home' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'publishedAfter' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->captions = new Google_Service_YouTube_Captions_Resource(
        $this,
        $this->serviceName,
        'captions',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'captions',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOf' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'download' => array(
              'path' => 'captions/{id}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'id' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'tfmt' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOf' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'tlang' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'insert' => array(
              'path' => 'captions',
              'httpMethod' => 'POST',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOf' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'sync' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'list' => array(
              'path' => 'captions',
              'httpMethod' => 'GET',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'videoId' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOf' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'update' => array(
              'path' => 'captions',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOf' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'sync' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),
          )
        )
    );
    $this->channelBanners = new Google_Service_YouTube_ChannelBanners_Resource(
        $this,
        $this->serviceName,
        'channelBanners',
        array(
          'methods' => array(
            'insert' => array(
              'path' => 'channelBanners/insert',
              'httpMethod' => 'POST',
              'parameters' => array(
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->channelSections = new Google_Service_YouTube_ChannelSections_Resource(
        $this,
        $this->serviceName,
        'channelSections',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'channelSections',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'insert' => array(
              'path' => 'channelSections',
              'httpMethod' => 'POST',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwnerChannel' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => 'channelSections',
              'httpMethod' => 'GET',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'channelId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'mine' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'hl' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'update' => array(
              'path' => 'channelSections',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->channels = new Google_Service_YouTube_Channels_Resource(
        $this,
        $this->serviceName,
        'channels',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'channels',
              'httpMethod' => 'GET',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'managedByMe' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'forUsername' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'mine' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'mySubscribers' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'hl' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'categoryId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'update' => array(
              'path' => 'channels',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->commentThreads = new Google_Service_YouTube_CommentThreads_Resource(
        $this,
        $this->serviceName,
        'commentThreads',
        array(
          'methods' => array(
            'insert' => array(
              'path' => 'commentThreads',
              'httpMethod' => 'POST',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'commentThreads',
              'httpMethod' => 'GET',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'searchTerms' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'allThreadsRelatedToChannelId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'channelId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'videoId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'moderationStatus' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'textFormat' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'order' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'update' => array(
              'path' => 'commentThreads',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->comments = new Google_Service_YouTube_Comments_Resource(
        $this,
        $this->serviceName,
        'comments',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'comments',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'comments',
              'httpMethod' => 'POST',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'comments',
              'httpMethod' => 'GET',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'parentId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'textFormat' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'markAsSpam' => array(
              'path' => 'comments/markAsSpam',
              'httpMethod' => 'POST',
              'parameters' => array(
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'setModerationStatus' => array(
              'path' => 'comments/setModerationStatus',
              'httpMethod' => 'POST',
              'parameters' => array(
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'moderationStatus' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'banAuthor' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'update' => array(
              'path' => 'comments',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->guideCategories = new Google_Service_YouTube_GuideCategories_Resource(
        $this,
        $this->serviceName,
        'guideCategories',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'guideCategories',
              'httpMethod' => 'GET',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'regionCode' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'hl' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->i18nLanguages = new Google_Service_YouTube_I18nLanguages_Resource(
        $this,
        $this->serviceName,
        'i18nLanguages',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'i18nLanguages',
              'httpMethod' => 'GET',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'hl' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->i18nRegions = new Google_Service_YouTube_I18nRegions_Resource(
        $this,
        $this->serviceName,
        'i18nRegions',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'i18nRegions',
              'httpMethod' => 'GET',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'hl' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->liveBroadcasts = new Google_Service_YouTube_LiveBroadcasts_Resource(
        $this,
        $this->serviceName,
        'liveBroadcasts',
        array(
          'methods' => array(
            'bind' => array(
              'path' => 'liveBroadcasts/bind',
              'httpMethod' => 'POST',
              'parameters' => array(
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwnerChannel' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'streamId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'bind_direct' => array(
              'path' => 'liveBroadcasts/bind/direct',
              'httpMethod' => 'POST',
              'parameters' => array(
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwnerChannel' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'streamId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'control' => array(
              'path' => 'liveBroadcasts/control',
              'httpMethod' => 'POST',
              'parameters' => array(
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'displaySlate' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'onBehalfOfContentOwnerChannel' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'offsetTimeMs' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'walltime' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'delete' => array(
              'path' => 'liveBroadcasts',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwnerChannel' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'insert' => array(
              'path' => 'liveBroadcasts',
              'httpMethod' => 'POST',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwnerChannel' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => 'liveBroadcasts',
              'httpMethod' => 'GET',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'broadcastStatus' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwnerChannel' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'mine' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'transition' => array(
              'path' => 'liveBroadcasts/transition',
              'httpMethod' => 'POST',
              'parameters' => array(
                'broadcastStatus' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwnerChannel' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'update' => array(
              'path' => 'liveBroadcasts',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwnerChannel' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->liveStreams = new Google_Service_YouTube_LiveStreams_Resource(
        $this,
        $this->serviceName,
        'liveStreams',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'liveStreams',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwnerChannel' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'insert' => array(
              'path' => 'liveStreams',
              'httpMethod' => 'POST',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwnerChannel' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => 'liveStreams',
              'httpMethod' => 'GET',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwnerChannel' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'mine' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'update' => array(
              'path' => 'liveStreams',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwnerChannel' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->playlistItems = new Google_Service_YouTube_PlaylistItems_Resource(
        $this,
        $this->serviceName,
        'playlistItems',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'playlistItems',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'playlistItems',
              'httpMethod' => 'POST',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => 'playlistItems',
              'httpMethod' => 'GET',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'playlistId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'videoId' => array(
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
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'update' => array(
              'path' => 'playlistItems',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->playlists = new Google_Service_YouTube_Playlists_Resource(
        $this,
        $this->serviceName,
        'playlists',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'playlists',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'insert' => array(
              'path' => 'playlists',
              'httpMethod' => 'POST',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwnerChannel' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => 'playlists',
              'httpMethod' => 'GET',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwnerChannel' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'channelId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'mine' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'hl' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'update' => array(
              'path' => 'playlists',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->search = new Google_Service_YouTube_Search_Resource(
        $this,
        $this->serviceName,
        'search',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'search',
              'httpMethod' => 'GET',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'eventType' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'channelId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'forDeveloper' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'videoSyndicated' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'channelType' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'videoCaption' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'publishedAfter' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'forContentOwner' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'regionCode' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'location' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'locationRadius' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'videoType' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'type' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'topicId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'publishedBefore' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'videoDimension' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'videoLicense' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'relatedToVideoId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'videoDefinition' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'videoDuration' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'relevanceLanguage' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'forMine' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'q' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'safeSearch' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'videoEmbeddable' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'videoCategoryId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'order' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->subscriptions = new Google_Service_YouTube_Subscriptions_Resource(
        $this,
        $this->serviceName,
        'subscriptions',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'subscriptions',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'insert' => array(
              'path' => 'subscriptions',
              'httpMethod' => 'POST',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'subscriptions',
              'httpMethod' => 'GET',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'onBehalfOfContentOwnerChannel' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'channelId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'mine' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'maxResults' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'forChannelId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'mySubscribers' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'order' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->thumbnails = new Google_Service_YouTube_Thumbnails_Resource(
        $this,
        $this->serviceName,
        'thumbnails',
        array(
          'methods' => array(
            'set' => array(
              'path' => 'thumbnails/set',
              'httpMethod' => 'POST',
              'parameters' => array(
                'videoId' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->videoAbuseReportReasons = new Google_Service_YouTube_VideoAbuseReportReasons_Resource(
        $this,
        $this->serviceName,
        'videoAbuseReportReasons',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'videoAbuseReportReasons',
              'httpMethod' => 'GET',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'hl' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->videoCategories = new Google_Service_YouTube_VideoCategories_Resource(
        $this,
        $this->serviceName,
        'videoCategories',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'videoCategories',
              'httpMethod' => 'GET',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'regionCode' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'hl' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->videos = new Google_Service_YouTube_Videos_Resource(
        $this,
        $this->serviceName,
        'videos',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'videos',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'getRating' => array(
              'path' => 'videos/getRating',
              'httpMethod' => 'GET',
              'parameters' => array(
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'insert' => array(
              'path' => 'videos',
              'httpMethod' => 'POST',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'stabilize' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'onBehalfOfContentOwnerChannel' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'notifySubscribers' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'autoLevels' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'list' => array(
              'path' => 'videos',
              'httpMethod' => 'GET',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'regionCode' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'locale' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'videoCategoryId' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'chart' => array(
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
                'hl' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'myRating' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'rate' => array(
              'path' => 'videos/rate',
              'httpMethod' => 'POST',
              'parameters' => array(
                'id' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'rating' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'reportAbuse' => array(
              'path' => 'videos/reportAbuse',
              'httpMethod' => 'POST',
              'parameters' => array(
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'update' => array(
              'path' => 'videos',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'part' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->watermarks = new Google_Service_YouTube_Watermarks_Resource(
        $this,
        $this->serviceName,
        'watermarks',
        array(
          'methods' => array(
            'set' => array(
              'path' => 'watermarks/set',
              'httpMethod' => 'POST',
              'parameters' => array(
                'channelId' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'unset' => array(
              'path' => 'watermarks/unset',
              'httpMethod' => 'POST',
              'parameters' => array(
                'channelId' => array(
                  'location' => 'query',
                  'type' => 'string',
                  'required' => true,
                ),
                'onBehalfOfContentOwner' => array(
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



class Google_Service_YouTube_Activities_Resource extends Google_Service_Resource
{

  
  public function insert($part, Google_Service_YouTube_Activity $postBody, $optParams = array())
  {
    $params = array('part' => $part, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_YouTube_Activity");
  }

  
  public function listActivities($part, $optParams = array())
  {
    $params = array('part' => $part);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTube_ActivityListResponse");
  }
}


class Google_Service_YouTube_Captions_Resource extends Google_Service_Resource
{

  
  public function delete($id, $optParams = array())
  {
    $params = array('id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function download($id, $optParams = array())
  {
    $params = array('id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('download', array($params));
  }

  
  public function insert($part, Google_Service_YouTube_Caption $postBody, $optParams = array())
  {
    $params = array('part' => $part, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_YouTube_Caption");
  }

  
  public function listCaptions($part, $videoId, $optParams = array())
  {
    $params = array('part' => $part, 'videoId' => $videoId);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTube_CaptionListResponse");
  }

  
  public function update($part, Google_Service_YouTube_Caption $postBody, $optParams = array())
  {
    $params = array('part' => $part, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_YouTube_Caption");
  }
}


class Google_Service_YouTube_ChannelBanners_Resource extends Google_Service_Resource
{

  
  public function insert(Google_Service_YouTube_ChannelBannerResource $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_YouTube_ChannelBannerResource");
  }
}


class Google_Service_YouTube_ChannelSections_Resource extends Google_Service_Resource
{

  
  public function delete($id, $optParams = array())
  {
    $params = array('id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function insert($part, Google_Service_YouTube_ChannelSection $postBody, $optParams = array())
  {
    $params = array('part' => $part, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_YouTube_ChannelSection");
  }

  
  public function listChannelSections($part, $optParams = array())
  {
    $params = array('part' => $part);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTube_ChannelSectionListResponse");
  }

  
  public function update($part, Google_Service_YouTube_ChannelSection $postBody, $optParams = array())
  {
    $params = array('part' => $part, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_YouTube_ChannelSection");
  }
}


class Google_Service_YouTube_Channels_Resource extends Google_Service_Resource
{

  
  public function listChannels($part, $optParams = array())
  {
    $params = array('part' => $part);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTube_ChannelListResponse");
  }

  
  public function update($part, Google_Service_YouTube_Channel $postBody, $optParams = array())
  {
    $params = array('part' => $part, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_YouTube_Channel");
  }
}


class Google_Service_YouTube_CommentThreads_Resource extends Google_Service_Resource
{

  
  public function insert($part, Google_Service_YouTube_CommentThread $postBody, $optParams = array())
  {
    $params = array('part' => $part, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_YouTube_CommentThread");
  }

  
  public function listCommentThreads($part, $optParams = array())
  {
    $params = array('part' => $part);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTube_CommentThreadListResponse");
  }

  
  public function update($part, Google_Service_YouTube_CommentThread $postBody, $optParams = array())
  {
    $params = array('part' => $part, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_YouTube_CommentThread");
  }
}


class Google_Service_YouTube_Comments_Resource extends Google_Service_Resource
{

  
  public function delete($id, $optParams = array())
  {
    $params = array('id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function insert($part, Google_Service_YouTube_Comment $postBody, $optParams = array())
  {
    $params = array('part' => $part, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_YouTube_Comment");
  }

  
  public function listComments($part, $optParams = array())
  {
    $params = array('part' => $part);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTube_CommentListResponse");
  }

  
  public function markAsSpam($id, $optParams = array())
  {
    $params = array('id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('markAsSpam', array($params));
  }

  
  public function setModerationStatus($id, $moderationStatus, $optParams = array())
  {
    $params = array('id' => $id, 'moderationStatus' => $moderationStatus);
    $params = array_merge($params, $optParams);
    return $this->call('setModerationStatus', array($params));
  }

  
  public function update($part, Google_Service_YouTube_Comment $postBody, $optParams = array())
  {
    $params = array('part' => $part, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_YouTube_Comment");
  }
}


class Google_Service_YouTube_GuideCategories_Resource extends Google_Service_Resource
{

  
  public function listGuideCategories($part, $optParams = array())
  {
    $params = array('part' => $part);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTube_GuideCategoryListResponse");
  }
}


class Google_Service_YouTube_I18nLanguages_Resource extends Google_Service_Resource
{

  
  public function listI18nLanguages($part, $optParams = array())
  {
    $params = array('part' => $part);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTube_I18nLanguageListResponse");
  }
}


class Google_Service_YouTube_I18nRegions_Resource extends Google_Service_Resource
{

  
  public function listI18nRegions($part, $optParams = array())
  {
    $params = array('part' => $part);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTube_I18nRegionListResponse");
  }
}


class Google_Service_YouTube_LiveBroadcasts_Resource extends Google_Service_Resource
{

  
  public function bind($id, $part, $optParams = array())
  {
    $params = array('id' => $id, 'part' => $part);
    $params = array_merge($params, $optParams);
    return $this->call('bind', array($params), "Google_Service_YouTube_LiveBroadcast");
  }

  
  public function bind_direct($id, $part, $optParams = array())
  {
    $params = array('id' => $id, 'part' => $part);
    $params = array_merge($params, $optParams);
    return $this->call('bind_direct', array($params), "Google_Service_YouTube_LiveBroadcast");
  }

  
  public function control($id, $part, $optParams = array())
  {
    $params = array('id' => $id, 'part' => $part);
    $params = array_merge($params, $optParams);
    return $this->call('control', array($params), "Google_Service_YouTube_LiveBroadcast");
  }

  
  public function delete($id, $optParams = array())
  {
    $params = array('id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function insert($part, Google_Service_YouTube_LiveBroadcast $postBody, $optParams = array())
  {
    $params = array('part' => $part, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_YouTube_LiveBroadcast");
  }

  
  public function listLiveBroadcasts($part, $optParams = array())
  {
    $params = array('part' => $part);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTube_LiveBroadcastListResponse");
  }

  
  public function transition($broadcastStatus, $id, $part, $optParams = array())
  {
    $params = array('broadcastStatus' => $broadcastStatus, 'id' => $id, 'part' => $part);
    $params = array_merge($params, $optParams);
    return $this->call('transition', array($params), "Google_Service_YouTube_LiveBroadcast");
  }

  
  public function update($part, Google_Service_YouTube_LiveBroadcast $postBody, $optParams = array())
  {
    $params = array('part' => $part, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_YouTube_LiveBroadcast");
  }
}


class Google_Service_YouTube_LiveStreams_Resource extends Google_Service_Resource
{

  
  public function delete($id, $optParams = array())
  {
    $params = array('id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function insert($part, Google_Service_YouTube_LiveStream $postBody, $optParams = array())
  {
    $params = array('part' => $part, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_YouTube_LiveStream");
  }

  
  public function listLiveStreams($part, $optParams = array())
  {
    $params = array('part' => $part);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTube_LiveStreamListResponse");
  }

  
  public function update($part, Google_Service_YouTube_LiveStream $postBody, $optParams = array())
  {
    $params = array('part' => $part, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_YouTube_LiveStream");
  }
}


class Google_Service_YouTube_PlaylistItems_Resource extends Google_Service_Resource
{

  
  public function delete($id, $optParams = array())
  {
    $params = array('id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function insert($part, Google_Service_YouTube_PlaylistItem $postBody, $optParams = array())
  {
    $params = array('part' => $part, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_YouTube_PlaylistItem");
  }

  
  public function listPlaylistItems($part, $optParams = array())
  {
    $params = array('part' => $part);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTube_PlaylistItemListResponse");
  }

  
  public function update($part, Google_Service_YouTube_PlaylistItem $postBody, $optParams = array())
  {
    $params = array('part' => $part, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_YouTube_PlaylistItem");
  }
}


class Google_Service_YouTube_Playlists_Resource extends Google_Service_Resource
{

  
  public function delete($id, $optParams = array())
  {
    $params = array('id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function insert($part, Google_Service_YouTube_Playlist $postBody, $optParams = array())
  {
    $params = array('part' => $part, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_YouTube_Playlist");
  }

  
  public function listPlaylists($part, $optParams = array())
  {
    $params = array('part' => $part);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTube_PlaylistListResponse");
  }

  
  public function update($part, Google_Service_YouTube_Playlist $postBody, $optParams = array())
  {
    $params = array('part' => $part, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_YouTube_Playlist");
  }
}


class Google_Service_YouTube_Search_Resource extends Google_Service_Resource
{

  
  public function listSearch($part, $optParams = array())
  {
    $params = array('part' => $part);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTube_SearchListResponse");
  }
}


class Google_Service_YouTube_Subscriptions_Resource extends Google_Service_Resource
{

  
  public function delete($id, $optParams = array())
  {
    $params = array('id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function insert($part, Google_Service_YouTube_Subscription $postBody, $optParams = array())
  {
    $params = array('part' => $part, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_YouTube_Subscription");
  }

  
  public function listSubscriptions($part, $optParams = array())
  {
    $params = array('part' => $part);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTube_SubscriptionListResponse");
  }
}


class Google_Service_YouTube_Thumbnails_Resource extends Google_Service_Resource
{

  
  public function set($videoId, $optParams = array())
  {
    $params = array('videoId' => $videoId);
    $params = array_merge($params, $optParams);
    return $this->call('set', array($params), "Google_Service_YouTube_ThumbnailSetResponse");
  }
}


class Google_Service_YouTube_VideoAbuseReportReasons_Resource extends Google_Service_Resource
{

  
  public function listVideoAbuseReportReasons($part, $optParams = array())
  {
    $params = array('part' => $part);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTube_VideoAbuseReportReasonListResponse");
  }
}


class Google_Service_YouTube_VideoCategories_Resource extends Google_Service_Resource
{

  
  public function listVideoCategories($part, $optParams = array())
  {
    $params = array('part' => $part);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTube_VideoCategoryListResponse");
  }
}


class Google_Service_YouTube_Videos_Resource extends Google_Service_Resource
{

  
  public function delete($id, $optParams = array())
  {
    $params = array('id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params));
  }

  
  public function getRating($id, $optParams = array())
  {
    $params = array('id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('getRating', array($params), "Google_Service_YouTube_VideoGetRatingResponse");
  }

  
  public function insert($part, Google_Service_YouTube_Video $postBody, $optParams = array())
  {
    $params = array('part' => $part, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_YouTube_Video");
  }

  
  public function listVideos($part, $optParams = array())
  {
    $params = array('part' => $part);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_YouTube_VideoListResponse");
  }

  
  public function rate($id, $rating, $optParams = array())
  {
    $params = array('id' => $id, 'rating' => $rating);
    $params = array_merge($params, $optParams);
    return $this->call('rate', array($params));
  }

  
  public function reportAbuse(Google_Service_YouTube_VideoAbuseReport $postBody, $optParams = array())
  {
    $params = array('postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('reportAbuse', array($params));
  }

  
  public function update($part, Google_Service_YouTube_Video $postBody, $optParams = array())
  {
    $params = array('part' => $part, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('update', array($params), "Google_Service_YouTube_Video");
  }
}


class Google_Service_YouTube_Watermarks_Resource extends Google_Service_Resource
{

  
  public function set($channelId, Google_Service_YouTube_InvideoBranding $postBody, $optParams = array())
  {
    $params = array('channelId' => $channelId, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('set', array($params));
  }

  
  public function unsetWatermarks($channelId, $optParams = array())
  {
    $params = array('channelId' => $channelId);
    $params = array_merge($params, $optParams);
    return $this->call('unset', array($params));
  }
}




class Google_Service_YouTube_AccessPolicy extends Google_Collection
{
  protected $collection_key = 'exception';
  protected $internal_gapi_mappings = array(
  );
  public $allowed;
  public $exception;


  public function setAllowed($allowed)
  {
    $this->allowed = $allowed;
  }
  public function getAllowed()
  {
    return $this->allowed;
  }
  public function setException($exception)
  {
    $this->exception = $exception;
  }
  public function getException()
  {
    return $this->exception;
  }
}

class Google_Service_YouTube_Activity extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $contentDetailsType = 'Google_Service_YouTube_ActivityContentDetails';
  protected $contentDetailsDataType = '';
  public $etag;
  public $id;
  public $kind;
  protected $snippetType = 'Google_Service_YouTube_ActivitySnippet';
  protected $snippetDataType = '';


  public function setContentDetails(Google_Service_YouTube_ActivityContentDetails $contentDetails)
  {
    $this->contentDetails = $contentDetails;
  }
  public function getContentDetails()
  {
    return $this->contentDetails;
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
  public function setSnippet(Google_Service_YouTube_ActivitySnippet $snippet)
  {
    $this->snippet = $snippet;
  }
  public function getSnippet()
  {
    return $this->snippet;
  }
}

class Google_Service_YouTube_ActivityContentDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $bulletinType = 'Google_Service_YouTube_ActivityContentDetailsBulletin';
  protected $bulletinDataType = '';
  protected $channelItemType = 'Google_Service_YouTube_ActivityContentDetailsChannelItem';
  protected $channelItemDataType = '';
  protected $commentType = 'Google_Service_YouTube_ActivityContentDetailsComment';
  protected $commentDataType = '';
  protected $favoriteType = 'Google_Service_YouTube_ActivityContentDetailsFavorite';
  protected $favoriteDataType = '';
  protected $likeType = 'Google_Service_YouTube_ActivityContentDetailsLike';
  protected $likeDataType = '';
  protected $playlistItemType = 'Google_Service_YouTube_ActivityContentDetailsPlaylistItem';
  protected $playlistItemDataType = '';
  protected $promotedItemType = 'Google_Service_YouTube_ActivityContentDetailsPromotedItem';
  protected $promotedItemDataType = '';
  protected $recommendationType = 'Google_Service_YouTube_ActivityContentDetailsRecommendation';
  protected $recommendationDataType = '';
  protected $socialType = 'Google_Service_YouTube_ActivityContentDetailsSocial';
  protected $socialDataType = '';
  protected $subscriptionType = 'Google_Service_YouTube_ActivityContentDetailsSubscription';
  protected $subscriptionDataType = '';
  protected $uploadType = 'Google_Service_YouTube_ActivityContentDetailsUpload';
  protected $uploadDataType = '';


  public function setBulletin(Google_Service_YouTube_ActivityContentDetailsBulletin $bulletin)
  {
    $this->bulletin = $bulletin;
  }
  public function getBulletin()
  {
    return $this->bulletin;
  }
  public function setChannelItem(Google_Service_YouTube_ActivityContentDetailsChannelItem $channelItem)
  {
    $this->channelItem = $channelItem;
  }
  public function getChannelItem()
  {
    return $this->channelItem;
  }
  public function setComment(Google_Service_YouTube_ActivityContentDetailsComment $comment)
  {
    $this->comment = $comment;
  }
  public function getComment()
  {
    return $this->comment;
  }
  public function setFavorite(Google_Service_YouTube_ActivityContentDetailsFavorite $favorite)
  {
    $this->favorite = $favorite;
  }
  public function getFavorite()
  {
    return $this->favorite;
  }
  public function setLike(Google_Service_YouTube_ActivityContentDetailsLike $like)
  {
    $this->like = $like;
  }
  public function getLike()
  {
    return $this->like;
  }
  public function setPlaylistItem(Google_Service_YouTube_ActivityContentDetailsPlaylistItem $playlistItem)
  {
    $this->playlistItem = $playlistItem;
  }
  public function getPlaylistItem()
  {
    return $this->playlistItem;
  }
  public function setPromotedItem(Google_Service_YouTube_ActivityContentDetailsPromotedItem $promotedItem)
  {
    $this->promotedItem = $promotedItem;
  }
  public function getPromotedItem()
  {
    return $this->promotedItem;
  }
  public function setRecommendation(Google_Service_YouTube_ActivityContentDetailsRecommendation $recommendation)
  {
    $this->recommendation = $recommendation;
  }
  public function getRecommendation()
  {
    return $this->recommendation;
  }
  public function setSocial(Google_Service_YouTube_ActivityContentDetailsSocial $social)
  {
    $this->social = $social;
  }
  public function getSocial()
  {
    return $this->social;
  }
  public function setSubscription(Google_Service_YouTube_ActivityContentDetailsSubscription $subscription)
  {
    $this->subscription = $subscription;
  }
  public function getSubscription()
  {
    return $this->subscription;
  }
  public function setUpload(Google_Service_YouTube_ActivityContentDetailsUpload $upload)
  {
    $this->upload = $upload;
  }
  public function getUpload()
  {
    return $this->upload;
  }
}

class Google_Service_YouTube_ActivityContentDetailsBulletin extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $resourceIdType = 'Google_Service_YouTube_ResourceId';
  protected $resourceIdDataType = '';


  public function setResourceId(Google_Service_YouTube_ResourceId $resourceId)
  {
    $this->resourceId = $resourceId;
  }
  public function getResourceId()
  {
    return $this->resourceId;
  }
}

class Google_Service_YouTube_ActivityContentDetailsChannelItem extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $resourceIdType = 'Google_Service_YouTube_ResourceId';
  protected $resourceIdDataType = '';


  public function setResourceId(Google_Service_YouTube_ResourceId $resourceId)
  {
    $this->resourceId = $resourceId;
  }
  public function getResourceId()
  {
    return $this->resourceId;
  }
}

class Google_Service_YouTube_ActivityContentDetailsComment extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $resourceIdType = 'Google_Service_YouTube_ResourceId';
  protected $resourceIdDataType = '';


  public function setResourceId(Google_Service_YouTube_ResourceId $resourceId)
  {
    $this->resourceId = $resourceId;
  }
  public function getResourceId()
  {
    return $this->resourceId;
  }
}

class Google_Service_YouTube_ActivityContentDetailsFavorite extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $resourceIdType = 'Google_Service_YouTube_ResourceId';
  protected $resourceIdDataType = '';


  public function setResourceId(Google_Service_YouTube_ResourceId $resourceId)
  {
    $this->resourceId = $resourceId;
  }
  public function getResourceId()
  {
    return $this->resourceId;
  }
}

class Google_Service_YouTube_ActivityContentDetailsLike extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $resourceIdType = 'Google_Service_YouTube_ResourceId';
  protected $resourceIdDataType = '';


  public function setResourceId(Google_Service_YouTube_ResourceId $resourceId)
  {
    $this->resourceId = $resourceId;
  }
  public function getResourceId()
  {
    return $this->resourceId;
  }
}

class Google_Service_YouTube_ActivityContentDetailsPlaylistItem extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $playlistId;
  public $playlistItemId;
  protected $resourceIdType = 'Google_Service_YouTube_ResourceId';
  protected $resourceIdDataType = '';


  public function setPlaylistId($playlistId)
  {
    $this->playlistId = $playlistId;
  }
  public function getPlaylistId()
  {
    return $this->playlistId;
  }
  public function setPlaylistItemId($playlistItemId)
  {
    $this->playlistItemId = $playlistItemId;
  }
  public function getPlaylistItemId()
  {
    return $this->playlistItemId;
  }
  public function setResourceId(Google_Service_YouTube_ResourceId $resourceId)
  {
    $this->resourceId = $resourceId;
  }
  public function getResourceId()
  {
    return $this->resourceId;
  }
}

class Google_Service_YouTube_ActivityContentDetailsPromotedItem extends Google_Collection
{
  protected $collection_key = 'impressionUrl';
  protected $internal_gapi_mappings = array(
  );
  public $adTag;
  public $clickTrackingUrl;
  public $creativeViewUrl;
  public $ctaType;
  public $customCtaButtonText;
  public $descriptionText;
  public $destinationUrl;
  public $forecastingUrl;
  public $impressionUrl;
  public $videoId;


  public function setAdTag($adTag)
  {
    $this->adTag = $adTag;
  }
  public function getAdTag()
  {
    return $this->adTag;
  }
  public function setClickTrackingUrl($clickTrackingUrl)
  {
    $this->clickTrackingUrl = $clickTrackingUrl;
  }
  public function getClickTrackingUrl()
  {
    return $this->clickTrackingUrl;
  }
  public function setCreativeViewUrl($creativeViewUrl)
  {
    $this->creativeViewUrl = $creativeViewUrl;
  }
  public function getCreativeViewUrl()
  {
    return $this->creativeViewUrl;
  }
  public function setCtaType($ctaType)
  {
    $this->ctaType = $ctaType;
  }
  public function getCtaType()
  {
    return $this->ctaType;
  }
  public function setCustomCtaButtonText($customCtaButtonText)
  {
    $this->customCtaButtonText = $customCtaButtonText;
  }
  public function getCustomCtaButtonText()
  {
    return $this->customCtaButtonText;
  }
  public function setDescriptionText($descriptionText)
  {
    $this->descriptionText = $descriptionText;
  }
  public function getDescriptionText()
  {
    return $this->descriptionText;
  }
  public function setDestinationUrl($destinationUrl)
  {
    $this->destinationUrl = $destinationUrl;
  }
  public function getDestinationUrl()
  {
    return $this->destinationUrl;
  }
  public function setForecastingUrl($forecastingUrl)
  {
    $this->forecastingUrl = $forecastingUrl;
  }
  public function getForecastingUrl()
  {
    return $this->forecastingUrl;
  }
  public function setImpressionUrl($impressionUrl)
  {
    $this->impressionUrl = $impressionUrl;
  }
  public function getImpressionUrl()
  {
    return $this->impressionUrl;
  }
  public function setVideoId($videoId)
  {
    $this->videoId = $videoId;
  }
  public function getVideoId()
  {
    return $this->videoId;
  }
}

class Google_Service_YouTube_ActivityContentDetailsRecommendation extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $reason;
  protected $resourceIdType = 'Google_Service_YouTube_ResourceId';
  protected $resourceIdDataType = '';
  protected $seedResourceIdType = 'Google_Service_YouTube_ResourceId';
  protected $seedResourceIdDataType = '';


  public function setReason($reason)
  {
    $this->reason = $reason;
  }
  public function getReason()
  {
    return $this->reason;
  }
  public function setResourceId(Google_Service_YouTube_ResourceId $resourceId)
  {
    $this->resourceId = $resourceId;
  }
  public function getResourceId()
  {
    return $this->resourceId;
  }
  public function setSeedResourceId(Google_Service_YouTube_ResourceId $seedResourceId)
  {
    $this->seedResourceId = $seedResourceId;
  }
  public function getSeedResourceId()
  {
    return $this->seedResourceId;
  }
}

class Google_Service_YouTube_ActivityContentDetailsSocial extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $author;
  public $imageUrl;
  public $referenceUrl;
  protected $resourceIdType = 'Google_Service_YouTube_ResourceId';
  protected $resourceIdDataType = '';
  public $type;


  public function setAuthor($author)
  {
    $this->author = $author;
  }
  public function getAuthor()
  {
    return $this->author;
  }
  public function setImageUrl($imageUrl)
  {
    $this->imageUrl = $imageUrl;
  }
  public function getImageUrl()
  {
    return $this->imageUrl;
  }
  public function setReferenceUrl($referenceUrl)
  {
    $this->referenceUrl = $referenceUrl;
  }
  public function getReferenceUrl()
  {
    return $this->referenceUrl;
  }
  public function setResourceId(Google_Service_YouTube_ResourceId $resourceId)
  {
    $this->resourceId = $resourceId;
  }
  public function getResourceId()
  {
    return $this->resourceId;
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

class Google_Service_YouTube_ActivityContentDetailsSubscription extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $resourceIdType = 'Google_Service_YouTube_ResourceId';
  protected $resourceIdDataType = '';


  public function setResourceId(Google_Service_YouTube_ResourceId $resourceId)
  {
    $this->resourceId = $resourceId;
  }
  public function getResourceId()
  {
    return $this->resourceId;
  }
}

class Google_Service_YouTube_ActivityContentDetailsUpload extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $videoId;


  public function setVideoId($videoId)
  {
    $this->videoId = $videoId;
  }
  public function getVideoId()
  {
    return $this->videoId;
  }
}

class Google_Service_YouTube_ActivityListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $eventId;
  protected $itemsType = 'Google_Service_YouTube_Activity';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;
  protected $pageInfoType = 'Google_Service_YouTube_PageInfo';
  protected $pageInfoDataType = '';
  public $prevPageToken;
  protected $tokenPaginationType = 'Google_Service_YouTube_TokenPagination';
  protected $tokenPaginationDataType = '';
  public $visitorId;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setEventId($eventId)
  {
    $this->eventId = $eventId;
  }
  public function getEventId()
  {
    return $this->eventId;
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
  public function setPageInfo(Google_Service_YouTube_PageInfo $pageInfo)
  {
    $this->pageInfo = $pageInfo;
  }
  public function getPageInfo()
  {
    return $this->pageInfo;
  }
  public function setPrevPageToken($prevPageToken)
  {
    $this->prevPageToken = $prevPageToken;
  }
  public function getPrevPageToken()
  {
    return $this->prevPageToken;
  }
  public function setTokenPagination(Google_Service_YouTube_TokenPagination $tokenPagination)
  {
    $this->tokenPagination = $tokenPagination;
  }
  public function getTokenPagination()
  {
    return $this->tokenPagination;
  }
  public function setVisitorId($visitorId)
  {
    $this->visitorId = $visitorId;
  }
  public function getVisitorId()
  {
    return $this->visitorId;
  }
}

class Google_Service_YouTube_ActivitySnippet extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $channelId;
  public $channelTitle;
  public $description;
  public $groupId;
  public $publishedAt;
  protected $thumbnailsType = 'Google_Service_YouTube_ThumbnailDetails';
  protected $thumbnailsDataType = '';
  public $title;
  public $type;


  public function setChannelId($channelId)
  {
    $this->channelId = $channelId;
  }
  public function getChannelId()
  {
    return $this->channelId;
  }
  public function setChannelTitle($channelTitle)
  {
    $this->channelTitle = $channelTitle;
  }
  public function getChannelTitle()
  {
    return $this->channelTitle;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setGroupId($groupId)
  {
    $this->groupId = $groupId;
  }
  public function getGroupId()
  {
    return $this->groupId;
  }
  public function setPublishedAt($publishedAt)
  {
    $this->publishedAt = $publishedAt;
  }
  public function getPublishedAt()
  {
    return $this->publishedAt;
  }
  public function setThumbnails(Google_Service_YouTube_ThumbnailDetails $thumbnails)
  {
    $this->thumbnails = $thumbnails;
  }
  public function getThumbnails()
  {
    return $this->thumbnails;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
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

class Google_Service_YouTube_Caption extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $id;
  public $kind;
  protected $snippetType = 'Google_Service_YouTube_CaptionSnippet';
  protected $snippetDataType = '';


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
  public function setSnippet(Google_Service_YouTube_CaptionSnippet $snippet)
  {
    $this->snippet = $snippet;
  }
  public function getSnippet()
  {
    return $this->snippet;
  }
}

class Google_Service_YouTube_CaptionListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $eventId;
  protected $itemsType = 'Google_Service_YouTube_Caption';
  protected $itemsDataType = 'array';
  public $kind;
  public $visitorId;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setEventId($eventId)
  {
    $this->eventId = $eventId;
  }
  public function getEventId()
  {
    return $this->eventId;
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
  public function setVisitorId($visitorId)
  {
    $this->visitorId = $visitorId;
  }
  public function getVisitorId()
  {
    return $this->visitorId;
  }
}

class Google_Service_YouTube_CaptionSnippet extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $audioTrackType;
  public $failureReason;
  public $isAutoSynced;
  public $isCC;
  public $isDraft;
  public $isEasyReader;
  public $isLarge;
  public $language;
  public $lastUpdated;
  public $name;
  public $status;
  public $trackKind;
  public $videoId;


  public function setAudioTrackType($audioTrackType)
  {
    $this->audioTrackType = $audioTrackType;
  }
  public function getAudioTrackType()
  {
    return $this->audioTrackType;
  }
  public function setFailureReason($failureReason)
  {
    $this->failureReason = $failureReason;
  }
  public function getFailureReason()
  {
    return $this->failureReason;
  }
  public function setIsAutoSynced($isAutoSynced)
  {
    $this->isAutoSynced = $isAutoSynced;
  }
  public function getIsAutoSynced()
  {
    return $this->isAutoSynced;
  }
  public function setIsCC($isCC)
  {
    $this->isCC = $isCC;
  }
  public function getIsCC()
  {
    return $this->isCC;
  }
  public function setIsDraft($isDraft)
  {
    $this->isDraft = $isDraft;
  }
  public function getIsDraft()
  {
    return $this->isDraft;
  }
  public function setIsEasyReader($isEasyReader)
  {
    $this->isEasyReader = $isEasyReader;
  }
  public function getIsEasyReader()
  {
    return $this->isEasyReader;
  }
  public function setIsLarge($isLarge)
  {
    $this->isLarge = $isLarge;
  }
  public function getIsLarge()
  {
    return $this->isLarge;
  }
  public function setLanguage($language)
  {
    $this->language = $language;
  }
  public function getLanguage()
  {
    return $this->language;
  }
  public function setLastUpdated($lastUpdated)
  {
    $this->lastUpdated = $lastUpdated;
  }
  public function getLastUpdated()
  {
    return $this->lastUpdated;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setStatus($status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
  public function setTrackKind($trackKind)
  {
    $this->trackKind = $trackKind;
  }
  public function getTrackKind()
  {
    return $this->trackKind;
  }
  public function setVideoId($videoId)
  {
    $this->videoId = $videoId;
  }
  public function getVideoId()
  {
    return $this->videoId;
  }
}

class Google_Service_YouTube_CdnSettings extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $format;
  protected $ingestionInfoType = 'Google_Service_YouTube_IngestionInfo';
  protected $ingestionInfoDataType = '';
  public $ingestionType;


  public function setFormat($format)
  {
    $this->format = $format;
  }
  public function getFormat()
  {
    return $this->format;
  }
  public function setIngestionInfo(Google_Service_YouTube_IngestionInfo $ingestionInfo)
  {
    $this->ingestionInfo = $ingestionInfo;
  }
  public function getIngestionInfo()
  {
    return $this->ingestionInfo;
  }
  public function setIngestionType($ingestionType)
  {
    $this->ingestionType = $ingestionType;
  }
  public function getIngestionType()
  {
    return $this->ingestionType;
  }
}

class Google_Service_YouTube_Channel extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $auditDetailsType = 'Google_Service_YouTube_ChannelAuditDetails';
  protected $auditDetailsDataType = '';
  protected $brandingSettingsType = 'Google_Service_YouTube_ChannelBrandingSettings';
  protected $brandingSettingsDataType = '';
  protected $contentDetailsType = 'Google_Service_YouTube_ChannelContentDetails';
  protected $contentDetailsDataType = '';
  protected $contentOwnerDetailsType = 'Google_Service_YouTube_ChannelContentOwnerDetails';
  protected $contentOwnerDetailsDataType = '';
  protected $conversionPingsType = 'Google_Service_YouTube_ChannelConversionPings';
  protected $conversionPingsDataType = '';
  public $etag;
  public $id;
  protected $invideoPromotionType = 'Google_Service_YouTube_InvideoPromotion';
  protected $invideoPromotionDataType = '';
  public $kind;
  protected $localizationsType = 'Google_Service_YouTube_ChannelLocalization';
  protected $localizationsDataType = 'map';
  protected $snippetType = 'Google_Service_YouTube_ChannelSnippet';
  protected $snippetDataType = '';
  protected $statisticsType = 'Google_Service_YouTube_ChannelStatistics';
  protected $statisticsDataType = '';
  protected $statusType = 'Google_Service_YouTube_ChannelStatus';
  protected $statusDataType = '';
  protected $topicDetailsType = 'Google_Service_YouTube_ChannelTopicDetails';
  protected $topicDetailsDataType = '';


  public function setAuditDetails(Google_Service_YouTube_ChannelAuditDetails $auditDetails)
  {
    $this->auditDetails = $auditDetails;
  }
  public function getAuditDetails()
  {
    return $this->auditDetails;
  }
  public function setBrandingSettings(Google_Service_YouTube_ChannelBrandingSettings $brandingSettings)
  {
    $this->brandingSettings = $brandingSettings;
  }
  public function getBrandingSettings()
  {
    return $this->brandingSettings;
  }
  public function setContentDetails(Google_Service_YouTube_ChannelContentDetails $contentDetails)
  {
    $this->contentDetails = $contentDetails;
  }
  public function getContentDetails()
  {
    return $this->contentDetails;
  }
  public function setContentOwnerDetails(Google_Service_YouTube_ChannelContentOwnerDetails $contentOwnerDetails)
  {
    $this->contentOwnerDetails = $contentOwnerDetails;
  }
  public function getContentOwnerDetails()
  {
    return $this->contentOwnerDetails;
  }
  public function setConversionPings(Google_Service_YouTube_ChannelConversionPings $conversionPings)
  {
    $this->conversionPings = $conversionPings;
  }
  public function getConversionPings()
  {
    return $this->conversionPings;
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
  public function setInvideoPromotion(Google_Service_YouTube_InvideoPromotion $invideoPromotion)
  {
    $this->invideoPromotion = $invideoPromotion;
  }
  public function getInvideoPromotion()
  {
    return $this->invideoPromotion;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setLocalizations($localizations)
  {
    $this->localizations = $localizations;
  }
  public function getLocalizations()
  {
    return $this->localizations;
  }
  public function setSnippet(Google_Service_YouTube_ChannelSnippet $snippet)
  {
    $this->snippet = $snippet;
  }
  public function getSnippet()
  {
    return $this->snippet;
  }
  public function setStatistics(Google_Service_YouTube_ChannelStatistics $statistics)
  {
    $this->statistics = $statistics;
  }
  public function getStatistics()
  {
    return $this->statistics;
  }
  public function setStatus(Google_Service_YouTube_ChannelStatus $status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
  public function setTopicDetails(Google_Service_YouTube_ChannelTopicDetails $topicDetails)
  {
    $this->topicDetails = $topicDetails;
  }
  public function getTopicDetails()
  {
    return $this->topicDetails;
  }
}

class Google_Service_YouTube_ChannelAuditDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $communityGuidelinesGoodStanding;
  public $contentIdClaimsGoodStanding;
  public $copyrightStrikesGoodStanding;
  public $overallGoodStanding;


  public function setCommunityGuidelinesGoodStanding($communityGuidelinesGoodStanding)
  {
    $this->communityGuidelinesGoodStanding = $communityGuidelinesGoodStanding;
  }
  public function getCommunityGuidelinesGoodStanding()
  {
    return $this->communityGuidelinesGoodStanding;
  }
  public function setContentIdClaimsGoodStanding($contentIdClaimsGoodStanding)
  {
    $this->contentIdClaimsGoodStanding = $contentIdClaimsGoodStanding;
  }
  public function getContentIdClaimsGoodStanding()
  {
    return $this->contentIdClaimsGoodStanding;
  }
  public function setCopyrightStrikesGoodStanding($copyrightStrikesGoodStanding)
  {
    $this->copyrightStrikesGoodStanding = $copyrightStrikesGoodStanding;
  }
  public function getCopyrightStrikesGoodStanding()
  {
    return $this->copyrightStrikesGoodStanding;
  }
  public function setOverallGoodStanding($overallGoodStanding)
  {
    $this->overallGoodStanding = $overallGoodStanding;
  }
  public function getOverallGoodStanding()
  {
    return $this->overallGoodStanding;
  }
}

class Google_Service_YouTube_ChannelBannerResource extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $kind;
  public $url;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
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

class Google_Service_YouTube_ChannelBrandingSettings extends Google_Collection
{
  protected $collection_key = 'hints';
  protected $internal_gapi_mappings = array(
  );
  protected $channelType = 'Google_Service_YouTube_ChannelSettings';
  protected $channelDataType = '';
  protected $hintsType = 'Google_Service_YouTube_PropertyValue';
  protected $hintsDataType = 'array';
  protected $imageType = 'Google_Service_YouTube_ImageSettings';
  protected $imageDataType = '';
  protected $watchType = 'Google_Service_YouTube_WatchSettings';
  protected $watchDataType = '';


  public function setChannel(Google_Service_YouTube_ChannelSettings $channel)
  {
    $this->channel = $channel;
  }
  public function getChannel()
  {
    return $this->channel;
  }
  public function setHints($hints)
  {
    $this->hints = $hints;
  }
  public function getHints()
  {
    return $this->hints;
  }
  public function setImage(Google_Service_YouTube_ImageSettings $image)
  {
    $this->image = $image;
  }
  public function getImage()
  {
    return $this->image;
  }
  public function setWatch(Google_Service_YouTube_WatchSettings $watch)
  {
    $this->watch = $watch;
  }
  public function getWatch()
  {
    return $this->watch;
  }
}

class Google_Service_YouTube_ChannelContentDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $googlePlusUserId;
  protected $relatedPlaylistsType = 'Google_Service_YouTube_ChannelContentDetailsRelatedPlaylists';
  protected $relatedPlaylistsDataType = '';


  public function setGooglePlusUserId($googlePlusUserId)
  {
    $this->googlePlusUserId = $googlePlusUserId;
  }
  public function getGooglePlusUserId()
  {
    return $this->googlePlusUserId;
  }
  public function setRelatedPlaylists(Google_Service_YouTube_ChannelContentDetailsRelatedPlaylists $relatedPlaylists)
  {
    $this->relatedPlaylists = $relatedPlaylists;
  }
  public function getRelatedPlaylists()
  {
    return $this->relatedPlaylists;
  }
}

class Google_Service_YouTube_ChannelContentDetailsRelatedPlaylists extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $favorites;
  public $likes;
  public $uploads;
  public $watchHistory;
  public $watchLater;


  public function setFavorites($favorites)
  {
    $this->favorites = $favorites;
  }
  public function getFavorites()
  {
    return $this->favorites;
  }
  public function setLikes($likes)
  {
    $this->likes = $likes;
  }
  public function getLikes()
  {
    return $this->likes;
  }
  public function setUploads($uploads)
  {
    $this->uploads = $uploads;
  }
  public function getUploads()
  {
    return $this->uploads;
  }
  public function setWatchHistory($watchHistory)
  {
    $this->watchHistory = $watchHistory;
  }
  public function getWatchHistory()
  {
    return $this->watchHistory;
  }
  public function setWatchLater($watchLater)
  {
    $this->watchLater = $watchLater;
  }
  public function getWatchLater()
  {
    return $this->watchLater;
  }
}

class Google_Service_YouTube_ChannelContentOwnerDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $contentOwner;
  public $timeLinked;


  public function setContentOwner($contentOwner)
  {
    $this->contentOwner = $contentOwner;
  }
  public function getContentOwner()
  {
    return $this->contentOwner;
  }
  public function setTimeLinked($timeLinked)
  {
    $this->timeLinked = $timeLinked;
  }
  public function getTimeLinked()
  {
    return $this->timeLinked;
  }
}

class Google_Service_YouTube_ChannelConversionPing extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $context;
  public $conversionUrl;


  public function setContext($context)
  {
    $this->context = $context;
  }
  public function getContext()
  {
    return $this->context;
  }
  public function setConversionUrl($conversionUrl)
  {
    $this->conversionUrl = $conversionUrl;
  }
  public function getConversionUrl()
  {
    return $this->conversionUrl;
  }
}

class Google_Service_YouTube_ChannelConversionPings extends Google_Collection
{
  protected $collection_key = 'pings';
  protected $internal_gapi_mappings = array(
  );
  protected $pingsType = 'Google_Service_YouTube_ChannelConversionPing';
  protected $pingsDataType = 'array';


  public function setPings($pings)
  {
    $this->pings = $pings;
  }
  public function getPings()
  {
    return $this->pings;
  }
}

class Google_Service_YouTube_ChannelId extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $value;


  public function setValue($value)
  {
    $this->value = $value;
  }
  public function getValue()
  {
    return $this->value;
  }
}

class Google_Service_YouTube_ChannelListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $eventId;
  protected $itemsType = 'Google_Service_YouTube_Channel';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;
  protected $pageInfoType = 'Google_Service_YouTube_PageInfo';
  protected $pageInfoDataType = '';
  public $prevPageToken;
  protected $tokenPaginationType = 'Google_Service_YouTube_TokenPagination';
  protected $tokenPaginationDataType = '';
  public $visitorId;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setEventId($eventId)
  {
    $this->eventId = $eventId;
  }
  public function getEventId()
  {
    return $this->eventId;
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
  public function setPageInfo(Google_Service_YouTube_PageInfo $pageInfo)
  {
    $this->pageInfo = $pageInfo;
  }
  public function getPageInfo()
  {
    return $this->pageInfo;
  }
  public function setPrevPageToken($prevPageToken)
  {
    $this->prevPageToken = $prevPageToken;
  }
  public function getPrevPageToken()
  {
    return $this->prevPageToken;
  }
  public function setTokenPagination(Google_Service_YouTube_TokenPagination $tokenPagination)
  {
    $this->tokenPagination = $tokenPagination;
  }
  public function getTokenPagination()
  {
    return $this->tokenPagination;
  }
  public function setVisitorId($visitorId)
  {
    $this->visitorId = $visitorId;
  }
  public function getVisitorId()
  {
    return $this->visitorId;
  }
}

class Google_Service_YouTube_ChannelLocalization extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $description;
  public $title;


  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
}

class Google_Service_YouTube_ChannelLocalizations extends Google_Model
{
}

class Google_Service_YouTube_ChannelSection extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $contentDetailsType = 'Google_Service_YouTube_ChannelSectionContentDetails';
  protected $contentDetailsDataType = '';
  public $etag;
  public $id;
  public $kind;
  protected $localizationsType = 'Google_Service_YouTube_ChannelSectionLocalization';
  protected $localizationsDataType = 'map';
  protected $snippetType = 'Google_Service_YouTube_ChannelSectionSnippet';
  protected $snippetDataType = '';
  protected $targetingType = 'Google_Service_YouTube_ChannelSectionTargeting';
  protected $targetingDataType = '';


  public function setContentDetails(Google_Service_YouTube_ChannelSectionContentDetails $contentDetails)
  {
    $this->contentDetails = $contentDetails;
  }
  public function getContentDetails()
  {
    return $this->contentDetails;
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
  public function setLocalizations($localizations)
  {
    $this->localizations = $localizations;
  }
  public function getLocalizations()
  {
    return $this->localizations;
  }
  public function setSnippet(Google_Service_YouTube_ChannelSectionSnippet $snippet)
  {
    $this->snippet = $snippet;
  }
  public function getSnippet()
  {
    return $this->snippet;
  }
  public function setTargeting(Google_Service_YouTube_ChannelSectionTargeting $targeting)
  {
    $this->targeting = $targeting;
  }
  public function getTargeting()
  {
    return $this->targeting;
  }
}

class Google_Service_YouTube_ChannelSectionContentDetails extends Google_Collection
{
  protected $collection_key = 'playlists';
  protected $internal_gapi_mappings = array(
  );
  public $channels;
  public $playlists;


  public function setChannels($channels)
  {
    $this->channels = $channels;
  }
  public function getChannels()
  {
    return $this->channels;
  }
  public function setPlaylists($playlists)
  {
    $this->playlists = $playlists;
  }
  public function getPlaylists()
  {
    return $this->playlists;
  }
}

class Google_Service_YouTube_ChannelSectionListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $eventId;
  protected $itemsType = 'Google_Service_YouTube_ChannelSection';
  protected $itemsDataType = 'array';
  public $kind;
  public $visitorId;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setEventId($eventId)
  {
    $this->eventId = $eventId;
  }
  public function getEventId()
  {
    return $this->eventId;
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
  public function setVisitorId($visitorId)
  {
    $this->visitorId = $visitorId;
  }
  public function getVisitorId()
  {
    return $this->visitorId;
  }
}

class Google_Service_YouTube_ChannelSectionLocalization extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $title;


  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
}

class Google_Service_YouTube_ChannelSectionLocalizations extends Google_Model
{
}

class Google_Service_YouTube_ChannelSectionSnippet extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $channelId;
  public $defaultLanguage;
  protected $localizedType = 'Google_Service_YouTube_ChannelSectionLocalization';
  protected $localizedDataType = '';
  public $position;
  public $style;
  public $title;
  public $type;


  public function setChannelId($channelId)
  {
    $this->channelId = $channelId;
  }
  public function getChannelId()
  {
    return $this->channelId;
  }
  public function setDefaultLanguage($defaultLanguage)
  {
    $this->defaultLanguage = $defaultLanguage;
  }
  public function getDefaultLanguage()
  {
    return $this->defaultLanguage;
  }
  public function setLocalized(Google_Service_YouTube_ChannelSectionLocalization $localized)
  {
    $this->localized = $localized;
  }
  public function getLocalized()
  {
    return $this->localized;
  }
  public function setPosition($position)
  {
    $this->position = $position;
  }
  public function getPosition()
  {
    return $this->position;
  }
  public function setStyle($style)
  {
    $this->style = $style;
  }
  public function getStyle()
  {
    return $this->style;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
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

class Google_Service_YouTube_ChannelSectionTargeting extends Google_Collection
{
  protected $collection_key = 'regions';
  protected $internal_gapi_mappings = array(
  );
  public $countries;
  public $languages;
  public $regions;


  public function setCountries($countries)
  {
    $this->countries = $countries;
  }
  public function getCountries()
  {
    return $this->countries;
  }
  public function setLanguages($languages)
  {
    $this->languages = $languages;
  }
  public function getLanguages()
  {
    return $this->languages;
  }
  public function setRegions($regions)
  {
    $this->regions = $regions;
  }
  public function getRegions()
  {
    return $this->regions;
  }
}

class Google_Service_YouTube_ChannelSettings extends Google_Collection
{
  protected $collection_key = 'featuredChannelsUrls';
  protected $internal_gapi_mappings = array(
  );
  public $country;
  public $defaultLanguage;
  public $defaultTab;
  public $description;
  public $featuredChannelsTitle;
  public $featuredChannelsUrls;
  public $keywords;
  public $moderateComments;
  public $profileColor;
  public $showBrowseView;
  public $showRelatedChannels;
  public $title;
  public $trackingAnalyticsAccountId;
  public $unsubscribedTrailer;


  public function setCountry($country)
  {
    $this->country = $country;
  }
  public function getCountry()
  {
    return $this->country;
  }
  public function setDefaultLanguage($defaultLanguage)
  {
    $this->defaultLanguage = $defaultLanguage;
  }
  public function getDefaultLanguage()
  {
    return $this->defaultLanguage;
  }
  public function setDefaultTab($defaultTab)
  {
    $this->defaultTab = $defaultTab;
  }
  public function getDefaultTab()
  {
    return $this->defaultTab;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setFeaturedChannelsTitle($featuredChannelsTitle)
  {
    $this->featuredChannelsTitle = $featuredChannelsTitle;
  }
  public function getFeaturedChannelsTitle()
  {
    return $this->featuredChannelsTitle;
  }
  public function setFeaturedChannelsUrls($featuredChannelsUrls)
  {
    $this->featuredChannelsUrls = $featuredChannelsUrls;
  }
  public function getFeaturedChannelsUrls()
  {
    return $this->featuredChannelsUrls;
  }
  public function setKeywords($keywords)
  {
    $this->keywords = $keywords;
  }
  public function getKeywords()
  {
    return $this->keywords;
  }
  public function setModerateComments($moderateComments)
  {
    $this->moderateComments = $moderateComments;
  }
  public function getModerateComments()
  {
    return $this->moderateComments;
  }
  public function setProfileColor($profileColor)
  {
    $this->profileColor = $profileColor;
  }
  public function getProfileColor()
  {
    return $this->profileColor;
  }
  public function setShowBrowseView($showBrowseView)
  {
    $this->showBrowseView = $showBrowseView;
  }
  public function getShowBrowseView()
  {
    return $this->showBrowseView;
  }
  public function setShowRelatedChannels($showRelatedChannels)
  {
    $this->showRelatedChannels = $showRelatedChannels;
  }
  public function getShowRelatedChannels()
  {
    return $this->showRelatedChannels;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
  public function setTrackingAnalyticsAccountId($trackingAnalyticsAccountId)
  {
    $this->trackingAnalyticsAccountId = $trackingAnalyticsAccountId;
  }
  public function getTrackingAnalyticsAccountId()
  {
    return $this->trackingAnalyticsAccountId;
  }
  public function setUnsubscribedTrailer($unsubscribedTrailer)
  {
    $this->unsubscribedTrailer = $unsubscribedTrailer;
  }
  public function getUnsubscribedTrailer()
  {
    return $this->unsubscribedTrailer;
  }
}

class Google_Service_YouTube_ChannelSnippet extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $country;
  public $defaultLanguage;
  public $description;
  protected $localizedType = 'Google_Service_YouTube_ChannelLocalization';
  protected $localizedDataType = '';
  public $publishedAt;
  protected $thumbnailsType = 'Google_Service_YouTube_ThumbnailDetails';
  protected $thumbnailsDataType = '';
  public $title;


  public function setCountry($country)
  {
    $this->country = $country;
  }
  public function getCountry()
  {
    return $this->country;
  }
  public function setDefaultLanguage($defaultLanguage)
  {
    $this->defaultLanguage = $defaultLanguage;
  }
  public function getDefaultLanguage()
  {
    return $this->defaultLanguage;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setLocalized(Google_Service_YouTube_ChannelLocalization $localized)
  {
    $this->localized = $localized;
  }
  public function getLocalized()
  {
    return $this->localized;
  }
  public function setPublishedAt($publishedAt)
  {
    $this->publishedAt = $publishedAt;
  }
  public function getPublishedAt()
  {
    return $this->publishedAt;
  }
  public function setThumbnails(Google_Service_YouTube_ThumbnailDetails $thumbnails)
  {
    $this->thumbnails = $thumbnails;
  }
  public function getThumbnails()
  {
    return $this->thumbnails;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
}

class Google_Service_YouTube_ChannelStatistics extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $commentCount;
  public $hiddenSubscriberCount;
  public $subscriberCount;
  public $videoCount;
  public $viewCount;


  public function setCommentCount($commentCount)
  {
    $this->commentCount = $commentCount;
  }
  public function getCommentCount()
  {
    return $this->commentCount;
  }
  public function setHiddenSubscriberCount($hiddenSubscriberCount)
  {
    $this->hiddenSubscriberCount = $hiddenSubscriberCount;
  }
  public function getHiddenSubscriberCount()
  {
    return $this->hiddenSubscriberCount;
  }
  public function setSubscriberCount($subscriberCount)
  {
    $this->subscriberCount = $subscriberCount;
  }
  public function getSubscriberCount()
  {
    return $this->subscriberCount;
  }
  public function setVideoCount($videoCount)
  {
    $this->videoCount = $videoCount;
  }
  public function getVideoCount()
  {
    return $this->videoCount;
  }
  public function setViewCount($viewCount)
  {
    $this->viewCount = $viewCount;
  }
  public function getViewCount()
  {
    return $this->viewCount;
  }
}

class Google_Service_YouTube_ChannelStatus extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $isLinked;
  public $longUploadsStatus;
  public $privacyStatus;


  public function setIsLinked($isLinked)
  {
    $this->isLinked = $isLinked;
  }
  public function getIsLinked()
  {
    return $this->isLinked;
  }
  public function setLongUploadsStatus($longUploadsStatus)
  {
    $this->longUploadsStatus = $longUploadsStatus;
  }
  public function getLongUploadsStatus()
  {
    return $this->longUploadsStatus;
  }
  public function setPrivacyStatus($privacyStatus)
  {
    $this->privacyStatus = $privacyStatus;
  }
  public function getPrivacyStatus()
  {
    return $this->privacyStatus;
  }
}

class Google_Service_YouTube_ChannelTopicDetails extends Google_Collection
{
  protected $collection_key = 'topicIds';
  protected $internal_gapi_mappings = array(
  );
  public $topicIds;


  public function setTopicIds($topicIds)
  {
    $this->topicIds = $topicIds;
  }
  public function getTopicIds()
  {
    return $this->topicIds;
  }
}

class Google_Service_YouTube_Comment extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $id;
  public $kind;
  protected $snippetType = 'Google_Service_YouTube_CommentSnippet';
  protected $snippetDataType = '';


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
  public function setSnippet(Google_Service_YouTube_CommentSnippet $snippet)
  {
    $this->snippet = $snippet;
  }
  public function getSnippet()
  {
    return $this->snippet;
  }
}

class Google_Service_YouTube_CommentListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $eventId;
  protected $itemsType = 'Google_Service_YouTube_Comment';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;
  protected $pageInfoType = 'Google_Service_YouTube_PageInfo';
  protected $pageInfoDataType = '';
  protected $tokenPaginationType = 'Google_Service_YouTube_TokenPagination';
  protected $tokenPaginationDataType = '';
  public $visitorId;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setEventId($eventId)
  {
    $this->eventId = $eventId;
  }
  public function getEventId()
  {
    return $this->eventId;
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
  public function setPageInfo(Google_Service_YouTube_PageInfo $pageInfo)
  {
    $this->pageInfo = $pageInfo;
  }
  public function getPageInfo()
  {
    return $this->pageInfo;
  }
  public function setTokenPagination(Google_Service_YouTube_TokenPagination $tokenPagination)
  {
    $this->tokenPagination = $tokenPagination;
  }
  public function getTokenPagination()
  {
    return $this->tokenPagination;
  }
  public function setVisitorId($visitorId)
  {
    $this->visitorId = $visitorId;
  }
  public function getVisitorId()
  {
    return $this->visitorId;
  }
}

class Google_Service_YouTube_CommentSnippet extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $authorChannelIdType = 'Google_Service_YouTube_ChannelId';
  protected $authorChannelIdDataType = '';
  public $authorChannelUrl;
  public $authorDisplayName;
  public $authorGoogleplusProfileUrl;
  public $authorProfileImageUrl;
  public $canRate;
  public $channelId;
  public $likeCount;
  public $moderationStatus;
  public $parentId;
  public $publishedAt;
  public $textDisplay;
  public $textOriginal;
  public $updatedAt;
  public $videoId;
  public $viewerRating;


  public function setAuthorChannelId(Google_Service_YouTube_ChannelId $authorChannelId)
  {
    $this->authorChannelId = $authorChannelId;
  }
  public function getAuthorChannelId()
  {
    return $this->authorChannelId;
  }
  public function setAuthorChannelUrl($authorChannelUrl)
  {
    $this->authorChannelUrl = $authorChannelUrl;
  }
  public function getAuthorChannelUrl()
  {
    return $this->authorChannelUrl;
  }
  public function setAuthorDisplayName($authorDisplayName)
  {
    $this->authorDisplayName = $authorDisplayName;
  }
  public function getAuthorDisplayName()
  {
    return $this->authorDisplayName;
  }
  public function setAuthorGoogleplusProfileUrl($authorGoogleplusProfileUrl)
  {
    $this->authorGoogleplusProfileUrl = $authorGoogleplusProfileUrl;
  }
  public function getAuthorGoogleplusProfileUrl()
  {
    return $this->authorGoogleplusProfileUrl;
  }
  public function setAuthorProfileImageUrl($authorProfileImageUrl)
  {
    $this->authorProfileImageUrl = $authorProfileImageUrl;
  }
  public function getAuthorProfileImageUrl()
  {
    return $this->authorProfileImageUrl;
  }
  public function setCanRate($canRate)
  {
    $this->canRate = $canRate;
  }
  public function getCanRate()
  {
    return $this->canRate;
  }
  public function setChannelId($channelId)
  {
    $this->channelId = $channelId;
  }
  public function getChannelId()
  {
    return $this->channelId;
  }
  public function setLikeCount($likeCount)
  {
    $this->likeCount = $likeCount;
  }
  public function getLikeCount()
  {
    return $this->likeCount;
  }
  public function setModerationStatus($moderationStatus)
  {
    $this->moderationStatus = $moderationStatus;
  }
  public function getModerationStatus()
  {
    return $this->moderationStatus;
  }
  public function setParentId($parentId)
  {
    $this->parentId = $parentId;
  }
  public function getParentId()
  {
    return $this->parentId;
  }
  public function setPublishedAt($publishedAt)
  {
    $this->publishedAt = $publishedAt;
  }
  public function getPublishedAt()
  {
    return $this->publishedAt;
  }
  public function setTextDisplay($textDisplay)
  {
    $this->textDisplay = $textDisplay;
  }
  public function getTextDisplay()
  {
    return $this->textDisplay;
  }
  public function setTextOriginal($textOriginal)
  {
    $this->textOriginal = $textOriginal;
  }
  public function getTextOriginal()
  {
    return $this->textOriginal;
  }
  public function setUpdatedAt($updatedAt)
  {
    $this->updatedAt = $updatedAt;
  }
  public function getUpdatedAt()
  {
    return $this->updatedAt;
  }
  public function setVideoId($videoId)
  {
    $this->videoId = $videoId;
  }
  public function getVideoId()
  {
    return $this->videoId;
  }
  public function setViewerRating($viewerRating)
  {
    $this->viewerRating = $viewerRating;
  }
  public function getViewerRating()
  {
    return $this->viewerRating;
  }
}

class Google_Service_YouTube_CommentThread extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $id;
  public $kind;
  protected $repliesType = 'Google_Service_YouTube_CommentThreadReplies';
  protected $repliesDataType = '';
  protected $snippetType = 'Google_Service_YouTube_CommentThreadSnippet';
  protected $snippetDataType = '';


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
  public function setReplies(Google_Service_YouTube_CommentThreadReplies $replies)
  {
    $this->replies = $replies;
  }
  public function getReplies()
  {
    return $this->replies;
  }
  public function setSnippet(Google_Service_YouTube_CommentThreadSnippet $snippet)
  {
    $this->snippet = $snippet;
  }
  public function getSnippet()
  {
    return $this->snippet;
  }
}

class Google_Service_YouTube_CommentThreadListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $eventId;
  protected $itemsType = 'Google_Service_YouTube_CommentThread';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;
  protected $pageInfoType = 'Google_Service_YouTube_PageInfo';
  protected $pageInfoDataType = '';
  protected $tokenPaginationType = 'Google_Service_YouTube_TokenPagination';
  protected $tokenPaginationDataType = '';
  public $visitorId;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setEventId($eventId)
  {
    $this->eventId = $eventId;
  }
  public function getEventId()
  {
    return $this->eventId;
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
  public function setPageInfo(Google_Service_YouTube_PageInfo $pageInfo)
  {
    $this->pageInfo = $pageInfo;
  }
  public function getPageInfo()
  {
    return $this->pageInfo;
  }
  public function setTokenPagination(Google_Service_YouTube_TokenPagination $tokenPagination)
  {
    $this->tokenPagination = $tokenPagination;
  }
  public function getTokenPagination()
  {
    return $this->tokenPagination;
  }
  public function setVisitorId($visitorId)
  {
    $this->visitorId = $visitorId;
  }
  public function getVisitorId()
  {
    return $this->visitorId;
  }
}

class Google_Service_YouTube_CommentThreadReplies extends Google_Collection
{
  protected $collection_key = 'comments';
  protected $internal_gapi_mappings = array(
  );
  protected $commentsType = 'Google_Service_YouTube_Comment';
  protected $commentsDataType = 'array';


  public function setComments($comments)
  {
    $this->comments = $comments;
  }
  public function getComments()
  {
    return $this->comments;
  }
}

class Google_Service_YouTube_CommentThreadSnippet extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $canReply;
  public $channelId;
  public $isPublic;
  protected $topLevelCommentType = 'Google_Service_YouTube_Comment';
  protected $topLevelCommentDataType = '';
  public $totalReplyCount;
  public $videoId;


  public function setCanReply($canReply)
  {
    $this->canReply = $canReply;
  }
  public function getCanReply()
  {
    return $this->canReply;
  }
  public function setChannelId($channelId)
  {
    $this->channelId = $channelId;
  }
  public function getChannelId()
  {
    return $this->channelId;
  }
  public function setIsPublic($isPublic)
  {
    $this->isPublic = $isPublic;
  }
  public function getIsPublic()
  {
    return $this->isPublic;
  }
  public function setTopLevelComment(Google_Service_YouTube_Comment $topLevelComment)
  {
    $this->topLevelComment = $topLevelComment;
  }
  public function getTopLevelComment()
  {
    return $this->topLevelComment;
  }
  public function setTotalReplyCount($totalReplyCount)
  {
    $this->totalReplyCount = $totalReplyCount;
  }
  public function getTotalReplyCount()
  {
    return $this->totalReplyCount;
  }
  public function setVideoId($videoId)
  {
    $this->videoId = $videoId;
  }
  public function getVideoId()
  {
    return $this->videoId;
  }
}

class Google_Service_YouTube_ContentRating extends Google_Collection
{
  protected $collection_key = 'djctqRatingReasons';
  protected $internal_gapi_mappings = array(
  );
  public $acbRating;
  public $agcomRating;
  public $anatelRating;
  public $bbfcRating;
  public $bfvcRating;
  public $bmukkRating;
  public $catvRating;
  public $catvfrRating;
  public $cbfcRating;
  public $cccRating;
  public $cceRating;
  public $chfilmRating;
  public $chvrsRating;
  public $cicfRating;
  public $cnaRating;
  public $cncRating;
  public $csaRating;
  public $cscfRating;
  public $czfilmRating;
  public $djctqRating;
  public $djctqRatingReasons;
  public $eefilmRating;
  public $egfilmRating;
  public $eirinRating;
  public $fcbmRating;
  public $fcoRating;
  public $fmocRating;
  public $fpbRating;
  public $fskRating;
  public $grfilmRating;
  public $icaaRating;
  public $ifcoRating;
  public $ilfilmRating;
  public $incaaRating;
  public $kfcbRating;
  public $kijkwijzerRating;
  public $kmrbRating;
  public $lsfRating;
  public $mccaaRating;
  public $mccypRating;
  public $mdaRating;
  public $medietilsynetRating;
  public $mekuRating;
  public $mibacRating;
  public $mocRating;
  public $moctwRating;
  public $mpaaRating;
  public $mtrcbRating;
  public $nbcRating;
  public $nbcplRating;
  public $nfrcRating;
  public $nfvcbRating;
  public $nkclvRating;
  public $oflcRating;
  public $pefilmRating;
  public $rcnofRating;
  public $resorteviolenciaRating;
  public $rtcRating;
  public $rteRating;
  public $russiaRating;
  public $skfilmRating;
  public $smaisRating;
  public $smsaRating;
  public $tvpgRating;
  public $ytRating;


  public function setAcbRating($acbRating)
  {
    $this->acbRating = $acbRating;
  }
  public function getAcbRating()
  {
    return $this->acbRating;
  }
  public function setAgcomRating($agcomRating)
  {
    $this->agcomRating = $agcomRating;
  }
  public function getAgcomRating()
  {
    return $this->agcomRating;
  }
  public function setAnatelRating($anatelRating)
  {
    $this->anatelRating = $anatelRating;
  }
  public function getAnatelRating()
  {
    return $this->anatelRating;
  }
  public function setBbfcRating($bbfcRating)
  {
    $this->bbfcRating = $bbfcRating;
  }
  public function getBbfcRating()
  {
    return $this->bbfcRating;
  }
  public function setBfvcRating($bfvcRating)
  {
    $this->bfvcRating = $bfvcRating;
  }
  public function getBfvcRating()
  {
    return $this->bfvcRating;
  }
  public function setBmukkRating($bmukkRating)
  {
    $this->bmukkRating = $bmukkRating;
  }
  public function getBmukkRating()
  {
    return $this->bmukkRating;
  }
  public function setCatvRating($catvRating)
  {
    $this->catvRating = $catvRating;
  }
  public function getCatvRating()
  {
    return $this->catvRating;
  }
  public function setCatvfrRating($catvfrRating)
  {
    $this->catvfrRating = $catvfrRating;
  }
  public function getCatvfrRating()
  {
    return $this->catvfrRating;
  }
  public function setCbfcRating($cbfcRating)
  {
    $this->cbfcRating = $cbfcRating;
  }
  public function getCbfcRating()
  {
    return $this->cbfcRating;
  }
  public function setCccRating($cccRating)
  {
    $this->cccRating = $cccRating;
  }
  public function getCccRating()
  {
    return $this->cccRating;
  }
  public function setCceRating($cceRating)
  {
    $this->cceRating = $cceRating;
  }
  public function getCceRating()
  {
    return $this->cceRating;
  }
  public function setChfilmRating($chfilmRating)
  {
    $this->chfilmRating = $chfilmRating;
  }
  public function getChfilmRating()
  {
    return $this->chfilmRating;
  }
  public function setChvrsRating($chvrsRating)
  {
    $this->chvrsRating = $chvrsRating;
  }
  public function getChvrsRating()
  {
    return $this->chvrsRating;
  }
  public function setCicfRating($cicfRating)
  {
    $this->cicfRating = $cicfRating;
  }
  public function getCicfRating()
  {
    return $this->cicfRating;
  }
  public function setCnaRating($cnaRating)
  {
    $this->cnaRating = $cnaRating;
  }
  public function getCnaRating()
  {
    return $this->cnaRating;
  }
  public function setCncRating($cncRating)
  {
    $this->cncRating = $cncRating;
  }
  public function getCncRating()
  {
    return $this->cncRating;
  }
  public function setCsaRating($csaRating)
  {
    $this->csaRating = $csaRating;
  }
  public function getCsaRating()
  {
    return $this->csaRating;
  }
  public function setCscfRating($cscfRating)
  {
    $this->cscfRating = $cscfRating;
  }
  public function getCscfRating()
  {
    return $this->cscfRating;
  }
  public function setCzfilmRating($czfilmRating)
  {
    $this->czfilmRating = $czfilmRating;
  }
  public function getCzfilmRating()
  {
    return $this->czfilmRating;
  }
  public function setDjctqRating($djctqRating)
  {
    $this->djctqRating = $djctqRating;
  }
  public function getDjctqRating()
  {
    return $this->djctqRating;
  }
  public function setDjctqRatingReasons($djctqRatingReasons)
  {
    $this->djctqRatingReasons = $djctqRatingReasons;
  }
  public function getDjctqRatingReasons()
  {
    return $this->djctqRatingReasons;
  }
  public function setEefilmRating($eefilmRating)
  {
    $this->eefilmRating = $eefilmRating;
  }
  public function getEefilmRating()
  {
    return $this->eefilmRating;
  }
  public function setEgfilmRating($egfilmRating)
  {
    $this->egfilmRating = $egfilmRating;
  }
  public function getEgfilmRating()
  {
    return $this->egfilmRating;
  }
  public function setEirinRating($eirinRating)
  {
    $this->eirinRating = $eirinRating;
  }
  public function getEirinRating()
  {
    return $this->eirinRating;
  }
  public function setFcbmRating($fcbmRating)
  {
    $this->fcbmRating = $fcbmRating;
  }
  public function getFcbmRating()
  {
    return $this->fcbmRating;
  }
  public function setFcoRating($fcoRating)
  {
    $this->fcoRating = $fcoRating;
  }
  public function getFcoRating()
  {
    return $this->fcoRating;
  }
  public function setFmocRating($fmocRating)
  {
    $this->fmocRating = $fmocRating;
  }
  public function getFmocRating()
  {
    return $this->fmocRating;
  }
  public function setFpbRating($fpbRating)
  {
    $this->fpbRating = $fpbRating;
  }
  public function getFpbRating()
  {
    return $this->fpbRating;
  }
  public function setFskRating($fskRating)
  {
    $this->fskRating = $fskRating;
  }
  public function getFskRating()
  {
    return $this->fskRating;
  }
  public function setGrfilmRating($grfilmRating)
  {
    $this->grfilmRating = $grfilmRating;
  }
  public function getGrfilmRating()
  {
    return $this->grfilmRating;
  }
  public function setIcaaRating($icaaRating)
  {
    $this->icaaRating = $icaaRating;
  }
  public function getIcaaRating()
  {
    return $this->icaaRating;
  }
  public function setIfcoRating($ifcoRating)
  {
    $this->ifcoRating = $ifcoRating;
  }
  public function getIfcoRating()
  {
    return $this->ifcoRating;
  }
  public function setIlfilmRating($ilfilmRating)
  {
    $this->ilfilmRating = $ilfilmRating;
  }
  public function getIlfilmRating()
  {
    return $this->ilfilmRating;
  }
  public function setIncaaRating($incaaRating)
  {
    $this->incaaRating = $incaaRating;
  }
  public function getIncaaRating()
  {
    return $this->incaaRating;
  }
  public function setKfcbRating($kfcbRating)
  {
    $this->kfcbRating = $kfcbRating;
  }
  public function getKfcbRating()
  {
    return $this->kfcbRating;
  }
  public function setKijkwijzerRating($kijkwijzerRating)
  {
    $this->kijkwijzerRating = $kijkwijzerRating;
  }
  public function getKijkwijzerRating()
  {
    return $this->kijkwijzerRating;
  }
  public function setKmrbRating($kmrbRating)
  {
    $this->kmrbRating = $kmrbRating;
  }
  public function getKmrbRating()
  {
    return $this->kmrbRating;
  }
  public function setLsfRating($lsfRating)
  {
    $this->lsfRating = $lsfRating;
  }
  public function getLsfRating()
  {
    return $this->lsfRating;
  }
  public function setMccaaRating($mccaaRating)
  {
    $this->mccaaRating = $mccaaRating;
  }
  public function getMccaaRating()
  {
    return $this->mccaaRating;
  }
  public function setMccypRating($mccypRating)
  {
    $this->mccypRating = $mccypRating;
  }
  public function getMccypRating()
  {
    return $this->mccypRating;
  }
  public function setMdaRating($mdaRating)
  {
    $this->mdaRating = $mdaRating;
  }
  public function getMdaRating()
  {
    return $this->mdaRating;
  }
  public function setMedietilsynetRating($medietilsynetRating)
  {
    $this->medietilsynetRating = $medietilsynetRating;
  }
  public function getMedietilsynetRating()
  {
    return $this->medietilsynetRating;
  }
  public function setMekuRating($mekuRating)
  {
    $this->mekuRating = $mekuRating;
  }
  public function getMekuRating()
  {
    return $this->mekuRating;
  }
  public function setMibacRating($mibacRating)
  {
    $this->mibacRating = $mibacRating;
  }
  public function getMibacRating()
  {
    return $this->mibacRating;
  }
  public function setMocRating($mocRating)
  {
    $this->mocRating = $mocRating;
  }
  public function getMocRating()
  {
    return $this->mocRating;
  }
  public function setMoctwRating($moctwRating)
  {
    $this->moctwRating = $moctwRating;
  }
  public function getMoctwRating()
  {
    return $this->moctwRating;
  }
  public function setMpaaRating($mpaaRating)
  {
    $this->mpaaRating = $mpaaRating;
  }
  public function getMpaaRating()
  {
    return $this->mpaaRating;
  }
  public function setMtrcbRating($mtrcbRating)
  {
    $this->mtrcbRating = $mtrcbRating;
  }
  public function getMtrcbRating()
  {
    return $this->mtrcbRating;
  }
  public function setNbcRating($nbcRating)
  {
    $this->nbcRating = $nbcRating;
  }
  public function getNbcRating()
  {
    return $this->nbcRating;
  }
  public function setNbcplRating($nbcplRating)
  {
    $this->nbcplRating = $nbcplRating;
  }
  public function getNbcplRating()
  {
    return $this->nbcplRating;
  }
  public function setNfrcRating($nfrcRating)
  {
    $this->nfrcRating = $nfrcRating;
  }
  public function getNfrcRating()
  {
    return $this->nfrcRating;
  }
  public function setNfvcbRating($nfvcbRating)
  {
    $this->nfvcbRating = $nfvcbRating;
  }
  public function getNfvcbRating()
  {
    return $this->nfvcbRating;
  }
  public function setNkclvRating($nkclvRating)
  {
    $this->nkclvRating = $nkclvRating;
  }
  public function getNkclvRating()
  {
    return $this->nkclvRating;
  }
  public function setOflcRating($oflcRating)
  {
    $this->oflcRating = $oflcRating;
  }
  public function getOflcRating()
  {
    return $this->oflcRating;
  }
  public function setPefilmRating($pefilmRating)
  {
    $this->pefilmRating = $pefilmRating;
  }
  public function getPefilmRating()
  {
    return $this->pefilmRating;
  }
  public function setRcnofRating($rcnofRating)
  {
    $this->rcnofRating = $rcnofRating;
  }
  public function getRcnofRating()
  {
    return $this->rcnofRating;
  }
  public function setResorteviolenciaRating($resorteviolenciaRating)
  {
    $this->resorteviolenciaRating = $resorteviolenciaRating;
  }
  public function getResorteviolenciaRating()
  {
    return $this->resorteviolenciaRating;
  }
  public function setRtcRating($rtcRating)
  {
    $this->rtcRating = $rtcRating;
  }
  public function getRtcRating()
  {
    return $this->rtcRating;
  }
  public function setRteRating($rteRating)
  {
    $this->rteRating = $rteRating;
  }
  public function getRteRating()
  {
    return $this->rteRating;
  }
  public function setRussiaRating($russiaRating)
  {
    $this->russiaRating = $russiaRating;
  }
  public function getRussiaRating()
  {
    return $this->russiaRating;
  }
  public function setSkfilmRating($skfilmRating)
  {
    $this->skfilmRating = $skfilmRating;
  }
  public function getSkfilmRating()
  {
    return $this->skfilmRating;
  }
  public function setSmaisRating($smaisRating)
  {
    $this->smaisRating = $smaisRating;
  }
  public function getSmaisRating()
  {
    return $this->smaisRating;
  }
  public function setSmsaRating($smsaRating)
  {
    $this->smsaRating = $smsaRating;
  }
  public function getSmsaRating()
  {
    return $this->smsaRating;
  }
  public function setTvpgRating($tvpgRating)
  {
    $this->tvpgRating = $tvpgRating;
  }
  public function getTvpgRating()
  {
    return $this->tvpgRating;
  }
  public function setYtRating($ytRating)
  {
    $this->ytRating = $ytRating;
  }
  public function getYtRating()
  {
    return $this->ytRating;
  }
}

class Google_Service_YouTube_GeoPoint extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $altitude;
  public $latitude;
  public $longitude;


  public function setAltitude($altitude)
  {
    $this->altitude = $altitude;
  }
  public function getAltitude()
  {
    return $this->altitude;
  }
  public function setLatitude($latitude)
  {
    $this->latitude = $latitude;
  }
  public function getLatitude()
  {
    return $this->latitude;
  }
  public function setLongitude($longitude)
  {
    $this->longitude = $longitude;
  }
  public function getLongitude()
  {
    return $this->longitude;
  }
}

class Google_Service_YouTube_GuideCategory extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $id;
  public $kind;
  protected $snippetType = 'Google_Service_YouTube_GuideCategorySnippet';
  protected $snippetDataType = '';


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
  public function setSnippet(Google_Service_YouTube_GuideCategorySnippet $snippet)
  {
    $this->snippet = $snippet;
  }
  public function getSnippet()
  {
    return $this->snippet;
  }
}

class Google_Service_YouTube_GuideCategoryListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $eventId;
  protected $itemsType = 'Google_Service_YouTube_GuideCategory';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;
  protected $pageInfoType = 'Google_Service_YouTube_PageInfo';
  protected $pageInfoDataType = '';
  public $prevPageToken;
  protected $tokenPaginationType = 'Google_Service_YouTube_TokenPagination';
  protected $tokenPaginationDataType = '';
  public $visitorId;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setEventId($eventId)
  {
    $this->eventId = $eventId;
  }
  public function getEventId()
  {
    return $this->eventId;
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
  public function setPageInfo(Google_Service_YouTube_PageInfo $pageInfo)
  {
    $this->pageInfo = $pageInfo;
  }
  public function getPageInfo()
  {
    return $this->pageInfo;
  }
  public function setPrevPageToken($prevPageToken)
  {
    $this->prevPageToken = $prevPageToken;
  }
  public function getPrevPageToken()
  {
    return $this->prevPageToken;
  }
  public function setTokenPagination(Google_Service_YouTube_TokenPagination $tokenPagination)
  {
    $this->tokenPagination = $tokenPagination;
  }
  public function getTokenPagination()
  {
    return $this->tokenPagination;
  }
  public function setVisitorId($visitorId)
  {
    $this->visitorId = $visitorId;
  }
  public function getVisitorId()
  {
    return $this->visitorId;
  }
}

class Google_Service_YouTube_GuideCategorySnippet extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $channelId;
  public $title;


  public function setChannelId($channelId)
  {
    $this->channelId = $channelId;
  }
  public function getChannelId()
  {
    return $this->channelId;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
}

class Google_Service_YouTube_I18nLanguage extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $id;
  public $kind;
  protected $snippetType = 'Google_Service_YouTube_I18nLanguageSnippet';
  protected $snippetDataType = '';


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
  public function setSnippet(Google_Service_YouTube_I18nLanguageSnippet $snippet)
  {
    $this->snippet = $snippet;
  }
  public function getSnippet()
  {
    return $this->snippet;
  }
}

class Google_Service_YouTube_I18nLanguageListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $eventId;
  protected $itemsType = 'Google_Service_YouTube_I18nLanguage';
  protected $itemsDataType = 'array';
  public $kind;
  public $visitorId;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setEventId($eventId)
  {
    $this->eventId = $eventId;
  }
  public function getEventId()
  {
    return $this->eventId;
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
  public function setVisitorId($visitorId)
  {
    $this->visitorId = $visitorId;
  }
  public function getVisitorId()
  {
    return $this->visitorId;
  }
}

class Google_Service_YouTube_I18nLanguageSnippet extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $hl;
  public $name;


  public function setHl($hl)
  {
    $this->hl = $hl;
  }
  public function getHl()
  {
    return $this->hl;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
}

class Google_Service_YouTube_I18nRegion extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $id;
  public $kind;
  protected $snippetType = 'Google_Service_YouTube_I18nRegionSnippet';
  protected $snippetDataType = '';


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
  public function setSnippet(Google_Service_YouTube_I18nRegionSnippet $snippet)
  {
    $this->snippet = $snippet;
  }
  public function getSnippet()
  {
    return $this->snippet;
  }
}

class Google_Service_YouTube_I18nRegionListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $eventId;
  protected $itemsType = 'Google_Service_YouTube_I18nRegion';
  protected $itemsDataType = 'array';
  public $kind;
  public $visitorId;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setEventId($eventId)
  {
    $this->eventId = $eventId;
  }
  public function getEventId()
  {
    return $this->eventId;
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
  public function setVisitorId($visitorId)
  {
    $this->visitorId = $visitorId;
  }
  public function getVisitorId()
  {
    return $this->visitorId;
  }
}

class Google_Service_YouTube_I18nRegionSnippet extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $gl;
  public $name;


  public function setGl($gl)
  {
    $this->gl = $gl;
  }
  public function getGl()
  {
    return $this->gl;
  }
  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
}

class Google_Service_YouTube_ImageSettings extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $backgroundImageUrlType = 'Google_Service_YouTube_LocalizedProperty';
  protected $backgroundImageUrlDataType = '';
  public $bannerExternalUrl;
  public $bannerImageUrl;
  public $bannerMobileExtraHdImageUrl;
  public $bannerMobileHdImageUrl;
  public $bannerMobileImageUrl;
  public $bannerMobileLowImageUrl;
  public $bannerMobileMediumHdImageUrl;
  public $bannerTabletExtraHdImageUrl;
  public $bannerTabletHdImageUrl;
  public $bannerTabletImageUrl;
  public $bannerTabletLowImageUrl;
  public $bannerTvHighImageUrl;
  public $bannerTvImageUrl;
  public $bannerTvLowImageUrl;
  public $bannerTvMediumImageUrl;
  protected $largeBrandedBannerImageImapScriptType = 'Google_Service_YouTube_LocalizedProperty';
  protected $largeBrandedBannerImageImapScriptDataType = '';
  protected $largeBrandedBannerImageUrlType = 'Google_Service_YouTube_LocalizedProperty';
  protected $largeBrandedBannerImageUrlDataType = '';
  protected $smallBrandedBannerImageImapScriptType = 'Google_Service_YouTube_LocalizedProperty';
  protected $smallBrandedBannerImageImapScriptDataType = '';
  protected $smallBrandedBannerImageUrlType = 'Google_Service_YouTube_LocalizedProperty';
  protected $smallBrandedBannerImageUrlDataType = '';
  public $trackingImageUrl;
  public $watchIconImageUrl;


  public function setBackgroundImageUrl(Google_Service_YouTube_LocalizedProperty $backgroundImageUrl)
  {
    $this->backgroundImageUrl = $backgroundImageUrl;
  }
  public function getBackgroundImageUrl()
  {
    return $this->backgroundImageUrl;
  }
  public function setBannerExternalUrl($bannerExternalUrl)
  {
    $this->bannerExternalUrl = $bannerExternalUrl;
  }
  public function getBannerExternalUrl()
  {
    return $this->bannerExternalUrl;
  }
  public function setBannerImageUrl($bannerImageUrl)
  {
    $this->bannerImageUrl = $bannerImageUrl;
  }
  public function getBannerImageUrl()
  {
    return $this->bannerImageUrl;
  }
  public function setBannerMobileExtraHdImageUrl($bannerMobileExtraHdImageUrl)
  {
    $this->bannerMobileExtraHdImageUrl = $bannerMobileExtraHdImageUrl;
  }
  public function getBannerMobileExtraHdImageUrl()
  {
    return $this->bannerMobileExtraHdImageUrl;
  }
  public function setBannerMobileHdImageUrl($bannerMobileHdImageUrl)
  {
    $this->bannerMobileHdImageUrl = $bannerMobileHdImageUrl;
  }
  public function getBannerMobileHdImageUrl()
  {
    return $this->bannerMobileHdImageUrl;
  }
  public function setBannerMobileImageUrl($bannerMobileImageUrl)
  {
    $this->bannerMobileImageUrl = $bannerMobileImageUrl;
  }
  public function getBannerMobileImageUrl()
  {
    return $this->bannerMobileImageUrl;
  }
  public function setBannerMobileLowImageUrl($bannerMobileLowImageUrl)
  {
    $this->bannerMobileLowImageUrl = $bannerMobileLowImageUrl;
  }
  public function getBannerMobileLowImageUrl()
  {
    return $this->bannerMobileLowImageUrl;
  }
  public function setBannerMobileMediumHdImageUrl($bannerMobileMediumHdImageUrl)
  {
    $this->bannerMobileMediumHdImageUrl = $bannerMobileMediumHdImageUrl;
  }
  public function getBannerMobileMediumHdImageUrl()
  {
    return $this->bannerMobileMediumHdImageUrl;
  }
  public function setBannerTabletExtraHdImageUrl($bannerTabletExtraHdImageUrl)
  {
    $this->bannerTabletExtraHdImageUrl = $bannerTabletExtraHdImageUrl;
  }
  public function getBannerTabletExtraHdImageUrl()
  {
    return $this->bannerTabletExtraHdImageUrl;
  }
  public function setBannerTabletHdImageUrl($bannerTabletHdImageUrl)
  {
    $this->bannerTabletHdImageUrl = $bannerTabletHdImageUrl;
  }
  public function getBannerTabletHdImageUrl()
  {
    return $this->bannerTabletHdImageUrl;
  }
  public function setBannerTabletImageUrl($bannerTabletImageUrl)
  {
    $this->bannerTabletImageUrl = $bannerTabletImageUrl;
  }
  public function getBannerTabletImageUrl()
  {
    return $this->bannerTabletImageUrl;
  }
  public function setBannerTabletLowImageUrl($bannerTabletLowImageUrl)
  {
    $this->bannerTabletLowImageUrl = $bannerTabletLowImageUrl;
  }
  public function getBannerTabletLowImageUrl()
  {
    return $this->bannerTabletLowImageUrl;
  }
  public function setBannerTvHighImageUrl($bannerTvHighImageUrl)
  {
    $this->bannerTvHighImageUrl = $bannerTvHighImageUrl;
  }
  public function getBannerTvHighImageUrl()
  {
    return $this->bannerTvHighImageUrl;
  }
  public function setBannerTvImageUrl($bannerTvImageUrl)
  {
    $this->bannerTvImageUrl = $bannerTvImageUrl;
  }
  public function getBannerTvImageUrl()
  {
    return $this->bannerTvImageUrl;
  }
  public function setBannerTvLowImageUrl($bannerTvLowImageUrl)
  {
    $this->bannerTvLowImageUrl = $bannerTvLowImageUrl;
  }
  public function getBannerTvLowImageUrl()
  {
    return $this->bannerTvLowImageUrl;
  }
  public function setBannerTvMediumImageUrl($bannerTvMediumImageUrl)
  {
    $this->bannerTvMediumImageUrl = $bannerTvMediumImageUrl;
  }
  public function getBannerTvMediumImageUrl()
  {
    return $this->bannerTvMediumImageUrl;
  }
  public function setLargeBrandedBannerImageImapScript(Google_Service_YouTube_LocalizedProperty $largeBrandedBannerImageImapScript)
  {
    $this->largeBrandedBannerImageImapScript = $largeBrandedBannerImageImapScript;
  }
  public function getLargeBrandedBannerImageImapScript()
  {
    return $this->largeBrandedBannerImageImapScript;
  }
  public function setLargeBrandedBannerImageUrl(Google_Service_YouTube_LocalizedProperty $largeBrandedBannerImageUrl)
  {
    $this->largeBrandedBannerImageUrl = $largeBrandedBannerImageUrl;
  }
  public function getLargeBrandedBannerImageUrl()
  {
    return $this->largeBrandedBannerImageUrl;
  }
  public function setSmallBrandedBannerImageImapScript(Google_Service_YouTube_LocalizedProperty $smallBrandedBannerImageImapScript)
  {
    $this->smallBrandedBannerImageImapScript = $smallBrandedBannerImageImapScript;
  }
  public function getSmallBrandedBannerImageImapScript()
  {
    return $this->smallBrandedBannerImageImapScript;
  }
  public function setSmallBrandedBannerImageUrl(Google_Service_YouTube_LocalizedProperty $smallBrandedBannerImageUrl)
  {
    $this->smallBrandedBannerImageUrl = $smallBrandedBannerImageUrl;
  }
  public function getSmallBrandedBannerImageUrl()
  {
    return $this->smallBrandedBannerImageUrl;
  }
  public function setTrackingImageUrl($trackingImageUrl)
  {
    $this->trackingImageUrl = $trackingImageUrl;
  }
  public function getTrackingImageUrl()
  {
    return $this->trackingImageUrl;
  }
  public function setWatchIconImageUrl($watchIconImageUrl)
  {
    $this->watchIconImageUrl = $watchIconImageUrl;
  }
  public function getWatchIconImageUrl()
  {
    return $this->watchIconImageUrl;
  }
}

class Google_Service_YouTube_IngestionInfo extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $backupIngestionAddress;
  public $ingestionAddress;
  public $streamName;


  public function setBackupIngestionAddress($backupIngestionAddress)
  {
    $this->backupIngestionAddress = $backupIngestionAddress;
  }
  public function getBackupIngestionAddress()
  {
    return $this->backupIngestionAddress;
  }
  public function setIngestionAddress($ingestionAddress)
  {
    $this->ingestionAddress = $ingestionAddress;
  }
  public function getIngestionAddress()
  {
    return $this->ingestionAddress;
  }
  public function setStreamName($streamName)
  {
    $this->streamName = $streamName;
  }
  public function getStreamName()
  {
    return $this->streamName;
  }
}

class Google_Service_YouTube_InvideoBranding extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $imageBytes;
  public $imageUrl;
  protected $positionType = 'Google_Service_YouTube_InvideoPosition';
  protected $positionDataType = '';
  public $targetChannelId;
  protected $timingType = 'Google_Service_YouTube_InvideoTiming';
  protected $timingDataType = '';


  public function setImageBytes($imageBytes)
  {
    $this->imageBytes = $imageBytes;
  }
  public function getImageBytes()
  {
    return $this->imageBytes;
  }
  public function setImageUrl($imageUrl)
  {
    $this->imageUrl = $imageUrl;
  }
  public function getImageUrl()
  {
    return $this->imageUrl;
  }
  public function setPosition(Google_Service_YouTube_InvideoPosition $position)
  {
    $this->position = $position;
  }
  public function getPosition()
  {
    return $this->position;
  }
  public function setTargetChannelId($targetChannelId)
  {
    $this->targetChannelId = $targetChannelId;
  }
  public function getTargetChannelId()
  {
    return $this->targetChannelId;
  }
  public function setTiming(Google_Service_YouTube_InvideoTiming $timing)
  {
    $this->timing = $timing;
  }
  public function getTiming()
  {
    return $this->timing;
  }
}

class Google_Service_YouTube_InvideoPosition extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $cornerPosition;
  public $type;


  public function setCornerPosition($cornerPosition)
  {
    $this->cornerPosition = $cornerPosition;
  }
  public function getCornerPosition()
  {
    return $this->cornerPosition;
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

class Google_Service_YouTube_InvideoPromotion extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  protected $defaultTimingType = 'Google_Service_YouTube_InvideoTiming';
  protected $defaultTimingDataType = '';
  protected $itemsType = 'Google_Service_YouTube_PromotedItem';
  protected $itemsDataType = 'array';
  protected $positionType = 'Google_Service_YouTube_InvideoPosition';
  protected $positionDataType = '';
  public $useSmartTiming;


  public function setDefaultTiming(Google_Service_YouTube_InvideoTiming $defaultTiming)
  {
    $this->defaultTiming = $defaultTiming;
  }
  public function getDefaultTiming()
  {
    return $this->defaultTiming;
  }
  public function setItems($items)
  {
    $this->items = $items;
  }
  public function getItems()
  {
    return $this->items;
  }
  public function setPosition(Google_Service_YouTube_InvideoPosition $position)
  {
    $this->position = $position;
  }
  public function getPosition()
  {
    return $this->position;
  }
  public function setUseSmartTiming($useSmartTiming)
  {
    $this->useSmartTiming = $useSmartTiming;
  }
  public function getUseSmartTiming()
  {
    return $this->useSmartTiming;
  }
}

class Google_Service_YouTube_InvideoTiming extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $durationMs;
  public $offsetMs;
  public $type;


  public function setDurationMs($durationMs)
  {
    $this->durationMs = $durationMs;
  }
  public function getDurationMs()
  {
    return $this->durationMs;
  }
  public function setOffsetMs($offsetMs)
  {
    $this->offsetMs = $offsetMs;
  }
  public function getOffsetMs()
  {
    return $this->offsetMs;
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

class Google_Service_YouTube_LanguageTag extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $value;


  public function setValue($value)
  {
    $this->value = $value;
  }
  public function getValue()
  {
    return $this->value;
  }
}

class Google_Service_YouTube_LiveBroadcast extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $contentDetailsType = 'Google_Service_YouTube_LiveBroadcastContentDetails';
  protected $contentDetailsDataType = '';
  public $etag;
  public $id;
  public $kind;
  protected $snippetType = 'Google_Service_YouTube_LiveBroadcastSnippet';
  protected $snippetDataType = '';
  protected $statisticsType = 'Google_Service_YouTube_LiveBroadcastStatistics';
  protected $statisticsDataType = '';
  protected $statusType = 'Google_Service_YouTube_LiveBroadcastStatus';
  protected $statusDataType = '';
  protected $topicDetailsType = 'Google_Service_YouTube_LiveBroadcastTopicDetails';
  protected $topicDetailsDataType = '';


  public function setContentDetails(Google_Service_YouTube_LiveBroadcastContentDetails $contentDetails)
  {
    $this->contentDetails = $contentDetails;
  }
  public function getContentDetails()
  {
    return $this->contentDetails;
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
  public function setSnippet(Google_Service_YouTube_LiveBroadcastSnippet $snippet)
  {
    $this->snippet = $snippet;
  }
  public function getSnippet()
  {
    return $this->snippet;
  }
  public function setStatistics(Google_Service_YouTube_LiveBroadcastStatistics $statistics)
  {
    $this->statistics = $statistics;
  }
  public function getStatistics()
  {
    return $this->statistics;
  }
  public function setStatus(Google_Service_YouTube_LiveBroadcastStatus $status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
  public function setTopicDetails(Google_Service_YouTube_LiveBroadcastTopicDetails $topicDetails)
  {
    $this->topicDetails = $topicDetails;
  }
  public function getTopicDetails()
  {
    return $this->topicDetails;
  }
}

class Google_Service_YouTube_LiveBroadcastContentDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $boundStreamId;
  public $enableClosedCaptions;
  public $enableContentEncryption;
  public $enableDvr;
  public $enableEmbed;
  public $enableLowLatency;
  protected $monitorStreamType = 'Google_Service_YouTube_MonitorStreamInfo';
  protected $monitorStreamDataType = '';
  public $recordFromStart;
  public $startWithSlate;


  public function setBoundStreamId($boundStreamId)
  {
    $this->boundStreamId = $boundStreamId;
  }
  public function getBoundStreamId()
  {
    return $this->boundStreamId;
  }
  public function setEnableClosedCaptions($enableClosedCaptions)
  {
    $this->enableClosedCaptions = $enableClosedCaptions;
  }
  public function getEnableClosedCaptions()
  {
    return $this->enableClosedCaptions;
  }
  public function setEnableContentEncryption($enableContentEncryption)
  {
    $this->enableContentEncryption = $enableContentEncryption;
  }
  public function getEnableContentEncryption()
  {
    return $this->enableContentEncryption;
  }
  public function setEnableDvr($enableDvr)
  {
    $this->enableDvr = $enableDvr;
  }
  public function getEnableDvr()
  {
    return $this->enableDvr;
  }
  public function setEnableEmbed($enableEmbed)
  {
    $this->enableEmbed = $enableEmbed;
  }
  public function getEnableEmbed()
  {
    return $this->enableEmbed;
  }
  public function setEnableLowLatency($enableLowLatency)
  {
    $this->enableLowLatency = $enableLowLatency;
  }
  public function getEnableLowLatency()
  {
    return $this->enableLowLatency;
  }
  public function setMonitorStream(Google_Service_YouTube_MonitorStreamInfo $monitorStream)
  {
    $this->monitorStream = $monitorStream;
  }
  public function getMonitorStream()
  {
    return $this->monitorStream;
  }
  public function setRecordFromStart($recordFromStart)
  {
    $this->recordFromStart = $recordFromStart;
  }
  public function getRecordFromStart()
  {
    return $this->recordFromStart;
  }
  public function setStartWithSlate($startWithSlate)
  {
    $this->startWithSlate = $startWithSlate;
  }
  public function getStartWithSlate()
  {
    return $this->startWithSlate;
  }
}

class Google_Service_YouTube_LiveBroadcastListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $eventId;
  protected $itemsType = 'Google_Service_YouTube_LiveBroadcast';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;
  protected $pageInfoType = 'Google_Service_YouTube_PageInfo';
  protected $pageInfoDataType = '';
  public $prevPageToken;
  protected $tokenPaginationType = 'Google_Service_YouTube_TokenPagination';
  protected $tokenPaginationDataType = '';
  public $visitorId;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setEventId($eventId)
  {
    $this->eventId = $eventId;
  }
  public function getEventId()
  {
    return $this->eventId;
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
  public function setPageInfo(Google_Service_YouTube_PageInfo $pageInfo)
  {
    $this->pageInfo = $pageInfo;
  }
  public function getPageInfo()
  {
    return $this->pageInfo;
  }
  public function setPrevPageToken($prevPageToken)
  {
    $this->prevPageToken = $prevPageToken;
  }
  public function getPrevPageToken()
  {
    return $this->prevPageToken;
  }
  public function setTokenPagination(Google_Service_YouTube_TokenPagination $tokenPagination)
  {
    $this->tokenPagination = $tokenPagination;
  }
  public function getTokenPagination()
  {
    return $this->tokenPagination;
  }
  public function setVisitorId($visitorId)
  {
    $this->visitorId = $visitorId;
  }
  public function getVisitorId()
  {
    return $this->visitorId;
  }
}

class Google_Service_YouTube_LiveBroadcastSnippet extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $actualEndTime;
  public $actualStartTime;
  public $channelId;
  public $description;
  public $isDefaultBroadcast;
  public $liveChatId;
  public $publishedAt;
  public $scheduledEndTime;
  public $scheduledStartTime;
  protected $thumbnailsType = 'Google_Service_YouTube_ThumbnailDetails';
  protected $thumbnailsDataType = '';
  public $title;


  public function setActualEndTime($actualEndTime)
  {
    $this->actualEndTime = $actualEndTime;
  }
  public function getActualEndTime()
  {
    return $this->actualEndTime;
  }
  public function setActualStartTime($actualStartTime)
  {
    $this->actualStartTime = $actualStartTime;
  }
  public function getActualStartTime()
  {
    return $this->actualStartTime;
  }
  public function setChannelId($channelId)
  {
    $this->channelId = $channelId;
  }
  public function getChannelId()
  {
    return $this->channelId;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setIsDefaultBroadcast($isDefaultBroadcast)
  {
    $this->isDefaultBroadcast = $isDefaultBroadcast;
  }
  public function getIsDefaultBroadcast()
  {
    return $this->isDefaultBroadcast;
  }
  public function setLiveChatId($liveChatId)
  {
    $this->liveChatId = $liveChatId;
  }
  public function getLiveChatId()
  {
    return $this->liveChatId;
  }
  public function setPublishedAt($publishedAt)
  {
    $this->publishedAt = $publishedAt;
  }
  public function getPublishedAt()
  {
    return $this->publishedAt;
  }
  public function setScheduledEndTime($scheduledEndTime)
  {
    $this->scheduledEndTime = $scheduledEndTime;
  }
  public function getScheduledEndTime()
  {
    return $this->scheduledEndTime;
  }
  public function setScheduledStartTime($scheduledStartTime)
  {
    $this->scheduledStartTime = $scheduledStartTime;
  }
  public function getScheduledStartTime()
  {
    return $this->scheduledStartTime;
  }
  public function setThumbnails(Google_Service_YouTube_ThumbnailDetails $thumbnails)
  {
    $this->thumbnails = $thumbnails;
  }
  public function getThumbnails()
  {
    return $this->thumbnails;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
}

class Google_Service_YouTube_LiveBroadcastStatistics extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $concurrentViewers;
  public $totalChatCount;


  public function setConcurrentViewers($concurrentViewers)
  {
    $this->concurrentViewers = $concurrentViewers;
  }
  public function getConcurrentViewers()
  {
    return $this->concurrentViewers;
  }
  public function setTotalChatCount($totalChatCount)
  {
    $this->totalChatCount = $totalChatCount;
  }
  public function getTotalChatCount()
  {
    return $this->totalChatCount;
  }
}

class Google_Service_YouTube_LiveBroadcastStatus extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $lifeCycleStatus;
  public $liveBroadcastPriority;
  public $privacyStatus;
  public $recordingStatus;


  public function setLifeCycleStatus($lifeCycleStatus)
  {
    $this->lifeCycleStatus = $lifeCycleStatus;
  }
  public function getLifeCycleStatus()
  {
    return $this->lifeCycleStatus;
  }
  public function setLiveBroadcastPriority($liveBroadcastPriority)
  {
    $this->liveBroadcastPriority = $liveBroadcastPriority;
  }
  public function getLiveBroadcastPriority()
  {
    return $this->liveBroadcastPriority;
  }
  public function setPrivacyStatus($privacyStatus)
  {
    $this->privacyStatus = $privacyStatus;
  }
  public function getPrivacyStatus()
  {
    return $this->privacyStatus;
  }
  public function setRecordingStatus($recordingStatus)
  {
    $this->recordingStatus = $recordingStatus;
  }
  public function getRecordingStatus()
  {
    return $this->recordingStatus;
  }
}

class Google_Service_YouTube_LiveBroadcastTopic extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $snippetType = 'Google_Service_YouTube_LiveBroadcastTopicSnippet';
  protected $snippetDataType = '';
  public $type;
  public $unmatched;


  public function setSnippet(Google_Service_YouTube_LiveBroadcastTopicSnippet $snippet)
  {
    $this->snippet = $snippet;
  }
  public function getSnippet()
  {
    return $this->snippet;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
  public function setUnmatched($unmatched)
  {
    $this->unmatched = $unmatched;
  }
  public function getUnmatched()
  {
    return $this->unmatched;
  }
}

class Google_Service_YouTube_LiveBroadcastTopicDetails extends Google_Collection
{
  protected $collection_key = 'topics';
  protected $internal_gapi_mappings = array(
  );
  protected $topicsType = 'Google_Service_YouTube_LiveBroadcastTopic';
  protected $topicsDataType = 'array';


  public function setTopics($topics)
  {
    $this->topics = $topics;
  }
  public function getTopics()
  {
    return $this->topics;
  }
}

class Google_Service_YouTube_LiveBroadcastTopicSnippet extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $name;
  public $releaseDate;


  public function setName($name)
  {
    $this->name = $name;
  }
  public function getName()
  {
    return $this->name;
  }
  public function setReleaseDate($releaseDate)
  {
    $this->releaseDate = $releaseDate;
  }
  public function getReleaseDate()
  {
    return $this->releaseDate;
  }
}

class Google_Service_YouTube_LiveStream extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $cdnType = 'Google_Service_YouTube_CdnSettings';
  protected $cdnDataType = '';
  protected $contentDetailsType = 'Google_Service_YouTube_LiveStreamContentDetails';
  protected $contentDetailsDataType = '';
  public $etag;
  public $id;
  public $kind;
  protected $snippetType = 'Google_Service_YouTube_LiveStreamSnippet';
  protected $snippetDataType = '';
  protected $statusType = 'Google_Service_YouTube_LiveStreamStatus';
  protected $statusDataType = '';


  public function setCdn(Google_Service_YouTube_CdnSettings $cdn)
  {
    $this->cdn = $cdn;
  }
  public function getCdn()
  {
    return $this->cdn;
  }
  public function setContentDetails(Google_Service_YouTube_LiveStreamContentDetails $contentDetails)
  {
    $this->contentDetails = $contentDetails;
  }
  public function getContentDetails()
  {
    return $this->contentDetails;
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
  public function setSnippet(Google_Service_YouTube_LiveStreamSnippet $snippet)
  {
    $this->snippet = $snippet;
  }
  public function getSnippet()
  {
    return $this->snippet;
  }
  public function setStatus(Google_Service_YouTube_LiveStreamStatus $status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
}

class Google_Service_YouTube_LiveStreamConfigurationIssue extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $description;
  public $reason;
  public $severity;
  public $type;


  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setReason($reason)
  {
    $this->reason = $reason;
  }
  public function getReason()
  {
    return $this->reason;
  }
  public function setSeverity($severity)
  {
    $this->severity = $severity;
  }
  public function getSeverity()
  {
    return $this->severity;
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

class Google_Service_YouTube_LiveStreamContentDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $closedCaptionsIngestionUrl;
  public $isReusable;


  public function setClosedCaptionsIngestionUrl($closedCaptionsIngestionUrl)
  {
    $this->closedCaptionsIngestionUrl = $closedCaptionsIngestionUrl;
  }
  public function getClosedCaptionsIngestionUrl()
  {
    return $this->closedCaptionsIngestionUrl;
  }
  public function setIsReusable($isReusable)
  {
    $this->isReusable = $isReusable;
  }
  public function getIsReusable()
  {
    return $this->isReusable;
  }
}

class Google_Service_YouTube_LiveStreamHealthStatus extends Google_Collection
{
  protected $collection_key = 'configurationIssues';
  protected $internal_gapi_mappings = array(
  );
  protected $configurationIssuesType = 'Google_Service_YouTube_LiveStreamConfigurationIssue';
  protected $configurationIssuesDataType = 'array';
  public $lastUpdateTimeSeconds;
  public $status;


  public function setConfigurationIssues($configurationIssues)
  {
    $this->configurationIssues = $configurationIssues;
  }
  public function getConfigurationIssues()
  {
    return $this->configurationIssues;
  }
  public function setLastUpdateTimeSeconds($lastUpdateTimeSeconds)
  {
    $this->lastUpdateTimeSeconds = $lastUpdateTimeSeconds;
  }
  public function getLastUpdateTimeSeconds()
  {
    return $this->lastUpdateTimeSeconds;
  }
  public function setStatus($status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
}

class Google_Service_YouTube_LiveStreamListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $eventId;
  protected $itemsType = 'Google_Service_YouTube_LiveStream';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;
  protected $pageInfoType = 'Google_Service_YouTube_PageInfo';
  protected $pageInfoDataType = '';
  public $prevPageToken;
  protected $tokenPaginationType = 'Google_Service_YouTube_TokenPagination';
  protected $tokenPaginationDataType = '';
  public $visitorId;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setEventId($eventId)
  {
    $this->eventId = $eventId;
  }
  public function getEventId()
  {
    return $this->eventId;
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
  public function setPageInfo(Google_Service_YouTube_PageInfo $pageInfo)
  {
    $this->pageInfo = $pageInfo;
  }
  public function getPageInfo()
  {
    return $this->pageInfo;
  }
  public function setPrevPageToken($prevPageToken)
  {
    $this->prevPageToken = $prevPageToken;
  }
  public function getPrevPageToken()
  {
    return $this->prevPageToken;
  }
  public function setTokenPagination(Google_Service_YouTube_TokenPagination $tokenPagination)
  {
    $this->tokenPagination = $tokenPagination;
  }
  public function getTokenPagination()
  {
    return $this->tokenPagination;
  }
  public function setVisitorId($visitorId)
  {
    $this->visitorId = $visitorId;
  }
  public function getVisitorId()
  {
    return $this->visitorId;
  }
}

class Google_Service_YouTube_LiveStreamSnippet extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $channelId;
  public $description;
  public $isDefaultStream;
  public $publishedAt;
  public $title;


  public function setChannelId($channelId)
  {
    $this->channelId = $channelId;
  }
  public function getChannelId()
  {
    return $this->channelId;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setIsDefaultStream($isDefaultStream)
  {
    $this->isDefaultStream = $isDefaultStream;
  }
  public function getIsDefaultStream()
  {
    return $this->isDefaultStream;
  }
  public function setPublishedAt($publishedAt)
  {
    $this->publishedAt = $publishedAt;
  }
  public function getPublishedAt()
  {
    return $this->publishedAt;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
}

class Google_Service_YouTube_LiveStreamStatus extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $healthStatusType = 'Google_Service_YouTube_LiveStreamHealthStatus';
  protected $healthStatusDataType = '';
  public $streamStatus;


  public function setHealthStatus(Google_Service_YouTube_LiveStreamHealthStatus $healthStatus)
  {
    $this->healthStatus = $healthStatus;
  }
  public function getHealthStatus()
  {
    return $this->healthStatus;
  }
  public function setStreamStatus($streamStatus)
  {
    $this->streamStatus = $streamStatus;
  }
  public function getStreamStatus()
  {
    return $this->streamStatus;
  }
}

class Google_Service_YouTube_LocalizedProperty extends Google_Collection
{
  protected $collection_key = 'localized';
  protected $internal_gapi_mappings = array(
  );
  public $default;
  protected $defaultLanguageType = 'Google_Service_YouTube_LanguageTag';
  protected $defaultLanguageDataType = '';
  protected $localizedType = 'Google_Service_YouTube_LocalizedString';
  protected $localizedDataType = 'array';


  public function setDefault($default)
  {
    $this->default = $default;
  }
  public function getDefault()
  {
    return $this->default;
  }
  public function setDefaultLanguage(Google_Service_YouTube_LanguageTag $defaultLanguage)
  {
    $this->defaultLanguage = $defaultLanguage;
  }
  public function getDefaultLanguage()
  {
    return $this->defaultLanguage;
  }
  public function setLocalized($localized)
  {
    $this->localized = $localized;
  }
  public function getLocalized()
  {
    return $this->localized;
  }
}

class Google_Service_YouTube_LocalizedString extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $language;
  public $value;


  public function setLanguage($language)
  {
    $this->language = $language;
  }
  public function getLanguage()
  {
    return $this->language;
  }
  public function setValue($value)
  {
    $this->value = $value;
  }
  public function getValue()
  {
    return $this->value;
  }
}

class Google_Service_YouTube_MonitorStreamInfo extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $broadcastStreamDelayMs;
  public $embedHtml;
  public $enableMonitorStream;


  public function setBroadcastStreamDelayMs($broadcastStreamDelayMs)
  {
    $this->broadcastStreamDelayMs = $broadcastStreamDelayMs;
  }
  public function getBroadcastStreamDelayMs()
  {
    return $this->broadcastStreamDelayMs;
  }
  public function setEmbedHtml($embedHtml)
  {
    $this->embedHtml = $embedHtml;
  }
  public function getEmbedHtml()
  {
    return $this->embedHtml;
  }
  public function setEnableMonitorStream($enableMonitorStream)
  {
    $this->enableMonitorStream = $enableMonitorStream;
  }
  public function getEnableMonitorStream()
  {
    return $this->enableMonitorStream;
  }
}

class Google_Service_YouTube_PageInfo extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $resultsPerPage;
  public $totalResults;


  public function setResultsPerPage($resultsPerPage)
  {
    $this->resultsPerPage = $resultsPerPage;
  }
  public function getResultsPerPage()
  {
    return $this->resultsPerPage;
  }
  public function setTotalResults($totalResults)
  {
    $this->totalResults = $totalResults;
  }
  public function getTotalResults()
  {
    return $this->totalResults;
  }
}

class Google_Service_YouTube_Playlist extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $contentDetailsType = 'Google_Service_YouTube_PlaylistContentDetails';
  protected $contentDetailsDataType = '';
  public $etag;
  public $id;
  public $kind;
  protected $localizationsType = 'Google_Service_YouTube_PlaylistLocalization';
  protected $localizationsDataType = 'map';
  protected $playerType = 'Google_Service_YouTube_PlaylistPlayer';
  protected $playerDataType = '';
  protected $snippetType = 'Google_Service_YouTube_PlaylistSnippet';
  protected $snippetDataType = '';
  protected $statusType = 'Google_Service_YouTube_PlaylistStatus';
  protected $statusDataType = '';


  public function setContentDetails(Google_Service_YouTube_PlaylistContentDetails $contentDetails)
  {
    $this->contentDetails = $contentDetails;
  }
  public function getContentDetails()
  {
    return $this->contentDetails;
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
  public function setLocalizations($localizations)
  {
    $this->localizations = $localizations;
  }
  public function getLocalizations()
  {
    return $this->localizations;
  }
  public function setPlayer(Google_Service_YouTube_PlaylistPlayer $player)
  {
    $this->player = $player;
  }
  public function getPlayer()
  {
    return $this->player;
  }
  public function setSnippet(Google_Service_YouTube_PlaylistSnippet $snippet)
  {
    $this->snippet = $snippet;
  }
  public function getSnippet()
  {
    return $this->snippet;
  }
  public function setStatus(Google_Service_YouTube_PlaylistStatus $status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
}

class Google_Service_YouTube_PlaylistContentDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $itemCount;


  public function setItemCount($itemCount)
  {
    $this->itemCount = $itemCount;
  }
  public function getItemCount()
  {
    return $this->itemCount;
  }
}

class Google_Service_YouTube_PlaylistItem extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $contentDetailsType = 'Google_Service_YouTube_PlaylistItemContentDetails';
  protected $contentDetailsDataType = '';
  public $etag;
  public $id;
  public $kind;
  protected $snippetType = 'Google_Service_YouTube_PlaylistItemSnippet';
  protected $snippetDataType = '';
  protected $statusType = 'Google_Service_YouTube_PlaylistItemStatus';
  protected $statusDataType = '';


  public function setContentDetails(Google_Service_YouTube_PlaylistItemContentDetails $contentDetails)
  {
    $this->contentDetails = $contentDetails;
  }
  public function getContentDetails()
  {
    return $this->contentDetails;
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
  public function setSnippet(Google_Service_YouTube_PlaylistItemSnippet $snippet)
  {
    $this->snippet = $snippet;
  }
  public function getSnippet()
  {
    return $this->snippet;
  }
  public function setStatus(Google_Service_YouTube_PlaylistItemStatus $status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
}

class Google_Service_YouTube_PlaylistItemContentDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $endAt;
  public $note;
  public $startAt;
  public $videoId;


  public function setEndAt($endAt)
  {
    $this->endAt = $endAt;
  }
  public function getEndAt()
  {
    return $this->endAt;
  }
  public function setNote($note)
  {
    $this->note = $note;
  }
  public function getNote()
  {
    return $this->note;
  }
  public function setStartAt($startAt)
  {
    $this->startAt = $startAt;
  }
  public function getStartAt()
  {
    return $this->startAt;
  }
  public function setVideoId($videoId)
  {
    $this->videoId = $videoId;
  }
  public function getVideoId()
  {
    return $this->videoId;
  }
}

class Google_Service_YouTube_PlaylistItemListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $eventId;
  protected $itemsType = 'Google_Service_YouTube_PlaylistItem';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;
  protected $pageInfoType = 'Google_Service_YouTube_PageInfo';
  protected $pageInfoDataType = '';
  public $prevPageToken;
  protected $tokenPaginationType = 'Google_Service_YouTube_TokenPagination';
  protected $tokenPaginationDataType = '';
  public $visitorId;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setEventId($eventId)
  {
    $this->eventId = $eventId;
  }
  public function getEventId()
  {
    return $this->eventId;
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
  public function setPageInfo(Google_Service_YouTube_PageInfo $pageInfo)
  {
    $this->pageInfo = $pageInfo;
  }
  public function getPageInfo()
  {
    return $this->pageInfo;
  }
  public function setPrevPageToken($prevPageToken)
  {
    $this->prevPageToken = $prevPageToken;
  }
  public function getPrevPageToken()
  {
    return $this->prevPageToken;
  }
  public function setTokenPagination(Google_Service_YouTube_TokenPagination $tokenPagination)
  {
    $this->tokenPagination = $tokenPagination;
  }
  public function getTokenPagination()
  {
    return $this->tokenPagination;
  }
  public function setVisitorId($visitorId)
  {
    $this->visitorId = $visitorId;
  }
  public function getVisitorId()
  {
    return $this->visitorId;
  }
}

class Google_Service_YouTube_PlaylistItemSnippet extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $channelId;
  public $channelTitle;
  public $description;
  public $playlistId;
  public $position;
  public $publishedAt;
  protected $resourceIdType = 'Google_Service_YouTube_ResourceId';
  protected $resourceIdDataType = '';
  protected $thumbnailsType = 'Google_Service_YouTube_ThumbnailDetails';
  protected $thumbnailsDataType = '';
  public $title;


  public function setChannelId($channelId)
  {
    $this->channelId = $channelId;
  }
  public function getChannelId()
  {
    return $this->channelId;
  }
  public function setChannelTitle($channelTitle)
  {
    $this->channelTitle = $channelTitle;
  }
  public function getChannelTitle()
  {
    return $this->channelTitle;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setPlaylistId($playlistId)
  {
    $this->playlistId = $playlistId;
  }
  public function getPlaylistId()
  {
    return $this->playlistId;
  }
  public function setPosition($position)
  {
    $this->position = $position;
  }
  public function getPosition()
  {
    return $this->position;
  }
  public function setPublishedAt($publishedAt)
  {
    $this->publishedAt = $publishedAt;
  }
  public function getPublishedAt()
  {
    return $this->publishedAt;
  }
  public function setResourceId(Google_Service_YouTube_ResourceId $resourceId)
  {
    $this->resourceId = $resourceId;
  }
  public function getResourceId()
  {
    return $this->resourceId;
  }
  public function setThumbnails(Google_Service_YouTube_ThumbnailDetails $thumbnails)
  {
    $this->thumbnails = $thumbnails;
  }
  public function getThumbnails()
  {
    return $this->thumbnails;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
}

class Google_Service_YouTube_PlaylistItemStatus extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $privacyStatus;


  public function setPrivacyStatus($privacyStatus)
  {
    $this->privacyStatus = $privacyStatus;
  }
  public function getPrivacyStatus()
  {
    return $this->privacyStatus;
  }
}

class Google_Service_YouTube_PlaylistListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $eventId;
  protected $itemsType = 'Google_Service_YouTube_Playlist';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;
  protected $pageInfoType = 'Google_Service_YouTube_PageInfo';
  protected $pageInfoDataType = '';
  public $prevPageToken;
  protected $tokenPaginationType = 'Google_Service_YouTube_TokenPagination';
  protected $tokenPaginationDataType = '';
  public $visitorId;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setEventId($eventId)
  {
    $this->eventId = $eventId;
  }
  public function getEventId()
  {
    return $this->eventId;
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
  public function setPageInfo(Google_Service_YouTube_PageInfo $pageInfo)
  {
    $this->pageInfo = $pageInfo;
  }
  public function getPageInfo()
  {
    return $this->pageInfo;
  }
  public function setPrevPageToken($prevPageToken)
  {
    $this->prevPageToken = $prevPageToken;
  }
  public function getPrevPageToken()
  {
    return $this->prevPageToken;
  }
  public function setTokenPagination(Google_Service_YouTube_TokenPagination $tokenPagination)
  {
    $this->tokenPagination = $tokenPagination;
  }
  public function getTokenPagination()
  {
    return $this->tokenPagination;
  }
  public function setVisitorId($visitorId)
  {
    $this->visitorId = $visitorId;
  }
  public function getVisitorId()
  {
    return $this->visitorId;
  }
}

class Google_Service_YouTube_PlaylistLocalization extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $description;
  public $title;


  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
}

class Google_Service_YouTube_PlaylistLocalizations extends Google_Model
{
}

class Google_Service_YouTube_PlaylistPlayer extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $embedHtml;


  public function setEmbedHtml($embedHtml)
  {
    $this->embedHtml = $embedHtml;
  }
  public function getEmbedHtml()
  {
    return $this->embedHtml;
  }
}

class Google_Service_YouTube_PlaylistSnippet extends Google_Collection
{
  protected $collection_key = 'tags';
  protected $internal_gapi_mappings = array(
  );
  public $channelId;
  public $channelTitle;
  public $defaultLanguage;
  public $description;
  protected $localizedType = 'Google_Service_YouTube_PlaylistLocalization';
  protected $localizedDataType = '';
  public $publishedAt;
  public $tags;
  protected $thumbnailsType = 'Google_Service_YouTube_ThumbnailDetails';
  protected $thumbnailsDataType = '';
  public $title;


  public function setChannelId($channelId)
  {
    $this->channelId = $channelId;
  }
  public function getChannelId()
  {
    return $this->channelId;
  }
  public function setChannelTitle($channelTitle)
  {
    $this->channelTitle = $channelTitle;
  }
  public function getChannelTitle()
  {
    return $this->channelTitle;
  }
  public function setDefaultLanguage($defaultLanguage)
  {
    $this->defaultLanguage = $defaultLanguage;
  }
  public function getDefaultLanguage()
  {
    return $this->defaultLanguage;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setLocalized(Google_Service_YouTube_PlaylistLocalization $localized)
  {
    $this->localized = $localized;
  }
  public function getLocalized()
  {
    return $this->localized;
  }
  public function setPublishedAt($publishedAt)
  {
    $this->publishedAt = $publishedAt;
  }
  public function getPublishedAt()
  {
    return $this->publishedAt;
  }
  public function setTags($tags)
  {
    $this->tags = $tags;
  }
  public function getTags()
  {
    return $this->tags;
  }
  public function setThumbnails(Google_Service_YouTube_ThumbnailDetails $thumbnails)
  {
    $this->thumbnails = $thumbnails;
  }
  public function getThumbnails()
  {
    return $this->thumbnails;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
}

class Google_Service_YouTube_PlaylistStatus extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $privacyStatus;


  public function setPrivacyStatus($privacyStatus)
  {
    $this->privacyStatus = $privacyStatus;
  }
  public function getPrivacyStatus()
  {
    return $this->privacyStatus;
  }
}

class Google_Service_YouTube_PromotedItem extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $customMessage;
  protected $idType = 'Google_Service_YouTube_PromotedItemId';
  protected $idDataType = '';
  public $promotedByContentOwner;
  protected $timingType = 'Google_Service_YouTube_InvideoTiming';
  protected $timingDataType = '';


  public function setCustomMessage($customMessage)
  {
    $this->customMessage = $customMessage;
  }
  public function getCustomMessage()
  {
    return $this->customMessage;
  }
  public function setId(Google_Service_YouTube_PromotedItemId $id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setPromotedByContentOwner($promotedByContentOwner)
  {
    $this->promotedByContentOwner = $promotedByContentOwner;
  }
  public function getPromotedByContentOwner()
  {
    return $this->promotedByContentOwner;
  }
  public function setTiming(Google_Service_YouTube_InvideoTiming $timing)
  {
    $this->timing = $timing;
  }
  public function getTiming()
  {
    return $this->timing;
  }
}

class Google_Service_YouTube_PromotedItemId extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $recentlyUploadedBy;
  public $type;
  public $videoId;
  public $websiteUrl;


  public function setRecentlyUploadedBy($recentlyUploadedBy)
  {
    $this->recentlyUploadedBy = $recentlyUploadedBy;
  }
  public function getRecentlyUploadedBy()
  {
    return $this->recentlyUploadedBy;
  }
  public function setType($type)
  {
    $this->type = $type;
  }
  public function getType()
  {
    return $this->type;
  }
  public function setVideoId($videoId)
  {
    $this->videoId = $videoId;
  }
  public function getVideoId()
  {
    return $this->videoId;
  }
  public function setWebsiteUrl($websiteUrl)
  {
    $this->websiteUrl = $websiteUrl;
  }
  public function getWebsiteUrl()
  {
    return $this->websiteUrl;
  }
}

class Google_Service_YouTube_PropertyValue extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $property;
  public $value;


  public function setProperty($property)
  {
    $this->property = $property;
  }
  public function getProperty()
  {
    return $this->property;
  }
  public function setValue($value)
  {
    $this->value = $value;
  }
  public function getValue()
  {
    return $this->value;
  }
}

class Google_Service_YouTube_ResourceId extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $channelId;
  public $kind;
  public $playlistId;
  public $videoId;


  public function setChannelId($channelId)
  {
    $this->channelId = $channelId;
  }
  public function getChannelId()
  {
    return $this->channelId;
  }
  public function setKind($kind)
  {
    $this->kind = $kind;
  }
  public function getKind()
  {
    return $this->kind;
  }
  public function setPlaylistId($playlistId)
  {
    $this->playlistId = $playlistId;
  }
  public function getPlaylistId()
  {
    return $this->playlistId;
  }
  public function setVideoId($videoId)
  {
    $this->videoId = $videoId;
  }
  public function getVideoId()
  {
    return $this->videoId;
  }
}

class Google_Service_YouTube_SearchListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $eventId;
  protected $itemsType = 'Google_Service_YouTube_SearchResult';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;
  protected $pageInfoType = 'Google_Service_YouTube_PageInfo';
  protected $pageInfoDataType = '';
  public $prevPageToken;
  protected $tokenPaginationType = 'Google_Service_YouTube_TokenPagination';
  protected $tokenPaginationDataType = '';
  public $visitorId;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setEventId($eventId)
  {
    $this->eventId = $eventId;
  }
  public function getEventId()
  {
    return $this->eventId;
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
  public function setPageInfo(Google_Service_YouTube_PageInfo $pageInfo)
  {
    $this->pageInfo = $pageInfo;
  }
  public function getPageInfo()
  {
    return $this->pageInfo;
  }
  public function setPrevPageToken($prevPageToken)
  {
    $this->prevPageToken = $prevPageToken;
  }
  public function getPrevPageToken()
  {
    return $this->prevPageToken;
  }
  public function setTokenPagination(Google_Service_YouTube_TokenPagination $tokenPagination)
  {
    $this->tokenPagination = $tokenPagination;
  }
  public function getTokenPagination()
  {
    return $this->tokenPagination;
  }
  public function setVisitorId($visitorId)
  {
    $this->visitorId = $visitorId;
  }
  public function getVisitorId()
  {
    return $this->visitorId;
  }
}

class Google_Service_YouTube_SearchResult extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  protected $idType = 'Google_Service_YouTube_ResourceId';
  protected $idDataType = '';
  public $kind;
  protected $snippetType = 'Google_Service_YouTube_SearchResultSnippet';
  protected $snippetDataType = '';


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setId(Google_Service_YouTube_ResourceId $id)
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
  public function setSnippet(Google_Service_YouTube_SearchResultSnippet $snippet)
  {
    $this->snippet = $snippet;
  }
  public function getSnippet()
  {
    return $this->snippet;
  }
}

class Google_Service_YouTube_SearchResultSnippet extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $channelId;
  public $channelTitle;
  public $description;
  public $liveBroadcastContent;
  public $publishedAt;
  protected $thumbnailsType = 'Google_Service_YouTube_ThumbnailDetails';
  protected $thumbnailsDataType = '';
  public $title;


  public function setChannelId($channelId)
  {
    $this->channelId = $channelId;
  }
  public function getChannelId()
  {
    return $this->channelId;
  }
  public function setChannelTitle($channelTitle)
  {
    $this->channelTitle = $channelTitle;
  }
  public function getChannelTitle()
  {
    return $this->channelTitle;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setLiveBroadcastContent($liveBroadcastContent)
  {
    $this->liveBroadcastContent = $liveBroadcastContent;
  }
  public function getLiveBroadcastContent()
  {
    return $this->liveBroadcastContent;
  }
  public function setPublishedAt($publishedAt)
  {
    $this->publishedAt = $publishedAt;
  }
  public function getPublishedAt()
  {
    return $this->publishedAt;
  }
  public function setThumbnails(Google_Service_YouTube_ThumbnailDetails $thumbnails)
  {
    $this->thumbnails = $thumbnails;
  }
  public function getThumbnails()
  {
    return $this->thumbnails;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
}

class Google_Service_YouTube_Subscription extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $contentDetailsType = 'Google_Service_YouTube_SubscriptionContentDetails';
  protected $contentDetailsDataType = '';
  public $etag;
  public $id;
  public $kind;
  protected $snippetType = 'Google_Service_YouTube_SubscriptionSnippet';
  protected $snippetDataType = '';
  protected $subscriberSnippetType = 'Google_Service_YouTube_SubscriptionSubscriberSnippet';
  protected $subscriberSnippetDataType = '';


  public function setContentDetails(Google_Service_YouTube_SubscriptionContentDetails $contentDetails)
  {
    $this->contentDetails = $contentDetails;
  }
  public function getContentDetails()
  {
    return $this->contentDetails;
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
  public function setSnippet(Google_Service_YouTube_SubscriptionSnippet $snippet)
  {
    $this->snippet = $snippet;
  }
  public function getSnippet()
  {
    return $this->snippet;
  }
  public function setSubscriberSnippet(Google_Service_YouTube_SubscriptionSubscriberSnippet $subscriberSnippet)
  {
    $this->subscriberSnippet = $subscriberSnippet;
  }
  public function getSubscriberSnippet()
  {
    return $this->subscriberSnippet;
  }
}

class Google_Service_YouTube_SubscriptionContentDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $activityType;
  public $newItemCount;
  public $totalItemCount;


  public function setActivityType($activityType)
  {
    $this->activityType = $activityType;
  }
  public function getActivityType()
  {
    return $this->activityType;
  }
  public function setNewItemCount($newItemCount)
  {
    $this->newItemCount = $newItemCount;
  }
  public function getNewItemCount()
  {
    return $this->newItemCount;
  }
  public function setTotalItemCount($totalItemCount)
  {
    $this->totalItemCount = $totalItemCount;
  }
  public function getTotalItemCount()
  {
    return $this->totalItemCount;
  }
}

class Google_Service_YouTube_SubscriptionListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $eventId;
  protected $itemsType = 'Google_Service_YouTube_Subscription';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;
  protected $pageInfoType = 'Google_Service_YouTube_PageInfo';
  protected $pageInfoDataType = '';
  public $prevPageToken;
  protected $tokenPaginationType = 'Google_Service_YouTube_TokenPagination';
  protected $tokenPaginationDataType = '';
  public $visitorId;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setEventId($eventId)
  {
    $this->eventId = $eventId;
  }
  public function getEventId()
  {
    return $this->eventId;
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
  public function setPageInfo(Google_Service_YouTube_PageInfo $pageInfo)
  {
    $this->pageInfo = $pageInfo;
  }
  public function getPageInfo()
  {
    return $this->pageInfo;
  }
  public function setPrevPageToken($prevPageToken)
  {
    $this->prevPageToken = $prevPageToken;
  }
  public function getPrevPageToken()
  {
    return $this->prevPageToken;
  }
  public function setTokenPagination(Google_Service_YouTube_TokenPagination $tokenPagination)
  {
    $this->tokenPagination = $tokenPagination;
  }
  public function getTokenPagination()
  {
    return $this->tokenPagination;
  }
  public function setVisitorId($visitorId)
  {
    $this->visitorId = $visitorId;
  }
  public function getVisitorId()
  {
    return $this->visitorId;
  }
}

class Google_Service_YouTube_SubscriptionSnippet extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $channelId;
  public $channelTitle;
  public $description;
  public $publishedAt;
  protected $resourceIdType = 'Google_Service_YouTube_ResourceId';
  protected $resourceIdDataType = '';
  protected $thumbnailsType = 'Google_Service_YouTube_ThumbnailDetails';
  protected $thumbnailsDataType = '';
  public $title;


  public function setChannelId($channelId)
  {
    $this->channelId = $channelId;
  }
  public function getChannelId()
  {
    return $this->channelId;
  }
  public function setChannelTitle($channelTitle)
  {
    $this->channelTitle = $channelTitle;
  }
  public function getChannelTitle()
  {
    return $this->channelTitle;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setPublishedAt($publishedAt)
  {
    $this->publishedAt = $publishedAt;
  }
  public function getPublishedAt()
  {
    return $this->publishedAt;
  }
  public function setResourceId(Google_Service_YouTube_ResourceId $resourceId)
  {
    $this->resourceId = $resourceId;
  }
  public function getResourceId()
  {
    return $this->resourceId;
  }
  public function setThumbnails(Google_Service_YouTube_ThumbnailDetails $thumbnails)
  {
    $this->thumbnails = $thumbnails;
  }
  public function getThumbnails()
  {
    return $this->thumbnails;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
}

class Google_Service_YouTube_SubscriptionSubscriberSnippet extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $channelId;
  public $description;
  protected $thumbnailsType = 'Google_Service_YouTube_ThumbnailDetails';
  protected $thumbnailsDataType = '';
  public $title;


  public function setChannelId($channelId)
  {
    $this->channelId = $channelId;
  }
  public function getChannelId()
  {
    return $this->channelId;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setThumbnails(Google_Service_YouTube_ThumbnailDetails $thumbnails)
  {
    $this->thumbnails = $thumbnails;
  }
  public function getThumbnails()
  {
    return $this->thumbnails;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
}

class Google_Service_YouTube_Thumbnail extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $height;
  public $url;
  public $width;


  public function setHeight($height)
  {
    $this->height = $height;
  }
  public function getHeight()
  {
    return $this->height;
  }
  public function setUrl($url)
  {
    $this->url = $url;
  }
  public function getUrl()
  {
    return $this->url;
  }
  public function setWidth($width)
  {
    $this->width = $width;
  }
  public function getWidth()
  {
    return $this->width;
  }
}

class Google_Service_YouTube_ThumbnailDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $defaultType = 'Google_Service_YouTube_Thumbnail';
  protected $defaultDataType = '';
  protected $highType = 'Google_Service_YouTube_Thumbnail';
  protected $highDataType = '';
  protected $maxresType = 'Google_Service_YouTube_Thumbnail';
  protected $maxresDataType = '';
  protected $mediumType = 'Google_Service_YouTube_Thumbnail';
  protected $mediumDataType = '';
  protected $standardType = 'Google_Service_YouTube_Thumbnail';
  protected $standardDataType = '';


  public function setDefault(Google_Service_YouTube_Thumbnail $default)
  {
    $this->default = $default;
  }
  public function getDefault()
  {
    return $this->default;
  }
  public function setHigh(Google_Service_YouTube_Thumbnail $high)
  {
    $this->high = $high;
  }
  public function getHigh()
  {
    return $this->high;
  }
  public function setMaxres(Google_Service_YouTube_Thumbnail $maxres)
  {
    $this->maxres = $maxres;
  }
  public function getMaxres()
  {
    return $this->maxres;
  }
  public function setMedium(Google_Service_YouTube_Thumbnail $medium)
  {
    $this->medium = $medium;
  }
  public function getMedium()
  {
    return $this->medium;
  }
  public function setStandard(Google_Service_YouTube_Thumbnail $standard)
  {
    $this->standard = $standard;
  }
  public function getStandard()
  {
    return $this->standard;
  }
}

class Google_Service_YouTube_ThumbnailSetResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $eventId;
  protected $itemsType = 'Google_Service_YouTube_ThumbnailDetails';
  protected $itemsDataType = 'array';
  public $kind;
  public $visitorId;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setEventId($eventId)
  {
    $this->eventId = $eventId;
  }
  public function getEventId()
  {
    return $this->eventId;
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
  public function setVisitorId($visitorId)
  {
    $this->visitorId = $visitorId;
  }
  public function getVisitorId()
  {
    return $this->visitorId;
  }
}

class Google_Service_YouTube_TokenPagination extends Google_Model
{
}

class Google_Service_YouTube_Video extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $ageGatingType = 'Google_Service_YouTube_VideoAgeGating';
  protected $ageGatingDataType = '';
  protected $contentDetailsType = 'Google_Service_YouTube_VideoContentDetails';
  protected $contentDetailsDataType = '';
  protected $conversionPingsType = 'Google_Service_YouTube_VideoConversionPings';
  protected $conversionPingsDataType = '';
  public $etag;
  protected $fileDetailsType = 'Google_Service_YouTube_VideoFileDetails';
  protected $fileDetailsDataType = '';
  public $id;
  public $kind;
  protected $liveStreamingDetailsType = 'Google_Service_YouTube_VideoLiveStreamingDetails';
  protected $liveStreamingDetailsDataType = '';
  protected $localizationsType = 'Google_Service_YouTube_VideoLocalization';
  protected $localizationsDataType = 'map';
  protected $monetizationDetailsType = 'Google_Service_YouTube_VideoMonetizationDetails';
  protected $monetizationDetailsDataType = '';
  protected $playerType = 'Google_Service_YouTube_VideoPlayer';
  protected $playerDataType = '';
  protected $processingDetailsType = 'Google_Service_YouTube_VideoProcessingDetails';
  protected $processingDetailsDataType = '';
  protected $projectDetailsType = 'Google_Service_YouTube_VideoProjectDetails';
  protected $projectDetailsDataType = '';
  protected $recordingDetailsType = 'Google_Service_YouTube_VideoRecordingDetails';
  protected $recordingDetailsDataType = '';
  protected $snippetType = 'Google_Service_YouTube_VideoSnippet';
  protected $snippetDataType = '';
  protected $statisticsType = 'Google_Service_YouTube_VideoStatistics';
  protected $statisticsDataType = '';
  protected $statusType = 'Google_Service_YouTube_VideoStatus';
  protected $statusDataType = '';
  protected $suggestionsType = 'Google_Service_YouTube_VideoSuggestions';
  protected $suggestionsDataType = '';
  protected $topicDetailsType = 'Google_Service_YouTube_VideoTopicDetails';
  protected $topicDetailsDataType = '';


  public function setAgeGating(Google_Service_YouTube_VideoAgeGating $ageGating)
  {
    $this->ageGating = $ageGating;
  }
  public function getAgeGating()
  {
    return $this->ageGating;
  }
  public function setContentDetails(Google_Service_YouTube_VideoContentDetails $contentDetails)
  {
    $this->contentDetails = $contentDetails;
  }
  public function getContentDetails()
  {
    return $this->contentDetails;
  }
  public function setConversionPings(Google_Service_YouTube_VideoConversionPings $conversionPings)
  {
    $this->conversionPings = $conversionPings;
  }
  public function getConversionPings()
  {
    return $this->conversionPings;
  }
  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setFileDetails(Google_Service_YouTube_VideoFileDetails $fileDetails)
  {
    $this->fileDetails = $fileDetails;
  }
  public function getFileDetails()
  {
    return $this->fileDetails;
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
  public function setLiveStreamingDetails(Google_Service_YouTube_VideoLiveStreamingDetails $liveStreamingDetails)
  {
    $this->liveStreamingDetails = $liveStreamingDetails;
  }
  public function getLiveStreamingDetails()
  {
    return $this->liveStreamingDetails;
  }
  public function setLocalizations($localizations)
  {
    $this->localizations = $localizations;
  }
  public function getLocalizations()
  {
    return $this->localizations;
  }
  public function setMonetizationDetails(Google_Service_YouTube_VideoMonetizationDetails $monetizationDetails)
  {
    $this->monetizationDetails = $monetizationDetails;
  }
  public function getMonetizationDetails()
  {
    return $this->monetizationDetails;
  }
  public function setPlayer(Google_Service_YouTube_VideoPlayer $player)
  {
    $this->player = $player;
  }
  public function getPlayer()
  {
    return $this->player;
  }
  public function setProcessingDetails(Google_Service_YouTube_VideoProcessingDetails $processingDetails)
  {
    $this->processingDetails = $processingDetails;
  }
  public function getProcessingDetails()
  {
    return $this->processingDetails;
  }
  public function setProjectDetails(Google_Service_YouTube_VideoProjectDetails $projectDetails)
  {
    $this->projectDetails = $projectDetails;
  }
  public function getProjectDetails()
  {
    return $this->projectDetails;
  }
  public function setRecordingDetails(Google_Service_YouTube_VideoRecordingDetails $recordingDetails)
  {
    $this->recordingDetails = $recordingDetails;
  }
  public function getRecordingDetails()
  {
    return $this->recordingDetails;
  }
  public function setSnippet(Google_Service_YouTube_VideoSnippet $snippet)
  {
    $this->snippet = $snippet;
  }
  public function getSnippet()
  {
    return $this->snippet;
  }
  public function setStatistics(Google_Service_YouTube_VideoStatistics $statistics)
  {
    $this->statistics = $statistics;
  }
  public function getStatistics()
  {
    return $this->statistics;
  }
  public function setStatus(Google_Service_YouTube_VideoStatus $status)
  {
    $this->status = $status;
  }
  public function getStatus()
  {
    return $this->status;
  }
  public function setSuggestions(Google_Service_YouTube_VideoSuggestions $suggestions)
  {
    $this->suggestions = $suggestions;
  }
  public function getSuggestions()
  {
    return $this->suggestions;
  }
  public function setTopicDetails(Google_Service_YouTube_VideoTopicDetails $topicDetails)
  {
    $this->topicDetails = $topicDetails;
  }
  public function getTopicDetails()
  {
    return $this->topicDetails;
  }
}

class Google_Service_YouTube_VideoAbuseReport extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $comments;
  public $language;
  public $reasonId;
  public $secondaryReasonId;
  public $videoId;


  public function setComments($comments)
  {
    $this->comments = $comments;
  }
  public function getComments()
  {
    return $this->comments;
  }
  public function setLanguage($language)
  {
    $this->language = $language;
  }
  public function getLanguage()
  {
    return $this->language;
  }
  public function setReasonId($reasonId)
  {
    $this->reasonId = $reasonId;
  }
  public function getReasonId()
  {
    return $this->reasonId;
  }
  public function setSecondaryReasonId($secondaryReasonId)
  {
    $this->secondaryReasonId = $secondaryReasonId;
  }
  public function getSecondaryReasonId()
  {
    return $this->secondaryReasonId;
  }
  public function setVideoId($videoId)
  {
    $this->videoId = $videoId;
  }
  public function getVideoId()
  {
    return $this->videoId;
  }
}

class Google_Service_YouTube_VideoAbuseReportReason extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $id;
  public $kind;
  protected $snippetType = 'Google_Service_YouTube_VideoAbuseReportReasonSnippet';
  protected $snippetDataType = '';


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
  public function setSnippet(Google_Service_YouTube_VideoAbuseReportReasonSnippet $snippet)
  {
    $this->snippet = $snippet;
  }
  public function getSnippet()
  {
    return $this->snippet;
  }
}

class Google_Service_YouTube_VideoAbuseReportReasonListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $eventId;
  protected $itemsType = 'Google_Service_YouTube_VideoAbuseReportReason';
  protected $itemsDataType = 'array';
  public $kind;
  public $visitorId;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setEventId($eventId)
  {
    $this->eventId = $eventId;
  }
  public function getEventId()
  {
    return $this->eventId;
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
  public function setVisitorId($visitorId)
  {
    $this->visitorId = $visitorId;
  }
  public function getVisitorId()
  {
    return $this->visitorId;
  }
}

class Google_Service_YouTube_VideoAbuseReportReasonSnippet extends Google_Collection
{
  protected $collection_key = 'secondaryReasons';
  protected $internal_gapi_mappings = array(
  );
  public $label;
  protected $secondaryReasonsType = 'Google_Service_YouTube_VideoAbuseReportSecondaryReason';
  protected $secondaryReasonsDataType = 'array';


  public function setLabel($label)
  {
    $this->label = $label;
  }
  public function getLabel()
  {
    return $this->label;
  }
  public function setSecondaryReasons($secondaryReasons)
  {
    $this->secondaryReasons = $secondaryReasons;
  }
  public function getSecondaryReasons()
  {
    return $this->secondaryReasons;
  }
}

class Google_Service_YouTube_VideoAbuseReportSecondaryReason extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $id;
  public $label;


  public function setId($id)
  {
    $this->id = $id;
  }
  public function getId()
  {
    return $this->id;
  }
  public function setLabel($label)
  {
    $this->label = $label;
  }
  public function getLabel()
  {
    return $this->label;
  }
}

class Google_Service_YouTube_VideoAgeGating extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $alcoholContent;
  public $restricted;
  public $videoGameRating;


  public function setAlcoholContent($alcoholContent)
  {
    $this->alcoholContent = $alcoholContent;
  }
  public function getAlcoholContent()
  {
    return $this->alcoholContent;
  }
  public function setRestricted($restricted)
  {
    $this->restricted = $restricted;
  }
  public function getRestricted()
  {
    return $this->restricted;
  }
  public function setVideoGameRating($videoGameRating)
  {
    $this->videoGameRating = $videoGameRating;
  }
  public function getVideoGameRating()
  {
    return $this->videoGameRating;
  }
}

class Google_Service_YouTube_VideoCategory extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $id;
  public $kind;
  protected $snippetType = 'Google_Service_YouTube_VideoCategorySnippet';
  protected $snippetDataType = '';


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
  public function setSnippet(Google_Service_YouTube_VideoCategorySnippet $snippet)
  {
    $this->snippet = $snippet;
  }
  public function getSnippet()
  {
    return $this->snippet;
  }
}

class Google_Service_YouTube_VideoCategoryListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $eventId;
  protected $itemsType = 'Google_Service_YouTube_VideoCategory';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;
  protected $pageInfoType = 'Google_Service_YouTube_PageInfo';
  protected $pageInfoDataType = '';
  public $prevPageToken;
  protected $tokenPaginationType = 'Google_Service_YouTube_TokenPagination';
  protected $tokenPaginationDataType = '';
  public $visitorId;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setEventId($eventId)
  {
    $this->eventId = $eventId;
  }
  public function getEventId()
  {
    return $this->eventId;
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
  public function setPageInfo(Google_Service_YouTube_PageInfo $pageInfo)
  {
    $this->pageInfo = $pageInfo;
  }
  public function getPageInfo()
  {
    return $this->pageInfo;
  }
  public function setPrevPageToken($prevPageToken)
  {
    $this->prevPageToken = $prevPageToken;
  }
  public function getPrevPageToken()
  {
    return $this->prevPageToken;
  }
  public function setTokenPagination(Google_Service_YouTube_TokenPagination $tokenPagination)
  {
    $this->tokenPagination = $tokenPagination;
  }
  public function getTokenPagination()
  {
    return $this->tokenPagination;
  }
  public function setVisitorId($visitorId)
  {
    $this->visitorId = $visitorId;
  }
  public function getVisitorId()
  {
    return $this->visitorId;
  }
}

class Google_Service_YouTube_VideoCategorySnippet extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $assignable;
  public $channelId;
  public $title;


  public function setAssignable($assignable)
  {
    $this->assignable = $assignable;
  }
  public function getAssignable()
  {
    return $this->assignable;
  }
  public function setChannelId($channelId)
  {
    $this->channelId = $channelId;
  }
  public function getChannelId()
  {
    return $this->channelId;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
}

class Google_Service_YouTube_VideoContentDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $caption;
  protected $contentRatingType = 'Google_Service_YouTube_ContentRating';
  protected $contentRatingDataType = '';
  protected $countryRestrictionType = 'Google_Service_YouTube_AccessPolicy';
  protected $countryRestrictionDataType = '';
  public $definition;
  public $dimension;
  public $duration;
  public $licensedContent;
  protected $regionRestrictionType = 'Google_Service_YouTube_VideoContentDetailsRegionRestriction';
  protected $regionRestrictionDataType = '';


  public function setCaption($caption)
  {
    $this->caption = $caption;
  }
  public function getCaption()
  {
    return $this->caption;
  }
  public function setContentRating(Google_Service_YouTube_ContentRating $contentRating)
  {
    $this->contentRating = $contentRating;
  }
  public function getContentRating()
  {
    return $this->contentRating;
  }
  public function setCountryRestriction(Google_Service_YouTube_AccessPolicy $countryRestriction)
  {
    $this->countryRestriction = $countryRestriction;
  }
  public function getCountryRestriction()
  {
    return $this->countryRestriction;
  }
  public function setDefinition($definition)
  {
    $this->definition = $definition;
  }
  public function getDefinition()
  {
    return $this->definition;
  }
  public function setDimension($dimension)
  {
    $this->dimension = $dimension;
  }
  public function getDimension()
  {
    return $this->dimension;
  }
  public function setDuration($duration)
  {
    $this->duration = $duration;
  }
  public function getDuration()
  {
    return $this->duration;
  }
  public function setLicensedContent($licensedContent)
  {
    $this->licensedContent = $licensedContent;
  }
  public function getLicensedContent()
  {
    return $this->licensedContent;
  }
  public function setRegionRestriction(Google_Service_YouTube_VideoContentDetailsRegionRestriction $regionRestriction)
  {
    $this->regionRestriction = $regionRestriction;
  }
  public function getRegionRestriction()
  {
    return $this->regionRestriction;
  }
}

class Google_Service_YouTube_VideoContentDetailsRegionRestriction extends Google_Collection
{
  protected $collection_key = 'blocked';
  protected $internal_gapi_mappings = array(
  );
  public $allowed;
  public $blocked;


  public function setAllowed($allowed)
  {
    $this->allowed = $allowed;
  }
  public function getAllowed()
  {
    return $this->allowed;
  }
  public function setBlocked($blocked)
  {
    $this->blocked = $blocked;
  }
  public function getBlocked()
  {
    return $this->blocked;
  }
}

class Google_Service_YouTube_VideoConversionPing extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $context;
  public $conversionUrl;


  public function setContext($context)
  {
    $this->context = $context;
  }
  public function getContext()
  {
    return $this->context;
  }
  public function setConversionUrl($conversionUrl)
  {
    $this->conversionUrl = $conversionUrl;
  }
  public function getConversionUrl()
  {
    return $this->conversionUrl;
  }
}

class Google_Service_YouTube_VideoConversionPings extends Google_Collection
{
  protected $collection_key = 'pings';
  protected $internal_gapi_mappings = array(
  );
  protected $pingsType = 'Google_Service_YouTube_VideoConversionPing';
  protected $pingsDataType = 'array';


  public function setPings($pings)
  {
    $this->pings = $pings;
  }
  public function getPings()
  {
    return $this->pings;
  }
}

class Google_Service_YouTube_VideoFileDetails extends Google_Collection
{
  protected $collection_key = 'videoStreams';
  protected $internal_gapi_mappings = array(
  );
  protected $audioStreamsType = 'Google_Service_YouTube_VideoFileDetailsAudioStream';
  protected $audioStreamsDataType = 'array';
  public $bitrateBps;
  public $container;
  public $creationTime;
  public $durationMs;
  public $fileName;
  public $fileSize;
  public $fileType;
  protected $recordingLocationType = 'Google_Service_YouTube_GeoPoint';
  protected $recordingLocationDataType = '';
  protected $videoStreamsType = 'Google_Service_YouTube_VideoFileDetailsVideoStream';
  protected $videoStreamsDataType = 'array';


  public function setAudioStreams($audioStreams)
  {
    $this->audioStreams = $audioStreams;
  }
  public function getAudioStreams()
  {
    return $this->audioStreams;
  }
  public function setBitrateBps($bitrateBps)
  {
    $this->bitrateBps = $bitrateBps;
  }
  public function getBitrateBps()
  {
    return $this->bitrateBps;
  }
  public function setContainer($container)
  {
    $this->container = $container;
  }
  public function getContainer()
  {
    return $this->container;
  }
  public function setCreationTime($creationTime)
  {
    $this->creationTime = $creationTime;
  }
  public function getCreationTime()
  {
    return $this->creationTime;
  }
  public function setDurationMs($durationMs)
  {
    $this->durationMs = $durationMs;
  }
  public function getDurationMs()
  {
    return $this->durationMs;
  }
  public function setFileName($fileName)
  {
    $this->fileName = $fileName;
  }
  public function getFileName()
  {
    return $this->fileName;
  }
  public function setFileSize($fileSize)
  {
    $this->fileSize = $fileSize;
  }
  public function getFileSize()
  {
    return $this->fileSize;
  }
  public function setFileType($fileType)
  {
    $this->fileType = $fileType;
  }
  public function getFileType()
  {
    return $this->fileType;
  }
  public function setRecordingLocation(Google_Service_YouTube_GeoPoint $recordingLocation)
  {
    $this->recordingLocation = $recordingLocation;
  }
  public function getRecordingLocation()
  {
    return $this->recordingLocation;
  }
  public function setVideoStreams($videoStreams)
  {
    $this->videoStreams = $videoStreams;
  }
  public function getVideoStreams()
  {
    return $this->videoStreams;
  }
}

class Google_Service_YouTube_VideoFileDetailsAudioStream extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $bitrateBps;
  public $channelCount;
  public $codec;
  public $vendor;


  public function setBitrateBps($bitrateBps)
  {
    $this->bitrateBps = $bitrateBps;
  }
  public function getBitrateBps()
  {
    return $this->bitrateBps;
  }
  public function setChannelCount($channelCount)
  {
    $this->channelCount = $channelCount;
  }
  public function getChannelCount()
  {
    return $this->channelCount;
  }
  public function setCodec($codec)
  {
    $this->codec = $codec;
  }
  public function getCodec()
  {
    return $this->codec;
  }
  public function setVendor($vendor)
  {
    $this->vendor = $vendor;
  }
  public function getVendor()
  {
    return $this->vendor;
  }
}

class Google_Service_YouTube_VideoFileDetailsVideoStream extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $aspectRatio;
  public $bitrateBps;
  public $codec;
  public $frameRateFps;
  public $heightPixels;
  public $rotation;
  public $vendor;
  public $widthPixels;


  public function setAspectRatio($aspectRatio)
  {
    $this->aspectRatio = $aspectRatio;
  }
  public function getAspectRatio()
  {
    return $this->aspectRatio;
  }
  public function setBitrateBps($bitrateBps)
  {
    $this->bitrateBps = $bitrateBps;
  }
  public function getBitrateBps()
  {
    return $this->bitrateBps;
  }
  public function setCodec($codec)
  {
    $this->codec = $codec;
  }
  public function getCodec()
  {
    return $this->codec;
  }
  public function setFrameRateFps($frameRateFps)
  {
    $this->frameRateFps = $frameRateFps;
  }
  public function getFrameRateFps()
  {
    return $this->frameRateFps;
  }
  public function setHeightPixels($heightPixels)
  {
    $this->heightPixels = $heightPixels;
  }
  public function getHeightPixels()
  {
    return $this->heightPixels;
  }
  public function setRotation($rotation)
  {
    $this->rotation = $rotation;
  }
  public function getRotation()
  {
    return $this->rotation;
  }
  public function setVendor($vendor)
  {
    $this->vendor = $vendor;
  }
  public function getVendor()
  {
    return $this->vendor;
  }
  public function setWidthPixels($widthPixels)
  {
    $this->widthPixels = $widthPixels;
  }
  public function getWidthPixels()
  {
    return $this->widthPixels;
  }
}

class Google_Service_YouTube_VideoGetRatingResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $eventId;
  protected $itemsType = 'Google_Service_YouTube_VideoRating';
  protected $itemsDataType = 'array';
  public $kind;
  public $visitorId;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setEventId($eventId)
  {
    $this->eventId = $eventId;
  }
  public function getEventId()
  {
    return $this->eventId;
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
  public function setVisitorId($visitorId)
  {
    $this->visitorId = $visitorId;
  }
  public function getVisitorId()
  {
    return $this->visitorId;
  }
}

class Google_Service_YouTube_VideoListResponse extends Google_Collection
{
  protected $collection_key = 'items';
  protected $internal_gapi_mappings = array(
  );
  public $etag;
  public $eventId;
  protected $itemsType = 'Google_Service_YouTube_Video';
  protected $itemsDataType = 'array';
  public $kind;
  public $nextPageToken;
  protected $pageInfoType = 'Google_Service_YouTube_PageInfo';
  protected $pageInfoDataType = '';
  public $prevPageToken;
  protected $tokenPaginationType = 'Google_Service_YouTube_TokenPagination';
  protected $tokenPaginationDataType = '';
  public $visitorId;


  public function setEtag($etag)
  {
    $this->etag = $etag;
  }
  public function getEtag()
  {
    return $this->etag;
  }
  public function setEventId($eventId)
  {
    $this->eventId = $eventId;
  }
  public function getEventId()
  {
    return $this->eventId;
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
  public function setPageInfo(Google_Service_YouTube_PageInfo $pageInfo)
  {
    $this->pageInfo = $pageInfo;
  }
  public function getPageInfo()
  {
    return $this->pageInfo;
  }
  public function setPrevPageToken($prevPageToken)
  {
    $this->prevPageToken = $prevPageToken;
  }
  public function getPrevPageToken()
  {
    return $this->prevPageToken;
  }
  public function setTokenPagination(Google_Service_YouTube_TokenPagination $tokenPagination)
  {
    $this->tokenPagination = $tokenPagination;
  }
  public function getTokenPagination()
  {
    return $this->tokenPagination;
  }
  public function setVisitorId($visitorId)
  {
    $this->visitorId = $visitorId;
  }
  public function getVisitorId()
  {
    return $this->visitorId;
  }
}

class Google_Service_YouTube_VideoLiveStreamingDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $actualEndTime;
  public $actualStartTime;
  public $concurrentViewers;
  public $scheduledEndTime;
  public $scheduledStartTime;


  public function setActualEndTime($actualEndTime)
  {
    $this->actualEndTime = $actualEndTime;
  }
  public function getActualEndTime()
  {
    return $this->actualEndTime;
  }
  public function setActualStartTime($actualStartTime)
  {
    $this->actualStartTime = $actualStartTime;
  }
  public function getActualStartTime()
  {
    return $this->actualStartTime;
  }
  public function setConcurrentViewers($concurrentViewers)
  {
    $this->concurrentViewers = $concurrentViewers;
  }
  public function getConcurrentViewers()
  {
    return $this->concurrentViewers;
  }
  public function setScheduledEndTime($scheduledEndTime)
  {
    $this->scheduledEndTime = $scheduledEndTime;
  }
  public function getScheduledEndTime()
  {
    return $this->scheduledEndTime;
  }
  public function setScheduledStartTime($scheduledStartTime)
  {
    $this->scheduledStartTime = $scheduledStartTime;
  }
  public function getScheduledStartTime()
  {
    return $this->scheduledStartTime;
  }
}

class Google_Service_YouTube_VideoLocalization extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $description;
  public $title;


  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
}

class Google_Service_YouTube_VideoLocalizations extends Google_Model
{
}

class Google_Service_YouTube_VideoMonetizationDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $accessType = 'Google_Service_YouTube_AccessPolicy';
  protected $accessDataType = '';


  public function setAccess(Google_Service_YouTube_AccessPolicy $access)
  {
    $this->access = $access;
  }
  public function getAccess()
  {
    return $this->access;
  }
}

class Google_Service_YouTube_VideoPlayer extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $embedHtml;


  public function setEmbedHtml($embedHtml)
  {
    $this->embedHtml = $embedHtml;
  }
  public function getEmbedHtml()
  {
    return $this->embedHtml;
  }
}

class Google_Service_YouTube_VideoProcessingDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $editorSuggestionsAvailability;
  public $fileDetailsAvailability;
  public $processingFailureReason;
  public $processingIssuesAvailability;
  protected $processingProgressType = 'Google_Service_YouTube_VideoProcessingDetailsProcessingProgress';
  protected $processingProgressDataType = '';
  public $processingStatus;
  public $tagSuggestionsAvailability;
  public $thumbnailsAvailability;


  public function setEditorSuggestionsAvailability($editorSuggestionsAvailability)
  {
    $this->editorSuggestionsAvailability = $editorSuggestionsAvailability;
  }
  public function getEditorSuggestionsAvailability()
  {
    return $this->editorSuggestionsAvailability;
  }
  public function setFileDetailsAvailability($fileDetailsAvailability)
  {
    $this->fileDetailsAvailability = $fileDetailsAvailability;
  }
  public function getFileDetailsAvailability()
  {
    return $this->fileDetailsAvailability;
  }
  public function setProcessingFailureReason($processingFailureReason)
  {
    $this->processingFailureReason = $processingFailureReason;
  }
  public function getProcessingFailureReason()
  {
    return $this->processingFailureReason;
  }
  public function setProcessingIssuesAvailability($processingIssuesAvailability)
  {
    $this->processingIssuesAvailability = $processingIssuesAvailability;
  }
  public function getProcessingIssuesAvailability()
  {
    return $this->processingIssuesAvailability;
  }
  public function setProcessingProgress(Google_Service_YouTube_VideoProcessingDetailsProcessingProgress $processingProgress)
  {
    $this->processingProgress = $processingProgress;
  }
  public function getProcessingProgress()
  {
    return $this->processingProgress;
  }
  public function setProcessingStatus($processingStatus)
  {
    $this->processingStatus = $processingStatus;
  }
  public function getProcessingStatus()
  {
    return $this->processingStatus;
  }
  public function setTagSuggestionsAvailability($tagSuggestionsAvailability)
  {
    $this->tagSuggestionsAvailability = $tagSuggestionsAvailability;
  }
  public function getTagSuggestionsAvailability()
  {
    return $this->tagSuggestionsAvailability;
  }
  public function setThumbnailsAvailability($thumbnailsAvailability)
  {
    $this->thumbnailsAvailability = $thumbnailsAvailability;
  }
  public function getThumbnailsAvailability()
  {
    return $this->thumbnailsAvailability;
  }
}

class Google_Service_YouTube_VideoProcessingDetailsProcessingProgress extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $partsProcessed;
  public $partsTotal;
  public $timeLeftMs;


  public function setPartsProcessed($partsProcessed)
  {
    $this->partsProcessed = $partsProcessed;
  }
  public function getPartsProcessed()
  {
    return $this->partsProcessed;
  }
  public function setPartsTotal($partsTotal)
  {
    $this->partsTotal = $partsTotal;
  }
  public function getPartsTotal()
  {
    return $this->partsTotal;
  }
  public function setTimeLeftMs($timeLeftMs)
  {
    $this->timeLeftMs = $timeLeftMs;
  }
  public function getTimeLeftMs()
  {
    return $this->timeLeftMs;
  }
}

class Google_Service_YouTube_VideoProjectDetails extends Google_Collection
{
  protected $collection_key = 'tags';
  protected $internal_gapi_mappings = array(
  );
  public $tags;


  public function setTags($tags)
  {
    $this->tags = $tags;
  }
  public function getTags()
  {
    return $this->tags;
  }
}

class Google_Service_YouTube_VideoRating extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $rating;
  public $videoId;


  public function setRating($rating)
  {
    $this->rating = $rating;
  }
  public function getRating()
  {
    return $this->rating;
  }
  public function setVideoId($videoId)
  {
    $this->videoId = $videoId;
  }
  public function getVideoId()
  {
    return $this->videoId;
  }
}

class Google_Service_YouTube_VideoRecordingDetails extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  protected $locationType = 'Google_Service_YouTube_GeoPoint';
  protected $locationDataType = '';
  public $locationDescription;
  public $recordingDate;


  public function setLocation(Google_Service_YouTube_GeoPoint $location)
  {
    $this->location = $location;
  }
  public function getLocation()
  {
    return $this->location;
  }
  public function setLocationDescription($locationDescription)
  {
    $this->locationDescription = $locationDescription;
  }
  public function getLocationDescription()
  {
    return $this->locationDescription;
  }
  public function setRecordingDate($recordingDate)
  {
    $this->recordingDate = $recordingDate;
  }
  public function getRecordingDate()
  {
    return $this->recordingDate;
  }
}

class Google_Service_YouTube_VideoSnippet extends Google_Collection
{
  protected $collection_key = 'tags';
  protected $internal_gapi_mappings = array(
  );
  public $categoryId;
  public $channelId;
  public $channelTitle;
  public $defaultAudioLanguage;
  public $defaultLanguage;
  public $description;
  public $liveBroadcastContent;
  protected $localizedType = 'Google_Service_YouTube_VideoLocalization';
  protected $localizedDataType = '';
  public $publishedAt;
  public $tags;
  protected $thumbnailsType = 'Google_Service_YouTube_ThumbnailDetails';
  protected $thumbnailsDataType = '';
  public $title;


  public function setCategoryId($categoryId)
  {
    $this->categoryId = $categoryId;
  }
  public function getCategoryId()
  {
    return $this->categoryId;
  }
  public function setChannelId($channelId)
  {
    $this->channelId = $channelId;
  }
  public function getChannelId()
  {
    return $this->channelId;
  }
  public function setChannelTitle($channelTitle)
  {
    $this->channelTitle = $channelTitle;
  }
  public function getChannelTitle()
  {
    return $this->channelTitle;
  }
  public function setDefaultAudioLanguage($defaultAudioLanguage)
  {
    $this->defaultAudioLanguage = $defaultAudioLanguage;
  }
  public function getDefaultAudioLanguage()
  {
    return $this->defaultAudioLanguage;
  }
  public function setDefaultLanguage($defaultLanguage)
  {
    $this->defaultLanguage = $defaultLanguage;
  }
  public function getDefaultLanguage()
  {
    return $this->defaultLanguage;
  }
  public function setDescription($description)
  {
    $this->description = $description;
  }
  public function getDescription()
  {
    return $this->description;
  }
  public function setLiveBroadcastContent($liveBroadcastContent)
  {
    $this->liveBroadcastContent = $liveBroadcastContent;
  }
  public function getLiveBroadcastContent()
  {
    return $this->liveBroadcastContent;
  }
  public function setLocalized(Google_Service_YouTube_VideoLocalization $localized)
  {
    $this->localized = $localized;
  }
  public function getLocalized()
  {
    return $this->localized;
  }
  public function setPublishedAt($publishedAt)
  {
    $this->publishedAt = $publishedAt;
  }
  public function getPublishedAt()
  {
    return $this->publishedAt;
  }
  public function setTags($tags)
  {
    $this->tags = $tags;
  }
  public function getTags()
  {
    return $this->tags;
  }
  public function setThumbnails(Google_Service_YouTube_ThumbnailDetails $thumbnails)
  {
    $this->thumbnails = $thumbnails;
  }
  public function getThumbnails()
  {
    return $this->thumbnails;
  }
  public function setTitle($title)
  {
    $this->title = $title;
  }
  public function getTitle()
  {
    return $this->title;
  }
}

class Google_Service_YouTube_VideoStatistics extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $commentCount;
  public $dislikeCount;
  public $favoriteCount;
  public $likeCount;
  public $viewCount;


  public function setCommentCount($commentCount)
  {
    $this->commentCount = $commentCount;
  }
  public function getCommentCount()
  {
    return $this->commentCount;
  }
  public function setDislikeCount($dislikeCount)
  {
    $this->dislikeCount = $dislikeCount;
  }
  public function getDislikeCount()
  {
    return $this->dislikeCount;
  }
  public function setFavoriteCount($favoriteCount)
  {
    $this->favoriteCount = $favoriteCount;
  }
  public function getFavoriteCount()
  {
    return $this->favoriteCount;
  }
  public function setLikeCount($likeCount)
  {
    $this->likeCount = $likeCount;
  }
  public function getLikeCount()
  {
    return $this->likeCount;
  }
  public function setViewCount($viewCount)
  {
    $this->viewCount = $viewCount;
  }
  public function getViewCount()
  {
    return $this->viewCount;
  }
}

class Google_Service_YouTube_VideoStatus extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $embeddable;
  public $failureReason;
  public $license;
  public $privacyStatus;
  public $publicStatsViewable;
  public $publishAt;
  public $rejectionReason;
  public $uploadStatus;


  public function setEmbeddable($embeddable)
  {
    $this->embeddable = $embeddable;
  }
  public function getEmbeddable()
  {
    return $this->embeddable;
  }
  public function setFailureReason($failureReason)
  {
    $this->failureReason = $failureReason;
  }
  public function getFailureReason()
  {
    return $this->failureReason;
  }
  public function setLicense($license)
  {
    $this->license = $license;
  }
  public function getLicense()
  {
    return $this->license;
  }
  public function setPrivacyStatus($privacyStatus)
  {
    $this->privacyStatus = $privacyStatus;
  }
  public function getPrivacyStatus()
  {
    return $this->privacyStatus;
  }
  public function setPublicStatsViewable($publicStatsViewable)
  {
    $this->publicStatsViewable = $publicStatsViewable;
  }
  public function getPublicStatsViewable()
  {
    return $this->publicStatsViewable;
  }
  public function setPublishAt($publishAt)
  {
    $this->publishAt = $publishAt;
  }
  public function getPublishAt()
  {
    return $this->publishAt;
  }
  public function setRejectionReason($rejectionReason)
  {
    $this->rejectionReason = $rejectionReason;
  }
  public function getRejectionReason()
  {
    return $this->rejectionReason;
  }
  public function setUploadStatus($uploadStatus)
  {
    $this->uploadStatus = $uploadStatus;
  }
  public function getUploadStatus()
  {
    return $this->uploadStatus;
  }
}

class Google_Service_YouTube_VideoSuggestions extends Google_Collection
{
  protected $collection_key = 'tagSuggestions';
  protected $internal_gapi_mappings = array(
  );
  public $editorSuggestions;
  public $processingErrors;
  public $processingHints;
  public $processingWarnings;
  protected $tagSuggestionsType = 'Google_Service_YouTube_VideoSuggestionsTagSuggestion';
  protected $tagSuggestionsDataType = 'array';


  public function setEditorSuggestions($editorSuggestions)
  {
    $this->editorSuggestions = $editorSuggestions;
  }
  public function getEditorSuggestions()
  {
    return $this->editorSuggestions;
  }
  public function setProcessingErrors($processingErrors)
  {
    $this->processingErrors = $processingErrors;
  }
  public function getProcessingErrors()
  {
    return $this->processingErrors;
  }
  public function setProcessingHints($processingHints)
  {
    $this->processingHints = $processingHints;
  }
  public function getProcessingHints()
  {
    return $this->processingHints;
  }
  public function setProcessingWarnings($processingWarnings)
  {
    $this->processingWarnings = $processingWarnings;
  }
  public function getProcessingWarnings()
  {
    return $this->processingWarnings;
  }
  public function setTagSuggestions($tagSuggestions)
  {
    $this->tagSuggestions = $tagSuggestions;
  }
  public function getTagSuggestions()
  {
    return $this->tagSuggestions;
  }
}

class Google_Service_YouTube_VideoSuggestionsTagSuggestion extends Google_Collection
{
  protected $collection_key = 'categoryRestricts';
  protected $internal_gapi_mappings = array(
  );
  public $categoryRestricts;
  public $tag;


  public function setCategoryRestricts($categoryRestricts)
  {
    $this->categoryRestricts = $categoryRestricts;
  }
  public function getCategoryRestricts()
  {
    return $this->categoryRestricts;
  }
  public function setTag($tag)
  {
    $this->tag = $tag;
  }
  public function getTag()
  {
    return $this->tag;
  }
}

class Google_Service_YouTube_VideoTopicDetails extends Google_Collection
{
  protected $collection_key = 'topicIds';
  protected $internal_gapi_mappings = array(
  );
  public $relevantTopicIds;
  public $topicIds;


  public function setRelevantTopicIds($relevantTopicIds)
  {
    $this->relevantTopicIds = $relevantTopicIds;
  }
  public function getRelevantTopicIds()
  {
    return $this->relevantTopicIds;
  }
  public function setTopicIds($topicIds)
  {
    $this->topicIds = $topicIds;
  }
  public function getTopicIds()
  {
    return $this->topicIds;
  }
}

class Google_Service_YouTube_WatchSettings extends Google_Model
{
  protected $internal_gapi_mappings = array(
  );
  public $backgroundColor;
  public $featuredPlaylistId;
  public $textColor;


  public function setBackgroundColor($backgroundColor)
  {
    $this->backgroundColor = $backgroundColor;
  }
  public function getBackgroundColor()
  {
    return $this->backgroundColor;
  }
  public function setFeaturedPlaylistId($featuredPlaylistId)
  {
    $this->featuredPlaylistId = $featuredPlaylistId;
  }
  public function getFeaturedPlaylistId()
  {
    return $this->featuredPlaylistId;
  }
  public function setTextColor($textColor)
  {
    $this->textColor = $textColor;
  }
  public function getTextColor()
  {
    return $this->textColor;
  }
}
