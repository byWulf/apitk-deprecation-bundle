<?php

namespace Ofeige\Rfc18Bundle;

use Ofeige\Rfc18Bundle\DependencyInjection\OfeigeRfc18Extension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OfeigeRfc18Bundle extends Bundle
{
    public function getContainerExtension()
    {
        return new OfeigeRfc18Extension();
    }
}