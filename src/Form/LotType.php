<?php

namespace App\Form;

use App\Entity\Lot;
use App\Entity\TypeProduit;
use App\Repository\TypeProduitRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class LotType extends AbstractType
{
    public function __construct(
        protected TranslatorInterface $translator
        )
    {}
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('reference', TextType::class, [
                'label' => $this->translator->trans("Référence du lot"),
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => $this->translator->trans("Veuillez saisir la référence"),
                ]
            ])
            ->add('quantite', IntegerType::class, [
                'label' => $this->translator->trans("Quantité du lot"),
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => $this->translator->trans("Quantité"),
                ]
            ])
            
            ->add('prixAchat', IntegerType::class, [
                'label' => $this->translator->trans("Prix d'achat"),
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => $this->translator->trans("Prix de vente"),
                ]
            ])
            ->add('typeProduit', EntityType::class, [
                'class' => TypeProduit::class,
                'choice_label' => 'typeProduit',
                'required' => true,
                'placeholder' => $this->translator->trans('- - -'),
                'attr' => [
                    'class' => 'form-control select2-show-search',
                ],
                'query_builder' => function(TypeProduitRepository $typeProduitRepository){
                    
                    return $typeProduitRepository->createQueryBuilder('t')->orderBy('t.typeProduit');
                    
                },
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lot::class,
        ]);
    }
}
