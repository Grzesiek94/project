<?php
/**
 * Board controller.
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
use Model\BoardModel;
use Form\BoardForm;
use Model\UsersModel;

/**
 * Class BoardController.
 *
 * @package Controller
 * @implements ControllerProviderInterface
 * @author Grzegorz Stefański
 * @link wierzba.wzks.uj.edu.pl/~13_stefanski/php
 * @uses Silex\Application
 * @uses Silex\ControllerProviderInterface
 * @uses Symfony\Component\HttpFoundation\Request
 * @uses Model\BoardModel
 * @uses Form\BoardForm
 * @uses Model\UsersModel
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
     * @return BoardController Result
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
        $this->view['currentUser'] = $boardModel->getUserId($currentUser);
        $id = (int)$request->get('id', $this->view['currentUser']);
        $page = (int) $request->get('page', 1);
        $this->view['user_id'] = $id;
        $this->view = array_merge(
            $this->view,
            $boardModel->getPaginatedQuestions($page, $pageLimit, $id)
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
        return $app['twig']->render('board/index.twig', $this->view);
    }
}
