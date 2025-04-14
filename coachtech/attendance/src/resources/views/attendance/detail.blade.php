<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠詳細</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" >
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" >
    <link rel="stylesheet" href="{{ asset('css/attendance/detail.css') }}" >
</head>
<body>
    <header class="header">
        <img src="{{ asset('images/CoachTech_White 1.png') }}" alt="coachtech">
        <div class="header-links">
            <a href="{{ route('attendance.show') }}" class="attendance-link">勤怠</a>
            <a href="{{ route('attendance.index') }}" class="attendance-list-link">勤怠一覧</a>
            <a href="{{ route('requests.index') }}" class="request-link">申請</a>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
                <button type="submit">ログアウト</button>
        </form>
    </header>
    <main>
        <h1>勤怠詳細</h1>

        @if ($attendanceRequest && $attendanceRequest->breakRequests)
            <p style="color: red;">※承認待ちのため修正はできません。</p>

            <div class="attendance-detail">
                <table class="detail-table">
                    <tr>
                        <th>名前</th>
                        <td>{{ $attendanceRequest->user->name }}</td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td>{{ \Carbon\Carbon::parse($attendanceRequest->attendance->date)->format('Y年 m月 d日') }}</td>
                    </tr>
                    <tr>
                        <th>出勤退勤時間</th>
                        <td>
                            {{ \Carbon\Carbon::parse($attendanceRequest->checkin_time)->format('H:i') }}
                            〜
                            {{ \Carbon\Carbon::parse($attendanceRequest->checkout_time)->format('H:i') }}
                        </td>
                    </tr>
                    @foreach ($attendanceRequest->breakRequests as $index => $breakRequest)
                    <tr>
                        <th>休憩{{ $index + 1 }}</th>
                        <td>
                            {{ \Carbon\Carbon::parse($breakRequest->break_start)->format('H:i') }}
                            〜
                            {{ \Carbon\Carbon::parse($breakRequest->break_end)->format('H:i') }}
                        </td>
                    </tr>
                    @endforeach
                    <tr>
                        <th>備考</th>
                        <td>{{ $attendanceRequest->remarks }}</td>
                    </tr>
                </table>
            </div>
        @else

        <form method="POST" action="{{ route('attendance.request.submit') }}">
            @csrf
            <input type="hidden" name="attendance_id" value="{{ $attendance->id }}">
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
                                <input type="hidden" name="break_id[]" value="{{ $break->id }}">
                                <input type="time" name="break_start[]" value="{{ \Carbon\Carbon::parse($break->break_start)->format('H:i') }}" required>
                                〜
                                <input type="time" name="break_end[]" value="{{ \Carbon\Carbon::parse($break->break_end)->format('H:i') }}">
                            </td>
                        </tr>
                        @endforeach
                    <tr>
                        <th>休憩{{ count($attendance->breaks) + 1 }}</th>
                        <td>
                            <input type="hidden" name="break_id[]" value="">
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
        @endif
    </main>
</body>
</html>