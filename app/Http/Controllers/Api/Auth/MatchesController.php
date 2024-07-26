<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\BaseController as BaseController;
use App\Models\User;
use App\Models\MaritalStatus;
use App\Models\ReligionInfo;
use App\Models\PhysicalAttribute;
use Illuminate\Http\Request;
use App\Models\ShortListedProfile;
use App\Models\UserInterest;


class MatchesController extends BaseController
{



    public function matches()
    {
        $user  = auth()->user();
        $userData = $this->userData();
        $members = $userData['members'];
        $members->map(function ($shortlistsData)  use ($user) {
            $bookmarkUser = ShortListedProfile::where('user_id',$user->id)->where('profile_id', $shortlistsData->profile_id)->first();
            if (!empty($bookmarkUser))
                $shortlistsData->bookmark = 1;
            else
                $shortlistsData->bookmark = 0;

            return $shortlistsData;
        });

        $members->map(function ($interestData) use ($user) {
            $interestUser =  UserInterest::where('interesting_id', $interestData->id)->first();
            if (!empty($interestUser))
                $interestData->interestStatus = $interestUser->status;
            else
                $interestData->interestStatus = 0;
            return $interestData;
        });


        if (request()->ajax()) {
            return response()->json([
                'html' => view($this->activeTemplate . 'partials.members', compact('members', 'user'))->render()
            ]);
        }

        $maritalStatuses = MaritalStatus::all();
        $religions       = ReligionInfo::get();
        $countryData     = (array)json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countries       = array_column($countryData, 'country');

        $height['max'] = $userData['maxHeight'];
        $height['min'] = $userData['minHeight'];

        if ($height['min'] == $height['max']) {
            $height['min'] = 0;
        }


        $data['members']     = $members;
        $data['maritalStatuses'] = $maritalStatuses;
        $data['religions']     = $religions;
        $data['countries']   = $countries;
        $data['height'] = $height;

        $data['pageTitle']  = 'Searched Members';
        $data['user']  = auth()->user();

        return  $this->sendResponse(true, $data, 'Matches Retrieved successfully .');
    }



