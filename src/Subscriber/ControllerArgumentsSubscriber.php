<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\Subscriber;

use Shopware\Core\Checkout\Cart\Cart;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerArgumentsEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\SerializerInterface;
use Tradelinepro\GroupedProducts\Checkout\Cart\Error\NoLineItemsError;
use Tradelinepro\GroupedProducts\Config\ConfigService;
use Tradelinepro\GroupedProducts\Installer\CustomFieldInstaller;

#[Package('core')]
class ControllerArgumentsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly ConfigService $config,
        private readonly SerializerInterface $serializer
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER_ARGUMENTS => 'execute',
        ];
    }

    public function execute(ControllerArgumentsEvent $event): void
    {
        $request = $event->getRequest();
        if (!$request instanceof Request) {
            return;
        }
        if (!$request->attributes) {
            return;
        }

        $route = $request->attributes->get('_route');

        if ($route === 'frontend.checkout.line-item.add') {
            $this->checkoutLineItemAdd($event);
        } elseif ($route === 'api.action.sync') {
            $this->apiActionSync($event);
        }
    }

    private function checkoutLineItemAdd(ControllerArgumentsEvent $event): void
    {
        foreach ($event->getArguments() as $requestDataBag) {
            if ($requestDataBag instanceof RequestDataBag) {
                break;
            }

            unset($requestDataBag);
        }

        if (!isset($requestDataBag) || !$requestDataBag->has('grouped')) {
            return;
        }

        $lineItems = $requestDataBag->get('lineItems');
        if (!$lineItems) {
            return;
        }

        if ($lineItems instanceof RequestDataBag) {
            $lineItems = $lineItems->all();
        }

        foreach ($lineItems as $key => $lineItem) {
            if ($lineItem instanceof RequestDataBag) {
                $lineItem = $lineItem->all();
            }

            if (!isset($lineItem['grouped']) || !$lineItem['grouped']) {
                continue;
            }

            if (!isset($lineItem['quantity']) || $lineItem['quantity'] > 0) {
                continue;
            }

            unset($lineItems[$key], $key, $lineItem);
        }

        $requestDataBag->set('lineItems', new RequestDataBag($lineItems));

        if ($lineItems) {
            return;
        }

        foreach ($event->getArguments() as $cart) {
            if ($cart instanceof Cart) {
                $cart->addErrors(new NoLineItemsError());

                break;
            }
        }
    }

    private function apiActionSync(ControllerArgumentsEvent $event): void
    {
        foreach ($event->getArguments() as $request) {
            if ($request instanceof Request) {
                break;
            }

            unset($request);
        }

        if (!isset($request)) {
            return;
        }

        $operations = $request->getContent();
        if (!$operations) {
            return;
        }

        $operations = $this->serializer->decode($operations, 'json');
        if (!\is_array($operations) || !$operations) {
            return;
        }

        $customField = CustomFieldInstaller::CUSTOM_FIELD_IS_GROUPED;

        $taxId = $this->config->getTaxId();
        $modified = false;

        foreach ($operations as &$operation) {
            if (!\is_array($operation) || !$operation || !isset($operation['payload'])) {
                continue;
            }

            if (($operation['key'] ?? null) !== 'write') {
                continue;
            }
            if (($operation['action'] ?? null) !== 'upsert') {
                continue;
            }
            if (($operation['entity'] ?? null) !== 'product') {
                continue;
            }

            $payload = &$operation['payload'];
            if (!\is_array($payload) || !$payload) {
                continue;
            }

            foreach ($payload as &$entity) {
                if (!\is_array($entity) || !$entity) {
                    continue;
                }

                if (!($entity['customFields'][$customField] ?? false)) {
                    continue;
                }

                $entity['stock'] = 1;
                $entity['isCloseout'] = true;
                $entity['taxId'] = $taxId;

                if (isset($entity['price']) && \is_array($entity['price']) && $entity['price']) {
                    foreach ($entity['price'] as &$price) {
                        $price['net'] = 0;
                        $price['gross'] = 0;
                    }
                }

                $modified = true;
            }
        }

        if (!$modified) {
            return;
        }

        $setContent = function ($content): void {
            $this->content = $content;
        };

        \Closure::bind($setContent, $request, $request)($this->serializer->encode($operations, 'json'));
    }
}
