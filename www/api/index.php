<?
ini_set("display_errors", "1");
error_reporting(E_ALL);

require 'Slim/Slim.php';
require 'utils/ManagerDB.php';
require 'utils/ParserCSV.php';


\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->get(
    '/hello/:name',
    function ($name) {
        
        // echo "Hello ".$name;
		$managerDB = new ManagerDB();
        $managerDB->debugManagerDB();
		
    }
);


$app->post(
    '/users',
    function () use($app) {
        
        $paramEmail = $app->request->post('email'); 
        $paramPassword = $app->request->post('password');
        $paramPasswordConfirm = $app->request->post('password_confirmation');
        $paramLogin = $app->request->post('login');
        
        
        //echo $paramEmail."<br>";
        //echo $paramPassword."<br>";
        //echo $paramPasswordConfirm."<br>";
        //echo $paramName."<br>";
        
        $managerDB = new ManagerDB();
        $managerDB->reqUsers($paramEmail, $paramPassword, $paramLogin);
        
    }
);

$app->post(
    '/tokens.json',
    function () use($app) {
        $paramEmail = $app->request->post('email'); 
        $paramPassword = $app->request->post('password');    
    
        $managerDB = new ManagerDB();
        $managerDB->reqTokensJson($paramEmail, $paramPassword);
        
    }
);




$app->get(
    '/distance/:lat/:lon',
    function ($lat, $lon) {
        //$paramEmail = $app->request->post('email'); 
        //$paramPassword = $app->request->post('password');    
    
        $managerDB = new ManagerDB();
        $managerDB->reqDistance($lat, $lon);

    }
);

//reqUserInfo
$app->get(
    '/user/info/:token',
    function ($token) {
        
        $managerDB = new ManagerDB();
        $managerDB->reqUserInfo($token);


    }
);

$app->get(
    '/user/info',
    function () {
        
        $managerDB = new ManagerDB();
        $managerDB->reqAllUserInfo();


    }
);


$app->get(
    '/user/:token/:status',
    function ($token, $status) {
        
        echo "switch status";
        $managerDB = new ManagerDB();
        //$managerDB->reqAllUserInfo();


    }
);


$app->run(); 
?>