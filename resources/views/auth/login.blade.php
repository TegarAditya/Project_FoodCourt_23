<!DOCTYPE html>
<html class="h-100" lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ trans('labels.admin_title') }}</title>
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="{{asset('storage/app/public/assets/images/favicon.png')}}">
    <link rel="stylesheet" type="text/css" href="{{asset('storage/app/public/assets/css/fonts.css')}}">
    <link href="{{asset('storage/app/public/assets/css/style.css')}}" rel="stylesheet">

</head>

<body class="h-100">
    @if(session()->has('danger'))
        <div class="alert alert-danger" style="text-align: center;">
            {{ session()->get('danger') }}
        </div>
    @endif
    <div class="login-form-bg h-100">
        <div class="container h-100">
            <div class="row justify-content-center h-100">
                <div class="col-xl-6">
                    <div class="form-input-content">
                        <div class="card login-form mb-0">
                            <div class="card-body pt-5">
                                    <center>
                                        <img src="{!! asset('storage/app/public/assets/images/logo5.png') !!}" height="60" alt="" >
                                    </center>
                                <form method="POST" class="mt-5 mb-5 login-input" action="{{ route('login') }}">
                                    @csrf

                                    <div class="form-group">
                                            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" required="" autocomplete="email" autofocus placeholder="{{ trans('messages.enter_email') }}">

                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                    </div>

                                    <div class="form-group">
                                            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password" placeholder="{{ trans('messages.enter_password') }}">

                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                    </div>

                                    <button type="submit" class="btn login-form__btn submit w-100">
                                    {{ trans('labels.admin') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--**********************************
        Scripts
    ***********************************-->
    <script src="{{asset('storage/app/public/assets/plugins/common/common.min.js')}}"></script>
    <script src="{{asset('storage/app/public/assets/js/custom.min.js')}}"></script>
    <script src="{{asset('storage/app/public/assets/js/settings.js')}}"></script>
    <script src="{{asset('storage/app/public/assets/js/gleek.js')}}"></script>
    <script src="{{asset('storage/app/public/assets/js/styleSwitcher.js')}}"></script>
</body>
</html>
