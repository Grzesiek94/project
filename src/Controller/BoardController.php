<?php
/**
 * Board controller.
 *
 * @link http://epi.uj.edu.pl
 * @author epi(at)uj(dot)edu(dot)pl
 * @copyright EPI 2015
 */

namespace Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Model\BoardModel;
use Form\BoardForm;
use Model\UsersModel;

/**
 * Class BoardController.
 *
 * @package Controller
 * @implements ControllerProviderInterface
 */
class BoardController implements ControllerProviderInterface
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
     * @return UsersController Result
     */
    public function connect(Application $app)
    {
        $boardController = $app['controllers_factory'];
        $boardController->match('', array($this, 'indexAction'))
           ->bind('board_null');
        $boardController->match('/{id}/page/{page}', array($this, 'indexAction'))
           ->value('page', 1)->bind('board');
        return $boardController;
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
        $pageLimit = 15;
$token = $app['security']->getToken();
if (null !== $token) {
$currentUser = $token->getUsername();
}
$boardModel = new BoardModel($app);
        $id = (int)$request->get('id', $boardModel->getUserId($currentUser));
        $page = (int) $request->get('page', 1);
        $this->view['user_id'] = $id;
        $boardModel = new BoardModel($app);
        $this->view = array_merge(
            $this->view, $boardModel->getPaginatedQuestions($page, $pageLimit, $id)
        );
        $usersModel = new UsersModel($app);
        $this->view['user'] = $usersModel->getUser($id);
        $form = $app['form.factory']
            ->createBuilder(new BoardForm())->getForm();
        
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $data['users_answer_id'] = $id;
            $token = $app['security']->getToken();
            if (null !== $token) {
                $currentUser = $token->getUsername();
            }
            $boardModel = new BoardModel($app);
            $data['users_question_id'] = (int)$boardModel->getUserId($currentUser);
            $boardModel->askQuestion($data);
            $app['session']->getFlashBag()->add(
                'message',
                array(
                    'type' => 'success', 'content' => 
                    $app['translator']->trans('Question added.')
                )
            );
            return $app->redirect(
                $app['request']->getUri()
            );
        }

        $this->view['form'] = $form->createView();
//var_dump($this->view);
        return $app['twig']->render('board/index.twig', $this->view);
    }
 
}
