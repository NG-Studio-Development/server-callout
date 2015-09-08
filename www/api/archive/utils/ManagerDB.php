<?
ini_set("display_errors", "1");
error_reporting(E_ALL);
require_once 'php-activerecord/ActiveRecord.php';
require_once 'model/tables/RentTable.php';

class ManagerDB {
    
    function __construct() {
        
        $this->exhibitSettings();
        
        ActiveRecord\Config::initialize(function($cfg){
            $cfg->set_model_directory('model');
            $cfg->set_connections(array('development' => 'mysql://prokatvros:prokatvros2015@localhost/prokatvros'));
        });
    }

    
    private function exhibitSettings() {
        ini_set("display_errors", "1");
        error_reporting(E_ALL);
    }

    public function checkAuthData($login, $pass) {
        $admin = Admin::find( array('login'=>$login, 'pass'=>$pass) );
        
        //if (is_null($admin)) 
            //return FALSE;

        return !is_null($admin);        
    }

    public function authAdmin($login, $pass) {
        
        /*$admin = Admin::find( array('login'=>$login, 'pass'=>$pass) );
        
        if (is_null($admin)) {
            echo json_encode( array('Error'=>'Invalid login or password') );
            return;
        }*/
        
        if(!checkAuthData($login, $pass)) {
            echo json_encode( array('Error'=>'Invalid login or password') );
            return;
        }
        
        $pointcontrArr = Pointcontract::all( array('idAdmin'=>$admin->id) ); 
        $optionsPoints = array();
        
        if (count($pointcontrArr) == 0) {
            echo json_encode(array('Error'=>'Administrator not attached not to one point'));
            return;
        }
        
        foreach ($pointcontrArr as $point) {
            array_push($optionsPoints, $point->idpoint);
        }
        
        $pointsArr = Point::find($optionsPoints); 
        
        $resultPointArr = array();
        
        foreach ($pointsArr as $point) {
            array_push( $resultPointArr, array('id'=>$point->id, 'title'=>$point->title, 'address'=>$point->address) );
        }
        
        $resultArr = array( 'id'=>$admin->id,
                            'name'=>$admin->name,
                            'email'=>$admin->email );
        
        $resultArr['points'] = $resultPointArr; 
        
        echo json_encode($resultArr); 
        
    }
    
    public function reqAcceptShift($idPoint, $idAdmin) {
        $accept_date = date("d-m-Y H:m:s", time());
        
        $shift = Shift::create(array('idAdmin'=>$idAdmin, 'idPoint'=>$idPoint, 'accept_date'=>$accept_date));
        
        if ( !is_null($shift) )
            echo json_encode( array( 'accept_date'=>$accept_date ) );
        else 
            echo json_encode( array( 'error'=>'Unable to make a record' ) );
       
    }
    
    public function reqGetClients($phone) {
        $phone = str_replace(' ', '', $phone);
        
        $clientArr = Client::find('all', array('conditions' => "phone LIKE '%$phone%'"));//find( array('phone'=>$phone) );

        if ( !is_null($clientArr) ) {
                
            $resultArr = array();                
                
            foreach ($clientArr as $client) {
                
               $clientName = mb_convert_encoding($client->name, 'UTF-8', 'WINDOWS-1251');
                
               $rentArr = Rent::all( array('idClient'=>$client->id) );
        
                $resultClientArr = array('id'=>$client->id, 'name'=>$clientName, 'phone'=>$client->phone, 'sex'=>$client->sex); 
                $resultRentArr = array();

                foreach ( $rentArr as $rent ) {
                    $completed = $this->toBooleanType( $rent->completed );                
                    array_push( $resultRentArr, array('id'=>$rent->id, 'completed'=>$completed) );
                }
                
                $resultClientArr['rents'] = $resultRentArr;
               array_push($resultArr, $resultClientArr);
               
            }

            $jsonString = json_encode(array('client'=>$resultArr),JSON_UNESCAPED_UNICODE);
            
            echo mb_convert_encoding($jsonString, 'WINDOWS-1251', 'UTF-8'); 
            //echo json_encode(array('client'=>$resultArr)); 
        } else {
            echo json_encode(array('error'=>'Not found client by number'));
        } 
    }
    
