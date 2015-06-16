<?php
/**
 * Board model.
 *
 * @category Model
 * @author Grzegorz Stefański
 * @link wierzba.wzks.uj.edu.pl/~13_stefanski/php
 * @copyright EPI 2015
 */

namespace Model;

use Silex\Application;

/**
 * Class IndexModel.
 *
 * @package Model
 * @author Grzegorz Stefański
 * @link wierzba.wzks.uj.edu.pl/~13_stefanski/php
 * @uses Silex\Application
 */
class BoardModel
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
     * Get all questions on page.
     *
     * @access public
     * @param integer $page Page number
     * @param integer $limit Number of records on single page
     * @param integer $id Current user's ID
     * @retun array Result
     */
    public function getQuestionsPage($page, $limit, $id)
    {
        $query = '
            SELECT
                board.id as question_id,
                del,
                login,
                avatar,
                question,
                answer,
                users_question_id,
                users_answer_id
            FROM 
	        users 
            INNER JOIN
                users_data ON users.id = users_id
            INNER JOIN 
                board ON users_question_id = users.id
            WHERE
                users_answer_id = :id
            AND 
                answer IS NOT NULL
            ORDER BY
                board.id DESC
            LIMIT :start, :limit
        ';
        $statement = $this->db->prepare($query);
        $statement->bindValue('id', $id, \PDO::PARAM_INT);
        $statement->bindValue('start', ($page-1)*$limit, \PDO::PARAM_INT);
        $statement->bindValue('limit', $limit, \PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return !$result ? array() : $result;
    }

    /**
     * Counts question pages.
     *
     * @access public
     * @param integer $limit Number of records on single page
     * @param integer $id Current user's ID
     * @return integer Result
     */
    public function countQuestionsPages($limit, $id)
    {
        $pagesCount = 0;
        $sql = '
            SELECT 
                COUNT(*) as pages_count 
            FROM
                 board
            WHERE
                users_answer_id = :id
            AND
                answer IS NOT NULL
        ';
        $statement = $this->db->prepare($sql);
        $statement->bindValue('id', $id, \PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        if ($result) {
            $pagesCount = ceil($result[0]['pages_count']/$limit);
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
     * Gets questions for pagination.
     *
     * @access public
     * @param integer $page Page number
     * @param integer $limit Number of records on single page
     * @param integer $id Current user's ID
     * @return array Result
     */
    public function getPaginatedQuestions($page, $limit, $id)
    {
        $pagesCount = $this->countQuestionsPages($limit, $id);
        $page = $this->getCurrentPageNumber($page, $pagesCount);
        $board = $this->getQuestionsPage($page, $limit, $id);
        return array(
            'board' => $board,
            'paginator' => array('page' => $page, 'pagesCount' => $pagesCount)
        );
    }

     /* Inserts question to db.
     *
     * @access public
     * @param array $data Registration data
     * @retun mixed Result
     */
    public function askQuestion($question)
    {
        return $this->db->insert('board', $question);
    }

    /**
     * Gets user id.
     *
     * @access public
     * @param string $name User's login
     * @return string Result
     */
    public function getUserId($name)
    {
        if (($name != '') && ($name != 'anon.')) {
            $query = '
                SELECT
                    id
                FROM
                    users
                WHERE
                    login = :name
            ';
            $statement = $this->db->prepare($query);
            $statement->bindValue('name', $name, \PDO::PARAM_STR);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return !$result ? null : $result[0]['id'];
        } else {
            return null;
        }
    }
}
