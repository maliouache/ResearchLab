<?php

namespace ResearchLabUserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ResearchLabUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
