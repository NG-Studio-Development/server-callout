<?
ini_set("display_errors", "1");
error_reporting(E_ALL);
require_once 'php-activerecord/ActiveRecord.php';
//require_once 'model/tables/RentTable.php';

class ManagerDB {
    
    function __construct() {
        
        $this->exhibitSettings();
        
        ActiveRecord\Config::initialize(function($cfg){
            $cfg->set_model_directory('model');
            $cfg->set_connections(array('development' => 'mysql://calloutv2:qwerty@localhost/calloutv2'));
        });
    }

    
    private function exhibitSettings() {
        ini_set("display_errors", "1");
        error_reporting(E_ALL);
    }

    public function debugManagerDB() {
        echo "In debugManagerDB";
		
		// $cms_users = cms_users::all( array('group_id'=>12) );
		$cms_users_izbran = cms_users_izbran::all( array('objekt_id'=>58) );
		
		var_dump($cms_users_izbran);
		
	}

    
    public function reqUsers( $email, $password, $login ) {
                
            
            $cms_users = new cms_users(array('email'=>$email, 'password'=>$password, 'login'=>$login));
            $cms_users->save();
            
            $cms_users = cms_users::find( array('email'=>$email, 'password'=>$password, 'login'=>$login) );
            
            $lat = null;
            $lng = null;
            
            $errorCode = 0;
            
            $daraArr = null; 
            
            if ( !is_null($cms_users) ) {
                $items = Items::find( array('user_id'=>$cms_users->id) );
                
                if ( !is_null($items) ) {
                    $lat = $items->lat;
                    $lng = $items->lng;
                }
                   
                   
                $daraArr = array( 'email'=>$cms_users->email,
                                'latitude'=>$lat,
                                'longitude'=>$lng,
                                'name'=>$cms_users->login,
                                'session_token'=>$cms_users->password );
                    
            } else {
                $errorCode = 1;
            }
                
            echo json_encode( array( 'data'=>$daraArr,'error_code'=>$errorCode) );
    }
   
   
   
   public function reqTokensJson( $email, $password ) {
                
            
            $cms_users = cms_users::find( array('email'=>$email, 'password'=>$password) );
            
            $lat = null;
            $lng = null;
            
            $resultCode = 1;
            
            $daraArr = null; 
            
            if ( !is_null($cms_users) ) {
                
                   
                   
                $daraArr = array( 'data'=>array('name'=>$cms_users->login),
                                   'token'=>$cms_users->password );
                    
            } else {
                $resultCode = 0;
            }
                
            echo json_encode( array( 'data'=>$daraArr, 'result'=>$resultCode ) );
    }
   
   public function reqDistance( $latitude, $longitude ) {
                
            $mainCatigoriesArr = Category::all( array('parent_id'=>1) );
            
            $categoryArr = array();

            
            foreach ($mainCatigoriesArr as $mainCategory) {

                
                $subCatigoriesArr = Category::all( array('parent_id'=>$mainCategory->id) );
                
                $subCategoryJsonArr = array();
                
                foreach ($subCatigoriesArr as $subCategory) {
                        
                    
                    //$cms_usersArr = cms_users::all_by_category(47); 
                    $cms_usersArr = cms_users::all_by_category($subCategory->id);
                    
                    //echo "<br>";
                    //var_dump($cms_usersArr);
                    //echo "<br>";
                    
                    $userArr = array();
                    
                    foreach ($cms_usersArr as $cms_user) {
                        array_push($userArr, array('login'=>$cms_user->login, 'nickname'=>$cms_user->nickname));
                    }
                    
                    
                    array_push ($subCategoryJsonArr, array('sub_category'=>$subCategory->title, 
                                             'sub_category_id'=>$subCategory->id,
                                              'users'=>$userArr));
                     
                    
                }    
                
                array_push($categoryArr, array( 'category_icon'=>$mainCategory->marker,
                                        'category_name'=>$mainCategory->title,
                                        'subcategories'=>$subCategoryJsonArr ) );
                
            } 
            
            $dataArr = array('data'=>$categoryArr);
            
            print_r($dataArr);
            
            //echo json_encode($dataArr);                    
            
    }
   
   
    public function reqUserInfo($token) {
        
        $cms_users = cms_users::find(array('login'=>$token));
        
        $callStatusUser = CallStatusUser::find(array('user_id'=>$cms_users->id));
        $item = Items::find(array('user_id'=>$cms_users->id));
        
        
        $callscount = null;
        $email = null;
        $id = null;
        $state = null;
        $createdAt = null;
        $averageRate = null;
        $error_code = 0;
        
        if ( !is_null($cms_users) ) {
                
            $email = $cms_users->email;
            $id = $cms_users->id;
            
            if ( !is_null($callStatusUser) ) {
                $callscount = $callStatusUser->total;    
            }
            
            if ( !is_null($item) ) {
               $state = $item->status;
               $createdAt = $item->pubdate;
               $averageRate = $item->comrating;
            }
             
        } else {
            $error_code = 1;
        }
        
        $jsonUserArr = array( "callscount"=>$callscount, 
                                "email"=>$email,
                                "id"=>$id,
                                "state"=>$state, 
                                "created_at"=>$createdAt);
                                
                                        
        
        $jsonDataArr = array('average_rate'=>$averageRate, 'user_info'=>$jsonUserArr, 'error_code'=>$error_code);
        
        echo json_encode(array('data'=>$jsonDataArr));
    }
   
