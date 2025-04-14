<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" >
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" >
    <link rel="stylesheet" href="{{ asset('css/login.css') }}" >
</head>
<body>
    <header class="header">
        <img src="{{ asset('images/CoachTech_White 1.png') }}" alt="coachtech">
    </header>
    <main>
        <div class="login-container">
            <h2>管理者ログイン</h2>
            <form class="form-container" action="{{ route('admin.login') }}" method="POST">
                @csrf
                @if ($errors->has('login_error'))
                    <div class="error-message">
                        {{ $errors->first('login_error') }}
                    </div>
                @endif
                <div>
                    <label for="email">メールアドレス</label>
                    <input type="text" name="email" value="{{ old('email') }}" />
                    @error('email')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <label for="password">パスワード</label>
                    <input type="password" name="password" />
                    @error('password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit">管理者ログインする</button>

            </form>
        </div>
    </main>
</body>
</html>