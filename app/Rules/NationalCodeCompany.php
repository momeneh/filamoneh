<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class NationalCodeCompany implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (preg_match("/^[0-9]{11}$/", $value)) {/*چک 11 رقمی بودن و عدد بودن شناسه ملی شخص حقوقی*/
			$c = substr($value, 10, 1); //رقم کنترل
			$d = ((int)substr($value, 9, 1)) + 2; //محاسبه رقم دهگان +2
			$z = array(29, 27, 23, 19, 17); //ارایه ضرایب الگوریتم
			$s = 0;
			for ($i = 0; $i < 10; $i++) {
				// هر رقم به جز رقم کنترل را با دهگان +2 جمع و در ضرایب ضرب می کنیم.و حاصل را جمع می کنیم
				$s += ($d + (int)substr($value, $i, 1)) * $z[$i % 5];
			}
			$s = $s % 11; //مجموع بدست آمده را بر 11 تقسیم می کنیم
			if ($s == 10) { //اگر باقیمانده 10 باشد -باقیمانده را برابر صفر می کنیم
				$s = 0;
			}

			//اگر رقم کنترل با باقیمانده یکسان باشد شناسه درست است
			if ($c == $s) {
				
				return true;
			}
		}
		return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return trans('validation.national_code');
    }
}
