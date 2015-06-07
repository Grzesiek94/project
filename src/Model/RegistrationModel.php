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
    public function addUser($data)
    {
        if ($data['role_id'] === '2') {
            return $this->db->insert('users', $data);
        }
    }

    public function getUserId()
    {
        $query = 'SELECT id as users_id FROM users ORDER BY users_id DESC LIMIT 1';
        return $this->db->fetchAll($query);
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
}

