<?php

namespace MeloLab\BioGestion\FileUploadBundle\Twig;

use MeloLab\BioGestion\ProductivityBundle\Form\SelectEmployeeType;
use MeloLab\BioGestion\ProductivityBundle\Service\FilterHandler;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * @author Andreas Schueller <aschueller@bio.puc.cl>
 */
class HelperExtension extends \Twig_Extension
{
    private $container;
    
    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }
    
    public function getFilters()
    {
        return array(
            //'price' => new \Twig_Filter_Method($this, 'priceFilter'),
        );
    }
    
    public function getFunctions() {
        return array(
            'fileupload_max_file_size' => new \Twig_Function_Method($this, 'fileuploadMaxFileSize'),
            'fileupload_accepted_file_types' => new \Twig_Function_Method($this, 'fileuploadAcceptedFileTypes'),
        );
    }
    
    /**
     * Read the bundle configuration and returns the maximal file size in bytes
     * for the given mapping.
     * @return int
     */
    public function fileuploadMaxFileSize($mapping) {
        if (isset($this->container->getParameter('melolab_biogestion_fileupload.mappings')[$mapping]['max_file_size'])) {
            $value = $this->container->getParameter('melolab_biogestion_fileupload.mappings')[$mapping]['max_file_size'];
        } else {
            $value = $this->container->getParameter('melolab_biogestion_fileupload.max_file_size');
        }
        
        //TODO: Finish parsing of file sizes with symbols like 5M
        $value = trim($value);
        switch (substr($value, -1)) {
            case 'K':
                $value = substr($value, 0, -1) * 1024;
                break;
            case 'M':
                $value = substr($value, 0, -1) * 1024 * 1024;
                break;
            case 'G':
                $value = substr($value, 0, -1) * 1024 * 1024 * 1024;
                break;
            case 'T':
                $value = substr($value, 0, -1) * 1024 * 1024 * 1024 * 1024;
                break;
        }
        
        return $value;
    }
    
    /**
     * Read the bundle configuration and returns the accepted file types regexp
     * for the given mapping.
     * @return int
     */
    public function fileuploadAcceptedFileTypes($mapping) {
        if (isset($this->container->getParameter('melolab_biogestion_fileupload.mappings')[$mapping]['accepted_file_types'])) {
            return $this->container->getParameter('melolab_biogestion_fileupload.mappings')[$mapping]['accepted_file_types'];
        } else {
            return $this->container->getParameter('melolab_biogestion_fileupload.accepted_file_types');
        }
    }

    public function getName()
    {
        return 'helper_extension';
    }
}