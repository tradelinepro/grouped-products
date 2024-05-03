<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\GroupedProducts;

use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\SalesChannel\AbstractProductCloseoutFilterFactory;
use Shopware\Core\Content\Product\SalesChannel\Listing\ProductListingLoader;
use Shopware\Core\Content\Product\SalesChannel\ProductAvailableFilter;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin\Exception\DecorationPatternException;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SystemConfig\SystemConfigService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\ProductGroupedCollection;
use Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\ProductGroupedDefinition;
use Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\ProductGroupedEntity;
use Tradelinepro\GroupedProducts\Events\ProductGroupedCriteriaLoadEvent;
use Tradelinepro\GroupedProducts\Events\ProductGroupedIdsCriteriaEvent;
use Tradelinepro\GroupedProducts\Events\ProductGroupedLoadedEvent;
use Tradelinepro\GroupedProducts\Events\ProductGroupedStreamCriteriaEvent;

#[Route(defaults: ['_routeScope' => ['store-api']])]
#[Package('inventory')]
class GroupedProductRoute extends AbstractGroupedProductRoute
{
    public const RUNNING_GROUPED_LOADING_BLOCKER = 'groupedProductLoadingIsRunning';

    /**
     * @internal
     */
    public function __construct(
        private readonly EntityRepository $productGroupedRepository,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ProductStreamBuilderInterface $productStreamBuilder,
        private readonly SalesChannelRepository $productRepository,
        private readonly SystemConfigService $systemConfigService,
        private readonly ProductListingLoader $listingLoader,
        private readonly AbstractProductCloseoutFilterFactory $productCloseoutFilterFactory
    ) {
    }

    public function getDecorated(): AbstractGroupedProductRoute
    {
        throw new DecorationPatternException(self::class);
    }

    #[Route(path: '/store-api/product/{productId}/grouped', name: 'store-api.product.grouped', methods: ['POST'], defaults: ['_entity' => 'product'])]
    public function load(string $productId, Request $request, SalesChannelContext $context, Criteria $criteria, ?ProductGroupedCollection $productGroups): GroupedProductRouteResponse
    {
        $context->addExtension(self::RUNNING_GROUPED_LOADING_BLOCKER, new ArrayStruct());

        if (!$productGroups) {
            $productGroups = $this->loadProductGroups($productId, $context);
        }

        $elements = new GroupedProductGroupItemCollection();

        foreach ($productGroups as $productGroup) {
            $clone = clone $criteria;
            if ($this->useProductStream($productGroup)) {
                $element = $this->loadByStream($productGroup, $context, $clone);
            } else {
                $element = $this->loadByIds($productGroup, $context, $clone);
            }

            $elements->add($element);
        }

        $this->eventDispatcher->dispatch(new ProductGroupedLoadedEvent($elements, $context));

        $context->removeExtension(self::RUNNING_GROUPED_LOADING_BLOCKER);

        return new GroupedProductRouteResponse($elements);
    }

    private function loadProductGroups(string $productId, SalesChannelContext $context): ProductGroupedCollection
    {
        $criteria = new Criteria();
        $criteria->setTitle('product-grouped-route');
        $criteria
            ->addAssociation('assignedProducts')
            ->addFilter(new EqualsFilter('product.id', $productId))
            ->addFilter(new EqualsFilter('product.parentId', null))
            ->addFilter(new EqualsFilter('active', true))
            ->addSorting(new FieldSorting('position', FieldSorting::ASCENDING));

        $this->eventDispatcher->dispatch(
            new ProductGroupedCriteriaLoadEvent($criteria, $context)
        );

        /** @var ProductGroupedCollection $productGroups */
        $productGroups = $this->productGroupedRepository
            ->search($criteria, $context->getContext())
            ->getEntities();

        return $productGroups;
    }

    private function loadByStream(ProductGroupedEntity $productGroup, SalesChannelContext $context, Criteria $criteria): GroupedProductGroupItem
    {
        /** @var string $productStreamId */
        $productStreamId = $productGroup->getProductStreamId();

        $filters = $this->productStreamBuilder->buildFilters(
            $productStreamId,
            $context->getContext()
        );

        $criteria->addFilter(...$filters)
            ->setOffset(0)
            ->setLimit($productGroup->getLimit())
            ->addSorting($productGroup->getSorting());

        $criteria = $this->handleAvailableStock($criteria, $context);

        $this->eventDispatcher->dispatch(
            new ProductGroupedStreamCriteriaEvent($productGroup, $criteria, $context)
        );

        $searchResult = $this->listingLoader->load($criteria, $context);

        /** @var ProductCollection $products */
        $products = $searchResult->getEntities();

        $element = new GroupedProductGroupItem();
        $element->setProductGroup($productGroup);
        $element->setProducts($products);
        $element->setStreamId($productGroup->getProductStreamId());

        $element->setTotal($products->count());

        return $element;
    }

    private function loadByIds(ProductGroupedEntity $productGroup, SalesChannelContext $context, Criteria $criteria): GroupedProductGroupItem
    {
        $element = new GroupedProductGroupItem();
        $element->setProductGroup($productGroup);
        $element->setProducts(new ProductCollection());
        $element->setTotal(0);

        if (!$productGroup->getAssignedProducts()) {
            return $element;
        }

        $productGroup->getAssignedProducts()->sortByPosition();

        $ids = \array_values($productGroup->getAssignedProducts()->getProductIds());

        $filter = new ProductAvailableFilter(
            $context->getSalesChannel()->getId(),
            ProductVisibilityDefinition::VISIBILITY_LINK
        );

        if (!\count($ids)) {
            return $element;
        }

        $criteria->setIds($ids);
        $criteria->addFilter($filter);
        $criteria->addAssociation('options.group');

        $criteria = $this->handleAvailableStock($criteria, $context);

        $this->eventDispatcher->dispatch(
            new ProductGroupedIdsCriteriaEvent($productGroup, $criteria, $context)
        );

        $result = $this->productRepository
            ->search($criteria, $context);

        /** @var ProductCollection $products */
        $products = $result->getEntities();
        foreach ($products as $product) {
            /** @var SalesChannelProductEntity $product */
            $product->inStock = $this->inStock($product);
        }

        $products->sortByIdArray($ids);

        $element->setProducts($products);
        $element->setTotal(\count($products));

        return $element;
    }

    private function handleAvailableStock(Criteria $criteria, SalesChannelContext $context): Criteria
    {
        $salesChannelId = $context->getSalesChannel()->getId();
        $hide = $this->systemConfigService->get('core.listing.hideCloseoutProductsWhenOutOfStock', $salesChannelId);

        if (!$hide) {
            return $criteria;
        }

        $closeoutFilter = $this->productCloseoutFilterFactory->create($context);
        $criteria->addFilter($closeoutFilter);

        return $criteria;
    }

    private function useProductStream(ProductGroupedEntity $productGroup): bool
    {
        return $productGroup->getType() === ProductGroupedDefinition::TYPE_PRODUCT_STREAM
            && $productGroup->getProductStreamId() !== null;
    }

    private function inStock(SalesChannelProductEntity $product): bool
    {
        return $product->getAvailable() && $product->getChildCount() <= 0
            && $product->getCalculatedMaxPurchase() > 0;
    }
}
