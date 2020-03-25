<?php



namespace moodle\mod\lti; 
defined('MOODLE_INTERNAL') || die;


class TrivialOAuthDataStore extends OAuthDataStore {

    
    private $consumers = array();

    
    public function add_consumer($consumerkey, $consumersecret) {
        $this->consumers[$consumerkey] = $consumersecret;
    }

    
    public function lookup_consumer($consumerkey) {
        if (strpos($consumerkey, "http://" ) === 0) {
            $consumer = new OAuthConsumer($consumerkey, "secret", null);
            return $consumer;
        }
        if ( $this->consumers[$consumerkey] ) {
            $consumer = new OAuthConsumer($consumerkey, $this->consumers[$consumerkey], null);
            return $consumer;
        }
        return null;
    }

    
    public function lookup_token($consumer, $tokentype, $token) {
        return new OAuthToken($consumer, '');
    }

    
    public function lookup_nonce($consumer, $token, $nonce, $timestamp) {
                                return null;
    }

    
    public function new_request_token($consumer) {
        return null;
    }

    
    public function new_access_token($token, $consumer) {
        return null;
    }
}
