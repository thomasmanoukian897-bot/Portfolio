<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestPasswordChangeRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\VerifyPasswordChangeRequest;
use App\Mail\PasswordChangeVerificationCode;
use App\Models\PasswordChangeVerification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class ProfileController extends Controller
{
    public function edit(): View
    {
        $user = auth()->user();

        return view('profile.edit', [
            'user' => $user,
            'pendingPasswordChange' => PasswordChangeVerification::findActiveForUser($user) !== null,
            'activeTab' => request()->string('tab')->toString() === 'password' ? 'password' : 'account',
        ]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        unset($validated['avatar']);

        $user->update($validated);

        if ($request->hasFile('avatar')) {
            $user->update([
                'avatar_path' => $user->storeAvatar($request->file('avatar')),
            ]);
        }

        return redirect()
            ->route('profile.edit')
            ->with('status', 'Your profile has been updated successfully.');
    }

    public function requestPasswordChange(RequestPasswordChangeRequest $request): RedirectResponse
    {
        $user = $request->user();
        $result = PasswordChangeVerification::createForUser(
            $user,
            $request->string('password')->toString(),
        );

        Mail::to($user)->send(new PasswordChangeVerificationCode(
            user: $user,
            code: $result['plainCode'],
        ));

        return redirect()
            ->route('profile.edit', ['tab' => 'password'])
            ->with('password_status', 'A verification code has been sent to your email address.');
    }

    public function verifyPasswordChange(VerifyPasswordChangeRequest $request): RedirectResponse
    {
        $user = $request->user();
        $verification = PasswordChangeVerification::findActiveForUser($user);

        if ($verification === null) {
            return redirect()
                ->route('profile.edit', ['tab' => 'password'])
                ->withErrors(['code' => 'No active password change request found. Please request a new code.']);
        }

        if (! $verification->matchesCode($request->string('code')->toString())) {
            return redirect()
                ->route('profile.edit', ['tab' => 'password'])
                ->withErrors(['code' => 'The verification code is invalid.']);
        }

        $user->update([
            'password' => $verification->pendingPassword(),
        ]);

        $verification->delete();

        return redirect()
            ->route('profile.edit', ['tab' => 'password'])
            ->with('password_status', 'Your password has been changed successfully.');
    }
}
