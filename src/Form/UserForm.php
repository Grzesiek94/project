<?php
/**
 * User form.
 *
 * @category Form
 * @author Grzegorz Stefański
 * @link wierzba.wzks.uj.edu.pl/~13_stefanski/php
 * @copyright EPI 2015
 */
namespace Form;

use Silex\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Model\UsersModel;

/**
 * Class UserForm.
 *
 * @category Epi
 * @package Form
 * @extends AbstractType
 * @use Symfony\Component\Form\AbstractType
 * @use Symfony\Component\Form\FormBuilderInterface
 * @use Symfony\Component\OptionsResolver\OptionsResolverInterface
 * @use Symfony\Component\Validator\Constraints as Assert
 * @use Model\CategoriesModel
 * @use Silex\Application
 */
class UserForm extends AbstractType
{
 
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
            'name',
            'text',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('min' => 3, 'max' => 10))
                ),
                'attr' => array(
                        'class' => 'form-control',
                        'placeholder' => 'Name'
                ),
            )
        )
        ->add(
            'surname',
            'text',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('min' => 3, 'max' => 20))
                ),
                'attr' => array(
                        'class' => 'form-control',
                        'placeholder' => 'Surname'
                ),
            )
        )
        ->add(
            'email',
            'text',
            array(
                'constraints' => new Assert\Email(),
                'attr' => array(
                        'class' => 'form-control',
                        'placeholder' => 'e-mail'
                ),
            )
        )
        ->add(
            'website',
            'text',
            array(
                'constraints' => array(
                    new Assert\Length(array('min' => 10, 'max' => 50))
                ),
                'attr' => array(
                        'class' => 'form-control',
                        'placeholder' => 'Your website'
                ),
            )
        )
        ->add(
            'facebook',
            'text',
            array(
                'constraints' => array(
                    new Assert\Length(array('max' => 50))
                ),
                'attr' => array(
                        'class' => 'form-control',
                        'placeholder' => 'Your facebook'
                ),
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
        return 'userForm';
    }
}
