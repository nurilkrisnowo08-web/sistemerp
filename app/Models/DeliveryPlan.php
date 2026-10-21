<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryPlan extends Model
{
    use HasFactory;

    protected $table = 'delivery_plans';

    protected $fillable = [
        'plan_date', 'part_no', 'customer_code',
        'pulling_time', 'delv_time',
        'blnc_delv', 'plan_delv', 'total_delv',
        'act_delv_s1', 'act_delv_s2',
        'judge', 'keterangan', 'status'
    ];
}