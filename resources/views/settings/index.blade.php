@extends('layouts.master')

@section('title', 'Configurações')

@section('css')
<link href="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.css') }}" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="mg-clients-page">
    <div class="mg-clients-toolbar">
        <div class="mg-clients-toolbar__left">
            <h1 class="mg-page-title mb-0">Configurações</h1>
            <div class="mg-clients-counters">
                <span class="mg-clients-counter">
                    <i class="ri-settings-3-line"></i>
                    Preferências da plataforma
                </span>
            </div>
        </div>
        <div class="mg-clients-toolbar__right">
            <button type="button" class="mg-btn-ghost" id="resetDefaults">
                <i class="ri-refresh-line"></i> Restaurar padrões
            </button>
        </div>
    </div>

    <p class="mg-page-sub mb-0">Preferências da plataforma e integrações.</p>

    <div class="mg-panel mg-panel--compact">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="ri-check-line align-middle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="ri-error-warning-line align-middle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <form action="{{ route('settings.update') }}" method="POST" id="settingsForm">
            @csrf

            <div class="mg-segment-tabs mb-4" role="tablist">
                <a class="mg-segment-tab is-active active" data-bs-toggle="tab" href="#app-settings" role="tab">
                    <i class="ri-settings-3-line"></i> Aplicação
                </a>
                <a class="mg-segment-tab" data-bs-toggle="tab" href="#email-settings" role="tab">
                    <i class="ri-mail-line"></i> E-mail
                </a>
                <a class="mg-segment-tab" data-bs-toggle="tab" href="#payment-settings" role="tab">
                    <i class="ri-bank-card-line"></i> Pagamentos
                </a>
                <a class="mg-segment-tab" data-bs-toggle="tab" href="#security-settings" role="tab">
                    <i class="ri-shield-check-line"></i> Segurança
                </a>
                <a class="mg-segment-tab" data-bs-toggle="tab" href="#notification-settings" role="tab">
                    <i class="ri-notification-3-line"></i> Notificações
                </a>
            </div>

            <div class="tab-content">
                <div class="tab-pane active" id="app-settings" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="app_name" class="mg-field-label">Application Name <span class="text-danger">*</span></label>
                            <input type="text" class="mg-field @error('app_name') is-invalid @enderror"
                                   id="app_name" name="app_name"
                                   value="{{ old('app_name', $settings->get('app')?->firstWhere('name', 'app_name')->value ?? '') }}">
                            @error('app_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="app_timezone" class="mg-field-label">Timezone</label>
                            <select class="mg-field @error('app_timezone') is-invalid @enderror"
                                    id="app_timezone" name="app_timezone">
                                <option value="UTC">UTC</option>
                                <option value="America/New_York">America/New_York</option>
                                <option value="Europe/London">Europe/London</option>
                                <option value="Asia/Dubai">Asia/Dubai</option>
                            </select>
                            @error('app_timezone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="app_currency" class="mg-field-label">Currency</label>
                            <input type="text" class="mg-field" id="app_currency" name="app_currency"
                                   value="{{ old('app_currency', $settings->get('app')?->firstWhere('name', 'app_currency')->value ?? 'USD') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="app_language" class="mg-field-label">Language</label>
                            <select class="mg-field" id="app_language" name="app_language">
                                <option value="en">English</option>
                                <option value="es">Spanish</option>
                                <option value="fr">French</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="email-settings" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="mail_mailer" class="mg-field-label">Mail Provider</label>
                            <select class="mg-field" id="mail_mailer" name="mail_mailer">
                                <option value="smtp">SMTP</option>
                                <option value="sendmail">Sendmail</option>
                                <option value="mailgun">Mailgun</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="mail_host" class="mg-field-label">Mail Host</label>
                            <input type="text" class="mg-field" id="mail_host" name="mail_host"
                                   value="{{ old('mail_host', $settings->get('email')?->firstWhere('name', 'mail_host')->value ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="mail_port" class="mg-field-label">Port</label>
                            <input type="number" class="mg-field" id="mail_port" name="mail_port"
                                   value="{{ old('mail_port', $settings->get('email')?->firstWhere('name', 'mail_port')->value ?? '587') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="mail_encryption" class="mg-field-label">Encryption</label>
                            <select class="mg-field" id="mail_encryption" name="mail_encryption">
                                <option value="tls">TLS</option>
                                <option value="ssl">SSL</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="mail_from_address" class="mg-field-label">From Email</label>
                            <input type="email" class="mg-field" id="mail_from_address" name="mail_from_address"
                                   value="{{ old('mail_from_address', $settings->get('email')?->firstWhere('name', 'mail_from_address')->value ?? '') }}">
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="payment-settings" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-12">
                            <h5 class="mg-section-title h6 mb-0">Stripe Configuration</h5>
                        </div>
                        <div class="col-md-6">
                            <label for="stripe_key" class="mg-field-label">Stripe Publishable Key</label>
                            <input type="text" class="mg-field" id="stripe_key" name="stripe_key"
                                   value="{{ old('stripe_key', $settings->get('payment')?->firstWhere('name', 'stripe_key')->value ?? '') }}">
                        </div>
                        <div class="col-md-6">
                            <label for="stripe_secret" class="mg-field-label">Stripe Secret Key</label>
                            <input type="password" class="mg-field" id="stripe_secret" name="stripe_secret"
                                   value="{{ old('stripe_secret', $settings->get('payment')?->firstWhere('name', 'stripe_secret')->value ?? '') }}">
                        </div>
                        <div class="col-12 mt-2">
                            <h5 class="mg-section-title h6 mb-0">PayPal Configuration</h5>
                        </div>
                        <div class="col-md-4">
                            <label for="paypal_mode" class="mg-field-label">PayPal Mode</label>
                            <select class="mg-field" id="paypal_mode" name="paypal_mode">
                                <option value="sandbox">Sandbox</option>
                                <option value="live">Live</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="paypal_client_id" class="mg-field-label">Client ID</label>
                            <input type="text" class="mg-field" id="paypal_client_id" name="paypal_client_id"
                                   value="{{ old('paypal_client_id', $settings->get('payment')?->firstWhere('name', 'paypal_client_id')->value ?? '') }}">
                        </div>
                        <div class="col-md-4">
                            <label for="paypal_secret" class="mg-field-label">Secret</label>
                            <input type="password" class="mg-field" id="paypal_secret" name="paypal_secret"
                                   value="{{ old('paypal_secret', $settings->get('payment')?->firstWhere('name', 'paypal_secret')->value ?? '') }}">
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="security-settings" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="email_verification_enable" name="email_verification_enable" value="on"
                                       {{ ($settings->get('security')?->firstWhere('name', 'email_verification_enable')->value ?? '') == 'on' ? 'checked' : '' }}>
                                <label class="form-check-label" for="email_verification_enable">
                                    Enable Email Verification
                                </label>
                            </div>
                            <small class="text-muted">Require users to verify their email address</small>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="2fa_enable" name="2fa_enable" value="on">
                                <label class="form-check-label" for="2fa_enable">
                                    Enable Two-Factor Authentication
                                </label>
                            </div>
                            <small class="text-muted">Allow users to enable 2FA for their accounts</small>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="recaptcha_enable" name="recaptcha_enable" value="on">
                                <label class="form-check-label" for="recaptcha_enable">
                                    Enable reCAPTCHA
                                </label>
                            </div>
                            <small class="text-muted">Protect forms with Google reCAPTCHA</small>
                        </div>
                        <div class="col-md-6">
                            <label for="session_lifetime" class="mg-field-label">Session Lifetime (minutes)</label>
                            <input type="number" class="mg-field" id="session_lifetime" name="session_lifetime"
                                   value="{{ old('session_lifetime', $settings->get('security')?->firstWhere('name', 'session_lifetime')->value ?? '120') }}">
                        </div>
                    </div>
                </div>

                <div class="tab-pane" id="notification-settings" role="tabpanel">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="notification_email" name="notification_email" value="on" checked>
                                <label class="form-check-label" for="notification_email">
                                    Email Notifications
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="notification_sms" name="notification_sms" value="on">
                                <label class="form-check-label" for="notification_sms">
                                    SMS Notifications
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" role="switch"
                                       id="notification_push" name="notification_push" value="on">
                                <label class="form-check-label" for="notification_push">
                                    Push Notifications
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="mg-btn-primary">
                    <i class="ri-save-line"></i> Save Settings
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('script')
<script src="{{ URL::asset('build/libs/sweetalert2/sweetalert2.min.js') }}"></script>
<script>
document.getElementById('settingsForm').addEventListener('submit', function(e) {
    const appName = document.getElementById('app_name').value;
    if (!appName) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            text: 'Application name is required!'
        });
    }
});

document.getElementById('resetDefaults').addEventListener('click', function() {
    Swal.fire({
        title: 'Reset to Defaults?',
        text: "This will reset all settings to their default values!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, reset it!'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire(
                'Reset!',
                'Settings have been reset to defaults.',
                'success'
            );
        }
    });
});

document.querySelectorAll('.mg-segment-tabs [data-bs-toggle="tab"]').forEach(function (tab) {
    tab.addEventListener('shown.bs.tab', function (e) {
        document.querySelectorAll('.mg-segment-tabs [data-bs-toggle="tab"]').forEach(function (el) {
            el.classList.toggle('is-active', el === e.target);
            el.classList.toggle('active', el === e.target);
        });
    });
});
</script>
@endsection
