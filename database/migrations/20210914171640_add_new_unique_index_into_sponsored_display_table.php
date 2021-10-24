<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddNewUniqueIndexIntoSponsoredDisplayTable extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["production", "development", "testing"])) {
            $table = $this->table('sponsored_display_ads_stat');
            $table->addIndex(['date', 'asin', 'sku', 'campaignName', 'adGroupName'], ['unique' => true,'name' => 'productByDate'])->save();
        }
    }
}
