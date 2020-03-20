<?php

namespace Shopping\ApiTKDeprecationBundle;

use Shopping\ApiTKDeprecationBundle\DependencyInjection\ShoppingApiTKDeprecationExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ShoppingApiTKDeprecationBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new ShoppingApiTKDeprecationExtension();
    }
}
