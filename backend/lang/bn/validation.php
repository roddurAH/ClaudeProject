<?php

/*
 * Bangla messages for the validation rules the mobile app can trigger.
 * Rules not listed here fall back to English (config app.fallback_locale).
 */

return [
    'required' => ':attribute দিতে হবে।',
    'string' => ':attribute লেখা হতে হবে।',
    'integer' => ':attribute একটি সংখ্যা হতে হবে।',
    'numeric' => ':attribute একটি সংখ্যা হতে হবে।',
    'regex' => ':attribute সঠিক নয়।',
    'unique' => 'এই :attribute আগেই ব্যবহার করা হয়েছে।',
    'exists' => 'নির্বাচিত :attribute সঠিক নয়।',
    'in' => 'নির্বাচিত :attribute সঠিক নয়।',
    'enum' => 'নির্বাচিত :attribute সঠিক নয়।',
    'image' => ':attribute অবশ্যই একটি ছবি হতে হবে।',
    'mimes' => ':attribute অবশ্যই :values ধরনের ফাইল হতে হবে।',
    'min' => [
        'string' => ':attribute কমপক্ষে :min অক্ষরের হতে হবে।',
        'numeric' => ':attribute কমপক্ষে :min হতে হবে।',
    ],
    'max' => [
        'string' => ':attribute :max অক্ষরের বেশি হতে পারবে না।',
        'file' => ':attribute :max কিলোবাইটের বেশি হতে পারবে না।',
        'numeric' => ':attribute :max এর বেশি হতে পারবে না।',
    ],
    'required_with' => ':values দিলে :attribute-ও দিতে হবে।',
    'accepted' => ':attribute-এ টিক দিতে হবে।',

    'attributes' => [
        'name' => 'নাম',
        'phone' => 'মোবাইল নম্বর',
        'pin' => 'পিন',
        'current_pin' => 'বর্তমান পিন',
        'body' => 'প্রশ্ন',
        'image' => 'ছবি',
        'crop_id' => 'ফসল',
        'district_id' => 'জেলা',
        'confirm' => 'নিশ্চিতকরণের ঘর',
    ],
];
