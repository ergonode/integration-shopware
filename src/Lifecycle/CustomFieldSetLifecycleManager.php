<?php

declare(strict_types=1);

namespace Strix\Ergonode\Lifecycle;

use Psr\Container\ContainerInterface;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Strix\Ergonode\Manager\ProductCustomFieldManager;

class CustomFieldSetLifecycleManager
{
    public const NAME = 'strix_ergonode_custom_fields';

    private static ?self $instance = null;

    private ProductCustomFieldManager $productCustomFieldManager;

    public function __construct(
        ProductCustomFieldManager $productCustomFieldManager
    ) {
        $this->productCustomFieldManager = $productCustomFieldManager;
    }

    public static function getInstance(ContainerInterface $container): self
    {
        if (null === self::$instance) {
            self::$instance = new self(
                $container->get(ProductCustomFieldManager::class)
            );
        }

        return self::$instance;
    }

    public function install(InstallContext $installContext): void
    {
        $this->productCustomFieldManager->initCustomFieldSet($installContext->getContext());
    }
}