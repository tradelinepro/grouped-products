<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\Subscriber;

use Shopware\Core\Content\Product\ProductEvents;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductCollection;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelEntityLoadedEvent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Page\Product\ProductPageCriteriaEvent;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\Product\ProductExtension;
use Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\ProductGroupedCollection;
use Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\ProductGroupedEntity;
use Tradelinepro\GroupedProducts\Extension\GroupedProductExtension;
use Tradelinepro\GroupedProducts\GroupedProducts\AbstractGroupedProductRoute;
use Tradelinepro\GroupedProducts\GroupedProducts\GroupedProductRoute;
use Tradelinepro\GroupedProducts\Installer\CustomFieldInstaller;

#[Package('core')]
class ProductLoadedEventListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly AbstractGroupedProductRoute $groupedProductsLoader
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'sales_channel.' . ProductEvents::PRODUCT_LOADED_EVENT => 'loaded',
            ProductPageCriteriaEvent::class => 'productPageCriteria',
            ProductPageLoadedEvent::class => 'productPageLoaded',
        ];
    }

    public function loaded(SalesChannelEntityLoadedEvent $event): void
    {
        if ($event->getSalesChannelContext()->hasExtension(GroupedProductRoute::RUNNING_GROUPED_LOADING_BLOCKER)) {
            return;
        }

        /** @var SalesChannelProductCollection $products */
        $products = $event->getEntities();

        if (\count($products) <= 0) {
            return;
        }

        foreach ($products as $product) {
            $this->addPriceExtension($product, $event->getSalesChannelContext());
            $this->updateProductAssignedExtension($product);
        }
    }

    public function productPageCriteria(ProductPageCriteriaEvent $event): void
    {
        $event->getCriteria()->addAssociation('groups.assignedProducts');
    }

    public function productPageLoaded(ProductPageLoadedEvent $event): void
    {
        $product = $event->getPage()->getProduct();
        if (!($product->getCustomFields()[CustomFieldInstaller::CUSTOM_FIELD_IS_GROUPED] ?? false)) {
            return;
        }

        if ($product->getExtension(ProductExtension::EXTENSION_NAME)) {
            return;
        }

        $groups = $product->getExtension(ProductExtension::GROUPS_EXTENSION_NAME);

        $productGroups = $this->groupedProductsLoader->load(
            $product->getId(),
            new Request(),
            $event->getSalesChannelContext(),
            new Criteria(),
            $groups
        );
        $product->addExtension(ProductExtension::EXTENSION_NAME, $productGroups->getResult());
    }

    private function addPriceExtension(SalesChannelProductEntity $product, SalesChannelContext $salesChannelContext): void
    {
        if ($product->getCustomFields()[CustomFieldInstaller::CUSTOM_FIELD_GROUPED_PRICE] ?? false) {
            $customFieldsPrice = $product->getCustomFields()[CustomFieldInstaller::CUSTOM_FIELD_GROUPED_PRICE];
            if (!isset($customFieldsPrice[$salesChannelContext->getSalesChannelId()])) {
                return;
            }
            $groupedPriceInfo = $customFieldsPrice[$salesChannelContext->getSalesChannelId()];
            $extension = new GroupedProductExtension(
                $groupedPriceInfo['inStock'] ?? null,
                $groupedPriceInfo['min'] ?? null,
                $groupedPriceInfo['max'] ?? null
            );
            $product->addExtension(ProductExtension::PRICE_EXTENSION_NAME, $extension);
            $customFields = $product->getCustomFields() ?? [];

            if (isset($customFields[CustomFieldInstaller::CUSTOM_FIELD_GROUPED_PRICE])) {
                unset($customFields[CustomFieldInstaller::CUSTOM_FIELD_GROUPED_PRICE]);
                $product->setCustomFields($customFields);
            }

            $translated = $product->getTranslated() ?? [];
            $customFields = $translated['customFields'] ?? [];
            if (isset($customFields[CustomFieldInstaller::CUSTOM_FIELD_GROUPED_PRICE])) {
                unset($customFields[CustomFieldInstaller::CUSTOM_FIELD_GROUPED_PRICE]);
                $translated['customFields'] = $customFields;
                $product->setTranslated($translated);
            }
        }
    }

    private function updateProductAssignedExtension(SalesChannelProductEntity $product): void
    {
        /** @var ProductGroupedCollection $groups */
        $groups = $product->getExtension(ProductExtension::GROUPS_EXTENSION_NAME);

        if (!$groups) {
            return;
        }

        $groups->filterAndReduceByProperty('active', false);
        $groups->sortByPosition();

        foreach ($groups as $group) {
            /** @var ProductGroupedEntity $group */
            if (!$group->isActive() || !$group->getAssignedProducts()) {
                continue;
            }
            $group->getAssignedProducts()->sortByPosition();
        }
    }
}
