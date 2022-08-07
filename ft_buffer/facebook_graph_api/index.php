<?php

use Illuminate\Support\Facades\Http;

class FbGraphApi
{
    private $graphUrl = 'https://graph.facebook.com';
    private $access_token = null;
    private $expires_In = null;
    private $userId = null;

    public function setUserID($id)
    {
        $this->userId = $id;
    }

    public function __construct($userId, $access_token, $expires_In)
    {
        $this->access_token = $access_token;
        $this->expires_In = $expires_In;
        $this->userId = $userId;
    }

    public function LoadCurrentUser()
    {
        $data = $this->curlGET($this->graphUrl . '/v14.0/me?access_token=' . $this->access_token . '&fields=id,name,email');
        $data = json_decode($data, true);
        return $data;
    }

    public function LoadPages()
    {
        $data = $this->curlGET($this->graphUrl . '/v14.0/' . $this->userId . '/accounts?access_token=' . $this->access_token);
        $data = json_decode($data, true);
        return $data;
    }

    public function CreatePagePost($pageId, $pageAccessToken, $message, $link = null, $image = null, $video = null)
    {
        $data = $this->curlPOST($this->graphUrl . "/$pageId/feed?message=$message&access_token=" . $pageAccessToken, []);
        $data = json_decode($data, true);
        return $data;
    }

    private function curlGET($url)
    {
        return Http::get($url);
    }

    private function curlPOST($url, $data = [])
    {
        return Http::post($url, $data);
    }
}
