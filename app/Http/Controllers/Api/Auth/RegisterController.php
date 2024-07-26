<?php

namespace App\Http\Controllers\Api\Auth;

use App\Constants\Status;
use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\AdminNotification;
use App\Models\GeneralSetting;
use App\Models\User;
use App\Models\UserLogin;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use App\Models\BasicInfo;
use App\Models\BloodGroup;
use App\Models\CareerInfo;
use App\Models\ReligionInfo;
use App\Models\MaritalStatus;
use App\Models\FamilyInfo;
use App\Models\EducationInfo;
use App\Models\PartnerExpectation;
use App\Models\PhysicalAttribute;
use App\Models\Community;
use App\Models\MotherTongue;
use App\Models\Profession;
use App\Models\PositionHeld;
use Carbon\Carbon;
use App\Models\Drinking;
use App\Models\Smoking;
use App\Rules\CheckEmailUsage;
use App\Rules\CheckMobileNumber;



class RegisterController extends BaseController
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware('guest');
        $this->middleware('registration.status')->except('registrationNotAllowed');
    }


    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */


    protected function validator(array $data)
    {
        $general = GeneralSetting::first();
        $passwordValidation = Password::min(6);
        if ($general->secure_password) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }
        $agree = 'nullable';
        if ($general->agree) {
            $agree = 'required';
        }
        $countryData = (array)json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryCodes = implode(',', array_keys($countryData));
        $mobileCodes = implode(',', array_column($countryData, 'dial_code'));
        $countries = implode(',', array_column($countryData, 'country'));
        $validate = Validator::make($data, [
            'email' => ['required', 'string', 'email', new CheckEmailUsage],
            'mobile' => ['required', 'integer', new CheckMobileNumber],
            'password' => ['required', 'confirmed', $passwordValidation],
            'username' => 'required|alpha_num|unique:users|min:6',
            'captcha' => 'sometimes|required',
            'mobile_code' => 'required|in:' . $mobileCodes,
            'country_code' => 'required|in:' . $countryCodes,
            'country' => 'required|in:' . $countries,
            'agree' => $agree
        ]);
        return $validate;
    }


    public function register(Request $request)
    {
        // dd($request->all());
        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $validator->errors()->all()],
            ]);
        }

        if (preg_match("/[^a-z0-9_]/", trim($request->username))) {
            $response[] = 'No special character, space or capital letters in username.';
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $response],
            ]);
        }

        $exist = User::where('mobile', $request->mobile_code . $request->mobile)->first();
        if ($exist) {
            $response[] = 'The mobile number already exists';
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $response],
            ]);
        }


        $user = $this->create($request->all());

        $response['access_token'] =  $user->createToken('auth_token')->plainTextToken;
        $response['user'] = $user;

        //basic info

        // Define validation rules for basic info table
        $profileRules = [
            'user_type' => 'required',

        ];

        // Define validation messages for basic info table
        $basicInfoMessages = [
            'user_type.required' => 'User Type is required.',
        ];
        // Validate profile basic info fields
        $basicInfoValidator = Validator::make($request->all(), $profileRules, $basicInfoMessages);
        // If validation fails for basic info table
        if ($basicInfoValidator->fails()) {
            return response()->json([
                'remark' => 'validation_error',
                'status' => 'error',
                'message' => ['error' => $basicInfoValidator->errors()->all()],
            ]);
        }


        $basicInfo                      = new BasicInfo();
        $basicInfo->user_id             = $user->id;
        $basicInfo->gender              = $request->gender ?? '';
        $basicInfo->profession          = $request->profession ?? '';
      //  $basicInfo->position_held          = $request->position_held ?? '';
        $basicInfo->religion            = $request->religion ?? '';
        $basicInfo->mother_tongue       = $request->mother_tongue ?? '';
        $basicInfo->community           = $request->community ?? '';
        $basicInfo->birth_date          = Carbon::parse($request->birth_date)->format('Y-m-d') ?? '';
     //   $basicInfo->age             = $request->age ?? '';
        $basicInfo->about_us      = $request->about_us ?? '';
        $basicInfo->marital_status      = $request->marital_status ?? '';
        $basicInfo->batch_start      = Carbon::parse($request->batch_start)->format('Y-m-d') ?? '';
        $basicInfo->batch_end      = Carbon::parse($request->batch_end)->format('Y-m-d') ?? '';
        $basicInfo->financial_condition = $request->financial_condition;

        $basicInfo->cadar      = $request->cadar ?? '';
      
        // $basicInfo->phone      = $request->phone ?? '';
   
            
       

        $basicInfo->user_type      = $request->user_type ?? '';


        $basicInfo->present_address = [
            'country'  => @$user->address->country ?? $request->pre_country,
            'state'    => $request->state,
            'zip'      => $request->pre_zip,
            'district'     => $request->district,
        ];
        $basicInfo->permanent_address = [
            'country'  => @$user->address->country ?? $request->per_country,
            'state'    => $request->state,
            'zip'      => $request->zip,
            'district'     => $request->district,
        ];
        $basicInfo->save();




        // partnerExpectation
        $partnerExpectation   = new PartnerExpectation();
        $partnerExpectation->user_id     = $user->id;
        $partnerExpectation->marital_status = $request->marital_status_p;
        $partnerExpectation->religion = $request->religion_p;
        $partnerExpectation->community = $request->community_p;
        $partnerExpectation->mother_tongue = $request->mother_tongue_p;     
        $partnerExpectation->profession = $request->profession_p;
        $partnerExpectation->position = $request->position_p;     
        $partnerExpectation->general_requirement = $request->general_requirement_p;
        $partnerExpectation->country = $request->country_p;
        $partnerExpectation->min_age = $request->min_age_p;
        $partnerExpectation->max_age = $request->max_age_p;
        $partnerExpectation->min_height = $request->min_height_p;
        $partnerExpectation->max_height = $request->max_height_p;
        $partnerExpectation->smoking_status = $request->smoking_status ?? 0;
        $partnerExpectation->drinking_status = $request->drinking_status ?? 0;
        $partnerExpectation->save();




        //physical attribute
        $physicalAttribute              = new PhysicalAttribute();
        $physicalAttribute->user_id     = $user->id;
        $physicalAttribute->height      = $request->height ?? '';
        $physicalAttribute->weight      = $request->weight ?? '';
        $physicalAttribute->blood_group = $request->blood_group ?? '';
        $physicalAttribute->eye_color   = $request->eye_color ?? '';
        $physicalAttribute->hair_color  = $request->hair_color ?? '';
        $physicalAttribute->save();



        
        //educationInfo 
        $educationInfo              = new EducationInfo();
        $educationInfo->user_id     = $user->id;
        $educationInfo->degree      = $request->degree ?? '';
        $educationInfo->field_of_study      = $request->field_of_study ?? '';
        $educationInfo->institute = $request->institute ?? '';
        $educationInfo->save();



        $CareerInfo              = new CareerInfo();
        $CareerInfo->user_id     = $user->id;
        $CareerInfo->position      = $request->position ?? '';
        $CareerInfo->state_posting      = $request->state_posting ?? '';
        $CareerInfo->district_posting      = $request->district_posting ?? '';
        $CareerInfo->from         = $request->from ?? '';
        $CareerInfo->end          = $request->end  ?? '';
        
        $CareerInfo->save();



        $response['token_type'] = 'Bearer';
        $notify[] = 'Registration successful';
        return response()->json([
            'remark' => 'registration_success',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => $response
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     * @return \App\User
     */

    protected function create(array $data)
    {
        // dd($data);
        $general = GeneralSetting::first();

        $referBy = @$data['reference'];
        if ($referBy) {
            $referUser = User::where('username', $referBy)->first();
        } else {
            $referUser = null;
        }
        //User Create
        $user               = new User();
        $user->profile_id   = getNumber(8);
        $user->looking_for  = $data['looking_for'];
        $user->email        = strtolower($data['email']);
        $user->password     = Hash::make($data['password']);
        $user->username     = $data['username'];
        $user->firstname    = $data['firstname'];
        $user->lastname     = $data['lastname'];
        $user->religion    = $data['religion'] ?? '';
        $user->marital_status     = $data['marital_status'] ?? '';
        $user->mother_tongue     = $data['mother_tongue'] ?? '';
        $user->community     = $data['community'] ?? '';
        $user->profession     = $data['profession'] ?? '';
        $user->middle_name     = $data['middle_name'] ?? '';
        $user->gender     = $data['gender'] ?? '';

        $user->fun     = $data['fun'] ?? '';
        $user->fitness     = $data['fitness'] ?? '';
        $user->other_interest     = $data['other_interest'] ?? '';
        $user->creative     = $data['creative'] ?? '';
        $user->hobby     = $data['hobby'] ?? '';

        if ($data['image']) {
            $fileName = fileUploader($data['image'], getFilePath('userProfile'), getFileSize('userProfile'), $data['image']);
            $user->image = $fileName;
        }


        //  $user->ref_by       = $referUser ? $referUser->id : 0;
        $user->country_code = $data['country_code'];
        $user->mobile       = $data['mobile_code'] . $data['mobile'];
        $user->address      = [
            'address' => isset($data['address']) ? $data['address'] : null,
            'state' => isset($data['state']) ? $data['state'] : null,
            'zip' => isset($data['zip']) ? $data['zip'] : null,
            'country' => isset($data['country']) ? $data['country'] : null,
            'district' => isset($data['district']) ? $data['district'] : null,
        ];
//        $user->ev = $general->ev ? Status::UNVERIFIED : Status::VERIFIED;
        $user->kv = $general->kv;

        $user->sv = $general->sv ? Status::UNVERIFIED : Status::VERIFIED;
        $user->save();



        $adminNotification = new AdminNotification();
        $adminNotification->user_id = $user->id;
        $adminNotification->title = 'New member registered';
        $adminNotification->click_url = urlPath('admin.users.detail', $user->id);
        $adminNotification->save();


        //Login Log Create
        $ip = getRealIP();
        $exist = UserLogin::where('user_ip', $ip)->first();
        $userLogin = new UserLogin();


        //Check exist or not
        if ($exist) {
            $userLogin->longitude =  $exist->longitude;
            $userLogin->latitude =  $exist->latitude;
            $userLogin->city =  $exist->city;
            $userLogin->country_code = $exist->country_code;
            $userLogin->country =  $exist->country;
        } else {
            $info = json_decode(json_encode(getIpInfo()), true);
            $userLogin->longitude =  @implode(',', $info['long']);
            $userLogin->latitude =  @implode(',', $info['lat']);
            $userLogin->city =  @implode(',', $info['city']);
            $userLogin->country_code = @implode(',', $info['code']);
            $userLogin->country =  @implode(',', $info['country']);
        }

        $userAgent = osBrowser();
        $userLogin->user_id = $user->id;
        $userLogin->user_ip =  $ip;

        $userLogin->browser = @$userAgent['browser'];
        $userLogin->os = @$userAgent['os_platform'];
        $userLogin->save();


        return $user;
    }
  

    public function user_attributes(){

        $data['religions']       = ReligionInfo::get();
        $data['maritalStatuses'] = MaritalStatus::get();
        $data['community']       = Community::with('religion')->get();
        $data['motherTongue'] = MotherTongue::get();

        $data['profession']       = Profession::get();
        $data['positionHeld'] = PositionHeld::get();

        $data['smoking']       = Smoking::get();
        $data['drinking'] = Drinking::get();

        return  $this->sendResponse(true, $data, 'User Attributes  Retrieved successful .');


    }

    public function religion_community(Request $request){

        $religion= ReligionInfo::where('id',$request->religion_id)->first();
        $data['community']       = Community::where('religion_id',$religion->id)->get();

        return  $this->sendResponse(true, $data, 'Community Attributes  successful .');


    }
}


