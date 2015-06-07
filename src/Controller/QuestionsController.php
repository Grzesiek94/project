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
     * @return UsersController Result
     */
    public function connect(Application $app)
    {
        $usersController = $app['controllers_factory'];
        $usersController->get('/{quest}', array($this, 'indexAction'))
                         ->value('quest', 1)->bind('questions_index');
        return $usersController;
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
        $pageLimit = 10;
        $page = (int) $request->get('page', 1);
        $usersModel = new UsersModel($app);
        $this->view = array_merge(
            $this->view, $usersModel->getPaginatedAlbums($page, $pageLimit)
        );
        return $app['twig']->render('users/index.twig', $this->view);
    }

    /**
     * View action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function viewAction(Application $app, Request $request)
    {
        $id = (int)$request->get('id', null);
        $usersModel = new UsersModel($app);
        $this->view['user'] = $usersModel->getUser($id);
var_dump($this->view);
        return $app['twig']->render('users/view.twig', $this->view);
    }

    /**
     * Edit action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function editAction(Application $app, Request $request)
    {

        $usersModel = new UsersModel($app);
        $id = (int) $request->get('id', 0);
        $user = $usersModel->goEditUser($id);
        if (count($user)) {
            $form = $app['form.factory']
                ->createBuilder(new UserForm(), $user)->getForm();
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();
                $usersModel = new UsersModel($app);
                $usersModel->editUser($data);
                $app['session']->getFlashBag()->add(
                'message', array(
                    'type' => 'success', 'content' =>
                    $app['translator']->trans('Data edited.')
                           )
                );
                return $app->redirect(
                    $app['url_generator']->generate('user_index'), 301
                );
            }

            $this->view['id'] = $id;
            $this->view['form'] = $form->createView();

        } else {
            return $app->redirect(
                $app['url_generator']->generate('user_index'), 301
            );
        }

        return $app['twig']->render('users/edit.twig', $this->view);
    }

    /**
     * Delete action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function deleteAction(Application $app, Request $request)
    {

        $usersModel = new UsersModel($app);
        $id = (int) $request->get('id', 0);
        $user = $usersModel->goEditUser($id);

        if (count($user)) {
            $form = $app['form.factory']
                ->createBuilder(new UserForm(), $user)->getForm();
            $form->remove('name');
            $form->remove('surname');
            $form->remove('email');
            $form->remove('website');
            $form->remove('facebook');
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();
                $usersModel = new UsersModel($app);
                $usersModel->deleteUser($data);
                $usersModel = new UsersModel($app);
                $usersModel->deleteDetails($data);
                $app['session']->getFlashBag()->add(
                'message', array(
                    'type' => 'success', 'content' =>
                    $app['translator']->trans('User deleted.')
                           )
                );
                return $app->redirect(
                    $app['url_generator']->generate('user_index'), 301
                );
            }

            $this->view['id'] = $id;
            $this->view['form'] = $form->createView();

        } else {
            return $app->redirect(
                $app['url_generator']->generate('user_index'), 301
            );
        }

        return $app['twig']->render('users/delete.twig', $this->view);
    } 

}
