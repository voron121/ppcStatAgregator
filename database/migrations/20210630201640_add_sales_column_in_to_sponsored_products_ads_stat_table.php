<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSalesColumnInToSponsoredProductsAdsStatTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["production", "development", "testing"])) {
            $table = $this->table('sponsored_products_ads_stat');
            $table->addColumn(
                'sale',
                'biginteger',
                [
                    'limit' => 20,
                    'null' => true,
                    "comment" => "Сумма продаж товара за день умноженная на 1000000",
                    'after' => 'spend'
                ]
            )->save();
        }
    }
}
