<?php
/**
 * Album form.
 *
 * @author EPI <epi@uj.edu.pl>
 * @link http://epi.uj.edu.pl
 * @copyright 2015 EPI
 */

namespace Form;

use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Model\UsersModel;

/**
 * Class AlbumForm.
 *
 * @category Epi
 * @package Form
 * @extends AbstractType
 * @use Symfony\Component\Form\AbstractType
 * @use Symfony\Component\Form\FormBuilderInterface
 * @use Symfony\Component\OptionsResolver\OptionsResolverInterface
 * @use Symfony\Component\Validator\Constraints as Assert
 * @use Model\UsersModel
 * @use Silex\Application
 */
class GrantsForm extends AbstractType
{

    /**
     * Silex application.
     *
     * @access protected
     * @var Silex\Application $app
     */
    protected $app;

    /**
     * Object constructor.
     *
     * @access public
     * @param Silex\Application $app Silex application
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    private function getAllRoles(Application $app)
    {
        $return = array();
        $usersModel = new UsersModel($app);
        $list = $usersModel->getRoles();
        foreach ($list as $value) {
            $return[$value['id']] = $value['role_id'];
        }
        return $return;
    }
    
    /**
     * Form builder.
     *
     * @access public
     * @param FormBuilderInterface $builder
     * @param array $options
     *
     * @return FormBuilderInterface
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        return $builder
        ->add(
            'id',
            'hidden',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Type(array('type' => 'digit'))
                )
            )
        )
        ->add(
            'role_id',
            'choice',
            array(
                'choices' => $this->getAllRoles($this->app),
                'attr' => array(
                        'class' => 'form-control'
                ),
            'label' => 'Set role'
            )
        );


    }

    /**
     * Gets form name.
     *
     * @access public
     *
     * @return string
     */
    public function getName()
    {
        return 'grantsForm';
    }
}
