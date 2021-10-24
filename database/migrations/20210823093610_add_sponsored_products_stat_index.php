<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSponsoredProductsStatIndex extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["production", "development", "testing"])) {
            $this->execute("ALTER TABLE `sponsored_products_ads_stat` DROP INDEX `adIdByDate`");
            $table = $this->table('sponsored_products_ads_stat');
            $table->addIndex(['adId','campaignId','date','asin'], ['unique' => true,'name' => 'adIdByDate'])->save();
        }
    }
}
