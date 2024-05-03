<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\Product\SalesChannel;

use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\AggregationResult\AggregationResultCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\IdSearchResult;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\ProductGroupedEntity;
use Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\Search\ProductGroupedCriteriaBuilder;
use Tradelinepro\GroupedProducts\Extension\GroupedProductSearchExtension;

#[Package('core')]
class ProductSalesChannelRepositoryDecorator extends SalesChannelRepository
{
    public function __construct(
        private readonly SalesChannelRepository $innerService,
        private readonly ProductGroupedCriteriaBuilder $criteriaBuilder,
        private readonly EntityRepository $productGroupedRepository
    ) {
    }

    public function search(Criteria $criteria, SalesChannelContext $salesChannelContext): EntitySearchResult
    {
        return $this->innerService->search($criteria, $salesChannelContext);
    }

    public function aggregate(Criteria $criteria, SalesChannelContext $salesChannelContext): AggregationResultCollection
    {
        return $this->innerService->aggregate($criteria, $salesChannelContext);
    }

    public function searchIds(Criteria $criteria, SalesChannelContext $salesChannelContext): IdSearchResult
    {
        $idSearchResult = $this->innerService->searchIds($criteria, $salesChannelContext);

        if ($criteria->excludeCustomProductTypes ?? false) {
            return $idSearchResult;
        }

        if (!$salesChannelContext->hasExtension(GroupedProductSearchExtension::EXTENSION_NAME)) {
            return $idSearchResult;
        }

        $salesChannelContext->removeExtension(GroupedProductSearchExtension::EXTENSION_NAME);
        if ($idSearchResult->getTotal() <= 0) {
            return $idSearchResult;
        }

        $groups = $this->productGroupedRepository->search(
            (new Criteria())->addFilter(new EqualsAnyFilter('assignedProducts.productId', $idSearchResult->getIds())),
            $salesChannelContext->getContext()
        );
        if ($groups->count() <= 0) {
            return $idSearchResult;
        }

        $allIds = $idSearchResult->getIds();
        foreach ($groups as $group) {
            /** @var ProductGroupedEntity $group */
            $allIds[] = $group->getProductId();
        }

        $allIds = \array_values(\array_unique($allIds));
        if ($idSearchResult->getTotal() === \count($allIds)) {
            return $idSearchResult;
        }

        $idSearchResultMerged = $this->innerService->searchIds(
            $this->criteriaBuilder->build(new Criteria($allIds), $salesChannelContext)
                ->setTotalCountMode($criteria->getTotalCountMode())
                ->setLimit(\count($allIds))
                ->assign([
                    'sorting' => $criteria->getSorting(),
                    'aggregations' => $criteria->getAggregations(),
                    'associations' => $criteria->getAssociations(),
                ]),
            $salesChannelContext
        );
        if ($idSearchResultMerged->getTotal() <= 0) {
            return $idSearchResult;
        }

        $criteria->setIds($idSearchResultMerged->getIds());

        return $idSearchResultMerged;
    }
}