    public function reqGetInventory($idPoint) {
            
        $inventoriesArr = Inventories::all( array('idPoint'=>$idPoint) );
        
        $resultArr = array();
        
        foreach ($inventoriesArr as $inventory) {
                
            $rent = Rent::last( array('idInventory'=>$inventory->id) );
            $rentsResultArr = array();
            
            $comleted = null; 
            $rentId = -1;
            
            if ( !is_null($rent) && !is_null($rent->completed) ) {
                $comleted = $this->toBooleanType($rent->completed);
                $rentId = $rent->id;
            }
            
            $inventory->model = mb_convert_encoding($inventory->model, 'UTF-8', 'WINDOWS-1251');

            array_push( $rentsResultArr, array( 'id'=>$rentId, 'comleted'=>$comleted ) );                    
            array_push($resultArr, array('id'=>$inventory->id, 'model'=>$inventory->model, 'number'=>$inventory->number, 'rents'=>$rentsResultArr) );

        } 

        $jsonString = json_encode(array('inventories'=>$resultArr));
        //$jsonString = json_encode(array('inventories'=>$resultArr),JSON_UNESCAPED_UNICODE);
        echo $jsonString; 
        //echo mb_convert_encoding($jsonString, 'WINDOWS-1251', 'UTF-8');
    }
    
    
    public function reqAddRents($idPoint, $jsonListRents) {
        $rentsArr = json_decode($jsonListRents, TRUE); 
        
        $jsonLastError = json_last_error();
  
        if ($jsonLastError != JSON_ERROR_NONE) {
            showJsonError($jsonLastError);
            echo "<br><br>".$rentsJsonString."<br><br>";
            return;
        } 
        
         foreach ($rentsArr['rents'] as $rent) {

              if( !is_null( $rent ) 
                    && !is_null( $rent['client'] )
                    && !is_null( $rent['administrator'] ) 
                    && !is_null( $rent['inventory'] ) ) {
                        
                $client = Client::create( array( 'guid'=>$rent['client']['id'],
                                    'name'=>$rent['client']['name'], 
                                    'phone'=>$rent['client']['phone'],
                                    'sex'=>$rent['client']['sex'] ) );
             
                $rent = Rent::create( array( 'id'=>$rent['id'], 
                               'start'=>$rent['start'],
                               'end'=>$rent['end'],
                               'note'=>$rent['note'],
                               'idAdmin'=>$rent['administrator']['id'],
                               'idClient'=>$rent['client']['id'],
                               'idInventory'=>$rent['inventory']['id'] ) );
                               
                var_dump($rent); 
                                                        
           } else {
                echo "Error: Invalid json structure";
            } 
        } 
    }
    
    public function reqGetRents($idPoins, $date) {
                    
        $rentsArr = Rent::all( array('idPoints'=>$idPoins) );
        $resultArr = array();
        
        foreach($rentsArr as $rent) {
            
            $admin = Admin::find( array('id'=>$rent->idadmin) );
            $client = Client::find( array('id'=>$rent->idclient) );
            $inventory = Inventories::find( array('idRents'=>$rent->id) );
            
            $tarif = Tarif::find( array('id'=>$inventory->idtarif) );
            
            $resultTarifArr = array('id'=>$tarif->id, 'sum_per_hour'=>$tarif->sum_per_hour);
            
            $resultAdminArr = array('id'=>$admin->id, 'name'=>$admin->name, 'email'=>$admin->email);
            $resultClientArr = array('id'=>$client->id, 'name'=>$client->name, "phone"=>$client->phone, "sex"=>$client->sex);
            $resultInventoryArr = array('id'=>$inventory->id, 'model'=>$inventory->model, 'number'=>$inventory->number, 'tarif'=>$resultTarifArr);    
            
            $startDate = $rent->start;//date_format($rent->start, 'Y-m-d H:i:s');
            $endDate =  $rent->end; //date_format($rent->end, 'Y-m-d H:i:s');
            
            if(!is_null($rent->start)) {
                $startDate = date_format($rent->start, 'Y-m-d H:i:s');
            }
            
            if (!is_null($rent->end)) {
                $endDate = date_format($rent->end, 'Y-m-d H:i:s');
            }
            
            array_push( $resultArr, array( 'id'=>$rent->guid, 'start'=>$startDate, 'end'=>$endDate, 'note'=>$rent->note, 'administrator'=>$resultAdminArr, 'client'=>$resultClientArr, 'inventory'=>$resultInventoryArr ) );
        }

        echo json_encode(array("modified"=>"2015-11-23 22:23:00", "rents"=>$resultArr));  
        
    }
    
    
    public function reqGetRentsByInventory( $number ) {

        $inventory = Inventories::find( array('number'=>$number) );         
        
        $rents = Rent::all( array('idInventory'=>$inventory->id) );
        
        $rents = $this->buildRents($rents);
        
        return $rents;
    }
    
   
    
    
    public function reqGetRentsByClient( $phone ) {

        $client = Client::find( array('phone'=>$phone) );            
        
        $rents = Rent::all( array('idClient'=>$client->id) );
        
        
        $rents = $this->buildRents($rents);
        
        return $rents;
    }
    
