<?php

namespace Shopping\ApiDeprecationBundle;

use Shopping\ApiDeprecationBundle\DependencyInjection\ShoppingApiDeprecationExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ShoppingApiDeprecationBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new ShoppingApiDeprecationExtension();
    }
}