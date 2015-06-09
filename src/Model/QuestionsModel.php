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

class QuestionsModel
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
    public function getUnanswered($id)
    {
        if (($id != '') && ctype_digit((string)$id)) {
            $query = 'SELECT
                      del,
                      board.id,
                      login,
                      avatar,
                      question,
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
                  AND answer IS NULL
                  AND row_ignore = 0
                  ORDER BY board.id DESC';
            $statement = $this->db->prepare($query);
            $statement->bindValue('id', $id, \PDO::PARAM_INT);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return !$result ? array() : $result;
        } else {
            return array();
        }
    }

    /**
     * Gets single question to edit.
     *
     * @access public
     * @param integer $id Record Id
     * @return array Result
     */
    public function getSingleQuestion($id, $userId)
    {
        if (($id != '') && ctype_digit((string)$id)) {
            $query = 'SELECT
                      del,
                      board.id,
                      login,
                      avatar,
                      question,
                      users_question_id,
                      users_answer_id
                  FROM 
	              users 
                  INNER JOIN
	              users_data ON users.id = users_id
                  INNER JOIN 
	              board ON users_question_id = users.id
                  WHERE
                      users_answer_id = :userId
                  AND board.id = :id
                  AND row_ignore = 0
                  AND answer IS NULL';
            $statement = $this->db->prepare($query);
            $statement->bindValue('id', $id, \PDO::PARAM_INT);
            $statement->bindValue('userId', $userId, \PDO::PARAM_INT);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return !$result ? array() : current($result);
        } else {
            return array();
        }
    }

    /* edit data.
     *
     * @access public
     * @param array $data Question data
     * @retun mixed Result
     */
    public function edit($data)
    {
        if (isset($data['id'])
            && ($data['id'] != '')
            && ctype_digit((string)$data['id'])) {
            // update record
            $id = $data['id'];
            unset($data['id']);
            unset($data['login']);
            unset($data['avatar']);
            unset($data['del']);
            return $this->db->update('board', $data, array('id' => $id));
        }
    }

    /* ignore.
     *
     * @access public
     * @param array $data Question data
     * @retun mixed Result
     */
    public function ignore($data)
    {
        if (isset($data['id'])
            && ($data['id'] != '')
            && ctype_digit((string)$data['id'])) {
            // update record
            $id = $data['id'];
            unset($data['id']);
            unset($data['login']);
            unset($data['avatar']);
            unset($data['del']);
            $data['row_ignore'] = 1;
            return $this->db->update('board', $data, array('id' => $id));
        }
    }

    /**
     * Gets single user data.
     *
     * @access public
     * @param integer $id Record Id
     * @return array Result
     */
    public function getAskedQuestions($id)
    {
        if (($id != '') && ctype_digit((string)$id)) {
            $query = 'SELECT 
                          board.id,
                          login,
                          avatar,
                          question,
                          users_question_id,
                          users_answer_id
                      FROM 
	                  users 
                      INNER JOIN 
	                  users_data ON users.id = users_id
                      INNER JOIN 
	                  board ON users_answer_id = users.id
                      WHERE users_question_id = :id
                      AND answer IS NULL
                      AND del = 0
                      AND row_ignore = 0
                      ORDER BY board.id DESC';
            $statement = $this->db->prepare($query);
            $statement->bindValue('id', $id, \PDO::PARAM_INT);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return !$result ? array() : $result;
        } else {
            return array();
        }
    }

    /**
     * Gets single question to edit.
     *
     * @access public
     * @param integer $id Record Id
     * @return array Result
     */
    public function getQuestionToEdit($id, $userId)
    {
        if (($id != '') && ctype_digit((string)$id)) {
            $query = 'SELECT
                      board.id,
                      login,
                      avatar,
                      question,
                      users_question_id,
                      users_answer_id
                  FROM 
	              users 
                  INNER JOIN
	              users_data ON users.id = users_id
                  INNER JOIN 
	              board ON users_answer_id = users.id
                  WHERE
                      users_question_id = :userId
                  AND board.id = :id
                  AND answer IS NULL
                  AND del = 0
                  AND row_ignore = 0';
            $statement = $this->db->prepare($query);
            $statement->bindValue('id', $id, \PDO::PARAM_INT);
            $statement->bindValue('userId', $userId, \PDO::PARAM_INT);
            $statement->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return !$result ? array() : current($result);
        } else {
            return array();
        }
    }
}

