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
            <a href="" class="request-link">申請一覧</a>
        </div>
        <form action="{{ route('admin.logout') }}" method="POST">
            @csrf
                <button type="submit">ログアウト</button>
        </form>
    </header>
    <main>
    <h1>{{ $user->name }} さんの勤怠</h1>
    <nav>
        <a href="{{ route('admin.monthlyAttendance', ['user' => $user->id, 'month' => \Carbon\Carbon::parse($month)->subMonth()->format('Y-m')]) }}">←前月</a>
        {{ \Carbon\Carbon::parse($month)->format('Y年m月') }}
        <a href="{{ route('admin.monthlyAttendance', ['user' => $user->id, 'month' => \Carbon\Carbon::parse($month)->addMonth()->format('Y-m')]) }}">翌月→</a>
    </nav>
    <table>
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
            @foreach ($attendances as $attendance)
                <tr>
                    <td>{{ $attendance->date }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->checkin_time)->format('H:i') }}</td>
                    <td>{{ \Carbon\Carbon::parse($attendance->checkout_time)->format('H:i') ?? '未退勤' }}</td>
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
                    <td><a href="{{ route('admin.show', ['attendance' => $attendance->id]) }}">詳細</a></td>
                </tr>
            @endforeach
        </tbody>
        </table>
    </main>
</body>
</html>