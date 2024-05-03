<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\GroupedProducts;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Struct\Collection;

#[Package('core')]
class GroupedProductGroupItemCollection extends Collection
{
    final public const EXTENSION_NAME = 'groupedProductGroupItems';

    public function getExpectedClass(): string
    {
        return GroupedProductGroupItem::class;
    }

    public function getApiAlias(): string
    {
        return 'grouped_product_group_items';
    }
}
