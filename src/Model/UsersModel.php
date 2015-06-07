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
                      AND users.id = :id';
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
    public function goEditUser($id)
    {
        if (($id != '') && ctype_digit((string)$id)) {
            $query = 'SELECT id, name, surname, email, website, facebook
                      FROM users_data
                      WHERE id = :id';
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
        $query = 'SELECT users.id, login, name, surname, avatar
                  FROM users, users_data
                  WHERE users.id = users_id
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
        $sql = 'SELECT COUNT(*) as pages_count FROM users';
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
            return $this->db->delete('users', array('id' => $id));
        }
    }

     /* Delete user's data.
     *
     * @access public
     * @param array $album Album data
     * @retun mixed Result
     */
    public function deleteDetails($data)
    {
        if (isset($data['id'])
            && ($data['id'] != '')
            && ctype_digit((string)$data['id'])) {
            // delete record
            $id = $data['id'];
            return $this->db->delete('users_data', array('id' => $id));
        }
    }
}

