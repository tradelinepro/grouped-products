<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('core')]
class ProductGroupedCollection extends EntityCollection
{
    public function getExpectedClass(): string
    {
        return ProductGroupedEntity::class;
    }

    public function getApiAlias(): string
    {
        return 'product_grouped_collection';
    }

    public function sortByPosition(): void
    {
        $this->sort(fn (ProductGroupedEntity $a, ProductGroupedEntity $b) => $a->getPosition() <=> $b->getPosition());
    }
}
