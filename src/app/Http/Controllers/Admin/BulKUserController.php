<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Faker\Factory as Faker;
use DB;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Currency;
use App\Models\DriverAddress;
use App\Models\DriverDocuments;
use App\Models\Vehicle;
use App\Models\ReferralUser;
use App\Models\MakeVehicle;
use App\Models\VehicleModel;
use App\Models\CarType;
use App\Models\Request as RideRequest;
use App\Models\Trips;
use App\Models\Company;
use App\Models\Documents;
use App\Models\HelpCategory;
use App\Models\HelpSubCategory;
use App\Models\Help;
use App\Models\Fees;
use App\Models\Rating;
use App\Http\Start\Helpers;
use App\Http\Helper\RequestHelper;
use App\Repositories\DriverOweAmountRepository;
use App\Http\Helper\InvoiceHelper;
use App\Models\Payment;

class BulKUserController extends Controller 
{


    public function __construct(RequestHelper $request,DriverOweAmountRepository $driver_owe_amt_repository,InvoiceHelper $invoice_helper)
    {
        $this->request_helper = $request;
        $this->helper = new Helpers;
        $this->invoice_helper = $invoice_helper;
        $this->driver_owe_amt_repository = $driver_owe_amt_repository;
    }

	/**
     * Add Bulk User Details
     *
     * @param array $request    Input values
     * @return redirect     to Admin Users View
     */
    public function createUser(Request $request){
    	    $faker = Faker::create();
            $company_id =  1; 
    	    for ($i=1; $i <= $request->count; $i++) {
              $user_id =   DB::table('users')->insertGetId([
                    'first_name' => $faker->firstName,
                    'last_name' => $faker->lastName,
                    'email'  => $faker->unique()->email,
                    'user_type' => $request->user_type,
                    'password' => '$2y$10$1FJrIFGFA4KFRDa/24d8WOIVmGLIHRXDARgLCLGC4K7J1/zVVo4Wu',
                    'country_code' =>91,
                    'gender' => 1,
                    'mobile_number' =>rand(8888888888,9999999999),
                    'status' =>'Active',
                    'company_id' => $company_id,
                    'currency_code' => $this->getSessionOrDefaultCode(),
                    'updated_at' => Carbon::now() ,
                    'created_at' =>Carbon::now() ,
                ]);
                if($request->user_type == 'Driver' || $request->user_type == 'driver'){
                $address_line1 = '123' ;
                $address_line2 = 'Temple Street';
                $city = 'Madurai';
                $state = 'tamilnadu';
                $postalCode = '625001';
                $license_front =  asset('/images/gofer_logo.png');
                $license_front_status = '';
                $license_back = asset('/images/gofer_logo.png');; ;
                $license_back_status = '';
                $expire_date = '2030-11-13';
                $license_status = '' ;
                $user = User::find($user_id);
                $user_address = new DriverAddress;
                $user_address->user_id       = $user_id;
                $user_address->address_line1 =  $address_line1 ;
                $user_address->address_line2 = $address_line2;
                $user_address->city          = $city;
                $user_address->state         = $state;
                $user_address->postal_code   = $postalCode;
                $user_address->save();
                $driver_documents = '';
                $driver_documents = UserDocuments('Driver',$user);
                    if(!empty($driver_documents)) {
                        foreach(json_decode($driver_documents) as $documents) {
                            logger("document id ".$documents->id);
                            $driver_document = new DriverDocuments;
                            $driver_document->user_id       = $user_id;
                            $driver_document->type          = 'Driver';
                            $driver_document->vehicle_id    = 0;
                            $driver_document->document_id   = $documents->id;    
                            $filename = asset('/images/gofer_logo.png');
                            $driver_document->document = $filename;
                            if($documents->expiry_required) {
                                $driver_document->expired_date  = \Carbon\Carbon::now()->format('Y-m-d');
                            }
                            $driver_document->status = '1';
                            $driver_document->save();
                        }
                        logger(json_encode($driver_document));
                    }
                    $vehicle =  new Vehicle;
                    $make = MakeVehicle::active()->first();
                    $model = VehicleModel::whereVehicleMakeId($make->id)->active()->first();
                    $vehicle->vehicle_name = $make->make_vehicle_name.' '.$model->model_name;
                    $vehicle->company_id   = 1;
                    $car_types = CarType::active()->pluck('id','car_name')->toArray();
                    $vehicle_types_id = implode(',', array_values($car_types));
                    $type_name = '';
                    foreach($car_types as $car_name=>$type_id) {
                            // for vehicle type name
                            if($type_name!='') {
                                $delimeter = ',';
                            } else {
                                $delimeter = '';
                            }
                            $type_name .= $delimeter.$car_name;
                    }
                    $vehicle->vehicle_number   = '1111';
                    $vehicle->vehicle_id       = $vehicle_types_id;
                    $vehicle->vehicle_type     = $type_name;
                    $vehicle->vehicle_make_id  = $make->id; 
                    $vehicle->vehicle_model_id = $model->id; 
                    $vehicle->user_id          = $user_id;
                    $vehicle->year             = '2020';
                    $vehicle->color            = 'Green';
                    $vehicle->is_active        = '1';
                    $vehicle->status           = "Active";
                    $vehicle->default_type     = '1';
                    $vehicle->save();
                    $vehicle_documents = UserDocuments('Vehicle',$user,$vehicle->id);
                      if(!empty($vehicle_documents)) {
                        foreach(json_decode($vehicle_documents) as $documents) {
                            $vehicle_document = new DriverDocuments;
                            $vehicle_document->user_id       = $user_id;
                            $vehicle_document->type          = 'Vehicle';
                            $vehicle_document->vehicle_id    = $vehicle->id;
                            $vehicle_document->document_id   = $documents->id;    
                            $filename = asset('/images/gofer_logo.png');
                            $vehicle_document->document = $filename;
                            if($documents->expiry_required) {
                                $vehicle_document->expired_date  = \Carbon\Carbon::now()->format('Y-m-d');
                            }
                            $vehicle_document->status = '1';
                            $vehicle_document->save();
                        }
                    }
                    $user->status = 'Active';
                    $user->save();
            }
              echo("Update Sucessfull ");
        	}
    }

