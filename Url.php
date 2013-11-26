<?php

class Del_Googl_Url
{
    private $google_url = 'https://www.googleapis.com/urlshortener/v1/url?';
    private $oauth_key = 'Your Key Here';
    private $curl;

    private static $buffer = array();

    function __construct($oauth_key = null)
    {
        if ( $oauth_key != null )
        {
            $this->oauth_key = $oauth_key;
            $this->google_url .= 'key='.$oauth_key;
        }

        // init connection
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_URL, $this->google_url);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
    }

    function __destruct()
    {
        curl_close($this->curl);
        $this->curl = null;
    }

    /**
     * @param $key
     */
    public function setOAuthKey($key)
    {
        $this->oauth_key = $key;
    }

    public function shorten($url, $verbose = false)
    {
        // if we already have the shortened link then return it
        if (!empty(self::$buffer[$url]) )
        {
            return self::$buffer[$url];
        }

        //sort out what to post
        $json = json_encode(array(
            'longUrl' => $url
        ));

        // Set cURL options specific to a shorten request
        curl_setopt($this->curl, CURLOPT_POST, 1);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $json);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, Array('Content-Type: application/json'));

        //get result, stick in memory, and return result
        if($verbose == true)
        {
            $result = json_decode(curl_exec($this->curl));
        }
        else
        {
            $result = json_decode(curl_exec($this->curl))->id;
        }

        self::$buffer[$url] = $result;
        return $result;
    }

    public function expand($url, $verbose = false)
    {
        // Set cURL options specific to a expand request
        curl_setopt($this->curl, CURLOPT_HTTPGET, true);
        curl_setopt($this->curl, CURLOPT_URL, $this->google_url.'shortUrl='.$url);
        $result = json_decode(curl_exec($this->curl));
        if($verbose == true)
        {
            return $result;
        }
        return $result->longUrl;
    }
}

