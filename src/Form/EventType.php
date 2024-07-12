<?php

namespace App\Form;

use App\Entity\Event;
use App\Service\EventService;
use Doctrine\DBAL\Types\TextType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use function Sodium\add;

class EventType extends AbstractType
{
    private array $eventCategories;
    public function __construct(private readonly EventService $eventService){
        $this->eventCategories=$this->eventService->getEventCategories();
    }
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',null,[
                'required'=>'true'
            ])
            ->add('date', null, [
                'widget' => 'single_text',
            ])
            ->add('coordinates', HiddenType::class,[
                'mapped'=>false,
                'required'=>true,
            ])
            ->add('address', HiddenType::class,[
                'required'=>'true'
            ])
            ->add('category',ChoiceType::class,[
                'required'=>'true',
                'choices'=>$this->eventCategories
            ])
            ->add('picture', FileType::class, [
                'required'=>false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png'
                        ],
                        'mimeTypesMessage' => 'Please upload a valid PDF document',
                    ])
                ],

            ])
        ->add('ticketLink')
        ->add('comments',TextareaType::class);

    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
