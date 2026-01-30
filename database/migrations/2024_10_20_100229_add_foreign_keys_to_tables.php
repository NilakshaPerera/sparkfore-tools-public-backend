<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->foreign('reseller_id')->references('id')->on('resellers');
            $table->foreign('parent_customer_id')->references('id')->on('customers');
        });

        Schema::table('plugin_available_customers', function (Blueprint $table) {
            $table->foreign('plugin_id')->references('id')->on('plugins');
            $table->foreign('customer_id')->references('id')->on('customers');
        });

        Schema::table('customer_available_products', function (Blueprint $table) {
            $table->foreign('reseller_id')->references('id')->on('resellers');
            $table->foreign('customer_id')->references('id')->on('customers');
        });

        Schema::table('product_available_customers', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('customer_id')->references('id')->on('customers');
        });

        Schema::table('product_has_plugins', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('plugin_id')->references('id')->on('plugins');
        });

        Schema::table('product_has_external_plugins', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('plugin_id')->references('id')->on('external_plugins');
        });

        Schema::table('product_has_plugin_versions', function (Blueprint $table) {
            $table->foreign('product_has_plugin_id')->references('id')->on('product_has_plugins');
        });

        Schema::table('installations', function (Blueprint $table) {
            $table->foreign('customer_product_id')->references('id')->on('customer_products');
            $table->foreign('hosting_id')->references('id')->on('hostings');
        });

        Schema::table('hosting_on_prem_settings', function (Blueprint $table) {
            $table->foreign('hosting_id')->references('id')->on('hostings');
        });

        Schema::table('hosting_cloud_settings', function (Blueprint $table) {
            $table->foreign('hosting_id')->references('id')->on('hostings');
            $table->foreign('base_package_id')->references('id')->on('base_packages');
            $table->foreign('hosting_provider_id')->references('id')->on('hosting_providers');
        });

        Schema::table('customer_products', function (Blueprint $table) {
            // Foreign key constraints
            $table->foreign('customer_id')->references('id')->on('customers');
            $table->foreign('product_id')->references('id')->on('products');
        });

        Schema::table('products', function (Blueprint $table) {
            // Foreign key constraints
            $table->foreign('pipeline_maintainer_id')->references('id')->on('pipeline_maintainers');

        });
        Schema::table('product_has_software', function (Blueprint $table) {
            // Foreign key constraints
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('software_id')->references('id')->on('softwares');
        });


        Schema::table('plugins', function (Blueprint $table) {
            // Foreign key constraint
            $table->foreign('git_version_type_id')->references('id')->on('git_version_types');
        });

        Schema::table('plugin_versions', function (Blueprint $table) {
            $table->foreign('plugin_id')->references('id')->on('plugins');
            $table->foreign('version_type')->references('id')->on('git_version_types');
        });

        Schema::table('plugin_supports_softwares', function (Blueprint $table) {
            // Foreign key constraints
            $table->foreign('software_id')->references('id')->on('softwares');
            $table->foreign('plugin_id')->references('id')->on('plugins');
        });

        Schema::table('hostings', function (Blueprint $table) {
            // Foreign key constraint
            $table->foreign('hosting_provider_id')->references('id')->on('hosting_providers');
            $table->foreign('hosting_type_id')->references('id')->on('hosting_types');
        });

        Schema::table('softwares', function (Blueprint $table) {
            // Foreign key constraint
            $table->foreign('git_version_type_id')->references('id')->on('git_version_types');
        });

        Schema::table('product_build_logs', function (Blueprint $table) {
            // Foreign key constraints
            $table->foreign('product_id')->references('id')->on('products');
            $table->foreign('software_id')->references('id')->on('softwares');
        });

        Schema::table('hosting_available_customers', function (Blueprint $table) {
            // Foreign key constraints
            $table->foreign('hosting_id')->references('id')->on('hostings');
            $table->foreign('customer_id')->references('id')->on('customers');
        });

        Schema::table('users', function (Blueprint $table) {
            // Foreign key constraints
            $table->foreign('role_id')->references('id')->on('roles');
            $table->foreign('account_type_id')->references('id')->on('account_types');
            $table->foreign('customer_id')->references('id')->on('customers')->nullable();
        });


        // Add foreign keys for other tables here
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['reseller_id']);
            $table->dropForeign(['parent_customer_id']);
        });

        Schema::table('resellers', function (Blueprint $table) {
            $table->dropForeign(['customer_id']);
        });

        Schema::table('plugin_available_customers', function (Blueprint $table) {
            $table->dropForeign(['plugin_id']);
            $table->dropForeign(['customer_id']);
        });

        Schema::table('customer_available_products', function (Blueprint $table) {
            $table->dropForeign(['reseller_id']);
            $table->dropForeign(['customer_id']);
        });

        Schema::table('product_available_customers', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['customer_id']);
        });

        Schema::table('product_has_plugins', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['plugin_id']);
        });

        Schema::table('product_has_external_plugins', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['plugin_id']);
        });

        
        Schema::table('plugin_versions', function (Blueprint $table) {
            $table->dropForeign(['plugin_id']);
            $table->dropForeign(['version_type']);
        });

        Schema::table('product_has_plugin_versions', function (Blueprint $table) {
            $table->dropForeign(['product_has_plugin_id']);
        });

        Schema::table('product_available_customers', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['software_id']);
        });

        Schema::table('installations', function (Blueprint $table) {
            $table->dropForeign(['customer_product_id']);
            $table->dropForeign(['hosting_id']);
        });

        Schema::table('hosting_on_prem_settings', function (Blueprint $table) {
            $table->dropForeign(['hosting_id']);
        });

        Schema::table('hosting_cloud_settings', function (Blueprint $table) {
            $table->dropForeign(['hosting_id']);
            $table->dropForeign(['base_package_id']);
            $table->dropForeign(['hosting_provider_id']);
        });

        // Drop foreign keys for other tables here
    }
};