    public function reqAllUserInfo() {
        
        $cms_usersArr = cms_users::all();
        
        $jsonDataArr = array();
        
        foreach($cms_usersArr as $cms_users) {
            $jsonUserArr = array();                
                
                
            $callStatusUser = CallStatusUser::find(array('user_id'=>$cms_users->id));   
            $item = Items::find(array('user_id'=>$cms_users->id));
            
            $callscount = null;
            $email = null;
            $id = null;
            $on_status = null;
            $createdAt = null;
            $averageRate = null;
            $description = null;
            $name = null;
            $cityId = null;
            $state = null;
            $error_code = 0;
            $latitude = null; 
            $longitude = null;
            
            
            $email = $cms_users->email;
            $id = $cms_users->id;
            $name = $cms_users->nickname;
            $phone = $cms_users->phone;
            $location = $cms_users->location;
            
            
            if ($cms_users->group_id == 12) 
                $state = "cпециалист";
            else 
                $state = "пользователь";
            
            
            if ( !is_null($callStatusUser) ) {
                $callscount = $callStatusUser->total;    
            }
            
            if ( !is_null($item) ) {
               $on_status = $item->on_status;
               $createdAt = $item->pubdate;
               $averageRate = $item->comrating;
               $description = $item->description;
               $cityId = $item->city_id;
               $latitude = $item->lat;
               $longitude = $item->lng;
            }
            
            $jsonUserInfoArr = array( 'calls_count'=>$callscount, 
                                                            'email'=>$email,
                                                            'id'=>$id,
                                                            'status'=>$on_status, 
                                                            'created_at'=>$createdAt, 
                                                            'description'=>$description,
                                                            'name'=>$name,
                                                            'location'=>$location, 
                                                            'telephone_number'=>$phone,
                                                            'city_id'=>$cityId,
                                                            'latitude'=>$latitude,
                                                            'longitude'=>$longitude,
                                                            'state'=>$state, 
                                                            
                                                            "oauth_expires_at"=> null,
                                                            "oauth_token"=> null,
                                                            "price"=> null,
                                                            "uid"=> null,
                                                            "provider"=> null,
                                                            "updated_at"=> "2014-08-18T14:52:11+04:00",
                                                            "discount"=> 0,
                                                            
                                                            "odnk_id"=> null,
                                                            "vk_id"=> null,
                                                            "y_id"=> null,
                                                            "fb_id"=> null,
                                                            "g_id"=> null );
            
            
            $jsonUserArr = array( 'user_info'=>$jsonUserInfoArr, 
                                    'reviews'=>array(),
                                    'average_rate'=>$averageRate,
                                    'favorites'=>array());
             
            
            array_push($jsonDataArr, $jsonUserArr); 
        }
        
        echo json_encode(array('error_code'=>0,'data'=>$jsonDataArr));
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
    
  
    
           
    
      
     
    
    
   
    
};



?>