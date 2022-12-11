<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('password', PasswordType::class, [
                'hash_property_path' => 'password',
                'mapped' => false,
                'label' => 'utilisateur.mot_de_passe',
            ])
            ->add('prenom', null, [
                'label' => 'utilisateur.prenom',
            ])
            ->add('nom', null, [
                'label' => 'utilisateur.nom',
            ])
            ->add('Sauvegarder', SubmitType::class, [
                'label' => 'form.sauvegarder',
                'attr' => [
                    'class' => 'btn btn-primary mx-auto d-block',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
