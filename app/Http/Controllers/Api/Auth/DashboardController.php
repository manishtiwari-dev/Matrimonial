<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\User;
use App\Models\Frontend;
use App\Models\Page;
use Illuminate\Http\Request;
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
use App\Models\ShortListedProfile;

class DashboardController extends BaseController
{


    public function dashboard_data()
    {
        $user = auth()->user();
      

        $userId = auth()->id();
        $user = User::with('basicInfo', 'partnerExpectation', 'physicalAttributes', 'family', 'careerInfo', 'educationInfo')->findOrFail($userId);

        $partnerExpectation = PartnerExpectation::get();
        $premium_match=[];
        if (!empty($partnerExpectation)) {
            foreach ($partnerExpectation as $partner) {
 
                if (($partner->marital_status ==  $user->marital_status) && ($partner->religion ==  $user->religion) && ($partner->country == $user->country_code) ){
                    $premium_match[] = $partner;
                }
               
            }
        }


        $data['user']            = $user;
        $data['premium_match'] = $premium_match;


        return  $this->sendResponse(true, $data, 'User Retrieved successfully .');
    }

    public function userData()
    {
        $user = auth()->user();


        $data['religions']       = ReligionInfo::get();
        $data['maritalStatuses'] = MaritalStatus::get();
        $data['community']       = Community::get();
        $data['motherTongue'] = MotherTongue::get();

        $data['countries']   = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $data['user'] = $user;

        return  $this->sendResponse(true, $data, 'Profile Data Retrieved successful .');
    }
}