    protected function userData()
    {
        $request = request();
        $userId    = auth()->id();
    
        $query = User::with('basicInfo', 'religion','mother_tongue','community','profession','bloodGroups','maritialStatus','careerInfo.positionHeld','partnerExpectation','partnerExpectation.maritialStatus','partnerExpectation.profession','partnerExpectation.religion','partnerExpectation.mother_tongue','partnerExpectation.community','partnerExpectation.positionHeld','physicalAttributes', 'family', 'careerInfo','careerInfo.positionHeld', 'educationInfo','basicInfo.religion','basicInfo.mother_tongue','basicInfo.community','basicInfo.profession','basicInfo.maritialStatus','basicInfo.drinking','basicInfo.smoking')->active();
  

        $maxHeight = round(PhysicalAttribute::max('height')) ?? 0;
        $minHeight = round(PhysicalAttribute::min('height')) ?? 0;
        if ($userId) {
            $query = $query->whereDoesNtHave('ignoredProfile', function ($q) use ($userId) {
                $q->where('ignored_id', $userId);
            })->whereDoesNtHave('ignoredBy', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->where('id', '!=', $userId);
        }

        if ($request->member_id) {
            $query = $query->where('profile_id', $request->member_id);
        }

        if ($request->looking_for) {
            $query = $query->where('looking_for', $request->looking_for);
        }

        if ($request->min_height && $request->max_height) {
            $min = $request->min_height;
            $max = $request->max_height;
            $query = $query->whereHas('physicalAttributes', function ($q) use ($min, $max) {
                $q->whereBetween('height', [$min, $max]);
            });
        }



        if ($request->marital_status) {
            $query = $query->whereHas('basicInfo', function ($q) use ($request) {
                $q->where('marital_status', $request->marital_status);
            });
        }

        if ($request->max_weight) {
            $query = $query->whereHas('physicalAttributes', function ($q) use ($request) {
                $q->where('weight','<=', $request->max_weight);
            });
        }

        if ($request->state) {
            $query = $query->whereHas('basicInfo', function ($q) use ($request) {
                $q->where('present_address->state', $request->state);
            });
        }

        if ($request->religion) {
            $query = $query->whereHas('basicInfo', function ($q) use ($request) {
                $q->where('religion', $request->religion);
            });
        }

        if ($request->country) {
            $query = $query->whereHas('basicInfo', function ($q) use ($request) {
                $q->where('present_address->country', $request->country);
            });
        }

        if ($request->profession) {
            $query = $query->whereHas('basicInfo', function ($q) use ($request) {
                $q->where('profession', 'like', "%$request->profession%");
            });
        }

        if ($request->city) {
            $query = $query->whereHas('basicInfo', function ($q) use ($request) {
                $q->where('present_address->city', 'like', "%$request->city%");
            });
        }

        if ($request->smoking_status) {
            $query = $query->whereHas('basicInfo', function ($q) use ($request) {
                $q->where('smoking_status', $request->smoking_status);
            });
        }

        if ($request->drinking_status) {
            $query = $query->whereHas('basicInfo', function ($q) use ($request) {
                $q->where('drinking_status', $request->drinking_status);
            });
        }

        if ($request->mother_tongue) {
            $query = $query->whereHas('basicInfo', function ($q) use ($request) {
                $q->where('mother_tongue', $request->mother_tongue);
            });
        }
        if ($request->country) {
            $query = $query->whereHas('basicInfo', function ($q) use ($request) {
                $q->where('present_address->country', 'like', "%$request->country%");
            });
        }
        if ($request->gender) {
            $query = $query->whereHas('basicInfo', function ($q) use ($request) {
                $q->where('gender', $request->gender);
            });
        }
        if ($request->community) {
            $query = $query->whereHas('basicInfo', function ($q) use ($request) {
                $q->where('community', $request->community);
            });
        }

     


    
        if ($request->batch) {
            $query = $query->whereHas('basicInfo', function ($q) use ($request) {
                $q->where('batch', $request->batch);
            });
        }



        
   
              
        if ($request->cadar) {
            $query = $query->whereHas('basicInfo', function ($q) use ($request) {
                $q->where('cadar', $request->cadar);
            });
        }


        if ($request->position) {
            $query = $query->whereHas('careerInfo', function ($q) use ($request) {
                $q->where('position', $request->position);
            });
        }


        if ($request->state_posting) {
            $query = $query->whereHas('careerInfo', function ($q) use ($request) {
                $q->where('state_posting', $request->state_posting);
            });
        }

        if ($request->district_posting) {
            $query = $query->whereHas('careerInfo', function ($q) use ($request) {
                $q->where('district_posting', $request->district_posting);
            });
        }



        if ($request->from) {
            $query = $query->whereHas('careerInfo', function ($q) use ($request) {
                $q->where('from', $request->from);
            });
        }




        if ($request->end) {
            $query = $query->whereHas('careerInfo', function ($q) use ($request) {
                $q->where('end', $request->end);
            });
        }





        if ($request->user_type) {
            $query = $query->whereHas('basicInfo', function ($q) use ($request) {
                $q->where('user_type', $request->user_type);
            });
        }

        //partner matches filter
        if ($request->preference_religion) {
            $query = $query->whereHas('partnerExpectation', function ($q) use ($request) {
                $q->where('religion', $request->preference_religion);
            });
        }
        if ($request->preference_community) {
            $query = $query->whereHas('partnerExpectation', function ($q) use ($request) {
                $q->where('community', $request->preference_community);
            });
        }
        if ($request->preference_marital_status) {
            $query = $query->whereHas('partnerExpectation', function ($q) use ($request) {
                $q->where('marital_status', $request->preference_marital_status);
            });
        }

        if ($request->preference_mother_tongue) {
            $query = $query->whereHas('partnerExpectation', function ($q) use ($request) {
                $q->where('mother_tongue', $request->preference_mother_tongue);
            });
        }

        if ($request->preference_profession) {
            $query = $query->whereHas('partnerExpectation', function ($q) use ($request) {
                $q->where('profession', $request->preference_profession);
            });
        }

        if ($request->preference_smoking_status) {
            $query = $query->whereHas('partnerExpectation', function ($q) use ($request) {
                $q->where('smoking_status', $request->preference_smoking_status);
            });
        }
        if ($request->preference_country) {
            $query = $query->whereHas('partnerExpectation', function ($q) use ($request) {
                $q->where('country', $request->preference_country);
            });
        }

     

        // if ($request->min_age) {
        //     $query = $query->whereHas('partnerExpectation', function ($q) use ($request) {
        //         $q->where('min_age', $request->min_age);
        //     });
        // }

        // if ($request->max_age) {
        //     $query = $query->whereHas('partnerExpectation', function ($q) use ($request) {
        //         $q->where('max_age', $request->max_age);
        //     });
        // }

        if ($request->min_age && $request->max_age) {
            $query = $query->whereHas('partnerExpectation', function ($q) use ($request) {
                $q->where('min_age', '<=', $request->max_age)
                    ->where('max_age', '>=', $request->min_age);
            });
        }

        // if ($request->min_height && $request->max_height) {
        //     $query = $query->whereHas('partnerExpectation', function ($q) use ($request) {
        //         $q->where('min_height', '<=', $request->max_height)
        //             ->where('max_height', '>=', $request->min_height);
        //     });
        // }



        $members = $query->with('physicalAttributes', 'limitation.package', 'basicInfo', 'interests')->orderBy('id', 'desc')->paginate(getPaginate(8));
        return ['members' => $members, 'minHeight' => $minHeight, 'maxHeight' => $maxHeight];
    }


    public function profile(Request $request)
    {
        $id = $request->id;
   


        $matches = User::with(['basicInfo', 'religion','mother_tongue','community','profession','bloodGroups','maritialStatus','careerInfo.positionHeld','partnerExpectation','partnerExpectation.maritialStatus','partnerExpectation.profession','partnerExpectation.religion','partnerExpectation.mother_tongue','partnerExpectation.community','partnerExpectation.positionHeld','physicalAttributes', 'family', 'careerInfo','careerInfo.positionHeld','basicInfo.religion','basicInfo.mother_tongue','basicInfo.community','basicInfo.profession','basicInfo.maritialStatus','partnerExpectation.smoking','partnerExpectation.drinking','basicInfo.drinking','basicInfo.smoking','educationInfo'=>function ($query) {
            $query->orderBy('start', 'desc');
        }])->findOrFail($id);

    

        $maxLimit = $matches->limitation->image_upload_limit ?? '';
        $matches->load(['galleries' => function ($image) use ($maxLimit) {
            if ($maxLimit > 0) $image->latest('id')->limit($maxLimit);
        }]);

        $user_id = auth()->user()->id;
        $data['pageTitle']  = 'Matches\'s Profile';
        $data['user']  = User::with('basicInfo', 'religion','mother_tongue','community','profession','bloodGroups','maritialStatus','careerInfo.positionHeld','partnerExpectation','partnerExpectation.maritialStatus','partnerExpectation.profession','partnerExpectation.religion','partnerExpectation.mother_tongue','partnerExpectation.community','partnerExpectation.drinking','partnerExpectation.smoking', 'physicalAttributes', 'family', 'careerInfo','careerInfo.positionHeld', 'educationInfo','basicInfo.religion','basicInfo.mother_tongue','basicInfo.community','basicInfo.profession','basicInfo.drinking','basicInfo.smoking','basicInfo.maritialStatus','partnerExpectation.positionHeld','partnerExpectation.smoking','partnerExpectation.drinking',)->where('id', $user_id)->first();
        $data['matches']  = $matches;

        return  $this->sendResponse(true, $data, 'Matches Profile Retrieved successfully .');
    }


    public function bookmarkSave(Request $request)
    {
        $id = $request->id;
        $infoData = User::find($id);
        $infoData->bookmark = ($infoData->bookmark == 0) ? 1 : 0;
        $infoData->save();

        return  $this->sendResponse(true, $infoData, 'Bookmark 
        Save  successfully .');
    }


    public function bookmarkSaveMatches(Request $request)
    {
        // $id = $request->id;
        // //$userId = auth()->id();

        // $bookmarkInfo = User::with(['basicInfo', 'physicalAttributes', 'family', 'partnerExpectation', 'careerInfo', 'limitation', 'educationInfo' => function ($query) {
        //     $query->orderBy('start', 'desc');
        // }])->where('bookmark', 1)->findOrFail($id);

        // if (!empty($bookmarkInfo))
        //     return  $this->sendResponse(true, $bookmarkInfo, 'Bookmark 
        // Save Matches Retrieved successfully .');
        // else
        //     return  $this->sendResponse(true, [], 'Bookmark .');







        $pageTitle  = 'Shortlisted Profiles';
        $user       = auth()->user();
        $shortlists = ShortListedProfile::where('user_id', $user->id)->searchable(['profile:username,firstname,lastname'])->with('profile.basicInfo', 'profile.interests')->latest()->paginate(getPaginate());


        $data['pageTitle']  = $pageTitle;
        $data['shortlists']  = $shortlists;
        $data['user']  = $user;


        return  $this->sendResponse(true, $data, 'Bookmark 
         Save Matches Retrieved successfully .');
    }

    public function deleteUserAccount(Request $request)
    {

        //  $userId = auth()->id();
        $userId = $request->id;

        // $user = User::with('basicInfo', 'partnerExpectation', 'physicalAttributes', 'family', 'careerInfo', 'educationInfo')->findOrFail($userId);

        //     $user->basicInfo()->delete(); // relation data delete
        //     $user->partnerExpectation()->delete(); 
        //     $user->physicalAttributes()->delete();
        //     $user->family()->delete();
        //     $user->careerInfo()->delete();
        //     $user->educationInfo()->delete();
        //     $user->delete();

        $infoData = User::find($userId);
        $infoData->status = ($infoData->status == 0) ? 1 : 0;
        $infoData->save();

        return  $this->sendResponse(true, $infoData, 'Account  deleted successfully');
    }



    public function bookmarkStatus(Request $request)
    {
        $id = $request->profile_id;

        $bookmarkUser = ShortListedProfile::where('profile_id', $id)->first();
        if (!empty($bookmarkUser))
            $bookmark = 1;
        else
            $bookmark = 0;

        $data['bookmarkUser']     = $bookmarkUser;
        $data['bookmark']     = $bookmark;
        return  $this->sendResponse(true, $data, 'Bookmark 
        Retrieved  successfully .');
    }



    public function interestStatus(Request $request)
    {
        $id = $request->interesting_id;

        $interestUser =  UserInterest::where('interesting_id', $id)->first();
        if (!empty($interestUser))
            $status = $interestUser->status;
        else
            $status = '';

        $data['interestUser']     = $interestUser;
        $data['status']     = $status;
        return  $this->sendResponse(true, $data, 'Interest 
        Retrieved  successfully .');
    }
}
