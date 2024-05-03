<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\Subscriber;

use Shopware\Core\Content\Product\Events\ProductSuggestCriteriaEvent;
use Shopware\Core\Framework\Log\Package;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tradelinepro\GroupedProducts\Extension\GroupedProductSearchExtension;

#[Package('core')]
class ProductSuggestCriteriaEventListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ProductSuggestCriteriaEvent::class => 'execute',
        ];
    }

    public function execute(ProductSuggestCriteriaEvent $event): void
    {
        $event->getSalesChannelContext()->addExtension(
            GroupedProductSearchExtension::EXTENSION_NAME,
            new GroupedProductSearchExtension()
        );
    }
}
