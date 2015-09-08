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