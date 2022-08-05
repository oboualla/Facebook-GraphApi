<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Facebook\Facebook;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

function curlGET($url)
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/json'
    ]);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function curlPOST($url, $data)
{
    $curl = curl_init($url);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

function curlLessMethod($url, $data, $method)
{
    // $url = 'http://server.com/path';
    // $data = array('key1' => 'value1', 'key2' => 'value2');
    // use key 'http' even if you send the request to https://...
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => $method,
            'content' => http_build_query($data)
        )
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    if ($result === FALSE)
        return [
            'error' => true,
            'success' => false,
            'result' => []
        ];
    return [
        'error' => true,
        'success' => false,
        'result' => $result
    ];
}

function BuildLoginUrl()
{
    $fb_connection = new Facebook([
        'app_id' => '426905939480875',
        'app_secret' => '4bdb361ce417d136629ebdb93445c4cd',
        'default_graph_version' => 'v13.0'
    ]);

    $helper = $fb_connection->getRedirectLoginHelper();
    $login_url = $helper->getLoginUrl('http://localhost:8000/facebook_callback');
    try {
        $access_token = $helper->getAccessToken();
        if (isset($access_token)) {
            $_SESSION['user_name'] = 'Omar Bouallam';
            $_SESSION['access_token'] = (string)$access_token;
        }
        header('Location: http://localhost:8000/facebook_callback');
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getTraceAsString();
        header('Location: /');
    }
    return $login_url . '&response_type=code%20token&scope=email,pages_show_list,pages_manage_cta,pages_read_engagement,pages_read_user_content,pages_manage_posts,pages_manage_metadata,pages_manage_instant_articles,pages_manage_engagement';
}

function LoadClientData()
{
    if (isset($_SESSION['access_token'])) {
        // load pages that we manage

        // load client id, name
        $data = curlGET(
            'https://graph.facebook.com/v14.0/me?access_token=' .
                $_SESSION['access_token']
            // . '&app_id=426905939480875&app_secret=4bdb361ce417d136629ebdb93445c4cd'
        );
        $data = json_decode($data, true);
        $_SESSION['name'] = $data['name'];
        $_SESSION['id'] = $data['id'];
        $data = curlGET(
            'https://graph.facebook.com/v14.0/' . $_SESSION['id'] . '/accounts?access_token=' .
                $_SESSION['access_token']
            // . '&app_id=426905939480875&app_secret=4bdb361ce417d136629ebdb93445c4cd'
        );
        $data = json_decode($data, true);
        $_SESSION['accounts'] = $data['data'];
        $_SESSION['accounts_length'] = count($data['data']);
        return true;
    }
    return [];
}

Route::get('/', function (Request $request) {
    $loginUrl = BuildLoginUrl();
    return view('home', [
        'loginUrl' => $loginUrl
    ]);
});

Route::any('/logout', function (Request $request) {
    if (
        isset($request['logout'])
        && $request['logout'] === 'logout'
    ) {
        session_destroy();
        unset($_SESSION['access_token']);
    }
    // header('Location : http://localhost:8000');
    $loginUrl = BuildLoginUrl();
    if (isset($request['code']))
        $_SESSION['access_token'] = $request['code'];
    return view('home', [
        'loginUrl' => $loginUrl
    ]);
});

Route::get('/facebook_callback', function (Request $request) {
    if (isset($request['access_token'])) {
        $_SESSION['access_token'] = $request['access_token'];
        LoadClientData();
        redirect()->to('http://localhost:8000')->send();
        exit;
    }
    return view('callback');
});
