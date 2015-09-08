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






$app->run(); 
?>