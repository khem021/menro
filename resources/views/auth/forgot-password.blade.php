@extends('layouts.guest')

@section('title', 'Forgot Password')

@section('card')

            <div style="text-align:center;margin-bottom:1.75rem;">
                <div style="font-size:1.625rem;font-weight:700;letter-spacing:0.1em;color:var(--title-accent);text-shadow:0 0 48px var(--title-glow);">MENRO</div>
                <div style="font-size:0.75rem;margin-top:0.3rem;color:var(--subtitle-color);letter-spacing:0.01em;">Waste Management Information System</div>
            </div>

            <h1 class="help-title">Forgot your password?</h1>
            <p class="help-text">
                MENRO does not email reset links. Accounts are managed by the office,
                so a person sets your new password for you.
            </p>

            <ul class="help-list help-text">
                <li>
                    <strong>MENRO Staff:</strong> ask your System Administrator. They can
                    set a new password for you on the Users page.
                </li>
                <li>
                    <strong>System Administrators:</strong> if another administrator can
                    still sign in, ask them. If nobody can, contact the person who
                    maintains the MENRO system.
                </li>
            </ul>

            <p class="help-text">
                Setting a new password signs the account out everywhere else, so anyone
                still using the old one is logged out.
            </p>
@endsection

@section('below-card')
        <p style="text-align:center;margin-top:1.25rem;">
            <a href="{{ route('login') }}" class="forgot-link" style="font-size:0.8125rem;">&larr; Back to login</a>
        </p>
@endsection
