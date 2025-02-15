<?php

namespace App\Form;

use App\Entity\LostObject;
use App\Entity\FoundObject;
use App\Enum\StatusFoundObjectEnum;
use App\Enum\StatusLostObjectEnum;
use App\Repository\CategoryRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ObjectFormType extends AbstractType
{
    private  $categoryRepository;
    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $categories = $this->categoryRepository->findAll();  // Récupère toutes les catégories

        // Crée un tableau de catégories pour le formulaire
        $categoryChoices = [];
        foreach ($categories as $category) {
            $categoryChoices[$category->getName()] = $category->getId(); 
        }

        $builder
            ->add('title', TextType::class, [
                'label' => 'Nom de l\'objet',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
            ])
            ->add('category', ChoiceType::class, [
                'label' => 'Catégorie',
                'choices' => $categoryChoices,  // Afficher les catégories dynamiquement
            ])
            ->add('location', TextType::class, [
                'label' => 'Lieu',
            ])
            ->add('latitude', TextType::class, [
                'label' => 'Latitude',
                'required' => false,
            ])
            ->add('longitude', TextType::class, [
                'label' => 'Longitude',
                'required' => false,
            ])
            ->add('objectType', ChoiceType::class, [
                'label' => 'Type d\'objet',
                'choices' => [
                    'Objet Perdu' => 'lost',
                    'Objet Trouvé' => 'found',
                ],
                'mapped' => false, // Ce champ ne correspond pas directement à une entité
            ]);           
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}