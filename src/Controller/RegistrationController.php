<?php
/**
 * Registration controller.
 *
 * @link http://epi.uj.edu.pl
 * @author epi(at)uj(dot)edu(dot)pl
 * @copyright EPI 2015
 */

namespace Controller;

use Silex\Application;
use Silex\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Model\RegistrationModel;
use Form\RegistrationForm;

/**
 * Class RegistrationController.
 *
 * @package Controller
 * @implements ControllerProviderInterface
 */
class RegistrationController implements ControllerProviderInterface
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
        $registrationController = $app['controllers_factory'];
        $registrationController->match('/', array($this, 'registerAction'))
            ->bind('registration');
        return $registrationController;
    }

    /**
     * Register action.
     *
     * @access public
     * @param Silex\Application $app Silex application
     * @param Symfony\Component\HttpFoundation\Request $request Request object
     * @return string Output
     */
    public function registerAction(Application $app, Request $request)
    {
        $data = array(
            'login' => '',
            'password' => '',
            'confirm' => '',
        );
        $form = $app['form.factory']
            ->createBuilder(new RegistrationForm(), $data)->getForm();
        
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $registrationModel = new RegistrationModel($app);
            $register = $registrationModel->addUser($app, $data);
            if (count($register)) {
                $details = $registrationModel->getUserId();
                $registrationModel->addUserData($details);
                $app['session']->getFlashBag()->add(
                    'message', array(
                        'type' => 'success', 'content' => 
                        $app['translator']
                            ->trans('Account created correctly. Now You can log in to your account.')
                    )
                );
                return $app->redirect(
                    $app['url_generator']->generate('main'), 301
                );
            } else {
                $app['session']->getFlashBag()->add(
                    'message', array(
                        'type' => 'danger', 'content' => 
                        $app['translator']->trans('You typed different passwords! Try again.')
                    )
                );
                return $app->redirect(
                    $app['url_generator']->generate('registration'), 301
                );

            }
        }

        $this->view['form'] = $form->createView();

        return $app['twig']->render('registration/index.twig', $this->view);
    }
}
