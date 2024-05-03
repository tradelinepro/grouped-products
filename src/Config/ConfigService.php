<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\Config;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SystemConfig\SystemConfigService;

#[Package('core')]
class ConfigService
{
    public const CART_POSITION_BOTTOM = 'bottom';
    public const CART_POSITION_SEPARATE = 'separate';
    public const CART_POSITION_BOTH = 'both';

    public function __construct(
        private readonly SystemConfigService $config,
        private readonly string $configPrefix
    ) {
    }

    public function getDefaultQty(?string $salesChannelId = null): int
    {
        $defaultQty = (int) $this->config->get("{$this->configPrefix}.config.defaultQty", $salesChannelId);

        if ($defaultQty < 1) {
            $defaultQty = 1;
        }

        return $defaultQty;
    }

    public function getTaxId(?string $salesChannelId = null): string
    {
        return (string) $this->config->get("{$this->configPrefix}.config.tax", $salesChannelId);
    }

    public function getCartPosition(?string $salesChannelId = null): ?string
    {
        return (string) $this->config->get("{$this->configPrefix}.config.cartPosition", $salesChannelId) ?? null;
    }

    public function showCartBottom(?string $salesChannelId = null): bool
    {
        return !$this->getCartPosition($salesChannelId) || $this->getCartPosition($salesChannelId) === self::CART_POSITION_BOTTOM || $this->showBothCart($salesChannelId);
    }

    public function getSeparateAddToCart(?string $salesChannelId = null): bool
    {
        return $this->getCartPosition($salesChannelId) === self::CART_POSITION_SEPARATE || $this->showBothCart($salesChannelId);
    }

    public function showBothCart(?string $salesChannelId = null): bool
    {
        return $this->getCartPosition($salesChannelId) === self::CART_POSITION_BOTH;
    }
}
