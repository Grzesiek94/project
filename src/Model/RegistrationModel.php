<?php
/**
 * Registration model.
 *
 * @link http://epi.uj.edu.pl
 * @author epi(at)uj(dot)edu(dot)pl
 * @copyright EPI 2015
 */

namespace Model;

use Silex\Application;

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
     * @param array $data Registration data
     * @retun mixed Result
     */
    public function addUser($app, $data)
    {
        if ($data['password'] === $data['confirm']) {
            unset($data['confirm']);
            $data['password'] = $app['security.encoder.digest']
                ->encodePassword($data['password'],'');
            $data['role_id'] = 2;
            return $this->db->insert('users', $data);
        } else {
            return array();
        }
    }

    public function getUserId()
    {
        $query = 'SELECT id as users_id FROM users ORDER BY users_id DESC LIMIT 1';
        return current($this->db->fetchAll($query));
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
     * Gets information about best users.
     *
     * @access public
     * @return array Result
     */
    public function isUnique($data)
    {
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
    }
}
