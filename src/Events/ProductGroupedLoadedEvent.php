<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\Events;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\ShopwareSalesChannelEvent;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;
use Tradelinepro\GroupedProducts\GroupedProducts\GroupedProductGroupItemCollection;

#[Package('inventory')]
class ProductGroupedLoadedEvent extends Event implements ShopwareSalesChannelEvent
{
    public function __construct(
        private readonly GroupedProductGroupItemCollection $result,
        private readonly SalesChannelContext $salesChannelContext
    ) {
    }

    public function getContext(): Context
    {
        return $this->salesChannelContext->getContext();
    }

    public function getResult(): GroupedProductGroupItemCollection
    {
        return $this->result;
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->salesChannelContext;
    }
}
