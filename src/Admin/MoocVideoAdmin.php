<?php

namespace AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MoocVideoAdmin extends AbstractAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'filter_emojis' => true,
            ])
            ->add('youtubeUrl', TextType::class, [
                'label' => 'Lien de la vidéo',
                'filter_emojis' => true,
            ])
            ->add('displayOrder', IntegerType::class, [
                'label' => 'Ordre d\'affichage',
                'scale' => 0,
                'attr' => [
                    'min' => 0,
                ],
            ])
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name', null, [
                'label' => 'Nom',
                'show_filter' => true,
            ])
            ->add('youtubeUrl', null, [
                'label' => 'Lien de la vidéo',
                'show_filter' => true,
            ])
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name', null, [
                'label' => 'Nom',
            ])
            ->add('slug', null, [
                'label' => 'Slug',
            ])
            ->add('youtubeUrl', null, [
                'label' => 'Lien de la vidéo',
            ])
            ->add('displayOrder', null, [
                'label' => 'Ordre d\'affichage',
            ])
            ->add('_action', null, [
                'virtual_field' => true,
                'actions' => [
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }
}
