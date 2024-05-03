<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\Service;

use Shopware\Core\Content\Product\Aggregate\ProductVisibility\ProductVisibilityDefinition;
use Shopware\Core\Content\Product\SalesChannel\ProductAvailableFilter;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearchResult;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\Product\ProductExtension;
use Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\Aggregate\ProductGroupedAssignedProducts\ProductGroupedAssignedProductsCollection;
use Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\ProductGroupedDefinition;
use Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\ProductGroupedEntity;
use Tradelinepro\GroupedProducts\Extension\GroupedProductExtension;
use Tradelinepro\GroupedProducts\Installer\CustomFieldInstaller;

class FromPriceCalculator
{
    private array $groupedProductsStreamIdCache = [];

    public function __construct(
        private readonly SalesChannelRepository $productRepository,
        private readonly ProductStreamBuilderInterface $productStreamBuilder
    ) {
    }

    public function calculate(EntityCollection $products, SalesChannelContext $salesChannelContext): array
    {
        if ($products->count() <= 0) {
            return [];
        }

        $groupedProducts = $products->filter(function ($product) use ($salesChannelContext) {
            if (!$product->getCustomFields() || !$product->getCustomFields(
            )[CustomFieldInstaller::CUSTOM_FIELD_IS_GROUPED]) {
                return false;
            }

            foreach ($product->getVisibilities() as $visibility) {
                if ($visibility->getSalesChannelId() === $salesChannelContext->getSalesChannelId()) {
                    return true;
                }
            }

            return false;
        });

        $this->assignProductsToGroups($groupedProducts, $salesChannelContext);

        $productGroups = [];

        foreach ($groupedProducts as $product) {
            $groups = $product->getExtension(ProductExtension::GROUPS_EXTENSION_NAME);

            if (!$groups) {
                continue;
            }

            foreach ($groups as $group) {
                if (!$group->isActive()) {
                    continue;
                }
                $key = "{$group->getProductId()}";

                foreach ($group->getAssignedProducts() as $groupAssignedProduct) {
                    if (!isset($productGroups[$key])) {
                        $productGroups[$key] = [false, []];
                    }

                    /** @var SalesChannelProductEntity $assignedProduct */
                    $assignedProduct = $groupAssignedProduct->getProduct();

                    if (!$assignedProduct || !$assignedProduct->getActive()) {
                        continue;
                    }

                    $assignedProduct->getVisibilities()->filterBySalesChannelId($salesChannelContext->getSalesChannelId());

                    if (!$productGroups[$key][0]) {
                        $productGroups[$key][0] = $assignedProduct->getAvailable()
                            && $assignedProduct->getChildCount() <= 0
                            && $assignedProduct->getCalculatedMaxPurchase() > 0;
                    }

                    $productGroups[$key][1][] = $this->calculatePrice($assignedProduct);
                }
            }
        }

        if (!$productGroups) {
            return [];
        }

        $groupedProductPrices = [];

        foreach ($productGroups as $key => $group) {
            $min = $group[1] ? \min($group[1]) : null;
            $max = null;

            if ($min !== null) {
                $max = \max($group[1]);

                if ($max <= $min) {
                    $max = null;
                }
            }

            $extension = new GroupedProductExtension($group[0], $min ?? 0, $max);

            $groupedProduct = $products->get($key);
            $groupedProduct->addExtension($extension::EXTENSION_NAME, $extension);
            $groupedProductPrices[$groupedProduct->getId()] = [
                $salesChannelContext->getSalesChannelId() => [
                    'inStock' => $extension->inStock(),
                    'min' => $extension->getMinimalPrice(),
                    'max' => $extension->getMaximalPrice(),
                ],
            ];
        }

        return $groupedProductPrices;
    }

    private function assignProductsToGroups(EntityCollection $groupedProducts, SalesChannelContext $salesChannelContext): void
    {
        if ($groupedProducts->count() <= 0) {
            return;
        }

        $productsIdsToLoad = [];
        foreach ($groupedProducts as $product) {
            foreach ($product->getExtension(ProductExtension::GROUPS_EXTENSION_NAME) as $group) {
                if (!$group->isActive()) {
                    continue;
                }
                $groupType = $group->getType();

                if ($groupType === ProductGroupedDefinition::TYPE_PRODUCT_LIST) {
                    $groupAssignedProducts = $group->getAssignedProducts();
                } elseif ($groupType === ProductGroupedDefinition::TYPE_PRODUCT_STREAM) {
                    $groupAssignedProducts = $this->getAssignedProductsByStreamId($group, $salesChannelContext);
                    $group->setAssignedProducts($groupAssignedProducts);
                }

                if (!$groupAssignedProducts || $groupAssignedProducts->count() <= 0) {
                    continue;
                }

                /** @var ProductGroupedAssignedProductsCollection $groupAssignedProducts */
                $productsIdsToLoad += $groupAssignedProducts->getProductIds();
            }
        }

        if (!$productsIdsToLoad) {
            return;
        }

        $criteria = new Criteria($productsIdsToLoad);
        $criteria->addAssociation('visibilities');
        $filter = new ProductAvailableFilter(
            $salesChannelContext->getSalesChannel()->getId(),
            ProductVisibilityDefinition::VISIBILITY_LINK
        );
        $criteria->addFilter($filter);

        $loadedProducts = $this->productRepository->search(
            $criteria,
            $salesChannelContext
        );

        foreach ($groupedProducts as $product) {
            foreach ($product->getExtension(ProductExtension::GROUPS_EXTENSION_NAME) as $group) {
                $groupAssignedProducts = $group->getAssignedProducts();
                foreach ($groupAssignedProducts as $groupAssignedProduct) {
                    if (!$loadedProducts || !$loadedProducts->has($groupAssignedProduct->getProductId())) {
                        continue;
                    }
                    $groupAssignedProduct->setProduct($loadedProducts->get($groupAssignedProduct->getProductId()));
                }
            }
        }
    }

    /**
     * @see @Storefront/storefront/component/product/card/price-unit.html.twig
     */
    private function calculatePrice(SalesChannelProductEntity $product): float
    {
        $calculatedPrices = $product->getCalculatedPrices();

        if ($calculatedPrices->count() > 0) {
            return $calculatedPrices->last()->getUnitPrice();
        }

        return $product->getCalculatedPrice()->getUnitPrice();
    }

    private function getAssignedProductsByStreamId(
        ProductGroupedEntity $group,
        SalesChannelContext $salesChannelContext
    ): EntitySearchResult {
        $groupIdCacheKey = "_{$group->getId()}";

        if (isset($this->groupedProductsStreamIdCache[$groupIdCacheKey])) {
            return $this->groupedProductsStreamIdCache[$groupIdCacheKey];
        }

        $this->groupedProductsStreamIdCache[$groupIdCacheKey] = $this->productRepository->search(
            (new Criteria())
                ->addFilter(
                    ...$this->productStreamBuilder->buildFilters(
                        $group->getProductStreamId(),
                        $salesChannelContext->getContext(),
                    )
                )
                ->addSorting($group->getSorting())
                ->setOffset(0)
                ->setLimit($group->getLimit()),
            $salesChannelContext,
        );

        return $this->groupedProductsStreamIdCache[$groupIdCacheKey];
    }
}
