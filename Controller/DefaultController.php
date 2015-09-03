<?php

namespace MeloLab\BioGestion\FileUploadBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Default controller of this bundle.
 * 
 * @author Andreas Schueller <aschueller@bio.puc.cl>
 */
class DefaultController extends Controller
{
    
    /**
     * Download attached files
     * @Route("/download/{id}/{mapping}", name="biogestion_fileupload_download")
     */
    public function downloadAction($id, $mapping) {
//        var_dump($this->get('vich_uploader.metadata_reader')->getUploadableFields(\Symfony\Component\Security\Core\Util\ClassUtils::getRealClass($lr)));
//        var_dump($this->container->getParameter('melolab_biogestion_fileupload.mappings'));
//        var_dump($this->container->getParameter('vich_uploader.mappings')); die();
        
        $mappings = $this->container->getParameter('melolab_biogestion_fileupload.mappings');
        $config = $mappings[$mapping];
                
        $entity = $this->getDoctrine()->getManager()->getRepository($config['entity'])->$config['repository_method']($id);

        if (!$entity) {
            throw $this->createNotFoundException($this->get('translator')->trans('file.entity_not_found'));
        }
        
        // Security
        if (true === $config['allow_anonymous_downloads']) {
            if (true === $this->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED') and false === $this->get('security.context')->isGranted('VIEW', $entity)) {
                throw new AccessDeniedException();            
            }
        } else {
            if (false === $this->get('security.context')->isGranted('VIEW', $entity)) {
                throw new AccessDeniedException();
            }
        }
        
        $mimeTypes = array(
            'pdf' => 'application/pdf',
            'txt' => 'text/plain',
            'html' => 'text/html',
            'exe' => 'application/octet-stream',
            'zip' => 'application/zip',
            'doc' => 'application/msword',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
            'gif' => 'image/gif',
            'png' => 'image/png',
            'jpeg' => 'image/jpg',
            'jpg' => 'image/jpg',
            'php' => 'text/plain'
        );
        
        // Get filename
        $filename = $entity->$config['filename_getter']();
        
        if (!$filename) {
            throw $this->createNotFoundException($this->get('translator')->trans('file.file_not_found'));
        }
        
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $uploadFolder = $this->container->getParameter('vich_uploader.mappings')[$config['vich_mapping']]['upload_destination'];
        
        // Full path to file
        $path = $uploadFolder."/".$filename;
        
        // Prepare the http response
        $response = new StreamedResponse();
        $response->setCallback(function() use ($path) {
            $fp = fopen($path, 'rb');
            fpassthru($fp);
        });
        $response->headers->set('Content-Type', $mimeTypes[$ext]); 

        return $response;
    }
}
