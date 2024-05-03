<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\Subscriber;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Routing\Event\SalesChannelContextResolvedEvent;
use Shopware\Core\Framework\Struct\ArrayStruct;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Tradelinepro\GroupedProducts\Config\ConfigService;

#[Package('core')]
class SalesChannelEventListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly ConfigService $config
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SalesChannelContextResolvedEvent::class => 'execute',
        ];
    }

    public function execute(SalesChannelContextResolvedEvent $event): void
    {
        $event->getSalesChannelContext()->addExtension('groupedCartPosition', new ArrayStruct([
            'showBottom' => $this->config->showCartBottom(),
            'showSeparated' => $this->config->getSeparateAddToCart(),
        ]));
    }
}
