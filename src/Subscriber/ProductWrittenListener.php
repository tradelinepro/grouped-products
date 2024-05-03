<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\Subscriber;

use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Product\Events\ProductIndexerEvent;
use Shopware\Core\Content\Product\SalesChannel\Detail\CachedProductDetailRoute;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Adapter\Cache\CacheInvalidator;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Event\BeforeDeleteEvent;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\MultiFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tradelinepro\GroupedProducts\GroupedProducts\GroupedProductRoute;
use Tradelinepro\GroupedProducts\Installer\CustomFieldInstaller;
use Tradelinepro\GroupedProducts\Service\FromPriceCalculator;

#[Package('core')]
class ProductWrittenListener implements EventSubscriberInterface
{
    private array $productIdsToDelete = [];

    private ?EntityCollection $connectedGroupedProductsWithDeleted = null;

    public function __construct(
        private readonly EntityRepository $productRepository,
        private readonly EntityRepository $salesChannelRepository,
        private readonly FromPriceCalculator $fromPriceCalculator,
        private readonly AbstractSalesChannelContextFactory $salesChannelContextFactory,
        private readonly LoggerInterface $groupedProductsLogger,
        private readonly CacheInvalidator $cacheInvalidator,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductIndexerEvent::class => 'update',
            BeforeDeleteEvent::class => 'beforeDeletion',
        ];
    }

    public function beforeDeletion(BeforeDeleteEvent $event): void
    {
        $this->productIdsToDelete = $event->getIds('product');

        if (!$this->productIdsToDelete) {
            return;
        }

        $this->connectedGroupedProductsWithDeleted = $this->getConnectedGroupedProducts(
            $this->productIdsToDelete,
            $event->getContext()
        );
    }

    public function update(ProductIndexerEvent $event): void
    {
        $context = $event->getContext();

        if ($context->getExtension(GroupedProductRoute::RUNNING_GROUPED_LOADING_BLOCKER)) {
            return;
        }

        $products = $this->getConnectedGroupedProducts(
            $event->getIds(),
            $context
        );

        if ($products->count() <= 0) {
            // product deleted
            $products = $this->connectedGroupedProductsWithDeleted;

            if (!$products || $products->count() <= 0) {
                return;
            }
        }

        $salesChannelId = null;
        $criteria = $this->createCriteria($salesChannelId);
        $salesChannels = $this->salesChannelRepository->search($criteria, $context);

        $productToUpdate = [];
        /** @var SalesChannelEntity $salesChannel */
        foreach ($salesChannels as $salesChannel) {
            $salesChannelContext = $this->salesChannelContextFactory->create(Uuid::randomHex(), $salesChannel->getId());

            try {
                $calculatedGroupedPrices = $this->fromPriceCalculator->calculate(
                    $products,
                    $salesChannelContext,
                );

                foreach ($calculatedGroupedPrices as $productId => $calculatedGroupedPrice) {
                    $groupedProductPrice = $calculatedGroupedPrice;
                    if (isset($productToUpdate[$productId])) {
                        $groupedProductPrice += $productToUpdate[$productId]['customFields'][CustomFieldInstaller::CUSTOM_FIELD_GROUPED_PRICE];
                    }
                    $productToUpdate[$productId] = [
                        'id' => $productId,
                        'customFields' => [
                            CustomFieldInstaller::CUSTOM_FIELD_GROUPED_PRICE => $groupedProductPrice,
                        ],
                    ];
                }
            } catch (\Exception $exception) {
                $this->groupedProductsLogger->error('Reindex error: ' . $exception->getMessage());
            }
        }

        if (!$productToUpdate) {
            return;
        }

        $context->addExtension(GroupedProductRoute::RUNNING_GROUPED_LOADING_BLOCKER, new ArrayStruct());
        $this->productRepository->update(\array_values($productToUpdate), $context);
        $context->removeExtension(GroupedProductRoute::RUNNING_GROUPED_LOADING_BLOCKER);

        $productIds = \array_keys($productToUpdate);

        try {
            $this->cacheInvalidator->invalidate(
                \array_map([CachedProductDetailRoute::class, 'buildName'], $productIds)
            );
        } catch (\Exception $exception) {
            $this->groupedProductsLogger->error('Invalidation error: ' . $exception->getMessage());
        }
    }

    private function getConnectedGroupedProducts(array $productIds, Context $context): EntityCollection
    {
        return $this->productRepository->search(
            (new Criteria())
                ->addFilter(
                    new MultiFilter(
                        MultiFilter::CONNECTION_OR,
                        [
                            new EqualsAnyFilter('groups.assignedProducts.productId', $productIds),
                            new EqualsAnyFilter('groups.productId', $productIds),
                        ]
                    )
                )
                ->addAssociation('groups.assignedProducts.visibilities')
                ->addAssociation('visibilities'),
            $context
        )->getEntities();
    }

    private function createCriteria(?string $salesChannelId = null): Criteria
    {
        $criteria = $salesChannelId ? new Criteria([$salesChannelId]) : new Criteria();
        $criteria->addAssociation('domains');
        $criteria->addFilter(
            new NotFilter(
                NotFilter::CONNECTION_AND,
                [new EqualsFilter('domains.id', null)]
            )
        );

        $criteria->addAssociation('type');
        $criteria->addFilter(new EqualsFilter('type.id', Defaults::SALES_CHANNEL_TYPE_STOREFRONT));

        return $criteria;
    }
}
