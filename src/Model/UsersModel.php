<?php
/**
 * Users model.
 *
 * @link http://epi.uj.edu.pl
 * @author epi(at)uj(dot)edu(dot)pl
 * @copyright EPI 2015
 */

namespace Model;

use Silex\Application;

class UsersModel
{
    /**
     * Db object.
     *
     * @access protected
     * @var Silex\Provider\DoctrineServiceProvider $_db
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

    /**
     * Gets single user data.
     *
     * @access public
     * @param integer $id Record Id
     * @return array Result
     */
    public function getUser($id)
    {
        if (($id != '') && ctype_digit((string)$id)) {
            $query = 'SELECT users.id, avatar, login, name, surname, email, website, facebook
                      FROM users, users_data
                      WHERE users.id = users_id
                      AND users.id = :id
                      AND del = 0';
            $statement = $this->db->prepare($query);
            $statement->bindValue('id', $id, \PDO::PARAM_INT);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return !$result ? array() : current($result);
        } else {
            return array();
        }
    }

    /**
     * Gets user data to edit.
     *
     * @access public
     * @param integer $id Record Id
     * @return array Result
     */
    public function getUserDetails($id)
    {
        if (($id != '') && ctype_digit((string)$id)) {
            $query = 'SELECT users_data.id, name, surname, email, website, facebook
                      FROM users_data, users
                      WHERE users.id = users_data.id
                      AND users.id = :id
                      AND del = 0';
            $statement = $this->db->prepare($query);
            $statement->bindValue('id', $id, \PDO::PARAM_INT);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return !$result ? array() : current($result);
        } else {
            return array();
        }
    }

    /**
     * Get all users on page.
     *
     * @access public
     * @param integer $page Page number
     * @param integer $limit Number of records on single page
     * @retun array Result
     */
    public function getUsersPage($page, $limit)
    {
        $query = 'SELECT users.id, role_id, login, name, surname, avatar
                  FROM users, users_data
                  WHERE users.id = users_id
                  AND del = 0
                  LIMIT :start, :limit';
        $statement = $this->db->prepare($query);
        $statement->bindValue('start', ($page-1)*$limit, \PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return !$result ? array() : $result;
    }

    /**
     * Counts user pages.
     *
     * @access public
     * @param integer $limit Number of records on single page
     * @return integer Result
     */
    public function countUsersPages($limit)
    {
        $pagesCount = 0;
        $sql = 'SELECT COUNT(*) as pages_count FROM users WHERE del = 0';
        $result = $this->db->fetchAssoc($sql);
        if ($result) {
            $pagesCount = ceil($result['pages_count']/$limit);
        }
        return $pagesCount;
    }

    /**
     * Returns current page number.
     *
     * @access public
     * @param integer $page Page number
     * @param integer $pagesCount Number of all pages
     * @return integer Page number
     */
    public function getCurrentPageNumber($page, $pagesCount)
    {
        return (($page <= 1) || ($page > $pagesCount)) ? 1 : $page;
    }

    /**
     * Gets users for pagination.
     *
     * @access public
     * @param integer $page Page number
     * @param integer $limit Number of records on single page
     *
     * @return array Result
     */
     public function getPaginatedUsers($page, $limit)
     {
         $pagesCount = $this->countUsersPages($limit);
         $page = $this->getCurrentPageNumber($page, $pagesCount);
         $users = $this->getUsersPage($page, $limit);
         return array(
             'users' => $users,
             'paginator' => array('page' => $page, 'pagesCount' => $pagesCount)
         );
     }

     /* Save album.
     *
     * @access public
     * @param array $album User data
     * @retun mixed Result
     */
    public function editUser($user)
    {
        if (isset($user['id'])
            && ($user['id'] != '')
            && ctype_digit((string)$user['id'])) {
            // update record
            $id = $user['id'];
            unset($user['id']);
            return $this->db->update('users_data', $user, array('id' => $id));
        }
    }

     /* Delete user.
     *
     * @access public
     * @param array $album Album data
     * @retun mixed Result
     */
    public function deleteUser($user)
    {
        if (isset($user['id'])
            && ($user['id'] != '')
            && ctype_digit((string)$user['id'])) {
            // delete record
            $id = $user['id'];
            unset($user['id']);
            unset($user['name']);
            unset($user['surname']);
            unset($user['email']);
            unset($user['website']);
            unset($user['facebook']);
            $user['del'] = 1;
            return $this->db->update('users', $user, array('id' => $id));
        }
    }

    /**
     * Gets single user.
     *
     * @access public
     * @param integer $id Record Id
     * @return array Result
     */
    public function getSingleUser($login)
    {
        if ($login != '') {
            $query = 'SELECT users.id, role_id, login, name, surname, avatar
                      FROM users, users_data
                      WHERE users.id = users_id
                      AND login = :login
                      AND del = 0';
            $statement = $this->db->prepare($query);
            $statement->bindValue('login', $login, \PDO::PARAM_STR);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return !$result ? array() : current($result);
        } else {
            return array();
        }
    }

     /* Delete user.
     *
     * @access public
     * @param array $album Album data
     * @retun mixed Result
     */
    public function setGrants($user)
    {
        if (isset($user['id'])
            && ($user['id'] != '')
            && ctype_digit((string)$user['id'])) {
            // delete record
            $id = $user['id'];
            unset($user['id']);
            unset($user['name']);
            unset($user['surname']);
            unset($user['email']);
            unset($user['facebook']);
            unset($user['website']);
            return $this->db->update('users', $user, array('id' => $id));
        }
    }

    /**
     * Gets all roles.
     *
     * @access public
     * @return array Result
     */
    public function getRoles()
    {
        $query = 'SELECT id, name as role_id FROM roles';
        return $this->db->fetchAll($query);
    }
}

