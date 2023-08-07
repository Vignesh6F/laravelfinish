<?php

/**
 * Pages Model
 *
 * @package     Gofer
 * @subpackage  Model
 * @category    Pages
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Session;
use Request;
use App\Models\PagesTranslations;

class Pages extends Model
{

    use Translatable;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'pages';

    public $translatedAttributes = ['name', 'description'];

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if(Request::segment(1) == 'admin') {
            $this->defaultLocale = 'en';
        }
        else {
            $this->defaultLocale = Session::get('language');
        }
    }


    // question_lang
    public function getNameLangAttribute()
    {
      $lan = Session::get('language');
      if($lan=='en')
        return $this->attributes['name'];
      else{ 
         $get = PagesTranslations::where('pages_id',$this->attributes['id'])->where('locale',$lan)->first();
         if($get)
          return $get->name;
        else
          return $this->attributes['name'];
      }
    }
    
// answer_lang
    public function getDescriptionLangAttribute()
    {
      $lan = Session::get('language');
      if($lan=='en')
        return $this->attributes['content'];
      else{ 
         $get = PagesTranslations::where('pages_id',$this->attributes['id'])->where('locale',$lan)->first();
         if($get)
          return $get->description;
        else
          return $this->attributes['content'];
      }
    }


    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}