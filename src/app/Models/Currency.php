<?php

/**
 * Currency Model
 *
 * @package     Gofer
 * @subpackage  Model
 * @category    Currency
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
class Currency extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'currency';

    protected $appends = ['original_symbol'];

    public $timestamps =  false;

    /**
     * Scope to get Active records Only
     *
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    /**
     * Scope to get Default Currency
     *
     */
    public function scopeDefaultCurrency($query)
    {
        return $query->where('default_currency', '1');
    }

    /**
     * Scope to get Paypal Currency
     *
     */
    public function scopePaypal($query)
    {
        return $query->where('paypal_currency', 'Yes');
    }

    /**
     * Scope to get Currency code dropdown
     *
     */
    public function scopeCodeSelect($query)
    {
        return $query->active()->pluck('code','code');
    }

    /**
     * Scope to get paypal currency dropdown
     *
     */
    public function scopeCodeSelectPaypal($query)
    {
        return $query->active()->paypal()->pluck('code','code');
    }

    /**
     * Static function to get original symbol
     *
     */
    public static function original_symbol($code)
    {
    	$symbol = DB::table('currency')->where('code', $code)->first()->symbol;
    	return html_entity_decode($symbol);
    }

   // Get currenct record symbol
    public function getOriginalSymbolAttribute()
    {
        $symbol = $this->attributes['symbol'];
        return html_entity_decode($symbol);
    }

    // Get currenct record symbol
    public function getSymbolAttribute()
    {
        return html_entity_decode($this->attributes['symbol']);
    }

    /**
     * Set symbol Attribute
     *
     */
    public function setSymbolAttribute($value)
    {
       $this->attributes['symbol'] =  htmlentities($value);
    }
}