    public function reqGetRentsByDate( $date ) {
        
        $date = new ActiveRecord\DateTime($date);    
        
        $shift = Shift::find( array('shiftDate'=>$date) );            
        
        $rents = Rent::all( array('idShift'=>$shift->id) );
        
        
        $rents = $this->buildRents($rents);
        
        return $rents;
    }
    
    public function reqCountRents() {
        $numRows = Rent::find_num_rows();
        return $numRows[0]->num_rows;
    }
    
    public function reqGetAllRents() {
        
        $rents = Rent::find('all', array('limit' => 15000));
        
        $rents = $this->buildRents($rents);
        
        return $rents;
    }
    
    private function buildRents($rents) {
        //var_dump($rents[0]);     
        
        $tableArr = array();
        
        
        foreach($rents as $rent) {
            $rentTable = new RentTable();
            //$tableRowArr = array();               
           //echo "idClient: ".$rent->idclient."<br>";
            $client = $this->reqGetClientById($rent->idclient);
            $inventories = $this->reqGetInventoriesById($rent->idinventory);             
            $shift = $this->reqGetShiftById($rent->idshift);
            
            
            if ($client != null) {
                //echo "name: ".$client->name."<br>";
                //echo "phone: ".$client->phone."<br>";
                
                $rentTable->clientName = $client->name;
                $rentTable->clientPhone = $client->phone;                  
            }
            
            if ($inventories != null) {
                //echo "number: ".$inventories->number."<br>";
                //echo "model: ".$inventories->model."<br>";
                
                $rentTable->inventoryNumber = $inventories->number;
                $rentTable->inventoryModel = $inventories->model;   
                
            } 
            
            if ($shift != null)
                //echo "shift: ".$shift->shiftdate."<br>";
                $rentTable->shiftDate = $shift->shiftdate;
            else 
                //echo "no date";
            
            //var_dump($rent);
            //echo "expense: ".$rent->expense."<br>";
            $rentTable->expense = $rent->expense;
            
            array_push( $tableArr, $rentTable );

        }
        
        return $tableArr;   
    }
    
    private function reqGetClientById($id) {
        
        try {
                
            if($id == -1)
                return null;
            else 
                return Client::find($id);
                
        } catch(\exception $e) {
                //echo $e->getMessage()."<br>";
                return null;
          }  
        
    }
    
    private function reqGetInventoriesById($id) {
        
        try {
                
            if($id == -1)
                return null;
            else 
                return Inventories::find($id);
                
        } catch(\exception $e) {
                //echo $e->getMessage()."<br>";
                return null;
         }  
        
    }
    
    private function reqGetShiftById($id) {
        
        try {
                
            if($id == -1)
                return null;
            else 
                return Shift::find($id);
                
        } catch(\exception $e) {
                //echo $e->getMessage()."<br>";
                return null;
         }  
        
    }
    
    public function reqLogout($idShift) {
            
        $shift = Shift::find( array('id'=>$idShift) );
        
        if ( !is_null($shift) ) {
            $shift->end_date = time();
            $shift->save();    
            echo json_encode( array('result'=>'Success') );
        } else {
            echo json_encode( array('result'=>'Error, can not found shift') ); 
        }
    }
    
    private function toBooleanType($integerValue) {
        return $integerValue != 0;
    }
    
