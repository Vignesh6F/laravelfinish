<?php

namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Language;
use Illuminate\Http\Request;
use App;
use Lang;

class LocaleFileController extends Controller
{
   	private $lang = '';
    private $file;
    private $path;
    private $arrayLang = array();

    //------------------------------------------------------------------------------
	// Read lang file content
	//------------------------------------------------------------------------------

    private function read() 
    {
        if ($this->lang == '') $this->lang = App::getLocale();
        $this->arrayLang = Lang::get(str_replace('.php','',$this->file),[],$this->lang);
        if (gettype($this->arrayLang) == 'string') $this->arrayLang = array();

        //if some data not in array it's store in other array 
        $other = $this->arrayLang; 
        foreach ($other as $key => $value) { 
            if (is_array($value))
                unset($other[$key]);
            else
                unset($this->arrayLang[$key]);
        } 
        if(count($other))
        $this->arrayLang['other'] = $other;
    }

    //------------------------------------------------------------------------------
	// Save lang file content
	//------------------------------------------------------------------------------

    private function save() 
    {
        $path = base_path().'/resources/lang/'.$this->lang.'/'.$this->file;
        $content = "<?php\n\nreturn\n[\n";

        foreach ($this->arrayLang as $key => $value) 
        {
            if(is_array($value))
            {
            	//save other array ti individual 
                if($key!='other')
                    $content .= "\t'".$key."'=>[\n"; 
                foreach ($value as $sub_key => $sub_value) {
                    $content .= "\t'".$sub_key."' => '".str_replace("'", "\'", $sub_value)."',\n";
                }
                if($key!='other')
                    $content .= "],\n";
            }else{

                $content .= "\t'".$key."' => '".str_replace("'", "\'", $value)."',\n";
            }
        }
        $content .= "];";

        file_put_contents($path, $content);
    }



    public function get_locale(Request $request) 
    {
		// Process and prepare you data as you like.
        $this->lang = $request->lang ?? 'en';
        $this->file = 'messages.php';
		// END - Process and prepare your data
        $this->read();
        $language = $this->arrayLang;
        $select_lang = $this->lang;
        $all_lanuage = Language::active()->pluck('name','value');

        return view('admin.language.change_lanuage',compact('language','all_lanuage','select_lang'));
    }

  

    public function update_locale(Request $request) 
    {
        // Process and prepare you data as you like.
        $this->lang = $request->lanuage ?? 'en';
        $this->file = 'messages.php';
        $this->arrayLang = $request->data;
        // END - Process and prepare your data
        $this->save();
        $helper = new App\Http\Start\Helpers;
        $helper->flash_message('success', 'Language update Successfully');
        return redirect()->route('language.locale',['lang'=>$this->lang]);
    }
}
