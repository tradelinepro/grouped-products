<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\Search;

use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Content\Product\SalesChannel\ProductAvailableFilter;
use Shopware\Core\Content\Product\SalesChannel\ProductCloseoutFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Grouping\FieldGrouping;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;

#[Package('core')]
class ProductGroupedCriteriaBuilder
{
    public function __construct(private readonly SystemConfigService $systemConfigService)
    {
    }

    public function build(Criteria $criteria, SalesChannelContext $salesChannelContext): Criteria
    {
        $salesChannelId = $salesChannelContext->getSalesChannel()->getId();

        $criteria->addFilter(new ProductAvailableFilter($salesChannelId, ProductVisibilityDefinition::VISIBILITY_SEARCH))
            ->addFilter(new NotFilter(NotFilter::CONNECTION_AND, [new EqualsFilter('displayGroup', null)]))
            ->addGroupField(new FieldGrouping('displayGroup'));

        if ($this->systemConfigService->get('core.listing.hideCloseoutProductsWhenOutOfStock', $salesChannelId)) {
            $criteria->addFilter(new ProductCloseoutFilter());
        }

        return $criteria;
    }
}
