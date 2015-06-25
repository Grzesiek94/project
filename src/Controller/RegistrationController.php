<?php
/**
 * Registration controller.
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
use Model\RegistrationModel;
use Form\RegistrationForm;
use Doctrine\DBAL\DBALException;
use MyException\FormValidException;

/**
 * Class RegistrationController.
 *
 * @package Controller
 * @implements ControllerProviderInterface
 * @author Grzegorz Stefański
 * @link wierzba.wzks.uj.edu.pl/~13_stefanski/php
 * @uses Silex\Application
 * @uses Silex\ControllerProviderInterface
 * @uses Symfony\Component\HttpFoundation\Request
 * @uses Model\RegistrationModel
 * @uses Form\RegistrationForm
 * @uses Doctrine\DBAL\DBALException
 * @uses MyException\FormValidException
 */
class RegistrationController implements ControllerProviderInterface
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
     * @return RegistrationController Result
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
        try {
            $data = array();
            $form = $app['form.factory']
                ->createBuilder(new RegistrationForm(), $data)->getForm();
            
            $form->handleRequest($request);

            if ($form->isValid()) {
                try {
                    $data = $form->getData();
                    $registrationModel = new RegistrationModel($app);
                    if (count($registrationModel->isUnique($data))) {
                        $register = $registrationModel->addUser($app, $data);
                    } else {
                        $app['session']->getFlashBag()->add(
                            'message',
                            array(
                                'type' => 'danger', 'content' =>
                                $app['translator']
                                    ->trans('Someone uses this login. Try other.')
                            )
                        );
                        return $app->redirect(
                            $app['url_generator']->generate('registration'),
                            301
                        );
                    }
                    if (count($register)) {
                        $details = $registrationModel->getUserId();
                        $registrationModel->addUserData($details);
                        $app['session']->getFlashBag()->add(
                            'message',
                            array(
                                'type' => 'success', 'content' =>
                                $app['translator']
                                    ->trans('Account created correctly. Now You can log in to your account.')
                            )
                        );
                        return $app->redirect(
                            $app['url_generator']->generate('main'),
                            301
                        );
                    } else {
                        $app['session']->getFlashBag()->add(
                            'message',
                            array(
                                'type' => 'danger', 'content' =>
                                $app['translator']->trans('You typed different passwords! Try again.')
                            )
                        );
                        return $app->redirect(
                            $app['url_generator']->generate('registration'),
                            301
                        );
                    }
                } catch (\FormValidException $e) {
                    $app->abort(403, $app['translator']->trans('Something went wrong with form'));
                }
            }

            $this->view['form'] = $form->createView();
        } catch (\PDOException $e) {
            $app->abort(500, $app['translator']->trans('Sorry, something wrong with database'));
        }
        return $app['twig']->render('registration/index.twig', $this->view);
    }
}
