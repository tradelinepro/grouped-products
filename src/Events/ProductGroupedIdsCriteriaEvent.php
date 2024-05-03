<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\Events;

use Shopware\Core\Framework\Log\Package;

#[Package('inventory')]
class ProductGroupedIdsCriteriaEvent extends ProductGroupedCriteriaEvent
{
}
