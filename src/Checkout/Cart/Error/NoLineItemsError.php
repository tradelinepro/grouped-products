<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\Checkout\Cart\Error;

use Shopware\Core\Checkout\Cart\Error\Error;
use Shopware\Core\Framework\Log\Package;

#[Package('core')]
class NoLineItemsError extends Error
{
    public function __construct()
    {
        parent::__construct('You need at least one product with quantity above zero.');
    }

    public function getParameters(): array
    {
        return [];
    }

    public function getId(): string
    {
        return $this->getMessageKey();
    }

    public function getMessageKey(): string
    {
        return 'no-line-items';
    }

    public function getLevel(): int
    {
        return self::LEVEL_NOTICE;
    }

    public function blockOrder(): bool
    {
        return false;
    }
}
