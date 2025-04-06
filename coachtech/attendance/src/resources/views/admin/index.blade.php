<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠一覧</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" >
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" >
    <link rel="stylesheet" href="{{ asset('css/admin/index.css') }}" >
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
        <div class="container">
            <h2>{{ $date->format('Y年m月d日') }}の勤怠</h2>
            <div class="date-navigation">
                <a href="{{ route('admin.index', ['date' => $date->copy()->subDay()->format('Y-m-d')]) }}">←前日</a>
                <span>{{ $date->format('Y/m/d') }}</span>
                <a href="{{ route('admin.index', ['date' => $date->copy()->addDay()->format('Y-m-d')]) }}">翌日→</a>
            </div>
            <table class="attendance-table">
                <thead>
                    <tr>
                        <th>名前</th>
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
                        <td>{{ $attendance->user->name }}</td>
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
        </div>
    </main>
</body>
</html>