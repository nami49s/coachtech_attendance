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
            <a href="{{ route('admin.requests.index') }}" class="request-link">申請一覧</a>
        </div>
        <form action="{{ route('admin.logout') }}" method="POST">
            @csrf
                <button type="submit">ログアウト</button>
        </form>
    </header>
    <main>
        <div class="container">
            <h2><span class="line">⎜</span>{{ $date->format('Y年m月d日') }}の勤怠</h2>
            <div class="date-navigation">
                <a class="date-navigation-link" href="{{ route('admin.index', ['date' => $date->copy()->subDay()->format('Y-m-d')]) }}">⬅︎ 前日</a>
                <span class="date-title">🗓️{{ $date->format('Y/m/d') }}</span>
                <a class="date-navigation-link" href="{{ route('admin.index', ['date' => $date->copy()->addDay()->format('Y-m-d')]) }}">翌日 ➡︎</a>
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
                    @foreach ($staffs as $staff)
                        @php
                            $attendance = $attendances->firstWhere('user_id', $staff->id);
                            $checkin = $attendance?->checkin_time;
                            $checkout = $attendance?->checkout_time;
                            $breaks = $attendance?->breaks ?? collect([]);
                            $endedBreaks = $breaks->filter(fn($b) => $b->break_end);
                            $totalBreak = $endedBreaks->sum(fn($b) => strtotime($b->break_end) - strtotime($b->break_start));
                        @endphp
                        <tr>
                            <td>{{ $staff->name }}</td>
                            <td>{{ $checkin ? \Carbon\Carbon::parse($checkin)->format('H:i') : '' }}</td>
                            <td>{{ $checkout ? \Carbon\Carbon::parse($checkout)->format('H:i') : ($checkin ? '未退勤' : '') }}</td>
                            <td>{{ $totalBreak > 0 ? gmdate("H:i", $totalBreak) : '' }}</td>
                            <td>
                                @if ($checkin && $checkout)
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
        </div>
    </main>
</body>
</html>