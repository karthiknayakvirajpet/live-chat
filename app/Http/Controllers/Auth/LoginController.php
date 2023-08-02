<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /************************************************************************
    *Registration Form
    *************************************************************************/
    public function registerForm()
    {        
        return view('auth.register');
    }

    /************************************************************************
    *User registration
    *************************************************************************/
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        //create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if($user)
        {
            return redirect()->route('login')->with('success', 'Registered successfully.');
        }
        return redirect()->back()->withErrors(['error' => 'Something went wrong.']);
    }

    /************************************************************************
    *Login Form
    *************************************************************************/
    public function loginForm()
    {
        //If user is already logged in then redirect to home
        if (Auth::check()) {
            if(auth()->user()->role == 1) //Support Agent
            {
                return redirect('/home');
            }
            else
            {
                return redirect('/chat'); //Customer
            }
        }
        
        return view('auth.login');
    }

    /************************************************************************
    *Login function
    *************************************************************************/
    public function login(Request $request)
    {
        //login credentials
        $credentials = $request->only('email', 'password');

        //authentication logic
        if (Auth::attempt($credentials))
        {
            // Authentication successful, update the active status to true
            $user = Auth::user();
            User::where('id', $user->id)->update(['active' => true]);

            if(auth()->user()->role == 1)
            {
                return redirect()->intended('/home'); //Support Agent
            }
            else
            {
                return redirect()->intended('/chat'); //Customer
            }

        }
        else
        {
            //if failed
            return redirect()->back()->withInput()->withErrors(['login' => 'Invalid credentials']);
        }
    }

    /************************************************************************
    *Logout function
    *************************************************************************/
    public function logout()
    {
        // Get the currently authenticated user
        $user = Auth::user();

        if ($user) {
            // Update the active status to false
            User::where('id', $user->id)->update(['active' => false]);
        }

        Auth::logout();
        return redirect()->route('login');
    }
}
