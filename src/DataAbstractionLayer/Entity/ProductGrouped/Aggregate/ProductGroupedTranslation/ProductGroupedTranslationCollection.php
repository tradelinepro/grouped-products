<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\Aggregate\ProductGroupedTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\Log\Package;

#[Package('core')]
class ProductGroupedTranslationCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'product_grouped_assigned_products_translation_collection';
    }

    protected function getExpectedClass(): string
    {
        return ProductGroupedTranslationEntity::class;
    }
}
