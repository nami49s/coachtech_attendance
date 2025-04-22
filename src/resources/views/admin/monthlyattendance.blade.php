<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>月次勤怠</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" >
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" >
    <link rel="stylesheet" href="{{ asset('css/admin/monthlyattendance.css') }}" >
</head>
<body>
    <header class="header">
        <img src="{{ asset('images/CoachTech_White 1.png') }}" alt="coachtech">
        <div class="header-links">
            <a href="{{ route('admin.index') }}" class="attendance-link">勤怠一覧</a>
            <a href="{{ route('admin.staffs') }}" class="attendance-list-link">スタッフ一覧</a>
            <a href="{{ route('admin.requests.index') }}" class="request-link">申請一覧</a>
        </div>
        <form action="{{ route('admin.logout') }}" method="POST">
            @csrf
                <button type="submit">ログアウト</button>
        </form>
    </header>
    <main>
    <h1><span class="line">⎜</span>{{ $user->name }} さんの勤怠</h1>
    <nav class="month-navigation">
        <a class="month-navigation-link" href="{{ route('admin.monthlyAttendance', ['user' => $user->id, 'month' => \Carbon\Carbon::parse($month)->subMonth()->format('Y-m')]) }}">⬅︎ 前月</a>
        <span class="month-title">🗓️{{ \Carbon\Carbon::parse($month)->format('Y年m月') }}</span>
        <a class="month-navigation-link" href="{{ route('admin.monthlyAttendance', ['user' => $user->id, 'month' => \Carbon\Carbon::parse($month)->addMonth()->format('Y-m')]) }}">翌月 ➡︎</a>
    </nav>
    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
            @php
                $startOfMonth = \Carbon\Carbon::parse($month)->startOfMonth();
                $endOfMonth = \Carbon\Carbon::parse($month)->endOfMonth();
                $dates = \Carbon\CarbonPeriod::create($startOfMonth, $endOfMonth);
            @endphp

            @foreach ($dates as $date)
                @php
                    $attendance = $attendances->firstWhere('date', $date->format('Y-m-d'));
                    $checkin = $attendance?->checkin_time;
                    $checkout = $attendance?->checkout_time;
                    $breaks = $attendance?->breaks ?? collect();
                    $totalBreak = $breaks->sum(function($break) {
                        return $break->break_end ? strtotime($break->break_end) - strtotime($break->break_start) : 0;
                    });
                @endphp
                <tr>
                    <td>{{ $date->format('m/d') }} ({{ ['日','月','火','水','木','金','土'][$date->dayOfWeek] }})</td>
                    <td>{{ $checkin ? \Carbon\Carbon::parse($checkin)->format('H:i') : '' }}</td>
                    <td>{{ $checkout ? \Carbon\Carbon::parse($checkout)->format('H:i') : '' }}</td>
                    <td>{{ $attendance ? gmdate("H:i", $totalBreak) : '' }}</td>
                    <td>
                        @if ($attendance && $checkout)
                            {{ gmdate("H:i", strtotime($checkout) - strtotime($checkin) - $totalBreak) }}
                        @else

                        @endif
                    </td>
                    <td>
                        @if ($attendance)
                            <a class="detail-link" href="{{ route('admin.show', ['attendance' => $attendance->id]) }}">詳細</a>
                        @else

                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        </table>
        <div class="csv-export">
            <a href="{{ route('admin.monthlyAttendance.export', ['user' => $user->id, 'month' => $month]) }}" class="csv-button">
                CSV出力
            </a>
        </div>
    </main>
</body>
</html>