<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\GroupedProducts;

use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Log\Package;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\Request;
use Tradelinepro\GroupedProducts\DataAbstractionLayer\Entity\ProductGrouped\ProductGroupedCollection;

/**
 * This route will be used to load all assigned products of the provided grouped product id
 */
#[Package('inventory')]
abstract class AbstractGroupedProductRoute
{
    abstract public function getDecorated(): AbstractGroupedProductRoute;

    abstract public function load(string $productId, Request $request, SalesChannelContext $context, Criteria $criteria, ?ProductGroupedCollection $productGroups): GroupedProductRouteResponse;
}
