<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $guarded =[];

    //status
    const CANCELED = 0;
    const COMPLETED = 1;
    const PENDING = 2;
    const ON_PROCESSING = 3;
    const DELIVERED = 4;
    const RETURNED = 5;

    //payment method arr
    const PAYMENT_METHOD_ARR = ['cod'];

    public function orderStatus()
    {
        if ($this->status == self::CANCELED) {
            return 'Canceled';
        }elseif ($this->status == self::COMPLETED) {
            return 'Completed';
        }elseif ($this->status == self::PENDING) {
            return 'Pending';
        }elseif ($this->status == self::ON_PROCESSING) {
            return 'On Processing';
        }elseif ($this->status == self::DELIVERED) {
            return 'Delivered';
        }elseif ($this->status == self::RETURNED) {
            return 'Returned';
        }
    }

    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'order_id');
    }
}