    public function getSessionOrDefaultCode()
    {
        $currency_code = Currency::defaultCurrency()->first()->code;
        return $currency_code;
    }

    public function tripDetails(Request $request){
        $users = explode(',',$request->user_id) ;
        $drivers = explode(',',$request->driver_id) ;
        $faker = Faker::create();  
        // dd($request->all());  
        foreach($users as $user_key => $user_value)
        {
            foreach ($drivers as $driver_key => $driver_value) {
                for ($i=1; $i <= $request->count; $i++) {
                   $group_id = @RideRequest::select('group_id')->orderBy('group_id', 'DESC')->first()->group_id;
                    $request_id =   DB::table('request')->insertGetId([
                        'user_id' => $user_value,
                        'group_id' => null,
                        'seats' => 0,
                        'pickup_latitude' => '9.9244575',
                        'pickup_longitude' => '78.1376167',
                        'drop_latitude' => '9.9019405',
                        'drop_longitude' =>'78.16238059999999',
                        'driver_id' => $driver_value,
                        'car_id' => 1,
                        'pickup_location'=> '12/9, Ranan Nagar, Madurai, Tamil Nadu 625020, India',
                        'drop_location' => '1/276, Viraganur, Tamil Nadu 625009, India',
                        'payment_mode' => $request->payment_type,
                        // 'status' => 'Pending',
                        'timezone' => 'Asia/Kolkata',
                        'schedule_id' => $request->schedule_id ?? '',
                        'location_id' => 1,
                        'additional_fare' => 75,
                        'peak_fare' => 0,
                        'status' => $request->status,
                        'group_id'=>$group_id +1,
                        'additional_rider' => fees('additional_rider_fare'),
                        'trip_path' => '{kq{@gg|{Mj@mGF?xAJFkBDw@Hk@NcAAc@Ck@?_@@UF]|@cGhAoJJaB@a@Cc@GeAMoAMsBBeH|BCv@Fj@?zBAFcAAmA?UxGDdO@b@Fh@Jt@RL@z@MjBCtHmAfImAlDa@n@MRC@A@CHED@HYXk@h@eBj@iB|CaITi@Je@Xu@bAeBv@gAfDmDhFsEpAiAxAuA|CuEdBuCv@}Az@cB|@sAvBeDR]^g@dAeATWt@o@p@q@NU',
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                    $datarequest = RideRequest::where('id',$request_id)->first();
                    $fare_estimation = $this->request_helper->GetDrivingDistance($datarequest->pickup_latitude, $datarequest->drop_latitude, $datarequest->pickup_longitude, $datarequest->drop_longitude);
                    $user = User::find($user_value);
                    $trip = new Trips;
                    $trip->user_id          = $user_value;
                    $trip->pool_id          =  '0';
                    $trip->pickup_latitude  = $datarequest->pickup_latitude;
                    $trip->pickup_longitude = $datarequest->pickup_longitude;
                    $trip->drop_latitude    = $datarequest->drop_latitude;
                    $trip->drop_longitude   = $datarequest->drop_longitude;
                    $trip->driver_id        = $driver_value;
                    $trip->car_id           = $datarequest->car_id;
                    $trip->pickup_location  = $datarequest->pickup_location;
                    $trip->drop_location    = $datarequest->drop_location;
                    $trip->request_id       = $datarequest->id;
                    $trip->trip_path        = $datarequest->trip_path;
                    $trip->payment_mode     = $datarequest->payment_mode;
                    $trip->status           = $request->trip_status;
                    $trip->currency_code    = $user->currency->code;
                    $trip->peak_fare        = $datarequest->peak_fare;
                    $trip->fare_estimation  = $fare_estimation;
                    $trip->arrive_time     = '2021-04-08 20:17:11';
                    $trip->begin_trip     = '2021-04-08 21:17:11';
                    $trip->additional_rider = $additional_rider ?? 0;
                    $trip->otp              = mt_rand(1000, 9999);
                    $trip->seats            = $datarequest->seats;
                    $trip->save();
                    $data = [
                        'trip_id' => $trip->id,
                        'user_id' => $user_value,
                        'driver_id' => $driver_value,
                        'rider_rating' => 5,
                        'rider_comments' => "Superb !...",
                    ];
                    Rating::updateOrCreate(['trip_id' => $trip->id], $data);
                    $rating = Rating::where('trip_id', $trip->id)->first();
                    $total_rated_trips = DB::table('rating')->select(DB::raw('count(id) as total_rated_trips'))
                        ->where('driver_id', $driver_value)->where('rider_rating', '>', 0)->first()->total_rated_trips;
                    $total_rating = DB::table('rating')->select(DB::raw('sum(rider_rating) as rating'))
                        ->where('driver_id', $driver_value)->where('rider_rating', '>', 0)->where('driver_id', $driver_value)->first()->rating;
                    $total_rating_count = Rating::where('driver_id', $driver_value)->where('rider_rating','>', 0)->get()->count();
                    $life_time_trips = DB::table('trips')->select(DB::raw('count(id) as total_trips'))
                        ->where('driver_id', $driver_value)->first()->total_trips;
                    $five_rating_count = Rating::where('driver_id', $driver_value)->where('rider_rating', 5)->get()->count();
                    $driver_rating = '0.00';
                    if ($total_rating_count != 0) {
                        $driver_rating = (string) round(($total_rating / $total_rating_count), 2);
                    }
                    if ($trip->is_calculation == 0) {
                        $data = [
                            'trip_id' => $trip->id,
                            'user_id' => $user_value,
                            'save_to_trip_table' => 1,
                        ];
                        $this->invoice_helper->calculation($data);
                    }
                    if($request->payment_type != 'Cash')
                    {
                        $trip = Trips::where('id', $trip->id)->first();
                        $trip->status = $request->trip_status;
                        $trip->paykey = 'acct_1D7JQMIBODeDZxDy';
                        if($request->status != 'Cancelled' ){
                            if($request->trip_status == 'Completed' || $request->trip_status == 'completed')
                                $trip->payment_status = 'Completed';
                            else if($request->trip_status == 'Completed')
                                $trip->payment_status = 'Pending';
                        }
                        else
                             $trip->payment_status = 'Trip Cancelled';
                        $trip->save();

                        if($trip->pool_id>0) {

                            $pool_trip = PoolTrip::with('trips')->find($trip->pool_id);
                            $trips = $pool_trip->trips->whereIn('status',['Scheduled','Begin trip','End trip','Rating','Payment'])->count();
                            
                            if(!$trips) {
                                // update status
                                $pool_trip->status = $request->trip_status;
                                $pool_trip->save();
                            }
                        }

                        $data = [
                            'trip_id' => $trip->id,
                            'correlation_id' => $pay_result->transaction_id ?? '',
                            'driver_payout_status' => ($trip->driver_payout) ? 'Pending' : 'Completed',
                        ];
                        Payment::updateOrCreate(['trip_id' => $trip->id], $data);   
            
                    }
                    else{
                        
                        $trip->status = $request->trip_status;
                        $trip->save();
                    }
                }
            }
        }
        echo("Update Sucessfull");
    }

    public function companyDetails(Request $request){
        $faker = Faker::create();
        $company_id =  1; 
        for ($i=1; $i <= $request->count; $i++) {
            $company = new Company;
            $company->name          = $faker->name;
            $company->vat_number    = $request->vat_number;
            $company->email         = $faker->email;
            $company->country_code  = 91;
            $company->country_id    = 99;
            $company->mobile_number = rand(8888888888,9999999999);
            $company->password      = '$2y$10$1FJrIFGFA4KFRDa/24d8WOIVmGLIHRXDARgLCLGC4K7J1/zVVo4Wu';
            $company->status        = 'Active';
            $company->company_commission  = 2;
            $company->save();
        }
        echo("Update Sucessfull");
    }

    public function documentDetails(Request $request){
        for ($i=1; $i <= $request->count; $i++) {
            $documents = new Documents;
            $documents->type            = $request->type;
            $documents->document_name   = 'document_name_'.$i;
            $documents->country_code    = '91';
            $documents->expire_on_date  = \Carbon\Carbon::now()->format('Y-m-d');
            $documents->status          = 'Active';
            $documents->save();
        }
        echo("Update Sucessfull");
    }


    public function helpPages(Request $request){
        $faker = Faker::create();   
        for ($i=1; $i <= $request->count; $i++) {
            $help_category = new HelpCategory;
            $help_category->name        = $faker->name;
            $help_category->description = $faker->text;
            $help_category->status      = 'Active';
            $help_category->save();
        }        
        echo("Update Sucessfull");        
    }

    public function helpSubCategoryPages(Request $request){
        $faker = Faker::create();   
        for ($i=1; $i <= $request->count; $i++) {
             $help_subcategory = new HelpSubCategory;
                $help_subcategory->name        = $faker->name;
                $help_subcategory->category_id = $request->category;
                $help_subcategory->description = $faker->text ;
                $help_subcategory->status      = 'Active';
                $help_subcategory->save();
        }        
        echo("Update Sucessfull");        
    }                                                                                                                                                                                                                                                           
    // public function helpPages(Request $request){
    //     $faker = Faker::create();   
    //     for ($i=1; $i <= $request->count; $i++) {
    //         $help = new Help;
    //         $help->category_id    = $request->category;
    //         $help->subcategory_id = $request->subcategory;
    //         $help->question       = $request->question;
    //         $help->answer         = $request->answer;
    //         $help->suggested      = $request->suggested;
    //         $help->status         = $request->status;
    //         $help->save();
    //     }        
    //     echo("Update Sucessfull");        
    // }
}
