<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // HARD BLOCK: Check if this user has an active session on ANOTHER device
            // We consider a session "active" if it was updated in the last 5 minutes.
            $activeSession = DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', session()->getId())
                ->where('last_activity', '>=', now()->subMinutes(5)->getTimestamp())
                ->first();

            if ($activeSession) {
                // Log the attempt and logout the current attempt
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'This account is already logged in on another device. Please log out from that device first.',
                ])->withInput($request->only('email'));
            }

            $request->session()->regenerate();

            // Record this session as the authorised one
            $user->update([
                'status'     => 'Active',
                'session_id' => session()->getId(),
            ]);

            // Log successful login
            \App\Helpers\AuditLogger::log('Login', 'User ' . $user->name . ' logged in');

            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->withInput($request->only('email'));
    }

    /**
     * Helper to redirect users based on their role.
     */
    private function redirectBasedOnRole($user)
    {
        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            // Update status to Inactive and clear last_seen_at for instant offline status
            try {
                Auth::user()->update([
                    'status'     => 'Inactive',
                    'last_seen_at' => null, // Forces is_online to return false immediately
                    'session_id' => null,   // Clear session so middleware doesn't fire after re-login
                ]);

                \App\Helpers\AuditLogger::log('Logout', 'User ' . Auth::user()->name . ' logged out');
            } catch (\Exception $e) {
                // Log error but continue logging out
                \Log::error('Error during logout status update: ' . $e->getMessage());
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    public function forgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $email = $request->email;
        $user = \App\Models\User::where('email', $email)->first();

        if (!$user) {
            // To prevent email enumeration, pretend it sent.
            return redirect()->route('otp.verify.form')->with([
                'status' => 'If your email is registered, you will receive an OTP shortly.',
                'email' => $email
            ]);
        }

        // Generate 6-digit OTP
        $otp = (string) rand(100000, 999999);

        // Store OTP in database (hash it for security)
        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'email' => $email,
                'token' => \Hash::make($otp),
                'created_at' => now()
            ]
        );

        // Fetch settings for the email
        $settings = \App\Models\SystemSetting::pluck('value', 'key')->toArray();

        // Dispatch Email immediately.
        try {
            \Mail::to($email)->send(new \App\Mail\ResetPasswordOtpMail($otp, $settings));
        } catch (\Exception $e) {
            \Log::error('OTP Mail Delivery Failed: ' . $e->getMessage());
            return back()->withErrors(['email' => 'Unable to send OTP. Please check your internet connection or try again later.'])
                         ->withInput($request->only('email'));
        }

        // Redirect to OTP verification page immediately.
        return redirect()->route('otp.verify.form')->with([
            'status' => 'An authentication code has been sent to your email.',
            'email' => $email
        ]);
    }

    public function showVerifyOtpForm()
    {
        if (!session('email') && !request()->old('email')) {
            return redirect()->route('password.request')->withErrors(['email' => 'Session expired. Please start over.']);
        }
        return view('auth.verify-otp', ['email' => session('email') ?? request()->old('email')]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'token' => 'required|numeric|digits:6',
            'email' => 'required|email',
        ]);

        $record = \DB::table('password_reset_tokens')->where('email', $request->email)->first();

        if (!$record) {
            return back()->withErrors(['token' => 'Invalid or expired OTP.'])->withInput($request->only('email'));
        }

        // Check expiration (Strict 15 minutes)
        $createdAt = \Carbon\Carbon::parse($record->created_at);
        $expiryTime = $createdAt->copy()->addMinutes(15);
        $currentTime = now();

        if ($currentTime->gt($expiryTime)) {
            \Log::warning("OTP Expired for {$request->email}. Created at: {$createdAt}, Expires at: {$expiryTime}, Current check: {$currentTime}");
            \DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return back()->withErrors(['token' => 'Your OTP has expired. Please request a new one.'])->withInput($request->only('email'));
        }

        // Verify OTP Hash
        if (!\Hash::check($request->token, $record->token)) {
            return back()->withErrors(['token' => 'The provided OTP is incorrect.'])->withInput($request->only('email'));
        }

        // OTP is valid! Store session authorization to proceed to the actual password reset route.
        session(['otp_verified_email' => $request->email]);

        return redirect()->route('password.reset')->with('status', 'OTP Verified! Please enter your new password.');
    }

    public function resetPassword()
    {
        // Must be authorized from the OTP verification step
        if (!session('otp_verified_email')) {
            return redirect()->route('password.request')->withErrors(['email' => 'Unauthorized access. Please verify your OTP first.']);
        }

        return view('auth.reset-password', ['email' => session('otp_verified_email')]);
    }

    public function updatePassword(Request $request)
    {
        // Must be authorized
        if (!session('otp_verified_email') || session('otp_verified_email') !== $request->email) {
            return redirect()->route('password.request')->withErrors(['email' => 'Session expired or invalid. Please verify your OTP again.']);
        }

        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        // Update User Password
        $user = \App\Models\User::where('email', $request->email)->first();
        if ($user) {
            $user->forceFill([
                'password' => \Hash::make($request->password)
            ])->setRememberToken(\Str::random(60));
            $user->save();
            event(new \Illuminate\Auth\Events\PasswordReset($user));
        }

        // Clean up OTP token & Session Variables
        \DB::table('password_reset_tokens')->where('email', $request->email)->delete();
        session()->forget('otp_verified_email');

        return redirect()->route('login')->with('status', 'Your password has been reset successfully. You may now log in.');
    }
}
