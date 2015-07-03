<?php

namespace MeloLab\BioGestion\FileUploadBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MeloLabBioGestionFileUploadBundle extends Bundle
{

    /**
     * Returns the bundle's container extension.
     *
     * @return ExtensionInterface|null The container extension
     *
     * @throws \LogicException
     *
     * @api
     */
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $basename = preg_replace('/Bundle$/', '', $this->getName());

            $class = $this->getNamespace().'\\DependencyInjection\\'.$basename.'Extension';
            if (class_exists($class)) {
                $extension = new $class();

//                // check naming convention
//                $expectedAlias = Container::underscore($basename);
//                if ($expectedAlias != $extension->getAlias()) {
//                    throw new \LogicException(sprintf(
//                        'The extension alias for the default extension of a '.
//                        'bundle must be the underscored version of the '.
//                        'bundle name ("%s" instead of "%s")',
//                        $expectedAlias, $extension->getAlias()
//                    ));
//                }

                $this->extension = $extension;
            } else {
                $this->extension = false;
            }
        }

        if ($this->extension) {
            return $this->extension;
        }
    }
}
