<?php

namespace Beinarovic\AirpayBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AirpayPaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('_cmd', 'text', array(
                'mapped'    => false,
                'data'      => 'payment'
                )
            )
            ->add('merchant_id', 'text', array('property_path' => 'merchantId'))
            ->add('amount', 'text', array('property_path' => 'amount'))
            ->add('currency', 'text', array('property_path' => 'currency'))
            ->add('invoice', 'text', array('property_path' => 'invoice'))
            ->add('language', 'text', array('property_path' => 'language'))
            ->add('cl_fname', 'text', array('property_path' => 'clFname'))
            ->add('cl_lname', 'text', array('property_path' => 'clLname'))
            ->add('cl_email', 'text', array('property_path' => 'clEmail'))
            ->add('cl_country', 'text', array('property_path' => 'clCountry'))
            ->add('cl_city', 'text', array('property_path' => 'clCity'))
            ->add('description', 'text', array('property_path' => 'description'))
            ->add('hash', 'text')
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Beinarovic\AirpayBundle\Entity\AirpayPayment'
        ));
    }
    
    public function getDefaultOptions(array $options)
    {
        $options = parent::getDefaultOptions($options);
        $options['csrf_protection'] = false;

        return $options;
    }

    public function getName()
    {
        return 'beinarovic_airpaybundle_airpaypaymenttype';
    }
}
