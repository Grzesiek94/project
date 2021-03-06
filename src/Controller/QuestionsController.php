<?php
/**
 * Questions controller.
 *
 * @category Controller
 * @author Grzegorz Stefański
 * @link wierzba.wzks.uj.edu.pl/~13_stefanski/php
 * @copyright EPI 2015
 */

namespace Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Model\QuestionsModel;
use Model\BoardModel;
use Form\QuestionForm;
use Doctrine\DBAL\DBALException;
use MyException\FormValidException;

/**
 * Class QuestionsController.
 *
 * @package Controller
 * @implements ControllerProviderInterface
 * @author Grzegorz Stefański
 * @link wierzba.wzks.uj.edu.pl/~13_stefanski/php
 * @uses Silex\Application
 * @uses Silex\ControllerProviderInterface
 * @uses Symfony\Component\HttpFoundation\Request
 * @uses Model\QuestionsModel
 * @uses Model\BoardModel
 * @uses Form\QuestionForm
 * @uses Doctrine\DBAL\DBALException
 * @uses MyException\FormValidException
 */
class QuestionsController implements ControllerProviderInterface
{

    /**
     * Data for view.
     *
     * @access protected
     * @var array $view
     */
    protected $view = array();

    /**
     * Routing settings.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @return QuestionsController Result
     */
    public function connect(Application $app)
    {
        $questionsController = $app['controllers_factory'];
        $questionsController->match('/', array($this, 'indexAction'))
                         ->bind('questions_index');
        $questionsController->match('/answer/{id}', array($this, 'answerAction'))
                         ->bind('questions_edit');
        $questionsController->get('/my', array($this, 'askedQuestionAction'))
                         ->bind('my_questions');
        $questionsController->get('/my/{param}', array($this, 'askedQuestionAction'));
        $questionsController->match('/my/edit/{id}', array($this, 'editQuestionAction'));
        $questionsController->match('/my/edit/{id}', array($this, 'editQuestionAction'))
                         ->bind('my_questions_edit');
        $questionsController->match('/answer/edit/{id}', array($this, 'editAnswerAction'));
        $questionsController->match('/answer/edit/{id}', array($this, 'editAnswerAction'))
                         ->bind('my_answers_edit');
        $questionsController->get('/ignore/{id}', array($this, 'ignoreAction'));
        $questionsController->match('/ignore/{id}', array($this, 'ignoreAction'))
                         ->bind('ignore');
        $questionsController->get('/delete/{id}', array($this, 'deleteAction'));
        $questionsController->match('/delete/{id}', array($this, 'deleteAction'))
                         ->bind('question_delete');
        $questionsController->match('/ignored', array($this, 'ignoredAction'))
                         ->bind('ignored');
        $questionsController->get('/ignored/del/yes', array($this, 'deleteIgnoredAction'));
        $questionsController->match('/ignored/del/yes', array($this, 'deleteIgnoredAction'))
                         ->bind('ignored_delete_yes');
        $questionsController->get('/ignored/del', array($this, 'deleteIgnoredViewAction'));
        $questionsController->match('/ignored/del', array($this, 'deleteIgnoredViewAction'))
                         ->bind('ignored_delete');
        return $questionsController;
    }

