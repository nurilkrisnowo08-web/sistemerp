<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductionPlan extends Model
{
    protected $table = 'production_plans';
    protected $fillable = [
        'plan_date', 'line_code', 'customer_code', 'part_no', 
        'manpower', 'cap_per_hour', 
        's1_plan_reg', 's1_plan_ot', 's2_plan_reg', 's2_plan_ot', 
        'remark'
    ];
}