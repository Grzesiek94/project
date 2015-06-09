<?php
/**
 * Index controller.
 *
 * @link http://epi.uj.edu.pl
 * @author epi(at)uj(dot)edu(dot)pl
 * @copyright EPI 2015
 */

namespace Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Model\IndexModel;

/**
 * Class IndexController.
 *
 * @package Controller
 * @implements ControllerProviderInterface
 */
class IndexController implements ControllerProviderInterface
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
     * @return UsersController Result
     */
    public function connect(Application $app)
    {
        $indexController = $app['controllers_factory'];
        $indexController->match('/add', array($this, 'addAction'))
            ->bind('user_add');
        $indexController->get('/', array($this, 'indexAction'))
            ->bind('main');
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
        $indexModel = new IndexModel($app);
        $this->view['users'] = $indexModel->getData();
        return $app['twig']->render('index/index.twig', $this->view);
    }
}
