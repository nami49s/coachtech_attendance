<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠詳細画面</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" >
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" >
    <link rel="stylesheet" href="{{ asset('css/admin/show.css') }}" >
</head>
<body>
    <header class="header">
        <img src="{{ asset('images/CoachTech_White 1.png') }}" alt="coachtech">
        <div class="header-links">
            <a href="{{ route('admin.index') }}" class="attendance-link">勤怠一覧</a>
            <a href="{{ route('admin.staffs') }}" class="attendance-list-link">スタッフ一覧</a>
            <a href="" class="request-link">申請一覧</a>
        </div>
        <form action="{{ route('admin.logout') }}" method="POST">
            @csrf
                <button type="submit">ログアウト</button>
        </form>
    </header>
    <main>
        <h1>勤怠詳細</h1>

        <form method="POST" action="">
            @csrf
            <div class="attendance-detail">
                <table class="detail-table">
                    <tr>
                        <th>名前</th>
                        <td>{{ $attendance->user->name }}</td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td>{{ \Carbon\Carbon::parse($attendance->date)->format('Y年 m月 d日') }}</td>
                    </tr>
                    <tr>
                        <th>出勤退勤時間</th>
                        <td>
                            <input type="time" name="checkin_time" value="{{ \Carbon\Carbon::parse($attendance->checkin_time)->format('H:i') }}" required>
                            〜
                            <input type="time" name="checkout_time" value="{{ \Carbon\Carbon::parse($attendance->checkout_time)->format('H:i') }}">
                        </td>
                    </tr>
                        @foreach ($attendance->breaks as $index => $break)
                        <tr>
                            <th>休憩{{ $index + 1 }}</th>
                            <td>
                                <input type="time" name="break_start[]" value="{{ \Carbon\Carbon::parse($break->break_start)->format('H:i') }}" required>
                                〜
                                <input type="time" name="break_end[]" value="{{ \Carbon\Carbon::parse($break->break_end)->format('H:i')  }}">
                            </td>
                        </tr>
                        @endforeach
                    <tr>
                        <th>休憩{{ count($attendance->breaks) + 1 }}</th>
                        <td>
                            <input type="time" name="break_start[]" value="">
                            〜
                            <input type="time" name="break_end[]" value="">
                        </td>
                    </tr>
                    <tr>
                        <th>備考</th>
                        <td>
                            <textarea name="remarks">{{ $attendance->remarks }}</textarea>
                        </td>
                    </tr>
                </table>
            </div>

            <button type="submit" class="btn btn-primary">修正</button>
        </form>
    </main>
</body>
</html>
