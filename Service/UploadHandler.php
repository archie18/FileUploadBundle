<?php

namespace MeloLab\BioGestion\FileUploadBundle\Service;

use Doctrine\ORM\EntityManager;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Translation\TranslatorInterface;

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * This service provides handles file ajax uploads
 *
 * @author Andreas Schueller <aschueller@bio.puc.cl>
 */
class UploadHandler {
    
    /* @var $em EntityManager */
    private $em;
    
    /* @var $container ContainerInterface */
    private $container;
    
    /* @var $securityContext SecurityContext */
    private $securityContext;

    /* @var $translator TranslatorInterface */
    private $translator;
    
    /* @var FormFactoryInterface $formFactory */
    private $formFactory;
    
    /* @var RouterInterface $router */
    private $router;

    public function __construct(EntityManager $em, ContainerInterface $container, SecurityContext $securityContext, TranslatorInterface $translator, FormFactoryInterface $formFactory, RouterInterface $router) {
        $this->em = $em;
        $this->container = $container;
        $this->securityContext = $securityContext;
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->router = $router;
    }
    
    /**
     * Handle AJAX file uploads
     * 
     * @param Request $request The HTTP request
     * @param int $id The entity ID. If ID == 0 a new (to be created) entity is assumed
     * @return Response
     * @throws Exception
     */
    public function handleUpload(Request $request, $id, FormTypeInterface $formType = null) {
        // Get mapping key for config access
        $mapping = $request->query->get('mapping');
        if (!$mapping) {
            throw new RuntimeException($this->translator->trans('file.mapping_not_found'));
        }
        
        $entityClass = $this->container->getParameter('melolab_biogestion_fileupload.mappings')[$mapping]['entity'];
        $repository_method = $this->container->getParameter('melolab_biogestion_fileupload.mappings')[$mapping]['repository_method'];
        if (!$formType) {
            $formType = $this->container->getParameter('melolab_biogestion_fileupload.mappings')[$mapping]['form_type'];
        }
        $fileField = $this->container->getParameter('melolab_biogestion_fileupload.mappings')[$mapping]['file_field'];
        $allowAnonymousUploads = $this->container->getParameter('melolab_biogestion_fileupload.mappings')[$mapping]['allow_anonymous_uploads'];
//        var_dump($this->get('vich_uploader.metadata_reader')->getUploadableFields(\Symfony\Component\Security\Core\Util\ClassUtils::getRealClass($lr)));
//        var_dump($this->container->getParameter('melolab_biogestion_fileupload.mappings'));
//        var_dump($this->container->getParameter('vich_uploader.mappings')); die();

        // Fetch existing entity
        if ($id) {
            $this->em->clear(); // Clear EntityManager to fetch a fresh copy

            $entity = $this->em->getRepository($entityClass)->$repository_method($id);

            if (!$entity) {
                throw new NotFoundHttpException($this->translator->trans('file.entity_not_found'));
            }

            // Security
            if (false === $allowAnonymousUploads && false === $this->securityContext->isGranted('EDIT', $entity)) {
                throw new AccessDeniedException();
            }
        }
        // Create new entity
        else {
            $entity = new $entityClass();

            // Security
            if (false === $allowAnonymousUploads && false === $this->securityContext->isGranted('CREATE', $entity)) {
                throw new AccessDeniedException();
            }
        }

        $form = $this->formFactory->create(new $formType(), $entity, array());

//        // Submit form without overwriting entity values with nulls.
//        // Actually, we just want to validate the token and file upload
//        $form->submit($request, false);
        // TODO: $form->submit($request, false) using GrantType does not work since $form->get('investigators')->isValid() === false
        // and this is due to $form->get('investigators')->isSubmitted() === false. Likely a bug of $form->submit().
        // Workaround: We use a GrantFileType which only contains file fields and use $form->handleRequest($request).
        $form->handleRequest($request);

        $errorMessages = array();
        $ok = true;
        $file = $form->get($fileField)->getData();
        $response = array('files' => array(array(
            'name' => $file->getClientOriginalName() ,
            'size' => $file->getClientSize(),
            'type' => $file->getClientMimeType(),
        )));

        if ($form->isValid()) {
            
            // Persist entity
            $this->em->persist($entity);
            $this->em->flush();
            
            // Generate new form action with added ID
//            if (!$id and $entity->getId()) {
//                $response['formAction'] = $this->generateUrl('eva_research_ref_create', array('id' => $entity->getId()));
//            }
            
            $response['files'][0]['url'] = $this->router->generate('biogestion_fileupload_download', array('id' => $entity->getId(), 'mapping' => $mapping));
            //'deleteUrl' => '',
            //'deleteType' => 'DELETE',
            
            // Update reference text
//            $response['refText'] = $this->renderView('MeloLabBioGestionResearchBundle:Reference:reference.html.twig', array(
//                'ref' => $entity,
//            ));
            
            // Update PDF file download link
//            if ($entity->getPublicationFile()) {
//                $response['fileText'] = $this->renderView('MeloLabBioGestionResearchBundle:Reference:referencePublicationFile.html.twig', array(
//                    'ref' => $entity,
//                ));
//            }
        } else {
            $ok = false;
            
            // Get file form errors
            foreach ($form->get($fileField)->getErrors() as $error) {
                $errorMessages[] = $error->getMessage();
            }
            
            // Other errors
            if (!$errorMessages) {
                // Check whether php.ini POST_MAX_SIZE was exceeded
                // Source: http://andrewcurioso.com/2010/06/detecting-file-size-overflow-in-php/
                if ( $_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0 ) {
                    $errorMessages[] = $this->translator->trans('file.upload.post_max_size_error');
                } else { // Unknown error. Form did not validate?
                    $errorMessages[] = $this->translator->trans('file.upload.unknown_error');
                }
            }

            $response['files'][0]['error'] = implode('. ', $errorMessages);
        }

        $response['ok'] = $ok;
        
        // Fix for IE9
        if (!in_array('application/json', $request->getAcceptableContentTypes())) {
            $contentType = 'text/plain';
        } else {
            $contentType = 'application/json';
        }
        
        // Expected JSON response format:
        // https://github.com/blueimp/jQuery-File-Upload/wiki/Setup#using-jquery-file-upload-ui-version-with-a-custom-server-side-upload-handler
        return new Response(json_encode($response), 200, array('Content-Type' => $contentType));
    }
    
}
