<?php

namespace App\Domain\Jobs;

use App\Domain\Models\CustomerProduct;
use App\Domain\Models\Installation;
use App\Domain\Models\Product;
use App\Domain\Models\ProductAvailableCustomer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Log;

class SetInstallationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $installationSeedData;
    protected $installationDbData;
    /**
     * Create a new job instance.
     */
    public function __construct($installationSeedData, $installationDbData)
    {
        $this->installationSeedData = (object) $installationSeedData;
        $this->installationDbData = $installationDbData;
    }

    public $tries = 2;

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $productId = null;

        if (isset($this->installationSeedData->product_id)) {
            $productId = $this->installationSeedData->product_id;
        } elseif (isset($this->installationSeedData->pipeline_name)) {
            $product = Product::where("pipeline_name", $this->installationSeedData->pipeline_name)->first();
            $productId = $product->id;
        }

        $customerProductId = null;
        CustomerProduct::updateOrCreate(
            [
                'customer_id' => $this->installationSeedData->customer_id,
                'product_id' => $productId,
            ],
            [
                'label' => $product->name ?? 'N/A',
                'base_price_increase_yearly' => 0,
                'base_price_per_user_increase_yearly' => 0,
                'include_maintenance' => 0
            ]
        );

        ProductAvailableCustomer::updateOrCreate(
            [
                'customer_id' => $this->installationSeedData->customer_id,
                'product_id' => $productId,
            ],
            [
                'customer_id' => $this->installationSeedData->customer_id,
                'product_id' => $productId,
            ]
        );

        $customerProduct = DB::table('customer_products')
            ->where('customer_id', '=', $this->installationSeedData->customer_id)
            ->where('product_id', '=', $productId) // Actually this is product id
            ->first();

        if($customerProduct) {
            $customerProductId = $customerProduct->id;
        }

        $customerProductId = $customerProductId ?? $customerProduct->id;
        $this->installationDbData['customer_product_id'] = $customerProductId;

        Installation::updateOrCreate(
            [
                'url' => $this->installationDbData["url"],
            ],
            $this->installationDbData
        );

        DB::statement(
            "SELECT SETVAL(pg_get_serial_sequence('installations', 'id'), (SELECT MAX(id) FROM installations));"
        );
    }
}
