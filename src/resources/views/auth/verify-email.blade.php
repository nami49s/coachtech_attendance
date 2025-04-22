<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" >
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" >
    <link rel="stylesheet" href="{{ asset('css/email.css') }}" >
</head>
<body>
    <header class="header">
        <img src="{{ asset('images/CoachTech_White 1.png') }}" alt="coachtech">
    </header>
    <main>
        <div class="container">
            <p>登録していただいたメールアドレスに認証メールを送付しました。</p>
            <p>メール認証を完了してください。</p>

            @if (session('message'))
                <div class="alert alert-success">
                    {{ session('message') }}
                </div>
            @endif
            <div class="btn-container">
                @if (app()->isLocal() || app()->environment('testing'))
                    @if (session('verification_link'))
                    <a href="{{ session('verification_link') }}" class="btn btn-primary">
                        認証はこちらから
                    </a>
                @else
                    <p>メール内のリンクから認証を完了してください。</p>
                    @endif
                @endif

                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn btn-resend">認証メールを再送する</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>