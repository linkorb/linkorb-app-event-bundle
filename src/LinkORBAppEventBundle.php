<?php

namespace LinkORB\AppEventBundle;

use LinkORB\AppEventBundle\DependencyInjection\LinkORBAppEventExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class LinkORBAppEventBundle extends Bundle
{
    /*
     * Implemented here to allow this bundle's Extension to define a custom
     * alias "linkorb_app_event" ("link_orb_app_event" is the expected alias).
     */
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new LinkORBAppEventExtension();
        }

        return parent::getContainerExtension();
    }
}
