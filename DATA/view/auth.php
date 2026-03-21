<style>
    body {
        background-color: #f8f9fa;
    }

    .row>* {
        padding-right: 0px!important;
        padding-left: 0px!important;
    }

    .login-wrapper {
        max-width: 1000px;
        background: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    .login-image {
        background: url('/DATA/img/alessio-soggetti-8jeWeKdygfk-unsplash-1000x1200.jpg') no-repeat center center;
        background-size: cover;
        height: 100%;
        border-radius: 0 12px 12px 0;
    }
    .login-form {
        padding: 40px;
    }
    @media (max-width: 767.98px) {
        .login-image {
            height: 200px;
        }

        .row>* {
            flex-shrink: 0;
            width: 100%;
            max-width: 100%;
            padding-right: calc(var(--bs-gutter-x) * .5)!important;
            padding-left: calc(var(--bs-gutter-x) * .5)!important;
            margin-top: var(--bs-gutter-y);
        }

        .login-image {
            border-radius: 0px!important;
        }
    }
</style>
<div class="d-flex align-items-center min-vh-100 py-4">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12 login-wrapper">
            <div class="row g-0">
                <!-- Форма -->
                <div class="col-lg-6">
                    <div class="login-form">
                        <h2 class="mb-4 text-center">Y.AddURL</h2>
                        <form action="/" method="post">
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" name="auth" class="form-control" id="password" required>
                            </div>
                            <button type="submit" class="btn btn-dark w-100 mt-3 py-2">Login</button>
                        </form>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="login-image"></div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
