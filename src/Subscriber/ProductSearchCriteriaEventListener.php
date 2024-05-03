<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\Subscriber;

use Shopware\Core\Content\Product\Events\ProductSearchCriteriaEvent;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tradelinepro\GroupedProducts\Extension\GroupedProductSearchExtension;

#[Package('core')]
class ProductSearchCriteriaEventListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ProductSearchCriteriaEvent::class => 'execute',
        ];
    }

    public function execute(ProductSearchCriteriaEvent $event): void
    {
        $event->getSalesChannelContext()->addExtension(
            GroupedProductSearchExtension::EXTENSION_NAME,
            new GroupedProductSearchExtension()
        );
    }
}
