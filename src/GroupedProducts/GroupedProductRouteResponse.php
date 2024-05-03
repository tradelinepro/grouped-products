<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\GroupedProducts;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\StoreApiResponse;

#[Package('inventory')]
class GroupedProductRouteResponse extends StoreApiResponse
{
    /**
     * @var GroupedProductGroupItemCollection
     */
    protected $object;

    public function __construct(GroupedProductGroupItemCollection $object)
    {
        parent::__construct($object);
    }

    public function getResult(): GroupedProductGroupItemCollection
    {
        return $this->object;
    }
}