    /**
     * Index action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function indexAction(Application $app, Request $request)
    {
        try {
            $token = $app['security']->getToken();
            if (null !== $token) {
                $currentUser = $token->getUsername();
            }
            $boardModel = new BoardModel($app);
            $id = (int)$boardModel->getUserId($currentUser);
            $questionsModel = new QuestionsModel($app);
            $this->view['questions'] = $questionsModel->getUnanswered($id);
        } catch (\PDOException $e) {
            $app->abort(500, $app['translator']->trans('Sorry, something wrong with database'));
        }
        return $app['twig']->render('questions/index.twig', $this->view);
    }

    /**
     * Answer action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function answerAction(Application $app, Request $request)
    {
        try {
            $id = (int) $request->get('id', null);
            $token = $app['security']->getToken();
            if (null !== $token) {
                $currentUser = $token->getUsername();
            }
            $boardModel = new BoardModel($app);
            $userId = (int)$boardModel->getUserId($currentUser);
            $questionsModel = new QuestionsModel($app);
            $question = $questionsModel->getSingleQuestion($id, $userId);
            $this->view['question'] = $question;
            if (count($question)) {
                $form = $app['form.factory']
                    ->createBuilder(new QuestionForm(), $question)->getForm();
                $form->remove('question');
                $form->handleRequest($request);

                if ($form->isValid()) {
                    try {
                        $data = $form->getData();
                        $questionsModel->edit($data);
                        $app['session']->getFlashBag()->add(
                            'message',
                            array(
                                'type' => 'success', 'content' =>
                                $app['translator']->trans('Answer added correctly.')
                            )
                        );
                        return $app->redirect(
                            $app['url_generator']->generate('questions_index'),
                            301
                        );
                    } catch (\FormValidException $e) {
                        $app->abort(403, $app['translator']->trans('Something went wrong with form'));
                    }
                }

                $this->view['id'] = $id;
                $this->view['form'] = $form->createView();

            } else {
                $app['session']->getFlashBag()->add(
                    'message',
                    array(
                        'type' => 'danger', 'content' =>
                        $app['translator']
                            ->trans('You tried to make something illegal! Be care.')
                    )
                );
                return $app->redirect(
                    $app['url_generator']->generate('questions_index'),
                    301
                );
            }
        } catch (\PDOException $e) {
            $app->abort(500, $app['translator']->trans('Sorry, something wrong with database'));
        }
        return $app['twig']->render('questions/answer.twig', $this->view);
    }

    /**
     * Asked question action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function askedQuestionAction(Application $app, Request $request)
    {
        try {
            $token = $app['security']->getToken();
            if (null !== $token) {
                $currentUser = $token->getUsername();
            }
            $boardModel = new BoardModel($app);
            $id = (int)$boardModel->getUserId($currentUser);
            $questionsModel = new QuestionsModel($app);
            $this->view['questions'] = $questionsModel->getAskedQuestions($id);
        } catch (\PDOException $e) {
            $app->abort(500, $app['translator']->trans('Sorry, something wrong with database'));
        }
        return $app['twig']->render('questions/asked.twig', $this->view);
    }

    /**
     * Edit question action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function editQuestionAction(Application $app, Request $request)
    {
        try {
            $id = (int) $request->get('id', null);
            $token = $app['security']->getToken();
            if (null !== $token) {
                $currentUser = $token->getUsername();
            }
            $boardModel = new BoardModel($app);
            $userId = (int)$boardModel->getUserId($currentUser);
            $questionsModel = new QuestionsModel($app);
            $question = $questionsModel->getQuestionToEdit($id, $userId);
            $this->view['question'] = $question;
            if (count($question)) {
                $form = $app['form.factory']
                    ->createBuilder(new QuestionForm(), $question)->getForm();
                $form->remove('answer');
                $form->handleRequest($request);

                if ($form->isValid()) {
                    try {
                        $data = $form->getData();
                        $questionsModel->edit($data);
                        $app['session']->getFlashBag()->add(
                            'message',
                            array(
                                'type' => 'success', 'content' =>
                                $app['translator']->trans('Question edited.')
                            )
                        );
                        return $app->redirect(
                            $app['url_generator']->generate('my_questions'),
                            301
                        );
                    } catch (\FormValidException $e) {
                        $app->abort(403, $app['translator']->trans('Something went wrong with form'));
                    }
                }

                $this->view['id'] = $id;
                $this->view['form'] = $form->createView();

            } else {
                $app['session']->getFlashBag()->add(
                    'message',
                    array(
                        'type' => 'danger', 'content' =>
                        $app['translator']
                            ->trans('You tried to make something illegal! Be care.')
                    )
                );
                return $app->redirect(
                    $app['url_generator']->generate('my_questions'),
                    301
                );
            }
        } catch (\PDOException $e) {
            $app->abort(500, $app['translator']->trans('Sorry, something wrong with database'));
        }
        return $app['twig']->render('questions/edit.twig', $this->view);
    }

    /**
     * Edit answer action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function editAnswerAction(Application $app, Request $request)
    {
        try {
            $id = (int) $request->get('id', null);
            $token = $app['security']->getToken();
            if (null !== $token) {
                $currentUser = $token->getUsername();
            }
            $boardModel = new BoardModel($app);
            $userId = (int)$boardModel->getUserId($currentUser);
            $questionsModel = new QuestionsModel($app);
            $answer = $questionsModel->getAnswerToEdit($id, $userId);
            $this->view['answer'] = $answer;
            if (count($answer)) {
                $form = $app['form.factory']
                    ->createBuilder(new QuestionForm(), $answer)->getForm();
                $form->remove('question');
                $form->handleRequest($request);

                if ($form->isValid()) {
                    try {
                        $data = $form->getData();
                        $questionsModel->edit($data);
                        $app['session']->getFlashBag()->add(
                            'message',
                            array(
                                'type' => 'success', 'content' =>
                                $app['translator']->trans('Answer edited.')
                            )
                        );
                        return $app->redirect(
                            $app['url_generator']->generate('board_null'),
                            301
                        );
                    } catch (\FormValidException $e) {
                        $app->abort(403, $app['translator']->trans('Something went wrong with form'));
                    }
                }

                $this->view['id'] = $id;
                $this->view['form'] = $form->createView();

            } else {
                $app['session']->getFlashBag()->add(
                    'message',
                    array(
                        'type' => 'danger', 'content' =>
                        $app['translator']
                            ->trans('You tried to make something illegal! Be care.')
                    )
                );
                return $app->redirect(
                    $app['url_generator']->generate('board_null'),
                    301
                );
            }
        } catch (\PDOException $e) {
            $app->abort(500, $app['translator']->trans('Sorry, something wrong with database'));
        }
        return $app['twig']->render('questions/editAnswer.twig', $this->view);
    }

    /**
     * Ignore action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function ignoreAction(Application $app, Request $request)
    {
        try {
            $id = (int) $request->get('id', null);
            $token = $app['security']->getToken();
            if (null !== $token) {
                $currentUser = $token->getUsername();
            }
            $boardModel = new BoardModel($app);
            $userId = (int)$boardModel->getUserId($currentUser);
            $questionsModel = new QuestionsModel($app);
            $question = $questionsModel->getSingleQuestion($id, $userId);
            if (count($question)) {
                $form = $app['form.factory']
                    ->createBuilder(new QuestionForm(), $question)->getForm();
                $form->remove('answer');
                $form->remove('question');
                $form->handleRequest($request);

                if ($form->isValid()) {
                    try {
                        $data = $form->getData();
                        $questionsModel->ignore($data);
                        $app['session']->getFlashBag()->add(
                            'message',
                            array(
                                'type' => 'success', 'content' =>
                                $app['translator']->trans('Question deleted.')
                            )
                        );
                        return $app->redirect(
                            $app['url_generator']->generate('questions_index'),
                            301
                        );
                    } catch (\FormValidException $e) {
                        $app->abort(403, $app['translator']->trans('Something went wrong with form'));
                    }
                }

                $this->view['id'] = $id;
                $this->view['form'] = $form->createView();

            } else {
                $app['session']->getFlashBag()->add(
                    'message',
                    array(
                        'type' => 'danger', 'content' =>
                        $app['translator']
                            ->trans('You tried to make something illegal! Be care.')
                    )
                );
                return $app->redirect(
                    $app['url_generator']->generate('questions_index'),
                    301
                );
            }
        } catch (\PDOException $e) {
            $app->abort(500, $app['translator']->trans('Sorry, something wrong with database'));
        }
        return $app['twig']->render('questions/ignore.twig', $this->view);
    }

    /**
     * delete action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function deleteAction(Application $app, Request $request)
    {
        try {
            $id = (int) $request->get('id', null);
            $questionsModel = new QuestionsModel($app);
            $question = $questionsModel->letsDelete($id);
            $redirect = $question['users_answer_id'];
            $this->view['inform'] = $question;
            if (count($question)) {
                $form = $app['form.factory']
                    ->createBuilder(new QuestionForm(), $question)->getForm();
                $form->remove('answer');
                $form->remove('question');
                $form->handleRequest($request);

                if ($form->isValid()) {
                    try {
                        $data = $form->getData();
                        $questionsModel->delete($data);
                        $app['session']->getFlashBag()->add(
                            'message',
                            array(
                                'type' => 'success', 'content' =>
                                $app['translator']->trans('Question deleted.')
                            )
                        );
                        return $app->redirect(
                            $app['url_generator']
                            ->generate('board', array('id' => $redirect)),
                            301
                        );
                    } catch (\FormValidException $e) {
                        $app->abort(403, $app['translator']->trans('Something went wrong with form'));
                    }
                }

                $this->view['id'] = $id;
                $this->view['form'] = $form->createView();

            } else {
                $app['session']->getFlashBag()->add(
                    'message',
                    array(
                        'type' => 'danger', 'content' =>
                        $app['translator']
                            ->trans('You tried to make something illegal! Be care.')
                    )
                );
                return $app->redirect(
                    $app['url_generator']->generate('user_index'),
                    301
                );
            }
        } catch (\PDOException $e) {
            $app->abort(500, $app['translator']->trans('Sorry, something wrong with database'));
        }
        return $app['twig']->render('questions/delete.twig', $this->view);
    }
    /**
     * Ignored action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function ignoredAction(Application $app, Request $request)
    {
        try {
            $questionsModel = new QuestionsModel($app);
            $this->view['ignored'] = $questionsModel->getIgnored();
        } catch (\PDOException $e) {
            $app->abort(500, $app['translator']->trans('Sorry, something wrong with database'));
        }
        return $app['twig']->render('questions/ignored.twig', $this->view);
    }
    /**
     * Delete ignored questions action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function deleteIgnoredAction(Application $app, Request $request)
    {
        try {
            $questionsModel = new QuestionsModel($app);
            $questionsModel->deleteIgnored();
            $app['session']->getFlashBag()->add(
                'message',
                array(
                    'type' => 'success', 'content' =>
                    $app['translator']
                        ->trans('All ignored questions have just deleted permanently.')
                )
            );
            return $app->redirect(
                $app['url_generator']->generate('ignored'),
                301
            );
        } catch (\PDOException $e) {
            $app->abort(500, $app['translator']->trans('Sorry, something wrong with database'));
        }
    }
    /**
     * Delete Ignored View Action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function deleteIgnoredViewAction(Application $app, Request $request)
    {
        return $app['twig']->render('questions/deleteIgnored.twig', $this->view);
    }
}
