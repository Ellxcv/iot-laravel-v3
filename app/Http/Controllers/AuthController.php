<?php

namespace App\Http\Controllers;

use App\Application\DTOs\LoginUserDTO;
use App\Application\DTOs\RegisterUserDTO;
use App\Application\UseCases\Auth\LoginUserUseCase;
use App\Application\UseCases\Auth\RegisterUserUseCase;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use InvalidArgumentException;

/**
 * Authentication Controller
 * 
 * Handles HTTP requests for authentication
 * Delegates business logic to use cases (Clean Architecture)
 */
class AuthController extends Controller
{
    public function __construct(
        private RegisterUserUseCase $registerUseCase,
        private LoginUserUseCase $loginUseCase
    ) {}

    /**
     * Show the registration form
     */
    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        try {
            // Create DTO from validated data
            $dto = RegisterUserDTO::fromArray($request->validated());

            // Execute use case
            $userEntity = $this->registerUseCase->execute($dto);

            // Log the user in (convert entity ID to authenticate)
            Auth::loginUsingId($userEntity->getId());

            return redirect()->route('dashboard')
                ->with('success', 'Registration successful! Welcome aboard!');

        } catch (InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show the login form
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        try {
            // Create DTO from validated data
            $dto = LoginUserDTO::fromArray($request->validated());

            // Execute use case
            $userEntity = $this->loginUseCase->execute($dto);

            // Log the user in
            Auth::loginUsingId($userEntity->getId(), $dto->remember);

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Welcome back!');

        } catch (InvalidArgumentException $e) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Handle logout request
     */
    public function logout(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully');
    }
}
