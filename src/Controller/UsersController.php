<?php
/**
 * Users controller.
 *
 * @link http://epi.uj.edu.pl
 * @author epi(at)uj(dot)edu(dot)pl
 * @copyright EPI 2015
 */

namespace Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Form\UserForm;
use Form\GrantsForm;
use Form\AvatarForm;
use Model\UsersModel;
use Model\BoardModel;
use Model\AvatarModel;

/**
 * Class UsersController.
 *
 * @package Controller
 * @implements ControllerProviderInterface
 */
class UsersController implements ControllerProviderInterface
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
        $usersController->get('/', array($this, 'indexAction'))
            ->bind('user_index');
        $usersController->match('/add', array($this, 'addAction'))
            ->bind('user_add');
        $usersController->match('/search/', array($this, 'searchAction'));
        $usersController->match('/search', array($this, 'searchAction'))
            ->bind('user_search');
        $usersController->match('/add/', array($this, 'addAction'));
        $usersController->match('/view', array($this, 'viewAction'))
            ->bind('user_profile');
        $usersController->match('/edit/{id}', array($this, 'editAction'))
            ->bind('user_edit');
        $usersController->match('/edit/{id}/', array($this, 'editAction'));
        $usersController->match('/delete/{id}', array($this, 'deleteAction'))
            ->bind('user_delete');
        $usersController->match('/delete/{id}/', array($this, 'deleteAction'));
        $usersController->get('/view/{id}', array($this, 'viewAction'))
            ->bind('user_view');
        $usersController->get('/view/{id}/', array($this, 'viewAction'));
        $usersController->match('/set_grants/{id}', array($this, 'setGrantsAction'))
            ->bind('set_grants');
        $usersController->match('/set_grants/{id}/', array($this, 'setGrantsAction'));
        $usersController->get('/index', array($this, 'indexAction'));
        $usersController->get('/index/', array($this, 'indexAction'));
        $usersController->match('/avatar/{id}/', array($this, 'avatarAction'));
        $usersController->match('/avatar/{id}/', array($this, 'avatarAction'));
        $usersController->match('/avatar', array($this, 'avatarAction'))
            ->bind('avatar');
        $usersController->get('/{page}', array($this, 'indexAction'))
                         ->value('page', 1)->bind('user_index');
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
        $token = $app['security']->getToken();
        if (null !== $token) {
            $this->view['currentUser'] = $token->getUsername();
        }
        $this->view = array_merge(
            $this->view, $usersModel->getPaginatedUsers($page, $pageLimit)
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
        $token = $app['security']->getToken();
        if (null !== $token) {
            $currentUser = $token->getUsername();
        }
        $boardModel = new BoardModel($app);
        $this->view['userId'] = $boardModel->getUserId($currentUser);
        $id = (int)$request->get('id', $this->view['userId']);
        $usersModel = new UsersModel($app);
        $this->view['user'] = $usersModel->getUser($id);
        if (!count($this->view['user']))
        {
            return $app->redirect(
                $app['url_generator']->generate('user_index'), 301
            );
        }
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
        $user = $usersModel->getUserDetails($id);
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
        $id = (int)$request->get('id', null);
        $user = $usersModel->getUserDetails($id);
        $token = $app['security']->getToken();
        if (null !== $token) {
            $currentUser = $token->getUsername();
        }
        $boardModel = new BoardModel($app);
        
        if (count($user) && (int)$boardModel->getUserId($currentUser) != $id) {
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
                $usersModel->deleteUser($data);
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

    /**
     * Search action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function searchAction(Application $app, Request $request)
    {
        $login = $_GET['login'];
        $usersModel = new UsersModel($app);
        $this->view['user'] = $usersModel->getSingleUser($login);
        $token = $app['security']->getToken();
        if (null !== $token) {
            $this->view['currentUser'] = $token->getUsername();
        }
        return $app['twig']->render('users/search.twig', $this->view);
    }

    /**
     * Delete action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function setGrantsAction(Application $app, Request $request)
    {
        $usersModel = new UsersModel($app);
        $id = (int)$request->get('id', null);
        $user = $usersModel->getUserDetails($id);
        if (count($user)) {
            $form = $app['form.factory']
                ->createBuilder(new GrantsForm($app), $user)->getForm();
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();
                if (count($usersModel->setGrants($data))) {
                    $app['session']->getFlashBag()->add(
                    'message', array(
                        'type' => 'success', 'content' =>
                        $app['translator']->trans('Grants has been changed.')
                               )
                    );
                    return $app->redirect(
                        $app['url_generator']->generate('user_index'), 301
                    );
                } else {
                    $app['session']->getFlashBag()->add(
                    'message',
                    array(
                        'type' => 'danger', 'content' =>
                        $app['translator']->trans('You are the last Admin, first you must choose other.')
                               )
                    );
                    return $app->redirect(
                        $app['url_generator']->generate('user_index'), 301
                    );
                }
            }

            $this->view['id'] = $id;
            $this->view['form'] = $form->createView();
        } else {
            return $app->redirect(
                $app['url_generator']->generate('user_index'), 301
            );
        }
        return $app['twig']->render('users/setGrants.twig', $this->view);
    }
    /**
     * Add action.
     *
     * @access public
     * @param Application $app Silex application
     * @param Request $request Request object
     * @return string Output
     */
    public function avatarAction(Application $app, Request $request)
    {
        $token = $app['security']->getToken();
        if (null !== $token) {
            $currentUser = $token->getUsername();
        }
        $boardModel = new BoardModel($app);
        $userId = $boardModel->getUserId($currentUser);
        $id = (int)$request->get('id', $userId);
        // default values:
        $data = array();
        $form = $app['form.factory']
            ->createBuilder(new AvatarForm(), $data)->getForm();
        if ($request->isMethod('POST')) {
            $form->bind($request);
            if ($form->isValid()) {

                try {
                    $files = $request->files->get($form->getName());
                    $mediaPath = dirname(dirname(dirname(__FILE__))).'/web/upload';
                    $photosModel = new AvatarModel($app);
                    $photosModel->saveImage($files, $mediaPath, $userId);

                    $app['session']->getFlashBag()->add(
                        'message',
                        array(
                            'type' => 'success',
                            'content' => $app['translator']
                                ->trans('Avatar successfully uploaded.')
                        )
                    );
                    return $app->redirect(
                        $app['url_generator']->generate('user_profile'), 301
                    );
                } catch (Exception $e) {
                    $app['session']->getFlashBag()->add(
                        'message',
                        array(
                            'type' => 'error',
                            'content' => $app['translator']
                                ->trans('Can not upload file.')
                        )
                    );
                }
            } else {
                $app['session']->getFlashBag()->add(
                    'message',
                    array(
                        'type' => 'error',
                        'content' => $app['translator']
                            ->trans('Form contains invalid data.')
                    )
                );
            }
        }
        $this->view['form'] = $form->createView();
        return $app['twig']->render('users/avatar.twig', $this->view);
    }
}
