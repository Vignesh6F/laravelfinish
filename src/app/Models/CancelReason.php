<?php

/**
 * Cancel Reson Model
 *
 * @package     Gofer
 * @subpackage  Model
 * @category    CancelReason
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Session;
class CancelReason extends Model
{    
    use Translatable;
    
    public $timestamps = false;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if(\Request::segment(1) == 'admin') {
            $this->defaultLocale = 'en';
        }
        else {
            $this->defaultLocale = Session::get('language');
        }
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['reason','status','cancelled_by'];

    /**
     * The attributes that are translates.
     *
     * @var array
     */
    public $translatedAttributes = ['reason'];


    /**
     * Scope to get Active Records Only
     *
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }


    public function translate()
    {
        return $this->hasmany('App\Models\CancelReasonTranslations','reason_id','id');
    }
    
}
