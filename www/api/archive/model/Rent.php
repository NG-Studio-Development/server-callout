<?
class Rent extends ActiveRecord\Model {
    
    public static function find_num_rows() {
        return self::find('all', array('select' => 'count(*) AS num_rows'));
    }
    
    
};
?>