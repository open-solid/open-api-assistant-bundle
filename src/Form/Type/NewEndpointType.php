<?php

namespace OpenSolid\OpenApiAssistantBundle\Form\Type;

use OpenSolid\OpenApiAssistantBundle\Model\NewEndpointModel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewEndpointType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('method', ChoiceType::class, [
                'choices' => [
                    'POST' => 'POST',
                    'GET' => 'GET',
                    'PUT' => 'PUT',
                    'PATCH' => 'PATCH',
                    'DELETE' => 'DELETE',
                ],
                'invalid_message' => 'Please select a valid method.',
            ])
            ->add('uri')
            ->add('req', HiddenType::class)
            ->add('res', HiddenType::class)
            ->add('openapi', HiddenType::class)
            ->add('dir')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NewEndpointModel::class,
        ]);
    }
}
