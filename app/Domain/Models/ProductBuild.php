<?php

namespace App\Domain\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductBuild extends AppModel
{

    protected $fillable = [
        "remote_job_id",
        "preparing_build_stage",
        "building_application_stage" ,
        "performing_tests_stage",
        "analyzing_results_stage",
        "publishing_application_stage"
    ];
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i:s',
        'updated_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * Get the remoteJobType that owns the RemoteJob
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function remoteJob(): BelongsTo
    {
        return $this->belongsTo(RemoteJob::class);
    }

}

