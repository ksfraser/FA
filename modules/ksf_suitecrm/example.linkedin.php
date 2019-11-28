/**
     * Fetching Linkedin Contacts
     */

    public function getAllContacts($user_id,$account_id=NULL)
    {       
        $access_token = $this->fetchAccessToken($user_id,$account_id);
        $this->linkedin->access_token = new OAuthConsumer($access_token['oauth_token'], $access_token['oauth_token_secret']);
        **$xml_response = $this->linkedin->getProfile("~/connections");**       
        $contacts= simplexml_load_string($xml_response);
        //Zend_Debug::dump($contacts); 
        return $contacts;                       
    }
