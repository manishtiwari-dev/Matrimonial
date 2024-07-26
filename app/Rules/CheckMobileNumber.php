<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User;

class CheckMobileNumber implements Rule
{
 

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return User::where('mobile', $value)->count() < 2;
    }

    public function message()
    {
        return 'The mobile number has already been used more than twice.';
    }



    
}
