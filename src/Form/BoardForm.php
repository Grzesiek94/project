<?php
/**
 * Board form.
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
use Model\BoardModel;

/**
 * Class BoardForm.
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
class BoardForm extends AbstractType
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
            'users_question_id',
            'hidden'
        )
        ->add(
            'question',
            'text',
            array(
                'constraints' => array(
                    new Assert\NotBlank(),
                    new Assert\Length(array('min' => 3, 'max' => 250))
                ),
                'attr' => array(
                        'class' => 'form-control',
                        'placeholder' => 'Ask a question'
                ),
                'label' => false
            )
        )
        ->add(
            'users_answer_id',
            'hidden'
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
        return 'boardForm';
    }
}
