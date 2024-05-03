<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\Installer;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\System\CustomField\CustomFieldTypes;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Tradelinepro\Core\Installer\InstallerInterface;

#[Package('core')]
class CustomFieldInstaller implements InstallerInterface
{
    final public const CUSTOM_FIELD_IS_GROUPED = 'isGrouped';
    final public const CUSTOM_FIELD_GROUPED_PRICE = 'groupedProductPrice';

    private readonly EntityRepository $customFieldRepository;

    public function __construct(?ContainerInterface $container)
    {
        $this->customFieldRepository = $container->get('custom_field.repository');
    }

    public function activate(ActivateContext $context): void
    {
        $context = $context->getContext();
        $this->customFieldRepository->upsert([[
            'id' => '1605776802bd3f2cb27bae57166698c5',
            'name' => static::CUSTOM_FIELD_IS_GROUPED,
            'type' => CustomFieldTypes::BOOL,
            'active' => true,
        ]], $context);

        $this->customFieldRepository->upsert([[
            'id' => '018da4bf39077278a75e8b66be267f7e',
            'name' => static::CUSTOM_FIELD_GROUPED_PRICE,
            'type' => CustomFieldTypes::JSON,
            'active' => true,
        ]], $context);
    }
}
