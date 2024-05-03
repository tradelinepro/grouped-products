<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\Aggregate\ProductGroupedAssignedProducts;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('core')]
class ProductGroupedAssignedProductsCollection extends EntityCollection
{
    public function getExpectedClass(): string
    {
        return ProductGroupedAssignedProductsEntity::class;
    }

    public function getApiAlias(): string
    {
        return 'product_grouped_assigned_products_collection';
    }

    public function getGroupedIds(): array
    {
        return $this->fmap(fn (ProductGroupedAssignedProductsEntity $entity) => $entity->getGroupedId());
    }

    public function getProductIds(): array
    {
        return $this->fmap(fn (ProductGroupedAssignedProductsEntity $entity) => $entity->getProductId());
    }

    public function sortByPosition(): void
    {
        $this->sort(fn (ProductGroupedAssignedProductsEntity $a, ProductGroupedAssignedProductsEntity $b) => $a->getPosition() <=> $b->getPosition());
    }
}
