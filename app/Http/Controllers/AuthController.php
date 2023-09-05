<?php

namespace App\Http\Controllers;

use App\Mail\ResetPassword;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Throwable;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register() {
        return view('auth.register');
    }

    protected function create(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required'],
            'photo' => ['image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        try {
            $file = $request->file('photo');
            $name_file = time()."_".$file->getClientOriginalName();
            
            $destination = 'assets/user';
            $file->move($destination, $name_file);
    
            User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'photo'     => $name_file,
            ]);
    
            $notif = [
                'message'   => 'Register berhasil',
                'title'      => 'Register',
            ];

            return redirect()->route('login')->with($notif);
        } catch (Throwable $e) {
            return redirect()->back()->with(['error' => 'Failed! ' . $e->getMessage()]); 
        }
    }

    public function showReset() 
    {
        return view('auth.reset');
    }

    public function sendEmail(Request $request) {
        try {

            $user = User::where('email', $request->email)->first();

            if($user) {
            $random = Str::random(10);
            $link = env('APP_URL') . '/reset-passoword/' . $random;

            User::where('id', $user->id)->update([
                'reset_link'    => $random
            ]);

            Mail::to($request->email)->send(new ResetPassword($user, $link));

            $notif = [
                'message'    => 'Link reset berhasil terkirim',
                'title'      => 'Link Reset',
            ];
            
            return redirect()->back()->with($notif);
            } else {
                return redirect()->back()->with(['error' => 'Email ' . $request->email . ' Tidak Terdaftar!.']); 
            }

        } catch (Throwable $e) {
            return redirect()->back()->with(['error' => 'Failed! ' . $e->getMessage()]); 
        }
    }

    public function resetPasswordShow($code) {
        $user = User::where('reset_link', $code)->first();
        // $code = $code;
        if($user) {
            return view('auth.update-password', compact('code'));
        } else {
            return 'Link sudah diperbarui.';
        }
    }

    public function updatePassword(Request $request, $code) {
        try {
            User::where('reset_link', $code)->update([
                'password'  => Hash::make($request->password),
            ]);

            $notif = [
                'message'   => 'Password Berhasil Di Update',
                'title'      => 'Reset Password',
            ];

            return redirect()->route('login')->with($notif);
        } catch (Throwable $e) {
            return redirect()->back()->with(['error' => 'Failed! ' . $e->getMessage()]);
        }
    }
}
