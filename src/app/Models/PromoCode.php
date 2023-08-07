<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use CurrencyConversion;

    protected $table = 'promo_code';

    protected $appends = ['expire_date_dmy','expire_date_mdy','original_amount'];

    protected $convert_fields = ['amount', 'original_amount'];
    public $disable_admin_panel_convertion = true;

    /**
     * Get Expire Date in Dmy Format
     *  
     */
    public function getExpireDateDmyAttribute()
    {
    	return date('d-m-Y',strtotime($this->attributes['expire_date']));
    }

    /**
     * Get Expire Date in Mdy Format
     *  
     */
    public function getExpireDateMdyAttribute()
    {
        return date('m/d/Y',strtotime($this->attributes['expire_date']));
    }

    /**
     * Get Amount Attribute
     *  
     */
    public function getAmountAttribute()
    {
    	return number_format(($this->attributes['amount']),2 ,'.', '');
    }

    /**
     * Get Original Amount Attribute
     *  
     */
    public function getOriginalAmountAttribute()
    {
        return $this->attributes['amount'];
    }
}
