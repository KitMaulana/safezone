<?php

namespace App\Events;

use App\Models\AttendanceLog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VehicleScanned
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public AttendanceLog $log) {}
}
