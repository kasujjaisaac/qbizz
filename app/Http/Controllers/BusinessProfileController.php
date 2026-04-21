<?php

namespace App\Http\Controllers;

use App\Http\Requests\BusinessProfileRequest;
use App\Models\BusinessProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class BusinessProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $businessProfile = $user->businessProfile ?? new BusinessProfile([
            'contact_email' => $user->email,
        ]);

        return view('business-profile.edit', [
            'businessProfile' => $businessProfile,
            'completionPercentage' => $businessProfile->completionPercentage(),
            'missingFields' => $businessProfile->missingFields(),
            'requiredFieldLabels' => BusinessProfile::requiredFieldLabels(),
            'teamMembers' => $businessProfile->exists
                ? $businessProfile->members()->orderBy('name')->get()
                : collect([$user]),
        ]);
    }

    public function update(BusinessProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();
        $signatureData = $data['signature_data'] ?? null;
        $clearSignature = (bool) ($data['clear_signature'] ?? false);
        $regenerateTeamInviteCode = (bool) ($data['regenerate_team_invite_code'] ?? false);
        unset($data['signature_data'], $data['clear_signature'], $data['regenerate_team_invite_code']);

        $businessProfile = DB::transaction(function () use ($request, $user, $data, $signatureData, $clearSignature, $regenerateTeamInviteCode): BusinessProfile {
            $businessProfile = $user->businessProfile ?? new BusinessProfile([
                'contact_email' => $user->email,
                'team_invite_code' => BusinessProfile::generateUniqueTeamInviteCode(),
            ]);

            if ($request->hasFile('logo')) {
                if ($businessProfile->logo_path) {
                    Storage::disk('public')->delete($businessProfile->logo_path);
                }

                $data['logo_path'] = $request->file('logo')->store('business-logos', 'public');
            }

            if ($clearSignature && $businessProfile->signature_path) {
                Storage::disk('public')->delete($businessProfile->signature_path);
                $data['signature_path'] = null;
            }

            if (filled($signatureData)) {
                if ($businessProfile->signature_path) {
                    Storage::disk('public')->delete($businessProfile->signature_path);
                }

                $data['signature_path'] = $this->storeSignature($signatureData);
            }

            if ($regenerateTeamInviteCode || blank($businessProfile->team_invite_code)) {
                $data['team_invite_code'] = BusinessProfile::generateUniqueTeamInviteCode();
            }

            $businessProfile->fill($data);

            if (! $businessProfile->exists) {
                $businessProfile->user()->associate($user);
            }

            $businessProfile->setup_completed_at = $businessProfile->completionPercentage() === 100 ? now() : null;
            $businessProfile->save();

            if ($user->business_profile_id !== $businessProfile->id) {
                $user->forceFill([
                    'business_profile_id' => $businessProfile->id,
                ])->save();
            }

            return $businessProfile;
        });

        return redirect()
            ->route($businessProfile->isComplete() ? 'dashboard' : 'business-profile.edit')
            ->with('status', $businessProfile->isComplete()
                ? 'Business profile saved. Your dashboard is ready.'
                : 'Business profile saved. Complete the remaining details to unlock the dashboard and invoices.');
    }

    protected function storeSignature(string $signatureData): string
    {
        if (! preg_match('/^data:image\/png;base64,(.+)$/', $signatureData, $matches)) {
            abort(422, 'The signature format is invalid.');
        }

        $binaryData = base64_decode($matches[1], true);

        if ($binaryData === false) {
            abort(422, 'The signature could not be processed.');
        }

        $path = 'business-signatures/'.uniqid('signature_', true).'.png';
        Storage::disk('public')->put($path, $binaryData);

        return $path;
    }
}
