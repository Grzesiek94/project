<?php
/**
 * Index controller.
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
use Model\IndexModel;
use Model\BoardModel;
use Doctrine\DBAL\DBALException;

/**
 * Class IndexController.
 *
 * @package Controller
 * @implements ControllerProviderInterface
 * @author Grzegorz Stefański
 * @link wierzba.wzks.uj.edu.pl/~13_stefanski/php
 * @uses Silex\Application
 * @uses Silex\ControllerProviderInterface
 * @uses Symfony\Component\HttpFoundation\Request
 * @uses Model\IndexModel
 * @uses Model\BoardModel
 * @uses Doctrine\DBAL\DBALException
 */
class IndexController implements ControllerProviderInterface
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
     * @return IndexController Result
     */
    public function connect(Application $app)
    {
        $indexController = $app['controllers_factory'];
        $indexController->get('/', array($this, 'indexAction'))
            ->bind('main');
        $indexController->get('/{param}/', array($this, 'indexAction'));
        return $indexController;
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
            $userId = $boardModel->getUserId($currentUser);
            $indexModel = new IndexModel($app);
            $question = $indexModel->haveQuestions($userId);
            if ($question > 0) {
                $app['session']->getFlashBag()->add(
                    'message',
                    array(
                        'type' => 'warning', 'content' =>
                        $app['translator']
                        ->trans('You have new questions to answer!')
                    )
                );
            }
            $data = $indexModel->dataCompleted($userId);
            if ($data > 0) {
                $app['session']->getFlashBag()->add(
                    'message',
                    array(
                        'type' => 'warning', 'content' =>
                        $app['translator']
                        ->trans('You have to fill infromation about Your profile!')
                    )
                );
            }
            $this->view = $indexModel->countUsers();
            $this->view['bestQuestioning'] = $indexModel->bestQuestioning();
            $this->view['bestAnswering'] = $indexModel->bestAsnwering();
            $this->view = array_merge(
                $this->view,
                $indexModel->countQuestions(),
                $indexModel->countAnswers()
            );
        } catch (\PDOException $e) {
            $app->abort(500, $app['translator']->trans('Sorry, something wrong with database'));
        }
        return $app['twig']->render('index/index.twig', $this->view);
    }
}
