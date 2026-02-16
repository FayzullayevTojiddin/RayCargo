<?php

return [
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