    function showJsonError($json_last_error) {
        
        switch ($json_last_error) {
            case JSON_ERROR_NONE:
                //echo ' - Ошибок нет!!!!!!!!!!';
                //$isValid = TRUE;
            break;
            case JSON_ERROR_DEPTH:
                echo ' - Достигнута максимальная глубина стека';
            break;
            case JSON_ERROR_STATE_MISMATCH:
                echo ' - Некорректные разряды или не совпадение режимов';
            break;
            case JSON_ERROR_CTRL_CHAR:
                echo ' - Некорректный управляющий символ';
            break;
            case JSON_ERROR_SYNTAX:
                echo ' - Синтаксическая ошибка, не корректный JSON';
            break;
            case JSON_ERROR_UTF8:
                echo ' - Некорректные символы UTF-8, возможно неверная кодировка';
            break;
            default:
                echo ' - Неизвестная ошибка';
            break;
        }
        
        //return $isValid;    
    }
    
    
    public function parseTarifs($inventory) {
        Tarif::create(array('name'=>$inventory[0],'sum_per_hour'=>$inventory[1], 'sum_day'=>$inventory[2], 'sum_ts_hour'=>$inventory[3] ));
    }
    
    public function parseClients($client) {

        //echo mb_convert_encoding($client[1], 'WINDOWS-1251', 'UTF-8');;
        $clientName = mb_convert_encoding($client[1], 'WINDOWS-1251', 'UTF-8');
        try {
            Client::create(array('name'=>$clientName, 'phone'=>$client[0]));    
            echo "name: ".$client[1]." phone: ".$client[0]." =========================================<br>";
        } catch (\exception $e) {
            if (empty($client[1]))
                $client[1] = 'EMPTY';                
            echo "Duplicate name: ".$client[1]." phone: ".$client[0]."<br>";
        }          

    }
    
    
    public function searchDate($string) {
        $date = preg_replace("/[^0-9,.]/", "", $string );                    
                    
                if (strpos($date,'2014') !== false || strpos($date,'2015') !== false) {
                
                } else {
                    $date = $date."2015";
                } 
        try {
            return new ActiveRecord\DateTime($date);    
        } catch(\exception $e) {
            return null;
        }
                        
    }
    
    
    public function parseRents( $rents ) {
                  
       try {
            $client = Client::find(array('phone'=>$rents[7]));
            $inventory = Inventories::find( array('number'=>$rents[3]) );
            $shift = null;
            
            $date = $this->searchDate( $rents[0] );
            
            if(!is_null($date) )
                $shift = Shift::find(array('shiftDate'=>$date));
             
            
            $idInventory = -1;
            $idClient = -1;
            $idShift = -1;
            
            if ( !is_null($inventory) )
                 $idInventory = $inventory->id;
            
            if ( !is_null($client) )
                $idClient = $client->id;
            
            if ( !is_null($shift) ) {
                $idShift = $shift->id;                                        
                //echo $idShift."<br>";
            }
                
            
             Rent::create( array( 'start'=>$rents[1], 
                                    'end'=>$rents[2],
                                    'idInventory'=>$idInventory,
                                    'idClient'=>$idClient,
                                    'expense'=>$rents[22],
                                    'idShift'=>$idShift ) );  
            
        } catch (\exception $e) {
            //echo "Exception in parseRents <br>";
            echo $e->getMessage()."<br>";
        }    

    }
    
    public function parseInventory($inventories) {

        echo "Was commented<br>";

        /*print_r($inventories);
        
        $tarif = Tarif::find(array('conditions'=>"name LIKE '%$inventories[2]%'"));
        
        try {
            Inventories::create(array('number'=>$inventories[0], 'model'=>$inventories[1], 'idTarif'=>$tarif->id));    
        } catch (\exception $e) {
            echo "Exception";
        } */    
    }       
    
    public function parseAdmins($admins) {

        //echo "Was commented";
        //echo $admins[0]."<br>";
        foreach ($admins as $admin) {
            //echo $admin."<br>";
            try {
                Admin::create(array('name'=>$admin));    
            } catch (\exception $e) {
                echo "Exception";
            }
            
        }
        //print_r($admins);
        
        
    }  
    
    
    public function parseShift($acceptDate) {

        echo $acceptDate."<br>";
        
        
        
        try {
            Shift::create(array('shiftDate'=>$acceptDate));    
            
        } catch (\exception $e) {
                echo "Exception of create order in Shifts <br>";
        }
        
        /*foreach ($shifts as $shift) {
            try {
                //Admin::create(array('name'=>$admin));    
            
            } catch (\exception $e) {
                echo "Exception";
            }
            
        }*/
        
    } 
    
    
    public function parseInventoryNotMark($inventories) {
        
        $a = "You are cat";
        
        if (strpos($a,'are') !== false) {
            echo 'true';
        }
      
    }  
    
    public function createDebugOrder() {
    }
    
};



?>