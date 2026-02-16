<?php

return [
    'auth' => [
        'user_blocked' => "Sizning hisobingiz bloklangan. Qo'llab-quvvatlash xizmatiga murojaat qiling.",
        'login_success' => "Tizimga muvaffaqiyatli kirdingiz.",
        'failed' => "Email yoki parol noto'g'ri",
        'verification_code_sent' => "Tasdiqlash kodi emailga yuborildi",
        'verification_code_resent' => "Tasdiqlash kodi qayta yuborildi",
        'verification_code_invalid' => "Tasdiqlash kodi noto'g'ri",
        'verification_expired' => "Tasdiqlash kodi eskirgan",
        'register_success' => "Ro'yxatdan muvaffaqiyatli o'tdingiz",
    ],
    'errors' => [
        'too_many_attempts' => "Juda ko'p urinish. Iltimos :minutes daqiqadan keyin qayta urinib ko'ring.",
    ],
    'roles' => [
        'admin'   => 'Administrator',
        'courier' => 'Kuryer',
        'client'  => 'Mijoz',
        'worker' => 'Xodim'
    ],
    'statuses' => [
        'active'   => 'Faol',
        'inactive' => 'Nofaol',
        'blocked'  => 'Bloklangan',
    ],
    'order_statuses' => [
        'created'     => 'Yaratildi',
        'accepted'    => 'Qabul qilindi',
        'in_progress' => 'Jarayonda',
        'completed'   => 'Yakunlandi',
        'cancelled'   => 'Bekor qilindi',
    ],
    'order_stop_types' => [
        'pickup'  => 'Yuk olish',
        'dropoff' => 'Yuk topshirish',
        'return'  => 'Qaytish',
    ],
    'order_price_item_types' => [
        'base'     => 'Asosiy narx',
        'delivery' => 'Yetkazib berish',
        'service'  => 'Xizmat haqi',
        'discount' => 'Chegirma',
        'bonus'    => 'Bonus',
        'tax'      => 'Soliq',
    ],
];