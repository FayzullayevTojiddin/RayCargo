<?php

namespace App\Http\Controllers\Courier;

use App\Http\Controllers\Controller;
use App\Models\CourierProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\JsonResponse;

class SubmitProfileController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->courierProfile && $user->courierProfile->application_status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Sizning arizangiz allaqachon tasdiqlangan.',
            ], 400);
        }

        if ($user->courierProfile && $user->courierProfile->application_status === 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Sizning arizangiz allaqachon yuborilgan. Iltimos, natijani kuting.',
            ], 400);
        }

        $request->validate([
            'is_pedestrian' => ['required', 'boolean'],
            'vehicle_brand' => ['required_if:is_pedestrian,false', 'nullable', 'string', 'max:255'],
            'vehicle_model' => ['required_if:is_pedestrian,false', 'nullable', 'string', 'max:255'],
            'vehicle_color' => ['required_if:is_pedestrian,false', 'nullable', 'string', 'max:255'],
            'vehicle_number' => ['required_if:is_pedestrian,false', 'nullable', 'string', 'max:20'],
            'vehicle_photo_front' => ['required_if:is_pedestrian,false', 'nullable', 'image', 'max:5120'],
            'vehicle_photo_back' => ['required_if:is_pedestrian,false', 'nullable', 'image', 'max:5120'],
            'vehicle_photo_left' => ['required_if:is_pedestrian,false', 'nullable', 'image', 'max:5120'],
            'vehicle_photo_right' => ['required_if:is_pedestrian,false', 'nullable', 'image', 'max:5120'],
        ]);

        $profile = DB::transaction(function () use ($request, $user) {
            $isPedestrian = (bool) $request->is_pedestrian;

            $profile = CourierProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'is_pedestrian' => $isPedestrian,
                    'vehicle_brand' => $isPedestrian ? null : $request->vehicle_brand,
                    'vehicle_model' => $isPedestrian ? null : $request->vehicle_model,
                    'vehicle_color' => $isPedestrian ? null : $request->vehicle_color,
                    'vehicle_number' => $isPedestrian ? null : $request->vehicle_number,
                    'application_status' => 'pending',
                    'rejection_reason' => null,
                ]
            );

            if (!$isPedestrian) {
                // Eski rasmlarni o'chirib yangilarini saqlash
                foreach ($profile->vehiclePhotos as $doc) {
                    Storage::disk('public')->delete($doc->file_path);
                    $doc->delete();
                }

                $photoTypes = ['vehicle_photo_front', 'vehicle_photo_back', 'vehicle_photo_left', 'vehicle_photo_right'];
                $docTypes = ['vehicle_front', 'vehicle_back', 'vehicle_left', 'vehicle_right'];

                foreach ($photoTypes as $i => $field) {
                    if ($request->hasFile($field)) {
                        $path = $request->file($field)->store("courier_documents/{$profile->id}", 'public');
                        $profile->documents()->create([
                            'type' => $docTypes[$i],
                            'file_path' => $path,
                            'status' => 'pending',
                        ]);
                    }
                }
            }

            return $profile;
        });

        return response()->json([
            'success' => true,
            'message' => 'Arizangiz muvaffaqiyatli yuborildi. Admin tomonidan ko\'rib chiqiladi.',
            'data' => [
                'application_status' => $profile->application_status,
            ],
        ]);
    }
}
