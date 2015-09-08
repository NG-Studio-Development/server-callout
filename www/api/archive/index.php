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
        
        echo "Hello ".$name;
    }
);



$app->post(
    '/auth',
    function () use($app) {
      
      $paramLogin = $app->request->post('login'); 
      $paramPassword = $app->request->post('password');
      
      //var_dump($paramLogin);
        if (is_null($paramLogin) || is_null($paramPassword)) {
            echo json_encode( array( 'error'=>"Not found variables, you must send parameter with following name: 'login', 'password'" ) );                
        } else {
            $managerDB = new ManagerDB();
            $managerDB->authAdmin($paramLogin, $paramPassword);
        }
      
    }
);


$app->post(
    '/accept_shift',
    function () use($app) {
      
      $paramPoint = $app->request->post('point'); 
      $paramUserId = $app->request->post('user_id');
      
      if (is_null($paramPoint) || is_null($paramUserId)) {
            echo json_encode( array( 'error'=>"Not found variables, you must send parameter with following name: 'point', 'user_id'" ) );
      } else {
        $managerDB = new ManagerDB();
        $managerDB->reqAcceptShift($paramPoint, $paramUserId);    
      }
    }
);


$app->post(
    '/logout',
    function () use($app) {
      $paramIdShift = $app->request->post('shift_id'); 
      
      if ( is_null($paramIdShift) ) {
            echo json_encode( array( 'error'=>"Not found variables, you must send parameter with following name: 'shift_id'" ) );
      } else {
            $managerDB = new ManagerDB();
            $managerDB->reqLogout( $paramIdShift );          
      }
    }
);

$app->get(
    '/rents/:pointId/:date',
    function ($pontId, $date) {
      
      $managerDB = new ManagerDB();
      $managerDB->reqGetRents( $pontId, $date );
      
    }
);

$app->post(
    '/add_rents',
    function () use($app) {
      
      $paramId = $app->request->post('id'); 
      $rentsJsonString = $app->request->post('rents'); 
      
      if ( is_null( $paramId ) || is_null( $rentsJsonString ) ) {
            echo json_encode( array( 'error'=>"Not found variables, you must send parameter with following name: 'id', 'rents'" ) );
      } else {
            $managerDB = new ManagerDB();
            $managerDB->reqAddRents($paramId, $rentsJsonString);          
      }
                
    }
);

$app->get(
    '/clients/:phoneNumber',
    function ($phoneNumber) {
        
      $managerDB = new ManagerDB();
      $managerDB->reqGetClients($phoneNumber);        
        
    }
);

$app->get(
    '/inventory/:idPoint',
    function ($idPoint) {
        
      $managerDB = new ManagerDB();
      $managerDB->reqGetInventory($idPoint);        
        
    }
);


// ================================ //


$app->get(
    '/util/rents',
    function () {
        
        $pasrserCSV = new ParserCSV();
        $rentsArr = $pasrserCSV->rents();
        
        $managerDB = new ManagerDB();
        
        
        foreach( $rentsArr  as $rent ) {
            $managerDB->parseRents($rent);
        }
                                 
        //var_dump($arr);
        
    }
);

$app->get(
    '/util/tarifs',
    function () {
        
        $pasrserCSV = new ParserCSV();
        $tarifsArr = $pasrserCSV->tarifs();
        
        $managerDB = new ManagerDB();
        
        
        foreach( $tarifsArr as $tarifs ) {
            $managerDB->parseTarifs($tarifs);
        }
                                 
        //var_dump($arr);
        
    }
);


$app->get(
    '/util/clients',
    function () {
        $pasrserCSV = new ParserCSV();
        $clientsArr = $pasrserCSV->clients();
        $managerDB = new ManagerDB();

        foreach( $clientsArr as $client ) {
                
            if(!empty($client[0])) {
                $managerDB->parseClients($client);                   
            }                
        }
    }
);



$app->get(
    '/util/inventory',
    function () {
        $pasrserCSV = new ParserCSV();
        $inventoryArr = $pasrserCSV->inventory();
        $managerDB = new ManagerDB();

        foreach( $inventoryArr as $inventory ) {
            if ( !empty($inventory[0]) ) {
                $managerDB->parseInventory($inventory);                   
            }                
        }
    }
);


$app->get(
    '/util/shift',
    function () {
            
        //$pasrserCSV = new ParserCSV();
        //$inventoryArr = $pasrserCSV->inventory();
        //$managerDB = new ManagerDB();

        
        $testString = "12.322,11T";
        echo preg_replace("/[^0-9,.]/", "", $testString);
        
    }
);


$app->get(
    '/util/admin',
    function () {
        $pasrserCSV = new ParserCSV();
        $adminsArr = $pasrserCSV->admins(); 
         $managerDB = new ManagerDB();
        
        
        foreach( $adminsArr as $admin ) {
            if ( !empty($admin[0]) ) {
                $managerDB->parseAdmins($admin);                   
            }                
        }                    
    }
);



$app->get(
    '/util/shifts',
    function () {
        $pasrserCSV = new ParserCSV();
        $shiftsArr = $pasrserCSV->shifts(); 
        $managerDB = new ManagerDB();
        
        //print_r($shiftsArr);
        //echo preg_replace("/[^0-9,.]/", "", $testString);
        foreach( $shiftsArr as $shift ) {
                
            //echo preg_replace("/[^0-9,.]/", "", $shift[0])."<br>";
            
            
            try {
                
                //$date = strtotime( preg_replace("/[^0-9,.]/", "", $shift[0]));
                $date = preg_replace("/[^0-9,.]/", "", $shift[0]);                    
                    
                if (strpos($date,'2014') !== false || strpos($date,'2015') !== false) {
                
                } else {
                    $date = $date."2015";
                }  
                //echo preg_replace("/[^0-9,.]/", "", $shift[0])."<br>";
                //echo $date."<br>";
                
                //$shiftDate = strtotime( $shift[37]);//." ".$shift[37]."<br>";
                $managerDB->parseShift($date);
                  
            } catch (\exception $e) {
                echo "Exception";
            }
               
        }                    
    }
);

$app->get(
    '/util/inventoryNotMark',
    function () {
        $pasrserCSV = new ParserCSV();
        $inventoryArr = $pasrserCSV->inventory();
        $managerDB = new ManagerDB();


        /*foreach( $inventoryArr as $inventory ) {
            if ( !empty($inventory[0]) ) {
                $managerDB->parseInventory($inventory);                   
            }                
        }*/
    }
);



$app->run(); 
?>