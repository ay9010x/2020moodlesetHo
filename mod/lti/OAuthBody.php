<?php

namespace moodle\mod\lti; 
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/mod/lti/OAuth.php');
require_once($CFG->dirroot . '/mod/lti/TrivialStore.php');

function get_oauth_key_from_headers() {
    $requestheaders = OAuthUtil::get_headers();

    if (@substr($requestheaders['Authorization'], 0, 6) == "OAuth ") {
        $headerparameters = OAuthUtil::split_header($requestheaders['Authorization']);

        return format_string($headerparameters['oauth_consumer_key']);
    }
    return false;
}

function handle_oauth_body_post($oauthconsumerkey, $oauthconsumersecret, $body, $requestheaders = null) {

    if ($requestheaders == null) {
        $requestheaders = OAuthUtil::get_headers();
    }

        if (isset($requestheaders['Content-type'])) {
        if ($requestheaders['Content-type'] == 'application/x-www-form-urlencoded' ) {
            throw new OAuthException("OAuth request body signing must not use application/x-www-form-urlencoded");
        }
    }

    if (@substr($requestheaders['Authorization'], 0, 6) == "OAuth ") {
        $headerparameters = OAuthUtil::split_header($requestheaders['Authorization']);
        $oauthbodyhash = $headerparameters['oauth_body_hash'];
    }

    if ( ! isset($oauthbodyhash)  ) {
        throw new OAuthException("OAuth request body signing requires oauth_body_hash body");
    }

        $store = new TrivialOAuthDataStore();
    $store->add_consumer($oauthconsumerkey, $oauthconsumersecret);

    $server = new OAuthServer($store);

    $method = new OAuthSignatureMethod_HMAC_SHA1();
    $server->add_signature_method($method);
    $request = OAuthRequest::from_request();

    try {
        $server->verify_request($request);
    } catch (\Exception $e) {
        $message = $e->getMessage();
        throw new OAuthException("OAuth signature failed: " . $message);
    }

    $postdata = $body;

    $hash = base64_encode(sha1($postdata, true));

    if ( $hash != $oauthbodyhash ) {
        throw new OAuthException("OAuth oauth_body_hash mismatch");
    }

    return $postdata;
}
