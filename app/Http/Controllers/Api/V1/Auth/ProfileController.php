<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1\Auth;

use App\Data\Auth\UpdateProfileData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\UpdateProfileRequest;
use App\Http\Requests\Api\V1\Auth\UserAvatarRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Services\Auth\UserProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ProfileController extends Controller
{
    public function show(Request $request, UserProfileService $userProfileService): UserResource
    {
        return UserResource::make($userProfileService->show($request->user()));
    }

    public function update(UpdateProfileRequest $request, UserProfileService $userProfileService): UserResource
    {
        return UserResource::make(
            $userProfileService->update($request->user(), UpdateProfileData::from($request->validated()))
        );
    }

    public function avatar(UserAvatarRequest $request, UserProfileService $userProfileService): UserResource
    {
        return UserResource::make(
            $userProfileService->avatar($request->user(), $request->file('avatar'))
        );
    }
}
