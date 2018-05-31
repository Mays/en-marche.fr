<?php

namespace AppBundle\Admin;

use AppBundle\Form\PurifiedTextareaType;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
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
            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'disabled' => true,
            ])
            ->add('content', PurifiedTextareaType::class, [
                'label' => 'Contenu',
                'attr' => ['class' => 'ck-editor'],
                'purifier_type' => 'enrich_content',
            ])
            ->add('youtubeUrl', TextType::class, [
                'label' => 'Lien de la vidéo Youtube',
                'filter_emojis' => true,
            ])
            ->add('youtubeThumbnailUrl', TextType::class, [
                'label' => 'Lien de la miniature Youtube',
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
                'label' => 'Lien de la vidéo Youtube',
            ])
            ->add('youtubeThumbnailUrl', null, [
                'label' => 'Miniature Youtube',
                'template' => 'admin/mooc/thumbnail.html.twig',
            ])
            ->add('chapter', null, [
                'label' => 'Chapitre associé',
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

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('show');
    }
}
