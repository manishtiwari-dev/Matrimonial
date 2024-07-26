<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\BasicInfo;
use App\Models\CareerInfo;
use App\Models\ReligionInfo;
use App\Models\MaritalStatus;
use App\Models\FamilyInfo;
use App\Models\EducationInfo;
use App\Models\PartnerExpectation;
use App\Models\PhysicalAttribute;
use App\Models\Community;
use App\Models\MotherTongue;
use Validator;
use Carbon\Carbon;


class ProfileController extends BaseController
{


    public function userData()
    {
        $user = auth()->user();


        $data['religions']       = ReligionInfo::get();
        $data['maritalStatuses'] = MaritalStatus::get();
        $data['community']       = Community::get();
        $data['motherTongue'] = MotherTongue::get();

        $data['countries']   = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $data['user'] = $user;
        $about_content = getContent('about.content', true);
        $about_element = getContent('about.element', false, 6, true);
        $data['about_content'] = $about_content;
        $data['about_element'] = $about_element;
        return  $this->sendResponse(true, $data, 'Profile Data Retrieved successful .');
    }

    public function profileData()
    {

        $userId = auth()->id();

        $user = User::with('basicInfo', 'religion','mother_tongue','community','profession','bloodGroups','maritialStatus','careerInfo.positionHeld','partnerExpectation','partnerExpectation.maritialStatus','partnerExpectation.profession','partnerExpectation.religion','partnerExpectation.mother_tongue','partnerExpectation.smoking','partnerExpectation.drinking','partnerExpectation.community', 'partnerExpectation.positionHeld','physicalAttributes', 'family', 'careerInfo','careerInfo.positionHeld', 'educationInfo','basicInfo.religion','basicInfo.mother_tongue','basicInfo.community','basicInfo.profession','basicInfo.maritialStatus','basicInfo.smoking','basicInfo.drinking')->findOrFail($userId);

        $data['user'] = $user;
    
        return  $this->sendResponse(true, $data, 'Profile Data Retrieved successful .');
    }


    public function updateUser(Request $request)
    {

        $userId = auth()->id();
        $userDetails =   User::find($userId );
        $userDetails->fitness =  $request->fitness;
        $userDetails->fun =  $request->fun;
        $userDetails->other_interest = $request->other_interest;
        $userDetails->creative = $request->creative;
        $userDetails->hobby = $request->hobby;
        $userDetails->save();


    }




