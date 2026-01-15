<?php

return [
    'roles' => [
        'admin'   => 'Administrator',
        'courier' => 'Kuryer',
        'client'  => 'Mijoz',
    ],
    'statuses' => [
        'active'   => 'Faol',
        'inactive' => 'Nofaol',
        'blocked'  => 'Bloklangan',
    ],
    'auth' => [
        'code_sent'     => 'Tasdiqlash kodi yuborildi',
        'code_verified' => 'Tasdiqlash muvaffaqiyatli bajarildi',
        'code_invalid'  => 'Tasdiqlash kodi noto‘g‘ri',
        'code_expired'  => 'Tasdiqlash kodi eskirgan yoki topilmadi',
        'email_subject' => 'Tasdiqlash kodi',
        'email_text'    => 'Tasdiqlash kodi: :code',
        'sms_text'      => 'Tasdiqlash kodi: :code',
        'user_blocked' => "Sizning hisobingiz bloklangan. Qo'llab-quvvatlash xizmatiga murojaat qiling.",
        'login_success' => "Tizimga muvaffaqiyatli kirdingiz.",
        'logout' => "Tizimdan muvaffaqiyatli chiqdingiz."
    ],
    'errors' => [
        'phone_invalid' => "Telefon raqam noto'g'ri formatda",
        'email_invalid' => "Email noto'g'ri formatda",
        'too_many_attempts' => "Juda ko'p urinish. Iltimos :minutes daqiqadan keyin qayta urinib ko'ring.",
        'value_rate_limit' => "Bu raqam/email uchun juda ko'p so'rov. :minutes daqiqadan keyin urinib ko'ring.",
        'ip_rate_limit' => "Juda ko'p so'rov yuborildi. :minutes daqiqadan keyin urinib ko'ring.",
        'suspicious_activity_blocked' => "Shubhali faollik aniqlandi. Hisobingiz vaqtincha bloklandi.",
        'ip_blocked' => "Sizning IP manzilingiz bloklangan. Qo'llab-quvvatlash xizmatiga murojaat qiling.",
        'value_verify_blocked' => "Bu raqam/email uchun juda ko'p noto'g'ri kod kiritildi. 30 daqiqadan keyin urinib ko'ring.",
        'value_blocked' => "Bu raqam/email vaqtincha bloklangan.",
        'ip_verify_limit' => "Juda ko'p tekshirish urinishi. :minutes daqiqadan keyin qayta urinib ko'ring."
    ],
    'settings' => [
        'edited_language' => "Til muvaffaqiyatli o‘zgartirildi",
    ]
];