<?php

namespace App\Http\Controllers;

use App\Models\UserSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $settings = $user->setting ?: UserSetting::query()->create([
            'user_id' => $user->id,
            'monthly_income' => 0,
            'currency' => 'KZT',
            'locale' => 'ru-RU',
        ]);

        return view('settings.index', compact('user', 'settings'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.Auth::id()],
        ]);

        Auth::user()->update($data);

        return back()->with('success', 'Профиль обновлен.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        $user = Auth::user();
        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Текущий пароль неверный.']);
        }

        $user->update(['password' => Hash::make($data['password'])]);

        return back()->with('success', 'Пароль обновлен.');
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'monthly_income' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:8'],
            'locale' => ['required', 'string', 'max:16'],
        ]);

        UserSetting::query()->updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'monthly_income' => (float) ($data['monthly_income'] ?? 0),
                'currency' => $data['currency'],
                'locale' => $data['locale'],
            ]
        );

        return back()->with('success', 'Настройки сохранены.');
    }
}
