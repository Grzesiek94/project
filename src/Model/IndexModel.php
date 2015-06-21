<?php
/**
 * Index model.
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
 * Class IndexModel.
 *
 * @package Model
 * @author Grzegorz Stefański
 * @link wierzba.wzks.uj.edu.pl/~13_stefanski/php
 * @uses Doctrine\DBAL\DBALException;
 * @uses Silex\Application
 */
class IndexModel
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
     * Counts users.
     *
     * @access public
     * @return array Result
     */
    public function countUsers()
    {
        try {
            $query = 'SELECT
                          COUNT(*) AS countUsers
                      FROM 
                          users
                      WHERE 
                          del = 0';
            $statement = $this->db->prepare($query);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return !$result ? array() : current($result);
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * Counts questions.
     *
     * @access public
     * @return array Result
     */
    public function countQuestions()
    {
        try {
            $query = 'SELECT
                          COUNT(*) AS countQuestions
                      FROM
                          board';
            $statement = $this->db->prepare($query);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return !$result ? array() : current($result);
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * Counts answers.
     *
     * @access public
     * @return array Result
     */
    public function countAnswers()
    {
        try {
            $query = 'SELECT
                          COUNT(*) AS countAnswers
                      FROM
                          board
                      WHERE
                          answer IS NOT NULL';
            $statement = $this->db->prepare($query);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return !$result ? array() : current($result);
        } catch (\PDOException $e) {
            throw $e;
        }
    }
    /**
     * Gets information about best users.
     *
     * @access public
     * @return array Result
     */
    public function bestQuestioning()
    {
        try {
            $query = '
	            SELECT 
                    avatar,
                    login,
                    COUNT(users_question_id) AS best
                FROM 
                    users 
                INNER JOIN
                    board ON users_question_id = users.id 
                INNER JOIN
                    users_data ON users.id = users_data.id
                GROUP BY 
                    users_question_id 
                ORDER BY
                    best DESC LIMIT 3
	        ';
            $statement = $this->db->prepare($query);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return !$result ? array() : $result;
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * Gets information about best users.
     *
     * @access public
     * @return array Result
     */
    public function bestAsnwering()
    {
        try {
            $query = '
                SELECT
                    avatar,
                    login,
                    COUNT(users_answer_id) AS best
                FROM
                    users
                INNER JOIN
                    board ON users_answer_id = users.id
                INNER JOIN
                    users_data ON users.id = users_data.id
                WHERE 
                    answer IS NOT NULL
                GROUP BY
                    users_answer_id
                ORDER BY
                    best DESC LIMIT 3
            ';
            $statement = $this->db->prepare($query);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return !$result ? array() : $result;
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * Checks if users have questions to answer.
     *
     * @access public
     * @param integer $id Current user's ID
     * @retun bool Result
     */
    public function haveQuestions($id)
    {
        try {
            $query = '
                SELECT
                    COUNT(*) AS counter
                FROM
                    board
                WHERE
                    users_answer_id = :id
                AND
                    row_ignore = 0
                AND 
                    answer IS NULL
            ';
            $statement = $this->db->prepare($query);
            $statement->bindValue('id', $id, \PDO::PARAM_INT);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result[0]['counter'] == 0 ? 0 : 1;
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * Checks if users have missings in their data.
     *
     * @access public
     * @param integer $id Current user's ID
     * @retun bool Result
     */
    public function dataCompleted($id)
    {
        try {
            $query = '
                SELECT 
                    *
                FROM
                    users_data
                WHERE
                    users_id = :id
                AND (
                    name IS NULL
                OR 
                    surname is NULL
                OR
                    email IS NULL
                OR
                    website IS NULL
                OR
                    facebook IS NULL
            )';
            $statement = $this->db->prepare($query);
            $statement->bindValue('id', $id, \PDO::PARAM_INT);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return !$result ? 0 : 1;
        } catch (\PDOException $e) {
            throw $e;
        }
    }
}
