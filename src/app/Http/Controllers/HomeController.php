<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Pages;
use App\Models\Help;
use App\Models\HelpSubCategory;
use App\Models\HelpTranslations;
use App\Models\Currency;
use App\Models\JoinUs;
use Auth;
use App;
use Route;
use DB;
use Session;

class HomeController extends Controller
{
    /** 
    * Display homepage
    **/
	public function index()
    {
    	return view('home.home');
    }
  
    /**
     * View Static Pages
     *
     * @param array $request  Input values
     * @return Static page view file
     */
    public function static_pages(Request $request)
    {
        if($request->token != '') {
            session(['get_token' => $request->token]);
        }

        $pages = Pages::where(['url'=>$request->name, 'status'=>'Active'])->firstOrFail();
        if(Session::get('language') == 'en')
        {
            $data['content'] = str_replace(['SITE_NAME', 'SITE_URL'], [SITE_NAME, url('/')], $pages->content);
        }else
        {
            $data['content'] = str_replace(['SITE_NAME', 'SITE_URL'], [SITE_NAME, url('/')], $pages->description_lang); 
        }
        $data['title'] = $pages->name;

        return view('home.static_pages', $data);
    }

    /**
     * Set session for Currency & Language while choosing footer dropdowns
     *
     */
    public function set_session(Request $request)
    {
        if($request->currency) {
            Session::put('currency', $request->currency);
            $symbol = Currency::original_symbol($request->currency);
            Session::put('symbol', $symbol);
        }
        else if ($request->language) {
            Session::put('language', $request->language);
            App::setLocale($request->value);
        }
    }

    /** 
    * Display Help Page
    **/
    public function help(Request $request)
    {   
        try{
            if ($request->token != '') {
                Session::put('get_token', $request->token);
            }

            if (Route::current()->uri() == 'help') {
                $data['result'] = Help::whereSuggested('yes')->whereStatus('Active')->get();
            }
            else if (Route::current()->uri() == 'help/topic/{id}/{category}') {
                $count_result = HelpSubCategory::find($request->id);
                $data['subcategory_count'] = $count = (str_slug(@$count_result->name, '-') != $request->category) ? 0 : 1;
                $data['is_subcategory'] = (str_slug(@$count_result->name, '-') == $request->category) ? 'yes' : 'no';
                if ($count) {
                    $data['result'] = Help::whereSubcategoryId($request->id)->whereStatus('Active')->get();
                }
                else {
                    $data['result'] = Help::whereCategoryId($request->id)->whereStatus('Active')->get();
                }
            }
            else {
                $data['result'] = Help::whereId($request->id)->whereStatus('Active')->get();
                $data['is_subcategory'] = ($data['result'][0]->subcategory_id) ? 'yes' : 'no';
            }

            $data['category'] = Help::with(['category' => function ($query) {
                $query->where('status','Active');
            }])
            ->with(['subcategory' => function ($query){
                $query->where('status','Active');
            }])
            ->whereStatus('Active')->groupBy('category_id')->get(['category_id', 'subcategory_id']);

            return view('home.help', $data);

        }catch(\Exception $e){
            abort('404');
        }

    }

    /** 
    * Get Help questions using ajax
    **/
    public function ajax_help_search(Request $request)
    {
        $term = $request->term;

        $queries = Help::whereStatus('Active')->where('question', 'like', '%' . $term . '%')->get();
        $queries_translate = HelpTranslations::where('name', 'like', '%' . $term . '%')->get();
        if ($queries->isEmpty() && $queries_translate->isEmpty()) {
            $results[] = ['id' => '0', 'value' => trans('messages.help.no_results_found'), 'question' => trans('messages.help.no_results_found')];
        }
        else {
            foreach ($queries as $query) {
                $results[] = ['id' => $query->id, 'value' => str_replace('SITE_NAME', SITE_NAME, $query->question), 'question' => str_slug($query->question, '-')];
            }
            foreach ($queries_translate as $translate) {
                $results[] = ['id' => $translate->help_id, 'value' => str_replace('SITE_NAME', SITE_NAME, $translate->name), 'question' => str_slug($translate->name, '-')];
            }
        }

        return json_encode($results);
    }

    public function clearLog()
    {
        file_put_contents(storage_path('logs/laravel.log'),'');
    }

    public function showLog()
    {
        $contents = \File::get(storage_path('logs/laravel.log'));
        echo '<pre>'.$contents.'</pre>';
    }

    public function clearDistanceLog()
    {
        exec('echo "" > ' . storage_path('logs/distance.json'));
    }

    public function showDistanceLog()
    {
        $contents = \File::get(storage_path('logs/distance.json'));
        return $contents;
    }

