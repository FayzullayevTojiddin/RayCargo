<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApplicationStatusController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->courierProfile;

        if (!$profile) {
            return response()->json([
                'success' => false,
                'message' => 'Siz hali ariza yubormagan ekansiz.',
                'data' => [
                    'has_profile' => false,
                    'application_status' => null,
                ],
            ]);
        }

        $data = [
            'has_profile' => true,
            'application_status' => $profile->application_status,
            'is_pedestrian' => $profile->is_pedestrian,
        ];

        if ($profile->application_status === 'rejected') {
            $data['rejection_reason'] = $profile->rejection_reason;
            $data['message'] = 'Arizangiz rad etildi. Iltimos, ma\'lumotlarni to\'g\'rilab qayta yuboring.';
        } elseif ($profile->application_status === 'approved') {
            $data['message'] = 'Arizangiz tasdiqlandi. Ishni boshlashingiz mumkin!';
        } else {
            $data['message'] = 'Arizangiz ko\'rib chiqilmoqda. Iltimos, kuting.';
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }
}
