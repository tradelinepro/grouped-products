<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\Extension;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Struct\Struct;

#[Package('core')]
class GroupedProductExtension extends Struct
{
    final public const EXTENSION_NAME = 'groupedProduct';

    public function __construct(protected bool $inStock, protected float $minimalPrice, protected ?float $maximalPrice = null)
    {
    }

    public function inStock(): bool
    {
        return $this->inStock;
    }

    public function getMinimalPrice(): float
    {
        return $this->minimalPrice;
    }

    public function getMaximalPrice(): ?float
    {
        return $this->maximalPrice;
    }
}