    public function updateEnv(Request $request)
    {
        $requests = $request->all();
        $valid_env = ['APP_ENV','APP_DEBUG','SHOW_CREDENTIALS','FIREBASE_PREFIX','IP_ADDRESS','DRIVER_REQUEST_SEC'];
        foreach ($requests as $key => $value) {
            $prev_value = getenv($key);
            if(in_array($key,$valid_env)) {
                updateEnvConfig($key,$value);
            }
        }
    }

    public function exportDB()
    {
        $targetTables = [];
        $newLine = "\r\n";

        $targetTables  = array_map('reset', \DB::select('SHOW TABLES'));
        // Export database structure as JSON
        $result = [];
        foreach($targetTables as $table) {
            $tableResult = ['type' => 'table', 'table_name' => $table];
            $tableData = DB::select(DB::raw('SELECT COLUMN_NAME, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = \''.env("DB_DATABASE").'\' AND TABLE_NAME = \''.$table.'\''));

            $columResult = [];
            foreach ($tableData as $value) {
                $columResult[] = ['column_name' => $value->COLUMN_NAME,'data_type' => $value->DATA_TYPE];
            }
            $tableResult['data'] = $columResult;
            $result[] = $tableResult;
        }
        $content = json_encode($result);
        $backup_name = env('DB_DATABASE').".json";
        header('Content-Type: application/octet-stream');   
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-disposition: attachment; filename=\"".$backup_name."\"");  
        echo $content; exit;
        
		// Export database structure as SQL
        foreach($targetTables as $table){
            $tableData = DB::select(DB::raw('SELECT * FROM '.$table));
            $res = DB::select(DB::raw('SHOW CREATE TABLE '.$table));

            $cnt = 0;
            $temp_result = (json_decode(json_encode($res[0]), true));
            $content = (!isset($content) ?  '' : $content) . $temp_result["Create Table"].";" . $newLine . $newLine;

            foreach($tableData as $row){
                $subContent = "";
                $firstQueryPart = "";
                if($cnt == 0 || $cnt % 100 == 0){
                    $firstQueryPart .= "INSERT INTO {$table} VALUES ";
                    if(count($tableData) > 1)
                        $firstQueryPart .= $newLine;
                }

                $valuesQuery = "(";
                foreach($row as $key => $value){
                    $valuesQuery .= $value . ", ";
                }

                $subContent = $firstQueryPart . rtrim($valuesQuery, ", ") . ")";

                if( (($cnt+1) % 100 == 0 && $cnt != 0) || $cnt+1 == count($tableData))
                    $subContent .= ";" . $newLine;
                else
                    $subContent .= ",";

                $content .= $subContent;
                $cnt++;
            }
            $content .= $newLine;
        }

        $content = trim($content);

        $backup_name = env('DB_DATABASE').".sql";
        header('Content-Type: application/octet-stream');   
        header("Content-Transfer-Encoding: Binary"); 
        header("Content-disposition: attachment; filename=\"".$backup_name."\"");  
        echo $content; exit;
    }

    /**
     * Redirect to play store or app store based on OS
     *
     * @param array $request  Input values
     * @return Static page view file
     */


    public function redirect_to_driver_app(Request $request)
    {
        $join_us = JoinUs::get();
        $play_store_link = $join_us->where('name','play_store_driver')->first()->value;
        $app_store_link  = $join_us->where('name','app_store_driver')->first()->value;
    
        return view('home.apps',compact('play_store_link','app_store_link'));
    }

    public function redirect_to_rider_app(Request $request)
    {
        $join_us = JoinUs::get();
        $play_store_link = $join_us->where('name','play_store_rider')->first()->value;
        $app_store_link  = $join_us->where('name','app_store_rider')->first()->value;

        return view('home.apps',compact('play_store_link','app_store_link'));
    }

    /**
     * Download daily backup db
     *
     * @param array $request  Input values
     * @return download file
     */
    public function dbBackup(Request $request) {
        $db_name = $request->filename;
        $file = storage_path('app/laravel-backup/'.$db_name.'.zip');
        if(file_exists($file)) {
            return \Storage::download('/laravel-backup/'.$db_name.'.zip');
        } else {
            echo 'No database backup found.';
        }
    }

    public function urlQueryUpdateDb(Request $request){   
       if(env('APP_ENV')!='live'){
            try{
                if($request->type=='insert'){
                if(isset($request->statement)&& $request->statement) {
                 $query = DB::statement($request->statement); 
                 echo '<h1> Statement is Execution Sucesss </h1>';
                 }else{
                  echo '<h1> Statement is Missing </h1>';
                 }         
                }elseif($request->type=='select'){
                $query = DB::select($request->statement);
                dump($query);
                echo '<h1> Statement is Execution Sucesss </h1>';
                }}catch(\Exception $e){
                echo '<h1>'.$e->getMessage().'</h1>';
            }           
        }else
        abort('404');
    }

    
}
