<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Laravel\Fortify\Actions\EnableTwoFactorAuthentication;
use Laravel\Fortify\Actions\DisableTwoFactorAuthentication;
use Laravel\Fortify\Contracts\TwoFactorAuthenticationProvider;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthController extends Controller
{
    /**
     * Display the two-factor authentication setup page.
     */
    public function show(Request $request)
    {
        $user = $request->user();
        
        return Inertia::render('Profile/TwoFactorAuth', [
            'twoFactorEnabled' => !is_null($user->two_factor_secret),
            'recoveryCodes' => $user->two_factor_recovery_codes ? json_decode(decrypt($user->two_factor_recovery_codes), true) : [],
            'qrCodeSvg' => $user->two_factor_secret ? $this->generateQrCode($user) : null,
        ]);
    }

    /**
     * Enable two-factor authentication.
     */
    public function enable(Request $request, EnableTwoFactorAuthentication $enable)
    {
        $request->validate([
            'password' => 'required|string|current_password',
        ]);

        $enable($request->user());

        return back()->with('success', 'Two-factor authentication has been enabled.');
    }

    /**
     * Confirm two-factor authentication setup.
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->user();
        
        if (!$user->two_factor_secret) {
            return back()->withErrors(['code' => 'Two-factor authentication has not been enabled.']);
        }

        $google2fa = app(TwoFactorAuthenticationProvider::class);
        
        if (!$google2fa->verify(decrypt($user->two_factor_secret), $request->code)) {
            return back()->withErrors(['code' => 'The provided two factor authentication code was invalid.']);
        }

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
        ])->save();

        return back()->with('success', 'Two-factor authentication has been confirmed and is now active.');
    }

    /**
     * Disable two-factor authentication.
     */
    public function disable(Request $request, DisableTwoFactorAuthentication $disable)
    {
        $request->validate([
            'password' => 'required|string|current_password',
        ]);

        $disable($request->user());

        return back()->with('success', 'Two-factor authentication has been disabled.');
    }

    /**
     * Generate new recovery codes.
     */
    public function generateRecoveryCodes(Request $request)
    {
        $request->validate([
            'password' => 'required|string|current_password',
        ]);

        $user = $request->user();
        
        if (!$user->two_factor_secret) {
            return back()->withErrors(['password' => 'Two-factor authentication is not enabled.']);
        }

        $user->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 10);
            })->all())),
        ])->save();

        return back()->with('success', 'New recovery codes have been generated.');
    }

    /**
     * Generate QR code for 2FA setup.
     */
    private function generateQrCode($user): string
    {
        $google2fa = new Google2FA();
        
        $companyName = config('app.name', 'CalibrationTrack');
        $holderName = $user->email;
        $secret = decrypt($user->two_factor_secret);
        
        $qrCodeUrl = $google2fa->getQRCodeUrl(
            $companyName,
            $holderName,
            $secret
        );

        // Generate SVG QR code using bacon/bacon-qr-code
        $renderer = new \BaconQrCode\Renderer\ImageRenderer(
            new \BaconQrCode\Renderer\RendererStyle\RendererStyle(200),
            new \BaconQrCode\Renderer\Image\SvgImageRenderer()
        );
        $writer = new \BaconQrCode\Writer($renderer);
        
        return $writer->writeString($qrCodeUrl);
    }
}
