<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Helpers\AuditLogger;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile.index');
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'profile_pic' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        $user = Auth::user();

        if ($request->hasFile('profile_pic')) {
            try {
                // Delete old photo if it exists and is accessible
                if ($user->profile_pic) {
                    $oldPath = public_path('uploads/' . $user->profile_pic);
                    if (file_exists($oldPath)) {
                        @unlink($oldPath); // @ suppresses a warning if deletion fails non-fatally
                    }
                }

                $file     = $request->file('profile_pic');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads'), $filename);

                $user->profile_pic = $filename;
                $user->save();

                AuditLogger::log('Update Profile', 'Updated profile picture');
                return redirect()->back()->with('success', 'Profile picture updated successfully.');
            } catch (\Exception $e) {
                \Log::error('Profile picture upload failed: ' . $e->getMessage());
                return redirect()->back()->with('error', 'Failed to upload profile picture. Please check file permissions and try again.');
            }
        }

        return redirect()->back()->with('error', 'No file uploaded.');
    }

    public function clearPhoto()
    {
        $user = Auth::user();
        if ($user->profile_pic) {
            $oldPath = public_path('uploads/' . $user->profile_pic);
            if (file_exists($oldPath)) {
                @unlink($oldPath);
            }
            $user->profile_pic = null;
            $user->save();
            AuditLogger::log('Clear Profile Picture', 'Removed profile picture');
            return redirect()->back()->with('success', 'Profile picture removed successfully.');
        }
        return redirect()->back()->with('error', 'No profile picture to remove.');
    }

    public function updateAccount(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'current_password' => 'required_with:password|nullable|string',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->name = $request->name;

        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return redirect()->back()->with('error', 'Incorrect current password. Password not updated.');
            }
            $user->password = Hash::make($request->password);
            AuditLogger::log('Update Password', 'Changed account password');
        }

        $user->save();

        AuditLogger::log('Update Account', 'Updated account details');
        return redirect()->back()->with('success', 'Account details updated successfully.');
    }
}
