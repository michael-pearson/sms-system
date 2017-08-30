<?php

namespace SmsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class SmsType extends AbstractType
{
    /**
     * Overrides the Symfony form builder functionality for the SMS type.
     *
     * @param FormBuilderInterface $_builder
     * @param array $_options
     * @return void
     */
    public function buildForm(FormBuilderInterface $_builder, array $_options):void
    {
        $_builder
            ->add('number', TextType::class)
            ->add('message', TextareaType::class)
            ->add('save', SubmitType::class, array('label' => 'Send SMS'));
    }
}