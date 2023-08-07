<?php

/**
 * Documents Translation Model
 *
 * @package     Gofer
 * @subpackage  Controller
 * @category    Documents Translation
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model; 

class DocumentsTranslations extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'documents_langs';

    public $timestamps = false;

    protected $fillable = ['name'];

    public function language()
    {
        return $this->belongsTo('App\Models\Language','locale','value');
    }
}
