<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RemoteJob extends AppModel
{
    use HasFactory;
    protected $table = 'remote_jobs';
    protected $fillable = ["callback_msg", "callback_status", "callback_log_uri"];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * Get the remoteJobType that owns the RemoteJob
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function remoteJobType(): BelongsTo
    {
        return $this->belongsTo(RemoteJobType::class, 'remote_job_type_id');
    }

    /**
     * Get the user that owns the RemoteJob
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the product that owns the RemoteJob
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'reference_id');
    }

    /**
     * Get all of the productBuild for the RemoteJob
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function productBuild(): HasOne
    {
        return $this->hasOne(ProductBuild::class);
    }

    public function installation(): BelongsTo
    {
        return $this->belongsTo(Installation::class, 'reference_id');
    }

}

