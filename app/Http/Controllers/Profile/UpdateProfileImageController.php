<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\JsonResponse;

class UpdateProfileImageController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $user = $request->user();

        // Eski rasmni o'chirish
        if ($user->image && Storage::disk('public')->exists($user->image)) {
            Storage::disk('public')->delete($user->image);
        }

        $path = $request->file('image')->store('users', 'public');

        $user->update(['image' => $path]);

        return response()->json([
            'success' => true,
            'message' => __('messages.profile.image_updated'),
            'data' => [
                'image' => $path,
                'image_url' => Storage::disk('public')->url($path),
            ]
        ]);
    }
}
