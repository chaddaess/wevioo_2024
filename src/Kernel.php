<?php

namespace App;
// src/Kernel.php
namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel implements CompilerPassInterface
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container): void
    {
        parent::build($container);

        // Register the compiler pass only for the test environment
        if ('test' === $this->getEnvironment()) {
            $container->addCompilerPass($this);
        }
    }

    public function process(ContainerBuilder $container): void
    {
        if ('test' === $this->getEnvironment()) {
                $container->getDefinition('security.token_storage')->clearTag('kernel.reset');
                $container->getDefinition('doctrine')->clearTag('kernel.reset');
        }
    }
}

