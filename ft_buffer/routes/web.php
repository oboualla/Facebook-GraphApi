<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Facebook\Facebook;
use Illuminate\Support\Facades\DB;
use App\Mail\InformPosts;
use App\Mail\InformWeekly;
use Illuminate\Support\Facades\Mail;


// require $_SERVER['DOCUMENT_ROOT'] . '/../facebook_graph_api/index.php';
require '/app/facebook_graph_api/index.php';

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

function BuildLoginUrl()
{
    $fb_connection = new Facebook([
        'app_id' => '426905939480875',
        'app_secret' => '4bdb361ce417d136629ebdb93445c4cd',
        'default_graph_version' => 'v13.0'
    ]);

    $helper = $fb_connection->getRedirectLoginHelper();
    $login_url = $helper->getLoginUrl(env('APP_URL', 'http://localhost:8000') . '/facebook_callback');
    return $login_url . '&response_type=code%20token&scope=email,pages_show_list,pages_manage_cta,pages_read_engagement,pages_read_user_content,pages_manage_posts,pages_manage_metadata,pages_manage_instant_articles,pages_manage_engagement,email';
}

function LoadClientData()
{
    if (isset($_SESSION['access_token'])) {
        $GraphApi = new FbGraphApi(null, $_SESSION['access_token'], 1020202);
        // load pages that we manage
        // load client id, name
        $data = $GraphApi->LoadCurrentUser();
        $_SESSION['name'] = $data['name'];
        $_SESSION['email'] = $data['email'];
        $_SESSION['id'] = $data['id'];
        $GraphApi->setUserID($data['id']);
        // concat pages
        $data = $GraphApi->LoadPages();
        $_SESSION['accounts'] = $data['data'];
        $_SESSION['accounts_length'] = count($data['data']);

        // insert user into database if not exits
        $user = DB::select('select * from users where `user_id`=? limit 1', [$_SESSION['id']]);
        if (!count($user)) { // user not exit
            if (!DB::insert('insert into users (user_id, name, email) values (?,?,?)', [$_SESSION['id'], $_SESSION['name'], $_SESSION['email']])) {
                session_destroy();
                $_SESSION['_oauth_error'] = 'something went wrong, try again !!';
                redirect()->to(env('APP_URL', 'http://localhost:8000'))->send();
                exit;
            }
        }
    }
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
    redirect()->to(env('APP_URL', 'http://localhost:8000'))->send();
});

Route::get('/facebook_callback', function (Request $request) {
    if (isset($request['long_lived_token'])) {
        $_SESSION['access_token'] = $request['long_lived_token'];
        LoadClientData();
    } else if (isset($request['error']))
        $_SESSION['_oauth_error'] = $request['error_description'];
    if (isset($request['error']) || isset($request['long_lived_token'])) {
        redirect()->to(env('APP_URL', 'http://localhost:8000'))->send();
        exit;
    }
    return view('callback');
});

Route::post('/submit_post', function (Request $request) {
    $pageId = null;
    $access_token = null;
    // search for the target page id
    foreach ($_SESSION['accounts'] as $value) {
        if (isset($request[$value['id']]) && $request[$value['id']] == 'on') {
            $pageId = $value['id'];
            $access_token = $value['access_token'];
            break;
        }
    }
    // if not show a error
    if (!$pageId)
        $_SESSION['_error'] = 'no page has been selected';
    else if (!isset($request['post']) || $request['post'] == '') {
        $_SESSION['_error'] = 'too short post';
    } else if (isset($request['scheduled']) && $request['scheduled'] == 'on') {
        if (!isset($request['scheduled_date'])
            // || !preg_match('/^[\d]{4}\-[\d]{2}\-[\d]{2}$/', $request['scheduled_date'])
        ) {
            $_SESSION['_error'] = 'date is required';
        } else {
            DB::table('scheduled_jobs')->insert([
                'user_id' => $_SESSION['id'],
                'page_id' => $pageId,
                'message' => $request['post'],
                'email' => $_SESSION['email'],
                'name' => $_SESSION['name'],
                'access_token' => $access_token,
                'pushing_date' => $request['scheduled_date']
            ]);
            $_SESSION['_success'] = 'Operation success, the post will be add on  ' . $request['scheduled_date'];
        }
    } else {
        // creating of the post
        $status = false; // post status
        $GraphApi = new FbGraphApi($_SESSION['id'], $access_token, null);
        $response = $GraphApi->CreatePagePost($pageId, $access_token, $request['post']);
        if (!$response || isset($response['error']))
            $_SESSION['_error'] = 'unexpected error : ' . (isset($response['error']['message']) ? $response['error']['message'] : 'empty error');
        else if (!isset($response['id']))
            $_SESSION['_error'] = 'unexpected error : ' . 'empty error';
        else {
            $status = true;
            $_SESSION['_success'] = "Operation success, publication id (" . $response['id'] . ")";
        }
        Mail::to($_SESSION['email'])->send(new InformPosts([
            'name' => $_SESSION['name'],
            'status' => $status ? 'success' : 'error',
            'page_id' => $pageId
        ]));
    }
    redirect()->to(env('APP_URL', 'http://localhost:8000'))->send();
});

Route::get('/scheduled_posts', function () {
    if (!isset($_SESSION['access_token']))
        redirect()->to(env('APP_URL', 'http://localhost:8000'))->send();
    $data = DB::select('select id,user_id,page_id,message,pushing_date,status,created_at,updated_at from scheduled_jobs where `user_id`=? ORDER BY `updated_at` DESC', [$_SESSION['id']]);
    return view('scheduledPosts', [
        'data' => $data
    ]);
});


Route::post('/delete_post', function (Request $request) {
    if (!isset($request['id']))
        $_SESSION['_post_error'] = 'id is required';
    else if (!preg_match('/^[\d]{1,200}$/', $request['id']))
        $_SESSION['_post_error'] = 'id must be integer';
    if (DB::delete('Delete from scheduled_jobs where id=? and user_id=? limit 1', [$request['id'], $_SESSION['id']]))
        $_SESSION['_post_success'] = 'schedule ' . $request['id'] . ' is deleted successfully.';
    else
        $_SESSION['_post_error'] = 'schedule ' . $request['id'] . ' not found.';
    redirect()->to(env('APP_URL', 'http://localhost:8000') . '/scheduled_posts')->send();
});

Route::post('/retry_post', function (Request $request) {
    if (!isset($request['id']))
        $_SESSION['_post_error'] = 'id is required';
    else if (!preg_match('/^[\d]{1,200}$/', $request['id']))
        $_SESSION['_post_error'] = 'id must be integer';
    if (DB::update('update scheduled_jobs set status=1 where id=? and user_id=? limit 1', [$request['id'], $_SESSION['id']]))
        $_SESSION['_post_success'] = 'schedule ' . $request['id'] . ' is updated successfully.';
    else
        $_SESSION['_post_error'] = 'schedule ' . $request['id'] . ' not found.';
    redirect()->to(env('APP_URL', 'http://localhost:8000') . '/scheduled_posts')->send();
});
