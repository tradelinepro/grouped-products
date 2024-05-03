<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\Installer;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Shopware\Core\Framework\Uuid\Uuid;
use Tradelinepro\Core\Installer\AbstractInstaller;

#[Package('core')]
final class TaxInstaller extends AbstractInstaller
{
    public function activate(ActivateContext $context): void
    {
        $context = $context->getContext();
        $taxRepository = $this->container->get('tax.repository');

        $criteria = (new Criteria())
            ->addFilter(new EqualsFilter('taxRate', 0))
            ->setLimit(1);

        $searchResults = $taxRepository->searchIds($criteria, $context);

        if ($searchResults->getTotal() > 0) {
            $taxId = $searchResults->firstId();
        } else {
            $taxId = Uuid::randomHex();

            $taxRepository->create([[
                'id' => $taxId,
                'taxRate' => 0,
                'name' => 'Reduced rate 2',
                'position' => 0,
            ]], $context);
        }

        $configRepository = $this->container->get('system_config.repository');

        $configRepository->upsert([[
            'id' => '149ecc2af2607950465ea3e479774e0a',
            'configurationKey' => 'TradelineproGroupedProducts.config.tax',
            'configurationValue' => $taxId,
        ]], $context);
    }
}
