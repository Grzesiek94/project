<?php
/**
 * Users model.
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
 * Class UsersModel.
 *
 * @package Model
 * @author Grzegorz Stefański
 * @link wierzba.wzks.uj.edu.pl/~13_stefanski/php
 * @uses Doctrine\DBAL\DBALException;
 * @uses Silex\Application
 */
class UsersModel
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

    /**
     * Gets single user data to view.
     *
     * @access public
     * @param integer $id User's Id
     * @return array Result
     */
    public function getUser($id)
    {
        if (($id != '') && ctype_digit((string)$id)) {
            try {
                $query = '
                    SELECT
                        users.id, 
                        avatar, 
                        login, 
                        name, 
                        surname, 
                        email, 
                        website, 
                        facebook
                    FROM
                        users, 
                        users_data
                    WHERE
                        users.id = users_id
                    AND
                        users.id = :id
                    AND
                        del = 0
                ';
                $statement = $this->db->prepare($query);
                $statement->bindValue('id', $id, \PDO::PARAM_INT);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                return !$result ? array() : current($result);
            } catch (\PDOException $e) {
                throw $e;
            }
        } else {
            return array();
        }
    }

    /**
     * Gets user data to edit, delete and set grants actions.
     *
     * @access public
     * @param integer $id User's Id
     * @return array Result
     */
    public function getUserDetails($id)
    {
        if (($id != '') && ctype_digit((string)$id)) {
            try {
                $query = '
                    SELECT
                        users_data.id, 
                        name, surname, 
                        email, 
                        website, 
                        facebook
                    FROM
                        users_data, 
                        users
                    WHERE
                        users.id = users_data.id
                    AND
                        users.id = :id
                    AND
                        del = 0
                ';
                $statement = $this->db->prepare($query);
                $statement->bindValue('id', $id, \PDO::PARAM_INT);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                return !$result ? array() : current($result);
            } catch (\PDOException $e) {
                throw $e;
            }
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
        try {
            $query = '
                SELECT 
                    users.id, 
                    role_id, 
                    login, 
                    name, 
                    surname, 
                    avatar
                FROM 
                    users,
                    users_data
                WHERE
                    users.id = users_id
               AND
                    del = 0
               ORDER BY 
                    login
               LIMIT :start, :limit
            ';
            $statement = $this->db->prepare($query);
            $statement->bindValue('start', ($page-1)*$limit, \PDO::PARAM_INT);
            $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return !$result ? array() : $result;
        } catch (\PDOException $e) {
            throw $e;
        }
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
        try {
            $pagesCount = 0;
            $sql = '
                SELECT
                    COUNT(*) AS pages_count
                FROM
                    users
                WHERE
                    del = 0
            ';
            $result = $this->db->fetchAssoc($sql);
            if ($result) {
                $pagesCount = ceil($result['pages_count']/$limit);
            }
            return $pagesCount;
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * Counts Admins.
     *
     * @access public
     * @return mixed Result
     */
    public function countAdmins()
    {
        try {
            $sql = '
                SELECT
                    COUNT(*) AS counter
                FROM
                    users
                WHERE
                    role_id = 1
                AND
                    del = 0
            ';
            $result = $this->db->fetchAssoc($sql);
        } catch (\PDOException $e) {
            throw $e;
        }
        if ($result) {
            return (int)$result['counter'];
        } else {
            return $array();
        }
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

    /**
     * Edits user.
     *
     * @access public
     * @param array $user User data
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

     /**
     * Delete user.
     *
     * @access public
     * @param array $user User data
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
     * Gets single user to search action.
     *
     * @access public
     * @param string $login User's login
     * @return array Result
     */
    public function getSingleUser($login)
    {
        if ($login != '') {
            try {
                $query = '
                    SELECT
                        users.id,
                        role_id,
                        login,
                        name,
                        surname,
                        avatar
                    FROM
                        users,
                        users_data
                    WHERE
                        users.id = users_id
                    AND
                        login = :login
                    AND
                        del = 0
                ';
                $statement = $this->db->prepare($query);
                $statement->bindValue('login', $login, \PDO::PARAM_STR);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                return !$result ? array() : current($result);
            } catch (\PDOException $e) {
                throw $e;
            }
        } else {
            return array();
        }
    }

    /**
     * Set grants.
     *
     * @access public
     * @param array $user User's data
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
            if ($user['role_id'] == 2) {
                if ($this->countAdmins() > 1) {
                    return $this->db->update('users', $user, array('id' => $id));
                } else {
                    return array();
                }
            }
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
        try {
            $query = '
                SELECT 
                    id,
                    name AS role_id
                FROM 
                    roles';
            return $this->db->fetchAll($query);
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * Gets old password.
     *
     * @access public
     * @param integer $userId User's Id
     * @return array Result
     */
    public function getOldPassword($userId)
    {
        if ($userId != '') {
            try {
                $query = '
                    SELECT
                        password
                    FROM
                        users
                    WHERE
                        id = :userId
                    AND
                        del = 0
                ';
                $statement = $this->db->prepare($query);
                $statement->bindValue('userId', $userId, \PDO::PARAM_INT);
                $statement->execute();
                $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
                return !$result ? array() : current($result);
            } catch (\PDOException $e) {
                throw $e;
            }
        } else {
            return array();
        }
    }

    /**
     * Resets password.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param array $data User's data
     * @param integer $OldPassword User's old password
     * @param integer $userId User's Id
     * @retun mixed Result
     */
    public function resetPassword($app, $data, $OldPassword, $userId)
    {
        if ($OldPassword['password'] === $app['security.encoder.digest']
            ->encodePassword($data['old'], '')
            && $data['new'] === $data['confirm']) {
                unset($data['old']);
                unset($data['confirm']);
                $data['password'] = $app['security.encoder.digest']
                    ->encodePassword($data['new'], '');
                unset($data['new']);
                return $this->db->update('users', $data, array('id' => $userId));
        }
        return array();
    }
}
