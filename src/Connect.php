<?php
/**
 * All rights reserved.
 * User: hello@mimidots.com
 * Date: 29-Oct-17
 * Time: 09:42
 */

namespace QueryBuilder;

use Dotenv\Dotenv;
use PDO;
use PDOException;


class Connect
{

    /**
     * @var PDO
     */
    private static $conn;
    /**
     * @var Response
     */
    public static $response;

    /**
     * Connect constructor.
     */
    public function __construct()
    {
        static::$response=new Response(QueryBuilderResponses::SUCCESS_STATUS,200,'success');
    }


    private static function openConnection()
    {
        $dotenv = Dotenv::create(getcwd()); //load .env file from source root folder
        $dotenv->load();

        /*ENSURE THE FOLLOWING ARE PROVIDED*/
        $dotenv->required(['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USERNAME', 'DB_PASSWORD']);

        /*CHECK TO ENSURE THERE ARE NOT EMPTY*/
        $dotenv->required('DB_HOST')->notEmpty();
        $dotenv->required('DB_PORT')->notEmpty();
        $dotenv->required('DB_NAME')->notEmpty();

        /*CHECK IF PORT IS AN INTEGER*/
        $dotenv->required('DB_PORT')->isInteger();


        try {
            self::$conn = new PDO("mysql:host=" . $_SERVER["DB_HOST"] . ':' . $_SERVER["DB_PORT"] . ';dbname=' . $_SERVER["DB_NAME"], $_SERVER["DB_USERNAME"], $_SERVER["DB_PASSWORD"]);

            // set the PDO error mode to exception
            self::$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            //set it to use native prepared queries
            self::$conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        } catch (PDOException $e) {

            if($e->getCode()==2002){
                self::$response=QueryBuilderResponses::dbConnectionLost();
            }
            elseif($e->getCode()==1044 ||$e->getCode()==1045){
                self::$response=QueryBuilderResponses::incorrectDBCredentials();
            }else{
                self::$response=QueryBuilderResponses::genericDBConnectionError();
                self::$response->setSystemErrorCode($e->getCode());
                self::$response->setSystemErrorMessage($e->getMessage());
            }

        }
    }


    /**
     * @return PDO|null
     */
    protected static function getConn()
    {
        self::openConnection();
        return self::$conn;
    }

    /**
     * returns json encoded data. Used to terminate execution of statements and or return data
     * @param $data:optional
     *
     * @return string
     */
    protected static function terminate($data = null)
    {
        if (static::$response->getStatus() === QueryBuilderResponses::ERROR_STATUS) {
            return json_encode(static::$response);
        }
        return json_encode($data);

    }
}