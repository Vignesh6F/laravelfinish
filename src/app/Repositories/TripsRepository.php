<?php 

/**
 * Repository
 *
 * @package     Gofer
 * @subpackage  Repository
 * @author      Trioangle Product Team
 * @version     2.2.1
 * @link        http://trioangle.com
 */

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use App\Models\Trips;
use DB;

class TripsRepository
{
	/**
     * Get Heat Map Data
     *
     *
     * @return Collection instance of trips
     */
    public function heatMapData() {
        $heat_map_hours = site_settings('heat_map_hours');
        $date_obj = \Carbon\Carbon::now();

        $current_date = $date_obj->format('Y-m-d');
        $current_time = $date_obj->format('Y-m-d H:i:s');
        $prev_time = $date_obj->subHours($heat_map_hours)->format('Y-m-d H:i:s');  
        $requests = DB::table('request')->select('pickup_latitude','pickup_longitude', DB::raw('count(*) as weight'))->whereBetween('created_at', array($prev_time, $current_time))->groupBy('group_id')->orderByDesc('id')->get();

        return $requests;      
    }
    
}
