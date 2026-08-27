<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Login - GSTV</title>
  <meta content="" name="description">
  <meta content="" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <link href="{{ URL::asset('public/assets/img/favicon.png') }}" rel="icon">
  <link href="{{ URL::asset('public/assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">

  
  <!-- Vendor CSS Files -->
  <link href="{{ URL::asset('public/assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
  <link href="{{ URL::asset('public/assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
  <link href="{{ URL::asset('public/assets/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
  <link href="{{ URL::asset('public/assets/vendor/quill/quill.snow.css') }}" rel="stylesheet">
  <link href="{{ URL::asset('public/assets/vendor/quill/quill.bubble.css') }}" rel="stylesheet">
  <link href="{{ URL::asset('public/assets/vendor/remixicon/remixicon.css') }}" rel="stylesheet">
  <link href="{{ URL::asset('public/assets/vendor/simple-datatables/style.css') }}" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="{{ URL::asset('public/assets/css/style.css') }}" rel="stylesheet">
</head>

<body>

  <main>
    <div class="container">

      <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

              <div class="d-flex justify-content-center py-4">
                <a href="" class="logo d-flex align-items-center w-auto">
                  <img src="{{ URL::asset('public/assets/img/logo.png') }}" alt="">
                </a>
              </div><!-- End Logo -->

              <div class="card logincls mb-3">

                <div class="card-body">

                  <div class="pt-4 pb-2">
                    <h5 class="card-title text-center pb-0 fs-4">Login to Your Account</h5>
                    <p class="text-center small">Enter your username & password to login</p>
                  </div>                  
                  @if($errors->any())
                      <div class="alert alert-danger">
                          @foreach ($errors->all() as $error)
                            {{ $error }}
                          @endforeach
                      </div>
                  @endif
                  @if(session('status'))
                      <div class="alert alert-success">
                          {{ session('status') }}
                      </div>
                  @endif
                  <form class="row g-3 needs-validation" method="POST" action="{{ url('/login') }}" novalidate>
                    @csrf
                    <div class="col-12">
                      <label for="yourUsername" class="form-label">Email Address</label>
                      <div class="input-group has-validation">
                        <input type="email" name="email" value="{{ old('email') }}" class="form-control" id="yourUsername" required>
                        <div class="invalid-feedback">Please enter your Email Address.</div>
                      </div>
                    </div>

                    <div class="col-12">
                      <label for="yourPassword" class="form-label">Password</label>
                      <input type="password" name="password" class="form-control" id="yourPassword" required>
                      <div class="invalid-feedback">Please enter your password!</div>
                    </div>

                    
                    <div class="col-12">
                      <button class="btn btn-primary w-100" type="submit">Login</button>
                    </div>
                    
                  </form>

                </div>
              </div>


            </div>
          </div>
        </div>

      </section>

    </div>
  </main><!-- End #main -->
<!-- Vendor JS Files -->
<script src="{{ URL::asset('public/assets/vendor/tinymce/tinymce.min.js') }}"></script>

<!-- Template Main JS File -->
<script src="{{ URL::asset('public/assets/js/main.js') }}"></script>

</body>

</html>