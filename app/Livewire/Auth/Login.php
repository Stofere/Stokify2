<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;

#[Layout('layouts.auth')]
class Login extends Component
{
    public $username = '';
    public $password = '';

    public function prosesLogin()
    {
        $this->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        if (Auth::attempt(['username' => $this->username, 'password' => $this->password])) {
            session()->regenerate();
            return redirect()->route('dashboard');
        }

        $this->addError('login', 'Username atau Password salah!');
    }

    public function render()
    {
        return view('livewire.auth.login');
    }
}