<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Product extends AppModel
{
    use HasFactory;

    protected $fillable = ["git_deleted_at", "pipeline_deleted_at", "last_build", "plugin_changes", "legacy_product_name"];

    /**
     * Get all of the installations for the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasManyThrough
     */
    public function installations(): HasManyThrough
    {
        return $this->hasManyThrough(Installation::class, CustomerProduct::class);
    }

    /**
     * Get all of the customerProducts for the Product
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function customerProducts(): HasMany
    {
        return $this->hasMany(CustomerProduct::class);
    }

    public function productPlugins()
    {
        return $this->hasMany(ProductHasPlugin::class);
    }

    public function productSoftwares()
    {
        return $this->hasMany(ProductHasSoftware::class);
    }

    public function maintainer()
    {
        return $this->belongsTo(PipelineMaintainer::class, 'pipeline_maintainer_id');
    }

    public function productCustomer()
    {
        return $this->hasOne(ProductAvailableCustomer::class);
    }


    private function getScheduleParams($cronExpression, $env)
    {
        $parts = explode(' ', $cronExpression);
        if (count($parts) == 5) {
            return [$parts[1], $parts[2], $parts[3]];
        } else {
            return config("sparkfore.package_build.{$env}_cron");
        }
    }

    public function getDevelopmentScheduleHourAttribute()
    {
        return $this->getScheduleParams($this->development_scheduled_build, "development")[0];
    }

    public function getDevelopmentScheduleDayAttribute()
    {
        return $this->getScheduleParams($this->development_scheduled_build, "development")[1];
    }

    public function getDevelopmentScheduleMonthAttribute()
    {
        return $this->getScheduleParams($this->development_scheduled_build, "development")[2];
    }

    public function getStagingScheduleHourAttribute()
    {
        return $this->getScheduleParams($this->staging_scheduled_build, "staging")[0];
    }

    public function getStagingScheduleDayAttribute()
    {
        return $this->getScheduleParams($this->staging_scheduled_build, "staging")[1];
    }

    public function getStagingScheduleMonthAttribute()
    {
        return $this->getScheduleParams($this->staging_scheduled_build, "staging")[2];
    }

    public function getProductionScheduleHourAttribute()
    {
        return $this->getScheduleParams($this->development_scheduled_build, "production")[0];
    }

    public function getProductionScheduleDayAttribute()
    {
        return $this->getScheduleParams($this->development_scheduled_build, "production")[1];
    }

    public function getProductionScheduleMonthAttribute()
    {
        return $this->getScheduleParams($this->development_scheduled_build, "production")[2];
    }
}
