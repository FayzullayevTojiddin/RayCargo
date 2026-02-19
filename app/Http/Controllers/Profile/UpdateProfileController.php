<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use Symfony\Component\HttpFoundation\JsonResponse;

class UpdateProfileController extends Controller
{
    public function __invoke(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();

        $user->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => __('messages.profile.updated'),
            'data' => [
                'user' => $user->fresh(),
            ]
        ]);
    }
}
