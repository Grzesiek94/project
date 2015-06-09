<?php
/**
 * Questions controller.
 *
 * @link http://epi.uj.edu.pl
 * @author epi(at)uj(dot)edu(dot)pl
 * @copyright EPI 2015
 */

namespace Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Model\QuestionsModel;
use Model\BoardModel;
use Form\QuestionForm;

/**
 * Class QuestionsController.
 *
 * @package Controller
 * @implements ControllerProviderInterface
 */
class QuestionsController implements ControllerProviderInterface
{

    /**
     * Data for view.
     *
     * @access protected
     * @var array $_view
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
        $token = $app['security']->getToken();
        if (null !== $token) {
            $currentUser = $token->getUsername();
        }
        $boardModel = new BoardModel($app);
        $id = (int)$boardModel->getUserId($currentUser);
        $questionsModel = new QuestionsModel($app);
        $this->view['questions'] = $questionsModel->getUnanswered($id);
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
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();
                $questionsModel->answer($data);
                $app['session']->getFlashBag()->add(
                'message', array(
                    'type' => 'success', 'content' =>
                    $app['translator']->trans('Answer added correctly.')
                            )
                );
                return $app->redirect(
                    $app['url_generator']->generate('questions_index'), 301
                );
            }

            $this->view['id'] = $id;
            $this->view['form'] = $form->createView();

        } else {
            $app['session']->getFlashBag()->add(
            'message', array(
                'type' => 'danger', 'content' =>
                $app['translator']->trans('You tried to make something illegal! Be care.')
                        )
            );
            return $app->redirect(
                $app['url_generator']->generate('questions_index'), 301
            );
        }
        return $app['twig']->render('questions/answer.twig', $this->view);
    }
}
