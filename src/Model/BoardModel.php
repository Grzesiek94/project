<?php
/**
 * Board model.
 *
 * @link http://epi.uj.edu.pl
 * @author epi(at)uj(dot)edu(dot)pl
 * @copyright EPI 2015
 */

namespace Model;

use Silex\Application;

class BoardModel
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
    public function getQuestions($id)
    {
        if (($id != '') && ctype_digit((string)$id)) {
            $query = 'SELECT id, question, answer, users_question_id, users_answer_id 
                      FROM board
                      WHERE answer IS NOT NULL
                      AND users_answer_id = :id';
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
     * Get all questions on page.
     *
     * @access public
     * @param integer $page Page number
     * @param integer $limit Number of records on single page
     * @retun array Result
     */
    public function getQuestionsPage($page, $limit, $id)
    {
        $query = 'SELECT
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
                  AND answer IS NOT NULL
                  ORDER BY board.id DESC
                  LIMIT :start, :limit';
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
     * @return integer Result
     */
    public function countQuestionsPages($limit, $id)
    {
        $pagesCount = 0;
        $sql = 'SELECT COUNT(*) as pages_count 
                FROM board
                WHERE users_answer_id = :id
                AND answer IS NOT NULL';
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
     *
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

     /* Add user.
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
     * @param integer $id Record Id
     * @return string Result
     */
    public function getUserId($name)
    {
        if (($name != '') && ($name != 'anon.')) {
            $query = 'SELECT id
                      FROM users
                      WHERE login = :name';
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

