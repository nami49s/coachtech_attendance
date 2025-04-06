<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠一覧</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" >
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" >
    <link rel="stylesheet" href="{{ asset('css/attendance/index.css') }}" >
</head>
<body>
    <header class="header">
        <img src="{{ asset('images/CoachTech_White 1.png') }}" alt="coachtech">
        <div class="header-links">
            <a href="{{ route('attendance.show') }}" class="attendance-link">勤怠</a>
            <a href="{{ route('attendance.index') }}" class="attendance-list-link">勤怠一覧</a>
            <a href="" class="request-link">申請</a>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
                <button type="submit">ログアウト</button>
        </form>
    </header>
    <main>
        <h1>勤怠一覧</h1>

        <div class="month-navigation">
            <a href="{{ route('attendance.index', ['month' => $prevMonth]) }}">← 前月</a>
            <span>{{ \Carbon\Carbon::parse($currentMonth)->format('Y年m月') }}</span>
            <a href="{{ route('attendance.index', ['month' => $nextMonth]) }}">翌月 →</a>
        </div>

        <table class="attendance-table">
            <thead>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>勤務</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->date }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->checkin_time)->format('H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->checkout_time)->format('H:i')  ?? '未退勤' }}</td>
                    <td>
                        @php
                            $breaks = $attendance->breaks ?? collect([]);
                            $totalBreak = $breaks->sum(function($break) {
                                return $break->break_end ? strtotime($break->break_end) - strtotime($break->break_start) : 0;
                            });
                        @endphp
                        {{ gmdate("H:i", $totalBreak) }}
                    </td>
                    <td>
                        @if ($attendance->checkout_time)
                            {{ gmdate("H:i", strtotime($attendance->checkout_time) - strtotime($attendance->checkin_time) - $totalBreak) }}
                        @else
                            ー
                        @endif
                    </td>
                    <td><a href="{{ route('attendance.detail', $attendance->id) }}" class="detail-link">詳細</a></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </main>
</body>
</html>