    public function profileSetting(Request $request)
    {
        $step = $request->type;

        $steps = array('basicInfo', 'familyInfo', 'educationInfo', 'careerInfo', 'physicalAttributeInfo', 'partnerExpectation');

        if (!in_array($step, $steps)) {
            abort('404');
        }


        $user = auth()->user();


        $response = $this->$step($request, $user);

        if ($response && !$response['success']) {
            $notify[] = ['error', $response['message']];
            return  $this->sendResponse(true, $response,  $notify);
        }


        return  $this->sendResponse(true, $response, 'Profile Setting update successful');
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'method' => 'required'
        ]);
        $user = auth()->user();
        $method = $request->method;

        try {
            $notify = $this->$method($request, $user);
        } catch (\Exception $e) {
            $notify[] = ['error', $e->getMessage()];
            return back()->withNotify($notify)->withInput($request->all());
        }

        return  $this->sendResponse(true, [], $notify);
    }


    protected function basicInfo($request, $user)
    {
    

        $user->firstname = $request->firstname;
        $user->lastname = $request->lastname;
        $user->save();

        $basicInfo = BasicInfo::where('user_id', $user->id)->first();

        $notification = 'Basic Information updated successfully';
        if (!$basicInfo) {
            $basicInfo = new BasicInfo();
            $basicInfo->user_id  = $user->id;
            $notification = 'Basic Information added successfully';
        }
        $basicInfo->gender              = $request->gender;
        $basicInfo->profession          = $request->profession;
        $basicInfo->financial_condition = $request->financial_condition;
        $basicInfo->religion            = $request->religion;
        $basicInfo->smoking_status      = $request->smoking_status;
        $basicInfo->drinking_status     = $request->drinking_status;
        $basicInfo->birth_date          = Carbon::parse($request->birth_date)->format('Y-m-d') ?? '';
        $basicInfo->language            = $request->language;
        $basicInfo->marital_status      = $request->marital_status;
        $basicInfo->mother_tongue      = $request->mother_tongue;
        $basicInfo->community          = $request->community;
        $basicInfo->about_us           = $request->about_us ?? '';
        $basicInfo->user_type           = $request->user_type ?? '';

        $basicInfo->batch_start      = $request->batch_start ?? '';
        $basicInfo->batch_end      = $request->batch_end ?? '';
        $basicInfo->cadar      = $request->cadar ?? '';
    
    
        $basicInfo->present_address = [
            'country'  => @$user->address->country ?? $request->per_country,
            'state'    => $request->pre_state,
            'zip'      => $request->pre_zip,
            'city'     => $request->pre_city,
        ];
        $basicInfo->permanent_address = [
            'country'  => $request->per_country,
            'state'    => $request->per_state,
            'zip'      => $request->per_zip,
            'city'     => $request->per_city,
        ];
        $basicInfo->save();
        $this->updateRegistrationStep($user, 1, 'completed_step');

      //  return  $this->sendResponse(true, [], $notification);
        // $this->updateRegistrationStep($user, 1, 'completed_step');
        // }
    }


    protected function physicalAttributeInfo($request, $user)
    {
        // if (!$request->has('button_value')) {
        //     $this->updateRegistrationStep($user, 5, 'skipped_step');
        // } else {
        //     $rules = [
        //         'height'      => 'required|numeric|gt:0',
        //         'weight'      => 'required|numeric|gt:0',
        //         'blood_group' => 'required|exists:blood_groups,name',
        //         'eye_color'   => 'required|string|max:40',
        //         'hair_color'  => 'required|string|max:40',
        //         'complexion'  => 'required|string|max:255',
        //         'disability'  => 'nullable|string|max:40'
        //     ];

        //     $messages = [
        //         'height.required'      => 'Height field is required',
        //         'height.numeric'       => 'Height should be a number',
        //         'height.gt'            => 'Height can\'t be a negative number',
        //         'weight.required'      => 'Weight field is required',
        //         'weight.numeric'       => 'Weight should be a number',
        //         'weight.gt'            => 'Weight can\'t be a negative number',
        //         'blood_group.required' => 'Blood group field is required',
        //         'eye_color.required'   => 'Eye color field is required',
        //         'eye_color.string'     => 'Eye color field should be string',
        //         'eye_color.max'        => 'Eye color must not be greater than 40 characters',
        //         'hair_color.required'  => 'Hair color field is required',
        //         'hair_color.string'    => 'Hair color field should be string',
        //         'hair_color.max'       => 'Hair color must not be greater than 40 characters',
        //         'complexion.required'  => 'Complexion field is required',
        //         'complexion.string'    => 'Complexion field should be string',
        //         'complexion.max'       => 'Complexion must not be greater than 255 characters',
        //         'disability.string'    => 'Disability field should be string',
        //         'disability.max'       => 'disability must not be greater than 40 characters',
        //     ];

        //     $request->validate($rules, $messages);

            $physicalAttribute              = new PhysicalAttribute();
            $physicalAttribute->user_id     = $user->id;
            $physicalAttribute->height      = $request->height;
            $physicalAttribute->weight      = $request->weight;
            $physicalAttribute->blood_group = $request->blood_group;
            $physicalAttribute->eye_color   = $request->eye_color;
            $physicalAttribute->hair_color  = $request->hair_color;
            $physicalAttribute->complexion  = $request->complexion;
            $physicalAttribute->disability  = $request->disability;
            $physicalAttribute->save();

            $this->updateRegistrationStep($user, 5, 'completed_step');
        }
    
    
    private function partnerExpectation($request, $user)
    {
        // $validator = Validator::make($request->all(), [
        //     'general_requirement' => 'nullable|string|max:255',
        //     'country'             => 'nullable',
        //     'min_age'             => 'nullable|integer|gt:0',
        //     'max_age'             => 'nullable|integer|gt:0',
        //     'min_height'          => 'nullable',
        //     'max_height'          => 'nullable',
        //     'max_weight'          => 'nullable|numeric|gt:0',
        //     'marital_status'      => 'nullable',
        //     'religion'            => 'nullable|exists:religion_infos,name',
        //     // 'smoking_status'      => 'nullable|in:0,1,3',
        //     // 'drinking_status'     => 'nullable|in:0,1,3',
        //     'language'            => 'nullable|array',
        //     'language.*'          => 'string',
        //     'min_degree'          => 'nullable|string|max:40',
        //     'personality'         => 'nullable|string|max:40',
        //     'profession'          => 'nullable|string|max:40',
        //     'financial_condition' => 'nullable|string|max:40',
        //     'family_position'     => 'nullable|string|max:40'
        // ], [
        //     'general_requirement.string'  => 'General requirement should be string',
        //     'general_requirement.max'     => 'General requirement must not be greater than 255 words',
        //     'min_age.integer'             => 'Minimum age should be integer',
        //     'min_age.gt'                  => 'Minimum age can\'t be a negative number',
        //     'max_age.integer'             => 'Maximum age should be integer',
        //     'max_age.gt'                  => 'Maximum age can\'t be a negative number',
        //   //  'min_height.numeric'          => 'Minimum height should be a number',
        //     'min_height.gt'               => 'Minimum height can\'t be a negative number',
        //     'max_weight.numeric'          => 'Minimum height should be a number',
        //     'max_weight.gt'               => 'Minimum height can\'t be a negative number',
        //     'min_degree.string'           => 'Minimum degree should be string',
        //     'min_degree.max'              => 'Minimum degree must not be greater than 40 words',
        //     'personality.string'          => 'Personality should be string',
        //     'personality.max'             => 'Personality must not be greater than 40 words',
        //     'profession.string'           => 'Profession should be string',
        //     'profession.max'              => 'Profession must not be greater than 40 words',
        //     'financial_condition.string'  => 'Financial condition should be string',
        //     'financial_condition.max'     => 'Financial condition must not be greater than 40 words',
        //     'family_position.string'      => 'Family position should be string',
        //     'family_position.max'         => 'Family position must not be greater than 40 words',
        // ]);


        // if ($validator->fails()) {
        //     return  $this->sendResponse(true, [],  $validator->messages());
        // }

        $partnerExpectation  = PartnerExpectation::where('user_id', $user->id)->first();
        $notification = 'Partner expectation updated successfully';
        if (!$partnerExpectation) {
            $partnerExpectation = new PartnerExpectation();
            $partnerExpectation->user_id = $user->id;
            $notification = 'Partner expectation added successfully';
        }
        $partnerExpectation->general_requirement = $request->general_requirement;
        $partnerExpectation->country = $request->country;
        $partnerExpectation->min_age = $request->min_age;
        $partnerExpectation->max_age = $request->max_age;
        $partnerExpectation->min_height = $request->min_height;
        $partnerExpectation->max_height = $request->max_height;
        $partnerExpectation->max_weight = $request->max_weight;
        $partnerExpectation->marital_status = $request->marital_status;
        $partnerExpectation->religion = $request->religion;
        $partnerExpectation->community = $request->community;
        $partnerExpectation->mother_tongue = $request->mother_tongue;
      //  $partnerExpectation->complexion = $request->complexion;
        $partnerExpectation->smoking_status = $request->smoking_status ?? 0;
        $partnerExpectation->drinking_status = $request->drinking_status ?? 0;
        $partnerExpectation->language = $request->language ?? [];
        $partnerExpectation->min_degree = $request->min_degree;
        $partnerExpectation->profession = $request->profession;
        $partnerExpectation->personality = $request->personality;
        $partnerExpectation->financial_condition = $request->financial_condition;
        $partnerExpectation->family_position = $request->family_position;

    //    $partnerExpectation->position_held = $request->position_held;     


        $partnerExpectation->save();
        $this->updateRegistrationStep($user, 6, 'completed_step');

        return  $this->sendResponse(true, [], $notification);
    }

    private function physicalAttributes($request, $user)
    {

        // $validator = Validator::make($request->all(), [
        //     'height'      => 'required|numeric|gt:0',
        //     'weight'      => 'required|numeric|gt:0',
        //     'blood_group' => 'required|exists:blood_groups,name',
        //     'eye_color'   => 'required|string|max:40',
        //     'hair_color'  => 'required|string|max:40',
        //     'complexion'  => 'required|string|max:255',
        //     'disability'  => 'nullable|string|max:40'
        // ], [
        //     'height.required'      => 'Height field is required',
        //     'height.numeric'       => 'Height should be a number',
        //     'height.gt'            => 'Height can\'t be a negative number',
        //     'weight.required'      => 'Weight field is required',
        //     'weight.numeric'       => 'Weight should be a number',
        //     'weight.gt'            => 'Weight can\'t be a negative number',
        //     'blood_group.required' => 'Blood group field is required',
        //     'eye_color.required'   => 'Eye color field is required',
        //     'eye_color.string'     => 'Eye color field should be string',
        //     'eye_color.max'        => 'Eye color must not be greater than 40 characters',
        //     'hair_color.required'  => 'Hair color field is required',
        //     'hair_color.string'    => 'Hair color field should be string',
        //     'hair_color.max'       => 'Hair color must not be greater than 40 characters',
        //     'complexion.required'  => 'Complexion field is required',
        //     'complexion.string'    => 'Complexion field should be string',
        //     'complexion.max'       => 'Complexion must not be greater than 255 characters',
        //     'disability.string'    => 'Disability field should be string',
        //     'disability.max'       => 'disability must not be greater than 40 characters',
        // ]);


        // if ($validator->fails()) {
        //     return  $this->sendResponse(true, [],  $validator->messages());
        // }


        $physicalAttribute = PhysicalAttribute::where('user_id', $user->id)->first();
        $notification = 'Physical attributes updated successfully';
        if (!$physicalAttribute) {
            $physicalAttribute              = new PhysicalAttribute();
            $physicalAttribute->user_id     = $user->id;
            $notification = 'Physical attributes added successfully';
        }
        $physicalAttribute->height      = $request->height;
        $physicalAttribute->weight      = $request->weight;
        $physicalAttribute->blood_group = $request->blood_group;
        $physicalAttribute->eye_color   = $request->eye_color;
        $physicalAttribute->hair_color  = $request->hair_color;
        $physicalAttribute->complexion  = $request->complexion;
        $physicalAttribute->disability  = $request->disability;
        $physicalAttribute->save();
        $this->updateRegistrationStep($user, 5, 'completed_step');

        return  $this->sendResponse(true, [], $notification);
    }


    protected function familyInfo($request, $user)
    {

        // $validator = Validator::make($request->all(), [
        //     'father_name' => 'required',
        //     'father_contact' => 'required|numeric|gt:0',
        //     'mother_name' => 'required',
        //     'mother_contact' => 'required|numeric|gt:0',
        //     'total_brother' => 'nullable|min:0',
        //     'total_sister' => 'nullable|min:0',
        // ], [
        //     'father_name.required' => 'Father name is required',
        //     'father_contact.required' => 'Father\'s contact number is required',
        //     'father_contact.numeric' => 'Father\'s contact number should be a number',
        //     'father_contact.gt' => 'Father\'s contact number should be a positive number',
        //     'mother_name.required' => 'Mother name is required',
        //     'mother_contact.required' => 'Mother contact is required',
        //     'mother_contact.numeric' => 'Mothers\'s contact number should be a number',
        //     'mother_contact.gt' => 'Mothers\'s contact number should be a positive number',
        //     'total_brother.min' => 'Total brother can\'t be a negative number',
        //     'total_sister.min' => 'Total sister can\'t be a negative number'
        // ]);


        // if ($validator->fails()) {
        //     return  $this->sendResponse(true, [],  $validator->messages());
        // }


        $familyInfo = FamilyInfo::where('user_id', $user->id)->first();
        $notification = 'Family information updated successfully';

        if (!$familyInfo) {
            $familyInfo = new FamilyInfo();
            $familyInfo->user_id = $user->id;
            $notification = 'Family information added successfully';
        }

        $familyInfo->father_name = $request->father_name;
        $familyInfo->father_profession = $request->father_profession;
        $familyInfo->father_contact = $request->father_contact;
        $familyInfo->mother_name = $request->mother_name;
        $familyInfo->mother_profession = $request->mother_profession;
        $familyInfo->mother_contact = $request->mother_contact;
        $familyInfo->total_brother = $request->total_brother ?? 0;
        $familyInfo->total_sister = $request->total_sister ?? 0;
        $familyInfo->save();
        $this->updateRegistrationStep($user, 2, 'completed_step');

        return  $this->sendResponse(true, [], $notification);
    }

    public function updateEducationInfo(Request $request)
    {

        // $validator = Validator::make($request->all(), [
        //     'institute'      => 'required|string',
        //     'degree'         => 'required|string',
        //     'field_of_study' => 'required|string|max:255',
        //     'reg_no'         => 'nullable|integer|gt:0',
        //     'roll_no'        => 'nullable|integer|gt:0',
        //     'start'          => 'required|integer|gt:0|digits:4|max:' . date('Y'),
        //     'end'            => 'nullable|integer|gt:0|digits:4|after:start|max:' . date('Y'),
        //     'result'         => 'nullable|numeric|gte:0',
        //     'out_of'         => 'nullable|numeric|gte:0'
        // ], [
        //     'institute.required'      => 'Institute field is required',
        //     'degree.required'         => 'Degree field is required',
        //     'field_of_study.required' => 'Field of study is required',
        //     'field_of_study.string'   => 'Field of study must be a string',
        //     'field_of_study.max'      => 'Field of study must not be greater than 255 characters',
        //     'reg_no.integer'          => 'Registration number should be a number',
        //     'reg_no.gt'               => 'Registration number should be a positive number',
        //     'roll_no.integer'         => 'Roll number should be a number',
        //     'roll_no.gt'              => 'Roll number should be a positive number',
        //     'start.required'          => 'Starting year field is required',
        //     'start.integer'           => 'Starting year should be a year',
        //     'start.digits'            => 'Starting year should be a year',
        //     'start.gt'                => 'Starting year should be a year',
        //     'start.max'               => 'Starting year can\'t be greater than current year',
        //     'end.integer'             => 'Ending year should be a year',
        //     'end.digits'              => 'Ending year should be a year',
        //     'end.after'               => 'Ending year should be greater than starting year',
        //     'end.gt'                  => 'Ending year should be a year',
        //     'end.max'                 => 'Ending year can\'t be greater than current year',
        //     'result.numeric'          => 'Result should be a number',
        //     'result.gte'              => 'Result can\'t be a negative number',
        //     'out_of.numeric'          => 'Out of should be a number',
        //     'out_of.gte'              => 'Out of can\'t be a negative number'
        // ]);


        // if ($validator->fails()) {
        //     return  $this->sendResponse(true, [],  $validator->messages());
        // }

        if (!$request->id) {
            $educationInfo = new EducationInfo();
            $educationInfo->user_id = auth()->id();
            $notification = 'Education information added successfully';
        } else {
            $educationInfo = EducationInfo::findOrFail($request->id);
            $notification = 'Education information updated successfully';
        }

        $educationInfo->degree = $request->degree;
        $educationInfo->field_of_study = $request->field_of_study;
        $educationInfo->institute = $request->institute;
        // $educationInfo->reg_no = $request->reg_no;
        // $educationInfo->roll_no = $request->roll_no;
        // $educationInfo->start = $request->start;
        // $educationInfo->end = $request->end;
        // $educationInfo->result = $request->result;
        // $educationInfo->out_of = $request->out_of;
        $educationInfo->save();

        return  $this->sendResponse(true, [], $notification);
    }

    public function deleteEducationInfo(Request $request)
    {
        $education = EducationInfo::findOrFail($request->id);
        $education->delete();

        $notify = 'Education information deleted successfully';
        return  $this->sendResponse(true, [], $notify);
    }

    public function updateCareerInfo(Request $request)
    {
        // $rules = [
        //     'company'       => 'required|string|max:255',
        //     'designation'   => 'required|string|max:40',
        //     'start'       => 'required|integer|digits:4|gt:0|lte:' . date('Y'),
        //     'end'         => 'nullable|integer|digits:4|after:start|lte:' . date('Y')
        // ];

        // $messages = [
        //     'company.required'     => 'Company name is required',
        //     'company.max'          => 'Company name must not be greater than 255 characters',
        //     'designation.required' => 'The designation field is required',
        //     'designation.max'      => 'Designation must not be greater than 40 characters',
        //     'start.required'       => 'Starting year field is required',
        //     'start.integer'        => 'Starting year should be a year',
        //     'start.gt'             => 'Starting year should be a year',
        //     'start.digits'         => 'Starting year should be a year',
        //     'start.lte'            => 'Starting year should be less than or equal to current year',
        //     'end.integer'          => 'Ending year should be a year',
        //     'end.gt'               => 'Ending year should be a year',
        //     'end.digits'           => 'Ending year should be a year',
        //     'end.lte'              => 'Ending year should be less than or equal to current year',
        //     'end.after'            => 'Ending year should be greater than starting year'
        // ];

        // $request->validate($rules, $messages);


        if (!$request->id) {
            $CareerInfo = new CareerInfo();
            $CareerInfo->user_id = auth()->id();
            $notification = 'Career information added successfully';
        } else {
            $careerInfo  = CareerInfo::findOrFail($request->id);
            $notification = 'Career information updated successfully';
        }

        $CareerInfo->position      = $request->position ?? '';
        $CareerInfo->state_posting      = $request->state_posting ?? '';
        $CareerInfo->district_posting      = $request->district_posting ?? '';
        $CareerInfo->from         = $request->from ?? '';
        $CareerInfo->end          = $request->end  ?? '';
        $careerInfo->save();

        return  $this->sendResponse(true, [], $notification);
    }

    public function deleteCareerInfo(Request $request)
    {
        $career = CareerInfo::findOrFail($request->id);
        $career->delete();
        $notification =  'Career information deleted successfully';
        return  $this->sendResponse(true, [], $notification);
    }



    protected function updateRegistrationStep($user, $index, $column)
    {
        $array = $user->$column;

        if (!in_array($index, $array)) {
            array_push($array, $index);
        }

        $user->$column = $array;
        if ($index == 6) {
            $user->profile_complete = 1;
        }
        $user->save();


        return  $this->sendResponse(true, $user, 'Profile Setting update successful');
    }

    public function changePassword()
    {
        $pageTitle = 'Change Password';
        return view($this->activeTemplate . 'user.password', compact('pageTitle'));
    }

    public function submitPassword(Request $request)
    {

    

        $passwordValidation = Password::min(6);
        $general = gs();
        if ($general->secure_password) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $this->validate($request, [
            'current_password' => 'required',
            'password' => ['required', 'confirmed', $passwordValidation]
        ]);

        $user = auth()->user();
        if (Hash::check($request->current_password, $user->password)) {
            $password = Hash::make($request->password);
            $user->password = $password;
            $user->save();
            $notify[] = ['success', 'Password changes successfully'];
            return back()->withNotify($notify);
        } else {
            $notify[] = ['error', 'The password doesn\'t match!'];
            return back()->withNotify($notify);
        }
    }

    public function updateProfileImage(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'image' => 'required',
        ]);

        if ($validator->fails()) {
            return  $this->sendResponse(true, [],  $validator->messages());
        }


        if ($request->hasFile('image')) {
            try {
                $fileName = fileUploader($request->image, getFilePath('userProfile'), getFileSize('userProfile'), $user->image);
                $user->image = $fileName;
                $user->save();
            } catch (\Exception $exp) {
                return  $this->sendResponse(true, $user, 'Couldn\'t upload the image');
            }
        }
        return  $this->sendResponse(true, $user, 'Profile picture updated successfully');
    }

    public function profileShow()
    {

        $user = auth()->user();

        return getImage(getFilePath('userProfile') . '/' . $user->image);
    }

    
}
