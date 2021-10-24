<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ChangeSponsoredBrandsStatTableIndex extends AbstractMigration
{
    public function change(): void
    {
        $environment = $this->input->getParameterOption('-e');
        if (in_array($environment, ["production", "development", "testing"])) {
            $this->table('sponsored_brands_ads_stat')->removeIndexByName('productKey')->save();
            $this->table('sponsored_brands_ads_stat')->addIndex(['asin','sku','date','campaignId'], ['unique' => true,'name' => 'productStatKey'])->save();
        }
    }
}
