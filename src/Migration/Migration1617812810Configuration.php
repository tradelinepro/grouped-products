<?php declare(strict_types=1);
/*
 * @author digital.manufaktur GmbH
 * @link   https://www.digitalmanufaktur.com/
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tradelinepro\GroupedProducts\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Log\Package;
use Tradelinepro\Core\Migration\Migration\AbstractMigrationStep;

#[Package('core')]
class Migration1617812810Configuration extends AbstractMigrationStep
{
    public function update(Connection $connection): void
    {
        $sql = <<< 'SQL'
SELECT COUNT(*) FROM `system_config` WHERE `configuration_key` = 'TradelineproGroupedProducts.config.tax' LIMIT 1
SQL;

        if (!$connection->fetchOne($sql)) {
            $sql = <<< 'SQL'
SELECT LOWER(HEX(`id`)) FROM `tax` ORDER BY `tax_rate` ASC LIMIT 1
SQL;

            if ($taxId = $connection->fetchOne($sql)) {
                $sql = <<< SQL
INSERT IGNORE INTO `system_config` (`id`, `configuration_key`, `configuration_value`, `created_at`)
    VALUES (0x149ecc2af2607950465ea3e479774e0a, 'TradelineproGroupedProducts.config.tax', '{"_value":"$taxId"}', NOW());
SQL;

                $connection->executeStatement($sql);
            }
        }
    }
}
