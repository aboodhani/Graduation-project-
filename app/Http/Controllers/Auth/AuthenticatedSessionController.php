<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required','string','email'],
            'password' => ['required','string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // نجاح تسجيل الدخول -> جدد الجلسة
        $request->session()->regenerate();

        // احصل على المستخدم الحالي **بعد** نجاح Auth::attempt()
        $user = $request->user(); // مكافئ لـ Auth::user()

        // لو المستخدم دكتور، اجبره يروح لداشبورد الدكتور مباشرة (تجاهل أي intended سابق)
        if ($user && ($user->role ?? '') === 'doctor') {
            // امسح أي intended محفوظ حتى ما يرجع للمكان اللي كان يحاول يزوره قبل اللوقين
            $request->session()->forget('url.intended');

            // نفّذ redirect لراوت مسمّى إن وُجد، وإلا استخدم مسار ثابت احتياطي
            if (Route::has('doctor.dashboard')) {
                return redirect()->route('doctor.dashboard');
            }

            // احتياطي: /doctor
            return redirect('/doctor');
        }

        // خلاف ذلك: أعد التوجيه إلى intended (أو راوت توجيه عام إن موجود)
        if (Route::has('redirect.by.role')) {
            return redirect()->intended(route('redirect.by.role'));
        }

        return redirect()->intended('/'); // fallback عام
    }

    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
