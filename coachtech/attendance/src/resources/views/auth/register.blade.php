<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>register</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" >
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" >
    <link rel="stylesheet" href="{{ asset('css/register.css') }}" >
</head>
<body>
    <header class="header">
        <img src="{{ asset('images/CoachTech_White 1.png') }}" alt="coachtech">
    </header>
    <main>
        <div class="register-container">
            <h2>会員登録</h2>
            <form class="form-container" action="{{ route('register.store') }}" method="POST">
                @csrf
                <div>
                    <label for="name">名前</label>
                    <input type="text" name="name" value="{{ old('name') }}" />
                    @error('name')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <label for="email">メールアドレス</label>
                    <input type="email" name="email" value="{{ old('email') }}" />
                    @error('email')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <label for="password">パスワード</label>
                    <input type="password" name="password" value="{{ old('password') }}" />
                    @error('password')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation">パスワード確認</label>
                    <input type="password" name="password_confirmation" value="{{ old('password_confirmation') }}" />
                    @error('password_confirmation')
                        <div class="error-message">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit">登録する</button>

                <a href="{{ route('login') }}">ログインはこちら</a>
            </form>
        </div>
    </main>
</body>
</html>