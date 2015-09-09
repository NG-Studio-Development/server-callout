<?

class cms_users extends ActiveRecord\Model {
    
    public static function all_by_category($categoryId) {
        $table_name = "cms_users";            
        $table_items_name = "cms_map_items";    
                
        return self::find_by_sql("SELECT * FROM ".$table_name." WHERE id IN (SELECT user_id FROM ".$table_items_name." WHERE category_id = ?)", array($categoryId) );
    }
    
};

?>