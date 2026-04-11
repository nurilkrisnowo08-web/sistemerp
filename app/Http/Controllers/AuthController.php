<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Models\Part; // SAKTI: Import model Part biar gak error

class AuthController extends Controller
{
    // ================= LOGIN =================

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($request->only('email','password'))) {
            return redirect('/dashboard');
        }

        return back()->with('error','Email atau Password salah');
    }

    // ================= REGISTER =================

    public function showRegister()
    {
        return view('register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'produksi', 
        ]);

        Auth::logout(); 

        return redirect('/login')->with('success','Register berhasil. Silakan login!');
    }

    // ================= FORGOT =================

    public function showForgot()
    {
        return view('forgot');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        $user = User::where('email',$request->email)->first();

        if (!$user) {
            return back()->with('error','Email tidak ditemukan');
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect('/login')->with('success','Password berhasil direset');
    }

    // ================= DASHBOARD =================

   public function dashboard()
{
    // SAKTI: Ambil data biar baris 31 di dashboard.blade gak nangis
    $totalParts = Part::count(); 
    
    return view('dashboard', compact('totalParts'));
}

    // ================= PROFILE =================

    public function profile()
    {
        return view('profile');
    }

    // ================= UPDATE PROFILE =================

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|max:255',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        $user->name = $request->name;

        if ($request->hasFile('photo')) {
            if ($user->photo && Storage::exists('public/profile/'.$user->photo)) {
                Storage::delete('public/profile/'.$user->photo);
            }

            $file = $request->file('photo');
            $filename = time().'_'.$file->getClientOriginalName();
            $file->storeAs('public/profile', $filename);
            $user->photo = $filename;
        }

        $user->save();

        return redirect()->route('profile')->with('success','Profile berhasil diupdate');
    }

    // ================= LOGOUT =================

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}