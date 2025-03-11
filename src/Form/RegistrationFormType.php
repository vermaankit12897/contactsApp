<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fullName', TextType::class, [
                'constraints' => [new NotBlank()]
            ])
            ->add('email', EmailType::class, [
                'constraints' => [new NotBlank()]
            ])
            ->add('plainPassword', RepeatedType::class, [  
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options' => ['attr' => ['class' => 'form-control', 'placeholder' => 'Password']],
                'second_options' => ['attr' => ['class' => 'form-control', 'placeholder' => 'Re-enter Password']],
                'constraints' => [new NotBlank()]
            ])
            ->add('dob', DateType::class, [  
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('profilePicture', FileType::class, [  
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Please upload a valid image file (JPEG/PNG)',
                    ])
                ],
            ]);
    }
}
