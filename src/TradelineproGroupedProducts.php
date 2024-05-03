<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts;

use Shopware\Core\Framework\Log\Package;
use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\ActivateContext;
use Tradelinepro\GroupedProducts\Installer\CustomFieldInstaller;
use Tradelinepro\GroupedProducts\Installer\TaxInstaller;

#[Package('core')]
class TradelineproGroupedProducts extends Plugin
{
    public function activate(ActivateContext $context): void
    {
        (new CustomFieldInstaller($this->container))->activate($context);
        (new TaxInstaller($this->container))->activate($context);
    }

    public function executeComposerCommands(): bool
    {
        return true;
    }
}
