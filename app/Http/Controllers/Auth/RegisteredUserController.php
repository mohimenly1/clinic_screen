<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     * 
     * التسجيل معطل - فقط المديرون يمكنهم إنشاء حسابات جديدة
     */
    public function create(): RedirectResponse
    {
        // إعادة توجيه إلى صفحة تسجيل الدخول بدلاً من السماح بالتسجيل العام
        return redirect()->route('login')->with('message', 'التسجيل العام غير متاح. يرجى التواصل مع المدير للحصول على حساب.');
    }

    /**
     * Handle an incoming registration request.
     * 
     * التسجيل معطل - فقط المديرون يمكنهم إنشاء حسابات جديدة
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // رفض أي محاولة للتسجيل العام
        return redirect()->route('login')->with('error', 'التسجيل العام غير متاح. يرجى التواصل مع المدير للحصول على حساب.');
    }
}
