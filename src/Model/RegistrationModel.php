<?php
/**
 * Registration model.
 *
 * @category Model
 * @author Grzegorz Stefański
 * @link wierzba.wzks.uj.edu.pl/~13_stefanski/php
 * @copyright EPI 2015
 */

namespace Model;

use Doctrine\DBAL\DBALException;
use Silex\Application;

/**
 * Class RegistrationModel.
 *
 * @package Model
 * @author Grzegorz Stefański
 * @link wierzba.wzks.uj.edu.pl/~13_stefanski/php
 * @uses Doctrine\DBAL\DBALException;
 * @uses Silex\Application
 */
class RegistrationModel
{
    /**
     * Db object.
     *
     * @access protected
     * @var Silex\Provider\DoctrineServiceProvider $db
     */
    protected $db;

    /**
     * Object constructor.
     *
     * @access public
     * @param Silex\Application $app Silex application
     */
    public function __construct(Application $app)
    {
        $this->db = $app['db'];
    }

     /* Add user.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param array $data Registration data
     * @retun mixed Result
     */
    public function addUser($app, $data)
    {
        if ($data['password'] === $data['confirm']) {
            unset($data['confirm']);
            $data['password'] = $app['security.encoder.digest']
                ->encodePassword($data['password'], '');
            $data['role_id'] = 2;
            return $this->db->insert('users', $data);
        } else {
            return array();
        }
    }

    /**
     * Gets user's ID.
     *
     * @access public
     * @return mixed Result
     */
    public function getUserId()
    {
        try {
            $query = '
                SELECT
                    id AS users_id
                FROM
                    users
                ORDER BY
                    users_id DESC
                LIMIT 1
            ';
            return current($this->db->fetchAll($query));
        } catch (\PDOException $e) {
            throw $e;
        }
    }

     /* Add user's data.
     *
     * @access public
     * @param array $data Registration data
     * @retun mixed Result
     */
    public function addUserData($data)
    {
        return $this->db->insert('users_data', $data);
    }

    /**
     * Checks if user's login is unique.
     *
     * @access public
     * @param array $data Registration data
     * @return array Result
     */
    public function isUnique($data)
    {
        try {
            $query = '
	            SELECT 
                    COUNT(login) as isUnique
                FROM 
                    users
                WHERE
                    login = :login
	        ';
            $statement = $this->db->prepare($query);
            $statement->bindValue('login', $data['login'], \PDO::PARAM_STR);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result[0]['isUnique'] > 0 ? array() : $result;
        } catch (\PDOException $e) {
            throw $e;
        }
    }
}
