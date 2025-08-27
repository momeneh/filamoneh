<?php

   return [
       'fields' => [
           'mobile' => 'شماره موبایل',
           'name' => 'نام',
           'email' => 'ایمیل',
           'Profile' => 'پروفایل',
           'password' => 'رمز عبور',    
           'password_confirmation' => 'تایید رمز عبور',
           'current_password' => 'رمز عبور فعلی',
           'created_at'=>'زمان ایجاد ',
           'updated_at'=>'زمان آخرین ویرایش ',
           'email_verified_at'=>'زمان فعال سازی',
           'roles'=>'نقش ها',
           'usersInfo'=>'اطلاعات کاربری',
           'title' => 'عنوان',
           'view'=>'مشاهده',
           'update'=>'ویرایش',
           'viewAny'=>'مشاهده همه ',
           'create'=>'ایجاد',
           'delete'=>'حذف',
           'restore'=>'بازگرداندن',
           'id'=>'شناسه',
           'family' => 'نام خانوادگی',
           'national_code' => 'شناسه ملی',
           'shenasname' => 'شماره شناسنامه',
           'passport_number' => 'شناسه پاسپورت',
           'father_name' => 'نام پدر',
           'birth_year' => 'سال تولد',
           'photo' => 'تصویر پروفایل',
           'website' => 'وبسایت',
           'tel' => 'تلفن',
           'fax' => 'فکس',
           'postalcode' => 'کد پستی',
           'addr' => 'آدرس',
           'country' => 'کشور',
           'province' => 'استان',
           'city' => 'شهر',
           'gender' => 'جنسیت',
           'woman' => 'زن',
           'man' => 'مرد',
           'start_year' => 'سال شروع',
           'end_year' => 'سال پایان'
           // ...other fields
       ],
       'tables'=>[
        'role_user' => 'نقش کاربران',
        'users' => 'کاربران',
        'roles' => 'نقش ها',
       ],
       'messages'=>[
            "export_completed"=> " اکسپورت مربوط به سرویس :model آماده شده است  . تعداد ردیف قابل دریافت :‌",
       ]
       // You can add other keys for menus, actions, etc.
   ];