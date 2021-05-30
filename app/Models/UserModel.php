<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{
    protected $table = 'users';

    protected $fillable = [
        'id',
        'last_name'
        // `name`,
        // `middle_name`,
        // `online`,
        // `business_type`,
        // `mentor_id`,
        // `position`,
        // `amount`,
        // `debt`,
        // `registr_date`,
        // `qr`,
        // `image`
    ];
}
