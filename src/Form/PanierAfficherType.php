<?php

namespace App\Form;

use Assert\NotBlank;
use App\Entity\Facture;
use App\Entity\Client;
use App\Entity\ModePaiement;
use Symfony\Component\Form\AbstractType;
use App\Repository\ModePaiementRepository;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class PanierAfficherType extends AbstractType
{
    public function __construct(
        protected TranslatorInterface $translator,
        protected ModePaiementRepository $modePaiementRepository
        )
    {}
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $netApayer = $options['netApayer'];
        $builder
            ->add('nomClient', TextType::class, [
                'label' => $this->translator->trans("Nom pour la facture"),
                'required' => true,
                'attr' => [
                    'class' => "form-control",
                    'placeholder' => $this->translator->trans("Nom pour la facture"),
                    
                ]
            ])
            ->add('contactClient', TextType::class, [
                'label' => $this->translator->trans("Numéro de téléphone"),
                'required' => true,
                'attr' => [
                    'class' => "form-control",
                    'placeholder' => $this->translator->trans("Numéro de téléphone"),
                ]
            ])
            ->add('emailClient', TextType::class, [
                'label' => $this->translator->trans("Email du client"),
                'required' => true,
                'attr' => [
                    'class' => "form-control",
                    'placeholder' => $this->translator->trans("Email du client"),
                ]
            ])
            ->add('adresseClient', TextType::class, [
                'label' => $this->translator->trans("Adresse du client"),
                'required' => true,
                'attr' => [
                    'class' => "form-control",
                    'placeholder' => $this->translator->trans("Adresse du client"),
                ]
            ])
            ->add('remarques', TextType::class, [
                'label' => $this->translator->trans("Remarques"),
                'required' => false,
                'attr' => [
                    'class' => "form-control",
                    'placeholder' => $this->translator->trans("Remarques"),
                ]
            ])
            ->add('avance', NumberType::class, [
                'label' => false,
                'required' => true,
                'attr' => [
                    'min' => 0,
                    'max' => $netApayer,
                    'value' => $netApayer,
                    'class' => "form-control",
                    'placeholder' => $this->translator->trans("L'avance doit être <= à ").number_format($netApayer, 0, '', ' '),
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => $this->translator->trans("L'avance ne peut pas être vide!"),
                    ]),
                    new Assert\PositiveOrZero(),
                    new Assert\LessThanOrEqual([
                        'value' => $netApayer,
                        'message' => $this->translator->trans("L'avance doit être inférieure ou égale à ").number_format($netApayer, 0, '', ' ')." ! ",
                    ])
                ]
            ])
            ->add('modePaiement', EntityType::class, [
                'class' => ModePaiement::class,
                'choice_label' => 'modePaiement',
                'required' => true,
                'attr' => [
                    'class' => 'form-control select2-show-search',
                    'placeholder' => $this->translator->trans('- - -'),
                ],
                'query_builder' => function(ModePaiementRepository $modePaiementRepository){
                    
                    return $modePaiementRepository->createQueryBuilder('m')->orderBy('m.modePaiement');
                },
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'data_class' => Facture::class
        ]);

        $resolver->setRequired('netApayer');
    }
}
