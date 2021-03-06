<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Peter Wegrzynek
 * Date: 20.11.13
 * Time: 15:19
 * To change this template use File | Settings | File Templates.
 */

class controller {


    public static function createCategory($category) {
        $null = NULL;
        $sql = "INSERT INTO categories (
        Category
        ) VALUES (
        :category
        )";
        try{
            $db = dbConnect::getConnection();
            $stmt = $db->prepare($sql);
            $stmt->bindParam(":category", $category);
            $stmt->execute();
            echo "update gut";
        } catch(PDOException $e) {
            echo '{"error":{"text":'. $e->getMessage() .'}}';
        }
    }

    public static function createGroup($groupName) {
        //$arr = array('ba' => $groupName);
        //$arr = json_encode($groupName);
        $anzahlElements = (count($groupName['group']));
        $gruppenName = $groupName['group']['groupName'];
        for($i = 0; $i < $anzahlElements - 1; $i++) {
            $catName = ($groupName['group'][$i]);
            $catId = controller::getIdFromCategoryName($catName);
            $null = NULL;

            $sql = "INSERT INTO  categorygroup  ( groupname , categoryname, category_id)
                    SELECT * FROM (SELECT :groupName, :catName, :catId) AS tmp
                    WHERE NOT EXISTS (
                        SELECT groupname , categoryname FROM categorygroup WHERE groupname = :groupName AND categoryname = :catName
                    ) LIMIT 1;";
            try{
                $db = dbConnect::getConnection();
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":groupName", $gruppenName);
                $stmt->bindParam(":catName", $catName);
                $stmt->bindParam(":catId", $catId);
                $stmt->execute();
                echo "update gut";
            } catch(PDOException $e) {
                echo '{"error":{"text":'. $e->getMessage() .'}}';
            }
        }
    }

    public static function getAllGroups() {
        $sql = "SELECT * FROM categorygroup GROUP BY groupname";
        try{
            $dbh = dbConnect::getConnection();
            $stmt = $dbh->query($sql);
            $msg = $stmt->fetchAll(PDO::FETCH_OBJ);
            $dbh = null;

            echo '{"groups":'.json_encode($msg).'}';

        } catch(PDOException $e) {
            echo $e;
        }
    }



    public static function showCategories(){
    $sql = "SELECT * FROM categories";
        try{
            $dbh = dbConnect::getConnection();
            $stmt = $dbh->query($sql);
            $msg = $stmt->fetchAll(PDO::FETCH_OBJ);
            $dbh = null;
            echo '{"categories":'.json_encode($msg).'}';

        } catch(PDOException $e) {
            echo $e;
        }
    }

    public static function getNewItemsFromJSON($json_object_input){
        $json_object = json_decode( $json_object_input, true);
        $anzahlItems = sizeof($json_object['entries']);
        for($i = 0; $i<$anzahlItems; $i++) {
        //{"entries":[{"date":"2014-01-07 14:01:46","categoryName":"Klo","value":250}]}

        $amount =  $json_object['entries'][$i]['value'];
        $categoryName =  $json_object['entries'][$i]['categoryName'];
        $datum =  $json_object['entries'][$i]['date'];
        $user_id = 0;

            if(!controller::isCategoryExistend($categoryName)){
                controller::createCategory($categoryName);
            }
            $categoryId = controller::getIdFromCategoryName($categoryName);
            $sql = "INSERT INTO entry (
                    category_id,
                    amount,
                    datum,
                    user_id
                    ) VALUES (
                    :categoryId,
                    :amount,
                    :datum,
                    :userId
                    )";
            try{
                $db = dbConnect::getConnection();
                $stmt = $db->prepare($sql);
                $stmt->bindParam(":categoryId", $categoryId);
                $stmt->bindParam(":amount", $amount);
                $stmt->bindParam(":datum", $datum);
                $stmt->bindParam(":userId", $user_id);
                $stmt->execute();
                echo "update gut";
            } catch(PDOException $e) {
                echo '{"error":{"text":'. $e->getMessage() .'}}';
            }

        }
    }

    public static function showTest() {
        echo "routing funktioniert schonmal";
    }

    public static function checkJsonError($json) {
        json_decode($json);
        $json_errors = array(
            JSON_ERROR_NONE => 'No error has occurred',
            JSON_ERROR_DEPTH => 'The maximum stack depth has been exceeded',
            JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
            JSON_ERROR_SYNTAX => 'Syntax error',
        );
        echo 'Last error : ', $json_errors[json_last_error()], PHP_EOL, PHP_EOL;
    }

    /**
     * @param $category
     * returns true if categroy already in Database
     */
    public static function isCategoryExistend($categoryName){
        $sql = "SELECT * FROM categories WHERE Category LIKE '".$categoryName."'";
        try{
            $dbh = dbConnect::getConnection();
            $stmt = $dbh->query($sql);
            $msg = $stmt->fetchAll(PDO::FETCH_OBJ);
            $dbh = null;

            if(sizeof($msg) > 0)
                return true;
            else
                return false;

        } catch(PDOException $e) {
            echo $e;
        }
    }

    public static function getIdFromCategoryName($categoryName){
        $sql = "SELECT Id FROM categories WHERE Category LIKE '".$categoryName."'";
        try{
            $dbh = dbConnect::getConnection();
            $stmt = $dbh->query($sql);
            $msg = $stmt->fetchAll(PDO::FETCH_OBJ);
            $dbh = null;

           return $msg[0]->Id;

        } catch(PDOException $e) {
            echo $e;
        }
    }
}